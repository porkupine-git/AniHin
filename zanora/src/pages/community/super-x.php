<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/anime/mal_cache.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['userID'] ?? 0;
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function format_points($points) {
    $points = (int)$points;
    if ($points >= 1000000) return round($points / 1000000, 1) . 'M';
    if ($points >= 1000)    return round($points / 1000) . 'K';
    return $points;
}
function format_short_number($n) {
    $n = (int)$n;
    if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
    if ($n >= 1000)    return round($n / 1000) . 'K';
    return (string)$n;
}
function getTopUsersByPeriod(mysqli $conn, string $period, int $limit = 10): array {
    if ($period === 'day') {
        $where = "DATE(ch.created_at) = CURDATE()";
    } elseif ($period === 'week') {
        $where = "YEARWEEK(ch.created_at, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($period === 'month') {
        $where = "YEAR(ch.created_at) = YEAR(CURDATE())
                  AND MONTH(ch.created_at) = MONTH(CURDATE())";
    } else {
        return [];
    }
    $sql = "
        SELECT
            u.id,
            u.username,
            u.image,
            u.points,
            u.roles,
            SUM(ch.amount) AS earned_points
        FROM coin_history ch
        JOIN users u ON u.id = ch.user_id
        WHERE {$where}
        GROUP BY u.id
        HAVING earned_points > 0
        ORDER BY earned_points DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}
function getTopEpisodeComments(mysqli $conn, int $limit = 10): array
{
    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.content,
            c.movie_id,
            c.episode_id,
            c.created_at,
            u.username,
            u.image,
            u.points,
            u.roles,
            IFNULL(cv.upvotes, 0)   AS upvotes,
            IFNULL(cv.downvotes, 0) AS downvotes,
            (
                SELECT COUNT(*)
                FROM comments r
                WHERE r.parent_id = c.id
                  AND r.status = 'active'
            ) AS reply_count
        FROM comments c
        LEFT JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        WHERE c.parent_id = 0
          AND c.status = 'active'
          AND c.created_at >= (NOW() - INTERVAL 1 DAY)
        ORDER BY (IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0)) DESC,
                 reply_count DESC,
                 c.created_at DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}
function getTopPostComments(mysqli $conn, int $limit = 10): array
{
    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.content,
            c.post_id,
            c.created_at,
            u.username,
            u.image,
            u.points,
            u.roles,
            p.title       AS post_title,
            p.slug        AS post_slug,
            IFNULL(cv.upvotes, 0)   AS upvotes,
            IFNULL(cv.downvotes, 0) AS downvotes,
            (
                SELECT COUNT(*)
                FROM post_comments r
                WHERE r.parent_id = c.id
                  AND r.status    = 'visible'
            ) AS reply_count
        FROM post_comments c
        LEFT JOIN users u ON u.id = c.user_id
        LEFT JOIN posts p ON p.id = c.post_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM post_comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        WHERE (c.parent_id IS NULL OR c.parent_id = 0)
          AND c.status = 'visible'
          AND c.created_at >= (NOW() - INTERVAL 1 DAY)
        ORDER BY (IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0)) DESC,
                 reply_count DESC,
                 c.created_at DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}
function getTopAnimeByCommentsWithMal(mysqli $conn, string $period, int $limit = 10): array
{
    if ($period === 'day') {
        $whereDate = "c.created_at >= (NOW() - INTERVAL 1 DAY)";
        $suffix    = 'day';
    } elseif ($period === 'week') {
        $whereDate = "c.created_at >= (NOW() - INTERVAL 7 DAY)";
        $suffix    = 'week';
    } elseif ($period === 'month') {
        $whereDate = "c.created_at >= (NOW() - INTERVAL 30 DAY)";
        $suffix    = 'month';
    } else {
        return [];
    }

    $sql = "
        SELECT
            ta.anime_id,
            ta.title,
            ta.poster,
            COUNT(c.id) AS com_count
        FROM comments c
        JOIN tracked_anime ta
          ON ta.anime_id = c.movie_id
        WHERE c.status = 'active'
          AND {$whereDate}
        GROUP BY ta.anime_id
        HAVING com_count > 0
        ORDER BY com_count DESC
        LIMIT ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];

    while ($r = $res->fetch_assoc()) {
        $animeId   = $r['anime_id'];
        $comCount  = (int)$r['com_count'];   // REAL total number of comments

        // Get MAL cached stats (auto-update every 24h)
        $malStats = ensureMalStatsCached($conn, $animeId);

        $rows[] = [
            'anime_id'      => $animeId,
            'title'         => $r['title'],
            'poster'        => $r['poster'],
            'comment_count' => $comCount,        // REAL TOTAL
            'period_suffix' => $suffix,          // day/week/month
            'mal_score'     => $malStats['score'],
            'mal_members'   => $malStats['members'],
        ];
    }

    $stmt->close();
    return $rows;
}
$topDayAnime   		= getTopAnimeByCommentsWithMal($conn, 'day',   10);
$topWeekAnime  		= getTopAnimeByCommentsWithMal($conn, 'week',  10);
$topMonthAnime 		= getTopAnimeByCommentsWithMal($conn, 'month', 10);
$topDay   	   		= getTopUsersByPeriod($conn, 'day', 10);
$topWeek 	   		= getTopUsersByPeriod($conn, 'week', 10);
$topMonth	   		= getTopUsersByPeriod($conn, 'month', 10);
$topEpisodeComments = getTopEpisodeComments($conn, 10);
$topPostComments    = getTopPostComments($conn, 10);
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title>Super X Chart & Community Leaderboard - <?= $websiteTitle ?></title>
    <meta name="description" content="Check out the Top users, most upvoted posts, and popular anime episode comments on the <?= $websiteTitle ?> Community Super X Chart.">
    <meta name="keywords" content="leaderboard, top anime posts, top users, community points, <?= strtolower($websiteTitle) ?> chart">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/community/super-x" />

        <!-- Performance Optimization: DNS Prefetch & Preconnect -->
    <link rel="dns-prefetch" href="https://s4.anilist.co">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://s4.anilist.co" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Open Graph (Facebook / Discord) -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= WEBSITE_URL ?>/community/super-x">
    <meta property="og:title" content="Super X Chart & Community Leaderboard - <?= $websiteTitle ?>">
    <meta property="og:description" content="Check out the Top users, most upvoted posts, and popular anime episode comments on the <?= $websiteTitle ?> Community Super X Chart.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Super X Chart & Community Leaderboard - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Check out the Top users, most upvoted posts, and popular anime episode comments on the <?= $websiteTitle ?> Community Super X Chart.">
    <meta name="twitter:image" content="<?= $banner ?>">

    <!-- Favicons & App Manifests -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/x-icon" href="<?= $favicon ?>" sizes="any" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons-192.png"> 
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icons-512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#202125">
    <meta name="msapplication-TileColor" content="#da532c">

    <!-- Schema.org WebSite Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "<?= WEBSITE_URL ?>/",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?= WEBSITE_URL ?>/search?keyword={keyword}",
                "query-input": "required name=keyword"
            }
        }
    </script>
    

        <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/styles.min.css?v=<?= $versionCss ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap">
    </noscript>
    <style>
	    .pizza{ margin: 2rem auto; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; padding: 1.5rem; border-radius: .6rem; text-align: center; color: #000; font-size: 16px; font-weight: 400; background-color: #FAACA8; background-image: linear-gradient(19deg, #FAACA8 0%, #DDD6F3 100%);}
	    .pizza a{ color: #000; font-weight: 500;text-shadow: 0 1px 0 #fff;}
	    .pizza a:hover{ text-shadow: 0 1px 3px #fff;}
	    .pizza-x{ max-width: 800px; width: calc(100% - 32px); min-height: 90px;}
	    .pizza-y{ max-width: 400px; min-height: 300px;}
	    .pizza .in-text{ font-size: 1.5em; font-weight: 600;}
	    .pizza .in-contact{ font-size: 1em;}
	    @media screen and (max-width: 479px){
	        .pizza{ font-size: 13px; gap: .6rem}
	        .pizza-y{ min-height: 200px;}
	    }
	</style>
</head>

<body>
    <div id="sidebar_menu_bg"></div>
    <div id="sidebar_menu">
        <button class="btn btn-radius btn-sm btn-secondary toggle-sidebar"><i class="fas fa-angle-left mr-2"></i>Close
            menu</button>
        <div class="sb-setting">
            <div class="header-setting">
                <div class="hs-toggles">
                    <a href="/most-popular" class="hst-item" data-toggle="tooltip" title="Popular Anime List">
                        <div class="hst-icon"><i class="fa-solid fa-fire"></i></div>
                        <div class="name"><span>Popular</span></div>
                    </a>
                    <a href="/random" class="hst-item" data-toggle="tooltip" title="Select Random Anime">
                        <div class="hst-icon"><i class="fas fa-random"></i></div>
                        <div class="name"><span>Random</span></div>
                    </a>
                    <div class="hst-item" data-bs-toggle="tooltip" title="Select language of anime name to display.">
                        <div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span>
                        </div>
                        <div class="name"><span>Name</span></div>
                    </div>
                    <a href="/news" class="hst-item" title="<?= $websiteTitle ?> News">
                    	<div class="hst-icon"><i class="fas fa-rss"></i></div>
                    	<div class="name"><span>News</span></div>
                	</a>
                </div>
            </div>
        </div>
        <div class="sb-donate">
            <div class="hr-community"><a class="btn btn-sm" href="/community/board"
                    title="<?= $websiteTitle ?> Connect"><i class="fas fa-comments mr-2"></i>Community</a></div>
        </div>
        <ul class="nav sidebar_menu-list">
            <li class="nav-item active"><a class="nav-link" href="/home" title="Home">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/subbed-anime" title="Subbed Anime">Subbed Anime</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/dubbed-anime" title="Dubbed Anime">Dubbed Anime</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/most-popular" title="Most Popular">Most Popular</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/movie" title="Movies">Movies</a></li>
            <li class="nav-item"><a class="nav-link" href="/tv" title="TV Series">TV Series</a></li>
            <li class="nav-item"><a class="nav-link" href="/ova" title="OVAs">OVAs</a></li>
            <li class="nav-item"><a class="nav-link" href="/ona" title="ONAs">ONAs</a></li>
            <li class="nav-item"><a class="nav-link" href="/special" title="Specials">Specials</a></li>
            <li class="nav-item"><a class="nav-link" href="/events" title="Events">Events</a></li>
            <li class="nav-item">
                <div class="nav-link" title="Genre"><strong>Genre</strong></div>
                <div class="sidebar_menu-sub show" id="sidebar_subs_genre">
                    <ul class="nav color-list sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/genre/action">Action</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/adventure">Adventure</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/cars">Cars</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/comedy">Comedy</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/dementia">Dementia</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/demons">Demons</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/drama">Drama</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/ecchi">Ecchi</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/fantasy">Fantasy</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/game">Game</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/harem">Harem</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/historical">Historical</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/horror">Horror</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/isekai">Isekai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/josei">Josei</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/kids">Kids</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/magic">Magic</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/marial-arts">Martial Arts</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/mecha">Mecha</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/military">Military</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/music">Music</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/mystery">Mystery</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/parody">Parody</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/police">Police</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/psychological">Psychological</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/romance">Romance</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/samurai">Samurai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/school">School</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/sci-fi">Sci-Fi</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/seinen">Seinen</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shoujo">Shoujo</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shoujo-ai">Shoujo Ai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shounen">Shounen</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shounen-ai">Shounen Ai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/slice-of-life">Slice of Life</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/space">Space</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/sports">Sports</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/super-power">Super Power</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/supernatural">Supernatural</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/thriller">Thriller</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/vampire">Vampire</a></li>
                        <li class="nav-item nav-more">
                            <a class="nav-link"><i class="fas fa-plus mr-2"></i>More</a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
    <div id="wrapper">
        <div id="header">
            <div class="container">
                <div id="mobile_menu"><i class="fa fa-bars"></i></div>
                <a href="/" id="logo">
                    <img src="<?= $websiteLogo ?>" alt="<?= $websiteTitle ?>" width="165" height="40" decoding="async">
                    <div class="clearfix"></div>
                </a>
                <div id="search">
                    <div class="search-content">
                        <form action="/search" autocomplete="off">
                            <a href="/filter" class="filter-icon">Filter</a>
                            <input type="text" class="form-control search-input" name="keyword"
                                placeholder="Search anime..." required>
                            <button type="submit" class="search-icon"><i class="fas fa-search"></i></button>
                        </form>
                        <div class="nav search-result-pop" id="search-suggest" style="display: none;">
                            <div class="loading-relative" id="search-loading" style="display: none;">
                                <div class="loading">
                                    <div class="span1"></div>
                                    <div class="span2"></div>
                                    <div class="span3"></div>
                                </div>
                            </div>
                            <div class="result" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="header-group">
                    <div class="anw-group">
                        <div class="zrg-title"><span class="top">Join now</span><span
                                class="bottom"><?= $websiteTitle ?>
                                Group</span></div>
                        <div class="zrg-list">
                            <div class="item"><a href="<?= $discord ?>" target="_blank"
                                    class="zr-social-button dc-btn"><i class="fa-brands fa-discord"></i></a></div>
                            <div class="item"><a href="<?= $telegram ?>" target="_blank"
                                    class="zr-social-button tl-btn"><i class="fab fa-telegram-plane"></i></a></div>
                            <div class="item"><a href="<?= $reddit ?>" target="_blank"
                                    class="zr-social-button rd-btn"><i class="fab fa-reddit-alien"></i></a></div>
                            <div class="item"><a href="<?= $instagram ?>" target="_blank"
                                    class="zr-social-button in-btn"><i class="fa-brands fa-instagram"></i></a></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="header-setting">
                    <div class="hs-toggles">
                        <a href="/completed" class="hst-item" data-toggle="tooltip" title="Completed Anime">
                            <div class="hst-icon"><i class="fa-solid fa-hourglass-end"></i></div>
                            <div class="name"><span>Completed</span></div>
                        </a>
                        <a href="/random" class="hst-item" data-toggle="tooltip" data-original-title="Watch random anime">
                    		<div class="hst-icon"><i class="fas fa-random"></i></div>
                    		<div class="name"><span>Random</span></div>
                		</a>
                		<div class="hst-item" data-toggle="tooltip" title="Select language of anime name to display.">
                    		<div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span></div>
                    		<div class="name"><span>Anime Name</span></div>
                		</div>
                		<a href="/news" class="hst-item" data-toggle="tooltip" title="News">
                    		<div class="hst-icon"><i class="fas fa-rss"></i></div>
                    		<div class="name"><span>News</span></div>
                		</a>
                        <a href="/community/board" class="hst-item" data-toggle="tooltip"
                            title="<?= $websiteTitle ?> Connect">
                            <div class="hst-icon"><i class="fas fa-comments"></i></div>
                            <div class="name"><span>Community</span></div>
                        </a>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div id="pick_menu">
                    <div class="pick_menu-ul">
                        <ul class="ulclear">
                            <li class="pmu-item pmu-item-home">
                                <a class="pmu-item-icon" href="/home" title="Home">
                                    <img src="/images/pick-home.svg" width="20" height="20" alt="Home" data-toggle="tooltip" data-placement="right"
                                        title="Home">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-movies">
                                <a class="pmu-item-icon" href="/movie" title="Movies">
                                    <img src="/images/pick-movies.svg" width="20" height="20" alt="Movies" data-toggle="tooltip" data-placement="right"
                                        title="Movies">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-show">
                                <a class="pmu-item-icon" href="/tv" title="TV Series">
                                    <img src="/images/pick-show.svg" width="20" height="20" alt="TV Series" data-toggle="tooltip" data-placement="right"
                                        title="TV Series">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-popular">
                                <a class="pmu-item-icon" href="/most-popular" title="Most Popular">
                                    <img src="/images/pick-popular.svg" width="20" height="20" alt="Most Popular" data-toggle="tooltip" data-placement="right"
                                        title="Most Popular">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="header_right"></div>
                <div id="mobile_search"><i class="fa fa-search"></i></div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div id="main-wrapper" class="layout-page layout-award">
            <div class="award-bg">
                <div class="award-bg-img" style="background-image: url('/images/community/cover.jpg');"></div>
            </div>
            <!--Begin: award-->
            <div class="container">
                <div class="award-inner">
                    <div class="ai_-tabs">
                        <a href="/community/super-x" class="active" title="Super X">Super X</a>
                        <a href="/community/board" class="" title="Z Community">Z Community</a>
                        <?php if (!empty($userId)): ?>
                        <a href="/community/my-zone" class="" title="My Zone">My Zone</a>
                        <?php endif; ?>
                    </div>
                    <div class="ai_-welcome text-center">
                        <div class="d-block">We are <?= $websiteTitle ?>ebie</div>
                        <div class="description pt-3">Enjoy the Anime and join our <?= $websiteTitle ?> Discussion Board
                            to be a part
                            of our
                            community
                        </div>
                    </div>
                    <div class="wideview d-flex justify-content-between">
                        <div class="wv wv-item wv-users">
                            <div class="wv-list">
                                <div class="wv-icon"><img src="/images/community/wv-01.png"></div>
                                <div class="wv-title d-flex align-items-center justify-content-between w-bg mb-1">
                                    <div class="name mr-3">Top Users</div>
                                    <ul class="nav nav-tabs pre-tabs aw-tabs mb-0 text-right">
                                        <li class="nav-item"><a data-toggle="tab" href="#user-top-day"
                                                class="nav-link active show">Today</a></li>
                                        <li class="nav-item"><a data-toggle="tab" href="#user-top-week"
                                                class="nav-link">Week</a>
                                        </li>
                                        <li class="nav-item"><a data-toggle="tab" href="#user-top-month"
                                                class="nav-link">Month</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane active show" id="user-top-day">
                                        <div class="wv-ul">
                                            <?php if ($topDay): ?>
                                            <?php $rank = 1; foreach ($topDay as $u): ?>
                                            <?php
                                                      	$id       = (int)$u['id'];
                                                        $username = e($u['username'] ?? 'Unknown');
                                                        $avatar   = e($u['image'] ?: '/public/images/no-avatar.jpeg');
                                                        $totalPts = (int)($u['points'] ?? 0);
                                                        $role     = (int)($u['roles'] ?? 0);
                                                        if ($role === 1) {
                                                          	$rankName   = 'Admin';
                                                            $badgeClass = 'level-b';
                                                        } elseif ($role === 2) {
                                                            $rankName   = 'Mod';
                                                           	$badgeClass = 'level-d';
                                                      	} else {
                                                          	list($rankName, $badgeClass) = getRank($totalPts);
                                                     	}
                                                      	if ($role === 1) {
                                                           	$userNameClasses = 'user-name is-level-x is-level-b';
                                                       	} elseif ($role === 2) {
                                                          	$userNameClasses = 'user-name is-level-x is-level-d';
                                                    	} else {
                                                          	$userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                                                    	}
                                                   	?>
                                            <div
                                                class="item item-user d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf('%02d', $rank) ?></div>
                                                <div class="profile-avatar">
                                                    <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                                </div>
                                                <div class="item-inner">
                                                    <a href="/community/user/<?= $id ?>" target="_blank"
                                                        class="<?= $userNameClasses ?>">
                                                        <?php if (!empty($badgeClass)): ?>
                                                        <i class="badg-level <?= e($badgeClass) ?>"></i>
                                                        <?php endif; ?>
                                                        <?= $username ?>
                                                    </a>
                                                    <?php if (!empty($rankName)): ?>
                                                    <div class="extra">
                                                        <div class="user-name is-level-x">
                                                            <span><?= e($rankName) ?></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="uts d-flex justify-content-between align-items-center">
                                                        <div class="ots d-flex align-items-center">
                                                            <div title="ZRC Coin">
                                                                <i class="zrc-icon mr-1"></i>
                                                                <?= format_points($u['earned_points']) ?>
                                                            </div>
                                                            <div title="Profile" class="ml-3">
                                                                <a href="/community/user/<?= $id ?>">
                                                                    View profile<i
                                                                        class="fas fa-arrow-circle-right ml-2"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; ?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="user-top-week">
                                        <div class="wv-ul">
                                            <?php if ($topWeek): ?>
                                            <?php $rank = 1; foreach ($topWeek as $u): ?>
                                            <?php
                                                      	$id       = (int)$u['id'];
                                                        $username = e($u['username'] ?? 'Unknown');
                                                        $avatar   = e($u['image'] ?: '/public/images/no-avatar.jpeg');
                                                        $totalPts = (int)($u['points'] ?? 0);
                                                        $role     = (int)($u['roles'] ?? 0);
                                                        if ($role === 1) {
                                                          	$rankName   = 'Admin';
                                                            $badgeClass = 'level-b';
                                                        } elseif ($role === 2) {
                                                            $rankName   = 'Mod';
                                                           	$badgeClass = 'level-d';
                                                      	} else {
                                                          	list($rankName, $badgeClass) = getRank($totalPts);
                                                     	}
                                                      	if ($role === 1) {
                                                           	$userNameClasses = 'user-name is-level-x is-level-b';
                                                       	} elseif ($role === 2) {
                                                          	$userNameClasses = 'user-name is-level-x is-level-d';
                                                    	} else {
                                                          	$userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                                                    	}
                                                   	?>
                                            <div
                                                class="item item-user d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf('%02d', $rank) ?></div>
                                                <div class="profile-avatar">
                                                    <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                                </div>
                                                <div class="item-inner">
                                                    <a href="/community/user/<?= $id ?>" target="_blank"
                                                        class="<?= $userNameClasses ?>">
                                                        <?php if (!empty($badgeClass)): ?>
                                                        <i class="badg-level <?= e($badgeClass) ?>"></i>
                                                        <?php endif; ?>
                                                        <?= $username ?>
                                                    </a>
                                                    <?php if (!empty($rankName)): ?>
                                                    <div class="extra">
                                                        <div class="user-name is-level-x">
                                                            <span><?= e($rankName) ?></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="uts d-flex justify-content-between align-items-center">
                                                        <div class="ots d-flex align-items-center">
                                                            <div title="ZRC Coin">
                                                                <i class="zrc-icon mr-1"></i>
                                                                <?= format_points($u['earned_points']) ?>
                                                            </div>
                                                            <div title="Profile" class="ml-3">
                                                                <a href="/community/user/<?= $id ?>">
                                                                    View profile<i
                                                                        class="fas fa-arrow-circle-right ml-2"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; ?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="user-top-month">
                                        <div class="wv-ul">
                                            <?php if ($topMonth): ?>
                                            <?php $rank = 1; foreach ($topMonth as $u): ?>
                                            <?php
                                                      	$id       = (int)$u['id'];
                                                        $username = e($u['username'] ?? 'Unknown');
                                                        $avatar   = e($u['image'] ?: '/public/images/no-avatar.jpeg');
                                                        $totalPts = (int)($u['points'] ?? 0);
                                                        $role     = (int)($u['roles'] ?? 0);
                                                        if ($role === 1) {
                                                          	$rankName   = 'Admin';
                                                            $badgeClass = 'level-b';
                                                        } elseif ($role === 2) {
                                                            $rankName   = 'Mod';
                                                           	$badgeClass = 'level-d';
                                                      	} else {
                                                          	list($rankName, $badgeClass) = getRank($totalPts);
                                                     	}
                                                      	if ($role === 1) {
                                                           	$userNameClasses = 'user-name is-level-x is-level-b';
                                                       	} elseif ($role === 2) {
                                                          	$userNameClasses = 'user-name is-level-x is-level-d';
                                                    	} else {
                                                          	$userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                                                    	}
                                                   	?>
                                            <div
                                                class="item item-user d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf('%02d', $rank) ?></div>
                                                <div class="profile-avatar">
                                                    <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                                </div>
                                                <div class="item-inner">
                                                    <a href="/community/user/<?= $id ?>" target="_blank"
                                                        class="<?= $userNameClasses ?>">
                                                        <?php if (!empty($badgeClass)): ?>
                                                        <i class="badg-level <?= e($badgeClass) ?>"></i>
                                                        <?php endif; ?>
                                                        <?= $username ?>
                                                    </a>
                                                    <?php if (!empty($rankName)): ?>
                                                    <div class="extra">
                                                        <div class="user-name is-level-x">
                                                            <span><?= e($rankName) ?></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="uts d-flex justify-content-between align-items-center">
                                                        <div class="ots d-flex align-items-center">
                                                            <div title="ZRC Coin">
                                                                <i class="zrc-icon mr-1"></i>
                                                                <?= format_points($u['earned_points']) ?>
                                                            </div>
                                                            <div title="Profile" class="ml-3">
                                                                <a href="/community/user/<?= $id ?>">
                                                                    View profile<i
                                                                        class="fas fa-arrow-circle-right ml-2"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; ?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wv wv-item">
                            <div class="wv-list">
                                <div class="wv-icon"><img src="/images/community/wv-02.png"></div>
                                <div class="wv-title d-flex align-items-center justify-content-between w-bg mb-1">
                                    <div class="name mr-3">Top Comments</div>
                                    <ul class="nav nav-tabs pre-tabs aw-tabs mb-0 text-right">
                                        <li class="nav-item"><a data-toggle="tab" href="#comments-top"
                                                class="nav-link active show">Anime</a></li>
                                        <li class="nav-item"><a data-toggle="tab" href="#postComments-top"
                                                class="nav-link">Community</a></li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane active show" id="comments-top">
                                        <div class="wv-ul">
                                            <?php if ($topEpisodeComments): ?>
                                            <?php $rank = 1; foreach ($topEpisodeComments as $row): ?>
                                            <?php
                                            	$cid      = (int)$row['id'];
                        						$uid      = (int)$row['user_id'];
                        						$username = e($row['username'] ?? 'Unknown');
                        						$avatar   = e($row['image'] ?: '/public/images/no-avatar.jpeg');
                        						$content  = e($row['content'] ?? '');
                        						$movieId  = e($row['movie_id'] ?? '');
                        						$episodeId= (int)($row['episode_id'] ?? 0);
                        						$replies  = (int)($row['reply_count'] ?? 0);
                        						$up       = (int)($row['upvotes'] ?? 0);
                        						$down     = (int)($row['downvotes'] ?? 0);
                        						$points   = (int)($row['points'] ?? 0);
                        						$role     = (int)($row['roles'] ?? 0);
                        						// Fake anime title from slug if you don't have table
                        						$animeTitle = ucwords(str_replace('-', ' ', preg_replace('~-\d+$~', '', $movieId)));
                        						// rank / badge like before
                        						if ($role === 1) {
                        						    $rankName   = 'Admin';
                        						    $badgeClass = 'level-b';
                        						} elseif ($role === 2) {
                        						    $rankName   = 'Mod';
                        						    $badgeClass = 'level-d';
                        						} else {
                        						    list($rankName, $badgeClass) = getRank($points);
                        						}
                        						if ($role === 1) {
                        						    $userNameClasses = 'user-name is-level-x is-level-b';
                        						} elseif ($role === 2) {
                        						    $userNameClasses = 'user-name is-level-x is-level-d';
                        						} else {
                        						    $userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                        						}
                        						$commentUrl = "/watch/{$movieId}?ep={$episodeId}&c_id={$cid}&c_type=episode";
                    						?>
                                            <div
                                                class="item item-comment d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf('%02d', $rank) ?></div>
                                                <div class="profile-avatar">
                                                    <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                                </div>
                                                <div class="item-inner">
                                                    <div class="cn-owner mb-2">
                                                        <a href="/community/user/<?= $uid ?>" target="_blank"
                                                            class="<?= $userNameClasses ?>">
                                                            <?php if (!empty($badgeClass)): ?>
                                                            <i class="badg-level <?= e($badgeClass) ?>"></i>
                                                            <?php endif; ?>
                                                            <?= $username ?>
                                                            <?php if (!empty($rankName) && $role === 0): ?>
                                                            <span><?= e($rankName) ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                    <div class="subject">
                                                        <div><?= $content ?></div>
                                                    </div>
                                                    <div class="sts d-flex justify-content-between align-items-center">
                                                        <a href="<?= $commentUrl ?>" target="_blank" class="anime-name"
                                                            title="<?= e($animeTitle) ?>"><?= e($animeTitle) ?></a>
                                                        <div class="ots d-flex text-right">
                                                            <div class="ml-3"><i
                                                                    class="fas fa-reply mr-1"></i><?= $replies ?></div>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-thumbs-up mr-1"></i><?= $up ?></div>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-thumbs-down mr-1"></i><?= $down ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; ?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="postComments-top">
                                        <div class="wv-ul">
                                            <?php if ($topPostComments): ?>
                                            <?php $rank = 1; foreach ($topPostComments as $row): ?>
                                            <?php
                                            	$cid      = (int)$row['id'];
                                            	$uid      = (int)$row['user_id'];
                                            	$username = e($row['username'] ?? 'Unknown');
                                            	$avatar   = e($row['image'] ?: '/public/images/no-avatar.jpeg');
                                            	$content  = e($row['content'] ?? '');
                                            	$postSlug = e($row['post_slug'] ?? '');
                                            	$postTitle= e($row['post_title'] ?? 'Community Post');
                                            	$replies  = (int)($row['reply_count'] ?? 0);
                                            	$up       = (int)($row['upvotes'] ?? 0);
                                            	$down     = (int)($row['downvotes'] ?? 0);
                                            	$points   = (int)($row['points'] ?? 0);
                                            	$role     = (int)($row['roles'] ?? 0);
                                            	if ($role === 1) {
                                            	    $rankName   = 'Admin';
                                            	    $badgeClass = 'level-b';
                                            	} elseif ($role === 2) {
                                            	    $rankName   = 'Mod';
                                            	    $badgeClass = 'level-d';
                                            	} else {
                                            	    list($rankName, $badgeClass) = getRank($points);
                                            	}
                                            	if ($role === 1) {
                                            	    $userNameClasses = 'user-name is-level-x is-level-b';
                                            	} elseif ($role === 2) {
                                            	    $userNameClasses = 'user-name is-level-x is-level-d';
                                            	} else {
                                            	    $userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                                            	}
                                            	$commentUrl = "/community/post/{$postSlug}?c_id={$cid}";
                                        	?>
                                            <div
                                                class="item item-comment d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf('%02d', $rank) ?></div>
                                                <div class="profile-avatar">
                                                    <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                                </div>
                                                <div class="item-inner">
                                                    <div class="cn-owner mb-2">
                                                        <a href="/community/user/<?= $uid ?>" target="_blank"
                                                            class="<?= $userNameClasses ?>">
                                                            <?php if (!empty($badgeClass)): ?>
                                                            <i class="badg-level <?= e($badgeClass) ?>"></i>
                                                            <?php endif; ?>
                                                            <?= $username ?>
                                                            <?php if (!empty($rankName) && $role === 0): ?>
                                                            <span><?= e($rankName) ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                    <div class="subject">
                                                        <div><?= $content ?></div>
                                                    </div>
                                                    <div class="sts d-flex justify-content-between align-items-center">
                                                        <a href="<?= $commentUrl ?>" target="_blank" class="anime-name"
                                                            title="<?= $postTitle ?>"><?= $postTitle ?></a>
                                                        <div class="ots d-flex text-right">
                                                            <div class="ml-3"><i
                                                                    class="fas fa-reply mr-1"></i><?= $replies ?></div>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-thumbs-up mr-1"></i><?= $up ?></div>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-thumbs-down mr-1"></i><?= $down ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; ?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="wv-loadmore">
                                    <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load more</a>
                                </div>
                            </div>
                        </div>
                        <div class="wv wv-item">
                            <div class="wv-list">
                                <div class="wv-icon"><img src="/images/community/wv-03.png"></div>
                                <div class="wv-title d-flex align-items-center justify-content-between w-bg mb-1">
                                    <div class="name mr-3">Top Discussions</div>
                                    <ul class="nav nav-tabs pre-tabs aw-tabs mb-0 text-right">
                                        <li class="nav-item"><a data-toggle="tab" href="#movie-top-day"
                                                class="nav-link active show">Today</a></li>
                                        <li class="nav-item"><a data-toggle="tab" href="#movie-top-week"
                                                class="nav-link">Week</a>
                                        </li>
                                        <li class="nav-item"><a data-toggle="tab" href="#movie-top-month"
                                                class="nav-link">Month</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane active show" id="movie-top-day">
                                        <div class="wv-ul">
                                            <?php $rank = 1; foreach ($topDayAnime as $item):
                                    			$animeId  = $item['anime_id'];
                                    			$title    = e($item['title']);
                                    			$poster   = e($item['poster'] ?: '/public/images/no-poster.png');
                                    			$count    = (int)$item['comment_count'];
                                    			$suffix   = $item['period_suffix'];
                                    			$score    = $item['mal_score'];
                                    			$members  = (int)$item['mal_members'];
                                    			$scoreText   = $score !== null ? number_format($score, 2) : '';
                                    			$bookmarkNum = $members ? format_short_number($members) : '0';
                                			?>
                                            <div
                                                class="item item-discussion d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf("%02d", $rank) ?></div>
                                                <a href="/<?= $animeId ?>" class="item-poster">
                                                    <img src="<?= $poster ?>" class="item-poster-img"
                                                        alt="<?= $title ?>">
                                                </a>
                                                <div class="item-inner">
                                                    <h4 class="item-name">
                                                        <a href="/watch/<?= $animeId ?>" target="_blank"
                                                            title="<?= $title ?>">
                                                            <?= $title ?>
                                                        </a>
                                                    </h4>
                                                    <div class="sts d-flex justify-content-between align-items-center">
                                                        <div class="percent">~ <?= $count ?> com / <?= $suffix ?></div>
                                                        <div class="ots d-flex text-right">
                                                            <?php if ($score !== null): ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-star mr-1"></i><?= $scoreText ?></div>
                                                            <?php endif; ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-bookmark mr-1"></i><?= $bookmarkNum ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; endforeach; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="movie-top-week">
                                        <div class="wv-ul">
                                            <?php $rank = 1; foreach ($topWeekAnime as $item):
                                            	$animeId  = $item['anime_id'];
                                            	$title    = e($item['title']);
                                            	$poster   = e($item['poster'] ?: '/public/images/no-poster.png');
                                            	$count    = (int)$item['comment_count'];
                                            	$suffix   = $item['period_suffix'];
                                            	$score    = $item['mal_score'];
                                            	$members  = (int)$item['mal_members'];
                                            	$scoreText   = $score !== null ? number_format($score, 2) : '';
                                            	$bookmarkNum = $members ? format_short_number($members) : '0';
                                        	?>
                                            <div
                                                class="item item-discussion d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf("%02d", $rank) ?></div>
                                                <a href="/<?= $animeId ?>" class="item-poster">
                                                    <img src="<?= $poster ?>" class="item-poster-img"
                                                        alt="<?= $title ?>">
                                                </a>
                                                <div class="item-inner">
                                                    <h4 class="item-name">
                                                        <a href="/watch/<?= $animeId ?>" target="_blank"
                                                            title="<?= $title ?>">
                                                            <?= $title ?>
                                                        </a>
                                                    </h4>
                                                    <div class="sts d-flex justify-content-between align-items-center">
                                                        <div class="percent">~ <?= $count ?> com / <?= $suffix ?></div>
                                                        <div class="ots d-flex text-right">
                                                            <?php if ($score !== null): ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-star mr-1"></i><?= $scoreText ?></div>
                                                            <?php endif; ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-bookmark mr-1"></i><?= $bookmarkNum ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; endforeach; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="movie-top-month">
                                        <div class="wv-ul">
                                            <?php $rank = 1; foreach ($topMonthAnime as $item):
                								$animeId  = $item['anime_id'];
                								$title    = e($item['title']);
           								     	$poster   = e($item['poster'] ?: '/public/images/no-poster.png');
             								   	$count    = (int)$item['comment_count'];
          								      	$suffix   = $item['period_suffix'];
            								    $score    = $item['mal_score'];
         								       	$members  = (int)$item['mal_members'];
        								        $scoreText   = $score !== null ? number_format($score, 2) : '';
        								        $bookmarkNum = $members ? format_short_number($members) : '0';
        									?>
                                            <div
                                                class="item item-discussion d-flex align-items-center justify-content-between">
                                                <div class="number"><?= sprintf("%02d", $rank) ?></div>
                                                <a href="/<?= $animeId ?>" class="item-poster">
                                                    <img src="<?= $poster ?>" class="item-poster-img"
                                                        alt="<?= $title ?>">
                                                </a>
                                                <div class="item-inner">
                                                    <h4 class="item-name">
                                                        <a href="/watch/<?= $animeId ?>" target="_blank"
                                                            title="<?= $title ?>">
                                                            <?= $title ?>
                                                        </a>
                                                    </h4>
                                                    <div class="sts d-flex justify-content-between align-items-center">
                                                        <div class="percent">~ <?= $count ?> com / <?= $suffix ?></div>
                                                        <div class="ots d-flex text-right">
                                                            <?php if ($score !== null): ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-star mr-1"></i><?= $scoreText ?></div>
                                                            <?php endif; ?>
                                                            <div class="ml-3"><i
                                                                    class="fas fa-bookmark mr-1"></i><?= $bookmarkNum ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $rank++; endforeach; ?>
                                        </div>
                                        <div class="wv-loadmore">
                                            <a class="btn btn-sm btn-block"><i class="fas fa-arrow-down mr-2"></i>Load
                                                more</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/End: award-->
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type="text/javascript"
        src="https://platform-api.sharethis.com/js/sharethis.js#property=64a3d891df473b0019d1b0da&product=inline-share-buttons&source=platform"
        async="async"></script>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"
        integrity="sha384-/KNQL8Nu5gCHLqwqfQjA689Hhoqgi2S84SNUxC3roTe4EhJ9AfLkp8QiQcU8AMzI" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script>
    $(".wv-loadmore").click(function(e) {
        $(this).parent().find(".wv-ul").addClass("expand");
        $(this).hide();
    });
    </script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
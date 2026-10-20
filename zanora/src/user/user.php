<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once('src/component/anime/qtip.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php'); // ✅ Central rank system
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get user ID from URL
$userId = isset($_GET['slug']) ? intval($_GET['slug']) : 0;
if ($userId <= 0) {
    header("Location: /404.php");
    exit;
}
// Fetch user info
$stmt = $conn->prepare("SELECT id, username, public_watch_list, image, is_verified, created_at, points, roles, is_blocked FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    header("Location: /404.php");
    exit;
}
// Utility function for short numbers
function shortenNumber($num) {
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 1000) return round($num / 1000) . 'K';
    return $num;
}
$userRole = (int)$user['roles']; // 0,1,2
$isAdmin  = ($userRole === 1);
$isMod    = ($userRole === 2);
$isBlocked = !empty($user['is_blocked']) && (int)$user['is_blocked'] === 1;
// ✅ Use central rank.php
list($rankName, $badgeClass, $rankIcon, $requiredPoints, $minPoints, $maxPoints) = getRank($user['points']);
// Rank progress
if ($maxPoints === PHP_INT_MAX) {
    // Top rank => always show 100%
    $rankProgress = 100;
} else {
    $rankProgress = (($user['points'] - $minPoints) / ($maxPoints - $minPoints)) * 100;
    $rankProgress = max(0, min(100, $rankProgress)); // clamp between 0-100
}
// Power formatting
$fullPoints  = number_format($user['points']);
$shortPoints = shortenNumber($user['points']);
// Fetch watchlist
$watchlist = $conn->prepare("SELECT * FROM watchlist WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$watchlist->bind_param("i", $userId);
$watchlist->execute();
$watchlistData = $watchlist->get_result();
$watchlist->close();
// Fetch latest activity (comments)
$comments = $conn->prepare("SELECT * FROM comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$comments->bind_param("i", $userId);
$comments->execute();
$commentData = $comments->get_result();
$comments->close();

// Fetch current live watching status (active in the last 30 seconds)
$liveWatching = null;
$livePoster = '/images/no_poster.jpg';
$liveTitle = 'Anime';
$livePercent = 0;
$liveProgress = 0;
$liveDuration = 0;

$stmtLive = $conn->prepare("
    SELECT lw.*, ta.title, ta.poster 
    FROM live_watching lw 
    INNER JOIN users u ON lw.user_id = u.id
    LEFT JOIN tracked_anime ta ON lw.anime_id = ta.anime_id 
    WHERE lw.user_id = ? AND u.live_status = 1 AND lw.updated_at >= NOW() - INTERVAL 30 SECOND
    LIMIT 1
");
if ($stmtLive) {
    $stmtLive->bind_param("i", $userId);
    $stmtLive->execute();
    $liveWatching = $stmtLive->get_result()->fetch_assoc();
    $stmtLive->close();

    if ($liveWatching) {
        $liveTitle = !empty($liveWatching['title']) ? $liveWatching['title'] : ucwords(str_replace('-', ' ', $liveWatching['anime_id']));
        $livePoster = !empty($liveWatching['poster']) ? $liveWatching['poster'] : '/images/no_poster.jpg';
        $liveProgress = (int)$liveWatching['progress'];
        $liveDuration = (int)$liveWatching['duration'];
        $livePercent = $liveDuration > 0 ? ($liveProgress / $liveDuration) * 100 : 0;
    }
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($user['username']) ?>'s Profile - <?= $websiteTitle ?></title>
    <meta name="description" content="View <?= htmlspecialchars($user['username']) ?>'s profile, points, achievements, and anime updates on <?= $websiteTitle ?>.">
    <meta name="keywords" content="<?= htmlspecialchars($user['username']) ?> profile, user profile, anime user, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/community/user/<?= htmlspecialchars($user['id']) ?>" />

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
    <meta property="og:type" content="profile" />
    <meta property="og:url" content="<?= WEBSITE_URL ?>/community/user/<?= htmlspecialchars($user['id']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($user['username']) ?>'s Profile - <?= $websiteTitle ?>">
    <meta property="og:description" content="View <?= htmlspecialchars($user['username']) ?>'s profile, points, achievements, and anime updates on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= htmlspecialchars($user['image'] ?: $banner) ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($user['username']) ?>'s Profile - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="View <?= htmlspecialchars($user['username']) ?>'s profile, points, achievements, and anime updates on <?= $websiteTitle ?>.">
    <meta name="twitter:image" content="<?= htmlspecialchars($user['image'] ?: $banner) ?>">

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
        /* Live watch card animations */
        @keyframes pulseDot {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.25); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 10px 35px rgba(88, 101, 242, 0.12); border-color: rgba(88, 101, 242, 0.15); }
            50% { box-shadow: 0 10px 45px rgba(88, 101, 242, 0.25); border-color: rgba(88, 101, 242, 0.35); }
            100% { box-shadow: 0 10px 35px rgba(88, 101, 242, 0.12); border-color: rgba(88, 101, 242, 0.15); }
        }
        @keyframes rotateGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
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
    <!--End: Stylesheet-->
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
                <div class="award-bg-img" style="background-image: url('<?= htmlspecialchars($user['image']) ?>');">
                </div>
            </div>
            <div class="container">
                <div class="award-inner">
                    <div class="ai_-welcome text-center">
                        Hi! I'm <span><?= htmlspecialchars($user['username']) ?></span>
                        <?php if ($user['is_verified']) { ?>
                        <div class="d-block d-verify">
                            <div class="span-v is-yes"><i class="fas fa-user-check mr-2"></i>Verified</div>
                        </div>
                        <?php } else { ?>
                        <div class="d-block d-verify">
                            <div class="span-v is-no"><i class="fas fa-user-alt-slash mr-2"></i>Not Verified</div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="my-award d-flex justify-content-between">
                        <div class="maw-left">
                            <div class="ai_-about d-flex">
                                <div class="aa-item aa-avatar">
                                    <div class="profile-avatar d-inline-block <?= $isBlocked ? 'is-blocked' : '' ?>">
                                        <img src="<?= htmlspecialchars($user['image']) ?>"
                                            alt="<?= htmlspecialchars($user['username']) ?>">
                                    </div>
                                </div>
                                <div class="aa-item">
                                    <div class="aa-title <?= $isBlocked ? 'is-blocked' : '' ?>">
                                        <?php if ($isAdmin) { ?>
                                        <a href="/community/user/<?= $user['id'] ?>"
                                            class="user-name is-level-x is-level-b">
                                            <i class="badg-level level-b"></i>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </a>
                                        <?php } elseif ($isMod) { ?>
                                        <a href="/community/user/<?= $user['id'] ?>"
                                            class="user-name is-level-x is-level-d">
                                            <i class="badg-level level-d"></i>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </a>
                                        <?php } else { ?>
                                        <a href="/community/user/<?= $user['id'] ?>"
                                            class="user-name is-level-x <?= $badgeClass ? 'is-level-a' : '' ?>">
                                            <?php if ($badgeClass) { ?>
                                            <i class="badg-level <?= $badgeClass ?>"></i>
                                            <?php } ?>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </a>
                                        <?php } ?>
                                    </div>
                                    <div class="aa-result">
                                        <div class="user-name is-level-x">
                                            <span>
                                                <?php
                                                if ($isAdmin) echo 'Admin';
                                                elseif ($isMod) echo 'Mod';
                                                else echo 'Member';
                                            ?>
                                            </span>
                                        </div>
                                    </div>
                                    <small>Joined: <?= date("Y-m-d", strtotime($user['created_at'])) ?></small>
                                </div>
                                <div class="aa-item">
                                    <!-- ✅ Power with tooltip + short number -->
                                    <div class="aa-title d-flex align-items-center" data-toggle="tooltip"
                                        data-placement="top" data-original-title="<?= $fullPoints ?> ZRC">
                                        Power: <i class="zrc-icon mr-1 ml-2"></i> <?= $shortPoints ?>
                                    </div>
                                    <!-- ✅ Progress between thresholds -->
                                    <div class="aa-result d-flex align-items-center">
                                        <div class="aa-progress"><span style="width: <?= $rankProgress ?>%;"></span>
                                        </div>
                                        <div class="percent ml-2"><?= round($rankProgress, 2) ?>%</div>
                                    </div>
                                    <small data-toggle="modal" style="cursor: pointer;" data-target="#modalzrc">Earning History</small>
                                </div>
                            </div>

                            <!-- Rank Progression Line -->
                            <div class="role-line mt-4">
                                <div class="role-line-wrap d-flex justify-content-between">
                                    <div class="rlw-point rlw-01">
                                        <div class="rlw-title">New</div>
                                        <div class="desc">Let's Go</div>
                                        <div class="rlw-icon"><img src="/images/level-a-0.png" alt="New"></div>
                                    </div>
                                    <div class="rlw-point rlw-02">
                                        <div class="rlw-title">Angelfish</div>
                                        <div class="desc"><i class="zrc-icon mr-1"></i> 60K ZRC</div>
                                        <div class="rlw-icon"><img src="/images/level-a.png" alt="Angelfish">
                                        </div>
                                    </div>
                                    <div class="rlw-point rlw-03">
                                        <div class="rlw-title">Crab</div>
                                        <div class="desc"><i class="zrc-icon mr-1"></i> 120K ZRC</div>
                                        <div class="rlw-icon"><img src="/images/level-a-2.png" alt="Crab"></div>
                                    </div>
                                    <div class="rlw-point rlw-04">
                                        <div class="rlw-title">Starfish</div>
                                        <div class="desc"><i class="zrc-icon mr-1"></i> 180K ZRC</div>
                                        <div class="rlw-icon"><img src="/images/level-a-3.png" alt="Starfish">
                                        </div>
                                    </div>
                                    <div class="rlw-point rlw-05">
                                        <div class="rlw-title">Dolphin</div>
                                        <div class="desc"><i class="zrc-icon mr-1"></i> 240K ZRC</div>
                                        <div class="rlw-icon"><img src="/images/level-a-4.png" alt="Dolphin">
                                        </div>
                                    </div>
                                </div>
                                <?php
                            // Progress calculation
                            $maxPoints = 240000; // top threshold
                            $progress = min(100, ($user['points'] / $maxPoints) * 100);
                        ?>
                                <div class="role-line-load">
                                    <div class="loaded" style="width: <?= $progress ?>%;">
                                        <div class="profile-avatar">
                                            <img src="<?= htmlspecialchars($user['image']) ?>"
                                                alt="<?= htmlspecialchars($user['username']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Live Watch Card (Spotify-like Activity Status) -->
                            <div id="live-watch-wrapper">
                                <?php if ($liveWatching): ?>
                                <div class="live-watch-card" data-progress="<?= $liveProgress ?>" data-duration="<?= $liveDuration ?>" data-anime-id="<?= htmlspecialchars($liveWatching['anime_id']) ?>" data-episode-id="<?= htmlspecialchars($liveWatching['episode_id']) ?>" style="background: rgba(88, 101, 242, 0.08); border: 1px solid rgba(88, 101, 242, 0.2); border-radius: 20px; padding: 25px; margin-top: 25px; margin-bottom: 25px; display: flex; gap: 20px; align-items: center; box-shadow: 0 10px 35px rgba(88, 101, 242, 0.15); animation: pulseGlow 3s infinite ease-in-out; position: relative; overflow: hidden; font-family: 'Outfit', sans-serif;">
                                    
                                    <div class="glow-wave" style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(88,101,242,0.1) 0%, transparent 60%); pointer-events: none; animation: rotateGlow 15s linear infinite;"></div>

                                    <div class="lw-poster" style="width: 75px; height: 110px; border-radius: 12px; overflow: hidden; border: 2px solid #5865F2; box-shadow: 0 5px 15px rgba(0,0,0,0.3); flex-shrink: 0; position: relative; z-index: 2;">
                                        <img src="<?= htmlspecialchars($livePoster) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Anime Poster">
                                        <div class="play-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center;">
                                            <i class="fas fa-play" style="color: #5865F2; font-size: 18px; animation: pulseDot 1.5s infinite;"></i>
                                        </div>
                                    </div>

                                    <div class="lw-info" style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; position: relative; z-index: 2;">
                                        <div class="lw-badge" style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #5865F2; letter-spacing: 1.5px; margin-bottom: 5px; display: flex; align-items: center; gap: 6px;">
                                            <span class="pulse-dot" style="width: 7px; height: 7px; background-color: #5865F2; border-radius: 50%; display: inline-block; animation: pulseDot 1.5s infinite;"></span>
                                            Currently Watching
                                        </div>
                                        <h3 class="lw-title" style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($liveTitle) ?></h3>
                                        <div class="lw-episode" style="font-size: 13.5px; font-weight: 600; color: #aaa; margin-bottom: 12px;">
                                            Episode <?= htmlspecialchars($liveWatching['episode_number']) ?>
                                        </div>

                                        <div class="lw-progress-container" style="width: 100%; display: flex; flex-direction: column; gap: 6px;">
                                            <div class="lw-bar" style="width: 100%; height: 5px; background: rgba(255,255,255,0.08); border-radius: 10px; overflow: hidden;">
                                                <div class="lw-fill" style="width: <?= $livePercent ?>%; height: 100%; background: linear-gradient(90deg, #5865F2, #a370f7); border-radius: 10px; transition: width 0.5s ease;"></div>
                                            </div>
                                            <div class="lw-timer" style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #777; font-family: monospace;">
                                                <span class="lw-elapsed"><?= sprintf('%02d:%02d', floor($liveProgress / 60), $liveProgress % 60) ?></span>
                                                <span class="lw-total"><?= sprintf('%02d:%02d', floor($liveDuration / 60), $liveDuration % 60) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lw-action" style="flex-shrink: 0; position: relative; z-index: 2;">
                                        <a href="/watch/<?= htmlspecialchars($liveWatching['anime_id']) ?>?ep=<?= htmlspecialchars($liveWatching['episode_id']) ?>" class="btn btn-primary" style="background: linear-gradient(135deg, #5865F2, #4752c4); border: none; color: #fff; font-weight: 700; font-size: 12px; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(88,101,242,0.3); transition: transform 0.2s; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="fas fa-play mr-1"></i> Watch Along
                                        </a>
                                    </div>

                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Watchlist -->
                            <div class="block_area block_area_home my-watchlist">
                                <div class="block_area-header">
                                    <div class="float-left bah-heading mr-4">
                                        <h2 class="cat-heading">Watch List</h2>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php $hasqtipCounter = 0; ?>
                                <?php if ($user['public_watch_list']) { ?>
                                <?php if ($watchlistData->num_rows > 0) { ?>
                                <div class="block_area-content block_area-list film_list film_list-grid">
                                    <div class="film_list-wrap">
                                        <?php while($anime = $watchlistData->fetch_assoc()) {
                                            $animeData = fetchAnimeData($anime['anime_id']);
                                            if (!$animeData) continue; ?>
                                        <div class="flw-item">
                                            <div class="film-poster">
                                                <img loading="lazy" decoding="async" class="film-poster-img lazyload"
                                                    src="<?php echo htmlspecialchars($animeData['poster'] ?? '/path/to/default-image.jpg'); ?>"
                                                    alt="<?php echo htmlspecialchars($animeData['title'] ?? 'Unknown Title'); ?>">
                                                <a class="film-poster-ahref item-qtip"
                                                    href="<?php echo $websiteUrl; ?>/watch/<?php echo htmlspecialchars($anime['anime_id'] ?? ''); ?>"
                                                    data-id="<?php echo htmlspecialchars($animeData['data_id'] ?? 'Unknown Title'); ?>"
                                                    data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                    aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"
                                                    title="<?php echo htmlspecialchars($animeData['title'] ?? 'Unknown Title'); ?>">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            </div>
                                            <div class="film-detail">
                                                <h3 class="film-name">
                                                    <a href="/<?= htmlspecialchars($anime['anime_id']); ?>"
                                                        title="<?= htmlspecialchars($animeData['title'] ?? $animeData['japa']) ?>">
                                                        <span class="dynamic-name"
                                                            data-title="<?= htmlspecialchars($animeData['title']) ?>"
                                                            data-jname="<?= htmlspecialchars($animeData['japa']) ?>">
                                                            <?= htmlspecialchars($animeData['title']) ?>
                                                        </span>
                                                    </a>
                                                </h3>
                                                <div class="fd-infor">
                                                    <span
                                                        class="fdi-item"><?= htmlspecialchars($animeData['showType']) ?></span>
                                                    <span class="dot"></span>
                                                    <span
                                                        class="fdi-item fdi-duration"><?= htmlspecialchars($animeData['duration']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="py-3">
                                      	<a href="/community/user/<?= $user['id'] ?>/watch-list" class="btn sbtn btn-block btn-secondary">View more</a>
                                  	</div>
                                </div>
                                <?php } else { ?>
                                <div class="d-block p-5 text-center notpublic">Watchlist is empty.</div>
                                <?php } ?>
                                <?php } else { ?>
                                <div class="d-block p-5 text-center notpublic">Watchlist is not public.</div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- Latest Activities -->
                        <div class="maw-right"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script async
        src="https://platform-api.sharethis.com/js/sharethis.js#property=64a3d891df473b0019d1b0da&product=inline-share-buttons&source=platform">
    </script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js?v=<?= $versionJs2 ?>');
        });
    }
    $('.anime-request-link').click(function (e) {
        e.preventDefault();
        if (isLoggedIn) {
            window.location.href = $(this).attr('href');
        } else {
            $('#modallogin').modal('show');
        }
    });

    // Real-time ticking Spotify-style progress bar with AJAX polling for Live Watching Status
    $(document).ready(function() {
        const userId = <?= json_encode($userId) ?>;
        const $wrapper = $('#live-watch-wrapper');
        let currentProgress = 0;
        let totalDuration = 0;
        let tickerInterval = null;
        let currentAnimeId = null;
        let currentEpisodeId = null;

        // Initialize values from static PHP render if card exists
        const $card = $wrapper.find('.live-watch-card');
        if ($card.length > 0) {
            currentProgress = parseInt($card.data('progress')) || 0;
            totalDuration = parseInt($card.data('duration')) || 0;
            currentAnimeId = $card.data('anime-id');
            currentEpisodeId = $card.data('episode-id');
            startLocalTicker();
        }

        // Start local ticker (advances every 1s)
        function startLocalTicker() {
            if (tickerInterval) clearInterval(tickerInterval);
            tickerInterval = setInterval(function() {
                if (currentProgress < totalDuration) {
                    currentProgress++;
                    updateProgressDOM();
                } else {
                    clearInterval(tickerInterval);
                }
            }, 1000);
        }

        // Update progress bar and time label in DOM
        function updateProgressDOM() {
            const $elapsed = $wrapper.find('.lw-elapsed');
            const $fill = $wrapper.find('.lw-fill');
            
            // Format MM:SS
            const minutes = Math.floor(currentProgress / 60);
            const seconds = currentProgress % 60;
            const formatted = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            $elapsed.text(formatted);

            // Update percentage
            const percent = totalDuration > 0 ? (currentProgress / totalDuration) * 100 : 0;
            $fill.css('width', percent + '%');
        }

        // Format duration seconds to MM:SS
        function formatMMSS(sec) {
            const minutes = Math.floor(sec / 60);
            const seconds = sec % 60;
            return (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        }

        // Generate complete HTML template dynamically
        function generateCardHtml(data) {
            return `
            <div class="live-watch-card" data-progress="${data.progress}" data-duration="${data.duration}" data-anime-id="${data.anime_id}" data-episode-id="${data.episode_id}" style="background: rgba(88, 101, 242, 0.08); border: 1px solid rgba(88, 101, 242, 0.2); border-radius: 20px; padding: 25px; margin-top: 25px; margin-bottom: 25px; display: flex; gap: 20px; align-items: center; box-shadow: 0 10px 35px rgba(88, 101, 242, 0.15); animation: pulseGlow 3s infinite ease-in-out; position: relative; overflow: hidden; font-family: 'Outfit', sans-serif;">
                
                <div class="glow-wave" style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(88,101,242,0.1) 0%, transparent 60%); pointer-events: none; animation: rotateGlow 15s linear infinite;"></div>

                <div class="lw-poster" style="width: 75px; height: 110px; border-radius: 12px; overflow: hidden; border: 2px solid #5865F2; box-shadow: 0 5px 15px rgba(0,0,0,0.3); flex-shrink: 0; position: relative; z-index: 2;">
                    <img src="${data.poster}" style="width: 100%; height: 100%; object-fit: cover;" alt="Anime Poster">
                    <div class="play-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-play" style="color: #5865F2; font-size: 18px; animation: pulseDot 1.5s infinite;"></i>
                    </div>
                </div>

                <div class="lw-info" style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; position: relative; z-index: 2;">
                    <div class="lw-badge" style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #5865F2; letter-spacing: 1.5px; margin-bottom: 5px; display: flex; align-items: center; gap: 6px;">
                        <span class="pulse-dot" style="width: 7px; height: 7px; background-color: #5865F2; border-radius: 50%; display: inline-block; animation: pulseDot 1.5s infinite;"></span>
                        Currently Watching
                    </div>
                    <h3 class="lw-title" style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${data.title}</h3>
                    <div class="lw-episode" style="font-size: 13.5px; font-weight: 600; color: #aaa; margin-bottom: 12px;">
                        Episode ${data.episode_number}
                    </div>

                    <div class="lw-progress-container" style="width: 100%; display: flex; flex-direction: column; gap: 6px;">
                        <div class="lw-bar" style="width: 100%; height: 5px; background: rgba(255,255,255,0.08); border-radius: 10px; overflow: hidden;">
                            <div class="lw-fill" style="width: ${data.percent}%; height: 100%; background: linear-gradient(90deg, #5865F2, #a370f7); border-radius: 10px; transition: width 0.5s ease;"></div>
                        </div>
                        <div class="lw-timer" style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #777; font-family: monospace;">
                            <span class="lw-elapsed">${formatMMSS(data.progress)}</span>
                            <span class="lw-total">${formatMMSS(data.duration)}</span>
                        </div>
                    </div>
                </div>

                <div class="lw-action" style="flex-shrink: 0; position: relative; z-index: 2;">
                    <a href="/watch/${data.anime_id}?ep=${data.episode_id}" class="btn btn-primary" style="background: linear-gradient(135deg, #5865F2, #4752c4); border: none; color: #fff; font-weight: 700; font-size: 12px; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(88,101,242,0.3); transition: transform 0.2s; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-play mr-1"></i> Watch Along
                    </a>
                </div>

            </div>`;
        }

        // Fetch updates periodically via AJAX polling (every 5 seconds)
        function pollLiveStatus() {
            $.get('/ajax/user/live-status', { userId: userId }, function(response) {
                if (response.watching) {
                    currentProgress = response.progress;
                    totalDuration = response.duration;

                    if ($wrapper.find('.live-watch-card').length === 0) {
                        // Card is missing: render and slide in
                        const cardHtml = generateCardHtml(response);
                        $wrapper.hide().html(cardHtml).slideDown(400);
                        currentAnimeId = response.anime_id;
                        currentEpisodeId = response.episode_id;
                        startLocalTicker();
                    } else {
                        // Card exists: check if show or episode changed
                        if (currentAnimeId !== response.anime_id || currentEpisodeId !== response.episode_id) {
                            // Episode changed: replace card content dynamically
                            currentAnimeId = response.anime_id;
                            currentEpisodeId = response.episode_id;
                            const cardHtml = generateCardHtml(response);
                            $wrapper.html(cardHtml);
                            startLocalTicker();
                        } else {
                            // Same episode: keep ticker running but sync/align progress to correct any drift
                            updateProgressDOM();
                        }
                    }
                } else {
                    // Stopped watching: remove card if exists
                    const $activeCard = $wrapper.find('.live-watch-card');
                    if ($activeCard.length > 0) {
                        if (tickerInterval) clearInterval(tickerInterval);
                        $wrapper.slideUp(400, function() {
                            $wrapper.empty();
                            currentAnimeId = null;
                            currentEpisodeId = null;
                        });
                    }
                }
            });
        }

        // Run polling loop
        setInterval(pollLiveStatus, 5000);
    });
	</script>
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
	<?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
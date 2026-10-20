<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/component/anime/qtip.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------------------------------------
   Helper
---------------------------------------- */
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

/* ---------------------------------------
   Get user ID from URL
   Example rewrite:
   /community/user/10/watch-list  -> watch-list.php?uid=10
   Change 'uid' to your real query param if needed.
---------------------------------------- */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// match /community/user/10/watch-list or /community/user/10/watch-list/
if (preg_match('~^/community/user/(\d+)/watch-list/?$~', $path, $m)) {
    $userId = (int)$m[1];
} else {
    http_response_code(404);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
    exit;
}

if ($userId <= 0) {
    http_response_code(404);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
    exit;
}

/* ---------------------------------------
   Load user (for name + existence)
   Adjust table/fields if needed
---------------------------------------- */
$sqlUser = "SELECT id, username FROM users WHERE id = ?";
$stmt = $conn->prepare($sqlUser);
$stmt->bind_param("i", $userId);
$stmt->execute();
$resUser = $stmt->get_result();
$user    = $resUser->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
    exit;
}
$username = $user['username'];

/* ---------------------------------------
   Watchlist TYPES:
   0 = All
   1 = Watching
   2 = On-Hold
   3 = Plan to Watch
   4 = Dropped
   5 = Completed
---------------------------------------- */
$type = isset($_GET['type']) ? (int)$_GET['type'] : 0;
if ($type < 0 || $type > 5) $type = 0;

/* ---------------------------------------
   Pagination settings
---------------------------------------- */
$perPage = 30;
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* Count total rows */
if ($type === 0) {
    $sqlCount = "SELECT COUNT(*) AS cnt FROM watchlist WHERE user_id = ?";
    $stmt = $conn->prepare($sqlCount);
    $stmt->bind_param("i", $userId);
} else {
    $sqlCount = "SELECT COUNT(*) AS cnt FROM watchlist WHERE user_id = ? AND type = ?";
    $stmt = $conn->prepare($sqlCount);
    $stmt->bind_param("ii", $userId, $type);
}

$stmt->execute();
$resCount   = $stmt->get_result();
$rowCount   = $resCount->fetch_assoc();
$total_rows = (int)($rowCount['cnt'] ?? 0);
$stmt->close();

$total_pages = max(1, (int)ceil($total_rows / $perPage));
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $perPage;

$typeLabels = [
    1 => 'Watching',
    2 => 'On-Hold',
    3 => 'Plan to Watch',
    4 => 'Dropped',
    5 => 'Completed',
];

/* ---------------------------------------
   Load watchlist for this user (with pagination)
---------------------------------------- */
if ($type === 0) {
    $sqlW = "SELECT * FROM watchlist
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sqlW);
    $stmt->bind_param("iii", $userId, $perPage, $offset);
} else {
    $sqlW = "SELECT * FROM watchlist
             WHERE user_id = ? AND type = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sqlW);
    $stmt->bind_param("iiii", $userId, $type, $perPage, $offset);
}

$stmt->execute();
$site_watchlist_result = $stmt->get_result();
$stmt->close();

/* Make sure $websiteUrl exists (in case _config didn't set it) */
if (!isset($websiteUrl)) {
    $websiteUrl = '';
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
    <title><?= htmlspecialchars($username) ?>'s Anime Watchlist - <?= $websiteTitle ?></title>
    <meta name="description" content="Browse <?= htmlspecialchars($username) ?>'s anime watchlist on <?= $websiteTitle ?>. See what they are watching, completed, or plan to watch.">
    <meta name="keywords" content="<?= htmlspecialchars($username) ?> watchlist, anime watchlist, <?= strtolower($websiteTitle) ?> watchlist">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/community/user/<?= $userId ?>/watch-list" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/community/user/<?= $userId ?>/watch-list">
    <meta property="og:title" content="<?= htmlspecialchars($username) ?>'s Anime Watchlist - <?= $websiteTitle ?>">
    <meta property="og:description" content="Browse <?= htmlspecialchars($username) ?>'s anime watchlist on <?= $websiteTitle ?>. See what they are watching, completed, or plan to watch.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($username) ?>'s Anime Watchlist - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Browse <?= htmlspecialchars($username) ?>'s anime watchlist on <?= $websiteTitle ?>. See what they are watching, completed, or plan to watch.">
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
                        <a href="/random" class="hst-item" data-toggle="tooltip"
                            data-original-title="Watch random anime">
                            <div class="hst-icon"><i class="fas fa-random"></i></div>
                            <div class="name"><span>Random</span></div>
                        </a>
                        <div class="hst-item" data-toggle="tooltip" title="Select language of anime name to display.">
                            <div class="select-anime-name toggle-lang"><span class="en">EN</span><span
                                    class="jp">JP</span></div>
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
                <div class="award-bg-img" style="background-image: url(/images/community/cover.jpg);"></div>
            </div>
            <div class="container">
                <div class="award-inner myzone-inner">
                    <div class="ai-control">
                        <div class="aic-left">
                            <a href="/community/user/<?= e($userId) ?>"><i class="fas fa-angle-left mr-2"></i>Back</a>
                        </div>
                    </div>

                    <div class="block_area my-watchlist-full">
                        <div class="block_area-header mb-4">
                            <div class="float-left bah-heading">
                                <h2 class="cat-heading mb-0">Watch List of <?= e($username) ?></h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <!-- Tabs (All / Watching / On-Hold / Plan / Dropped / Completed) -->
                        <div class="fav-tabs mb-4">
                            <ul class="nav nav-tabs pre-tabs pre-tabs-min">
                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list"
                                        class="nav-link <?= $type === 0 ? 'active' : '' ?>">All</a>
                                </li>

                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list?type=1"
                                        class="nav-link <?= $type === 1 ? 'active' : '' ?>">Watching</a>
                                </li>

                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list?type=2"
                                        class="nav-link <?= $type === 2 ? 'active' : '' ?>">On-Hold</a>
                                </li>

                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list?type=3"
                                        class="nav-link <?= $type === 3 ? 'active' : '' ?>">Plan to Watch</a>
                                </li>

                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list?type=4"
                                        class="nav-link <?= $type === 4 ? 'active' : '' ?>">Dropped</a>
                                </li>

                                <li class="nav-item">
                                    <a href="/community/user/<?= e($userId) ?>/watch-list?type=5"
                                        class="nav-link <?= $type === 5 ? 'active' : '' ?>">Completed</a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>

                        <div class="block_area-content block_area-list film_list film_list-grid">
                            <div class="film_list-wrap">

                                <?php $hasqtipCounter = 0; ?>
                                <?php if ($site_watchlist_result && $site_watchlist_result->num_rows > 0): ?>
                                <?php while($anime = $site_watchlist_result->fetch_assoc()): ?>
                                <?php
                                    $animeId   = $anime['anime_id']; // from watchlist table
                                    $animeData = fetchAnimeData($animeId); // your API function
                                    if (!$animeData) continue;
                                ?>
                                <div class="flw-item">
                                    <div class="film-poster">
                                        <?php if (!empty($animeData['adultContent'])): ?>
                                        <div class="tick tick-rate">18+</div>
                                        <?php endif; ?>

                                        <div class="tick ltr">
                                            <?php if (!empty($animeData['subEp'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i class="fas fa-closed-captioning mr-1"></i>
                                                <?= e($animeData['subEp']) ?>
                                            </div>
                                            <?php endif; ?>

                                            <?php if (!empty($animeData['dubEp'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i class="fas fa-microphone mr-1"></i>
                                                <?= e($animeData['dubEp']) ?>
                                            </div>
                                            <?php endif; ?>

                                            <?php if (isset($animeData['eps'])): ?>
                                            <div class="tick-item tick-eps">
                                                <?= (int)$animeData['eps'] ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <img loading="lazy" decoding="async" class="film-poster-img lazyload"
                                            src="<?= e($animeData['poster'] ?? '/path/to/default-image.jpg') ?>"
                                            alt="<?= e($animeData['title'] ?? 'Unknown Title') ?>">

                                        <a class="film-poster-ahref item-qtip"
                                            href="<?= $websiteUrl; ?>/watch/<?= e($anime['anime_id'] ?? '') ?>"
                                            data-id="<?= e($animeData['data_id'] ?? '') ?>"
                                            data-hasqtip="<?= $hasqtipCounter ?>"
                                            aria-describedby="qtip-<?= $hasqtipCounter ?>"
                                            title="<?= e($animeData['title'] ?? 'Unknown Title') ?>">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    </div>

                                    <div class="film-detail">
                                        <h3 class="film-name">
                                            <a href="<?= $websiteUrl; ?>/<?= e($anime['anime_id'] ?? '') ?>"
                                                title="<?= e($animeData['title'] ?? ($animeData['japa'] ?? '')) ?>">
                                                <span class="dynamic-name"
                                                    data-title="<?= e($animeData['title'] ?? '') ?>"
                                                    data-jname="<?= e($animeData['japa'] ?? '') ?>">
                                                    <?= e($animeData['title'] ?? ($animeData['japa'] ?? 'Unknown Title')) ?>
                                                </span>
                                            </a>
                                        </h3>
                                        <div class="fd-infor">
                                            <span class="fdi-item"><?= e($animeData['showType'] ?? '') ?></span>
                                            <span class="dot"></span>
                                            <span
                                                class="fdi-item fdi-duration"><?= e($animeData['duration'] ?? '') ?></span>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>
                                </div>
                                <?php $hasqtipCounter++; ?>
                                <?php endwhile; ?>
                                <?php endif; ?>

                            </div>
                            <div class="clearfix"></div>

                            <!-- Pagination placeholder; fill later if you add paging -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pre-pagination mt-5 mb-5">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-lg justify-content-center">
                                            <?php
                                                // base URL
                                                $baseUrl   = "/community/user/{$userId}/watch-list";
                                                // build type part only if > 0
                                                $typeQuery = ($type > 0) ? 'type=' . urlencode($type) . '&' : '';
                                            ?>
                            
                                            <!-- First (only if ≥ 3 pages) -->
                                            <?php if ($page > 1 && $total_pages >= 3): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                       href="<?= e($baseUrl) ?>?<?= $typeQuery ?>page=1"
                                                       title="First">«</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Previous -->
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                       href="<?= e($baseUrl) ?>?<?= $typeQuery ?>page=<?= $page-1 ?>"
                                                       title="Previous">‹</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Smart numeric range -->
                                            <?php
                                                $range = 2;
                                                $start = max(1, $page - $range);
                                                $end   = min($total_pages, $page + $range);
                                            ?>

                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <?php if ($i <= 3 || $i == $total_pages || ($i >= $start && $i <= $end)): ?>
                                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                        <a class="page-link"
                                                           href="<?= e($baseUrl) ?>?<?= $typeQuery ?>page=<?= $i ?>"
                                                           title="Page <?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endfor; ?>

                                            <!-- Next -->
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                       href="<?= e($baseUrl) ?>?<?= $typeQuery ?>page=<?= $page+1 ?>"
                                                       title="Next">›</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Last (only if ≥ 3 pages) -->
                                            <?php if ($page < $total_pages && $total_pages >= 3): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                       href="<?= e($baseUrl) ?>?<?= $typeQuery ?>page=<?= $total_pages ?>"
                                                       title="Last">»</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   	</div>
        <?php include 'src/component/footer.php' ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons' async='async'></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
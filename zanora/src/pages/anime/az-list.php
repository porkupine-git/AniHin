<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure TTL exists (in case not defined in _config)
if (!defined('API_CACHE_TTL')) {
    define('API_CACHE_TTL', 600); // 10 minutes
}

/* ------------------------------
    CATEGORY + PAGINATION INPUT
------------------------------ */
$category    = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$page        = max(1, (int)($_GET['page'] ?? 1));
$currentPage = $page;

global $zpi;

$errorMessage = null;
$data        = [];
$azResults   = null;
$totalPages  = 1;
$totalResults = 0;

/* ------------------------------
    BUILD API URL + CACHE KEY
------------------------------ */

// Case 1: main A-Z list page (/az-list or /az-list/)
if ($category === 'az-list' || $category === '') {
    $letter   = '';
    $apiUrl   = "$zpi/az-list?page={$page}";
    $cacheKey = "azlist_all_page_{$page}";
} else {
    // Case 2: specific letter page like /az-list/a or /az-list/b
    $letter   = strtoupper(str_replace('az-list/', '', $category)); // for your logic
    $apiUrl   = "$zpi/az-list/{$category}?page={$page}";
    $cacheKey = "azlist_{$letter}_page_{$page}";
}

/* ------------------------------
    FETCH WITH GLOBAL CACHED API HELPER
------------------------------ */
$data = getCachedApiJson($cacheKey, $apiUrl, 3600);

/* If totally failed (no API, no cache) */
if (!$data || empty($data['success'])) {
    $errorMessage = 'Could not connect to the API.';
    $data = ['results' => []];
}

/* ------------------------------
    EXTRACT RESULTS FOR VIEW
------------------------------ */
$azResults    = $data['results']['data']       ?? null;
$totalPages   = $data['results']['totalPages'] ?? 1;
$totalResults = $totalPages * 20;

if (!$azResults && !$errorMessage) {
    $errorMessage = 'Failed to fetch popular anime. Please try again later.';
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />

    <!-- SEO Meta Tags -->
    <title>Anime A-Z List<?= !empty($letter) ? ' - ' . htmlspecialchars($letter) : '' ?> – <?= $websiteTitle ?></title>
    <meta name="description" content="Browse our full directory of anime starting with <?= !empty($letter) ? htmlspecialchars($letter) : 'all letters' ?> in English sub and dub. Watch free anime online in HD quality on <?= $websiteTitle ?>. No registration required!">
    <meta name="keywords" content="anime to watch, watch anime, anime online, free anime online, online anime, anime streaming, stream anime online, english anime, english dubbed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/az-list<?= !empty($letter) ? '/' . strtolower(htmlspecialchars($letter)) : '' ?><?= $page > 1 ? '?page=' . $page : '' ?>" />

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
    <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="Anime A-Z List<?= !empty($letter) ? ' - ' . htmlspecialchars($letter) : '' ?> – <?= $websiteTitle ?>">
    <meta property="og:description" content="Browse our full directory of anime starting with <?= !empty($letter) ? htmlspecialchars($letter) : 'all letters' ?>. Watch free anime online in HD quality on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Card Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Anime A-Z List<?= !empty($letter) ? ' - ' . htmlspecialchars($letter) : '' ?> – <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Browse our full directory of anime starting with <?= !empty($letter) ? htmlspecialchars($letter) : 'all letters' ?>. Watch free anime online in HD quality on <?= $websiteTitle ?>.">
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
        <div id="main-wrapper" class="layout-page page-az">
            <div class="container">
                <div class="prebreadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/home" title="Home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">A-Z List</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-az-wrap">
                    <!--Begin: Section film list-->
                    <section class="block_area block_area_category">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Sort By Letters</h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="main-az">
                            <ul class="ulclear az-list">
                                <li class="<?= empty($letter) ? 'active' : '' ?>"><a href="/az-list">All</a></li>
                                <li class="<?= $letter === 'OTHER' ? 'active' : '' ?>"><a href="/az-list/other">#</a>
                                </li>
                                <li class="<?= $letter === '0-9' ? 'active' : '' ?>"><a href="/az-list/0-9">0-9</a></li>
                                <li class="<?= $letter === 'A' ? 'active' : '' ?>"><a href="/az-list/a">A</a></li>
                                <li class="<?= $letter === 'B' ? 'active' : '' ?>"><a href="/az-list/b">B</a></li>
                                <li class="<?= $letter === 'C' ? 'active' : '' ?>"><a href="/az-list/c">C</a></li>
                                <li class="<?= $letter === 'D' ? 'active' : '' ?>"><a href="/az-list/d">D</a></li>
                                <li class="<?= $letter === 'E' ? 'active' : '' ?>"><a href="/az-list/e">E</a></li>
                                <li class="<?= $letter === 'F' ? 'active' : '' ?>"><a href="/az-list/f">F</a></li>
                                <li class="<?= $letter === 'G' ? 'active' : '' ?>"><a href="/az-list/g">G</a></li>
                                <li class="<?= $letter === 'H' ? 'active' : '' ?>"><a href="/az-list/h">H</a></li>
                                <li class="<?= $letter === 'I' ? 'active' : '' ?>"><a href="/az-list/i">I</a></li>
                                <li class="<?= $letter === 'J' ? 'active' : '' ?>"><a href="/az-list/j">J</a></li>
                                <li class="<?= $letter === 'K' ? 'active' : '' ?>"><a href="/az-list/k">K</a></li>
                                <li class="<?= $letter === 'L' ? 'active' : '' ?>"><a href="/az-list/l">L</a></li>
                                <li class="<?= $letter === 'M' ? 'active' : '' ?>"><a href="/az-list/m">M</a></li>
                                <li class="<?= $letter === 'N' ? 'active' : '' ?>"><a href="/az-list/n">N</a></li>
                                <li class="<?= $letter === 'O' ? 'active' : '' ?>"><a href="/az-list/o">O</a></li>
                                <li class="<?= $letter === 'P' ? 'active' : '' ?>"><a href="/az-list/p">P</a></li>
                                <li class="<?= $letter === 'Q' ? 'active' : '' ?>"><a href="/az-list/q">Q</a></li>
                                <li class="<?= $letter === 'R' ? 'active' : '' ?>"><a href="/az-list/r">R</a></li>
                                <li class="<?= $letter === 'S' ? 'active' : '' ?>"><a href="/az-list/s">S</a></li>
                                <li class="<?= $letter === 'T' ? 'active' : '' ?>"><a href="/az-list/t">T</a></li>
                                <li class="<?= $letter === 'U' ? 'active' : '' ?>"><a href="/az-list/u">U</a></li>
                                <li class="<?= $letter === 'V' ? 'active' : '' ?>"><a href="/az-list/v">V</a></li>
                                <li class="<?= $letter === 'W' ? 'active' : '' ?>"><a href="/az-list/w">W</a></li>
                                <li class="<?= $letter === 'X' ? 'active' : '' ?>"><a href="/az-list/x">X</a></li>
                                <li class="<?= $letter === 'Y' ? 'active' : '' ?>"><a href="/az-list/y">Y</a></li>
                                <li class="<?= $letter === 'Z' ? 'active' : '' ?>"><a href="/az-list/z">Z</a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <div class="block_area-content block_area-list film_list film_list-grid">
                                <div class="film_list-wrap">
                                    <?php $hasqtipCounter = 0; ?>
                                    <?php if (!empty($azResults)): ?>
                                    <?php foreach ($azResults as $anime): ?>
                                    <div class="flw-item">
                                        <div class="film-poster">
                                            <?php if (!empty($anime['adultContent'])): ?>
                                            <div class="tick tick-rate">18+</div>
                                            <?php endif; ?>
                                            <div class="tick ltr">
                                                <?php if (!empty($anime['tvInfo']['sub'])): ?>
                                                <div class="tick-item tick-sub amp-algn">
                                                    <i class="fas fa-closed-captioning"></i>
                                                    &nbsp;<?= $anime['tvInfo']['sub'] ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($anime['tvInfo']['dub'])): ?>
                                                <div class="tick-item tick-dub amp-algn">
                                                    <i class="fas fa-microphone"></i>
                                                    &nbsp;<?= $anime['tvInfo']['dub'] ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (isset($anime['tvInfo']['eps'])): ?>
                                                <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <img loading="lazy" decoding="async" class="film-poster-img lazyload" data-src="<?= $anime['poster'] ?>"
                                                alt="<?= htmlspecialchars($anime['title']) ?>">
                                            <a class="film-poster-ahref item-qtip" href="/<?= $anime['id'] ?>"
                                                data-id="<?= $anime['data_id']; ?>"
                                                data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"></a>
                                        </div>
                                        <div class="film-detail">
                                            <h3 class="film-name">
                                                <a href="/<?= $anime['id'] ?>" class="dynamic-name"
                                                    data-title="<?= htmlspecialchars((string)($anime['title'] ?? '')) ?>"
                                                    data-jname="<?= htmlspecialchars((string)($anime['japanese_title'] ?? '')) ?>"><?= htmlspecialchars((string)($anime['title'] ?? '')) ?></a>
                                            </h3>
                                            <div class="fd-infor">
                                                <span
                                                    class="fdi-item"><?= htmlspecialchars((string)($anime['tvInfo']['showType'] ?? '')) ?></span>
                                                <span class="dot"></span>
                                                <span
                                                    class="fdi-item"><?= htmlspecialchars((string)($anime['tvInfo']['duration'] ?? ($anime['duration'] ?? ''))) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $hasqtipCounter++; ?>
                                    <?php endforeach; ?>
                                    <div class="clearfix"></div>
                                    <?php else: ?>
                                    <p>No results found.</p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($totalPages > 1): ?>
                                <div class="pre-pagination mt-5 mb-5">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-lg justify-content-center">
                                            <?php
                                        $range = 2;
                            $start = max(1, $currentPage - $range);
                            $end   = min($totalPages, $currentPage + $range);
                            if ($currentPage > 1): ?>
                                            <?php if ($totalPages >= 3): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1" title="First">«</a>
                                            </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>"
                                                    title="Previous">‹</a>
                                            </li>
                                            <?php endif; ?>
                                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"
                                                    title="Page <?= $i ?>"><?= $i ?></a>
                                            </li>
                                            <?php endfor; ?>
                                            <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>"
                                                    title="Next">›</a>
                                            </li>
                                            <?php if ($totalPages >= 3): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $totalPages ?>" title="Last">»</a>
                                            </li>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </section>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons' async='async'></script>
    <script type='text/javascript' src="/js/app.min.js?v=<?= $versionJs ?>"></script>
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
	</script>
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
	<script defer src="https://cloud.umami.is/script.js" data-website-id="2bb00748-b32d-43ee-803c-163b2c56807b"></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require '_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$query = $_SERVER['QUERY_STRING'] ?: 'sort=default';

$searchResults = [];
$currentPage   = 1;
$totalPages    = 1;
$hasNextPage   = false;
$errorMessage  = null;
$data          = ['results' => []];

if ($query) {
    // Build API URL & cache key (per unique filter query)
    $apiUrl   = rtrim($zpi, '/') . "/filter?" . $query;
    $cacheKey = 'filter_' . md5($query);

    // Use global cached API helper (DB cache + TTL + stale fallback)
    $data = getCachedApiJson($cacheKey, $apiUrl, 3600);

    if (
        $data &&
        !empty($data['success']) &&
        isset($data['results']['data'])
    ) {
        $searchResults = $data['results']['data'];
        $currentPage   = $data['results']['currentPage'] ?? 1;
        $totalPages    = $data['results']['totalPage']  ?? 1;
        $hasNextPage   = $data['results']['hasNextPage'] ?? false;
    } else {
        $errorMessage = 'Failed to fetch search results. Please try again later.';
    }
}

$itemsPerPage = 36;
$totalResults = 0;

if (isset($data['results']['total'])) {
    $totalResults = $data['results']['total'];
} elseif (isset($data['results']['data']) && $totalPages > 0) {
    $totalResults = count($data['results']['data']) + (($totalPages - 1) * $itemsPerPage);
} else {
    $totalResults = $totalPages * $itemsPerPage;
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
    <title>Filter and Find Anime - <?= $websiteTitle ?></title>
    <meta name="description" content="Filter and search thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>. No registration required!">
    <meta name="keywords" content="watch anime online, anime site, free anime, anime to watch, online anime, anime streaming, stream anime online, english anime, english dubbed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/filter<?= !empty($query) ? '?' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') : '' ?>" />

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
    <meta property="og:title" content="Filter and Find Anime - <?= $websiteTitle ?>">
    <meta property="og:description" content="Filter and search thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Card Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Filter and Find Anime - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Filter and search thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
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
        <div id="main-wrapper" class="layout-page page-az page-filter">
            <div class="container">
                <div class="prebreadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Filter</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-search-wrap">
                    <section class="block_area block_area_search">
                        <div id="filter-block">
                            <button id="toggle-filter" class="btn btn-radius btn-sm btn-secondary"><i
                                    class="fas fa-plus-circle mr-2"></i>Advanced search
                            </button>
                            <div id="cate-filter" class="category_filter">
                                <div class="category_filter-content">
                                    <form method="get" id="filter-form">
                                        <input type="hidden" name="keyword" value="">
                                        <div class="cfc-min-block cfc-min-block-date">
                                            <div class="ni-head mb-3"><strong>Filter</strong></div>
                                            <div class="cmb-item cmb-type">
                                                <div class="ni-head">Type</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="type">
                                                            <option <?= empty($_GET['type']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '1') ? 'selected' : '' ?>
                                                                value="1">Movie</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '2') ? 'selected' : '' ?>
                                                                value="2">TV</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '3') ? 'selected' : '' ?>
                                                                value="3">OVA</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '4') ? 'selected' : '' ?>
                                                                value="4">ONA</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '5') ? 'selected' : '' ?>
                                                                value="5">Special</option>
                                                            <option
                                                                <?= (isset($_GET['type']) && $_GET['type'] == '6') ? 'selected' : '' ?>
                                                                value="6">Music</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item cmb-status">
                                                <div class="ni-head">Status</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="status">
                                                            <option <?= empty($_GET['status']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>
                                                                value="1">Finished Airing</option>
                                                            <option
                                                                <?= (isset($_GET['status']) && $_GET['status'] == '2') ? 'selected' : '' ?>
                                                                value="2">Currently Airing</option>
                                                            <option
                                                                <?= (isset($_GET['status']) && $_GET['status'] == '3') ? 'selected' : '' ?>
                                                                value="3">Not yet aired</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item cmb-rated">
                                                <div class="ni-head">Rated</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="rated">
                                                            <option <?= empty($_GET['rated']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '1') ? 'selected' : '' ?>
                                                                value="1">G</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '2') ? 'selected' : '' ?>
                                                                value="2">PG</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '3') ? 'selected' : '' ?>
                                                                value="3">PG-13</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '4') ? 'selected' : '' ?>
                                                                value="4">R</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '5') ? 'selected' : '' ?>
                                                                value="5">R+</option>
                                                            <option
                                                                <?= (isset($_GET['rated']) && $_GET['rated'] == '6') ? 'selected' : '' ?>
                                                                value="6">Rx</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item cmb-score">
                                                <div class="ni-head">Score</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="score">
                                                            <option <?= empty($_GET['score']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '1') ? 'selected' : '' ?>
                                                                value="1">(1) Appalling</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '2') ? 'selected' : '' ?>
                                                                value="2">(2) Horrible</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '3') ? 'selected' : '' ?>
                                                                value="3">(3) Very Bad</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '4') ? 'selected' : '' ?>
                                                                value="4">(4) Bad</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '5') ? 'selected' : '' ?>
                                                                value="5">(5) Average</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '6') ? 'selected' : '' ?>
                                                                value="6">(6) Fine</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '7') ? 'selected' : '' ?>
                                                                value="7">(7) Good</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '8') ? 'selected' : '' ?>
                                                                value="8">(8) Very Good</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '9') ? 'selected' : '' ?>
                                                                value="9">(9) Great</option>
                                                            <option
                                                                <?= (isset($_GET['score']) && $_GET['score'] == '10') ? 'selected' : '' ?>
                                                                value="10">(10) Masterpiece</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item">
                                                <div class="ni-head">Season</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="season">
                                                            <option <?= empty($_GET['season']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['season']) && $_GET['season'] == '1') ? 'selected' : '' ?>
                                                                value="1">Spring</option>
                                                            <option
                                                                <?= (isset($_GET['season']) && $_GET['season'] == '2') ? 'selected' : '' ?>
                                                                value="2">Summer</option>
                                                            <option
                                                                <?= (isset($_GET['season']) && $_GET['season'] == '3') ? 'selected' : '' ?>
                                                                value="3">Fall</option>
                                                            <option
                                                                <?= (isset($_GET['season']) && $_GET['season'] == '4') ? 'selected' : '' ?>
                                                                value="4">Winter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item">
                                                <div class="ni-head">Language</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="language">
                                                            <option <?= empty($_GET['language']) ? 'selected' : '' ?>
                                                                value="">All</option>
                                                            <option
                                                                <?= (isset($_GET['language']) && $_GET['language'] == '1') ? 'selected' : '' ?>
                                                                value="1">SUB</option>
                                                            <option
                                                                <?= (isset($_GET['language']) && $_GET['language'] == '2') ? 'selected' : '' ?>
                                                                value="2">DUB</option>
                                                            <option
                                                                <?= (isset($_GET['language']) && $_GET['language'] == '3') ? 'selected' : '' ?>
                                                                value="3">SUB & DUB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="cfc-min-block cfc-min-block-date">
                                            <div class="cmb-item cmb-date">
                                                <div class="ni-head mr-2">Start Date</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="sy">
                                                            <option <?= empty($_GET['sy']) ? 'selected' : '' ?>
                                                                value="">Year</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2025') ? 'selected' : '' ?>
                                                                value="2025">2025</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2024') ? 'selected' : '' ?>
                                                                value="2024">2024</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2023') ? 'selected' : '' ?>
                                                                value="2023">2023</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2022') ? 'selected' : '' ?>
                                                                value="2022">2022</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2021') ? 'selected' : '' ?>
                                                                value="2021">2021</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2020') ? 'selected' : '' ?>
                                                                value="2020">2020</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2019') ? 'selected' : '' ?>
                                                                value="2019">2019</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2018') ? 'selected' : '' ?>
                                                                value="2018">2018</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2017') ? 'selected' : '' ?>
                                                                value="2017">2017</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2016') ? 'selected' : '' ?>
                                                                value="2016">2016</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2015') ? 'selected' : '' ?>
                                                                value="2015">2015</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2014') ? 'selected' : '' ?>
                                                                value="2014">2014</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2013') ? 'selected' : '' ?>
                                                                value="2013">2013</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2012') ? 'selected' : '' ?>
                                                                value="2012">2012</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2011') ? 'selected' : '' ?>
                                                                value="2011">2011</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2010') ? 'selected' : '' ?>
                                                                value="2010">2010</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2009') ? 'selected' : '' ?>
                                                                value="2009">2009</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2008') ? 'selected' : '' ?>
                                                                value="2008">2008</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2007') ? 'selected' : '' ?>
                                                                value="2007">2007</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2006') ? 'selected' : '' ?>
                                                                value="2006">2006</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2005') ? 'selected' : '' ?>
                                                                value="2005">2005</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2004') ? 'selected' : '' ?>
                                                                value="2004">2004</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2003') ? 'selected' : '' ?>
                                                                value="2003">2003</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2002') ? 'selected' : '' ?>
                                                                value="2002">2002</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2001') ? 'selected' : '' ?>
                                                                value="2001">2001</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '2000') ? 'selected' : '' ?>
                                                                value="2000">2000</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1999') ? 'selected' : '' ?>
                                                                value="1999">1999</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1998') ? 'selected' : '' ?>
                                                                value="1998">1998</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1997') ? 'selected' : '' ?>
                                                                value="1997">1997</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1996') ? 'selected' : '' ?>
                                                                value="1996">1996</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1995') ? 'selected' : '' ?>
                                                                value="1995">1995</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1994') ? 'selected' : '' ?>
                                                                value="1994">1994</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1993') ? 'selected' : '' ?>
                                                                value="1993">1993</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1992') ? 'selected' : '' ?>
                                                                value="1992">1992</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1991') ? 'selected' : '' ?>
                                                                value="1991">1991</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1990') ? 'selected' : '' ?>
                                                                value="1990">1990</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1989') ? 'selected' : '' ?>
                                                                value="1989">1989</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1988') ? 'selected' : '' ?>
                                                                value="1988">1988</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1987') ? 'selected' : '' ?>
                                                                value="1987">1987</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1986') ? 'selected' : '' ?>
                                                                value="1986">1986</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1985') ? 'selected' : '' ?>
                                                                value="1985">1985</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1984') ? 'selected' : '' ?>
                                                                value="1984">1984</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1983') ? 'selected' : '' ?>
                                                                value="1983">1983</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1982') ? 'selected' : '' ?>
                                                                value="1982">1982</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1981') ? 'selected' : '' ?>
                                                                value="1981">1981</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1980') ? 'selected' : '' ?>
                                                                value="1980">1980</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1979') ? 'selected' : '' ?>
                                                                value="1979">1979</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1978') ? 'selected' : '' ?>
                                                                value="1978">1978</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1977') ? 'selected' : '' ?>
                                                                value="1977">1977</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1976') ? 'selected' : '' ?>
                                                                value="1976">1976</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1975') ? 'selected' : '' ?>
                                                                value="1975">1975</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1974') ? 'selected' : '' ?>
                                                                value="1974">1974</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1973') ? 'selected' : '' ?>
                                                                value="1973">1973</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1972') ? 'selected' : '' ?>
                                                                value="1972">1972</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1971') ? 'selected' : '' ?>
                                                                value="1971">1971</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1970') ? 'selected' : '' ?>
                                                                value="1970">1970</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1969') ? 'selected' : '' ?>
                                                                value="1969">1969</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1968') ? 'selected' : '' ?>
                                                                value="1968">1968</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1967') ? 'selected' : '' ?>
                                                                value="1967">1967</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1966') ? 'selected' : '' ?>
                                                                value="1966">1966</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1965') ? 'selected' : '' ?>
                                                                value="1965">1965</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1964') ? 'selected' : '' ?>
                                                                value="1964">1964</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1963') ? 'selected' : '' ?>
                                                                value="1963">1963</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1962') ? 'selected' : '' ?>
                                                                value="1962">1962</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1961') ? 'selected' : '' ?>
                                                                value="1961">1961</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1960') ? 'selected' : '' ?>
                                                                value="1960">1960</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1959') ? 'selected' : '' ?>
                                                                value="1959">1959</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1958') ? 'selected' : '' ?>
                                                                value="1958">1958</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1957') ? 'selected' : '' ?>
                                                                value="1957">1957</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1956') ? 'selected' : '' ?>
                                                                value="1956">1956</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1955') ? 'selected' : '' ?>
                                                                value="1955">1955</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1954') ? 'selected' : '' ?>
                                                                value="1954">1954</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1953') ? 'selected' : '' ?>
                                                                value="1953">1953</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1952') ? 'selected' : '' ?>
                                                                value="1952">1952</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1951') ? 'selected' : '' ?>
                                                                value="1951">1951</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1950') ? 'selected' : '' ?>
                                                                value="1950">1950</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1949') ? 'selected' : '' ?>
                                                                value="1949">1949</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1948') ? 'selected' : '' ?>
                                                                value="1948">1948</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1947') ? 'selected' : '' ?>
                                                                value="1947">1947</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1946') ? 'selected' : '' ?>
                                                                value="1946">1946</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1945') ? 'selected' : '' ?>
                                                                value="1945">1945</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1944') ? 'selected' : '' ?>
                                                                value="1944">1944</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1943') ? 'selected' : '' ?>
                                                                value="1943">1943</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1942') ? 'selected' : '' ?>
                                                                value="1942">1942</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1941') ? 'selected' : '' ?>
                                                                value="1941">1941</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1940') ? 'selected' : '' ?>
                                                                value="1940">1940</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1939') ? 'selected' : '' ?>
                                                                value="1939">1939</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1938') ? 'selected' : '' ?>
                                                                value="1938">1938</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1937') ? 'selected' : '' ?>
                                                                value="1937">1937</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1936') ? 'selected' : '' ?>
                                                                value="1936">1936</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1935') ? 'selected' : '' ?>
                                                                value="1935">1935</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1934') ? 'selected' : '' ?>
                                                                value="1934">1934</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1933') ? 'selected' : '' ?>
                                                                value="1933">1933</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1932') ? 'selected' : '' ?>
                                                                value="1932">1932</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1931') ? 'selected' : '' ?>
                                                                value="1931">1931</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1930') ? 'selected' : '' ?>
                                                                value="1930">1930</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1929') ? 'selected' : '' ?>
                                                                value="1929">1929</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1928') ? 'selected' : '' ?>
                                                                value="1928">1928</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1927') ? 'selected' : '' ?>
                                                                value="1927">1927</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1926') ? 'selected' : '' ?>
                                                                value="1926">1926</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1925') ? 'selected' : '' ?>
                                                                value="1925">1925</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1924') ? 'selected' : '' ?>
                                                                value="1924">1924</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1923') ? 'selected' : '' ?>
                                                                value="1923">1923</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1922') ? 'selected' : '' ?>
                                                                value="1922">1922</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1921') ? 'selected' : '' ?>
                                                                value="1921">1921</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1920') ? 'selected' : '' ?>
                                                                value="1920">1920</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1919') ? 'selected' : '' ?>
                                                                value="1919">1919</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1918') ? 'selected' : '' ?>
                                                                value="1918">1918</option>
                                                            <option
                                                                <?= (isset($_GET['sy']) && $_GET['sy'] == '1917') ? 'selected' : '' ?>
                                                                value="1917">1917</option>
                                                        </select>
                                                    </div>
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="sm">
                                                            <option <?= empty($_GET['sm']) ? 'selected' : '' ?>
                                                                value="">Month</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '1') ? 'selected' : '' ?>
                                                                value="1">01</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '2') ? 'selected' : '' ?>
                                                                value="2">02</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '3') ? 'selected' : '' ?>
                                                                value="3">03</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '4') ? 'selected' : '' ?>
                                                                value="4">04</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '5') ? 'selected' : '' ?>
                                                                value="5">05</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '6') ? 'selected' : '' ?>
                                                                value="6">06</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '7') ? 'selected' : '' ?>
                                                                value="7">07</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '8') ? 'selected' : '' ?>
                                                                value="8">08</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '9') ? 'selected' : '' ?>
                                                                value="9">09</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '10') ? 'selected' : '' ?>
                                                                value="10">10</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '11') ? 'selected' : '' ?>
                                                                value="11">11</option>
                                                            <option
                                                                <?= (isset($_GET['sm']) && $_GET['sm'] == '12') ? 'selected' : '' ?>
                                                                value="12">12</option>
                                                        </select>
                                                    </div>
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="sd">
                                                            <option <?= empty($_GET['sd']) ? 'selected' : '' ?>
                                                                value="">Day</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '1') ? 'selected' : '' ?>
                                                                value="1">01</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '2') ? 'selected' : '' ?>
                                                                value="2">02</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '3') ? 'selected' : '' ?>
                                                                value="3">03</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '4') ? 'selected' : '' ?>
                                                                value="4">04</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '5') ? 'selected' : '' ?>
                                                                value="5">05</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '6') ? 'selected' : '' ?>
                                                                value="6">06</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '7') ? 'selected' : '' ?>
                                                                value="7">07</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '8') ? 'selected' : '' ?>
                                                                value="8">08</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '9') ? 'selected' : '' ?>
                                                                value="9">09</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '10') ? 'selected' : '' ?>
                                                                value="10">10</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '11') ? 'selected' : '' ?>
                                                                value="11">11</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '12') ? 'selected' : '' ?>
                                                                value="12">12</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '13') ? 'selected' : '' ?>
                                                                value="13">13</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '14') ? 'selected' : '' ?>
                                                                value="14">14</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '15') ? 'selected' : '' ?>
                                                                value="15">15</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '16') ? 'selected' : '' ?>
                                                                value="16">16</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '17') ? 'selected' : '' ?>
                                                                value="17">17</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '18') ? 'selected' : '' ?>
                                                                value="18">18</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '19') ? 'selected' : '' ?>
                                                                value="19">19</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '20') ? 'selected' : '' ?>
                                                                value="20">20</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '21') ? 'selected' : '' ?>
                                                                value="21">21</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '22') ? 'selected' : '' ?>
                                                                value="22">22</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '23') ? 'selected' : '' ?>
                                                                value="23">23</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '24') ? 'selected' : '' ?>
                                                                value="24">24</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '25') ? 'selected' : '' ?>
                                                                value="25">25</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '26') ? 'selected' : '' ?>
                                                                value="26">26</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '27') ? 'selected' : '' ?>
                                                                value="27">27</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '28') ? 'selected' : '' ?>
                                                                value="28">28</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '29') ? 'selected' : '' ?>
                                                                value="29">29</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '30') ? 'selected' : '' ?>
                                                                value="30">30</option>
                                                            <option
                                                                <?= (isset($_GET['sd']) && $_GET['sd'] == '31') ? 'selected' : '' ?>
                                                                value="31">31</option>
                                                        </select>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item cmb-date">
                                                <div class="ni-head mr-2">End Date</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="ey">
                                                            <option <?= empty($_GET['ey']) ? 'selected' : '' ?>
                                                                value="">Year</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2025') ? 'selected' : '' ?>
                                                                value="2025">2025</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2024') ? 'selected' : '' ?>
                                                                value="2024">2024</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2023') ? 'selected' : '' ?>
                                                                value="2023">2023</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2022') ? 'selected' : '' ?>
                                                                value="2022">2022</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2021') ? 'selected' : '' ?>
                                                                value="2021">2021</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2020') ? 'selected' : '' ?>
                                                                value="2020">2020</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2019') ? 'selected' : '' ?>
                                                                value="2019">2019</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2018') ? 'selected' : '' ?>
                                                                value="2018">2018</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2017') ? 'selected' : '' ?>
                                                                value="2017">2017</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2016') ? 'selected' : '' ?>
                                                                value="2016">2016</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2015') ? 'selected' : '' ?>
                                                                value="2015">2015</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2014') ? 'selected' : '' ?>
                                                                value="2014">2014</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2013') ? 'selected' : '' ?>
                                                                value="2013">2013</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2012') ? 'selected' : '' ?>
                                                                value="2012">2012</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2011') ? 'selected' : '' ?>
                                                                value="2011">2011</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2010') ? 'selected' : '' ?>
                                                                value="2010">2010</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2009') ? 'selected' : '' ?>
                                                                value="2009">2009</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2008') ? 'selected' : '' ?>
                                                                value="2008">2008</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2007') ? 'selected' : '' ?>
                                                                value="2007">2007</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2006') ? 'selected' : '' ?>
                                                                value="2006">2006</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2005') ? 'selected' : '' ?>
                                                                value="2005">2005</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2004') ? 'selected' : '' ?>
                                                                value="2004">2004</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2003') ? 'selected' : '' ?>
                                                                value="2003">2003</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2002') ? 'selected' : '' ?>
                                                                value="2002">2002</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2001') ? 'selected' : '' ?>
                                                                value="2001">2001</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '2000') ? 'selected' : '' ?>
                                                                value="2000">2000</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1999') ? 'selected' : '' ?>
                                                                value="1999">1999</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1998') ? 'selected' : '' ?>
                                                                value="1998">1998</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1997') ? 'selected' : '' ?>
                                                                value="1997">1997</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1996') ? 'selected' : '' ?>
                                                                value="1996">1996</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1995') ? 'selected' : '' ?>
                                                                value="1995">1995</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1994') ? 'selected' : '' ?>
                                                                value="1994">1994</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1993') ? 'selected' : '' ?>
                                                                value="1993">1993</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1992') ? 'selected' : '' ?>
                                                                value="1992">1992</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1991') ? 'selected' : '' ?>
                                                                value="1991">1991</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1990') ? 'selected' : '' ?>
                                                                value="1990">1990</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1989') ? 'selected' : '' ?>
                                                                value="1989">1989</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1988') ? 'selected' : '' ?>
                                                                value="1988">1988</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1987') ? 'selected' : '' ?>
                                                                value="1987">1987</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1986') ? 'selected' : '' ?>
                                                                value="1986">1986</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1985') ? 'selected' : '' ?>
                                                                value="1985">1985</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1984') ? 'selected' : '' ?>
                                                                value="1984">1984</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1983') ? 'selected' : '' ?>
                                                                value="1983">1983</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1982') ? 'selected' : '' ?>
                                                                value="1982">1982</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1981') ? 'selected' : '' ?>
                                                                value="1981">1981</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1980') ? 'selected' : '' ?>
                                                                value="1980">1980</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1979') ? 'selected' : '' ?>
                                                                value="1979">1979</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1978') ? 'selected' : '' ?>
                                                                value="1978">1978</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1977') ? 'selected' : '' ?>
                                                                value="1977">1977</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1976') ? 'selected' : '' ?>
                                                                value="1976">1976</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1975') ? 'selected' : '' ?>
                                                                value="1975">1975</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1974') ? 'selected' : '' ?>
                                                                value="1974">1974</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1973') ? 'selected' : '' ?>
                                                                value="1973">1973</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1972') ? 'selected' : '' ?>
                                                                value="1972">1972</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1971') ? 'selected' : '' ?>
                                                                value="1971">1971</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1970') ? 'selected' : '' ?>
                                                                value="1970">1970</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1969') ? 'selected' : '' ?>
                                                                value="1969">1969</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1968') ? 'selected' : '' ?>
                                                                value="1968">1968</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1967') ? 'selected' : '' ?>
                                                                value="1967">1967</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1966') ? 'selected' : '' ?>
                                                                value="1966">1966</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1965') ? 'selected' : '' ?>
                                                                value="1965">1965</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1964') ? 'selected' : '' ?>
                                                                value="1964">1964</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1963') ? 'selected' : '' ?>
                                                                value="1963">1963</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1962') ? 'selected' : '' ?>
                                                                value="1962">1962</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1961') ? 'selected' : '' ?>
                                                                value="1961">1961</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1960') ? 'selected' : '' ?>
                                                                value="1960">1960</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1959') ? 'selected' : '' ?>
                                                                value="1959">1959</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1958') ? 'selected' : '' ?>
                                                                value="1958">1958</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1957') ? 'selected' : '' ?>
                                                                value="1957">1957</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1956') ? 'selected' : '' ?>
                                                                value="1956">1956</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1955') ? 'selected' : '' ?>
                                                                value="1955">1955</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1954') ? 'selected' : '' ?>
                                                                value="1954">1954</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1953') ? 'selected' : '' ?>
                                                                value="1953">1953</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1952') ? 'selected' : '' ?>
                                                                value="1952">1952</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1951') ? 'selected' : '' ?>
                                                                value="1951">1951</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1950') ? 'selected' : '' ?>
                                                                value="1950">1950</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1949') ? 'selected' : '' ?>
                                                                value="1949">1949</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1948') ? 'selected' : '' ?>
                                                                value="1948">1948</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1947') ? 'selected' : '' ?>
                                                                value="1947">1947</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1946') ? 'selected' : '' ?>
                                                                value="1946">1946</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1945') ? 'selected' : '' ?>
                                                                value="1945">1945</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1944') ? 'selected' : '' ?>
                                                                value="1944">1944</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1943') ? 'selected' : '' ?>
                                                                value="1943">1943</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1942') ? 'selected' : '' ?>
                                                                value="1942">1942</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1941') ? 'selected' : '' ?>
                                                                value="1941">1941</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1940') ? 'selected' : '' ?>
                                                                value="1940">1940</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1939') ? 'selected' : '' ?>
                                                                value="1939">1939</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1938') ? 'selected' : '' ?>
                                                                value="1938">1938</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1937') ? 'selected' : '' ?>
                                                                value="1937">1937</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1936') ? 'selected' : '' ?>
                                                                value="1936">1936</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1935') ? 'selected' : '' ?>
                                                                value="1935">1935</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1934') ? 'selected' : '' ?>
                                                                value="1934">1934</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1933') ? 'selected' : '' ?>
                                                                value="1933">1933</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1932') ? 'selected' : '' ?>
                                                                value="1932">1932</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1931') ? 'selected' : '' ?>
                                                                value="1931">1931</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1930') ? 'selected' : '' ?>
                                                                value="1930">1930</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1929') ? 'selected' : '' ?>
                                                                value="1929">1929</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1928') ? 'selected' : '' ?>
                                                                value="1928">1928</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1927') ? 'selected' : '' ?>
                                                                value="1927">1927</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1926') ? 'selected' : '' ?>
                                                                value="1926">1926</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1925') ? 'selected' : '' ?>
                                                                value="1925">1925</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1924') ? 'selected' : '' ?>
                                                                value="1924">1924</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1923') ? 'selected' : '' ?>
                                                                value="1923">1923</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1922') ? 'selected' : '' ?>
                                                                value="1922">1922</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1921') ? 'selected' : '' ?>
                                                                value="1921">1921</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1920') ? 'selected' : '' ?>
                                                                value="1920">1920</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1919') ? 'selected' : '' ?>
                                                                value="1919">1919</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1918') ? 'selected' : '' ?>
                                                                value="1918">1918</option>
                                                            <option
                                                                <?= (isset($_GET['ey']) && $_GET['ey'] == '1917') ? 'selected' : '' ?>
                                                                value="1917">1917</option>
                                                        </select>
                                                    </div>
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="em">
                                                            <option <?= empty($_GET['em']) ? 'selected' : '' ?>
                                                                value="">Month</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '1') ? 'selected' : '' ?>
                                                                value="1">01</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '2') ? 'selected' : '' ?>
                                                                value="2">02</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '3') ? 'selected' : '' ?>
                                                                value="3">03</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '4') ? 'selected' : '' ?>
                                                                value="4">04</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '5') ? 'selected' : '' ?>
                                                                value="5">05</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '6') ? 'selected' : '' ?>
                                                                value="6">06</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '7') ? 'selected' : '' ?>
                                                                value="7">07</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '8') ? 'selected' : '' ?>
                                                                value="8">08</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '9') ? 'selected' : '' ?>
                                                                value="9">09</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '10') ? 'selected' : '' ?>
                                                                value="10">10</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '11') ? 'selected' : '' ?>
                                                                value="11">11</option>
                                                            <option
                                                                <?= (isset($_GET['em']) && $_GET['em'] == '12') ? 'selected' : '' ?>
                                                                value="12">12</option>
                                                        </select>
                                                    </div>
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="ed">
                                                            <option <?= empty($_GET['ed']) ? 'selected' : '' ?>
                                                                value="">Day</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '1') ? 'selected' : '' ?>
                                                                value="1">01</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '2') ? 'selected' : '' ?>
                                                                value="2">02</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '3') ? 'selected' : '' ?>
                                                                value="3">03</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '4') ? 'selected' : '' ?>
                                                                value="4">04</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '5') ? 'selected' : '' ?>
                                                                value="5">05</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '6') ? 'selected' : '' ?>
                                                                value="6">06</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '7') ? 'selected' : '' ?>
                                                                value="7">07</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '8') ? 'selected' : '' ?>
                                                                value="8">08</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '9') ? 'selected' : '' ?>
                                                                value="9">09</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '10') ? 'selected' : '' ?>
                                                                value="10">10</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '11') ? 'selected' : '' ?>
                                                                value="11">11</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '12') ? 'selected' : '' ?>
                                                                value="12">12</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '13') ? 'selected' : '' ?>
                                                                value="13">13</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '14') ? 'selected' : '' ?>
                                                                value="14">14</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '15') ? 'selected' : '' ?>
                                                                value="15">15</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '16') ? 'selected' : '' ?>
                                                                value="16">16</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '17') ? 'selected' : '' ?>
                                                                value="17">17</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '18') ? 'selected' : '' ?>
                                                                value="18">18</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '19') ? 'selected' : '' ?>
                                                                value="19">19</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '20') ? 'selected' : '' ?>
                                                                value="20">20</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '21') ? 'selected' : '' ?>
                                                                value="21">21</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '22') ? 'selected' : '' ?>
                                                                value="22">22</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '23') ? 'selected' : '' ?>
                                                                value="23">23</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '24') ? 'selected' : '' ?>
                                                                value="24">24</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '25') ? 'selected' : '' ?>
                                                                value="25">25</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '26') ? 'selected' : '' ?>
                                                                value="26">26</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '27') ? 'selected' : '' ?>
                                                                value="27">27</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '28') ? 'selected' : '' ?>
                                                                value="28">28</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '29') ? 'selected' : '' ?>
                                                                value="29">29</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '30') ? 'selected' : '' ?>
                                                                value="30">30</option>
                                                            <option
                                                                <?= (isset($_GET['ed']) && $_GET['ed'] == '31') ? 'selected' : '' ?>
                                                                value="31">31</option>
                                                        </select>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="cmb-item">
                                                <div class="ni-head">Sort</div>
                                                <div class="nl-item">
                                                    <div class="nli-select">
                                                        <select class="custom-select" name="sort">
                                                            <option
                                                                <?= (!isset($_GET['sort']) || $_GET['sort'] === 'default') ? 'selected' : '' ?>
                                                                value="default">Default</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'recently_added') ? 'selected' : '' ?>
                                                                value="recently_added">Recently Added</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'recently_updated') ? 'selected' : '' ?>
                                                                value="recently_updated">Recently Updated</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'score') ? 'selected' : '' ?>
                                                                value="score">Score</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_az') ? 'selected' : '' ?>
                                                                value="name_az">Name A-Z</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'released_date') ? 'selected' : '' ?>
                                                                value="released_date">Released Date</option>
                                                            <option
                                                                <?= (isset($_GET['sort']) && $_GET['sort'] === 'most_watched') ? 'selected' : '' ?>
                                                                value="most_watched">Most Watched</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="cfc-item cfc-item-large cfc-genre">
                                            <div class="ni-head">Genre</div>
                                            <div class="ni-list">
                                                <input type="hidden" id="f-genre-ids" name="genres" value="">
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('1', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="1">Action</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('2', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="2">Adventure</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('3', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="3">Cars</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('4', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="4">Comedy</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('5', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="5">Dementia</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('6', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="6">Demons</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('8', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="8">Drama</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('9', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="9">Ecchi</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('10', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="10">Fantasy</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('11', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="11">Game</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('35', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="35">Harem</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('13', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="13">Historical</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('14', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="14">Horror</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('44', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="44">Isekai</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('43', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="43">Josei</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('15', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="15">Kids</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('16', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="16">Magic</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('17', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="17">Martial Arts</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('18', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="18">Mecha</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('38', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="38">Military</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('19', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="19">Music</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('7', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="7">Mystery</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('20', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="20">Parody</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('39', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="39">Police</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('40', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="40">Psychological</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('22', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="22">Romance</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('21', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="21">Samurai</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('23', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="23">School</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('24', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="24">Sci-Fi</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('42', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="42">Seinen</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('25', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="25">Shoujo</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('26', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="26">Shoujo Ai</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('27', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="27">Shounen</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('28', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="28">Shounen Ai</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('36', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="36">Slice of Life</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('29', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="29">Space</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('30', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="30">Sports</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('31', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="31">Super Power</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('37', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="37">Supernatural</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('41', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="41">Thriller</div>
                                                <div class="btn btn-sm btn-radius btn-filter-item f-genre-item <?= (isset($_GET['genres']) && in_array('32', explode(',', $_GET['genres']))) ? 'active' : '' ?>"
                                                    data-id="32">Vampire</div>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="cfc-button mt-4">
                                            <button class="btn btn-primary"><strong>Filter</strong></button>
                                            <div class="clearfix"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="block_area-header">
                            <div class="float-left bah-heading">
                                <h2 class="cat-heading">Filter Results</h2>
                            </div>
                            <div class="float-right bah-result">
                                <span style="line-height: 40px;"><?=$totalResults?> results</span>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                            <div class="film_list-wrap">
                                <?php $hasqtipCounter = 0; ?>
                                <?php if ($query && !empty($searchResults)): ?>
                                <?php foreach ($searchResults as $anime): ?>
                                <div class="flw-item">
                                    <div class="film-poster">
                                        <?php if ($anime['adultContent']) { ?>
                                        <div class="tick tick-rate">18+</div>
                                        <?php } ?>
                                        <div class="tick ltr">
                                            <?php if (!empty($anime['tvInfo']['sub'])) { ?>
                                            <div class="tick-item tick-sub">
                                                <i class="fas fa-closed-captioning"></i>
                                                &nbsp;<?=$anime['tvInfo']['sub']?>
                                            </div>
                                            <?php } ?>
                                            <?php if (!empty($anime['tvInfo']['dub'])) { ?>
                                            <div class="tick-item tick-dub">
                                                <i class="fas fa-microphone"></i> &nbsp;<?=$anime['tvInfo']['dub']?>
                                            </div>
                                            <?php } ?>
                                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <img loading="lazy" decoding="async" class="film-poster-img lazyload" data-src="<?=$anime['poster']?>"
                                            src="<?=$websiteUrl?>/public/images/no_poster.jpg"
                                            alt="<?=$anime['title']?>">
                                        <a class="film-poster-ahref item-qtip" href="/<?=$anime['id']?>"
                                           data-id="<?= $anime['data_id']; ?>"
                                           data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                           aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"
                                            title="<?=$anime['title']?>">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    </div>
                                    <div class="film-detail">
                                        <h3 class="film-name">
                                            <a href="/<?=$anime['id']?>"><?=$anime['title']?></a>
                                        </h3>
                                        <div class="fd-infor">
                                            <span class="fdi-item"><?=$anime['tvInfo']['showType']?></span>
                                            <span class="dot"></span>
                                            <span class="fdi-item"><?=$anime['tvInfo']['duration']?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php $hasqtipCounter++; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                     <?php if ($totalPages > 1): ?>
                    <div class="pre-pagination mt-5 mb-5">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-lg justify-content-center">
                                <?php
        // Clone all current query params
        $baseParams = $_GET;
        $range = 2;
        $start = max(1, $currentPage - $range);
        $end   = min($totalPages, $currentPage + $range);
        // First + Previous
        if ($currentPage > 1):
        	if ($totalPages >= 3):
        		$baseParams['page'] = 1;
        		$firstLink = '?' . http_build_query($baseParams);
        ?>
                                
                                <li class="page-item">
                                    <a class="page-link" href="<?= $firstLink ?>" title="First">«</a>
                                </li>
                                <?php endif; ?>
                                <?php
                $baseParams['page'] = $currentPage - 1;
                $prevLink = '?' . http_build_query($baseParams);
            ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $prevLink ?>" title="Previous">‹</a>
                                </li>
                                <?php endif; ?>
                                <!-- Numeric range -->
                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                <?php
                    $baseParams['page'] = $i;
                    $pageLink = '?' . http_build_query($baseParams);
                ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pageLink ?>" title="Page <?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <!-- Next + Last -->
                                <?php if ($currentPage < $totalPages): ?>
                                <?php
                    $baseParams['page'] = $currentPage + 1;
                    $nextLink = '?' . http_build_query($baseParams);
                ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $nextLink ?>" title="Next">›</a>
                                </li>
                                <?php if ($totalPages >= 3):
                    $baseParams['page'] = $totalPages;
                    $lastLink = '?' . http_build_query($baseParams);
                ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $lastLink ?>" title="Last">»</a>
                                </li>
                                <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons' async='async'></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
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
	</script>
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
<?php 
require_once('src/component/anime/qtip.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php'; 

error_reporting(E_ALL); 
ini_set('display_errors', 1); 

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
} 

// Base API URL from config 
define('BASE_API_URL', $zpi); 
$endpoint = '/home'; 
$apiUrl = BASE_API_URL . $endpoint; 

// Fetch the live API response directly instead of using cache
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// If API call failed or response structure is invalid 
if (!$data || empty($data['success']) || empty($data['results'])) { 
    http_response_code(503); 
    echo "Service temporarily unavailable."; 
    exit; 
} 

// Use just the results like before 
$data = $data['results']; 
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title><?= $websiteTitle ?> – Watch Free Anime Online in English Sub & Dub</title>
    <meta name="description" content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>. No registration required!">
    <meta name="keywords" content="anime to watch, watch anime, anime online, free anime online, online anime, anime streaming, stream anime online, english anime, english dubbed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/home" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/home">
    <meta property="og:title" content="<?= $websiteTitle ?> – Watch Free Anime Online in English Sub & Dub">
    <meta property="og:description" content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Card Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $websiteTitle ?> – Watch Free Anime Online in English Sub & Dub">
    <meta name="twitter:description" content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
    <meta name="twitter:image" content="<?= $banner ?>">

    <!-- Favicons & App Manifests -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=<?= $version ?>" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=<?= $version ?>" sizes="any" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=<?= $version ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=<?= $version ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons-192.png?v=<?= $version ?>"> 
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icons-512.png?v=<?= $version ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png?v=<?= $version ?>">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg?v=<?= $version ?>" color="#5bbad5">
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
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
    .pizza {
        margin: 2rem auto;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: .6rem;
        text-align: center;
        color: #000;
        font-size: 16px;
        font-weight: 400;
        background-color: #FAACA8;
        background-image: linear-gradient(19deg, #FAACA8 0%, #DDD6F3 100%);
    }

    .pizza a {
        color: #000;
        font-weight: 500;
        text-shadow: 0 1px 0 #fff;
    }

    .pizza a:hover {
        text-shadow: 0 1px 3px #fff;
    }

    .pizza-x {
        max-width: 800px;
        width: calc(100% - 32px);
        min-height: 90px;
    }

    .pizza-y {
        max-width: 400px;
        min-height: 300px;
    }

    .pizza .in-text {
        font-size: 1.5em;
        font-weight: 600;
    }

    .pizza .in-contact {
        font-size: 1em;
    }

    @media screen and (max-width: 479px) {
        .pizza {
            font-size: 13px;
            gap: .6rem
        }

        .pizza-y {
            min-height: 200px;
        }
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
                                    <img src="/images/pick-home.svg" alt="Home" width="20" height="20" data-toggle="tooltip" data-placement="right"
                                        title="Home">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-movies">
                                <a class="pmu-item-icon" href="/movie" title="Movies">
                                    <img src="/images/pick-movies.svg" alt="Movies" width="20" height="20" data-toggle="tooltip" data-placement="right"
                                        title="Movies">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-show">
                                <a class="pmu-item-icon" href="/tv" title="TV Series">
                                    <img src="/images/pick-show.svg" alt="TV Series" width="20" height="20" data-toggle="tooltip" data-placement="right"
                                        title="TV Series">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-popular">
                                <a class="pmu-item-icon" href="/most-popular" title="Most Popular">
                                    <img src="/images/pick-popular.svg" alt="Most Popular" width="20" height="20" data-toggle="tooltip" data-placement="right"
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
        <?php include('./src/component/anime/slidebar.php'); ?>
        <?php $hasqtipCounter = 0; ?>
        <?php include('./src/component/anime/trending.php') ?>
        <div class="share-buttons share-buttons-home">
            <div class="container">
                <div class="share-buttons-block">
                    <div class="share-icon"></div>
                    <div class="sbb-title mr-3">
                        <span>Share <?= $websiteTitle ?></span>
                        <p class="mb-0">to your friends</p>
                    </div>
                    <div class="sharethis-inline-share-buttons"></div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <?php $showHomeComments = (!isset($_COOKIE['showCommentsAtHome']) || $_COOKIE['showCommentsAtHome'] !== '0'); ?>
        <div id="discussion" class="<?= $showHomeComments ? '' : 'ds-hide' ?>">
            <div class="container">
                <div class="dis-wrap dis-model">
                    <div class="d_w-icon"><picture><source srcset="/images/discussion.webp" type="image/webp"><img src="/images/discussion.png" width="320" height="240" alt="Discussion" loading="lazy" decoding="async"></picture></div>
                    <div class="d_w-list">
                        <div class="display-toggle">
                            <div class="to-text">Show Comments</div>
                            <div id="display-comment" class="toggle-onoff to-small <?= $showHomeComments ? '' : 'off' ?>"><span></span></div>
                        </div>
                        <ul class="nav nav-tabs pre-tabs pre-tabs-min">
                            <li class="nav-item">
                                <a data-toggle="tab" href="#new-comment" class="nav-link cm-tab active show">Newest
                                    Comments</a>
                            </li>
                            <li class="nav-item">
                                <a data-toggle="tab" href="#top-comment" class="nav-link cm-tab">Top Comments</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="comment-widget">
                            <!-- AJAX loads comments here -->
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div id="anime-featured">
            <?php include('./src/component/anime/apfc.php'); ?>
        </div>
        <div id="main-wrapper">
            <div class="container">
                <div id="main-content">
                    <div id="widget-continue-watching"></div>
                    <?php include('./src/component/anime/thisSeason.php'); ?>
                    <?php include('./src/component/anime/lastSeason.php'); ?>
                    <div class="clearfix"></div>
                    <div id="schedule-block"></div>
                    <?php include('./src/component/anime/upcoming.php'); ?>
                </div>
                <div id="main-sidebar">
                    <section class="block_area block_area_sidebar block_area-genres">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Genres</h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block_area-content">
                            <div class="cbox cbox-genres">
                                <ul class="ulclear color-list sb-genre-list sb-genre-less">
                                    <li class="nav-item"> <a class="nav-link" href="/genre/action"
                                            title="Action">Action</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/adventure"
                                            title="Adventure">Adventure</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/cars" title="Cars">Cars</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/comedy"
                                            title="Comedy">Comedy</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/dementia"
                                            title="Dementia">Dementia</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/demons"
                                            title="Demons">Demons</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/drama"
                                            title="Drama">Drama</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/ecchi"
                                            title="Ecchi">Ecchi</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/fantasy"
                                            title="Fantasy">Fantasy</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/game" title="Game">Game</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/harem"
                                            title="Harem">Harem</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/historical"
                                            title="Historical">Historical</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/horror"
                                            title="Horror">Horror</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/Isekai"
                                            title="Isekai">Isekai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/josei"
                                            title="Josei">Josei</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/kids" title="Kids">Kids</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/magic"
                                            title="Magic">Magic</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/martial-arts"
                                            title="Martial Arts">Martial
                                            Arts</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/mecha"
                                            title="Mecha">Mecha</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/military"
                                            title="Military">Military</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/music"
                                            title="Music">Music</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/mystery"
                                            title="Mystery">Mystery</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/parody"
                                            Title="Parody">Parody</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/police"
                                            title="Police">Police</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/psychological"
                                            title="Psychological">Psychological</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/romance"
                                            title="Romance">Romance</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/samurai"
                                            title="Samurai">Samurai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/school"
                                            title="School">School</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/sci-fi"
                                            title="Sci-Fi">Sci-Fi</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/seinen"
                                            title="Seinen">Seinen</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/shoujo"
                                            title="Shoujo">Shoujo</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/shoujo-ai"
                                            title="Shoujo Ai">Shoujo Ai</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/shounen"
                                            title="Shounen">Shounen</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/shounen-Ai"
                                            title="Shounen Ai">Shounen
                                            Ai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/slice-of-life"
                                            title="Slice of Life">Slice
                                            of Life</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/space"
                                            title="Space">Space</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/sports"
                                            title="Sports">Sports</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/super-power"
                                            title="Super Power">Super
                                            Power</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/supernatural"
                                            title="Supernatural">Supernatural</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/thriller"
                                            title="Thriller">Thriller</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="/genre/vampire"
                                            title="Vampire">Vampire</a> </li>
                                </ul>
                                <div class="clearfix"></div>
                                <button class="btn btn-sm btn-block btn-showmore mt-2"></button>
                            </div>
                        </div>
                    </section>
                    <section class="block_area block_area_sidebar block_area-realtime">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-2">
                                <h2 class="cat-heading">Top 10</h2>
                            </div>
                            <div class="float-right bah-tab-min">
                                <ul class="nav nav-pills nav-fill nav-tabs anw-tabs">
                                    <li class="nav-item"><a data-toggle="tab" href="#top-viewed-day"
                                            class="nav-link active">Today</a>
                                    </li>
                                    <li class="nav-item"><a data-toggle="tab" href="#top-viewed-week"
                                            class="nav-link">Week</a></li>
                                    <li class="nav-item"><a data-toggle="tab" href="#top-viewed-month"
                                            class="nav-link">Month</a></li>
                                </ul>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block_area-content">
                            <div class="cbox cbox-list cbox-realtime">
                                <div class="cbox-content">
                                    <div class="tab-content">
                                        <div id="top-viewed-day" class="anif-block-ul anif-block-chart tab-pane active">
                                            <ul class="ulclear">
                                                <?php foreach ($data['topTen']['today'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span></div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>"
                                                            loading="lazy" decoding="async">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/<?php echo $anime['id']; ?>"
                                                                title="<?php echo $anime['japanese_title']; ?>"
                                                                class="dynamic-name"
                                                                data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                                                data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>">
                                                                <?php echo htmlspecialchars($anime['title']); ?>
                                                            </a>
                                                        </h3>
                                                        <div class="fd-infor">
                                                            <div class="tick">
                                                                <?php if (isset($anime['tvInfo']['sub'])): ?>
                                                                <div class="tick-item tick-sub">
                                                                    <i
                                                                        class="fas fa-closed-captioning mr-1"></i><?php echo $anime['tvInfo']['sub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['dub'])): ?>
                                                                <div class="tick-item tick-dub">
                                                                    <i
                                                                        class="fas fa-microphone mr-1"></i><?php echo $anime['tvInfo']['dub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['eps'])): ?>
                                                                <div class="tick-item tick-eps">
                                                                    <?php echo $anime['tvInfo']['eps']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                                <?php $hasqtipCounter++; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div id="top-viewed-week" class="anif-block-ul anif-block-chart tab-pane">
                                            <ul class="ulclear">
                                                <?php foreach ($data['topTen']['week'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span></div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>"
                                                            loading="lazy" decoding="async">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/<?php echo $anime['id']; ?>"
                                                                title="<?php echo $anime['japanese_title']; ?>"
                                                                class="dynamic-name"
                                                                data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                                                data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>">
                                                                <?php echo htmlspecialchars($anime['title']); ?>
                                                            </a>
                                                        </h3>
                                                        <div class="fd-infor">
                                                            <div class="tick">
                                                                <?php if (isset($anime['tvInfo']['sub'])): ?>
                                                                <div class="tick-item tick-sub">
                                                                    <i
                                                                        class="fas fa-closed-captioning mr-1"></i><?php echo $anime['tvInfo']['sub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['dub'])): ?>
                                                                <div class="tick-item tick-dub">
                                                                    <i
                                                                        class="fas fa-microphone mr-1"></i><?php echo $anime['tvInfo']['dub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['eps'])): ?>
                                                                <div class="tick-item tick-eps">
                                                                    <?php echo $anime['tvInfo']['eps']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                                <?php $hasqtipCounter++; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div id="top-viewed-month" class="anif-block-ul anif-block-chart tab-pane">
                                            <ul class="ulclear">
                                                <?php foreach ($data['topTen']['month'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span></div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>"
                                                            loading="lazy" decoding="async">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/<?php echo $anime['id']; ?>"
                                                                title="<?php echo $anime['japanese_title']; ?>"
                                                                class="dynamic-name"
                                                                data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                                                data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>">
                                                                <?php echo htmlspecialchars($anime['title']); ?>
                                                            </a>
                                                        </h3>
                                                        <div class="fd-infor">
                                                            <div class="tick">
                                                                <?php if (isset($anime['tvInfo']['sub'])): ?>
                                                                <div class="tick-item tick-sub">
                                                                    <i
                                                                        class="fas fa-closed-captioning mr-1"></i><?php echo $anime['tvInfo']['sub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['dub'])): ?>
                                                                <div class="tick-item tick-dub">
                                                                    <i
                                                                        class="fas fa-microphone mr-1"></i><?php echo $anime['tvInfo']['dub']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                <?php if (isset($anime['tvInfo']['eps'])): ?>
                                                                <div class="tick-item tick-eps">
                                                                    <?php echo $anime['tvInfo']['eps']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                                <?php $hasqtipCounter++; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php
                    if (!function_exists('e')) {
                        function e($s) {
                            return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        }
                    }

function timeAgo($datetime) {
    $ts = strtotime($datetime);
    if (!$ts) return '';
    $t = time() - $ts;
    if ($t < 60)    return $t . ' seconds ago';
    if ($t < 3600)  return floor($t / 60) . ' minutes ago';
    if ($t < 86400) return floor($t / 3600) . ' hours ago';
    return floor($t / 86400) . ' days ago';
}

$tagsMap = [
    1 => ['name'=>'Suggestion', 'class'=>'zt-green'],
    2 => ['name'=>'Question',   'class'=>'zt-yellow'],
    3 => ['name'=>'Discussion', 'class'=>'zt-purple'],
    4 => ['name'=>'General',    'class'=>'zt-blue'],
    5 => ['name'=>'Updates',    'class'=>'zt-red'],
    6 => ['name'=>'Feedback',   'class'=>'zt-orange'],
];

function getTrendingPosts24h(mysqli $conn, int $limit = 6): array
{
    $sql = "
        SELECT
            p.id,
            p.user_id,
            p.title,
            p.content,
            p.tags_id,
            p.status,
            p.like_count,
            p.dislike_count,
            p.created_at,
            p.slug,
            u.username,
            u.image,
            u.points,
            u.roles,
            COUNT(DISTINCT pc.id) AS comment_count,
            (
                COUNT(DISTINCT pc.id) * 2
                + (p.like_count - p.dislike_count)
                + COALESCE(SUM(
                    CASE 
                        WHEN pcv.type = 1 THEN 1
                        WHEN pcv.type = 0 THEN -1
                        ELSE 0
                    END
                ), 0)
            ) AS trending_score
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN post_comments pc
            ON pc.post_id = p.id
           AND pc.status = 'visible'
        LEFT JOIN post_comment_votes pcv
            ON pcv.comment_id = pc.id
        WHERE p.status = 'published'
          AND p.created_at >= (NOW() - INTERVAL 1 DAY)
        GROUP BY p.id
        ORDER BY trending_score DESC, p.created_at DESC
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

$trendingPosts = cacheRemember(
    "trending_24h_6",
    300, // 5 minutes
    function () use ($conn) {
        return getTrendingPosts24h($conn, 6);
    }
);
?>
                    <section class="block_area block_area_sidebar block_area-connecting">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-2">
                                <h2 class="cat-heading">Trending Posts</h2>
                            </div>
                            <div class="float-right viewmore">
                                <a class="btn" href="/community/board" title="View more">
                                    View more<i class="fas fa-angle-right ml-2"></i>
                                </a>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="block_area-content">
                            <div class="zr-connect zr-connect-list">
                                <div class="connecting-list">

                                    <?php if ($trendingPosts): ?>
                                    <?php foreach ($trendingPosts as $post): ?>
                                    <?php
                            $postId   = (int)$post['id'];
                            $slug     = e($post['slug'] ?? '');
                            $title    = e($post['title'] ?? 'Untitled');
                            $url      = '/community/post/' . $slug;
                            $created  = $post['created_at'] ?? '';
                            $timeText = $created ? timeAgo($created) : '';

                            $tagsId   = (int)($post['tags_id'] ?? 0);
                            $tagInfo  = $tagsMap[$tagsId] ?? ['name' => 'General', 'class' => 'zt-blue'];
                            $tagName  = $tagInfo['name'];
                            $tagClass = $tagInfo['class'];

                            $commentCount = (int)($post['comment_count'] ?? 0);
                            $body = $post['content'] ?? '';

                            $userId   = (int)$post['user_id'];
                            $username = e($post['username'] ?? 'Unknown');
                            $avatar   = e(fix_avatar_url($post['image'] ?: '/public/images/no-avatar.jpeg'));
                            $points   = (int)($post['points'] ?? 0);
                            $role     = (int)($post['roles'] ?? 0);

                            // Rank / badge
                            if ($role === 1) {
                                $rankName   = 'Admin';
                                $badgeClass = 'level-b';
                            } elseif ($role === 2) {
                                $rankName   = 'Mod';
                                $badgeClass = 'level-d';
                            } else {
                                list($rankName, $badgeClass, $rankIcon) = getRank($points);
                            }

                            // Username classes
                            if ($role === 1) {
                                $userNameClasses = 'user-name is-level-x is-level-b';
                            } elseif ($role === 2) {
                                $userNameClasses = 'user-name is-level-x is-level-d';
                            } else {
                                $userNameClasses = 'user-name is-level-x' . (!empty($badgeClass) ? ' is-level-a' : '');
                            }
                        ?>

                                    <div class="item">
                                        <div class="gi-top d-flex justify-content-between align-items-center">
                                            <div class="ztag">
                                                <span class="<?= e($tagClass) ?> mr-2">#<?= e($tagName) ?></span>
                                                <?php if ($timeText): ?>
                                                <div class="time d-inline">
                                                    <span><i class="dot mr-2"></i><?= e($timeText) ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="gi-stats d-flex align-items-center">
                                                <div class="ml-4">
                                                    <i class="fas fa-comment mr-1"></i><?= $commentCount ?>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="item-name">
                                            <a href="<?= $url ?>" title="<?= $title ?>"><?= $title ?></a>
                                        </h4>

                                        <div class="subject">
                                            <div><?= nl2br(e($body)) ?></div>
                                        </div>

                                        <div class="cn-owner">
                                            <div class="profile-avatar">
                                                <img src="<?= $avatar ?>" alt="<?= $username ?>">
                                            </div>

                                            <a href="/community/user/<?= $userId ?>" target="_blank"
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
                                    </div>

                                    <?php endforeach; ?>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script> 
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js?v=<?= $versionJs2 ?>');
        });
    }
    window.addEventListener('load', function () {
        setTimeout(function() {
            var st = document.createElement('script');
            st.src = 'https://platform-api.sharethis.com/js/sharethis.js#property=6973d5aa6473557d10606f1e&product=sop';
            st.async = true;
            document.body.appendChild(st);
        }, 1000);
    });
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
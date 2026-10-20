<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['userID'] ?? null;
if (!$user_id) {
    header("Location: /home");
    exit();
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
    <title>My Community Posts - <?= $websiteTitle ?></title>
    <meta name="description" content="Manage and view your community posts and threads on <?= $websiteTitle ?>.">
    <meta name="keywords" content="my posts, community, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/community/my-post" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/community/my-post">
    <meta property="og:title" content="My Community Posts - <?= $websiteTitle ?>">
    <meta property="og:description" content="Manage and view your community posts and threads on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="My Community Posts - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Manage and view your community posts and threads on <?= $websiteTitle ?>.">
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
                <div class="award-bg-img" style="background-image: url(../images/community/cover.jpg);"></div>
            </div>
            <!--Begin: award-->
            <div class="container">
                <div class="award-inner">
                    <div class="ai_-tabs">
                        <a href="/community/super-x" class="" title="Super X">Super X</a>
                        <a href="/community/board" class="active" title="Z Community">Z Community</a>
                        <a href="/community/my-zone" class="" title="My Zone">My Zone</a>
                    </div>
                    <div class="ai_-welcome text-center pb-0">
                        <div class="d-block">My Posts</div>
                    </div>
                    <div class="new-connect">
                        <div class="nc-wrap">
                            <div class="nc-sidebar">
                                <div class="nc-cate">
                                    <a class="item" href="/community/my-post">
                                        <div class="ztag">
                                            <span class="zt-white <?= (($_GET['tags'] ?? 'all') === 'all') ? 'active' : '' ?>">#All</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=updates">
                                        <div class="ztag">
                                            <span class="zt-red <?= (($_GET['tags'] ?? 'all') === 'updates') ? 'active' : '' ?>">#Updates</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=general">
                                        <div class="ztag">
                                            <span class="zt-blue <?= (($_GET['tags'] ?? 'all') === 'general') ? 'active' : '' ?>">#General</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=suggestion">
                                        <div class="ztag">
                                            <span class="zt-green <?= (($_GET['tags'] ?? 'all') === 'suggestion') ? 'active' : '' ?>">#Suggestion</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=question">
                                        <div class="ztag">
                                            <span class="zt-yellow <?= (($_GET['tags'] ?? 'all') === 'question') ? 'active' : '' ?>">#Question</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=discussion">
                                        <div class="ztag">
                                            <span class="zt-purple <?= (($_GET['tags'] ?? 'all') === 'discussion') ? 'active' : '' ?>">#Discussion</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                    <a class="item" href="?tags=feedback">
                                        <div class="ztag">
                                            <span class="zt-orange <?= (($_GET['tags'] ?? 'all') === 'feedback') ? 'active' : '' ?>">#Feedback</span>
                                        </div>
                                        <div class="number"></div>
                                    </a>
                                </div>
                            </div>
                            <div class="nc-body">
                                <div class="nc-head">
                                    <div class="is-left">
                                        <div class="zcate position-relative mr-3 d-hide">
                                            <a class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <span id="currentTag"></span>
                                                <i class="fas fa-sort ml-2"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal">
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'all') ? 'active' : '' ?>"
                                                   href="/community/my-post">
                                                    #All
                                                    <?= (($_GET['tags'] ?? 'all') === 'all') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'updates') ? 'active' : '' ?>"
                                                   href="?tags=updates">
                                                    #Updates
                                                    <?= (($_GET['tags'] ?? 'all') === 'updates') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'general') ? 'active' : '' ?>"
                                                   href="?tags=general">
                                                    #General
                                                    <?= (($_GET['tags'] ?? 'all') === 'general') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'suggestion') ? 'active' : '' ?>"
                                                   href="?tags=suggestion">
                                                    #Suggestion
                                                    <?= (($_GET['tags'] ?? 'all') === 'suggestion') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'question') ? 'active' : '' ?>"
                                                   href="?tags=question">
                                                    #Question
                                                    <?= (($_GET['tags'] ?? 'all') === 'question') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'discussion') ? 'active' : '' ?>"
                                                   href="?tags=discussion">
                                                    #Discussion
                                                    <?= (($_GET['tags'] ?? 'all') === 'discussion') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                                <a class="dropdown-item p-tag <?= (($_GET['tags'] ?? 'all') === 'feedback') ? 'active' : '' ?>"
                                                   href="?tags=feedback">
                                                    #Feedback
                                                    <?= (($_GET['tags'] ?? 'all') === 'feedback') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="zsort position-relative">
                                            <a class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Sort by<i class="fas fa-sort ml-2"></i>
                                            </a>
                                        
                                            <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal">
                                        
                                                <a class="dropdown-item <?= (($_GET['sort'] ?? 'newest') === 'newest') ? 'active' : '' ?>"
                                                   href="/community/my-post?tags=<?= htmlspecialchars($_GET['tags'] ?? '') ?>&sort=newest">
                                                    Newest <?= (($_GET['sort'] ?? 'newest') === 'newest') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                        
                                                <a class="dropdown-item <?= (($_GET['sort'] ?? 'newest') === 'oldest') ? 'active' : '' ?>"
                                                   href="/community/my-post?tags=<?= htmlspecialchars($_GET['tags'] ?? '') ?>&sort=oldest">
                                                    Oldest <?= (($_GET['sort'] ?? 'newest') === 'oldest') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                        
                                                <a class="dropdown-item <?= (($_GET['sort'] ?? 'newest') === 'top') ? 'active' : '' ?>"
                                                   href="/community/my-post?tags=<?= htmlspecialchars($_GET['tags'] ?? '') ?>&sort=top">
                                                    Top <?= (($_GET['sort'] ?? 'newest') === 'top') ? '<i class="fas fa-check ml-2"></i>' : '' ?>
                                                </a>
                                        
                                            </div>
                                        </div>
                                    </div>
                                    <div class="is-right">
                                        <div class="is-create">
                                            <a href="/community/board" class="item-link">
                                                <i class="fas fa-chevron-left"></i>
                                                <span class="ml-2">Back</span>
                                            </a>
                                        </div>
                                        <div class="is-create">
                                            <a href="/community/new-post" class="item-link active">
                                                <i class="fas fa-plus"></i>
                                                <span class="ml-2">Create</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="nc-list">
                                    <div class="post-list" data-tags="" data-sort="newest"></div>
                                    <div class="loading-relative" id="loading-data">
                                        <div class="loading">
                                            <div class="span1"></div>
                                            <div class="span2"></div>
                                            <div class="span3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    <script type="text/javascript"
        src="https://platform-api.sharethis.com/js/sharethis.js#property=64a3d891df473b0019d1b0da&product=inline-share-buttons&source=platform"
        async="async"></script>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"
        integrity="sha384-/KNQL8Nu5gCHLqwqfQjA689Hhoqgi2S84SNUxC3roTe4EhJ9AfLkp8QiQcU8AMzI" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
require_once('src/component/anime/qtip.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Discord token fetch removed (RPC disabled)
$user_id = $_SESSION['userID'] ?? null;

if (!defined('API_CACHE_TTL')) {
    define('API_CACHE_TTL', 600); // 10 minutes
}

// --- Mark notification as read if opened from notification link ---
$n_id = $_GET['n_id'] ?? null;
if ($user_id && $n_id) {
    // Use a unique identifier in n_id (as in your notification link, example: &n_id=69138042a4848c2b8ef4ee89)
    $sql = "UPDATE notifications SET is_read=1 WHERE user_id=? AND link LIKE ?";
    $like = "%n_id=" . $conn->real_escape_string($n_id) . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $like);
    $stmt->execute();
    $stmt->close();
}

$requestUri = $_SERVER['REQUEST_URI'];
$streaming = preg_replace('#^/watch/#', '', $requestUri);
$parts = explode('?', $streaming);
$animeId = $parts[0];
$queryParams = [];
parse_str($parts[1] ?? '', $queryParams);
$episodeId = isset($queryParams['ep']) ? (string)$queryParams['ep'] : '';

// Fetch anime data (this must run before our schedule lookup)
$animeData = fetchAnimeData($animeId);

// retry once using numeric ID if present in slug (optional but recommended)
if (!$animeData && preg_match('/-(\d+)$/', $animeId, $m)) {
    $animeData = fetchAnimeData((int)$m[1]);
}

// FINAL FAIL → show 404
if (!$animeData) {
    http_response_code(404);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
    exit;
}

$status = strtolower($animeData['status'] ?? $animeData['Status'] ?? '');
if ($status === 'not yet released' || $status === 'NOT_YET_RELEASED' || $status === 'upcoming') {
    header("Location: /$animeId");
    exit;
}
if ($animeId) {
    $apiUrl   = rtrim($zpi, '/') . '/schedule/' . urlencode($animeId);
    $cacheKey = 'schedule_' . $animeId;

    // Use global cached API helper (handles DB cache + TTL + stale fallback)
    $data = getCachedApiJson($cacheKey, $apiUrl, 3600);

    if (!empty($data['success']) && !empty($data['results']['nextEpisodeSchedule'])) {
        try {
            $dt = new DateTime($data['results']['nextEpisodeSchedule'], new DateTimeZone('UTC'));
            $dt->modify('+6 hours'); // same shift logic as before
            $nextEpisodeDate = $dt;
        } catch (Throwable $e) {
            $nextEpisodeDate = null;
        }
    }
}
$parts = parse_url($_SERVER['REQUEST_URI']);
$page_url = explode('/', $parts['path']);
$url = end($page_url);
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />

    <!-- SEO Meta Tags -->
    <title>Watch <?= htmlspecialchars($animeData['title']) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars($episodeId) : '' ?> English Sub & Dub online Free â€“ <?= $websiteTitle ?></title>
    <meta name="description" content="Watch <?= htmlspecialchars($animeData['title']) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars($episodeId) : '' ?> in English sub and dub. Stream free anime online in HD quality with fast players on <?= $websiteTitle ?>. No registration required!"/>
    <meta name="keywords" content="watch <?= htmlspecialchars($animeData['title']) ?><?= $episodeId !== '' ? ' episode ' . htmlspecialchars($episodeId) : '' ?>, watch <?= htmlspecialchars($animeData['title']) ?><?= $episodeId !== '' ? ' episode ' . htmlspecialchars($episodeId) : '' ?> english sub, <?= htmlspecialchars($animeData['title']) ?> dub, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/watch/<?= htmlspecialchars($animeId) ?><?= $episodeId !== '' ? '?ep=' . htmlspecialchars($episodeId) : '' ?>" />

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
    <meta property="og:type" content="video.episode" />
    <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>"/>
    <meta property="og:title" content="Watch <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars((string)$episodeId) : '' ?> English Sub/Dub online Free on <?= htmlspecialchars((string)($websiteTitle ?? '')) ?>"/>
    <meta property="og:image" content="<?= !empty($animeData['banner']) ? htmlspecialchars((string)$animeData['banner']) : htmlspecialchars((string)($banner ?? '')) ?>"/>
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:description" content="Watch <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars((string)$episodeId) : '' ?> in English sub and dub. Stream free anime online in HD quality with fast players on <?= htmlspecialchars((string)($websiteTitle ?? '')) ?>."/>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Watch <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars((string)$episodeId) : '' ?> English Sub/Dub online Free on <?= htmlspecialchars((string)($websiteTitle ?? '')) ?>">
    <meta name="twitter:description" content="Watch <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?><?= $episodeId !== '' ? ' Episode ' . htmlspecialchars((string)$episodeId) : '' ?> in English sub and dub. Stream free anime online in HD quality on <?= htmlspecialchars((string)($websiteTitle ?? '')) ?>.">
    <meta name="twitter:image" content="<?= !empty($animeData['banner']) ? htmlspecialchars((string)$animeData['banner']) : htmlspecialchars((string)($banner ?? '')) ?>">

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
    <div id="wrapper" data-id="<?= htmlspecialchars($animeId) ?>" data-page="watch">
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
        <div id="main-wrapper" class="layout-page layout-page-detail layout-page-watchtv">
            <div id="ani_detail">
                <div class="ani_detail-stage">
                    <div class="container">
                        <div class="anis-cover-wrap">
                            <div class="anis-cover"
                                style="background-image: url('<?= htmlspecialchars((string)($animeData['poster'] ?? '')) ?>')"></div>
                        </div>
                        <div class="anis-watch-wrap">
                            <div class="prebreadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/home" title="Home">Home</a></li>
                                        <li class="breadcrumb-item"><a
                                                href="/<?= strtolower($animeData['showType'] ?? 'tv') ?>"><?= strtoupper($animeData['showType'] ?? 'TV') ?></a>
                                        </li>
                                        <li class="breadcrumb-item dynamic-name active"
                                            data-title="<?= htmlspecialchars((string)($animeData['title'] ?? '')) ?>"
                                            data-jname="<?= htmlspecialchars((string)($animeData['japa'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?></li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="anis-watch anis-watch-tv">
                                <div class="watch-player">
                                    <div class="player-frame">
                                        <div class="loading-relative loading-box" id="embed-loading">
                                            <div class="loading">
                                                <div class="span1"></div>
                                                <div class="span2"></div>
                                                <div class="span3"></div>
                                            </div>
                                        </div>
                                        <iframe id="iframe-embed" src="about:blank" frameborder="0" referrerpolicy="strict-origin"
                                            allow="autoplay; fullscreen; geolocation; display-capture; picture-in-picture"
                                            webkitallowfullscreen mozallowfullscreen></iframe>
                                    </div>
                                    <div class="player-controls">
                                        <div class="pc-item pc-resize">
                                            <a href="javascript:;" id="media-resize" class="btn btn-sm"><i
                                                    class="fas fa-expand mr-1"></i>Expand</a>
                                        </div>
                                        <div class="pc-item pc-toggle pc-light">
                                            <div id="turn-off-light" class="toggle-basic">
                                                <span class="tb-name"><i class="fas fa-lightbulb mr-2"></i>Light</span>
                                                <span class="tb-result"></span>
                                            </div>
                                        </div>
                                        <div class="pc-item pc-toggle pc-autoplay">
                                            <div class="toggle-basic quick-settings off" data-option="auto_play">
                                                <span class="tb-name">Auto Play</span>
                                                <span class="tb-result"></span>
                                            </div>
                                        </div>
                                        <div class="pc-item pc-toggle pc-autonext">
                                            <div class="toggle-basic quick-settings off" data-option="auto_next">
                                                <span class="tb-name">Auto Next</span>
                                                <span class="tb-result"></span>
                                            </div>
                                        </div>
                                        <div class="pc-item pc-toggle pc-autoskip">
                                            <div class="toggle-basic quick-settings off" data-option="auto_skip_intro">
                                                <span class="tb-name">Auto Skip</span>
                                                <span class="tb-result"></span>
                                            </div>
                                        </div>
                                        <div class="pc-right">
                                            <div class="pc-item pc-control block-prev" style="display:block;">
                                                <a class="btn btn-sm btn-prev" href="javascript:;"
                                                    onclick="prevEpisode()"><i class="fas fa-backward mr-2"></i>Prev</a>
                                            </div>
                                            <div class="pc-item pc-control block-next" style="display:block;">
                                                <a class="btn btn-sm btn-next" href="javascript:;"
                                                    onclick="nextEpisode()">Next<i class="fas fa-forward ml-2"></i></a>
                                            </div>
                                            <div class="pc-item pc-fav" id="watch-list-content"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                <div class="player-servers">
                                    <div id="servers-content"></div>
                                </div>
                                <?php if ($nextEpisodeDateRaw = ($data['results']['nextEpisodeSchedule'] ?? null)): ?>
                                <div class="schedule-alert">
                                    <div class="alert small">
                                        <button type="button" class="close" data-dismiss="alert"><span
                                                aria-hidden="true">Ã—</span></button>
                                        <span class="icon-16 mr-1">ðŸš€</span> Estimated the next episode will come at
                                        <span data-value="<?= htmlspecialchars($nextEpisodeDateRaw) ?>"
                                            id="schedule-date">
                                        </span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($animeData['season']) && is_array($animeData['season'])): ?>
                                <div class="other-season">
                                    <div class="inner">
                                        <div class="os-title">Watch more seasons of this anime</div>
                                        <div class="os-list">
                                            <?php foreach ($animeData['season'] as $season): ?>
                                            <a href="<?= htmlspecialchars((string)($websiteUrl ?? '')) ?>/<?= htmlspecialchars((string)($season['id'] ?? '')) ?>"
                                                class="os-item <?= ($season['id'] ?? '') === $animeId ? 'active' : '' ?>"
                                                title="<?= htmlspecialchars((string)($season['title'] ?? '')) ?>">
                                                <div class="title"><?= htmlspecialchars((string)($season['name'] ?? '')) ?></div>
                                                <div class="season-poster"
                                                    style="background-image: url(<?= htmlspecialchars((string)($season['poster'] ?? '')) ?>);">
                                                </div>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php endif; ?>
                                <div id="episodes-content">
                                    <div class="loading-relative">
                                        <div class="loading">
                                            <div class="span1"></div>
                                            <div class="span2"></div>
                                            <div class="span3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="anis-watch-detail">
                                <div class="anis-content">
                                    <div class="anisc-poster">
                                        <div class="film-poster">
                                            <img src="<?= htmlspecialchars($animeData['poster']) ?>"
                                                class="film-poster-img"
                                                alt="<?= htmlspecialchars((string)($animeData['title'] ?? '')) ?>">
                                        </div>
                                    </div>
                                    <div class="anisc-detail">
                                        <h2 class="film-name">
                                            <a href="/<?= htmlspecialchars((string)($animeId ?? '')) ?>"
                                                class="text-white dynamic-name"
                                                data-title="<?= htmlspecialchars((string)($animeData['title'] ?? '')) ?>"
                                                data-jname="<?= htmlspecialchars((string)($animeData['japa'] ?? '')) ?>">
                                                <?= htmlspecialchars((string)($animeData['title'] ?? '')) ?>
                                            </a>
                                        </h2>
                                        <div class="film-stats">
                                            <div class="tick">
                                                <?php if (!empty($animeData['rating'])): ?>
                                                <div class="tick-item tick-pg">
                                                    <?= htmlspecialchars((string)$animeData['rating']) ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($animeData['quality'])): ?>
                                                <div class="tick-item tick-quality">
                                                    <?= htmlspecialchars((string)$animeData['quality']) ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($animeData['subEp'])): ?>
                                                <div class="tick-item tick-sub"><i
                                                        class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars((string)$animeData['subEp']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($animeData['dubEp'])): ?>
                                                <div class="tick-item tick-dub"><i
                                                        class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars((string)$animeData['dubEp']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (isset($animeData['eps']) && $animeData['eps'] !== null && $animeData['eps'] !== ''): ?>
                                                <div class="tick-item tick-eps"><?php echo htmlspecialchars((string)$animeData['eps']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($animeData['showType'])): ?>
                                                <span class="dot"></span>
                                                <span
                                                    class="item"><?php echo htmlspecialchars((string)$animeData['showType']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($animeData['duration'])): ?>
                                                <span class="dot"></span>
                                                <span
                                                    class="item"><?php echo htmlspecialchars((string)$animeData['duration']); ?></span>
                                                <?php endif; ?>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                        <div class="film-description m-hide">
                                            <div class="text">
                                                <?= htmlspecialchars((string)($animeData['overview'] ?? '')) ?>
                                            </div>
                                        </div>
                                        <div class="film-text m-hide mb-3">
    <?= htmlspecialchars((string)($websiteTitle ?? '')) ?> is the best site to watch
    <strong><?= htmlspecialchars((string)($animeData['title'] ?? ($animeData['japa'] ?? ''))) ?></strong> SUB online, or
    you can even watch
    <strong><?= htmlspecialchars((string)($animeData['title'] ?? ($animeData['japa'] ?? ''))) ?></strong> DUB in HD
    quality.

<?php
$studio = null;

if (!empty($animeData['studio'])) {
    if (is_array($animeData['studio'])) {
        $studio = $animeData['studio'][0] ?? null; // first studio only
    } else {
        $studio = $animeData['studio'];
    }
}

$invalidStudios = ['no studio available', 'unknown', 'n/a'];
?>

<?php if ($studio && !in_array(strtolower($studio), $invalidStudios)): ?>
    You can also find
    <a class="name"
       href="/producer/<?= htmlspecialchars(strtolower(str_replace(' ', '-', $studio))) ?>">
        <strong><?= htmlspecialchars($studio) ?></strong>
    </a>
    anime on <?= htmlspecialchars((string)($websiteTitle ?? '')) ?> website.
<?php endif; ?>
</div>
                                        <div class="block">
                                            <a href="/<?= htmlspecialchars((string)($animeData['id'] ?? '')) ?>"
                                                class="btn btn-xs btn-light">View detail</a>
                                        </div>
                                        <div class="dt-rate">
                                            <div id="vote-info">
                                                <!-- Loaded dynamically via AJAX -->
                                            </div>
                                            <div class="loading-relative" id="vote-loading" style="display: none;">
                                                <div class="loading">
                                                    <div class="span1"></div>
                                                    <div class="span2"></div>
                                                    <div class="span3"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="javascript:;" class="dt-comment">
                                            <div class="icon"><i class="fas fa-comment"></i></div>
                                            <div class="number"></div>
                                            <div class="text">Comments</div>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="share-buttons share-buttons-home">
                <div class="container">
                    <div class="share-buttons-block">
                        <div class="share-icon"></div>
                        <div class="sbb-title mr-3">
                            <span>Share <?=$websiteTitle?></span>
                            <p class="mb-0">to your friends</p>
                        </div>
                        <div class="sharethis-inline-share-buttons"></div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div id="main-content">
                    <!-- Characters & Voice Actors -->
                    <?php if (!empty($animeData['actors'])): ?>
                    <section class="block_area block_area-actors">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Characters &amp; Voice Actors</h2>
                            </div>
                            <div class="float-right viewmore">
                                <a class="btn" data-toggle="modal" data-target="#modalcharacters">View more<i
                                        class="fas fa-angle-right ml-2"></i></a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block-actors-content">
                            <div class="bac-list-wrap">
                                <?php
                                $limitedActors = array_slice($animeData['actors'], 0, 6);
                                foreach ($limitedActors as $entry):
                                ?>
                                <div class="bac-item">
                                    <div class="per-info ltr">
                                        <a href="/character/<?= htmlspecialchars($entry['character']['id']) ?>"
                                            class="pi-avatar" rel="noopener noreferrer">
                                            <img data-src="<?= htmlspecialchars($entry['character']['poster']) ?>"
                                                alt="<?= htmlspecialchars($entry['character']['name']) ?>"
                                                class="lazyload">
                                        </a>
                                        <div class="pi-detail">
                                            <h4 class="pi-name">
                                                <a href="/character/<?= htmlspecialchars($entry['character']['id']) ?>"
                                                    rel="noopener noreferrer">
                                                    <?= htmlspecialchars($entry['character']['name']) ?>
                                                </a>
                                            </h4>
                                            <span
                                                class="pi-cast"><?= htmlspecialchars($entry['character']['cast']) ?></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($entry['voiceActors']) && is_array($entry['voiceActors'])): ?>
                                    <?php $voiceActor = $entry['voiceActors'][0]; // Get the first voice actor ?>
                                    <div class="per-info rtl">
                                        <a href="/people/<?= htmlspecialchars($voiceActor['id']) ?>" class="pi-avatar"
                                            rel="noopener noreferrer">
                                            <img data-src="<?= htmlspecialchars($voiceActor['poster']) ?>"
                                                class="lazyload" alt="<?= htmlspecialchars($voiceActor['name']) ?>">
                                        </a>
                                        <div class="pi-detail">
                                            <h4 class="pi-name">
                                                <a href="/people/<?= htmlspecialchars($voiceActor['id']) ?>"
                                                    rel="noopener noreferrer">
                                                    <?= htmlspecialchars($voiceActor['name']) ?>
                                                </a>
                                            </h4>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="clearfix"></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </section>
                    <?php endif; ?>
                    <section class="block_area block_area-comment" id="comment-block">
                        <div class="block_area-header justify-content-start">
                            <div class="bah-heading mr-4">
                                <h2 class="cat-heading">Comments</h2>
                            </div>
                            <a class="guidelines" href="/news/rules-our-code-of-conduct-1">
                            	Community Guidelines <span>NEW</span>
                        	</a>
                        </div>
                        <div class="show-comments">
                        <div id="content-comments" class="comments-wrap">
                            <div class="loading-relative">
                                <div class="loading">
                                    <div class="span1"></div>
                                    <div class="span2"></div>
                                    <div class="span3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </section>
                    <section class="w-full flex items-center justify-center">
                        <section class="block_area block_area_category">
                        </section>
                    </section>
                    <!-- Recommended for you -->
                    <?php $hasqtipCounter = 0; ?>
                    <?php include('src/component/anime/recommended.php'); ?>
                </div>
                <?php include('src/component/anime/sidenav-dw.php'); ?>
                <div class="clearfix"></div>
            </div>
            <div id="totop"><i class="fas fa-chevron-up"></i></div>
        </div>
        <?php include('src/component/footer.php'); ?>
    </div>
    <div id="mask-overlay"></div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons' async='async'></script>
    <script type='text/javascript' src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script type="text/javascript" src="/js/watch.min.js?v=<?= $versionJs1 ?>"></script>
    <script id="syncData" type="application/json">
    {
        "page": "episode",
        "name": "<?= htmlspecialchars((string)($animeData['title'] ?? '')) ?>",
        "anime_id": "<?= htmlspecialchars((string)($animeData['data_id'] ?? '')) ?>",
        "mal_id": "<?= htmlspecialchars((string)($animeData['malId'] ?? '')) ?>",
        "anilist_id": "<?= htmlspecialchars((string)($animeData['anilistId'] ?? '')) ?>",
        "series_url": "<?= htmlspecialchars((string)($websiteUrl ?? '')) ?>/watch/<?= htmlspecialchars((string)($animeData['id'] ?? '')) ?>"
    }
    </script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js?v=<?= $versionJs2 ?>');
        });
    }
    window.toggleTheaterMode = function() {
        $("#media-resize").trigger("click");
    };
    $('.anime-request-link').click(function (e) {
        e.preventDefault();
        if (isLoggedIn) {
            window.location.href = $(this).attr('href');
        } else {
            $('#modallogin').modal('show');
        }
    });
	</script>

    <!-- Discord Rich Presence Configurations Disabled -->
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
	<?php echo WATCH_AD_CODE; ?>
</body>

</html>
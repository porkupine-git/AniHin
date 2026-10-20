<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- Helpers ---------- */

// esc
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// time ago
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        if (!$datetime) return '';
        $ts = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
        if ($ts <= 0) return '';

        $diff = time() - $ts;
        if ($diff < 60)        return 'just now';
        if ($diff < 3600)      return floor($diff/60) . ' minutes ago';
        if ($diff < 86400)     return floor($diff/3600) . ' hours ago';
        if ($diff < 30*86400) {
            $d = floor($diff/86400);
            return $d === 1 ? 'a day ago' : $d . ' days ago';
        }
        if ($diff < 365*86400) {
            $m = floor($diff/(30*86400));
            return $m === 1 ? 'a month ago' : $m . ' months ago';
        }
        $y = floor($diff/(365*86400));
        return $y === 1 ? 'a year ago' : $y . ' years ago';
    }
}

// news URL
if (!function_exists('news_url')) {
    function news_url(array $row): string {
        return '/news/' . $row['slug'] . '-' . $row['id'];
    }
}

/* ---------- Pagination setup ---------- */

$perPage = 10;
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset  = ($page - 1) * $perPage;

// total count of published news
$totalNews = 0;
$res = $conn->query("SELECT COUNT(*) AS cnt FROM news WHERE status = 'published'");
if ($res && $r = $res->fetch_assoc()) {
    $totalNews = (int)$r['cnt'];
}
$totalPages = $totalNews > 0 ? (int)ceil($totalNews / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* ---------- Load news list ---------- */

$sql = "SELECT id, title, slug, description, thumb_url, created_at
        FROM news
        WHERE status = 'published'
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();

$newsList = [];
while ($row = $res->fetch_assoc()) {
    $newsList[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title>Read Latest Anime News, Industry Updates, and Articles on <?= $websiteTitle ?></title>
    <meta name="description" content="Stay updated with the latest anime industry news, announcements, trailers, release dates, and reviews on <?= $websiteTitle ?>.">
    <meta name="keywords" content="anime news, latest anime news, anime industry news, anime announcements, <?= strtolower($websiteTitle) ?> news">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/news" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/news">
    <meta property="og:title" content="Read Latest Anime News on <?= $websiteTitle ?>">
    <meta property="og:description" content="Stay updated with the latest anime industry news, announcements, trailers, and reviews on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Read Latest Anime News on <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Stay updated with the latest anime industry news, announcements, trailers, and reviews on <?= $websiteTitle ?>.">
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
        <!--Begin: main-content-->
        <div id="main-wrapper" class="layout-page layout-news">
            <div class="container">
                <div id="main-content">
                    <div class="prebreadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/home">Home</a></li>
                                <li class="breadcrumb-item active">Anime News</li>
                            </ol>
                        </nav>
                    </div>

                    <!--Begin: Section film list-->
                    <section class="block_area block_area_category">
                        <div class="stx-center">
                            <h2 class="stx-title">Anime News</h2>
                            <div class="description">
                                Stay updated with the latest anime news and the best anime review on <?= $websiteUrlh ?>
                            </div>
                        </div>

                        <div class="block_area-content block_area-list news_list">
                            <div class="zr-news zr-news-list">

                                <?php if (!empty($newsList)): ?>
                                <?php foreach ($newsList as $item):
                                $url        = news_url($item);
                                $thumb      = $item['thumb_url'] ?: 'https://via.placeholder.com/300x300?text=News';
                                $timePosted = $item['created_at'] ? time_ago($item['created_at']) : '';
                            ?>
                                <div class="item">
                                    <a href="<?= e($url) ?>" class="zr-news-thumb">
                                        <img src="<?= e($thumb) ?>" class="zrn-image" alt="<?= e($item['title']) ?>">
                                    </a>
                                    <div class="zr-news-infor">
                                        <a href="<?= e($url) ?>" class="zrn-title">
                                            <h4 class="news-title"><?= e($item['title']) ?></h4>
                                        </a>
                                        <div class="description">
                                            <?= nl2br(e($item['description'])) ?>
                                        </div>
                                        <?php if ($timePosted): ?>
                                        <div class="time-posted"><?= e($timePosted) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <p style="color:#ccc; padding:15px 0;">No news articles yet.</p>
                                <?php endif; ?>

                            </div>
                            <div class="clearfix"></div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="pre-pagination mt-5 mb-5">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-lg justify-content-center">
                                        <?php
                                // simple prev
                                if ($page > 1):
                                    $prev = $page - 1;
                                ?>
                                        <li class="page-item">
                                            <a title="Previous" class="page-link" href="/news?page=<?= $prev ?>">‹</a>
                                        </li>
                                        <?php endif; ?>

                                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                        <?php if ($p == $page): ?>
                                        <li class="page-item active">
                                            <a class="page-link"><?= $p ?></a>
                                        </li>
                                        <?php else: ?>
                                        <li class="page-item">
                                            <a title="Page <?= $p ?>" class="page-link"
                                                href="/news?page=<?= $p ?>"><?= $p ?></a>
                                        </li>
                                        <?php endif; ?>
                                        <?php endfor; ?>

                                        <?php
                                // simple next
                                if ($page < $totalPages):
                                    $next = $page + 1;
                                ?>
                                        <li class="page-item">
                                            <a title="Next" class="page-link" href="/news?page=<?= $next ?>">›</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <!--End: Section film list-->

                    <div class="clearfix"></div>
                </div>
                <!--/End: main-content-->
                <?php $hasqtipCounter = 0; ?>
                <!--Begin: main-sidebar-->
                <?php
                	$apiUrl = $zpi . "/top-ten";
                	$response = file_get_contents($apiUrl);
                	$data = json_decode($response, true);
                	if ($data['success']) {
                	    $todayResults = $data['results']['today'];
                	    $weekResults = $data['results']['week'];
                	    $monthResults = $data['results']['month'];
                	}
                ?>
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
                                    <li class="nav-item"> <a class="nav-link" href="../genre/action"
                                            title="Action">Action</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/adventure"
                                            title="Adventure">Adventure</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/cars" title="Cars">Cars</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/comedy"
                                            title="Comedy">Comedy</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/dementia"
                                            title="Dementia">Dementia</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/demons"
                                            title="Demons">Demons</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/drama"
                                            title="Drama">Drama</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/ecchi"
                                            title="Ecchi">Ecchi</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/fantasy"
                                            title="Fantasy">Fantasy</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/game" title="Game">Game</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/harem"
                                            title="Harem">Harem</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/historical"
                                            title="Historical">Historical</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/horror"
                                            title="Horror">Horror</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/Isekai"
                                            title="Isekai">Isekai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/josei"
                                            title="Josei">Josei</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/kids" title="Kids">Kids</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/magic"
                                            title="Magic">Magic</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/martial-arts"
                                            title="Martial Arts">Martial
                                            Arts</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/mecha"
                                            title="Mecha">Mecha</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/military"
                                            title="Military">Military</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/music"
                                            title="Music">Music</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/mystery"
                                            title="Mystery">Mystery</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/parody"
                                            Title="Parody">Parody</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/police"
                                            title="Police">Police</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/psychological"
                                            title="Psychological">Psychological</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/romance"
                                            title="Romance">Romance</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/samurai"
                                            title="Samurai">Samurai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/school"
                                            title="School">School</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/sci-fi"
                                            title="Sci-Fi">Sci-Fi</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/seinen"
                                            title="Seinen">Seinen</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/shoujo"
                                            title="Shoujo">Shoujo</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/shoujo-ai"
                                            title="Shoujo Ai">Shoujo Ai</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/shounen"
                                            title="Shounen">Shounen</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/shounen-Ai"
                                            title="Shounen Ai">Shounen
                                            Ai</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/slice-of-life"
                                            title="Slice of Life">Slice
                                            of Life</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/space"
                                            title="Space">Space</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/sports"
                                            title="Sports">Sports</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/super-power"
                                            title="Super Power">Super
                                            Power</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/supernatural"
                                            title="Supernatural">Supernatural</a> </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/thriller"
                                            title="Thriller">Thriller</a>
                                    </li>
                                    <li class="nav-item"> <a class="nav-link" href="../genre/vampire"
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
                                                <?php foreach ($data['results']['today'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span>
                                                    </div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/details/<?php echo $anime['id']; ?>"
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
                                                <?php foreach ($data['results']['week'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span>
                                                    </div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/details/<?php echo $anime['id']; ?>"
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
                                                <?php foreach ($data['results']['month'] as $anime): ?>
                                                <li class="<?php echo $anime['number'] <= 3 ? 'item-top' : ''; ?>">
                                                    <div class="film-number">
                                                        <span><?php echo $anime['number']; ?></span>
                                                    </div>
                                                    <div class="film-poster item-qtip"
                                                        data-id="<?php echo $anime['data_id']; ?>"
                                                        data-hasqtip="<?php echo $hasqtipCounter; ?>"
                                                        aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                                        <img data-src="<?php echo $anime['poster']; ?>"
                                                            class="film-poster-img lazyload"
                                                            alt="<?php echo $anime['japanese_title']; ?>">
                                                    </div>
                                                    <div class="film-detail">
                                                        <h3 class="film-name">
                                                            <a href="/details/<?php echo $anime['id']; ?>"
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
    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons'
        async='async'></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
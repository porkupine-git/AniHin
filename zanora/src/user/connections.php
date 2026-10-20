<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Try to get userID from session first
$user_id = $_SESSION['userID'] ?? null;
// If still not found, redirect
if (!$user_id) {
    header("Location: /home");
    exit();
}
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$currentAvatar = $user['image'] ?? "";
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title>Manage Connections - <?= $websiteTitle ?></title>
    <meta name="description" content="Link your AnimeList or Discord account with <?= $websiteTitle ?>.">
    <meta name="keywords" content="connections, account linking, discord, anilist, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/user/connections" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/user/connections">
    <meta property="og:title" content="Manage Connections - <?= $websiteTitle ?>">
    <meta property="og:description" content="Link your AnimeList or Discord account with <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Manage Connections - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Link your AnimeList or Discord account with <?= $websiteTitle ?>.">
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
    <style>
    /* Linked Accounts / Connections */
    .profile-connections {
        background: rgba(20, 22, 26, 0.45);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 28px;
        margin-top: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    }

    .connection-card {
        background: rgba(255, 255, 255, 0.015);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .connection-card:hover {
        background: rgba(255, 255, 255, 0.025);
        border-color: rgba(2, 169, 255, 0.3);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    .connection-left {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        flex: 1;
        min-width: 0;
    }

    .connection-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
    }

    .anilist-card .connection-icon {
        background: rgba(2, 169, 255, 0.08);
        border: 1px solid rgba(2, 169, 255, 0.18);
    }

    .discord-card .connection-icon {
        background: rgba(88, 101, 242, 0.08);
        border: 1px solid rgba(88, 101, 242, 0.18);
    }

    .connection-info {
        flex: 1;
        min-width: 0;
    }

    .connection-info h3 {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin-top: 0;
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .connection-info p {
        font-size: 13px;
        color: #959da5;
        line-height: 1.5;
        margin-bottom: 12px;
        max-width: 480px;
    }

    .connection-features {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .connection-features span {
        font-size: 10.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .anilist-card .connection-features span {
        color: #02A9FF;
        background: rgba(2, 169, 255, 0.08);
        border: 1px solid rgba(2, 169, 255, 0.15);
    }

    .discord-card .connection-features span {
        color: #5865F2;
        background: rgba(88, 101, 242, 0.08);
        border: 1px solid rgba(88, 101, 242, 0.15);
    }

    .connection-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .connected-user {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.025);
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 8px 14px 8px 10px;
        border-radius: 12px;
    }

    .connected-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }

    .anilist-card .connected-avatar {
        border: 2px solid #02A9FF;
        box-shadow: 0 4px 10px rgba(2, 169, 255, 0.2);
    }

    .discord-card .connected-avatar {
        border: 2px solid #5865F2;
        box-shadow: 0 4px 10px rgba(88, 101, 242, 0.2);
    }

    .connected-meta {
        display: flex;
        flex-direction: column;
    }

    .connected-label {
        font-size: 9.5px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        font-family: 'Outfit', sans-serif;
    }

    .anilist-card .connected-label {
        color: #02A9FF;
    }

    .discord-card .connected-label {
        color: #5865F2;
    }

    .connected-meta .connected-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #fff;
        font-family: 'Outfit', sans-serif;
    }

    .btn-anilist,
    .btn-discord {
        color: #fff !important;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 22px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Outfit', sans-serif;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .btn-anilist {
        background: linear-gradient(135deg, #02A9FF, #0084c9);
        box-shadow: 0 4px 15px rgba(2, 169, 255, 0.25);
    }

    .btn-discord {
        background: linear-gradient(135deg, #5865F2, #4752c4);
        box-shadow: 0 4px 15px rgba(88, 101, 242, 0.25);
    }

    .btn-anilist:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(2, 169, 255, 0.4);
    }

    .btn-discord:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(88, 101, 242, 0.4);
    }

    .connection-actions {
        display: flex;
        gap: 8px;
    }

    .connection-actions .btn {
        padding: 7px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: 'Outfit', sans-serif;
        text-decoration: none !important;
    }

    .connection-actions .btn-primary {
        background: rgba(2, 169, 255, 0.1);
        border: 1px solid rgba(2, 169, 255, 0.2);
        color: #02A9FF;
    }

    .connection-actions .btn-primary:hover {
        background: #02A9FF;
        color: #fff;
        box-shadow: 0 4px 12px rgba(2, 169, 255, 0.25);
    }

    .connection-actions .btn-danger {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    .connection-actions .btn-danger:hover {
        background: #dc3545;
        color: #fff;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25);
    }

    /* Mobile & Tablet Responsive Optimizations */
    @media (max-width: 768px) {
        .profile-connections {
            padding: 20px 16px;
            border-radius: 18px;
            margin-top: 15px;
        }

        .profile-connections .h2-heading {
            font-size: 18px !important;
            margin-bottom: 16px !important;
        }

        .connection-card {
            flex-direction: column;
            align-items: stretch;
            padding: 16px 14px;
            gap: 14px;
            border-radius: 14px;
        }

        .connection-left {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .connection-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            padding: 8px;
        }

        .connection-info h3 {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .connection-info p {
            font-size: 12px;
            line-height: 1.45;
            margin-bottom: 8px;
            color: #8892b0;
        }

        .connection-features {
            gap: 5px;
        }

        .connection-features span {
            font-size: 9.5px;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .connection-right {
            width: 100%;
            align-items: stretch;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 12px;
            gap: 10px;
        }

        .connected-user {
            width: 100%;
            padding: 8px 12px;
            border-radius: 10px;
        }

        .connected-avatar {
            width: 32px;
            height: 32px;
        }

        .connected-meta .connected-name {
            font-size: 13px;
        }

        .btn-anilist,
        .btn-discord {
            width: 100%;
            justify-content: center;
            padding: 10px 14px;
            font-size: 12.5px;
            border-radius: 8px;
        }

        .connection-actions {
            width: 100%;
            margin-top: 0 !important;
            gap: 8px;
        }

        .connection-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 8px 10px;
            font-size: 12px;
            border-radius: 8px;
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
        <div id="main-wrapper" class="layout-page layout-profile">
            <div class="profile-header">
                <div class="profile-header-cover"
                    style="background-image: url(<?= htmlspecialchars($user['image']) ?>);"></div>
                <div class="container">
                    <div class="ph-title">Hi, <?= htmlspecialchars($user['username']) ?></div>
                    <div class="ph-tabs">
                        <div class="bah-tabs">
                            <ul class="nav nav-tabs pre-tabs">
                                <li class="nav-item"><a class="nav-link " href="/user/profile"><i
                                            class="fas fa-user mr-2"></i>Profile</a></li>
                                <li class="nav-item"><a class="nav-link " href="/user/continue-watching"><i
                                            class="fas fa-history mr-2"></i>Continue Watching
                                    </a></li>
                                <li class="nav-item"><a class="nav-link " href="/user/watch-list"><i
                                            class="fas fa-heart mr-2"></i>Watch
                                        List</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/notification"><i
                                            class="fas fa-bell mr-2"></i>Notification</a></li>
                                <li class="nav-item"><a class="nav-link active" href="/user/connections"><i
                                            class="fas fa-link mr-2"></i>Connections</a></li>
                                <li class="nav-item"><a class="nav-link " href="/user/settings"><i
                                            class="fas fa-cog mr-2"></i>Settings</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="profile-content">
                <div class="container">

                    <div class="profile-box profile-connections">

                        <h2 class="h2-heading mb-4">
                            <i class="fas fa-link mr-3"></i>
                            Linked Accounts
                        </h2>

                        <div class="block_area-content">

                            <!-- AniList Card -->
                            <div class="connection-card anilist-card">

                                <div class="connection-left">

                                    <div class="connection-icon">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                            style="width: 100%; height: 100%; fill: #02A9FF;">
                                            <path
                                                d="M24 17.53v2.421c0 .71-.391 1.101-1.1 1.101h-5l-.057-.165L11.84 3.736c.106-.502.46-.788 1.053-.788h2.422c.71 0 1.1.391 1.1 1.1v12.38H22.9c.71 0 1.1.392 1.1 1.101zM11.034 2.947l6.337 18.104h-4.918l-1.052-3.131H6.019l-1.077 3.131H0L6.361 2.948h4.673zm-.66 10.96-1.69-5.014-1.541 5.015h3.23z" />
                                        </svg>
                                    </div>

                                    <div class="connection-info">
                                        <h3>AniList</h3>

                                        <p>
                                            Sync your anime progress, watch list and activity automatically with
                                            AniList.
                                        </p>

                                        <div class="connection-features">
                                            <span>
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Auto Sync
                                            </span>

                                            <span>
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Import Library
                                            </span>

                                            <span>
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Track Episodes
                                            </span>
                                        </div>
                                    </div>

                                </div>

                                <div class="connection-right">

                                    <?php if (!empty($user['anilist_id'])): ?>

                                    <div class="connected-user">

                                        <img class="connected-avatar"
                                            src="<?= htmlspecialchars($user['anilist_avatar']) ?>">

                                        <div class="connected-meta">
                                            <div class="connected-label">
                                                Connected
                                            </div>

                                            <div class="connected-name">
                                                <?= htmlspecialchars($user['anilist_username']) ?>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="connection-actions mt-3">

                                        <a href="#" class="btn btn-sm btn-primary btn-sync-anilist">
                                            <i class="fas fa-sync-alt mr-1"></i>
                                            Sync
                                        </a>

                                        <a href="/user/anilist/disconnect" class="btn btn-sm btn-danger">
                                            Disconnect
                                        </a>

                                    </div>

                                    <?php else: ?>

                                    <a href="/auth/anilist" class="btn btn-anilist">
                                        <i class="fas fa-link mr-2"></i>
                                        Connect AniList
                                    </a>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php include 'src/component/footer.php' ?>
    </div>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons'
        async='async'></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <?php if (!empty($user['anilist_token']) && !empty($user['anilist_id'])): ?>
    <script>
    const ANILIST_TOKEN = <?= json_encode($user['anilist_token']) ?>;
    const ANILIST_USER_ID = <?= (int)$user['anilist_id'] ?>;

    async function executeClientAniListSync(token, anilistUserId, options = {}) {
        const onProgress = options.onProgress || function() {};
        onProgress("Fetching library from AniList...");

        const graphqlQuery = `
          query ($userId: Int) {
            MediaListCollection(userId: $userId, type: ANIME) {
              lists {
                entries {
                  mediaId
                  status
                  progress
                  score(format: POINT_10)
                  notes
                  updatedAt
                }
              }
            }
          }
        `;

        const alRes = await fetch("https://graphql.anilist.co", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                query: graphqlQuery,
                variables: { userId: anilistUserId }
            })
        });

        if (alRes.status === 429) {
            throw new Error("AniList rate limit reached. Please wait a moment and try again.");
        }
        if (!alRes.ok) {
            throw new Error("Failed to connect to AniList API (HTTP " + alRes.status + ")");
        }

        const collection = await alRes.json();
        const lists = (collection.data && collection.data.MediaListCollection && collection.data.MediaListCollection.lists) || [];
        const flatEntries = [];
        lists.forEach(list => {
            if (list.entries) {
                list.entries.forEach(entry => {
                    flatEntries.push({
                        mediaId: entry.mediaId,
                        status: entry.status,
                        progress: entry.progress,
                        score: entry.score,
                        notes: entry.notes,
                        updatedAt: entry.updatedAt
                    });
                });
            }
        });

        onProgress("Syncing with local watchlist (" + flatEntries.length + " entries)...");
        const syncRes = await fetch("/ajax/watch-list/sync-client", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ lists: flatEntries })
        });

        if (!syncRes.ok) {
            throw new Error("Failed to communicate with sync server.");
        }

        const syncData = await syncRes.json();
        if (!syncData.status) {
            throw new Error(syncData.msg || "Sync failed.");
        }

        if (syncData.needs_push && syncData.needs_push.length > 0) {
            onProgress("Updating AniList entries (" + syncData.needs_push.length + " items)...");
            const completedTasks = [];
            const saveMutation = `
              mutation ($mediaId: Int, $status: MediaListStatus, $progress: Int, $score: Float, $notes: String) {
                SaveMediaListEntry(mediaId: $mediaId, status: $status, progress: $progress, score: $score, notes: $notes) {
                  id
                }
              }
            `;

            for (let i = 0; i < syncData.needs_push.length; i++) {
                const item = syncData.needs_push[i];
                try {
                    const pushRes = await fetch("https://graphql.anilist.co", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            query: saveMutation,
                            variables: {
                                mediaId: item.mediaId,
                                status: item.status,
                                progress: item.progress,
                                score: item.score,
                                notes: item.notes
                            }
                        })
                    });

                    if (pushRes.ok && item.queueId) {
                        completedTasks.push(item.queueId);
                    }
                } catch (err) {
                    console.warn("Failed to push entry to AniList:", item.mediaId, err);
                }
                await new Promise(resolve => setTimeout(resolve, 250));
            }

            if (completedTasks.length > 0) {
                await fetch("/ajax/watch-list/sync-client", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ completed_tasks: completedTasks })
                });
            }
        }

        return syncData;
    }
    </script>
    <?php endif; ?>
    <script>
    $(document).ready(function() {
        $('.btn-sync-anilist').on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            if ($btn.hasClass('disabled')) return;

            var originalHtml = $btn.html();

            // Disable and show loading
            $btn.html('<i class="fas fa-sync-alt fa-spin mr-1"></i> Syncing...').addClass('disabled');

            if (typeof executeClientAniListSync === 'function' && typeof ANILIST_TOKEN !== 'undefined' && typeof ANILIST_USER_ID !== 'undefined') {
                executeClientAniListSync(ANILIST_TOKEN, ANILIST_USER_ID, {
                    onProgress: function(msg) {
                        $btn.html('<i class="fas fa-sync-alt fa-spin mr-1"></i> ' + msg);
                    }
                })
                .then(function(response) {
                    var successMsg = response.msg || 'Synchronization completed successfully from your IP!';
                    if (typeof toastr !== 'undefined') {
                        toastr.success(successMsg);
                    } else {
                        alert(successMsg);
                    }
                    $btn.html('<i class="fas fa-check mr-1"></i> Synced!').removeClass('disabled');
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2500);
                })
                .catch(function(err) {
                    var errMsg = err.message || 'Sync failed.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errMsg);
                    } else {
                        alert(errMsg);
                    }
                    $btn.html(originalHtml).removeClass('disabled');
                });
            } else {
                alert('AniList account is not linked or active.');
                $btn.html(originalHtml).removeClass('disabled');
            }
        });
    });
    </script>
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
</body>

</html>
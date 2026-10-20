<?php
require_once('src/component/anime/qtip.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Try to get userID from session first
$user_id = $_SESSION['userID'] ?? null;
$isLoggedIn = $_SESSION['userID'] ?? null;
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

$trigger_anilist_sync = (!empty($user['anilist_token']) && !empty($user['anilist_id']));
$page = $_GET['page'] ?? 1;
$items_per_page = 30;
$offset = ($page - 1) * $items_per_page;
$status = $_GET['status'] ?? 'All';
$safeStatus = htmlspecialchars($status ?? '', ENT_QUOTES, 'UTF-8');
// Map status to type values
$statusMap = [
    'Watching' => 1,
    'On-Hold' => 2,
    'Plan to Watch' => 3,
    'Dropped' => 4,
    'Completed' => 5
];
$type = $statusMap[$status] ?? null;
$site_watchlist_sql = "SELECT * FROM watchlist WHERE user_id = ?";
if ($type !== null) {
    $site_watchlist_sql .= " AND type = ?";
}
$site_watchlist_sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$site_stmt = $conn->prepare($site_watchlist_sql);
if ($type !== null) {
    $site_stmt->bind_param("iiii", $user_id, $type, $items_per_page, $offset);
} else {
    $site_stmt->bind_param("iii", $user_id, $items_per_page, $offset);
}
$site_stmt->execute();
$site_watchlist_result = $site_stmt->get_result();
$count_site_sql = "SELECT COUNT(*) as total FROM watchlist WHERE user_id = ?";
$count_site_stmt = $conn->prepare($count_site_sql);
$count_site_stmt->bind_param("i", $user_id);
$count_site_stmt->execute();
$total_site_items = $count_site_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_site_items / $items_per_page);
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />
    
    <!-- SEO Meta Tags -->
    <title>My Watchlist - <?= $websiteTitle ?></title>
    <meta name="description" content="Manage your anime watchlist: track what you are watching, completed, dropped, or plan to watch on <?= $websiteTitle ?>.">
    <meta name="keywords" content="my watchlist, anime tracker, plan to watch, completed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/user/watchlist" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/user/watchlist">
    <meta property="og:title" content="My Watchlist - <?= $websiteTitle ?>">
    <meta property="og:description" content="Manage your anime watchlist: track what you are watching, completed, dropped, or plan to watch on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="My Watchlist - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Manage your anime watchlist: track what you are watching, completed, dropped, or plan to watch on <?= $websiteTitle ?>.">
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
<?php if ($trigger_anilist_sync): ?>
    <!-- CLIENT SIDE ANILIST SYNC LOADER -->
    <div id="anilist-sync-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(18, 18, 20, 0.95); z-index:99999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-family:'Poppins', sans-serif;">
        <div class="loader-spinner" style="border: 4px solid rgba(255,255,255,0.1); border-left-color: #02A9FF; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
        <h2 id="sync-overlay-title" style="font-size: 20px; font-weight: 600; margin-bottom: 10px;">Synchronizing with AniList...</h2>
        <p id="sync-overlay-sub" style="font-size: 14px; color: #aaa; margin: 0;">Please wait while we update your watchlist.</p>
    </div>
    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
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

        document.addEventListener("DOMContentLoaded", function() {
            if (sessionStorage.getItem("watchlist_synced_session") === "true") {
                const overlay = document.getElementById("anilist-sync-overlay");
                if (overlay) overlay.remove();
                return;
            }

            const subEl = document.getElementById("sync-overlay-sub");
            executeClientAniListSync(ANILIST_TOKEN, ANILIST_USER_ID, {
                onProgress: function(msg) {
                    if (subEl) subEl.textContent = msg;
                }
            })
            .then(res => {
                sessionStorage.setItem("watchlist_synced_session", "true");
                window.location.reload();
            })
            .catch(err => {
                console.error("AniList Initial Sync Error:", err);
                sessionStorage.setItem("watchlist_synced_session", "true");
                const overlay = document.getElementById("anilist-sync-overlay");
                if (overlay) overlay.remove();
            });
        });
    </script>
<?php endif; ?>
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
        <div id="main-wrapper" class="layout-page layout-watchlist">
            <div class="profile-header">
                <div class="profile-header-cover"
                    style="background-image: url(<?= htmlspecialchars($user['image']) ?>);"></div>
                <div class="container">
                    <div class="ph-title">Hi, <?= htmlspecialchars($user['username']) ?></div>
                    <div class="ph-tabs">
                        <div class="bah-tabs">
                            <ul class="nav nav-tabs pre-tabs">
                                <li class="nav-item"><a class="nav-link" href="/user/profile"><i
                                            class="fas fa-user mr-2"></i>Profile</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/continue-watching"><i
                                            class="fas fa-history mr-2"></i>Continue Watching
                                    </a></li>
                                <li class="nav-item"><a class="nav-link active" href="/user/watch-list"><i
                                            class="fas fa-heart mr-2"></i>Watch
                                        List</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/notification"><i
                                            class="fas fa-bell mr-2"></i>Notification</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/connections"><i
                                            class="fas fa-link mr-2"></i>Connections</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/settings">
                                        <i class="fas fa-cog mr-2"></i>Settings</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="profile-content">
                <div class="container">
                    <div class="profile-box profile-list profile-list-fav">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
                            <h2 class="h2-heading mb-0">
                                <i class="fas fa-heart mr-3"></i>
                                Watch List
                            </h2>
                            <div class="d-flex align-items-center" style="gap: 12px;">
                                <?php if ($trigger_anilist_sync): ?>
                                <button type="button" id="btn-manual-anilist-sync" class="btn btn-sm" style="background: rgba(2, 169, 255, 0.12); border: 1px solid rgba(2, 169, 255, 0.25); color: #02A9FF; font-weight: 600; border-radius: 10px; padding: 7px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;">
                                    <i class="fas fa-sync-alt"></i> Sync AniList
                                </button>
                                <?php endif; ?>
                                <div data-option="public_watch_list"
                                    class="dub-toggle quick-settings-toggle <?= $user['public_watch_list'] == 1 ? 'active' : ''; ?> mt-0">
                                    <span class="dt-dub">Public</span>
                                    <span class="dt-status"></span>
                                </div>
                            </div>
                        </div>
                        <div class="fav-tabs mb-4">
                            <ul class="nav nav-tabs pre-tabs pre-tabs-min">
                                <li class="nav-item"> <a href="?status=All"
                                        class="nav-link <?= $status == 'All' ? 'active' : '' ?>"> All </a></li>
                                <li class="nav-item"> <a href="?status=Watching"
                                        class="nav-link <?= $status == 'Watching' ? 'active' : '' ?>"> Watching </a>
                                </li>
                                <li class="nav-item"> <a href="?status=On-Hold"
                                        class="nav-link <?= $status == 'On-Hold' ? 'active' : '' ?>"> On-Hold </a>
                                </li>
                                <li class="nav-item"> <a href="?status=Plan to Watch"
                                        class="nav-link <?= $status == 'Plan to Watch' ? 'active' : '' ?>"> Plan to
                                        Watch </a></li>
                                <li class="nav-item"> <a href="?status=Dropped"
                                        class="nav-link <?= $status == 'Dropped' ? 'active' : '' ?>"> Dropped </a>
                                </li>
                                <li class="nav-item"> <a href="?status=Completed"
                                        class="nav-link <?= $status == 'Completed' ? 'active' : '' ?>"> Completed
                                    </a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Site Watchlist Tab -->
                            <div class="tab-pane fade show active" id="site">
                                <div class="block_area-content block_area-list film_list film_list-grid">
                                    <div class="film_list-wrap">
                                        <?php $hasqtipCounter = 0; ?>
                                        <?php if ($site_watchlist_result->num_rows > 0): ?>
                                        <?php while($anime = $site_watchlist_result->fetch_assoc()): ?>
                                        <?php
    $animeId = (int)$anime['anime_id'];

    // Check local cache first
    if (isset($cachedAnime[$animeId])) {

        $animeData = [
            'malId'        => $cachedAnime[$animeId]['mal_id'],
            'title'        => $cachedAnime[$animeId]['title'],
            'japa'         => $cachedAnime[$animeId]['jtitle'],
            'poster'       => $cachedAnime[$animeId]['poster'],
            'adultContent' => $cachedAnime[$animeId]['adult'],
            'duration'     => $cachedAnime[$animeId]['duration'],
            'showType'     => $cachedAnime[$animeId]['show_type'],
        ];

    } else {

        // Fetch from AniList/API only if missing
        $animeData = fetchAnimeData($animeId);

        if (!$animeData) continue;

        // Save to DB cache
        mysqli_query($conn, "
            INSERT IGNORE INTO anime_meta (
                anime_id,
                mal_id,
                title,
                jtitle,
                poster,
                adult,
                duration,
                show_type
            ) VALUES (
                '{$animeId}',
                '".(int)$animeData['malId']."',
                '".mysqli_real_escape_string($conn, $animeData['title'])."',
                '".mysqli_real_escape_string($conn, $animeData['japa'])."',
                '".mysqli_real_escape_string($conn, $animeData['poster'])."',
                '".(int)$animeData['adultContent']."',
                '".mysqli_real_escape_string($conn, $animeData['duration'])."',
                '".mysqli_real_escape_string($conn, $animeData['showType'])."'
            )
        ");

        // Add into runtime cache
        $cachedAnime[$animeId] = [
            'mal_id'     => $animeData['malId'],
            'title'      => $animeData['title'],
            'jtitle'     => $animeData['japa'],
            'poster'     => $animeData['poster'],
            'adult'      => $animeData['adultContent'],
            'duration'   => $animeData['duration'],
            'show_type'  => $animeData['showType']
        ];
    }
?>
                                        <div class="flw-item">
                                            <div class="dr-fav">
                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                    class="btn btn-circle btn-light btn-fav">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal"
                                                    aria-labelledby="ssc-list">
                                                    <?php
                                                    $currentType = (int)$anime['type'];
                                                ?>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <a class="wl-item dropdown-item <?= $currentType == $i ? 'added' : '' ?>"
                                                        data-type="<?= $i ?>" data-movieid="<?= $anime['anime_id'] ?>"
                                                        data-page="watch-list" href="javascript:;">
                                                        <?= ['Watching', 'On-Hold', 'Plan to Watch', 'Dropped', 'Completed'][$i - 1] ?>
                                                        <?php if ($currentType == $i): ?><i
                                                            class="fas fa-check ml-2"></i><?php endif; ?>
                                                    </a>
                                                    <?php endfor; ?>
                                                    <a class="wl-item dropdown-item text-danger" data-type="0"
                                                        data-movieid="<?= $anime['anime_id'] ?>" data-page="watch-list"
                                                        href="javascript:;">
                                                        <i class="fas fa-trash mr-2"></i>Remove</a>
                                                </div>
                                            </div>
                                            <div class="film-poster">
                                                <?php if ($animeData['adultContent']) { ?>
                                                <div class="tick tick-rate">18+</div>
                                                <?php } ?>
                                                <div class="tick ltr">
                                                    <?php if (!empty($animeData['subEp'])): ?>
                                                    <div class="tick-item tick-sub"><i
                                                            class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars($animeData['subEp']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($animeData['dubEp'])): ?>
                                                    <div class="tick-item tick-dub"><i
                                                            class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars($animeData['dubEp']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (isset($animeData['eps'])): ?>
                                                    <div class="tick-item tick-eps"><?php echo $animeData['eps']; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
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
                                                    <a href="<?= $websiteUrl; ?>/<?= htmlspecialchars($anime['anime_id'] ?? '') ?>"
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
                                            <div class="clearfix"></div>
                                        </div>
                                        <?php $hasqtipCounter++; ?>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <!-- unified look: same markup / classes as the others -->
                                    <?php if ($total_pages > 1): ?>
                                    <div class="pre-pagination mt-5 mb-5">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination pagination-lg justify-content-center">
                                                <!-- First (only if ≥ 3 pages) -->
                                                <?php if ($page > 1 && $total_pages >= 3): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?status=<?= urlencode($status) ?>&page=1"
                                                        title="First">«</a>
                                                </li>
                                                <?php endif; ?>
                                                <!-- Previous -->
                                                <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?status=<?= urlencode($status) ?>&page=<?= $page-1 ?>"
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
                                                        href="?status=<?= urlencode($status) ?>&page=<?= $i ?>"
                                                        title="Page <?= $i ?>"><?= $i ?></a>
                                                </li>
                                                <?php endif; ?>
                                                <?php endfor; ?>
                                                <!-- Next -->
                                                <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?status=<?= urlencode($status) ?>&page=<?= $page+1 ?>"
                                                        title="Next">›</a>
                                                </li>
                                                <?php endif; ?>
                                                <!-- Last (only if ≥ 3 pages) -->
                                                <?php if ($page < $total_pages && $total_pages >= 3): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?status=<?= urlencode($status) ?>&page=<?= $total_pages ?>"
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
                <div class="clearfix"></div>
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

    $('#btn-manual-anilist-sync').click(function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass('disabled')) return;
        var originalHtml = $btn.html();
        $btn.html('<i class="fas fa-sync-alt fa-spin"></i> Syncing...').addClass('disabled');

        if (typeof executeClientAniListSync === 'function' && typeof ANILIST_TOKEN !== 'undefined' && typeof ANILIST_USER_ID !== 'undefined') {
            executeClientAniListSync(ANILIST_TOKEN, ANILIST_USER_ID)
            .then(function(res) {
                $btn.html('<i class="fas fa-check"></i> Synced!').removeClass('disabled');
                setTimeout(function() { window.location.reload(); }, 500);
            })
            .catch(function(err) {
                alert(err.message || "AniList sync failed.");
                $btn.html(originalHtml).removeClass('disabled');
            });
        }
    });
	</script>
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
</body>

</html>
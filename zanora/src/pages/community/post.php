<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['userID'] ?? null;
$roles  = $_SESSION['roles'] ?? null;

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    http_response_code(404);
    require_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
    exit;
}

/* Fetch post directly by slug */
$stmt = $conn->prepare(
    "SELECT p.*, u.username, u.image AS avatar, u.points, u.roles
     FROM posts p
     JOIN users u ON u.id = p.user_id
     WHERE p.slug = ? AND p.status = 'published'
     LIMIT 1"
);
$stmt->bind_param("s", $slug);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: /404");
    exit;
}

/* We still need numeric post id for votes, etc. */
$postId = (int)$post['id'];

/* Tag data (static) */
$tagId = (int)$post['tags_id'];
$tags = [
    1 => ['name'=>'Suggestion', 'class'=>'zt-green'],
    2 => ['name'=>'Question',   'class'=>'zt-yellow'],
    3 => ['name'=>'Discussion', 'class'=>'zt-purple'],
    4 => ['name'=>'General',    'class'=>'zt-blue'],
    5 => ['name'=>'Updates',    'class'=>'zt-red'],
    6 => ['name'=>'Feedback',   'class'=>'zt-orange'],
];
$tagName  = $tags[$tagId]['name']  ?? 'General';
$tagClass = $tags[$tagId]['class'] ?? 'zt-blue';

/* Time ago */
function timeAgo($time){
    $t = time() - strtotime($time);
    if ($t < 60)   return $t . "s ago";
    if ($t < 3600) return floor($t/60)   . "m ago";
    if ($t < 86400)return floor($t/3600) . "h ago";
    return floor($t/86400) . "d ago";
}

/* Votes */
$voteStmt = $conn->prepare("
    SELECT
        SUM(type = 1) AS upvotes,
        SUM(type = 0) AS downvotes
    FROM post_votes
    WHERE post_id = ?
");
$voteStmt->bind_param("i", $postId);
$voteStmt->execute();
$votes = $voteStmt->get_result()->fetch_assoc() ?? ['upvotes' => 0, 'downvotes' => 0];
$voteStmt->close();

$myVote = null;
if ($userId) {
    $stmtVote = $conn->prepare("SELECT type FROM post_votes WHERE user_id = ? AND post_id = ? LIMIT 1");
    $stmtVote->bind_param("ii", $userId, $postId);
    $stmtVote->execute();
    $res = $stmtVote->get_result()->fetch_assoc();
    $myVote = $res['type'] ?? null; // 1 = upvote, 0 = downvote
    $stmtVote->close();
}

/* Rank / badge */
$authorIsAdmin = (int)($post['roles'] ?? 0); // 1 = Admin, 2 = Mod, 0 = normal
$authorPoints  = (int)($post['points'] ?? 0);

if ($authorIsAdmin === 1) {
    $rankName   = 'Admin';
    $badgeClass = 'level-b';
} elseif ($authorIsAdmin === 2) {
    $rankName   = 'Mod';
    $badgeClass = 'level-d';
} else {
    list($rankName, $badgeClass) = getRank($authorPoints); // same function as in comments
}

/* Username CSS class */
if ($authorIsAdmin === 1) {
    $userNameClass = 'user-name is-level-x is-level-b';
} elseif ($authorIsAdmin === 2) {
    $userNameClass = 'user-name is-level-x is-level-d';
} else {
    $userNameClass = 'user-name is-level-x' . ($badgeClass ? ' is-level-a' : '');
}
$authorId = (int)$post['user_id'];
$isAuthorBlacklisted = false;

if ($roles > 0) { // only bother checking if current user is staff/admin
    $stmtBL = $conn->prepare("SELECT 1 FROM user_blacklist WHERE blocked_user_id = ? LIMIT 1");
    $stmtBL->bind_param("i", $authorId);
    if ($stmtBL->execute()) {
        $stmtBL->store_result();
        if ($stmtBL->num_rows > 0) {
            $isAuthorBlacklisted = true;
        }
    }
    $stmtBL->close();
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
    <title><?= htmlspecialchars($post['title']) ?> - <?= $websiteTitle ?> Community</title>
    <meta name="description" content="Read this post: &quot;<?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 150)) ?>...&quot; Join the discussion on the <?= $websiteTitle ?> community board.">
    <meta name="keywords" content="<?= htmlspecialchars($post['title']) ?>, anime discussion, <?= strtolower($websiteTitle) ?> forum">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/community/post/<?= htmlspecialchars($post['slug']) ?>" />

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
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?= WEBSITE_URL ?>/community/post/<?= htmlspecialchars($post['slug']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?> - <?= $websiteTitle ?> Community">
    <meta property="og:description" content="Read this post: &quot;<?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 150)) ?>...&quot; Join the discussion on the <?= $websiteTitle ?> community board.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?> - <?= $websiteTitle ?> Community">
    <meta name="twitter:description" content="Read this post: &quot;<?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 150)) ?>...&quot; Join the discussion on the <?= $websiteTitle ?> community board.">
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

    <!-- Schema.org DiscussionForumPosting Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "DiscussionForumPosting",
            "headline": "<?= htmlspecialchars($post['title']) ?>",
            "articleBody": "<?= htmlspecialchars(strip_tags($post['content'])) ?>",
            "datePublished": "<?= htmlspecialchars($post['created_at']) ?>",
            "author": {
                "@type": "Person",
                "name": "<?= htmlspecialchars($post['username']) ?>"
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
                <div class="award-bg-img" style="background-image: url('/images/community/cover.jpg');"></div>
            </div>
            <!--Begin: award-->
            <div class="container">
                <div class="award-inner">
                    <div class="wideview">
                        <div class="connect-detail">
                            <div class="ai-control">
                                <div class="aic-left">
                                    <a href="/community/board">
                                        <i class="fas fa-angle-left mr-2"></i>
                                        Back
                                    </a>
                                </div>
                            </div>
                            <div class="cd-article" style="margin-top: 15px;">
                                <div class="cda-about d-flex align-items-center mb-3">
                                    <div class="cn-owner">
                                        <div class="profile-avatar">
                                            <img src="<?= htmlspecialchars($post['avatar']) ?>"
                                                alt="<?= htmlspecialchars($post['username']) ?>">
                                        </div>
                                        <a href="/community/user/<?= $post['user_id'] ?>" target="_blank"
                                            class="<?= htmlspecialchars($userNameClass) ?>">
                                            <?php if (!empty($badgeClass)): ?>
                                            <i class="badg-level <?= htmlspecialchars($badgeClass) ?>"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($post['username']) ?>
                                            <?php if (!empty($rankName)): ?>
                                            <span><?= htmlspecialchars($rankName) ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="ztag ml-2">
                                        <span class="dot m-hide"></span>
                                        <a href="/community/board?tags=<?= $tagName ?>" title="<?= $tagClass ?>">
                                            <span class="zt-blue mr-2">#<?= $tagName ?></span>
                                        </a>
                                        <div class="time d-inline">
                                            <span>
                                                <i class="dot"></i>
                                                <?= htmlspecialchars(timeAgo($post['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="article-name mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                                <div class="cda-content">
                                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                </div>
                                <div class="cda-bottom">
                                    <div class="d-flex justify-content-between align-itmes-center">
                                        <div class="float-left">
                                            <a href="#content-comments" class="btn">
                                                <i class="fas fa-reply mr-2"></i>
                                                Reply
                                            </a>
                                            <a class="btn btn-upvote p-btn-vote <?= $myVote === 1 ? 'active' : '' ?>"
                                                data-type="1">
                                                <i class="far fa-thumbs-up mr-2"></i>
                                                <span class="value"><?= $votes['upvotes'] ?></span>
                                            </a>
                                            <a class="btn btn-downvote p-btn-vote <?= $myVote === 0 ? 'active' : '' ?>"
                                                data-type="0">
                                                <i class="far fa-thumbs-down mr-2"></i>
                                                <span class="value"><?= $votes['downvotes'] ?></span>
                                            </a>
                                        </div>
                                        <?php if ($roles > 0): ?>
                                            <div class="float-left">
                                                <a class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h mr-1"></i>More
                                                </a>
                                        
                                                <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal">
                                        
                                                    <?php if ($post['is_pinned'] == 1): ?>
                                                        <a class="dropdown-item post-action"
                                                           href="javascript:;" 
                                                           data-action="unpin" 
                                                           data-id="<?= htmlspecialchars($post['id']) ?>">
                                                           Unpin Post
                                                        </a>
                                                    <?php else: ?>
                                                        <a class="dropdown-item post-action"
                                                           href="javascript:;" 
                                                           data-action="pin" 
                                                           data-id="<?= htmlspecialchars($post['id']) ?>">
                                                           Pin Post
                                                        </a>
                                                    <?php endif; ?>
                                        
                                                    <a class="dropdown-item post-action"
                                                       href="javascript:;" 
                                                       data-action="delete" 
                                                       data-id="<?= htmlspecialchars($post['id']) ?>">
                                                       Delete Post
                                                    </a>

                                                    <a class="dropdown-item ac-user"
                                                       href="javascript:;"
                                                       data-toggle="modal"
                                                       data-target="#actionModal"
                                                       data-user-id="<?= (int)$post['user_id'] ?>">
                                                       <?= $isAuthorBlacklisted ? 'Unblacklist User' : 'Blacklist User' ?>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div id="content-comments" class="comments-wrap p-0"
                                style="margin-bottom: 30px; margin-top: 20px;">
                                <div class="loading-relative">
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
            <!--/End: award-->
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const post = {
        id: '<?= htmlspecialchars($post['id']) ?>'
    };
    </script>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons'
        async='async'></script>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"
        integrity="sha384-/KNQL8Nu5gCHLqwqfQjA689Hhoqgi2S84SNUxC3roTe4EhJ9AfLkp8QiQcU8AMzI" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
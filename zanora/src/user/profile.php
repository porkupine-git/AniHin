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
// ================================
// 🔥 Avatar groups mapping
// ================================

$avatarGroups = [
    'dragonball' => [
        "/dragon_ball/av-db-01.jpeg",
        "/dragon_ball/av-db-02.jpeg",
        "/dragon_ball/av-db-03.jpeg",
        "/dragon_ball/av-db-04.jpeg",
        "/dragon_ball/av-db-05.jpeg",
        "/dragon_ball/av-db-06.jpeg"
    ],
    'onepiece' => [
        "/one_piece/user-00.jpeg",
        "/one_piece/user-01.jpeg",
        "/one_piece/user-02.jpeg",
        "/one_piece/user-03.jpeg",
        "/one_piece/user-04.jpeg",
        "/one_piece/user-05.jpeg",
        "/one_piece/user-06.jpeg",
        "/one_piece/user-07.jpeg",
        "/one_piece/user-08.jpeg",
        "/one_piece/user-09.jpeg",
        "/one_piece/user-10.jpeg",
        "/one_piece/user-11.jpeg",
        "/one_piece/03.jpg",
        "/one_piece/04.jpg",
        "/one_piece/05.jpg",
        "/one_piece/18.jpg",
        "/one_piece/File1.png",
        "/one_piece/File2.png",
        "/one_piece/File3.png",
        "/one_piece/File4.png",
        "/one_piece/File5.png",
        "/one_piece/File6.png",
        "/one_piece/File7.png",
        "/one_piece/File8.png"
    ],
    'zorochibi' => [
        "/zoro_chibi/avatar-02.png",
        "/zoro_chibi/avatar-03.png",
        "/zoro_chibi/avatar-04.png",
        "/zoro_chibi/avatar-05.png",
        "/zoro_chibi/avatar-06.png",
        "/zoro_chibi/avatar-08.png",
        "/zoro_chibi/avatar-09.png",
        "/zoro_chibi/avatar-10.png",
        "/zoro_chibi/avatar-11.png",
        "/zoro_chibi/avatar-12.png",
        "/zoro_chibi/avatar2-01.png",
        "/zoro_chibi/avatar2-02.png",
        "/zoro_chibi/avatar2-03.png",
        "/zoro_chibi/avatar2-04.png",
        "/zoro_chibi/avatar2-05.png",
        "/zoro_chibi/avatar2-06.png",
        "/zoro_chibi/avatar2-07.png",
        "/zoro_chibi/avatar2-08.png",
        "/zoro_chibi/avatar2-09.png",
        "/zoro_chibi/avatar2-10.png"
    ],
    'zoronormal' => [
        "/zoro_normal/av-zz-01.jpeg",
        "/zoro_normal/av-zz-02.jpeg",
        "/zoro_normal/av-zz-03.jpeg",
        "/zoro_normal/av-zz-04.jpeg",
        "/zoro_normal/av-zz-05.jpeg",
        "/zoro_normal/av-zz-06.jpeg",
        "/zoro_normal/av-zz-07.jpeg",
        "/zoro_normal/av-zz-08.jpeg",
        "/zoro_normal/av-zz-09.jpeg",
        "/zoro_normal/av-zz-10.jpeg",
        "/zoro_normal/av-zz-11.jpeg",
        "/zoro_normal/av-zz-12.jpeg"
    ],
    'mha' => [
        "/mha/avatar-17.png",
        "/mha/avatar-18.png",
        "/mha/avatar-19.png",
        "/mha/avatar-20.png",
        "/mha/avatar-21.png",
        "/mha/avatar-22.png",
        "/mha/avatar-23.png",
        "/mha/avatar-24.png",
        "/mha/avatar-25.png"
    ],
    'dragonballchibi' => [
        "/dragon_ball_chibi/bulma.png",
        "/dragon_ball_chibi/buumap.png",
        "/dragon_ball_chibi/cell.png",
        "/dragon_ball_chibi/freezer.png",
        "/dragon_ball_chibi/gogeta.png",
        "/dragon_ball_chibi/gohan.png",
        "/dragon_ball_chibi/goku.png",
        "/dragon_ball_chibi/goku-saiyan.png",
        "/dragon_ball_chibi/gotenk.png",
        "/dragon_ball_chibi/kaio.png",
        "/dragon_ball_chibi/krillin.png",
        "/dragon_ball_chibi/mabu.png",
        "/dragon_ball_chibi/mrsatan.png",
        "/dragon_ball_chibi/MutenRoshi.png",
        "/dragon_ball_chibi/picolo.png",
        "/dragon_ball_chibi/tenshihan.png",
        "/dragon_ball_chibi/trunks.png",
        "/dragon_ball_chibi/vegeta.png",
        "/dragon_ball_chibi/videl.png",
        "/dragon_ball_chibi/beerus.png"
    ],
    'attackontitan' => [
        "/attack_on_titan/20.jpg",
        "/attack_on_titan/aot_01.png",
        "/attack_on_titan/aot_02.png",
        "/attack_on_titan/aot_03.png",
        "/attack_on_titan/aot_04.png",
        "/attack_on_titan/aot_05.png",
        "/attack_on_titan/aot_06.png",
        "/attack_on_titan/aot_07.png",
        "/attack_on_titan/aot_08.png",
        "/attack_on_titan/aot_09.png",
        "/attack_on_titan/aot_10.png"
    ],
    'conan' => [
        "/conan/06.jpg",
        "/conan/07.jpg",
        "/conan/8.png",
        "/conan/9.png",
        "/conan/10.png",
        "/conan/11.png",
        "/conan/12.png",
        "/conan/13.png",
        "/conan/14.png"
    ],
    'naruto' => [
        "/naruto/08.jpg",
        "/naruto/09.jpg",
        "/naruto/11.jpg",
        "/naruto/naruto_01.png",
        "/naruto/naruto_02.png",
        "/naruto/naruto_03.png",
        "/naruto/naruto_04.png",
        "/naruto/naruto_05.png",
        "/naruto/naruto_06.png",
        "/naruto/naruto_07.png",
        "/naruto/naruto_08.png",
        "/naruto/naruto_09.png",
        "/naruto/naruto_010.png"
    ],
    'onepunchman' => [
        "/one_punch_man/14.jpg",
        "/one_punch_man/opm_01.png",
        "/one_punch_man/opm_02.png",
        "/one_punch_man/opm_03.png",
        "/one_punch_man/opm_04.png",
        "/one_punch_man/opm_05.png",
        "/one_punch_man/opm_06.png",
        "/one_punch_man/opm_07.png",
        "/one_punch_man/opm_08.png",
        "/one_punch_man/opm_09.png",
        "/one_punch_man/opm_010.png"
    ],
    'sakura' => [
        "/sakura/10.jpg"
    ],
    'tha' => [
        "/tha/01.jpg",
        "/tha/02.jpg",
        "/tha/12.jpg",
        "/tha/13.jpg",
        "/tha/15.jpg",
        "/tha/16.jpg",
        "/tha/17.jpg",
        "/tha/19.jpg"
    ],
    'demonsplayer' => [
        "/demon_splayer/File1.jpg",
        "/demon_splayer/File2.jpg",
        "/demon_splayer/File3.jpg",
        "/demon_splayer/File4.jpg",
        "/demon_splayer/File5.jpg",
        "/demon_splayer/File6.jpg",
        "/demon_splayer/File7.jpg",
        "/demon_splayer/File8.jpg",
        "/demon_splayer/File9.jpg",
        "/demon_splayer/File10.jpg",
        "/demon_splayer/File11.jpg",
        "/demon_splayer/File12.jpg",
        "/demon_splayer/File13.jpg",
        "/demon_splayer/File14.jpg",
        "/demon_splayer/File15.jpg",
        "/demon_splayer/File16.jpg",
        "/demon_splayer/File17.jpg",
        "/demon_splayer/File18.jpg",
        "/demon_splayer/File19.jpg",
        "/demon_splayer/File20.jpg"
    ],
    'jujutsukaisen' => [
        "/jujutsu_kaisen/File1.png",
        "/jujutsu_kaisen/File2.png",
        "/jujutsu_kaisen/File3.png",
        "/jujutsu_kaisen/File4.png",
        "/jujutsu_kaisen/File5.png",
        "/jujutsu_kaisen/File6.png",
        "/jujutsu_kaisen/File7.png",
        "/jujutsu_kaisen/File8.png",
        "/jujutsu_kaisen/File9.png",
        "/jujutsu_kaisen/File10.png",
        "/jujutsu_kaisen/File11.png",
        "/jujutsu_kaisen/File12.png",
        "/jujutsu_kaisen/File13.png",
        "/jujutsu_kaisen/File14.png"
    ],
    'chainsaw' => [
        "/chainsaw/01.png",
        "/chainsaw/02.png",
        "/chainsaw/03.png",
        "/chainsaw/04.png",
        "/chainsaw/05.png",
        "/chainsaw/06.png",
        "/chainsaw/07.png",
        "/chainsaw/08.png",
        "/chainsaw/09.png"
    ],
    'spyfamily' => [
        "/spy_family/01.png",
        "/spy_family/02.png",
        "/spy_family/03.png",
        "/spy_family/04.png",
        "/spy_family/05.png",
        "/spy_family/06.png",
        "/spy_family/07.png",
        "/spy_family/08.png"
    ],
    'berserk' => [
        "/berserk/berserk_01.png",
        "/berserk/berserk_02.png",
        "/berserk/berserk_03.png",
        "/berserk/berserk_04.png",
        "/berserk/berserk_05.png",
        "/berserk/berserk_06.png",
        "/berserk/berserk_07.png",
        "/berserk/berserk_08.png",
        "/berserk/berserk_09.png",
        "/berserk/berserk_010.png"
    ],
    'bleach' => [
        "/bleach/bleanch_01.png",
        "/bleach/bleanch_02.png",
        "/bleach/bleanch_03.png",
        "/bleach/bleanch_04.png",
        "/bleach/bleanch_05.png",
        "/bleach/bleanch_06.png",
        "/bleach/bleanch_07.png",
        "/bleach/bleanch_08.png",
        "/bleach/bleanch_09.png",
        "/bleach/bleanch_010.png"
    ],
    'deadnote' => [
        "/deadnote/dead_note_01.png",
        "/deadnote/dead_note_02.png",
        "/deadnote/dead_note_03.png",
        "/deadnote/dead_note_04.png",
        "/deadnote/dead_note_05.png",
        "/deadnote/dead_note_06.png",
        "/deadnote/dead_note_07.png",
        "/deadnote/dead_note_08.png",
        "/deadnote/dead_note_09.png",
        "/deadnote/dead_note_010.png"
    ],
    'haikyu' => [
        "/haikyu/haikyu_01.png",
        "/haikyu/haikyu_02.png",
        "/haikyu/haikyu_03.png",
        "/haikyu/haikyu_04.png",
        "/haikyu/haikyu_05.png",
        "/haikyu/haikyu_06.png",
        "/haikyu/haikyu_07.png",
        "/haikyu/haikyu_08.png",
        "/haikyu/haikyu_09.png",
        "/haikyu/haikyu_010.png",
        "/haikyu/haikyu_011.png"
    ]
];
// ================================
// Helper to check if active tab
// ================================
function isActiveTab($group, $currentAvatar, $avatarGroups, $forPane = false) {
    // If group folder name exists in URL, mark it active
    if (stripos($currentAvatar, "/$group/") !== false) {
        return $forPane ? 'show active' : 'active';
    }
    // Otherwise fallback: match filename list
    if (isset($avatarGroups[$group])) {
        foreach ($avatarGroups[$group] as $filename) {
            if (stripos($currentAvatar, $filename) !== false) {
                return $forPane ? 'show active' : 'active';
            }
        }
    }
    return '';
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
    <title>Edit Profile - <?= $websiteTitle ?></title>
    <meta name="description" content="Update your avatar, password, and profile information on <?= $websiteTitle ?>.">
    <meta name="keywords" content="edit profile, user settings, avatar, change password, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/user/profile" />

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
    <meta property="og:url" content="<?= WEBSITE_URL ?>/user/profile">
    <meta property="og:title" content="Edit Profile - <?= $websiteTitle ?>">
    <meta property="og:description" content="Update your avatar, password, and profile information on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Edit Profile - <?= $websiteTitle ?>">
    <meta name="twitter:description" content="Update your avatar, password, and profile information on <?= $websiteTitle ?>.">
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
        <div id="main-wrapper" class="layout-page layout-profile">
            <div class="profile-header">
                <div class="profile-header-cover"
                    style="background-image: url(<?= htmlspecialchars($user['image']) ?>);"></div>
                <div class="container">
                    <div class="ph-title">Hi, <?= htmlspecialchars($user['username']) ?></div>
                    <div class="ph-tabs">
                        <div class="bah-tabs">
                            <ul class="nav nav-tabs pre-tabs">
                                <li class="nav-item"><a class="nav-link active" href="/user/profile"><i
                                            class="fas fa-user mr-2"></i>Profile</a></li>
                                <li class="nav-item"><a class="nav-link " href="/user/continue-watching"><i
                                            class="fas fa-history mr-2"></i>Continue Watching
                                    </a></li>
                                <li class="nav-item"><a class="nav-link " href="/user/watch-list"><i
                                            class="fas fa-heart mr-2"></i>Watch
                                        List</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/notification"><i
                                            class="fas fa-bell mr-2"></i>Notification</a></li>
                                <li class="nav-item"><a class="nav-link" href="/user/connections"><i
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
                    <div class="profile-box profile-box-account makeup">
                        <h2 class="h2-heading mb-4"><i class="fas fa-user mr-3"></i>Edit Profile</h2>
                        <div class="block_area-content">
                            <!-- Avatar -->
                            <div class="show-profile-avatar text-center mb-3">
                                <div class="profile-avatar d-inline-block" data-toggle="modal"
                                    data-target="#modalavatars">
                                    <div class="pa-edit"><i class="fas fa-pen"></i></div>
                                    <?php if (!empty($user['image'])): ?>
                                    <img id="preview-avatar" src="<?= htmlspecialchars($user['image']) ?>"
                                        alt="Profile Picture">
                                    <?php else: ?>
                                    <img id="preview-avatar" src="<?= htmlspecialchars($user['avatar_url']) ?>"
                                        alt="Default Avatar">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Profile Form -->
                            <form class="preform" method="post" id="profile-form">
                                <input type="hidden" name="avatar_id" value="">
                                <div class="row">
                                    <!-- Email -->
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label class="prelabel" for="pro5-email">Email address</label>
                                            <input type="email" name="email" class="form-control"
                                                <?= $user['is_verified'] ? 'readonly' : '' ?> id="pro5-email"
                                                value="<?= htmlspecialchars($user['email']) ?>">
                                            <?php if ($user['is_verified']): ?>
                                            <div class="d-block d-verify is-yes">
                                                <div class="span-v"><i class="fas fa-user-check mr-2"></i>Verified</div>
                                            </div>
                                            <?php else: ?>
                                            <div id="note-change-email" class="small mt-2" style="color:#aaa;">
                                                You will be able to change the email <b>ONCE ONLY</b>. Please enter your
                                                correct email address.
                                            </div>
                                            <div class="d-block d-verify is-no mt-2">
                                                <div class="span-v"><i class="fas fa-user-alt-slash mr-2"></i>Not
                                                    Verified</div>
                                                <div class="note">
                                                    Your account has not been verified.
                                                    <a href="javascript:;" class="highlight-text"
                                                        id="sendEmailVerify" style="text-decoration: underline !important;"><strong>Click here</strong></a> to resend
                                                    verification email.
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Username -->
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label class="prelabel" for="pro5-name">Your name</label>
                                            <input type="text" class="form-control" id="pro5-name" name="name" required
                                                value="<?= htmlspecialchars($user['username']) ?>">
                                        </div>
                                    </div>
                                    <!-- Joined Date -->
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <label class="prelabel" for="pro5-join">Joined</label>
                                            <input type="text" class="form-control" disabled id="pro5-join"
                                                value="<?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))) ?>">
                                        </div>
                                    </div>
                                    <!-- Change Password -->
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <div class="block">
                                            <a class="btn btn-sm btn-clear collapsed" data-toggle="collapse"
                                                href="#show-changepass" aria-expanded="false"><i
                                                    class="fas fa-key mr-2"></i>Change password</a>
                                        </div>
                                        <div id="show-changepass" class="mt-3 collapse">
                                            <div class="form-group">
                                                <label class="prelabel" for="pro5-currentpass">Current Password</label>
                                                <input type="password" class="form-control" id="pro5-currentpass"
                                                    name="current_password">
                                            </div>
                                            <div class="form-group">
                                                <label class="prelabel" for="pro5-pass">New Password</label>
                                                <input type="password" class="form-control" id="pro5-pass"
                                                    name="new_password">
                                            </div>
                                            <div class="form-group">
                                                <label class="prelabel" for="pro5-confirm">Confirm New Password</label>
                                                <input type="password" class="form-control" id="pro5-confirm"
                                                    name="confirm_new_password">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Save -->
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <div class="form-group">
                                            <div class="mt-4"></div>
                                            <button class="btn btn-block btn-primary">Save</button>
                                            <div class="loading-relative" id="profile-loading" style="display:none">
                                                <div class="loading">
                                                    <div class="span1"></div>
                                                    <div class="span2"></div>
                                                    <div class="span3"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php include 'src/component/footer.php' ?>
	</div>
    <?php
    $currentAvatar = $user['image'] ?? '';
    include 'public/avatar/avatar.php' ?>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
    const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6917137850eca9f240725177&product=inline-share-buttons' async='async'></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script>
    $('#profile-form').submit(function(e) {
        e.preventDefault();
        $('#profile-loading').show();
        var formData = $(this).serialize();
        $.post('/ajax/user/profile', formData, function(res) {
            $('#profile-loading').hide();
            if (res.status) {
                $('#pro5-currentpass').val('');
                $('#pro5-pass').val('');
                $('#pro5-confirm').val('');
                $('#show-changepass').collapse('hide');
                toastr.success(res.msg, '', {
                    timeout: 6000
                });
                if ($('#note-change-email').length > 0) {
                    window.location.reload();
                }
            } else {
                var err = '';
                if (res.errors) {
                    res.errors.forEach(e => {
                        err += `- ${e}</br>`;
                    })
                }
                toastr.error(err, res.msg, {
                    timeout: 6000
                }).css('max-width', '300px');
            }
        });
    });
    $('.item-avatar').click(function() {
        $('.item-avatar').removeClass('active');
        $(this).addClass('active');
        $('#preview-avatar').attr('src', $(this).find('img').attr('src'));
        $('input[name=avatar_id]').val($(this).data('id'));
    });
    var loadingSendEmail = false;
    $('#sendEmailVerify').click(function() {
        if (!loadingSendEmail) {
            loadingSendEmail = true;
            $.post('/ajax/user/send-email-verify', function(res) {
                if (res.status) {
                    toastr.success(res.msg, '', {
                        timeout: 6000
                    });
                } else {
                    toastr.error(res.msg, '', {
                        timeout: 6000
                    });
                }
            });
        }
    })
    </script>
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
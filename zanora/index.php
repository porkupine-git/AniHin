<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';
$apiUrl = $zpi . '/top-search';

$data = cacheRemember(
    "top_search",
    3600, // 5 minutes
    function () use ($apiUrl) {
        $response = file_get_contents($apiUrl);
        if (!$response) return null;

        $decoded = json_decode($response, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }
);

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

/* Tag map (your config) */
$tagsMap = [
    1 => ['name'=>'Suggestion', 'class'=>'zt-green'],
    2 => ['name'=>'Question',   'class'=>'zt-yellow'],
    3 => ['name'=>'Discussion', 'class'=>'zt-purple'],
    4 => ['name'=>'General',    'class'=>'zt-blue'],
    5 => ['name'=>'Updates',    'class'=>'zt-red'],
    6 => ['name'=>'Feedback',   'class'=>'zt-orange'],
];

/**
 * Get trending posts in last 24h.
 * Trending score = comments*2 + (post_likes - post_dislikes) + comment_votes_sum
 */
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
    3600, // 5 minutes
    function () use ($conn) {
        return getTrendingPosts24h($conn, 6);
    }
);
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />

    <!-- SEO Meta Tags -->
    <title><?= $websiteTitle ?> – Watch Anime Online in English Sub & Dub</title>
    <meta name="description" content="Watch anime online in English sub and dub. Stream free anime episodes in HD with daily updates on <?= $websiteTitle ?>. No registration required!">
    <meta name="keywords" content="anime to watch, watch anime, anime online, free anime online, online anime, anime streaming, stream anime online, english anime, english dubbed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/" />

    <!-- Performance Optimization: DNS Prefetch & Preconnect -->
    <link rel="dns-prefetch" href="https://s4.anilist.co">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://s4.anilist.co" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Open Graph (Facebook / Discord) -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= WEBSITE_URL ?>/">
    <meta property="og:title" content="<?= $websiteTitle ?> – Watch Anime Online in English Sub & Dub">
    <meta property="og:description" content="Watch anime online in English sub and dub. Stream free anime episodes in HD.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Card Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $websiteTitle ?> – Watch Anime Online in English Sub & Dub">
    <meta name="twitter:description" content="Watch anime online in English sub and dub. Stream free anime episodes in HD.">
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/home.css?v=<?= $versionCss1 ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </noscript>

    </head>

<body>
    <div id="xwrapper">
        <!--Begin: Header-->
        <div id="xheader">
            <div class="container">
                <div id="xheader_browser">
                    <div class="header-btn"><i class="fas fa-bars mr-2"></i>Menu</div>
                </div>
                <div id="xheader_menu">
                    <ul class="nav header_menu-list">
                        <li class="nav-item"><a href="/home" title="Home">Home</a></li>
                        <li class="nav-item"><a href="/movie" title="Movies">Movies</a></li>
                        <li class="nav-item"><a href="/tv" title="TV Series">TV Series</a>
                        </li>
                        <li class="nav-item"><a href="/most-popular" title="Most Popular">Most
                                Popular</a>
                        </li>
                        <li class="nav-item"><a href="/top-airing" title="Top Airing">Top Airing</a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!--End: Header-->
        <!--Begin: Main-->
        <div id="xmain-wrapper">
            <div id="mw-top">
                <div class="container">
                    <div class="mwt-content">
                        <div class="mwt-icon">
                            <picture>
                                <source srcset="/images/zorowaifu.webp" type="image/webp">
                                <img src="/images/zorowaifu.png" alt="Zanora Anime Character" width="650" height="433" loading="lazy" decoding="async">
                            </picture>
                        </div>
                        <div class="mwh-logo">
                            <a href="/home" class="mwh-logo-div">
                                <img src="<?= $websiteLogo ?>" alt="<?= $websiteTitle ?>" width="165" height="40" decoding="async">
                            </a>
                            <div class="clearfix"></div>
                        </div>
                        <div id="xsearch" class="home-search">
                            <div class="search-content">
                                <form action="/search" autocomplete="off" id="search-form">
                                    <div class="search-submit">
                                        <div class="search-icon btn-search" id="search-button"><i
                                                class="fa fa-search"></i></div>
                                    </div>
                                    <input type="text" class="form-control search-input" name="keyword"
                                        placeholder="Search anime..." required>
                                </form>
                            </div>
                            <div class="xhashtag" id="topSearch">
                                <span class="title">Top search:</span>
                                <?php if (!empty($data['success']) && !empty($data['results'])): ?>
                                    <?php 
                                        $results = array_slice($data['results'], 0, 10);
                                        foreach ($results as $i => $item): 
                                            $isLast = $i === count($results) - 1;
                                    ?>
                                        <a 
                                          href="<?= htmlspecialchars($item['link']) ?>" 
                                          class="item"<?= $isLast ? ' data-last="true"' : '' ?>>
                                            <?= htmlspecialchars($item['title']) ?></a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="item">No trending searches.</span>
                                <?php endif; ?>
                            </div>
                            <div class="clearfix"></div>

                            <div id="action-button">
                                <a href="/home" class="btn btn-lg btn-radius btn-primary">Watch Anime <i
                                        class="fas fa-arrow-circle-right ml-4"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mw-body">
                <div class="container">
                    <div class="share-buttons share-buttons-detail">
                        <div class="share-buttons-block">
                            <div class="share-icon"></div>
                            <div class="sbb-title mr-3">
                                <span>Share <?=$websiteTitle?></span>
                                <p class="mb-0">to your friends</p>
                            </div>
                            <div class="sbb-social">
                                <div class="sharethis-inline-share-buttons"></div>
                                <div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mwb-2col">
                        <div class="mwb-left">
                            <h1 class="mw-heading">
                                <?= $websiteTitle ?> - The best site to watch anime online for Free
                            </h1>
                            <p>Do you know that according to Google, the monthly search volume for anime related
                                topics
                                is
                                up to over 1 Billion times? Anime is famous worldwide and it is no wonder we've
                                seen a
                                sharp
                                rise in the number of free anime streaming sites.</p>
                            <p>Just like free online movie streaming sites, anime watching sites are not created
                                equally,
                                some are better than the rest, so we've decided to build
                                <?= $websiteTitle ?> to be one of
                                the best
                                free
                                anime streaming site for all anime fans on the world.
                            </p>
                            <h2>1/ What is
                                <?= $websiteTitle ?>?
                            </h2>
                            <p>
                                <?= $websiteTitle ?> is a free site to watch anime and you can even download
                                subbed or
                                dubbed anime in
                                ultra HD quality without any registration or payment. By having No Ads in all
                                kinds, we
                                are
                                trying to make it the safest site for free anime.
                            </p>
                            <h2>2/ Is
                                <?= $websiteTitle ?> safe?
                            </h2>
                            <p>Yes we are, we do have only one Ads to cover the server cost and we keep scanning
                                the ads
                                24/7 to
                                make sure all are clean, If you find any ads that is suspicious, please forward
                                us the
                                info and we will
                                remove it.</p>
                            <h2>3/ So what make
                                <?= $websiteTitle ?> the best site to watch anime free online?
                            </h2>
                            <p>Before building
                                <?= $websiteTitle ?>, we've checked many other free anime sites, and learnt
                                from them.
                                We
                                only keep the good things and remove all the bad things from all the
                                competitors, to put
                                it
                                in our
                                <?= $websiteTitle ?> website. Let's see how we're so confident about being the
                                best
                                site for
                                anime
                                streaming:
                            </p>
                            <ul>
                                <li><strong>Safety:</strong> We try our best to not having harmful ads on
                                    <?= $websiteTitle ?>.
                                </li>
                                <li><strong>Content library:</strong> Our main focus is anime. You can find here
                                    popular,
                                    classic, as well as current titles from all genres such as action, drama,
                                    kids,
                                    fantasy,
                                    horror, mystery, police, romance, school, comedy, music, game and many more.
                                    All
                                    these
                                    titles come with English subtitles or are dubbed in many languages.
                                </li>
                                <li><strong>Quality/Resolution:</strong> All titles are in excellent resolution,
                                    the
                                    best
                                    quality possible.
                                    <?= $websiteTitle ?> also has a quality setting function to make
                                    sure our users
                                    can
                                    enjoy streaming no matter how fast your Internet speed is. You can stream
                                    the anime
                                    at
                                    360p if your Internet is being ridiculous, Or if it is good, you can go with
                                    720p or
                                    even 1080p anime.
                                </li>
                                <li><strong>Streaming experience:</strong> Compared to other anime streaming
                                    sites, the
                                    loading speed at
                                    <?= $websiteTitle ?> is faster. Downloading is just as easy as
                                    streaming, you
                                    won't
                                    have any problem saving the videos to watch offline later.
                                </li>
                                <li><strong>Updates:</strong> We updates new titles as well as fulfill the
                                    requests on a
                                    daily basis so be warned, you will never run out of what to watch on
                                    <?= $websiteTitle ?>.
                                </li>
                                <li><strong>User interface:</strong> Our UI and UX makes it easy for anyone, no
                                    matter
                                    how
                                    old you are, how long have you been on the Internet. Literally, you can
                                    figure out
                                    how
                                    to navigate our site after a quick look. If you want to watch a specific
                                    title,
                                    search
                                    for it via the search box. If you want to look for suggestions, you can use
                                    the
                                    site's
                                    categories or simply scroll down for new releases.
                                </li>
                                <li><strong>Device compatibility:</strong>
                                    <?= $websiteTitle ?> works alright on both your
                                    mobile and
                                    desktop. However, we'd recommend you use your desktop for a smoother
                                    streaming
                                    experience.
                                </li>
                                <li><strong>Customer care:</strong> We are in active mode 24/7. You can always
                                    contact
                                    us
                                    for any help, query, or business-related inquiry. On our previous projects,
                                    we were
                                    known for our great customer service as we were quick to fix broken links or
                                    upload
                                    requested content.
                                </li>
                            </ul>
                            <p>So if you're looking for a trustworthy and safe site for your Anime streaming,
                                let's give
                                <?= $websiteTitle ?> a try. And if you like us, please help us to spread the
                                words and
                                do
                                not forget
                                to
                                bookmark our site.
                            </p>
                            <p>Thank you!</p>
                            <p>&nbsp;</p>
                        </div>
                        <div class="mwb-right">
                            <div class="zr-connect zr-connect-list">
                                <h2 class="heading-news">Trending Posts</h2>
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
                                                // no excerpt – CSS will handle truncation
                                                $bodyRaw      = $post['content'] ?? '';
                            
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
                                                    <div><?= nl2br(e($bodyRaw)) ?></div>
                                                </div>
                            
                                                <div class="cn-owner">
                                                    <div class="profile-avatar">
                                                        <img src="<?= $avatar ?>" alt="<?= $username ?>" loading="lazy" decoding="async" width="40" height="40">
                                                    </div>
                            
                                                    <a href="/community/user/<?= $userId ?>" target="_blank" class="<?= $userNameClasses ?>">
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
                                    <?php else: ?>
                                        <p class="mb-0 px-2">No trending posts in last 24 hours.</p>
                                    <?php endif; ?>
                            
                                </div>
                            
                                <div class="d-block">
                                    <a href="/community/board"
                                       class="btn btn-sm py-2 btn-block btn-radius btn-blank text-white"
                                       style="background-color: rgba(255,255,255,.1);">
                                        Read more
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--End: Main-->
        <!--Begin: Footer-->
        <div id="xfooter-about">
            <div class="container">
                <p class="copyright">©
                    <?php echo date("Y"); ?> <a href="/"><?= $websiteUrlh ?></a>. All rights
                    reserved.
                </p>
            </div>
        </div>
        <!--End: Footer-->
    </div>
    </div>
    </div>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js?v=<?= $versionJs2 ?>');
        });
    }
    window.addEventListener('load', function() {
        setTimeout(function() {
            var st = document.createElement('script');
            st.src = 'https://platform-api.sharethis.com/js/sharethis.js#property=6973d5aa6473557d10606f1e&product=sop';
            st.async = true;
            document.body.appendChild(st);
        }, 1000);
    });
    $(document).ready(function() {
        $("#xheader_browser").click(function(e) {
            $("#xheader_menu, #xheader_browser").toggleClass("active");
        });
        $('.btn-search').click(function() {
            if ($('.search-input').val().trim() !== "") {
                $('#search-form').submit();
            }
        });
    });
    </script>
	<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
	<?php echo GLOBAL_AD_CODE; ?>
</body>

</html>
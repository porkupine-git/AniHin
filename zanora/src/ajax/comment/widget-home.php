<?php
/**
 * /src/ajax/comment/widget-home.php
 * Returns newest + top comments for homepage discussion widget
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/component/anime/qtip.php';

if (!isset($conn) || $conn->connect_errno) {
    echo json_encode(['html' => '', 'error' => 'Database connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* 🔹 Time ago helper */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return $diff . ' seconds ago';
    $diff = round($diff / 60);
    if ($diff < 60) return $diff . ' minutes ago';
    $diff = round($diff / 60);
    if ($diff < 24) return $diff . ' hours ago';
    $diff = round($diff / 24);
    if ($diff < 30) return $diff . ' days ago';
    $diff = round($diff / 30);
    if ($diff < 12) return $diff . ' months ago';
    return round($diff / 12) . ' years ago';
}

/* 🔹 Latest 15 comments */
$latest = $conn->query("
    SELECT c.id, c.content, c.created_at, c.movie_id, c.episode_id,
           u.id AS user_id, u.username, u.image, u.points, u.roles,
           COALESCE(v.upvotes,0)  AS upvotes,
           COALESCE(v.downvotes,0) AS downvotes
    FROM comments c
    JOIN users u ON u.id = c.user_id
    LEFT JOIN (
        SELECT comment_id,
               SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
               SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
        FROM comment_votes
        GROUP BY comment_id
    ) v ON v.comment_id = c.id
    WHERE c.status = 'active'
    ORDER BY c.created_at DESC
    LIMIT 15
");

/* 🔹 Get top comments dynamically */
function getTopComments($conn, $days) {
    return $conn->query("
        SELECT c.id, c.content, c.created_at, c.movie_id, c.episode_id,
               u.id AS user_id, u.username, u.image, u.points, u.roles,
               COALESCE(v.upvotes,0)  AS upvotes,
               COALESCE(v.downvotes,0) AS downvotes
        FROM comments c
        JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) v ON v.comment_id = c.id
        WHERE c.status = 'active'
          AND c.created_at >= NOW() - INTERVAL {$days} DAY
        ORDER BY (COALESCE(v.upvotes,0) - COALESCE(v.downvotes,0)) DESC, c.created_at DESC
        LIMIT 15
    ");
}

/* 🔹 Find interval with comments */
$days = 1;
$maxDays = 30;
$top = [];

while ($days <= $maxDays) {
    $res = getTopComments($conn, $days);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $top[] = $row;
            if (count($top) >= 15) break;
        }
        break;
    }
    $days++;
}

/* 🔹 Fallback: all-time top */
if (count($top) < 15) {
    $res = $conn->query("
        SELECT c.id, c.content, c.created_at, c.movie_id, c.episode_id,
               u.id AS user_id, u.username, u.image, u.points, u.roles,
               COALESCE(v.upvotes,0) AS upvotes,
               COALESCE(v.downvotes,0) AS downvotes
        FROM comments c
        JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) v ON v.comment_id = c.id
        WHERE c.status = 'active'
        ORDER BY (COALESCE(v.upvotes,0) - COALESCE(v.downvotes,0)) DESC, c.created_at DESC
        LIMIT " . (15 - count($top))
    );
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $top[] = $row;
        }
    }
}

/* 🔹 Cache to prevent duplicate API calls */
$animeCache = [];

/* 🔹 Render Swiper slides */
function renderSwiper($res, &$animeCache) {
    $html = '<div class="dwl-ul"><div class="swiper-container"><div class="swiper-wrapper">';
    foreach ($res as $row) {
        $cid   = intval($row['id']);
        $user  = e($row['username']);
        $avatar= fix_avatar_url($row['image'] ?: '/public/images/no-avatar.jpeg');
        $content = e(mb_strimwidth($row['content'], 0, 120, '...'));
        $movieId = $row['movie_id'] ?: 0;
        $epId    = $row['episode_id'] ?: 0;
        $authorPoints = intval($row['points'] ?? 0);
        $authorIsAdmin = intval($row['roles'] ?? 0);

        if ($authorIsAdmin === 1) {
    // Admin
    $rankName   = 'Admin';
    $badgeClass = 'level-b';
    $rankIcon   = '/public/images/level-b.png';

} elseif ($authorIsAdmin === 2) {
    // Moderator
    $rankName   = 'Mod';
    $badgeClass = 'level-d';
    $rankIcon   = '/public/images/level-d.png';

} else {
    // Normal user rank from points
    list($rankName, $badgeClass, $rankIcon) = getRank($authorPoints);
}

    if ($authorIsAdmin === 1) {
    $userNameClass = 'user-name is-level-x is-level-b';     // Admin
}
elseif ($authorIsAdmin === 2) {
    $userNameClass = 'user-name is-level-x is-level-d';     // Mod
}
else {
    $userNameClass = 'user-name is-level-x' . ($badgeClass ? ' is-level-a' : '');
}
        $animeTitle = 'View Discussion';
        if ($movieId) {
            if (!isset($animeCache[$movieId])) {
                $animeCache[$movieId] = fetchAnimeData($movieId);
            }
            if (!empty($animeCache[$movieId]['title'])) {
                $animeTitle = e($animeCache[$movieId]['title']);
            }
        }

        $timeText = timeAgo($row['created_at']);
        $fullDate = date('Y-m-d H:i:s', strtotime($row['created_at']));

        $html .= "<div class='swiper-slide' data-id='{$cid}'>
            <div class='dwl-item'>
                <div class='comment-avatar'>
                    <div class='user-avatar'>
                        <a href='/community/user/{$row['user_id']}' target='_blank'>
                            <img src='" . e($avatar) . "' class='user-avatar-img' alt='" . e($user) . "'>
                        </a>
                    </div>
                </div>
                <div class='comment-inline'>
                    <div class='about'>
                        <a href='/community/user/{$row['user_id']}' target='_blank' class='" . e($userNameClass) . "'>";
        if (!empty($badgeClass)) {
            $html .= "<i class='badg-level " . e($badgeClass) . "'></i>";
        }
        $html .= e($user) . "</a>
                        <div class='time'>";
        if (!empty($rankName)) {
            $html .= "<span class='role mr-1'>" . e($rankName) . "</span>- ";
        }
        $html .= e($timeText);
        $html .= "</div>
                    </div>
                    <div class='text-cut'>" . e($content) . "</div>
                    <div class='on-chapt'>
                        <a href='/watch/{$movieId}?ep={$epId}&c_id={$cid}&c_type=episode'>
                            <i class='far fa-file-alt mr-1'></i>" . e($animeTitle) . "
                        </a>
                    </div>
                </div>
                <div class='clearfix'></div>
            </div>
        </div>";
    }

    $html .= "</div><div class='swiper-scrollbar'></div><div class='clearfix'></div></div></div>";
    return $html;
}

/* 🔹 Final HTML + JS init */
$html = "
<div id=\"top-comment\" class=\"tab-pane\">" . renderSwiper($top, $animeCache) . "</div>
<div id=\"new-comment\" class=\"tab-pane show active\">" . renderSwiper($latest, $animeCache) . "</div>
<script>
    var swCmTop = new Swiper('#top-comment .swiper-container', {
        slidesPerView: 5,
        spaceBetween: 20,
        freeMode: true,
        scrollbar: { el: '#top-comment .swiper-scrollbar' },
        breakpoints: {
            300: { slidesPerView: 'auto', spaceBetween: 15 },
            940: { slidesPerView: 4, spaceBetween: 15 },
            1199: { slidesPerView: 4, spaceBetween: 20 },
            1599: { slidesPerView: 5, spaceBetween: 20 }
        }
    });
    var swCmLatest = new Swiper('#new-comment .swiper-container', {
        slidesPerView: 5,
        spaceBetween: 20,
        freeMode: true,
        scrollbar: { el: '#new-comment .swiper-scrollbar' },
        breakpoints: {
            300: { slidesPerView: 'auto', spaceBetween: 15 },
            940: { slidesPerView: 4, spaceBetween: 15 },
            1199: { slidesPerView: 4, spaceBetween: 20 },
            1599: { slidesPerView: 5, spaceBetween: 20 }
        }
    });
    document.addEventListener('click', function(e) {
        var tab = e.target.closest('.cm-tab');
        if (!tab) return;
        e.preventDefault();
        e.stopPropagation();
        var targetId = tab.getAttribute('href');
        var targetPane = document.querySelector(targetId);
        if (!targetPane) return;
        document.querySelectorAll('.cm-tab').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        targetPane.classList.add('active');
        document.querySelector('#new-comment').classList.add('show');
        setTimeout(function() {
            if (targetId === '#top-comment' && swCmTop) swCmTop.update();
            if (targetId === '#new-comment' && swCmLatest) swCmLatest.update();
        }, 30);
    }, true);
</script>
";

/* ✅ Final response */
echo json_encode(['status' => true, 'html' => $html], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

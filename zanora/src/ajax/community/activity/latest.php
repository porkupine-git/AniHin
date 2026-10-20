<?php
header('Content-Type: application/json; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn->set_charset('utf8mb4');

/* --------- get user id from URL: /ajax/community/activity/latest/2 --------- */
$uriParts = explode('/', trim($_SERVER['REQUEST_URI'], '/')); 
// ajax / community / activity / latest / {id}
$userId = 0;
if (isset($uriParts[4]) && is_numeric($uriParts[4])) {
    $userId = (int)$uriParts[4];
}

// fallback: if no id in URL, use logged in user
if ($userId <= 0 && !empty($_SESSION['userID'])) {
    $userId = (int)$_SESSION['userID'];
}

if ($userId <= 0) {
    echo json_encode([
        'status' => false,
        'html'   => '<div class="wideview"><div class="wv"><div class="wv-list"><div class="wv-title"><div class="name mr-3">Latest Activities</div></div><div class="wv-ul"><div class="item"><div class="item-inner"><div class="sts"><div class="time text-muted">User not found.</div></div></div></div></div></div></div></div>'
    ]);
    exit;
}

/* --------- helpers --------- */

function time_ago_str(string $datetime): string {
    $ts = strtotime($datetime);
    if ($ts === false) return '';
    $diff = time() - $ts;
    if ($diff < 0) $diff = 0;

    if ($diff < 60) return 'a few seconds ago';
    if ($diff < 3600) {
        $m = floor($diff / 60);
        return $m . ' minute' . ($m > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $h = floor($diff / 3600);
        return $h . ' hour' . ($h > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 2 * 86400) return 'a day ago';
    if ($diff < 7 * 86400) {
        $d = floor($diff / 86400);
        return $d . ' days ago';
    }
    if ($diff < 30 * 86400) {
        $w = floor($diff / (7 * 86400));
        return $w . ' week' . ($w > 1 ? 's' : '') . ' ago';
    }
    $mo = floor($diff / (30 * 86400));
    return $mo . ' month' . ($mo > 1 ? 's' : '') . ' ago';
}

/**
 * Get anime title + poster via tracked_anime -> $zpi/info?id=
 */
function get_anime_info(string $movieId, mysqli $conn): array {
    global $zpi;

    $title  = '';
    $poster = '';

    // 1) tracked_anime
    if ($stmt = $conn->prepare("SELECT title, poster FROM tracked_anime WHERE anime_id = ? LIMIT 1")) {
        $stmt->bind_param('s', $movieId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $title  = $row['title']  ?? '';
                $poster = $row['poster'] ?? '';
            }
        }
        $stmt->close();
    }

    if ($title !== '' && $poster !== '') {
        return ['title' => $title, 'poster' => $poster];
    }

    // 2) API fallback
    $url = rtrim($zpi, '/') . '/info?id=' . urlencode($movieId);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $data = json_decode($response, true);
        if (!empty($data['success']) && !empty($data['results']['data'])) {
            $info   = $data['results']['data'];
            $title  = $info['title']  ?? ($info['name'] ?? '');
            $poster = $info['poster'] ?? '';

            if ($title !== '' && $poster !== '') {
                if ($stmt2 = $conn->prepare("
                    INSERT INTO tracked_anime (anime_id, title, poster, last_checked)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE title = VALUES(title), poster = VALUES(poster), last_checked = NOW()
                ")) {
                    $stmt2->bind_param('sss', $movieId, $title, $poster);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }

            return ['title' => $title, 'poster' => $poster];
        }
    }

    return ['title' => '', 'poster' => ''];
}

/**
 * Build one activity item HTML (for ANY user, not "You")
 */
function build_activity_html(array $row, mysqli $conn): string {
    $type          = $row['type'];
    $createdAt     = $row['created_at'];
    $metaJson      = $row['meta'] ?? '{}';
    $postSlug      = $row['post_slug'] ?? '';
    $postTitle     = $row['post_title'] ?? '';
    $refId         = (int)($row['ref_id'] ?? 0);

    $movieId       = $row['movie_id'] ?? '';
    $episodeId     = $row['episode_id'] ?? '';
    $parentUser    = $row['parent_username'] ?? '';
    $actorUsername = $row['actor_username'] ?? '';

    $meta = json_decode($metaJson, true);
    if (!is_array($meta)) $meta = [];

    if (!empty($meta['movie_id']))   $movieId   = $meta['movie_id'];
    if (!empty($meta['episode_id'])) $episodeId = $meta['episode_id'];

    $timeStr = time_ago_str($createdAt);

    $iconClass = 'fas fa-th-large';
    $timeIcon  = 'fas fa-comment mr-2';

    $safePostTitle   = htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8');
    $safePostSlug    = htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8');
    $safeParentUser  = htmlspecialchars($parentUser, ENT_QUOTES, 'UTF-8');
    $safeMovieId     = htmlspecialchars($movieId, ENT_QUOTES, 'UTF-8');
    $safeEpisodeId   = htmlspecialchars($episodeId, ENT_QUOTES, 'UTF-8');
    $safeActor       = htmlspecialchars($actorUsername, ENT_QUOTES, 'UTF-8');

    // ---------- post created ----------
    if ($type === 'post_created') {
        $timeIcon = 'fas fa-question-circle mr-2';
        $url = '/community/post/' . $safePostSlug;

        return '
<div class="item d-flex justify-content-between">
    <div class="item-connect">
        <div class="connect-inner"><i class="' . $iconClass . '"></i></div>
    </div>
    <div class="item-inner">
        <div class="sts d-flex justify-content-between align-items-center">
            <div class="time">
                <i class="' . $timeIcon . '"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            <div>
                <strong>' . $safeActor . '</strong> created Post
                <a href="' . $url . '" class="highlight-text"
                   title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>
        </div>
    </div>
</div>';
    }

    // ---------- comment OR reply on post ----------
    if ($type === 'comment_added' || $type === 'post_comment' || $type === 'post_reply') {
        $commentId = isset($meta['comment_id']) ? (int)$meta['comment_id'] : $refId;
        $url = '/community/post/' . $safePostSlug . '?c_id=' . $commentId;

        $isReply = ($type === 'post_reply');
        if ($isReply && $safeParentUser !== '') {
            // reply with parent username
            $subjectHtml = '
                <div>
                    <strong>' . $safeActor . '</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                    comment on Post <a
                            href="' . $url . '"
                            class="highlight-text"
                            title="' . $safePostTitle . '">' . $safePostTitle . '</a>
                </div>';
        } else {
            // normal comment
            $subjectHtml = '
                <div>
                    <strong>' . $safeActor . '</strong> commented on Post <a
                            href="' . $url . '"
                            class="highlight-text"
                            title="' . $safePostTitle . '">' . $safePostTitle . '</a>
                </div>';
        }

        return '
<div class="item d-flex justify-content-between">
    <div class="item-connect">
        <div class="connect-inner"><i class="' . $iconClass . '"></i></div>
    </div>
    <div class="item-inner">
        <div class="sts d-flex justify-content-between align-items-center">
            <div class="time">
                <i class="fas fa-comment mr-2"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            ' . $subjectHtml . '
        </div>
    </div>
</div>';
    }

    // ---------- episode comment / reply ----------
    if ($type === 'episode_comment' || $type === 'episode_reply') {
        $commentId = isset($meta['comment_id']) ? (int)$meta['comment_id'] : $refId;

        $animeInfo   = get_anime_info($movieId, $conn);
        $animeTitle  = $animeInfo['title']  ?? '';
        $animePoster = $animeInfo['poster'] ?? '';

        $safeAnimeTitle = htmlspecialchars($animeTitle, ENT_QUOTES, 'UTF-8');
        $safePoster     = htmlspecialchars($animePoster, ENT_QUOTES, 'UTF-8');

        $url = '/watch/' . $safeMovieId
             . '?ep=' . $safeEpisodeId
             . '&c_id=' . $commentId
             . '&c_type=episode';

        if ($type === 'episode_reply' && $safeParentUser !== '') {
            $actionText = '<strong>' . $safeActor . '</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                    comment on Anime <a
                            href="' . $url . '" class="highlight-text"
                            title="' . $safeAnimeTitle . '">' . $safeAnimeTitle . '</a>';
        } else {
            $actionText = '<strong>' . $safeActor . '</strong> commented on Anime <a
                            href="' . $url . '" class="highlight-text"
                            title="' . $safeAnimeTitle . '">' . $safeAnimeTitle . '</a>';
        }

        return '
<div class="item d-flex justify-content-between">
    <div class="item-poster">
        <img src="' . $safePoster . '"
             class="item-poster-img"
             alt="' . $safeAnimeTitle . '">
    </div>
    <div class="item-inner">
        <div class="sts d-flex justify-content-between align-items-center">
            <div class="time">
                <i class="fas fa-comment mr-2"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            <div>
                ' . $actionText . '
            </div>
        </div>
    </div>
</div>';
    }

    // ---------- unknown type -> skip ----------
    return '';
}

/* --------- fetch latest 10 activities for that user --------- */

$activities = [];

$sql = "
    SELECT
        ca.id,
        ca.user_id,
        ca.type,
        ca.ref_id,
        ca.meta,
        ca.created_at,

        p.slug  AS post_slug,
        p.title AS post_title,

        c.movie_id,
        c.episode_id,
        u2.username AS parent_username,
        u.username  AS actor_username
    FROM community_activity ca
    JOIN users u ON u.id = ca.user_id
    LEFT JOIN post_comments pc
        ON (ca.type IN ('comment_added','post_comment','post_reply') AND pc.id = ca.ref_id)
    LEFT JOIN posts p
        ON (
            (ca.type = 'post_created' AND p.id = ca.ref_id)
            OR
            (ca.type IN ('comment_added','post_comment','post_reply') AND p.id = pc.post_id)
        )
    LEFT JOIN comments c
        ON (ca.type IN ('episode_comment','episode_reply') AND c.id = ca.ref_id)
    LEFT JOIN users u2
        ON (
            (ca.type = 'episode_reply' AND u2.id = c.parent_user_id)
            OR
            (ca.type = 'post_reply'    AND u2.id = pc.parent_user_id)
        )
    WHERE ca.user_id = ?
    ORDER BY ca.id DESC
    LIMIT 10
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $activities[] = $row;
        }
    }
    $stmt->close();
}

/* --------- build wrapper HTML exactly like your example --------- */

$itemsHtml = '';

if (!empty($activities)) {
    foreach ($activities as $row) {
        $item = build_activity_html($row, $conn);
        if ($item !== '') {
            $itemsHtml .= "\n" . $item . "\n";
        }
    }
} else {
    $itemsHtml .= '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-between align-items-center">
            <div class="time text-muted">No recent activity.</div>
        </div>
    </div>
</div>';
}

$html = '<div class="wideview">
    <div class="wv wv-mine">
        <div class="wv-list">
            <div class="wv-title d-flex align-items-center">
                <div class="name mr-3">Latest Activities</div>
            </div>
            
                <div id="im-latest" class="tab-pane">
                    <div class="wv-ul">' .
                        $itemsHtml . '
                    </div>
                    
                </div>
            
        </div>
    </div>
</div>';

echo json_encode([
    'status' => true,
    'html'   => $html
]);
exit;

<?php
// /ajax/community/activity/list.php
header('Content-Type: application/json; charset=utf-8');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);

if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in.']);
    exit;
}

$conn->set_charset('utf8mb4');

/* ------------ helpers ------------ */

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
 * Get anime title + poster using:
 * 1) tracked_anime table
 * 2) /info?id=movieId (fallback)
 */
function get_anime_info(string $movieId, mysqli $conn): array {
    global $zpi;
    $title  = '';
    $poster = '';

    // 1) Try tracked_anime cache
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

    // If found and has poster, return
    if ($title !== '' && $poster !== '') {
        return ['title' => $title, 'poster' => $poster];
    }

    // 2) Fallback to /info?id=movieId
    $url = rtrim($zpi, '/') . '/info?id=' . urlencode($movieId);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] && isset($data['results']['data'])) {
            $info   = $data['results']['data'];
            $title  = $info['title']  ?? ($info['name'] ?? '');
            $poster = $info['poster'] ?? '';

            // Save to tracked_anime for next time (optional)
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

    // Fallback empty
    return ['title' => '', 'poster' => ''];
}

/**
 * Build one HTML item for the activity feed
 */
function render_activity_item(array $row): string {
    global $conn;

    $type        = $row['type'];
    $createdAt   = $row['created_at'];
    $metaJson    = $row['meta'] ?? '{}';
    $postSlug    = $row['post_slug'] ?? '';
    $postTitle   = $row['post_title'] ?? '';
    $refId       = (int)($row['ref_id'] ?? 0);

    $movieId     = $row['movie_id'] ?? '';
    $episodeId   = $row['episode_id'] ?? '';
    $parentUser  = $row['parent_username'] ?? '';

    $meta = json_decode($metaJson, true);
    if (!is_array($meta)) $meta = [];

    // Prefer meta movie/episode info if present
    if (!empty($meta['movie_id']))   $movieId   = $meta['movie_id'];
    if (!empty($meta['episode_id'])) $episodeId = $meta['episode_id'];

    $timeStr = time_ago_str($createdAt);
    $iconClass = 'fas fa-th-large';
    $timeIcon  = 'fas fa-comment mr-2';
    $subjectHtml = '';

    $safePostTitle  = htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8');
    $safePostSlug   = htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8');
    $safeParentUser = htmlspecialchars($parentUser, ENT_QUOTES, 'UTF-8');
    $safeMovieId    = htmlspecialchars($movieId, ENT_QUOTES, 'UTF-8');
    $safeEpisodeId  = htmlspecialchars($episodeId, ENT_QUOTES, 'UTF-8');

    // ---------- COMMUNITY: post created ----------
    if ($type === 'post_created') {
        $timeIcon = 'fas fa-question-circle mr-2';
        $url = '/community/post/' . $safePostSlug;

        $subjectHtml = '
            <div>
                <strong>You</strong> created Post
                <a href="' . $url . '" class="highlight-text"
                   title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';

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
            ' . $subjectHtml . '
        </div>
    </div>
</div>';
    }

    // ---------- COMMUNITY: comment or reply on post ----------
    if ($type === 'comment_added' || $type === 'post_comment' || $type === 'post_reply') {
        $timeIcon = 'fas fa-comment mr-2';
        $commentId = isset($meta['comment_id']) ? (int)$meta['comment_id'] : $refId;
        $url = '/community/post/' . $safePostSlug . '?c_id=' . $commentId;

        $isReply = ($type === 'post_reply');

        if ($isReply && $safeParentUser !== '') {
            // reply with parent username
            $subjectHtml = '
            <div>
                <strong>You</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                comment on Post <a
                        href="' . $url . '"
                        class="highlight-text"
                        title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';
        } else {
            // normal comment
            $subjectHtml = '
            <div>
                <strong>You</strong> commented on Post <a
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
                <i class="' . $timeIcon . '"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            ' . $subjectHtml . '
        </div>
    </div>
</div>';
    }

    // ---------- EPISODE COMMENT / REPLY ----------
    if ($type === 'episode_comment' || $type === 'episode_reply') {
        $timeIcon = 'fas fa-comment mr-2';
        $commentId = isset($meta['comment_id']) ? (int)$meta['comment_id'] : $refId;

        // Get anime title + poster from tracked_anime or /info
        $animeInfo = get_anime_info($movieId, $conn);
        $animeTitle = $animeInfo['title']  ?? '';
        $animePoster = $animeInfo['poster'] ?? '';

        $safeAnimeTitle = htmlspecialchars($animeTitle, ENT_QUOTES, 'UTF-8');
        $safePoster     = htmlspecialchars($animePoster, ENT_QUOTES, 'UTF-8');

        $url = '/watch/' . $safeMovieId
             . '?ep=' . $safeEpisodeId
             . '&c_id=' . $commentId
             . '&c_type=episode';

        if ($type === 'episode_reply' && $safeParentUser !== '') {
            $actionText = '<strong>You</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                    comment on Anime <a
                            href="' . $url . '" class="highlight-text"
                            title="' . $safeAnimeTitle . '">' . $safeAnimeTitle . '</a>';
        } else {
            $actionText = '<strong>You</strong> commented on Anime <a
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
                <i class="' . $timeIcon . '"></i>' . $timeStr . '
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

    // ---------- fallback ----------
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
            <div><strong>You</strong> did something.</div>
        </div>
    </div>
</div>';
}

function render_pagination(int $currentPage, int $totalPages): string {
    if ($totalPages <= 1) return '';

    $html = '<div class="pre-pagination">
    <nav aria-label="Page navigation">
        <ul class="pagination mb-0">';
    $baseUrl = '/ajax/community/activity/list?page=';

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active">
                <a href="javascript:;" class="page-link">' . $i . '</a>
            </li>';
        } else {
            $html .= '<li class="page-item">
                <a title="Page ' . $i . '" href="javascript:;" class="page-link"
                   onclick="paginationGetData(this)"
                   data-container="#activities-content"
                   data-url="' . $baseUrl . $i . '">' . $i . '</a>
            </li>';
        }
    }

    if ($currentPage < $totalPages) {
        $next = $currentPage + 1;
        $html .= '<li class="page-item">
            <a title="Next" href="javascript:;" class="page-link"
               onclick="paginationGetData(this)"
               data-container="#activities-content"
               data-url="' . $baseUrl . $next . '">&rsaquo;</a>
        </li>';
    }

    $html .= '</ul>
    </nav>
</div>';

    return $html;
}

/* ------------ pagination ------------ */
$perPage = 10;
$page = (isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0)
    ? (int)$_GET['page']
    : 1;
$offset = ($page - 1) * $perPage;

/* ------------ total count ------------ */
$stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM community_activity WHERE user_id = ?");
$stmtCount->bind_param('i', $userId);
$stmtCount->execute();
$resCount = $stmtCount->get_result();
$total = 0;
if ($rowCount = $resCount->fetch_assoc()) $total = (int)$rowCount['cnt'];
$stmtCount->close();

$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

/* ------------ fetch activities ------------ */
/*
   Assumptions:
   - posts(id, slug, title)
   - post_comments(id, post_id, parent_user_id)
   - comments(id, movie_id, episode_id, parent_user_id)
   - users(id, username)
*/
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
        u.username AS parent_username
    FROM community_activity ca
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
    LEFT JOIN users u
        ON (
            (ca.type = 'episode_reply' AND u.id = c.parent_user_id)
            OR
            (ca.type = 'post_reply'    AND u.id = pc.parent_user_id)
        )
    WHERE ca.user_id = ?
    ORDER BY ca.id DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $userId, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

/* ------------ build HTML ------------ */
$html = "<div class=\"wv-ul\">\n";
while ($row = $result->fetch_assoc()) {
    $html .= render_activity_item($row) . "\n";
}
$html .= "</div>\n";

$html .= '<div class="loading-relative" style="display: none;">
    <div class="loading">
        <div class="span1"></div>
        <div class="span2"></div>
        <div class="span3"></div>
    </div>
</div>' . "\n";

$html .= render_pagination($page, $totalPages);

echo json_encode([
    'status' => true,
    'html'   => $html
]);
exit;

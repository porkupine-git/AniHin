<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /ajax/community/coin/history.php
header('Content-Type: application/json; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn->set_charset('utf8mb4');

if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in.']);
    exit;
}

$userId = (int)$_SESSION['userID'];

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
 * Get anime title + poster using tracked_anime + $zpi/info?id=
 */
function get_anime_info(string $movieId, mysqli $conn): array {
    global $zpi;

    $title  = '';
    $poster = '';

    // 1) Try tracked_anime
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

    // 2) Fallback to API: $zpi/info?id=movieId
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
 * Build one coin history item HTML
 * $row is one row from coin_history with joined fields.
 */
function build_coin_item(array $row, mysqli $conn): string {
    $type        = $row['type'];        // e.g. like_comment_post, comment_post, reply_post, post_created, episode_reply
    $amount      = (int)$row['amount']; // zrc
    $createdAt   = $row['created_at'];
    $postSlug    = $row['post_slug'] ?? '';
    $postTitle   = $row['post_title'] ?? '';
    $commentId   = (int)($row['comment_id'] ?? 0);
    $movieId     = $row['movie_id'] ?? '';
    $episodeId   = $row['episode_id'] ?? '';
    $fromUser    = $row['from_username'] ?? '';   // user who liked / triggered
    $parentUser  = $row['parent_username'] ?? ''; // original comment owner (for replies)

    $timeStr = time_ago_str($createdAt);

    $safePostTitle  = htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8');
    $safePostSlug   = htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8');
    $safeFromUser   = htmlspecialchars($fromUser, ENT_QUOTES, 'UTF-8');
    $safeParentUser = htmlspecialchars($parentUser, ENT_QUOTES, 'UTF-8');
    $safeMovieId    = htmlspecialchars($movieId, ENT_QUOTES, 'UTF-8');
    $safeEpisodeId  = htmlspecialchars($episodeId, ENT_QUOTES, 'UTF-8');

    $amountHtml = '<div class="earn"><i class="fas fa-plus earn-icon mr-2"></i><strong>'
        . $amount . ' zrc</strong></div>';

    // default time icon
    $timeIcon = 'fas fa-comment mr-2';
    
    // ---------- LIKE RECEIVED ON COMMENT (POST) ----------
    if ($type === 'like_comment_post') {
        $timeIcon = 'fas fa-thumbs-up mr-2';
        $url = '/community/post/' . $safePostSlug . '?c_id=' . $commentId;

        $subject = '
            <div>
                <strong>' . $safeFromUser . '</strong> liked your Comment on Post
                <a href="' . $url . '"
                   class="highlight-text"
                   title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time"><i class="' . $timeIcon . '"></i>' . $timeStr . '</div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-connect">
        <div class="connect-inner"><i class="fas fa-th-large"></i></div>
    </div>
</div>';
    }

    // ---------- LIKE RECEIVED ON POST (not comment) ----------
    if ($type === 'like_post') {
        $timeIcon = 'fas fa-thumbs-up mr-2';
        $url = '/community/post/' . $safePostSlug;

        $subject = '
            <div>
                <strong>' . $safeFromUser . '</strong> liked your Post
                <a href="' . $url . '"
                   class="highlight-text"
                   title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time"><i class="' . $timeIcon . '"></i>' . $timeStr . '</div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-connect">
        <div class="connect-inner"><i class="fas fa-th-large"></i></div>
    </div>
</div>';
    }

    // ---------- COMMENT ON POST (You) ----------
    if ($type === 'comment_post') {
        $timeIcon = 'fas fa-comment mr-2';
        $url = '/community/post/' . $safePostSlug . '?c_id=' . $commentId;

        $subject = '
            <div><strong>You</strong> commented on Post <a
                        href="' . $url . '"
                        class="highlight-text"
                        title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time">
                <i class="' . $timeIcon . '"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-connect">
        <div class="connect-inner"><i class="fas fa-th-large"></i></div>
    </div>
</div>';
    }

    // ---------- REPLY ON POST COMMENT (You) ----------
    if ($type === 'reply_post') {
        $timeIcon = 'fas fa-comment mr-2';
        $url = '/community/post/' . $safePostSlug . '?c_id=' . $commentId;

        if ($safeParentUser !== '') {
            $subject = '
                <div><strong>You</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                    comment on Post <a
                            href="' . $url . '"
                            class="highlight-text"
                            title="' . $safePostTitle . '">' . $safePostTitle . '</a>
                </div>';
        } else {
            // fallback: just treat as comment if no parent username
            $subject = '
                <div><strong>You</strong> commented on Post <a
                            href="' . $url . '"
                            class="highlight-text"
                            title="' . $safePostTitle . '">' . $safePostTitle . '</a>
                </div>';
        }

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time">
                <i class="' . $timeIcon . '"></i>' . $timeStr . '
            </div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-connect">
        <div class="connect-inner"><i class="fas fa-th-large"></i></div>
    </div>
</div>';
    }

    // ---------- POST CREATED (You) ----------
    if ($type === 'post_created') {
        $timeIcon = 'fas fa-question-circle mr-2';
        $url = '/community/post/' . $safePostSlug;

        $subject = '
            <div>
                <strong>You</strong> created Post <a href="' . $url . '"
                                                             class="highlight-text"
                                                             title="' . $safePostTitle . '">' . $safePostTitle . '</a>
            </div>';

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time"><i class="' . $timeIcon . '"></i>' . $timeStr . '</div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-connect">
        <div class="connect-inner"><i class="fas fa-th-large"></i></div>
    </div>
</div>';
    }

    // ---------- EPISODE REPLY / COMMENT (Anime) ----------
    if ($type === 'episode_reply' || $type === 'episode_comment') {
        $timeIcon = 'fas fa-comment mr-2';

        $animeInfo   = get_anime_info($movieId, $conn);
        $animeTitle  = $animeInfo['title']  ?? '';
        $animePoster = $animeInfo['poster'] ?? '';

        $safeAnimeTitle = htmlspecialchars($animeTitle, ENT_QUOTES, 'UTF-8');
        $safePoster     = htmlspecialchars($animePoster, ENT_QUOTES, 'UTF-8');

        // coin history example used only ?c_id, but we may only know movieId here
        $url = '/watch/' . $safeMovieId . '?ep=' . $safeEpisodeId . '&c_id=' . $commentId;

        if ($type === 'episode_reply') {
            $subject = '
                <div><strong>You</strong> replied to <strong>' . $safeParentUser . '\'s</strong>
                    comment
                    on Anime <a
                            href="' . $url . '" class="highlight-text"
                            title="' . $safeAnimeTitle . '">' . $safeAnimeTitle . '</a>
                </div>';
        } else {
            $subject = '
                <div><strong>You</strong> commented on Anime
                    <a href="' . $url . '" class="highlight-text"
                       title="' . $safeAnimeTitle . '">' . $safeAnimeTitle . '</a>
                </div>';
        }

        return '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            ' . $amountHtml . '
            <div class="dot mx-3"></div>
            <div class="time"><i class="' . $timeIcon . '"></i>' . $timeStr . '</div>
        </div>
        <div class="subject">
            ' . $subject . '
        </div>
    </div>
    <div class="item-poster item-square">
        <img src="' . $safePoster . '"
             class="item-poster-img"
             alt="' . $safeAnimeTitle . '">
    </div>
</div>';
    }

    // ---------- fallback ----------
    return '';
}

function render_pagination(int $currentPage, int $totalPages): string {
    if ($totalPages <= 1) return '';

    $html = '<div class="pre-pagination">
    <nav aria-label="Page navigation">
        <ul class="pagination mb-0">';

    $baseUrl = '/ajax/community/coin/history?page=';

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === $currentPage) {
            $html .= '<li class="page-item active"><a href="javascript:;" class="page-link">' . $i . '</a></li>';
        } else {
            $html .= '<li class="page-item">
                <a title="Page ' . $i . '" href="javascript:;" class="page-link"
                   onclick="paginationGetData(this)"
                   data-container="#coins-content"
                   data-url="' . $baseUrl . $i . '">' . $i . '</a></li>';
        }
    }

    if ($currentPage < $totalPages) {
        $next = $currentPage + 1;
        $html .= '<li class="page-item">
            <a title="Next" href="javascript:;" class="page-link"
               onclick="paginationGetData(this)"
               data-container="#coins-content"
               data-url="' . $baseUrl . $next . '">&rsaquo;</a></li>';
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

$stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM coin_history WHERE user_id = ?");
$stmtCount->bind_param('i', $userId);
$stmtCount->execute();
$resCount = $stmtCount->get_result();
$total = 0;
if ($rowCount = $resCount->fetch_assoc()) {
    $total = (int)$rowCount['cnt'];
}
$stmtCount->close();

$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

/* ------------ fetch history ------------ */
/*
 * coin_history:
 *   id, user_id, amount, type, post_id, comment_id, movie_id, episode_id, from_user_id, created_at
 */

$sql = "
    SELECT
        ch.id,
        ch.user_id,
        ch.amount,
        ch.type,
        ch.post_id,
        ch.comment_id,
        ch.movie_id,
        ch.episode_id,
        ch.from_user_id,
        ch.created_at,

        p.slug  AS post_slug,
        p.title AS post_title,

        u_from.username AS from_username,
        u_parent.username AS parent_username
    FROM coin_history ch
    LEFT JOIN posts p
        ON p.id = ch.post_id
    LEFT JOIN users u_from
        ON u_from.id = ch.from_user_id
    LEFT JOIN comments c
        ON (ch.type IN ('episode_comment','episode_reply') AND c.id = ch.comment_id)
    LEFT JOIN post_comments pc
        ON (ch.type IN ('comment_post','reply_post','like_comment_post') AND pc.id = ch.comment_id)
    LEFT JOIN users u_parent
        ON (
            (ch.type IN ('episode_comment','episode_reply') AND u_parent.id = c.parent_user_id)
            OR
            (ch.type = 'reply_post' AND u_parent.id = pc.parent_user_id)
        )
    WHERE ch.user_id = ?
    ORDER BY ch.id DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $userId, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$itemsHtml = '';

while ($row = $result->fetch_assoc()) {
    $itemHtml = build_coin_item($row, $conn);
    if ($itemHtml !== '') {
        $itemsHtml .= $itemHtml . "\n";
    }
}

$stmt->close();

/* ------------ wrap HTML like your example ------------ */

if ($itemsHtml === '') {
    $itemsHtml = '
<div class="item d-flex justify-content-between">
    <div class="item-inner">
        <div class="sts d-flex justify-content-left align-items-center">
            <div class="time text-muted">No coin history found.</div>
        </div>
    </div>
</div>';
}

$html = '<div class="wv-ul">
    ' . $itemsHtml . '
</div>
<div class="loading-relative" style="display: none;">
    <div class="loading">
        <div class="span1"></div>
        <div class="span2"></div>
        <div class="span3"></div>
    </div>
</div>
' . render_pagination($page, $totalPages);

echo json_encode([
    'status' => true,
    'html'   => $html
]);
exit;

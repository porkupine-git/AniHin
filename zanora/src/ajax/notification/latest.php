<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /src/ajax/notification/latest.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once('src/component/anime/qtip.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['html' => '']);
    exit;
}

$userId = (int)$_SESSION['userID'];

// === Fetch latest 5 notifications for Anime (type=1) ===
$sqlAnime = "SELECT * FROM notifications 
             WHERE user_id = ? AND type = 1 
             ORDER BY created_at DESC 
             LIMIT 5";
$stmt1 = $conn->prepare($sqlAnime);
$stmt1->bind_param("i", $userId);
$stmt1->execute();
$res1 = $stmt1->get_result();

// === Fetch latest 5 notifications for Community (type=2) ===
$sqlCommunity = "SELECT * FROM notifications 
                 WHERE user_id = ? AND type = 2 
                 ORDER BY created_at DESC 
                 LIMIT 5";
$stmt2 = $conn->prepare($sqlCommunity);
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$res2 = $stmt2->get_result();

$html = '<div class="nnl-mark">
            <a class="btn btn-xs btn-noti-maar" data-position="dropdown">
                <i class="fas fa-check mr-2"></i> Mark all as read
            </a>
        </div>
        <div id="notify-1" class="tab-pane active show">';

// === Anime Tab ===
if ($res1->num_rows > 0) {
    while ($row = $res1->fetch_assoc()) {
        $readClass = $row['is_read'] ? '' : 'new';
        $link = !empty($row['link']) ? $row['link'] : '#';
        $poster = !empty($row['poster']) ? htmlspecialchars($row['poster']) : '/assets/img/default-poster.jpg';
        $title = htmlspecialchars($row['title']);
        $message = htmlspecialchars($row['message']);

        $html .= '<a class="nnl-item with-poster ' . $readClass . '" href="' . htmlspecialchars($link) . '">';
        $html .= '    <div class="film-poster"><img loading="lazy" decoding="async" src="' . $poster . '" class="film-poster-img" alt="' . $title . '"></div>';
        $html .= '    <strong class="manga-name">' . $title . '</strong> - <strong>' . $message . '</strong>';
        $html .= '    <div class="time">' . timeAgo($row['created_at']) . '</div>';
        $html .= '</a>';
    }
    $html .= '<a class="nnl-item nnl-more" href="/user/notification?type=1">
                <div class="text-center">View all</div>
             </a>';
} else {
    $html .= '<div style="display: block; padding: 25px 15px; text-align: center; font-size: 14px;">
                <div class="block mb-2"><i class="fas fa-box-open" style="font-size: 20px;"></i></div>
                No Notifications
              </div>';
}
$html .= '</div>';

// === Community Tab ===
$html .= '<div id="notify-2" class="tab-pane">';
if ($res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        $readClass = $row['is_read'] ? '' : 'new';
        $link = !empty($row['link']) ? $row['link'] : '#';

        // ---- Get user details (who replied) ----
        $fromUserId = isset($row['from_user_id']) ? (int)$row['from_user_id'] : 0;
        $userRes = $conn->query("SELECT username, image FROM users WHERE id={$fromUserId} LIMIT 1");
        $userRow = $userRes ? $userRes->fetch_assoc() : null;
        $username = $userRow && $userRow['username'] ? htmlspecialchars($userRow['username']) : 'Unknown';
        $avatar = $userRow && $userRow['image'] ? htmlspecialchars($userRow['image']) : '/public/images/no-avatar.jpeg';

        // ---- Get anime title (from API or DB) ----
        $animeTitle = '';
        if (!empty($row['anime_id'])) {
            // You can use your existing fetchAnimeData($animeId) here, assuming it returns ['title' => '...']
            $animeData = fetchAnimeData($row['anime_id']);
            if ($animeData && !empty($animeData['title'])) {
                $animeTitle = htmlspecialchars($animeData['title']);
            } else {
                $animeTitle = htmlspecialchars($row['anime_id']);
            }
        }

        $html .= '<a class="nnl-item with-avatar ' . $readClass . '" href="' . htmlspecialchars($link) . '">';
        $html .= '    <div class="user-avatar"><img src="' . $avatar . '" class="user-avatar-img" alt="' . $username . '"></div>';
        $html .= '    <strong class="manga-name">' . $username . '</strong> replied to your comment on Anime ';
        $html .= '    <strong>' . $animeTitle . '</strong>';
        $html .= '    <div class="time">' . timeAgo($row['created_at']) . '</div>';
        $html .= '</a>';
    }
    $html .= '<a class="nnl-item nnl-more" href="/user/notification?type=2">
            <div class="text-center">View all</div>
          </a>';
} else {
    $html .= '<div style="display: block; padding: 25px 15px; text-align: center; font-size: 14px;">
                <div class="block mb-2"><i class="fas fa-box-open" style="font-size: 20px;"></i></div>
                No Notifications
              </div>';
}
$html .= '</div>';

// ✅ Only return HTML now
echo json_encode(['html' => $html]);

// Helper function to show "2 days ago" style time
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return $diff . " seconds ago";
    $diff = floor($diff / 60);
    if ($diff < 60) return $diff . " minutes ago";
    $diff = floor($diff / 60);
    if ($diff < 24) return $diff . " hours ago";
    $diff = floor($diff / 24);
    return $diff . " days ago";
}
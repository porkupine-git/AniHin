<?php
if (!defined('ALLOW_ACCESS')) {
    http_response_code(404);
    header("Location: /404");
    exit();
}
// /src/ajax/notification/panel.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'html' => '']);
    exit;
}

$userId = (int)$_SESSION['userID'];

// --- 1️⃣ Get unread counts ---

// Total
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($countTotal);
$stmt->fetch();
$stmt->close();

// Anime
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0 AND type = 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($countAnime);
$stmt->fetch();
$stmt->close();

// Community
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0 AND type = 2");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($countCommunity);
$stmt->fetch();
$stmt->close();

// --- 2️⃣ Build badge spans only if count > 0 ---
function formatBadge($count) {
    return $count > 9 ? '9+' : $count;
}

$totalBadge = $countTotal > 0
    ? '<span class="number total-notify">'.formatBadge($countTotal).'</span>'
    : '';

$animeBadge = $countAnime > 0
    ? '<span class="number anime-notify-count">('.formatBadge($countAnime).')</span>'
    : '';

$communityBadge = $countCommunity > 0
    ? '<span class="number community-notify-count">('.formatBadge($countCommunity).')</span>'
    : '';

// --- 3️⃣ Build panel HTML ---
$panelHtml = '
<div class="hrn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    '.$totalBadge.'
    <i class="fas fa-bell"></i>
</div>
<div class="dropdown-menu dropdown-menu-noti new-noti-list" aria-labelledby="noti-list">
    <div class="nnl-head">
        <ul class="nav nav-tabs pre-tabs pre-tabs-min mb-0">
            <li class="nav-item">
                <a data-toggle="tab" data-type="1" href="#notify-1"
                   class="nav-link notify-type active show">
                    Anime '.$animeBadge.'
                </a>
            </li>
            <li class="nav-item">
                <a data-toggle="tab" data-type="2" href="#notify-2"
                   class="nav-link notify-type">
                    Community '.$communityBadge.'
                </a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
    <div class="loading-relative" id="noti-loading">
        <div class="loading">
            <div class="span1"></div>
            <div class="span2"></div>
            <div class="span3"></div>
        </div>
    </div>
    <div class="tab-content"></div>
</div>
';

// --- 4️⃣ Send response ---
echo json_encode([
    'status' => true,
    'html' => $panelHtml
]);
exit;
?>
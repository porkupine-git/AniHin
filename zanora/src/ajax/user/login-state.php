<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
header("Content-Type: application/json; charset=utf-8");

// Turn off error display for production
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 1️⃣ Get session token ---
$token = $_COOKIE['session_token'] ?? '';

if (!$token) {
    echo json_encode([
        "is_login" => false,
        "info" => (object)[],
        "settings" => (object)[],
        "html" => '
    <div class="header_right-user">
        <a data-toggle="modal" data-target="#modallogin" class="btn-user btn btn-sm btn-primary btn-login">Login</a>
    </div>
    '
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// --- 2️⃣ Validate session ---
$token_hash = hash('sha256', $token);
$stmt = $conn->prepare("
    SELECT 
        s.id AS session_id,
        s.expires_at,
        u.id AS user_id,
        u.username,
        u.email,
        u.roles,
        u.image,
        u.auto_play,
        u.auto_next,
        u.auto_skip_intro,
        u.show_comments_at_home,
        u.public_watch_list,
        u.anime_name,
        u.notify_ignore_folders,
        u.notify_ignore_lang,
        u.enable_dub,
        u.play_original_audio,
        u.live_status
    FROM user_sessions s
    INNER JOIN users u ON s.user_id = u.id
    WHERE s.token_hash = ? AND s.expires_at > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $cleanup = $conn->prepare("DELETE FROM user_sessions WHERE token_hash = ?");
    $cleanup->bind_param("s", $token_hash);
    $cleanup->execute();
    $cleanup->close();

    echo json_encode([
        "is_login" => false,
        "info" => (object)[],
        "settings" => (object)[],
        "html" => '
    <div class="header_right-user">
        <a data-toggle="modal" data-target="#modallogin" class="btn-user btn btn-sm btn-primary btn-login">Login</a>
    </div>
    '
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// --- 5️⃣ Regenerate PHP session ---
session_regenerate_id(true);
$_SESSION['userID'] = (int)$user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['roles'] = (int)$user['roles'];

// --- 6️⃣ Avatar + HTML ---
$avatar = !empty($user['image']) ? htmlspecialchars($user['image']) : "https://cdn.noitatnemucod.net/avatar/100x100/zoro_chibi/avatar2-01.png";
$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email'] ?? '');

$html = '
<div class="hr-notifications"></div>
<div id="user-slot">
    <div class="header_right-user logged">
        <div class="dropdown">
            <div class="btn-user-avatar" data-toggle="dropdown" aria-expanded="false" aria-controls="user_menu">
                <div class="profile-avatar">
                    <img src="'.$avatar.'" alt="'.$username.'">
                </div>
            </div>
            <div id="user_menu" class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-item dropdown-item-user">
                    <div class="user-detail">
                        <div class="name"><strong>'.$username.'</strong></div>
                        <div class="mail">'.$email.'</div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="grid-menu">
                    <a class="dropdown-item" href="/user/profile"><i class="fas fa-user mr-2"></i>Profile</a>
                    <a class="dropdown-item" href="/user/continue-watching"><i class="fas fa-history mr-2"></i>Continue Watching</a>
                    <a class="dropdown-item" href="/user/watch-list"><i class="fas fa-heart mr-2"></i>Watch List</a>
                    <a class="dropdown-item" href="/user/notification"><i class="fas fa-bell mr-2"></i>Notification</a>
                    <a class="dropdown-item" href="/user/connections"><i class="fas fa-link mr-2"></i>Connections</a>
                    <a class="dropdown-item" href="/user/settings"><i class="fas fa-cog mr-2"></i>Settings</a>
                    <div class="clearfix"></div>
                </div>
                <a class="dropdown-item text-right text-white" href="/user/logout">Logout<i class="fas fa-arrow-right ml-2 mr-1"></i></a>
            </div>
        </div>
    </div>
</div>
';

// --- 7️⃣  Build settings object ---
$folders = json_decode($user['notify_ignore_folders'], true);
if (!is_array($folders)) $folders = [];

$langs_raw = $user['notify_ignore_lang'];
$langs = json_decode($langs_raw, true);
if (!is_array($langs)) $langs = [];

$settings = [
    "auto_play" => (string)(int)$user['auto_play'],
    "auto_next" => (string)(int)$user['auto_next'],
    "auto_skip_intro" => (string)(int)$user['auto_skip_intro'],
    "live_status" => (string)(int)$user['live_status'],
    "show_comments_at_home" => (string)(int)$user['show_comments_at_home'],
    "enable_dub" => (string)(int)$user['enable_dub'],
    "anime_name" => $user['anime_name'] ?: "en",
    "play_original_audio" => (string)(int)$user['play_original_audio'],
    "public_watch_list" => (string)(int)$user['public_watch_list'],
    "notify_ignore_folders" => $folders,
    "notify_ignore_lang" => $langs
];

// --- 8️⃣ Response ---
$response = [
    "is_login" => true,
    "info" => [
        "id" => (int)$user['user_id'],
        "roles" => (int)$user['roles'],
        "username" => $user['username'],
        "avatar" => $avatar
    ],
    "html" => $html,
    "settings" => $settings
];

echo json_encode($response, JSON_UNESCAPED_SLASHES);
exit;
?>

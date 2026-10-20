<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

// --- 1️⃣ Delete token from DB ---
if (!empty($_COOKIE['session_token'])) {
    $token_hash = hash('sha256', $_COOKIE['session_token']);

    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE token_hash = ?");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $stmt->close();
}

// --- 2️⃣ Destroy all PHP session data ---
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// --- 3️⃣ Remove session cookie ---
setcookie('session_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// --- 4️⃣ Redirect user (you can change /home to /) ---
header('Location: /home');
exit;
?>

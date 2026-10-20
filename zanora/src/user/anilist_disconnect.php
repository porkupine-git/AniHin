<?php
// anilist_disconnect.php - Disconnect AniList account
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$userId = $_SESSION['userID'] ?? null;
if (!$userId) {
    header("Location: /home");
    exit();
}

$stmt = $conn->prepare("
    UPDATE users 
    SET anilist_id = NULL, anilist_username = NULL, anilist_avatar = NULL, 
        anilist_token = NULL, anilist_refresh_token = NULL, anilist_token_expires = NULL
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

header("Location: /user/connections");
exit();
?>

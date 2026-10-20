<?php
// discord_disconnect.php - Disconnect Discord account
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
    SET discord_id = NULL, discord_username = NULL, discord_avatar = NULL, 
        discord_token = NULL, discord_refresh_token = NULL, discord_token_expires = NULL
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

header("Location: /user/connections");
exit();
?>

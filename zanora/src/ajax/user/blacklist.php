<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');

$userId = (int)($_SESSION['userID'] ?? 0);
$roles  = (int)($_SESSION['roles'] ?? 0); // only admins/mods

if ($userId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in.']);
    exit;
}

if ($roles <= 0) {
    echo json_encode(['status' => false, 'msg' => 'You do not have permission to do this.']);
    exit;
}

$targetId = (int)($_POST['user_id'] ?? 0);

if ($targetId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid user.']);
    exit;
}

if ($targetId === $userId) {
    echo json_encode(['status' => false, 'msg' => 'You cannot blacklist yourself.']);
    exit;
}

// check if already blacklisted
$stmt = $conn->prepare("SELECT 1 FROM user_blacklist WHERE blocked_user_id = ? LIMIT 1");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$stmt->store_result();
$isBlacklisted = $stmt->num_rows > 0;
$stmt->close();

if ($isBlacklisted) {
    // UNBLOCK
    $stmt = $conn->prepare("DELETE FROM user_blacklist WHERE blocked_user_id = ?");
    $stmt->bind_param("i", $targetId);
    $ok1 = $stmt->execute();
    $stmt->close();

    // Remove is_blocked = 1 → 0 in users table
    $stmt2 = $conn->prepare("UPDATE users SET is_blocked = 0 WHERE id = ?");
    $stmt2->bind_param("i", $targetId);
    $ok2 = $stmt2->execute();
    $stmt2->close();

    echo json_encode([
        'status' => ($ok1 && $ok2),
        'msg'    => ($ok1 && $ok2) ? 'User unblacklisted.' : 'Failed to unblacklist user.'
    ]);

} else {
    // BLOCK
    $stmt = $conn->prepare("INSERT IGNORE INTO user_blacklist (user_id, blocked_user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $targetId);
    $ok1 = $stmt->execute();
    $stmt->close();

    // Set is_blocked = 1 in users table
    $stmt2 = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
    $stmt2->bind_param("i", $targetId);
    $ok2 = $stmt2->execute();
    $stmt2->close();

    echo json_encode([
        'status' => ($ok1 && $ok2),
        'msg'    => ($ok1 && $ok2) ? 'User blacklisted.' : 'Failed to blacklist user.'
    ]);
}

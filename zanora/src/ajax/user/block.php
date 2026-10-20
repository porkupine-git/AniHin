<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Login required']);
    exit;
}

$adminId = (int)$_SESSION['userID'];
$roles   = (int)($_SESSION['roles'] ?? 0);
$conn->set_charset('utf8mb4');

// Only admins/mods
if ($roles <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Permission denied']);
    exit;
}

$targetId = (int)($_POST['user_id'] ?? 0);

if ($targetId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid user']);
    exit;
}

if ($targetId === $adminId) {
    echo json_encode(['status' => false, 'msg' => 'You cannot block yourself']);
    exit;
}

/* Check if already blacklisted */
$stmt = $conn->prepare("SELECT 1 FROM user_blacklist WHERE blocked_user_id = ? LIMIT 1");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$stmt->store_result();
$isBlacklisted = $stmt->num_rows > 0;
$stmt->close();

/* ===========================
       UNBLOCK USER
   =========================== */
if ($isBlacklisted) {

    // remove from blacklist
    $stmt = $conn->prepare("DELETE FROM user_blacklist WHERE blocked_user_id = ?");
    $stmt->bind_param("i", $targetId);
    $ok1 = $stmt->execute();
    $stmt->close();

    // update is_blocked flag
    $stmt2 = $conn->prepare("UPDATE users SET is_blocked = 0 WHERE id = ?");
    $stmt2->bind_param("i", $targetId);
    $ok2 = $stmt2->execute();
    $stmt2->close();

    echo json_encode([
        'status' => ($ok1 && $ok2),
        'msg'    => ($ok1 && $ok2) ? 'User unblocked successfully.' : 'Failed to unblock user.'
    ]);
    exit;
}

/* ===========================
       BLOCK USER
   =========================== */

// add to blacklist table
$stmt = $conn->prepare("INSERT IGNORE INTO user_blacklist (user_id, blocked_user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $adminId, $targetId);
$ok1 = $stmt->execute();
$stmt->close();

// update is_blocked column
$stmt2 = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
$stmt2->bind_param("i", $targetId);
$ok2 = $stmt2->execute();
$stmt2->close();

echo json_encode([
    'status' => ($ok1 && $ok2),
    'msg'    => ($ok1 && $ok2) ? 'User blacklisted successfully.' : 'Failed to blacklist user.'
]);

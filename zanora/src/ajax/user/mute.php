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
$conn->set_charset('utf8mb4');

if (empty($_SESSION['userID'])) { echo json_encode(['status'=>false,'msg'=>'Login required']); exit; }
$adminId = (int)$_SESSION['userID'];

/* admin check */
$me = $conn->query("SELECT is_admin FROM users WHERE id={$adminId} LIMIT 1");
$me = $me ? $me->fetch_assoc() : null;
if (!$me || (int)$me['is_admin'] !== 1) { echo json_encode(['status'=>false,'msg'=>'Admin only']); exit; }

/* params */
$userId = (int)($_POST['user_id'] ?? 0);
$hours  = max(1, (int)($_POST['hours'] ?? 24));

/* make sure mute_until exists (one-time safe check) */
$conn->query("ALTER TABLE `users` 
    ADD COLUMN IF NOT EXISTS `mute_until` DATETIME NULL");

/* set mute_until */
$stmt = $conn->prepare("UPDATE users SET mute_until = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? HOUR) WHERE id=? LIMIT 1");
$stmt->bind_param('ii', $hours, $userId);

if ($stmt->execute()) {
    echo json_encode(['status'=>true, 'msg'=>"User muted for {$hours} hour(s)."]);
} else {
    echo json_encode(['status'=>false, 'msg'=>'Database error.']);
}
$stmt->close();

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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['userID'];
$notifId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$notifId) {
    echo json_encode(['status' => false, 'msg' => 'Invalid notification ID']);
    exit;
}

// Update notification as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notifId, $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => true, 'msg' => 'Notification marked as read']);
} else {
    echo json_encode(['status' => false, 'msg' => 'Notification not found or already read']);
}
$stmt->close();
exit;

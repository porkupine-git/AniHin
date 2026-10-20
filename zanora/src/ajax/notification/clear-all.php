<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /ajax/notification/clear-all.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['userID'];
$type   = $_GET['type'] ?? 'all';

// Only clear READ notifications
$where = "user_id = $userId AND is_read = 1";

if ($type !== 'all') {
    $typeSafe = $conn->real_escape_string($type);
    $where .= " AND type = '$typeSafe'";
}

$sql = "DELETE FROM notifications WHERE $where";
$res = $conn->query($sql);

echo json_encode([
    'status' => $res ? true : false,
    'msg'    => $res ? 'Read notifications cleared' : 'Error clearing notifications'
]);

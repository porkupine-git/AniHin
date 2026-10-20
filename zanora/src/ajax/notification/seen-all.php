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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['userID'];
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$where = "user_id = $userId";
if ($type !== 'all') {
    $where .= " AND type = '" . $conn->real_escape_string($type) . "'";
}

// ✅ Mark all as read
$sql = "UPDATE notifications SET is_read = 1 WHERE $where";
$res = $conn->query($sql);

echo json_encode([
    'status' => $res ? true : false,
    'msg' => $res ? 'All notifications marked as seen' : 'Error updating notifications'
]);
exit;

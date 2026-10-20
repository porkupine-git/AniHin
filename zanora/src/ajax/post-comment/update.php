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
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['userID'] ?? 0;
if (!$userId) {
    echo json_encode(['status' => false, 'msg' => 'Login required']);
    exit;
}

// check admin/mod
$stmt = $conn->prepare("SELECT roles FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

$role = (int)($u['roles'] ?? 0);
if (!in_array($role, [1,2], true)) {
    echo json_encode(['status' => false, 'msg' => 'No permission']);
    exit;
}

$commentId = (int)($_POST['id'] ?? 0);
$action    = $_POST['action'] ?? '';

if ($commentId <= 0 || !in_array($action, ['pin','unpin','delete'], true)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid parameters']);
    exit;
}

$msg = '';

if ($action === 'pin') {
    $stmt = $conn->prepare("UPDATE post_comments SET is_pinned = 1 WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $stmt->close();
    $msg = 'Comment pinned successfully';
} 
elseif ($action === 'unpin') {
    $stmt = $conn->prepare("UPDATE post_comments SET is_pinned = 0 WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $stmt->close();
    $msg = 'Comment unpinned successfully';
} 
elseif ($action === 'delete') {
    $stmt = $conn->prepare("UPDATE post_comments SET status = 'hidden' WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $stmt->close();
    $msg = 'Comment deleted successfully';
}

echo json_encode([
    'status' => true,
    'msg'    => $msg
]);
exit;

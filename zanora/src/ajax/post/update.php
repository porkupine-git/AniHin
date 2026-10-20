<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userId = $_SESSION['userID'] ?? 0;
$roles  = (int)($_SESSION['roles'] ?? 0); // 0 = normal, >0 = staff (mod/admin)

// Must be logged in
if ($userId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in.']);
    exit;
}

// Only roles > 0 can do these actions
if ($roles <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Permission denied']);
    exit;
}

$postId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = $_POST['action'] ?? '';

if ($postId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid post']);
    exit;
}

global $conn;

// Check post exists
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$res  = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    echo json_encode(['status' => false, 'msg' => 'Post not found']);
    exit;
}

// At this point: user has roles > 0, can manage ANY post
$msg = 'Action done';

switch ($action) {
    case 'delete':
        $stmt = $conn->prepare("UPDATE posts SET status = 'deleted' WHERE id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
        $msg = 'Post deleted';
        break;

    case 'pin':
        $stmt = $conn->prepare("UPDATE posts SET is_pinned = 1 WHERE id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
        $msg = 'Post pinned';
        break;

    case 'unpin':
        $stmt = $conn->prepare("UPDATE posts SET is_pinned = 0 WHERE id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
        $msg = 'Post unpinned';
        break;

    default:
        echo json_encode(['status' => false, 'msg' => 'Unknown action']);
        exit;
}

echo json_encode(['status' => true, 'msg' => $msg]);
exit;

<?php
/**
 * /src/ajax/comment/report.php
 * Handles reporting a comment as spam or spoil.
 * Returns JSON: { status: bool, msg: string }
 */
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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in to report comments.']);
    exit;
}
$userId = intval($_SESSION['userID']);
$commentId = intval($_POST['id'] ?? 0);
$type = intval($_POST['type'] ?? 0); // 1 = spam, 2 = spoil
if ($commentId <= 0 || ($type !== 1 && $type !== 2)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid request.']);
    exit;
}
// Prevent duplicate report from same user
$stmt = $conn->prepare("SELECT id FROM comment_reports WHERE comment_id = ? AND user_id = ? AND type = ? LIMIT 1");
$stmt->bind_param('iii', $commentId, $userId, $type);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    echo json_encode(['status' => false, 'msg' => 'You have already reported this comment.']);
    exit;
}
// Insert report
$ins = $conn->prepare("INSERT INTO comment_reports (comment_id, user_id, type, created_at) VALUES (?, ?, ?, NOW())");
$ins->bind_param('iii', $commentId, $userId, $type);
if (!$ins->execute()) {
    echo json_encode(['status' => false, 'msg' => 'Database error: ' . $ins->error]);
    exit;
}
// Optional: auto-flagging logic
if ($type === 1) {
    $check = $conn->query("SELECT COUNT(*) as c FROM comment_reports WHERE comment_id = {$commentId} AND type = 1");
    $count = $check->fetch_assoc()['c'] ?? 0;
    if ($count >= 100) {
        $conn->query("UPDATE comments SET status = 'deleted' WHERE id = {$commentId}");
    }
} elseif ($type === 2) {
    $check = $conn->query("SELECT COUNT(*) as c FROM comment_reports WHERE comment_id = {$commentId} AND type = 2");
    $count = $check->fetch_assoc()['c'] ?? 0;
    if ($count >= 50) {
        $conn->query("UPDATE comments SET is_spoil = 1 WHERE id = {$commentId}");
    }
}
echo json_encode(['status' => true, 'msg' => 'Report submitted.']);
?>

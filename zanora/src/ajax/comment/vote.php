<?php
/**
 * /src/ajax/comment/vote.php
 * Handles upvote/downvote for comments.
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
    echo json_encode(['status' => false, 'msg' => 'You must be logged in to vote.']);
    exit;
}
$userId = intval($_SESSION['userID']);
$commentId = intval($_POST['id'] ?? 0);
$type = intval($_POST['type'] ?? -1); // 1 = upvote, 0 = downvote
if ($commentId <= 0 || ($type !== 1 && $type !== 0)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid request.']);
    exit;
}
// Check if already voted
$stmt = $conn->prepare("SELECT type FROM comment_votes WHERE comment_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii', $commentId, $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    if (intval($row['type']) === $type) {
        // Remove vote
        $del = $conn->prepare("DELETE FROM comment_votes WHERE comment_id = ? AND user_id = ?");
        $del->bind_param('ii', $commentId, $userId);
        $del->execute();
        echo json_encode(['status' => true, 'msg' => 'Vote removed.']);
        exit;
    } else {
        // Update vote type
        $upd = $conn->prepare("UPDATE comment_votes SET type = ? WHERE comment_id = ? AND user_id = ?");
        $upd->bind_param('iii', $type, $commentId, $userId);
        $upd->execute();
        echo json_encode(['status' => true, 'msg' => 'Vote updated.']);
        exit;
    }
} else {
    // Insert new vote
    $ins = $conn->prepare("INSERT INTO comment_votes (comment_id, user_id, type) VALUES (?, ?, ?)");
    $ins->bind_param('iii', $commentId, $userId, $type);
    $ins->execute();
    echo json_encode(['status' => true, 'msg' => 'Vote added.']);
    exit;
}
?>

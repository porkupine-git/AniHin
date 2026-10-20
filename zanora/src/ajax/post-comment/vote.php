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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

// enable points helper – likes on comments give coins now
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php');
}

if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in to like/dislike.']);
    exit;
}
$userId = (int)$_SESSION['userID'];

$commentId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
// 1 = like, 0 = dislike
$type      = isset($_POST['type']) ? (int)$_POST['type'] : -1;

if ($commentId <= 0 || !in_array($type, [0, 1], true)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid parameters']);
    exit;
}

/* optional but cleaner */
if (method_exists($conn, 'begin_transaction')) {
    @$conn->begin_transaction();
}

try {
    // Fetch previous vote
    $stmt = $conn->prepare("SELECT type FROM post_comment_votes WHERE user_id = ? AND comment_id = ?");
    $stmt->bind_param("ii", $userId, $commentId);
    $stmt->execute();
    $res  = $stmt->get_result();
    $prev = $res->fetch_assoc();
    $stmt->close();

    // Get comment owner + post ID (needed for coins)
    $stmtC = $conn->prepare("SELECT user_id, post_id FROM post_comments WHERE id = ? LIMIT 1");
    $stmtC->bind_param("i", $commentId);
    $stmtC->execute();
    $resC = $stmtC->get_result();
    $rowC = $resC->fetch_assoc();
    $stmtC->close();

    if (!$rowC) {
        echo json_encode(['status' => false, 'msg' => 'Comment not found']);
        exit;
    }

    $ownerId = (int)$rowC['user_id'];
    $postId  = (int)$rowC['post_id'];
    $isSelf  = ($ownerId === $userId); // don't give coins for liking own comment

    if (!$prev) {

        // ----------------------
        // NEW VOTE
        // ----------------------
        $stmt = $conn->prepare("INSERT INTO post_comment_votes (user_id, comment_id, type) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $commentId, $type);
        $stmt->execute();
        $stmt->close();

        if ($type === 1) {
            // LIKE
            $conn->query("UPDATE post_comments SET like_count = like_count + 1 WHERE id = {$commentId}");

            // Give coins to comment owner (not self)
            if (!$isSelf && function_exists('addPoints')) {
                // userId who receives points = $ownerId
                // ref1 = post_id, ref2 = comment_id, fromUserId = liker
                addPoints($conn, $ownerId, 'like_comment_post', $postId, $commentId, $userId);
            }
        } else { // type === 0
            // DISLIKE
            $conn->query("UPDATE post_comments SET dislike_count = dislike_count + 1 WHERE id = {$commentId}");
        }

    } else {

        $prevType = (int)$prev['type']; // 1 or 0

        if ($prevType === $type) {

            // ----------------------
            // UNVOTE (same as clicking again)
            // ----------------------
            $stmt = $conn->prepare("DELETE FROM post_comment_votes WHERE user_id = ? AND comment_id = ?");
            $stmt->bind_param("ii", $userId, $commentId);
            $stmt->execute();
            $stmt->close();

            if ($type === 1) {
                // REMOVE LIKE
                $conn->query("UPDATE post_comments SET like_count = GREATEST(like_count - 1, 0) WHERE id = {$commentId}");

                // Remove coins from comment owner
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $ownerId, 'remove_like_comment_post', $postId, $commentId, $userId);
                }
            } else { // 0
                // REMOVE DISLIKE
                $conn->query("UPDATE post_comments SET dislike_count = GREATEST(dislike_count - 1, 0) WHERE id = {$commentId}");
            }

        } else {

            // ----------------------
            // CHANGE LIKE <-> DISLIKE
            // ----------------------
            $stmt = $conn->prepare("UPDATE post_comment_votes SET type = ? WHERE user_id = ? AND comment_id = ?");
            $stmt->bind_param("iii", $type, $userId, $commentId);
            $stmt->execute();
            $stmt->close();

            if ($prevType === 1 && $type === 0) {
                // LIKE → DISLIKE
                $conn->query("UPDATE post_comments SET 
                    like_count = GREATEST(like_count - 1, 0),
                    dislike_count = dislike_count + 1
                WHERE id = {$commentId}");

                // Remove coins when like is taken back
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $ownerId, 'remove_like_comment_post', $postId, $commentId, $userId);
                }

            } elseif ($prevType === 0 && $type === 1) {
                // DISLIKE → LIKE
                $conn->query("UPDATE post_comments SET 
                    dislike_count = GREATEST(dislike_count - 1, 0),
                    like_count = like_count + 1
                WHERE id = {$commentId}");

                // Give coins when turning into like
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $ownerId, 'like_comment_post', $postId, $commentId, $userId);
                }
            }
        }
    }

    if (method_exists($conn, 'commit')) {
        @$conn->commit();
    }

    echo json_encode(['status' => true]);

} catch (Throwable $e) {
    if (method_exists($conn, 'rollback')) {
        @$conn->rollback();
    }
    echo json_encode(['status' => false, 'msg' => 'Server error']);
}

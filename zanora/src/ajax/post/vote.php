<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * JSON helper
 */
function json_response($data = [], $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$userId = $_SESSION['userID'] ?? 0;
if (!$userId) {
    json_response(['status' => false, 'msg' => 'You must be logged in to vote']);
}

$userId = (int)$userId;

$postId = (int)($_POST['id'] ?? 0);
$type   = (int)($_POST['type'] ?? -1); // 1 = like, 0 = dislike

if ($postId <= 0 || !in_array($type, [0, 1], true)) {
    json_response(['status' => false, 'msg' => 'Invalid parameters']);
}

/* --------- get post owner (for coins) --------- */
$stmtPost = $conn->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
$stmtPost->bind_param("i", $postId);
$stmtPost->execute();
$resPost = $stmtPost->get_result();
$postRow = $resPost->fetch_assoc();
$stmtPost->close();

if (!$postRow) {
    json_response(['status' => false, 'msg' => 'Post not found']);
}

$postOwnerId = (int)$postRow['user_id'];
$isSelf      = ($postOwnerId === $userId); // don't give coins for liking own post

$conn->begin_transaction();

try {
    // Get previous vote
    $stmt = $conn->prepare("SELECT type FROM post_votes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $prev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$prev) {
        // ----------------------
        // NEW VOTE
        // ----------------------
        $stmt = $conn->prepare("INSERT INTO post_votes (user_id, post_id, type) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $postId, $type);
        $stmt->execute();
        $stmt->close();

        if ($type === 1) {
            // LIKE
            $conn->query("UPDATE posts SET like_count = like_count + 1 WHERE id = {$postId}");

            // Give coins to post owner (not self)
            if (!$isSelf && function_exists('addPoints')) {
                // userId who receives points = $postOwnerId
                // ref1 = post_id, ref2 = 0, fromUserId = liker
                addPoints($conn, $postOwnerId, 'like_post', $postId, 0, $userId);
            }
        } else {
            // DISLIKE
            $conn->query("UPDATE posts SET dislike_count = dislike_count + 1 WHERE id = {$postId}");
        }

    } else {
        $prevType = (int)$prev['type']; // 1 or 0

        if ($prevType === $type) {
            // ----------------------
            // UNVOTE (same clicked again)
            // ----------------------
            $stmt = $conn->prepare("DELETE FROM post_votes WHERE user_id = ? AND post_id = ?");
            $stmt->bind_param("ii", $userId, $postId);
            $stmt->execute();
            $stmt->close();

            if ($type === 1) {
                // UNLIKE
                $conn->query("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = {$postId}");

                // Remove coins from post owner
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $postOwnerId, 'like_post_remove', $postId, 0, $userId);
                }
            } else {
                // UN-DISLIKE
                $conn->query("UPDATE posts SET dislike_count = GREATEST(dislike_count - 1, 0) WHERE id = {$postId}");
            }

        } else {
            // ----------------------
            // CHANGE VOTE (like <-> dislike)
            // ----------------------
            $stmt = $conn->prepare("UPDATE post_votes SET type = ? WHERE user_id = ? AND post_id = ?");
            $stmt->bind_param("iii", $type, $userId, $postId);
            $stmt->execute();
            $stmt->close();

            if ($prevType === 1 && $type === 0) {
                // LIKE → DISLIKE
                $conn->query("UPDATE posts SET 
                    like_count = GREATEST(like_count - 1, 0),
                    dislike_count = dislike_count + 1
                WHERE id = {$postId}");

                // Remove coins when like is taken back
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $postOwnerId, 'like_post_remove', $postId, 0, $userId);
                }

            } elseif ($prevType === 0 && $type === 1) {
                // DISLIKE → LIKE
                $conn->query("UPDATE posts SET 
                    dislike_count = GREATEST(dislike_count - 1, 0),
                    like_count = like_count + 1
                WHERE id = {$postId}");

                // Give coins when turning into like
                if (!$isSelf && function_exists('addPoints')) {
                    addPoints($conn, $postOwnerId, 'like_post', $postId, 0, $userId);
                }
            }
        }
    }

    $conn->commit();
    json_response(['status' => true]);

} catch (Throwable $e) {
    $conn->rollback();
    json_response(['status' => false, 'msg' => 'Server error']);
}
?>

<?php
// /src/ajax/points.php

if (!function_exists('addPoints')) {

    function addPoints($conn, $userId, $action, $ref1 = null, $ref2 = null, $fromUserId = null, $commentIdExtra = null)
    {
        $userId = (int)$userId;
        if ($userId <= 0) return false;

        // -------------------
        // REWARDS CONFIG
        // -------------------
        $rewards = [
            'post_created'          => 50,
            'comment_post'          => 50,
            'reply_post'            => 25,
            'episode_comment'       => 50,
            'episode_reply'         => 25,
            'like_post'             => 50, // post like only
            'like_comment_post'     => 50, // like on comment (adjust amount as you want)
            // unlike handled separately → like_post_remove / remove_like_comment_post
        ];

        // Allow special "remove" actions even if they're not in $rewards
        if (!isset($rewards[$action]) &&
            !in_array($action, ['like_post_remove', 'remove_like_comment_post'], true)
        ) {
            return false;
        }

        $amount = $rewards[$action] ?? 0;

        // -------------------
        // Map refs to columns
        // -------------------
        $postId    = 0;
        $commentId = 0;
        $movieId   = '';
        $episodeId = '';
        $fromUser  = (int)($fromUserId ?? 0);
        $extraCid  = (int)($commentIdExtra ?? 0);

        switch ($action) {

            case 'post_created':
                $postId = (int)$ref1;
                break;

            case 'comment_post':
            case 'reply_post':
            case 'like_post':
            case 'like_comment_post':
                // For these we expect: ref1 = post_id, ref2 = comment_id (if any)
                $postId    = (int)$ref1;
                $commentId = (int)$ref2;
                break;

            case 'episode_comment':
            case 'episode_reply':
                $movieId   = (string)$ref1;
                $episodeId = (string)$ref2;
                if ($extraCid > 0) $commentId = $extraCid;
                break;
        }

        // -------------------
        // SPECIAL CASE: REMOVE LIKE ON POST
        // -------------------
        if ($action === 'like_post_remove') {

            // Get the correct post id from ref1
            $postId   = (int)$ref1;
            $fromUser = (int)($fromUserId ?? 0);

            $stmtSel = $conn->prepare("
                SELECT id, amount
                FROM coin_history
                WHERE user_id = ?
                  AND type = 'like_post'
                  AND post_id = ?
                  AND from_user_id = ?
                ORDER BY id DESC
                LIMIT 1
            ");
            if (!$stmtSel) return false;

            $stmtSel->bind_param("iii", $userId, $postId, $fromUser);
            $stmtSel->execute();
            $resSel = $stmtSel->get_result();
            $rowSel = $resSel->fetch_assoc();
            $stmtSel->close();

            if (!$rowSel) return false;

            $historyId  = (int)$rowSel['id'];
            $historyAmt = (int)$rowSel['amount'];

            // Delete that row
            $stmtDel = $conn->prepare("DELETE FROM coin_history WHERE id = ? LIMIT 1");
            if ($stmtDel) {
                $stmtDel->bind_param("i", $historyId);
                $stmtDel->execute();
                $stmtDel->close();
            }

            // Remove same amount from user points
            $delta = -1 * $historyAmt;

            $stmtPoints = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            if ($stmtPoints) {
                $stmtPoints->bind_param("ii", $delta, $userId);
                $stmtPoints->execute();
                $stmtPoints->close();
            }

            return true;
        }

        // -------------------
        // SPECIAL CASE: REMOVE LIKE ON COMMENT (post comments)
        // -------------------
        if ($action === 'remove_like_comment_post') {

            // Expect: ref1 = post_id, ref2 = comment_id
            $postId    = (int)$ref1;
            $commentId = (int)$ref2;
            $fromUser  = (int)($fromUserId ?? 0);

            $stmtSel = $conn->prepare("
                SELECT id, amount
                FROM coin_history
                WHERE user_id     = ?
                  AND type        = 'like_comment_post'
                  AND post_id     = ?
                  AND comment_id  = ?
                  AND from_user_id = ?
                ORDER BY id DESC
                LIMIT 1
            ");
            if (!$stmtSel) return false;

            $stmtSel->bind_param("iiii", $userId, $postId, $commentId, $fromUser);
            $stmtSel->execute();
            $resSel = $stmtSel->get_result();
            $rowSel = $resSel->fetch_assoc();
            $stmtSel->close();

            if (!$rowSel) return false;

            $historyId  = (int)$rowSel['id'];
            $historyAmt = (int)$rowSel['amount'];

            // Delete that row
            $stmtDel = $conn->prepare("DELETE FROM coin_history WHERE id = ? LIMIT 1");
            if ($stmtDel) {
                $stmtDel->bind_param("i", $historyId);
                $stmtDel->execute();
                $stmtDel->close();
            }

            // Remove same amount from user points
            $delta = -1 * $historyAmt;

            $stmtPoints = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            if ($stmtPoints) {
                $stmtPoints->bind_param("ii", $delta, $userId);
                $stmtPoints->execute();
                $stmtPoints->close();
            }

            return true;
        }

        // -------------------
        // NORMAL CASE: INSERT HISTORY + ADD POINTS
        // -------------------
        $stmt = $conn->prepare("
            INSERT INTO coin_history
                (user_id, amount, type, post_id, comment_id, movie_id, episode_id, from_user_id, created_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())
        ");
        if (!$stmt) return false;

        $stmt->bind_param(
            "iisiissi",
            $userId,
            $amount,
            $action,
            $postId,
            $commentId,
            $movieId,
            $episodeId,
            $fromUser
        );

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $stmt->close();

        // Update user points
        $stmt2 = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        if ($stmt2) {
            $stmt2->bind_param("ii", $amount, $userId);
            $stmt2->execute();
            $stmt2->close();
        }

        return true;
    }
}

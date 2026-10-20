<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /src/ajax/post-comment/add.php
header('Content-Type: application/json; charset=utf-8');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

// points helper (if exists)
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php');
}

// auth
if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in to comment.']);
    exit;
}
$userId = (int)$_SESSION['userID'];

/* -------------------------
   VERIFICATION + BLOCK CHECK
-------------------------- */
$isVerified = 0;
$isBlocked  = 0;

$stmtVer = $conn->prepare("
    SELECT 
        IFNULL(is_verified, 0) AS is_verified,
        IFNULL(is_blocked, 0)  AS is_blocked
    FROM users
    WHERE id = ?
    LIMIT 1
");
if ($stmtVer) {
    $stmtVer->bind_param('i', $userId);
    if ($stmtVer->execute()) {
        $resVer = $stmtVer->get_result();
        if ($rowVer = $resVer->fetch_assoc()) {
            $isVerified = (int)$rowVer['is_verified'];
            $isBlocked  = (int)$rowVer['is_blocked'];
        }
    }
    $stmtVer->close();
}

if ($isVerified === 0) {
    echo json_encode([
        'status' => false,
        'msg'    => 'You must verify your account before commenting.'
    ]);
    exit;
}

if ($isBlocked === 1) {
    echo json_encode([
        'status' => false,
        'msg'    => 'Your account is blocked from commenting.'
    ]);
    exit;
}

// inputs: Notice we accept post_id via POST OR via URL path like /ajax/post-comment/add/123
$postId = 0;
$uriParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
if (isset($uriParts[3]) && is_numeric($uriParts[3])) {
    $postId = (int)$uriParts[3];
}
if (isset($_POST['post_id'])) {
    $postId = (int)$_POST['post_id'];
}

$parentId  = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? (int)$_POST['parent_id'] : null;
$content   = trim($_POST['content'] ?? '');
$mentionId = (int)($_POST['mention_id'] ?? 0);

if ($postId <= 0 || $content === '') {
    echo json_encode(['status' => false, 'msg' => 'Invalid data']);
    exit;
}

// validation: length, links, bad words, emoji-only, max length 3000, min 3 non-space chars
if (mb_strlen($content) > 3000) {
    echo json_encode(['status' => false, 'msg' => 'Comment is too long.']);
    exit;
}
if (mb_strlen(preg_replace('/\s+/', '', $content)) < 3) {
    echo json_encode(['status' => false, 'msg' => 'Comment must be at least 3 characters.']);
    exit;
}
if (preg_match('/https?:\/\/|www\./i', $content)) {
    echo json_encode(['status' => false, 'msg' => 'Links are not allowed in comments.']);
    exit;
}
$emoji_regex = '/^([ \n\r\t\x{1F600}-\x{1F64F}\x{2600}-\x{26FF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E6}-\x{1F1FF}\x{2700}-\x{27BF}]+)$/u';
if (preg_match($emoji_regex, $content)) {
    echo json_encode(['status' => false, 'msg' => 'Comment is invalid.']);
    exit;
}

// Check post exists and is not locked
$stmt = $conn->prepare("SELECT id, slug, is_locked FROM posts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $postId);
$stmt->execute();
$res  = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    echo json_encode(['status' => false, 'msg' => 'Post not found']);
    exit;
}
if ((int)$post['is_locked'] === 1) {
    echo json_encode(['status' => false, 'msg' => 'Comments are locked for this post']);
    exit;
}

// Resolve root parent for 2-level thread
$rootParentId = 0;
$parentUserId = $mentionId ?: null;

if ($parentId && $parentId > 0) {
    $st = $conn->prepare("SELECT id, parent_id, user_id FROM post_comments WHERE id = ? AND status='visible' LIMIT 1");
    $st->bind_param("i", $parentId);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if ($r) {
        $rootParentId = ($r['parent_id'] && $r['parent_id'] > 0) ? (int)$r['parent_id'] : (int)$r['id'];
        // If mentionId not provided, fallback to clicked comment owner
        if (!$parentUserId) {
            $parentUserId = (int)$r['user_id'];
        }
    } else {
        // parent not found — treat as top-level
        $rootParentId = 0;
    }
}

// Insert comment
if ($rootParentId > 0) {
    $insertParent = $rootParentId;
} elseif ($parentId !== null) {
    // parentId provided but no root parent resolved (unlikely) -> use parentId
    $insertParent = $parentId;
} else {
    $insertParent = 0;
}
$insertParentUserId = $parentUserId ? (int)$parentUserId : null;

if ($insertParent === 0) {
    $parentUserNull = null;
    $parentIdZero   = null;
    $stmt2 = $conn->prepare("
        INSERT INTO post_comments (post_id, user_id, parent_user_id, parent_id, content, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'visible', UTC_TIMESTAMP())
    ");
    $stmt2->bind_param("iiiis", $postId, $userId, $parentUserNull, $parentIdZero, $content);
} else {
    $stmt2 = $conn->prepare("
        INSERT INTO post_comments (post_id, user_id, parent_user_id, parent_id, content, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'visible', UTC_TIMESTAMP())
    ");
    $stmt2->bind_param("iiiis", $postId, $userId, $insertParentUserId, $insertParent, $content);
}

if (!$stmt2->execute()) {
    echo json_encode(['status' => false, 'msg' => 'Database error: ' . $stmt2->error]);
    exit;
}
$newCommentId = $stmt2->insert_id;
$stmt2->close();

// Log community activity
$activityType = ($insertParent > 0) ? 'post_reply' : 'post_comment';

$meta = [
    'post_id'        => $postId,
    'comment_id'     => $newCommentId,
    'parent_id'      => $insertParent,
    'parent_user_id' => $insertParentUserId,
];

$metaJson = json_encode($meta);

$stmt3 = $conn->prepare("
    INSERT INTO community_activity (user_id, type, ref_id, meta)
    VALUES (?, ?, ?, ?)
");
$stmt3->bind_param("isis", $userId, $activityType, $newCommentId, $metaJson);
$stmt3->execute();
$stmt3->close();

// Notify parent user if reply

if ($insertParent > 0) {
    $stn = $conn->prepare("SELECT user_id FROM post_comments WHERE id = ? LIMIT 1");
    $stn->bind_param("i", $insertParent);
    $stn->execute();
    $rn = $stn->get_result()->fetch_assoc();
    $stn->close();
    if ($rn && (int)$rn['user_id'] !== $userId) {
        $toUser   = (int)$rn['user_id'];
        $notifMsg = "replied to your comment";
        // link to post with comment id and random token for notification
        $notifLink = '/community/post/' . urlencode($post['slug']) . '?c_id=' . $newCommentId . '&n_id=' . bin2hex(random_bytes(8));
        $notifType = 2;
        $ins = $conn->prepare("
            INSERT INTO notifications (user_id, from_user_id, post_id, message, link, type, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, UTC_TIMESTAMP())
        ");
        $ins->bind_param("iiissi", $toUser, $userId, $postId, $notifMsg, $notifLink, $notifType);
        $ins->execute();
        $ins->close();
    }
}

// Points awarding

if (function_exists('addPoints')) {
    if ($insertParent > 0) {
        addPoints($conn, $userId, "reply_post", $postId, $newCommentId, $insertParentUserId);
    } else {
        addPoints($conn, $userId, "comment_post", $postId, $newCommentId, 0);
    }
}

// Response:

if ($insertParent > 0) {
    $_GET['id'] = $insertParent;
    ob_start();
    include __DIR__ . '/replies.php';
    $repliesJson = ob_get_clean();
    $repliesData = json_decode($repliesJson, true);
    echo json_encode([
        'status'   => true,
        'parentId' => $insertParent,
        'html'     => $repliesData['html'] ?? ''
    ]);
} else {
    // refresh top-level widget (only comment list, not header)
    $_GET['post_id'] = $postId;
    $_GET['sort']    = 'newest';
    $_GET['partial'] = 'list';
    ob_start();
    include __DIR__ . '/widget.php';
    $widgetJson = ob_get_clean();
    $widgetData = json_decode($widgetJson, true);
    echo json_encode([
        'status'   => true,
        'parentId' => 0,
        'html'     => $widgetData['html'] ?? ''
    ]);
}
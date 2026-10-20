<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
/**
 * /src/ajax/comment/add.php
 * Adds a new comment or reply.
 * JSON: { status: bool, msg?: string, html?: string, parentId?: int, errors?: array }
 */
header('Content-Type: application/json; charset=utf-8');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php';
$conn->set_charset('utf8mb4');

/* ------------ helpers ------------ */
function human_interval_from_seconds(int $sec): string {
    if ($sec <= 0) return '0s';
    $d = intdiv($sec, 86400); $sec %= 86400;
    $h = intdiv($sec, 3600);  $sec %= 3600;
    $m = intdiv($sec, 60);    $s = $sec % 60;
    $out = [];
    if ($d) $out[] = $d . 'd';
    if ($h) $out[] = $h . 'h';
    if ($m) $out[] = $m . 'm';
    if ($s && !$d && !$h) $out[] = $s . 's';
    return implode(' ', $out);
}

function col_exists(mysqli $conn, string $table, string $col): bool {
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($col);
    $res = $conn->query("SHOW COLUMNS FROM `{$t}` LIKE '{$c}'");
    if ($res) { $ok = (bool)$res->num_rows; $res->free(); return $ok; }
    return false;
}

/* ------------ auth ------------ */
if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'You must be logged in to comment.']);
    exit;
}
$userId = (int)$_SESSION['userID'];

/* ------------ moderation state (uses only mute_until + is_blocked) ------------ */
$has_is_blocked = col_exists($conn, 'users', 'is_blocked');
$has_mute_until = col_exists($conn, 'users', 'mute_until');

$select = "SELECT IFNULL(is_verified,0) AS is_verified";
$select .= $has_is_blocked ? ", IFNULL(is_blocked,0) AS is_blocked" : ", 0 AS is_blocked";
$select .= $has_mute_until ? ", NULLIF(mute_until,'0000-00-00 00:00:00') AS mute_until" : ", NULL AS mute_until";
$select .= " FROM users WHERE id=? LIMIT 1";

$isVerified = 0; $isBlocked = 0; $muteUntil = null;

if ($stmt = $conn->prepare($select)) {
    $stmt->bind_param('i', $userId);
    if (!$stmt->execute()) {
        echo json_encode(['status' => false, 'msg' => 'Database error (user lookup).']);
        exit;
    }
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $isVerified = (int)($row['is_verified'] ?? 0);
        $isBlocked  = (int)($row['is_blocked'] ?? 0);
        $muteUntil  = $row['mute_until'] ?? null;
    }
    $stmt->close();
} else {
    echo json_encode(['status' => false, 'msg' => 'Database error (user lookup).']);
    exit;
}

/* ------------ enforcement ------------ */
if (!$isVerified) {
    echo json_encode(['status' => false, 'msg' => 'You must verify your account before commenting.']);
    exit;
}
if ($isBlocked === 1) {
    echo json_encode(['status' => false, 'msg' => 'Your account is blocked from commenting.']);
    exit;
}
if (!empty($muteUntil)) {
    $nowUtc = new DateTime('now', new DateTimeZone('UTC'));
    $mu = new DateTime($muteUntil, new DateTimeZone('UTC'));
    if ($mu > $nowUtc) {
        $secsLeft = (int)($mu->format('U') - $nowUtc->format('U'));
        $readable = human_interval_from_seconds($secsLeft);
        echo json_encode(['status' => false, 'msg' => "You are muted. Please try again in {$readable}."]);
        exit;
    }
}

/* ------------ payload ------------ */
$content   = trim($_POST['content'] ?? '');
$movieId   = trim($_POST['movie_id'] ?? '');
$episodeId = trim($_POST['episode_id'] ?? '');
$clickedId = (int)($_POST['parent_id'] ?? 0); // the comment you clicked "Reply" on
$isSpoil   = (int)($_POST['is_spoil'] ?? 0);
$mentionId = (int)($_POST['mention_id'] ?? 0); // The user you're replying to

/* ------------ validation ------------ */
$errors = [];
if ($movieId === '') $errors[] = 'Missing movie ID.';
if ($content === '') $errors[] = 'Comment cannot be empty.';
if (mb_strlen($content) > 3000) $errors[] = 'Comment is too long.';

// At least 3 non-whitespace characters
if (mb_strlen(preg_replace('/\s+/', '', $content)) < 3) {
    $errors[] = '- Comment length must be at least 3 characters long.';
}

// Reject only-emoji/whitespace comments
$emoji_regex = '/^([ \n\r\t\x{1F600}-\x{1F64F}\x{2600}-\x{26FF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E6}-\x{1F1FF}\x{2700}-\x{27BF}]+)$/u';
if (preg_match($emoji_regex, $content)) {
    $errors[] = 'Comment is invalid.';
}

if (preg_match('/https?:\/\/|www\./i', $content)) $errors[] = 'Links are not allowed in comments.';

if ($errors) {
    echo json_encode(['status' => false, 'errors' => $errors]);
    exit;
}

/* ------------ resolve root parent and parent user ------------ */
$rootParentId = 0;
$parentUserId = $mentionId ?: null;

if ($clickedId > 0) {
    $stmt = $conn->prepare("
        SELECT c.id, c.parent_id, c.user_id
        FROM comments c
        WHERE c.id = ? AND c.status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param('i', $clickedId);
    if ($stmt->execute()) {
        $stmt->bind_result($cid, $parent_id, $c_user_id);
        if ($stmt->fetch()) {
            $rootParentId = ($parent_id > 0) ? (int)$parent_id : (int)$cid;
            // if no mentionId passed, use the clicked comment owner as parent user
            if (!$parentUserId) {
                $parentUserId = (int)$c_user_id;
            }
        }
    }
    $stmt->close();
}

/* ------------ insert ------------ */
$insertParentId     = ($rootParentId > 0) ? $rootParentId : 0;
$insertParentUserId = $parentUserId ? (int)$parentUserId : null;

$stmt = $conn->prepare("
    INSERT INTO comments (movie_id, episode_id, parent_id, parent_user_id, user_id, content, is_spoil, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', UTC_TIMESTAMP())
");
$stmt->bind_param('ssiiisi', $movieId, $episodeId, $insertParentId, $insertParentUserId, $userId, $content, $isSpoil);

if (!$stmt->execute()) {
    echo json_encode(['status' => false, 'msg' => 'Database error: ' . $stmt->error]);
    exit;
}
$newCommentId = $stmt->insert_id;
$stmt->close();

// ------------ log activity (for activity feed) ------------
$typeActivity = ($insertParentId > 0) ? 'episode_reply' : 'episode_comment';

$metaJson = json_encode([
    'movie_id'       => $movieId,
    'episode_id'     => $episodeId,
    'comment_id'     => $newCommentId,
    'parent_id'      => $insertParentId,       // 0 if top-level
    'parent_user_id' => $insertParentUserId,   // null/0 if top-level
]);

$stmtAct = $conn->prepare("
    INSERT INTO community_activity (user_id, type, ref_id, meta)
    VALUES (?, ?, ?, ?)
");
$stmtAct->bind_param("isis", $userId, $typeActivity, $newCommentId, $metaJson);
$stmtAct->execute();
$stmtAct->close();

/* ------------ send reply notification ------------ */
if ($insertParentId > 0 && $insertParentUserId && $insertParentUserId !== $userId) {
    $notifMsg = "replied to your comment";
    $notifType = 2;
    $notifLink = "/watch/" . urlencode($movieId) .
                 "?ep=" . urlencode($episodeId) .
                 "&c_id=" . urlencode($newCommentId) .
                 "&n_id=" . bin2hex(random_bytes(8)) .
                 "&c_type=episode";
    $stmtNotif = $conn->prepare("
        INSERT INTO notifications (user_id, from_user_id, anime_id, message, link, type, episode_id, is_read, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, UTC_TIMESTAMP())
    ");
    $stmtNotif->bind_param(
        'iisssii',
        $insertParentUserId,
        $userId,         // Save the user ID of who replied
        $movieId,
        $notifMsg,
        $notifLink,
        $notifType,
        $episodeId
    );
    $stmtNotif->execute();
    $stmtNotif->close();
}

/* ------------ points ------------ */

if (function_exists('addPoints')) {
    if ($insertParentId > 0) {
        addPoints($conn, $userId, "episode_reply", $movieId, $episodeId, $insertParentUserId, $newCommentId);
    } else {
        // Top-level comment on episode
        // fromUserId = 0, commentId = new comment id
        addPoints($conn, $userId, "episode_comment", $movieId, $episodeId, 0, $newCommentId);
    }
}

/* ------------ response ------------ */

if ($insertParentId > 0) {
    $_GET['id'] = $insertParentId;
    ob_start();
    include __DIR__ . '/replies.php';
    $repliesJson = ob_get_clean();
    $repliesData = json_decode($repliesJson, true);
    echo json_encode([
        'status'   => true,
        'parentId' => $insertParentId,
        'html'     => $repliesData['html'] ?? ''
    ]);
} else {
    $_GET['movieId']   = $movieId;
    $_GET['episodeId'] = $episodeId;
    $_GET['sort']      = 'newest';
    $_GET['type']      = 'episode';
    $_GET['partial']   = 'list';
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

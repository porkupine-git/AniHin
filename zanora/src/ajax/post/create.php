<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// -------------------------------
// SLUGIFY FUNCTION
// -------------------------------
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// -------------------------------
// TURNSTILE CHECK
// -------------------------------
$cf_token = $_POST['cf-turnstile-response'] ?? '';
if (!verify_turnstile($cf_token)) {
    echo json_encode(['status' => false, 'msg' => 'Turnstile verification failed']);
    exit;
}

// -------------------------------
// INPUTS
// -------------------------------
$userId  = (int)($_SESSION['userID'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$tags_id = trim($_POST['tags_id'] ?? '');

// must be logged in
if ($userId <= 0) {
    echo json_encode([
        'status' => false,
        'msg'    => 'You must be logged in to create a post'
    ]);
    exit;
}

// default tag if none
$tags_id = ($tags_id === '' || $tags_id === null) ? 1 : (int)$tags_id;

// ROLE CHECK: only role > 0 can use tag_id = 5
$userRole = (int)($_SESSION['roles'] ?? 0);

if ($tags_id === 5 && $userRole <= 0) {
    echo json_encode([
        'status' => false,
        'msg'    => 'You are not allowed to use this tag'
    ]);
    exit;
}

// -------------------------------
// VALIDATION
// -------------------------------
if ($title === '' || $content === '') {
    echo json_encode([
        'status' => false,
        'msg'    => 'Title and content required'
    ]);
    exit;
}

global $conn;

// -------------------------------
// VERIFY + BLOCK CHECK (from users table)
// -------------------------------
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

// must be verified
if ($isVerified === 0) {
    echo json_encode([
        'status' => false,
        'msg'    => 'You must verify your account before creating posts.'
    ]);
    exit;
}

// must not be blocked
if ($isBlocked === 1) {
    echo json_encode([
        'status' => false,
        'msg'    => 'Your account is blocked from creating posts.'
    ]);
    exit;
}

// -------------------------------
// INSERT POST (without slug first)
// -------------------------------
$stmt = $conn->prepare("
    INSERT INTO posts (user_id, title, content, tags_id)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("issi", $userId, $title, $content, $tags_id);

$ok = $stmt->execute();
$postId = $ok ? $stmt->insert_id : 0;
$stmt->close();

if (!$ok) {
    echo json_encode([
        'status' => false,
        'msg'    => 'Failed to create post'
    ]);
    exit;
}

// -------------------------------
// CREATE SLUG & UPDATE POST
// -------------------------------
$slugBase = slugify($title);
$slug = $slugBase . '-' . $postId;

$stmtSlug = $conn->prepare("UPDATE posts SET slug = ? WHERE id = ?");
$stmtSlug->bind_param("si", $slug, $postId);
$stmtSlug->execute();
$stmtSlug->close();

// -------------------------------
// LOG ACTIVITY
// -------------------------------
$type = 'post_created';
$metaJson = json_encode(['title' => $title]);

$stmt2 = $conn->prepare("
    INSERT INTO community_activity (user_id, type, ref_id, meta)
    VALUES (?, ?, ?, ?)
");
$stmt2->bind_param("isis", $userId, $type, $postId, $metaJson);
$stmt2->execute();
$stmt2->close();

// -------------------------------
// AWARD COINS
// -------------------------------
if (function_exists('addPoints')) {
    // post create = 50 zrc
    addPoints($conn, $userId, 'post_created', $postId, null, null);
}

// -------------------------------
// RESPONSE
// -------------------------------
echo json_encode([
    'status'      => true,
    'msg'         => 'Post created successfully',
    'redirectUrl' => '/community/post/' . $slug
]);

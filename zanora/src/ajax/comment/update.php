<?php
/**
 * /src/ajax/comment/update.php
 * Handles comment actions: hide/delete/spoil/pin/unpin
 * Admins can do everything.
 * Users can only modify their own comments (hide/spoil/delete only).
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
    echo json_encode(['status' => false, 'msg' => 'You must be logged in.']);
    exit;
}

$userId    = intval($_SESSION['userID']);
$commentId = intval($_POST['id'] ?? 0);
$action    = trim($_POST['action'] ?? ''); 
// allowed actions
$valid = ['hide','delete','spoil','pin','unpin'];

if ($commentId <= 0 || !in_array($action, $valid)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid request.']);
    exit;
}

// ---- ROLE CHECK -------------------------------------------------
$roleQuery = $conn->prepare("SELECT roles FROM users WHERE id=? LIMIT 1");
$roleQuery->bind_param('i', $userId);
$roleQuery->execute();
$roleRes = $roleQuery->get_result()->fetch_assoc();

$userRole = (int)($roleRes['roles'] ?? 0);
$isAdmin = ($userRole === 1);
$isMod   = ($userRole === 2);

// ---- OWNER CHECK ------------------------------------------------
$ownerQuery = $conn->prepare("SELECT user_id FROM comments WHERE id=? LIMIT 1");
$ownerQuery->bind_param('i', $commentId);
$ownerQuery->execute();
$ownerRes = $ownerQuery->get_result();

if (!$ownerRes || !($ownerRow = $ownerRes->fetch_assoc())) {
    echo json_encode(['status' => false, 'msg' => 'Comment not found.']);
    exit;
}

$isOwner = (intval($ownerRow['user_id']) === $userId);

// ---- PERMISSIONS ------------------------------------------------
// pin/unpin -> ONLY admin or mod
if (in_array($action, ['pin','unpin']) && !$isAdmin && !$isMod) {
    echo json_encode(['status' => false, 'msg' => 'Only Admin/Mod can pin comments.']);
    exit;
}

if ($action === 'delete' && !$isAdmin) {
    echo json_encode(['status' => false, 'msg' => 'Only Admin can delete comments.']);
    exit;
}

// other actions -> owner OR admin OR mod
if (!$isOwner && !$isAdmin && !$isMod) {
    echo json_encode(['status' => false, 'msg' => 'Permission denied.']);
    exit;
}

// ---- ACTIONS ----------------------------------------------------

if ($action === 'hide') {
    $stmt = $conn->prepare("UPDATE comments SET status='hidden' WHERE id=?");
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    echo json_encode(['status' => true, 'msg' => 'Comment hidden.']);
    exit;
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id=?");
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    echo json_encode(['status' => true, 'msg' => 'Comment deleted.']);
    exit;
}

if ($action === 'spoil') {
    $stmt = $conn->prepare("UPDATE comments SET is_spoil=1 WHERE id=?");
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    echo json_encode(['status' => true, 'msg' => 'Comment marked as spoiler.']);
    exit;
}

// ---- PIN / UNPIN ------------------------------------------------

if ($action === 'pin') {
    $stmt = $conn->prepare("UPDATE comments SET is_pinned=1 WHERE id=?");
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    echo json_encode(['status' => true, 'msg' => 'Comment pinned.']);
    exit;
}


if ($action === 'unpin') {
    $stmt = $conn->prepare("UPDATE comments SET is_pinned=0 WHERE id=?");
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    echo json_encode(['status' => true, 'msg' => 'Comment unpinned.']);
    exit;
}
?>

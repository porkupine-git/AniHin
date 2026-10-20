<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /ajax/vote/submit.php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

// ---------- Auth ----------
$userId = $_SESSION['userID'] ?? null;
if (!$userId) {
    echo json_encode([
        'status' => false,
        'msg' => 'Login required',
        'triggerLoginModal' => true,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------- Input ----------
$movieId = isset($_POST['movieId']) ? trim($_POST['movieId']) : '';
$mark    = isset($_POST['mark']) ? (int)$_POST['mark'] : 0;

$allowedMarks = [1, 5, 10];
if ($movieId === '' || !in_array($mark, $allowedMarks, true)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------- Upsert vote ----------
$stmt = $conn->prepare("
    INSERT INTO votes (user_id, movie_id, mark)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE mark = VALUES(mark)
");
if (!$stmt) {
    echo json_encode(['status' => false, 'msg' => 'DB prepare failed'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
$stmt->bind_param("isi", $userId, $movieId, $mark);
if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(['status' => false, 'msg' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
$stmt->close();

ob_start();
$_SERVER['REQUEST_URI'] = "/ajax/vote/info/" . $movieId;
include $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/vote/info.php';
$infoOutput = ob_get_clean();

// Decode the JSON string returned by info.php
$decoded = json_decode($infoOutput, true); // array

$htmlFragment = isset($decoded['html']) ? $decoded['html'] : $infoOutput; // fallback in case not JSON

echo json_encode([
    'status' => true,
    'html' => $htmlFragment, // pure HTML only!
    'msg' => 'Thanks for the vote.'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
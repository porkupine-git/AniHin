<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
if (!isset($_SESSION['userID']) || !isset($_POST['movieId'])) {
    echo json_encode([
        'status' => false,
        'msg' => 'Session or movie ID missing'
    ]);
    exit;
}
$userId = $_SESSION['userID'];
$animeId = $_POST['movieId'];
$stmt = $conn->prepare("DELETE FROM watch_history WHERE user_id = ? AND anime_id = ?");
$stmt->bind_param("is", $userId, $animeId);
if ($stmt->execute()) {
    echo json_encode([
        'status' => true,
        'msg' => 'Removed from Continue Watching Successfully'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'msg' => 'Error deleting or already removed'
    ]);
}
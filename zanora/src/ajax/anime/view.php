<?php
// /src/ajax/anime/view.php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');

// 1. Validate input
$animeId = trim($_POST['anime_id'] ?? '');
if ($animeId === '') {
    echo json_encode(['status' => false, 'msg' => 'Missing anime ID']);
    exit;
}

// 2. Get current year/month
$year = (int)date('Y');
$month = (int)date('n');

// 3. Upsert view count
try {
    $stmt = $conn->prepare("
        INSERT INTO anime_monthly_views (anime_id, year, month, view_count)
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE view_count = view_count + 1
    ");
    $stmt->bind_param('sii', $animeId, $year, $month);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['status' => true, 'msg' => 'View counted']);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'msg' => 'Database error: '.$e->getMessage()]);
}

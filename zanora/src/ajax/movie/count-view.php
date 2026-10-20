<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /ajax/movie/count-view/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header("Content-Type: application/json; charset=utf-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Only allow POST to avoid accidental crawlers incrementing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

/**
 * Expect path: /ajax/movie/count-view/{slug}
 */
function getSlugFromRequest(): ?string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));
    // last segment after count-view is the slug
    $slug = end($parts);
    $slug = urldecode($slug);
    // allow letters, digits, dot, underscore, hyphen
    return ($slug && preg_match('/^[A-Za-z0-9._-]+$/', $slug)) ? $slug : null;
}

$animeSlug = getSlugFromRequest();
if (!$animeSlug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid anime id/slug']);
    exit;
}

// Current year/month in server timezone
$year  = (int)date('Y');
$month = (int)date('n'); // 1..12

// We want an UPSERT that increments view_count
// Requires a UNIQUE KEY on (anime_id, year, month) — see SQL below
$sql = "
    INSERT INTO anime_monthly_views (anime_id, year, month, view_count)
    VALUES (?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE view_count = view_count + 1
";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: '.$conn->error);
    $stmt->bind_param('sii', $animeSlug, $year, $month);
    if (!$stmt->execute()) throw new Exception('Execute failed: '.$stmt->error);
    $stmt->close();

    // Optionally return the updated count
    $stmt2 = $conn->prepare("
        SELECT view_count
        FROM anime_monthly_views
        WHERE anime_id = ? AND year = ? AND month = ?
        LIMIT 1
    ");
    if ($stmt2) {
        $stmt2->bind_param('sii', $animeSlug, $year, $month);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt2->close();

		echo json_encode([
		    'status' => true
		], JSON_UNESCAPED_SLASHES);
		exit;
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    error_log('count-view error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

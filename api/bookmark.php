<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'not_logged_in',
        'message' => 'Please sign in to manage your Watchlist.'
    ]);
    exit;
}

$userId = (int)($user['id'] ?? 0);
$pdo = function_exists('getHindiPdo') ? getHindiPdo() : null;

if (!$pdo) {
    echo json_encode([
        'success' => false,
        'error' => 'db_error',
        'message' => 'Database connection unavailable.'
    ]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'toggle';

// 1. Check if an anime is bookmarked
if ($action === 'check') {
    $animeId = (int)($_GET['anime_id'] ?? 0);
    $slug = trim($_GET['slug'] ?? '');

    if ($animeId > 0) {
        $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND anime_id = ? LIMIT 1");
        $stmt->execute([$userId, $animeId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND anime_slug = ? LIMIT 1");
        $stmt->execute([$userId, $slug]);
    }
    $isBookmarked = (bool)$stmt->fetch();
    echo json_encode(['success' => true, 'bookmarked' => $isBookmarked]);
    exit;
}

// 2. Toggle Bookmark (Add / Remove)
if ($action === 'toggle') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $animeId = (int)($input['anime_id'] ?? 0);
    $animeSlug = trim($input['anime_slug'] ?? '');
    $animeTitle = trim($input['anime_title'] ?? '');
    $animePoster = trim($input['anime_poster'] ?? '');

    if (empty($animeSlug) && $animeId <= 0) {
        echo json_encode(['success' => false, 'error' => 'invalid_input', 'message' => 'Missing anime details.']);
        exit;
    }

    // Check if exists
    $checkStmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND (anime_id = ? OR (anime_slug = ? AND anime_slug != '')) LIMIT 1");
    $checkStmt->execute([$userId, $animeId, $animeSlug]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Remove from bookmarks
        $del = $pdo->prepare("DELETE FROM bookmarks WHERE id = ?");
        $del->execute([$existing['id']]);
        echo json_encode([
            'success' => true,
            'bookmarked' => false,
            'message' => 'Removed from Watchlist'
        ]);
        exit;
    } else {
        // Insert into bookmarks
        $ins = $pdo->prepare("INSERT INTO bookmarks (user_id, anime_id, anime_slug, anime_title, anime_poster) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$userId, $animeId, $animeSlug, $animeTitle, $animePoster]);
        echo json_encode([
            'success' => true,
            'bookmarked' => true,
            'message' => 'Added to your Watchlist!'
        ]);
        exit;
    }
}

// 3. List all bookmarks for user
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT anime_id, anime_slug, anime_title, anime_poster, created_at FROM bookmarks WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$userId]);
    $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'bookmarks' => $bookmarks]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'unknown_action']);

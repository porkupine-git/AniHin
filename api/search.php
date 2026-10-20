<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/anixo_api.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Search across live db.anixo.buzz catalog and cached database
$results = searchAnixoCatalog($query, 8);

// If API returned fewer than 3, also check unified catalog
if (count($results) < 3) {
    $localResults = getAllAnime(['q' => $query]);
    $seenIds = array_flip(array_column($results, 'id'));
    foreach ($localResults as $lr) {
        if (!isset($seenIds[$lr['id']])) {
            $results[] = $lr;
            $seenIds[$lr['id']] = true;
            if (count($results) >= 8) break;
        }
    }
}

$formatted = [];
foreach (array_slice($results, 0, 8) as $item) {
    $formatted[] = [
        'id' => $item['id'],
        'title' => $item['title'],
        'hindi_title' => $item['hindi_title'] ?? '',
        'slug' => $item['slug'] ?? ('anime-' . $item['id']),
        'poster' => $item['poster'],
        'type' => $item['show_type'] ?? 'TV',
        'is_dub' => !empty($item['is_hindi_dubbed']),
        'score' => $item['score'] ?? 8.0,
        'genres' => $item['genres'] ?? ''
    ];
}

echo json_encode($formatted);

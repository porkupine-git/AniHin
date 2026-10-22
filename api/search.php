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

// Search across authoritative Hindi database
$results = getAllAnime(['q' => $query]);

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
        'score' => $item['score'] ?? 8.5,
        'genres' => is_array($item['genres']) ? implode(', ', array_slice($item['genres'], 0, 2)) : $item['genres']
    ];
}

echo json_encode($formatted);

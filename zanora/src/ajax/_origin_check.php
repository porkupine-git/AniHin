<?php
// _origin_check.php

// 1. Manual list of external domains you want to allow
$manual_allowed_hosts = [
    'anome.free.nf',
    'animex.free.nf',
    'anihaven.free.nf',
    'shinora.fun',
];

// 2. Automatically detect the current host this script is running on
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Merge both lists
$allowed_hosts = array_merge([strtolower($current_host)], $manual_allowed_hosts);

// Extract host safely
function extract_host($url) {
    if (!$url) return null;
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }
    $parts = parse_url($url);
    return strtolower($parts['host'] ?? '');
}

$origin  = $_SERVER['HTTP_ORIGIN']  ?? null;
$referer = $_SERVER['HTTP_REFERER'] ?? null;

$origin_host  = extract_host($origin);
$referer_host = extract_host($referer);

$ok = false;

// Check Origin
if ($origin_host && in_array($origin_host, $allowed_hosts, true)) {
    $ok = true;
}
// Fallback to Referer
elseif ($referer_host && in_array($referer_host, $allowed_hosts, true)) {
    $ok = true;
}
// Allow if both are empty (Direct access/Server-side calls)
elseif (empty($origin_host) && empty($referer_host)) {
    $ok = true;
}

if (!$ok) {
    header('Content-Type: application/json');
    header('HTTP/1.1 403 Forbidden');
    echo json_encode([
        'status' => false,
        'msg'    => 'Invalid request origin',
        'debug'  => [
            'detected_current' => $current_host,
            'origin'  => $origin,
            'referer' => $referer
        ]
    ]);
    exit;
}

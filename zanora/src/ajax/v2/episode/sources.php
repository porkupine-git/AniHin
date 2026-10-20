<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header('Content-Type: application/json; charset=utf-8');

$episodeId = $_GET['id'] ?? null;

if (!$episodeId) {

    echo json_encode([
        "success" => false,
        "msg" => "Missing id"
    ]);

    exit;
}

if (
    !preg_match(
        '/^([0-9]+)-([0-9]+)-([a-zA-Z0-9]+)-(sub|dub)$/',
        $episodeId
    )
) {

    echo json_encode([
        "success" => false,
        "msg" => "Invalid source id"
    ]);

    exit;
}

$encodeUrl = rtrim($player, '/') . '/embed-2/v3/e-1/encode?id=' . urlencode($episodeId);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $encodeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode([
        "success" => false,
        "msg" => "Failed to connect to player server"
    ]);
    exit;
}

$data = json_decode($response, true);
if (empty($data['success']) || empty($data['playerUrl'])) {
    echo json_encode([
        "success" => false,
        "msg" => $data['error'] ?? "Failed to encode source ID"
    ]);
    exit;
}

$playerUrl = $data['playerUrl'];

echo json_encode([
    "success" => true,
    "type" => "iframe",
    "server" => "1",
    "link" => $playerUrl
], JSON_UNESCAPED_UNICODE);

exit;
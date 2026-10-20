<?php
header("Content-Type: application/json");

function http_get($url, $headers = [], $timeout = 15) {
    $ch = curl_init();

    $formattedHeaders = [];
    foreach ($headers as $k => $v) {
        $formattedHeaders[] = "$k: $v";
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => $formattedHeaders,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception($err);
    }

    curl_close($ch);
    return $response;
}

function extract_streaming_info($iframeUrl) {
    if (!$iframeUrl || !filter_var($iframeUrl, FILTER_VALIDATE_URL)) {
        return [
            "success" => false,
            "message" => "Invalid iframe URL"
        ];
    }

    $headers = [
        "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:146.0) Gecko/20100101 Firefox/146.0",
        "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Referer" => "https://hianime.to/"
    ];

    // Fetch iframe HTML
    $iframeHtml = http_get($iframeUrl, $headers);

    // Extract fileId
    if (!preg_match('/data-id="([^"]+)"/', $iframeHtml, $m)) {
        return [
            "success" => false,
            "message" => "fileId not found in iframe"
        ];
    }
    $fileId = $m[1];

    // Extract nonce
    $nonce = null;

    if (preg_match('/\b[a-zA-Z0-9]{48}\b/', $iframeHtml, $m)) {
        $nonce = $m[0];
    } else if (preg_match(
        '/\b([a-zA-Z0-9]{16})\b.*?\b([a-zA-Z0-9]{16})\b.*?\b([a-zA-Z0-9]{16})\b/',
        $iframeHtml,
        $m
    )) {
        $nonce = $m[1] . $m[2] . $m[3];
    }

    if (!$nonce) {
        return [
            "success" => false,
            "message" => "nonce not found in iframe"
        ];
    }

    // Build base domain
    $parsed = parse_url($iframeUrl);
    $defaultDomain = $parsed['scheme'] . "://" . $parsed['host'] . "/";

    $getSourcesUrl = $defaultDomain .
        "embed-2/v3/e-1/getSources?id=" .
        urlencode($fileId) .
        "&_k=" . urlencode($nonce);

    // Fetch sources
    $rawSourcesJson = http_get($getSourcesUrl, $headers);

    $sourcesJson = json_decode($rawSourcesJson, true);

    if (!$sourcesJson || empty($sourcesJson['sources'][0]['file'])) {
        return [
            "success" => false,
            "message" => "No video source found"
        ];
    }

    return [
        "success" => true,
        "sources" => $sourcesJson["sources"],
        "tracks" => $sourcesJson["tracks"] ?? [],
        "intro" => $sourcesJson["intro"] ?? null,
        "outro" => $sourcesJson["outro"] ?? null,
        "headers" => [
            "Referer" => $defaultDomain,
            "User-Agent" =>
                "Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 Chrome Mobile"
        ]
    ];
}

try {
    $iframeUrl = $_POST["url"] ?? $_GET["url"] ?? null;
    $result = extract_streaming_info($iframeUrl);
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
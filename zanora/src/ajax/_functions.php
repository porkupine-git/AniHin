<?php
function verify_turnstile($token) {
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => CF_TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => getClientIP()
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) return false;

    $resp = json_decode($result, true);
    return $resp['success'] ?? false;
}

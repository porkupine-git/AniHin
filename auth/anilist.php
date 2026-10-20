<?php
require_once __DIR__ . '/../config/config.php';

// If credentials are placeholder, provide a seamless mock login option for local testing
if (ANILIST_CLIENT_ID === 'YOUR_ANILIST_CLIENT_ID') {
    // Local development mock login
    $_SESSION['user'] = [
        'id' => 202,
        'username' => 'AniListUser',
        'email' => null,
        'avatar' => 'https://s4.anilist.co/file/anilistcdn/user/avatar/medium/default.png',
        'provider' => 'anilist'
    ];
    header('Location: /index.php?login_success=1');
    exit;
}

// Production AniList OAuth URL
$params = [
    'client_id' => ANILIST_CLIENT_ID,
    'redirect_uri' => ANILIST_REDIRECT_URI,
    'response_type' => 'code'
];

$authUrl = 'https://anilist.co/api/v2/oauth/authorize?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;

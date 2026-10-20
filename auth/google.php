<?php
require_once __DIR__ . '/../config/config.php';

// If credentials are placeholder, provide a seamless mock login option for local testing
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com') {
    // Local development mock login
    $_SESSION['user'] = [
        'id' => 101,
        'username' => 'HindiOtaku',
        'email' => 'user@gmail.com',
        'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=HindiOtaku',
        'provider' => 'google'
    ];
    header('Location: /index.php?login_success=1');
    exit;
}

// Production Google OAuth URL
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;

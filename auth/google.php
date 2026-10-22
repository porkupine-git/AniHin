<?php
require_once __DIR__ . '/../config/config.php';

// Save referring page so user returns smoothly after login
if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'anixohindi.cc') !== false) {
    $_SESSION['auth_redirect_after'] = $_SERVER['HTTP_REFERER'];
}

// Production Google OAuth URL
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
    'state' => 'google'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;

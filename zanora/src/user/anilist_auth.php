<?php
// anilist_auth.php - Initiate AniList OAuth redirection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$clientId = getenv('ANILIST_CLIENT_ID') ?: 'placeholder_client_id';
$redirectUri = getenv('ANILIST_REDIRECT_URI') ?: (WEBSITE_URL . '/auth/anilist/callback');

// Generate secure state to prevent CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['anilist_oauth_state'] = $state;

$authUrl = "https://anilist.co/api/v2/oauth/authorize?" . http_build_query([
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'state'         => $state
]);

header("Location: " . $authUrl);
exit();
?>

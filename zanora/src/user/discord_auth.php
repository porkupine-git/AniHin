<?php
// discord_auth.php - Initiate Discord OAuth redirection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$clientId = getenv('DISCORD_CLIENT_ID') ?: 'placeholder_client_id';
$redirectUri = getenv('DISCORD_REDIRECT_URI') ?: 'https://zanora.lol/auth/discord/callback';

// Generate secure state to prevent CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['discord_oauth_state'] = $state;

$authUrl = "https://discord.com/api/oauth2/authorize?" . http_build_query([
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'identify',
    'state'         => $state
]);

header("Location: " . $authUrl);
exit();
?>

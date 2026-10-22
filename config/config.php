<?php
/**
 * AniXoHindi - Configuration & Settings
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Details
define('SITE_NAME', 'AniXoHindi');
define('SITE_TAGLINE', 'Watch Anime in Hindi Dubbed & Subbed');
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$siteProtocol = $isSecure ? 'https://' : 'http://';
$siteHost = $_SERVER['HTTP_HOST'] ?? 'anixohindi.cc';
define('SITE_URL', $siteProtocol . $siteHost);
define('CONTACT_EMAIL', 'support@anixohindi.com');

// Authoritative Hindi DB & Streaming Provider Configuration
define('HINDI_DB_PATH', '/www/wwwroot/anilist_database/anilist.db');
define('HINDI_EMBED_BASE', 'https://embed.anixohindi.cc');

// Database Credentials (Update with your MySQL credentials)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'anixohindi');

// Load local private credentials if available
if (file_exists(__DIR__ . '/credentials.php')) {
    require_once __DIR__ . '/credentials.php';
}

// Google OAuth 2.0 Credentials (Fallback)
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET');
}
$isWww = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'www.') !== false;
define('GOOGLE_REDIRECT_URI', 'https://' . ($isWww ? 'www.anixohindi.cc' : 'anixohindi.cc') . '/auth/callback.php');

// AniList OAuth Credentials (Replace with your AniList Developer Client ID/Secret)
define('ANILIST_CLIENT_ID', 'YOUR_ANILIST_CLIENT_ID');
define('ANILIST_CLIENT_SECRET', 'YOUR_ANILIST_CLIENT_SECRET');
define('ANILIST_REDIRECT_URI', SITE_URL . '/auth/callback.php?provider=anilist');

// The Movie Database (TMDB) API Credentials
define('TMDB_API_KEY', '85c0c9c03af878253af71c2ef787eb32');
define('TMDB_ACCESS_TOKEN', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI4NWMwYzljMDNhZjg3ODI1M2FmNzFjMmVmNzg3ZWIzMiIsIm5iZiI6MTc4MzM1Njg1MS4zMDMsInN1YiI6IjZhNGJkZGIzOWUzZjJiYjEzMDg0ZTc3YyIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.83s64Jy8FXMUIT8LK3GdGTTNCrE7nc4VEHlp-_rwWLE');

// Helper function to sanitize output
function e($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Current logged in user helper
function currentUser() {
    return $_SESSION['user'] ?? null;
}

function isLoggedIn() {
    return !empty($_SESSION['user']);
}

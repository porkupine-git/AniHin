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
define('SITE_URL', 'http://localhost:3000');
define('CONTACT_EMAIL', 'support@anixohindi.com');

// Database Credentials (Update with your MySQL credentials)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'anixohindi');

// Google OAuth 2.0 Credentials (Replace with your actual keys from Google Cloud Console)
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth/callback.php?provider=google');

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

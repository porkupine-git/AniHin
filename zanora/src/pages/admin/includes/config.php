<?php
// includes/config.php - Admin Panel Configuration & Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/_config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
} else {
    require_once(dirname(__DIR__, 4) . '/_config.php');
}

if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/user/bootstrap_session.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/user/bootstrap_session.php');
} elseif (file_exists(dirname(__DIR__, 4) . '/src/ajax/user/bootstrap_session.php')) {
    require_once(dirname(__DIR__, 4) . '/src/ajax/user/bootstrap_session.php');
}

// Bootstrap user session from session_token cookie if session is missing
if (function_exists('ensureUserSession') && isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        ensureUserSession($conn);
    } catch (Throwable $e) {}
}

// Define CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('ADMIN_BASE_URL', '/admin');
define('ADMIN_ASSETS_URL', '/src/pages/admin/assets');

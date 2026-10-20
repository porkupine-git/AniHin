<?php
// includes/auth.php - Security, Session Authentication & Permission Middleware
require_once __DIR__ . '/config.php';

// Bootstrap session if needed
if (function_exists('ensureUserSession') && isset($conn) && $conn instanceof mysqli) {
    ensureUserSession($conn);
}

$user_id = $_SESSION['userID'] ?? null;
$is_logged_in = false;
$admin_username = 'Staff';

if (!empty($user_id) && isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        $stmt = @$conn->prepare("SELECT id, username, roles, is_blocked, is_admin FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) {
            // Fallback query if is_admin column has not been migrated yet
            $stmt = @$conn->prepare("SELECT id, username, roles, is_blocked FROM users WHERE id = ? LIMIT 1");
        }
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Admin verification: roles = 1 (or is_admin = 1) and not blocked
            if ($userData && empty($userData['is_blocked']) && ((int)($userData['roles'] ?? 0) === 1 || (int)($userData['is_admin'] ?? 0) === 1)) {
                $is_logged_in = true;
                $admin_username = $userData['username'];

                // Hydrate admin session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = (int)$userData['id'];
                $_SESSION['admin_username']  = $admin_username;
                $_SESSION['admin_role']      = 'super_admin';
                $_SESSION['roles']           = 1;
            }
        }
    } catch (Throwable $e) {
        error_log("Admin auth check error: " . $e->getMessage());
    }
}

// Fallback: If live DB lookup had a temporary issue but user is already an authenticated super admin in session
if (!$is_logged_in && !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']) && (int)($_SESSION['roles'] ?? 0) === 1) {
    $is_logged_in = true;
    $admin_username = $_SESSION['admin_username'] ?? 'Staff';
}

function renderAdmin404() {
    http_response_code(404);
    $doc404 = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/404.php' : '');
    $rel404 = dirname(__DIR__, 4) . '/404.php';
    if ($doc404 && file_exists($doc404)) {
        include $doc404;
    } elseif (file_exists($rel404)) {
        include $rel404;
    } else {
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1></body></html>";
    }
    exit();
}

// Unauthorized visitors receive 404 Not Found (invisible panel protection)
function enforceAdminAuth() {
    global $is_logged_in;
    if (!$is_logged_in) {
        renderAdmin404();
    }
}

// Helper: Enforce specific admin privilege level
function checkAdminPermission($requiredRole = 'moderator') {
    global $is_logged_in;
    if (!$is_logged_in) {
        renderAdmin404();
    }

    $currentRole = $_SESSION['admin_role'] ?? 'super_admin';
    $roleHierarchy = [
        'super_admin' => 3,
        'admin'       => 2,
        'moderator'   => 1
    ];

    $currentVal  = $roleHierarchy[$currentRole] ?? 3;
    $requiredVal = $roleHierarchy[$requiredRole] ?? 1;

    if ($currentVal < $requiredVal) {
        http_response_code(404);
        include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        exit();
    }
}

// Helper: Log Admin Actions
function logAdminAction($action) {
    global $conn;
    $adminId   = $_SESSION['admin_id'] ?? ($_SESSION['userID'] ?? 0);
    $adminName = $_SESSION['admin_username'] ?? ($_SESSION['username'] ?? 'Admin');
    $ip        = function_exists('getClientIP') ? getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? '');
    $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (isset($conn) && $conn instanceof mysqli) {
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, username, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issss", $adminId, $adminName, $action, $ip, $ua);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Automatically enforce admin authorization on any admin panel page access
enforceAdminAuth();

if (isset($_GET['logout'])) {
    logAdminAction("Admin exited control panel.");
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
    header('Location: /user/logout');
    exit();
}

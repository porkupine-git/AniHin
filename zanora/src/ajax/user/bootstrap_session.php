<?php
// Only block direct URL requests to bootstrap_session.php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (
        empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
    ) {
        http_response_code(404);
        include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        exit();
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('ensureUserSession')) {
    function ensureUserSession(mysqli $conn): ?int {
        if (!empty($_SESSION['userID'])) {
            return (int)$_SESSION['userID'];
        }

        $token = $_COOKIE['session_token'] ?? '';
        if (!$token) return null;

        $token_hash = hash('sha256', $token);
        $stmt = $conn->prepare("
            SELECT s.id AS session_id, s.expires_at,
                   u.id AS user_id, u.username, u.roles
            FROM user_sessions s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.token_hash = ? AND s.expires_at > NOW()
            LIMIT 1
        ");
        if (!$stmt) return null;
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if (!$user) return null;

        // Extend expiry
        $newExpiry = date('Y-m-d H:i:s', time() + 86400 * 30);
        $upd = $conn->prepare("UPDATE user_sessions SET expires_at = ? WHERE id = ?");
        if ($upd) {
            $upd->bind_param("si", $newExpiry, $user['session_id']);
            $upd->execute();
            $upd->close();
        }

        // Refresh cookie
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        setcookie('session_token', $token, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Hydrate PHP session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['userID']   = (int)$user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['roles']    = (int)$user['roles'];
        }

        return (int)$user['user_id'];
    }
}

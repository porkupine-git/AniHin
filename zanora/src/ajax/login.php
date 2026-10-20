<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_functions.php');
header("Content-Type: application/json; charset=utf-8");

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);


$cf_token = $_POST['cf-turnstile-response'] ?? '';
if (!verify_turnstile($cf_token)) {
    echo json_encode(['status'=>false,'msg'=>'Turnstile verification failed']);
    exit;
}

// Input
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = !empty($_POST['remember']);

// Basic validation
if (!$email || !$password) {
    echo json_encode(['status' => false, 'msg' => 'Please fill in all fields']);
    exit;
}

// Lookup user
$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {

        // ✅ Regenerate session ID to prevent fixation attacks
        session_regenerate_id(true);

        // ✅ Store session data
        $_SESSION['userID'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // ✅ Clean up expired sessions
        $conn->query("DELETE FROM user_sessions WHERE expires_at < NOW()");

        // ✅ Only create persistent token if "Remember Me" checked
        if ($remember) {
            $token_plain = bin2hex(random_bytes(32)); // 64-char random token
            $token_hash  = hash('sha256', $token_plain);
            $expires     = time() + (86400 * 30); // 30 days

            // Record session in DB (multi-device allowed)
            $stmt2 = $conn->prepare("
                INSERT INTO user_sessions (user_id, token_hash, expires_at, created_at, ip, user_agent)
                VALUES (?, ?, FROM_UNIXTIME(?), NOW(), ?, ?)
            ");
            $ip = getClientIP();
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt2->bind_param("isiss", $user['id'], $token_hash, $expires, $ip, $agent);
            $stmt2->execute();
            $stmt2->close();

            // Set secure cookie for 30 days
            setcookie('session_token', $token_plain, [
                'expires'  => $expires,
                'path'     => '/',
                'secure'   => true,   // HTTPS only
                'httponly' => true,   // not accessible via JS
                'samesite' => 'Strict'
            ]);
        }

        echo json_encode([
            'status' => true,
            'msg' => 'Login successful',
            'reload' => true,
            'info' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);

    } else {
        echo json_encode(['status' => false, 'msg' => 'Incorrect password']);
    }

} else {
    echo json_encode(['status' => false, 'msg' => 'Account not found']);
}

// Cleanup
$stmt->close();
$conn->close();
?>

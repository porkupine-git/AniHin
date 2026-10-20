<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_functions.php');

header("Content-Type: application/json; charset=utf-8");

$cf_token = $_POST['cf-turnstile-response'] ?? '';

if (!verify_turnstile($cf_token)) {
    echo json_encode(['status'=>false,'msg'=>'Verification failed']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'msg' => 'Valid email required']);
    exit;
}

$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['status' => false, 'msg' => 'No account found']);
    exit;
}

$ip  = getClientIP();
$now = time();

if (!defined('DISABLE_PASSWORD_RESET_RATE_LIMIT') || DISABLE_PASSWORD_RESET_RATE_LIMIT !== true) {
    try {
        $stmt = $conn->prepare("SELECT last_sent FROM password_reset_rate WHERE ip = ? ORDER BY last_sent DESC LIMIT 1");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 60)) {
            echo json_encode(['status' => false, 'msg' => 'Too many requests. Please wait.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT last_sent FROM password_reset_rate WHERE email = ? ORDER BY last_sent DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 300)) {
            echo json_encode(['status' => false, 'msg' => 'Password reset already requested recently.']);
            exit;
        }
    } catch (\Throwable $e) {
        error_log("RESET RATE LIMIT ERROR: " . $e->getMessage());
    }
}

function generateRandomPassword($length = 10) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

$newPassword = generateRandomPassword();
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$htmlBody = "
<div style='background:linear-gradient(135deg,#6d28d9,#9333ea);padding:40px 20px;font-family:Arial,sans-serif'>
    <div style='max-width:500px;margin:auto;background:#ffffff;border-radius:12px;padding:30px;text-align:center;color:#333'>
        
        <h2 style='color:#6d28d9;margin-bottom:10px'>Password Reset</h2>

        <p>Hi <strong>{$user['username']}</strong>,</p>

        <p>Your password has been reset for <strong>" . WEBSITE_TITLE . "</strong>.</p>

        <div style='margin:20px 0;padding:15px;background:#f3f4f6;border-radius:8px'>
            <p style='margin:0;font-size:14px;color:#555'>Your new password:</p>
            <p style='font-size:18px;font-weight:bold;color:#111;margin:5px 0'>
                " . htmlspecialchars($newPassword) . "
            </p>
        </div>

        <p style='color:#555'>Please login and change your password immediately.</p>

        <a href='" . WEBSITE_URL . "/login'
           style='display:inline-block;margin-top:20px;padding:12px 24px;
           background:#6d28d9;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold'>
           Go to Login
        </a>

        <p style='margin-top:25px;font-size:12px;color:#777'>
            If you didn’t request this, secure your account immediately.
        </p>

    </div>
</div>
";

$payload = [
    "sender" => [
        "name" => WEBSITE_TITLE,
        "email" => SMTP_EMAIL
    ],
    "to" => [
        [
            "email" => $email,
            "name" => $user['username']
        ]
    ],
    "subject" => "Password Reset - " . WEBSITE_TITLE,
    "htmlContent" => $htmlBody
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.brevo.com/v3/smtp/email",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "api-key: " . BREVO_API_KEY,
        "content-type: application/json"
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error) {
    error_log("RESET MAIL CURL ERROR: " . $error);
    echo json_encode(['status' => false, 'msg' => 'Email service unavailable']);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user['id']);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO password_reset_rate (email, ip, last_sent) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => true, 'msg' => 'New password sent to your email']);

} else {
    error_log("RESET MAIL ERROR: " . $response);
    echo json_encode(['status' => false, 'msg' => 'Failed to send email']);
}
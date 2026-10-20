<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=utf-8");

/* -------------------------------------------------------------------------- */
/* GLOBALS */
/* -------------------------------------------------------------------------- */

$ip  = getClientIP();
$now = time();

/* -------------------------------------------------------------------------- */
/* GET USER */
/* -------------------------------------------------------------------------- */

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$userId = null;

if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $stmt = $conn->prepare("SELECT id, username, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo json_encode(['status' => false, 'msg' => 'No account found with this email.']);
        exit;
    }

    $userId = $user['id'];
    $username = $user['username'];
    $isVerified = $user['is_verified'];

} else {

    $userId = $_SESSION['userID'] ?? null;

    if (!$userId) {
        echo json_encode(['status' => false, 'msg' => 'You are not logged in']);
        exit;
    }

    $stmt = $conn->prepare("SELECT email, username, is_verified FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo json_encode(['status' => false, 'msg' => 'User not found']);
        exit;
    }

    $email = $user['email'];
    $username = $user['username'];
    $isVerified = $user['is_verified'];
}

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'msg' => 'Invalid email']);
    exit;
}

if ((int)$isVerified === 1) {
    echo json_encode(['status' => true, 'msg' => 'Email already verified']);
    exit;
}

/* -------------------------------------------------------------------------- */
/* RATE LIMIT */
/* -------------------------------------------------------------------------- */

if (!defined('DISABLE_EMAIL_RATE_LIMIT') || DISABLE_EMAIL_RATE_LIMIT !== true) {

    try {

        $stmt = $conn->prepare("SELECT last_sent FROM email_verification_rate WHERE ip = ? ORDER BY last_sent DESC LIMIT 1");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 60)) {
            echo json_encode(['status' => false, 'msg' => 'Please wait before retry']);
            exit;
        }

        $stmt = $conn->prepare("SELECT last_sent FROM email_verification_rate WHERE email = ? ORDER BY last_sent DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 300)) {
            echo json_encode(['status' => false, 'msg' => 'Email already sent recently']);
            exit;
        }

    } catch (\Throwable $e) {
        error_log("RATE LIMIT ERROR: " . $e->getMessage());
    }
}

/* -------------------------------------------------------------------------- */
/* TOKEN */
/* -------------------------------------------------------------------------- */

$tokenPlain = bin2hex(random_bytes(32));
$tokenHash  = hash('sha256', $tokenPlain);
$expires    = time() + 86400;

/* -------------------------------------------------------------------------- */
/* STORE */
/* -------------------------------------------------------------------------- */

$stmt = $conn->prepare("UPDATE users SET verification_token_hash=?, verification_expires=? WHERE id=?");
$stmt->bind_param("sii", $tokenHash, $expires, $userId);
$stmt->execute();
$stmt->close();

/* -------------------------------------------------------------------------- */
/* LOG */
/* -------------------------------------------------------------------------- */

$stmt = $conn->prepare("INSERT INTO email_verification_rate (email, ip, last_sent) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $email, $ip);
$stmt->execute();
$stmt->close();

/* -------------------------------------------------------------------------- */
/* EMAIL CONTENT */
/* -------------------------------------------------------------------------- */

$verifyUrl = rtrim(WEBSITE_URL, '/') . '/auth/account-verification?token=' . urlencode($tokenPlain);

$htmlBody = "
<div style='background:linear-gradient(135deg,#6d28d9,#9333ea);padding:40px 20px;font-family:Arial,sans-serif'>
    <div style='max-width:500px;margin:auto;background:#ffffff;border-radius:12px;padding:30px;text-align:center;color:#333'>
        
        <h2 style='color:#6d28d9;margin-bottom:10px'>Confirm Your Email</h2>

        <p>Hi <strong>$username</strong>,</p>
        <p>Verify your email for <strong>" . WEBSITE_TITLE . "</strong>.</p>

        <a href='$verifyUrl'
           style='display:inline-block;margin-top:20px;padding:12px 24px;
           background:#6d28d9;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold'>
           Verify Email
        </a>

        <p style='margin-top:25px;font-size:12px;color:#777'>
            This link expires in 24 hours.
        </p>
    </div>
</div>
";

/* -------------------------------------------------------------------------- */
/* SEND VIA BREVO API */
/* -------------------------------------------------------------------------- */

$payload = [
    "sender" => [
        "name" => WEBSITE_TITLE,
        "email" => SMTP_EMAIL
    ],
    "to" => [
        [
            "email" => $email,
            "name" => $username
        ]
    ],
    "subject" => "Verify your email for " . WEBSITE_TITLE,
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

/* -------------------------------------------------------------------------- */
/* RESPONSE */
/* -------------------------------------------------------------------------- */

if ($error) {
    error_log("BREVO CURL ERROR: " . $error);

    echo json_encode([
        'status' => false,
        'msg' => 'Email service unavailable'
    ]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode([
        'status' => true,
        'msg' => 'Verification email sent'
    ]);
} else {
    error_log("BREVO ERROR RESPONSE: " . $response);

    echo json_encode([
        'status' => false,
        'msg' => 'Failed to send email'
    ]);
}
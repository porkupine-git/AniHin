<?php

// verify-send.php
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/Exception.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=utf-8");

// === Config (move these into _config.php if not already) ===
// define('SMTP_HOST', '...');
// define('SMTP_USER', '...');
// define('SMTP_PASS', '...');
// define('SMTP_PORT', 587);
// define('SMTP_SECURE', 'tls'); // or 'ssl'
// define('WEBSITE_TITLE', 'YourSite');
// define('WEBSITE_URL', 'https://yourdomain.com');

// Input
$userId = $_SESSION['userID'] ?? null;

if (!$userId) {
    // Not logged in
    echo json_encode(['status' => false, 'msg' => 'You are not logged in']);
    exit;
}

$query = $conn->prepare("SELECT email FROM users WHERE id = ?");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($email);
$query->fetch();
$query->close();

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => false,
        'msg' => 'A valid email is required',
        'received_email' => $email
    ]);
    exit;
}

$ip = getClientIP();
$now = time();

// --- Rate limiting ---------------------------------------------------------
// Limits:
//  - Per IP: 1 request / 60 seconds
//  - Per email: 1 request / 300 seconds (5 minutes)

try {
    // 1) check per-IP
    $stmt = $conn->prepare("SELECT last_sent FROM email_verification_rate WHERE ip = ? ORDER BY last_sent DESC LIMIT 1");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row && strtotime($row['last_sent']) > ($now - 60)) {
        echo json_encode(['status' => false, 'msg' => 'Please wait a moment before requesting another verification email.']);
        exit;
    }

    // 2) check per-email
    $stmt = $conn->prepare("SELECT last_sent FROM email_verification_rate WHERE email = ? ORDER BY last_sent DESC LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row && strtotime($row['last_sent']) > ($now - 300)) {
        echo json_encode(['status' => false, 'msg' => 'A verification email was recently sent to this address. Check your inbox or wait a few minutes.']);
        exit;
    }
} catch (Exception $e) {
    // If the rate-limit table doesn't exist or DB error, proceed but log it
    error_log("Rate-limit check error: " . $e->getMessage());
}

// --- Lookup user ----------------------------------------------------------
$stmt = $conn->prepare("SELECT id, username, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['status' => false, 'msg' => 'Email not found']);
    exit;
}

if ($user['is_verified']) {
    echo json_encode(['status' => true, 'msg' => 'Email is already verified']);
    exit;
}

// --- Generate token & store hashed token with expiry ----------------------
$token_plain = bin2hex(random_bytes(32));        // 64 chars, sent to user
$token_hash  = hash('sha256', $token_plain);    // 64 hex chars stored in DB
$expires     = $now + 86400;                    // 24 hours

try {
    $stmt = $conn->prepare("UPDATE users SET verification_token_hash = ?, verification_expires = FROM_UNIXTIME(?) WHERE id = ?");
    $stmt->bind_param("sii", $token_hash, $expires, $user['id']);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Failed to update verification token for user {$user['id']}: " . $e->getMessage());
    echo json_encode(['status' => false, 'msg' => 'Server error. Try again later.']);
    exit;
}

// --- Record rate-limit entry (best-effort, non-blocking) ------------------
try {
    $stmt = $conn->prepare("INSERT INTO email_verification_rate (email, ip, last_sent) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    // non-fatal
    error_log("Failed to write rate limit row: " . $e->getMessage());
}

// --- Prepare email --------------------------------------------------------
$verifyUrl = rtrim(WEBSITE_URL, '/') . '/auth/account-verification?token=' . urlencode($token_plain);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE; // 'tls' or 'ssl'
    $mail->Port       = SMTP_PORT;
    $mail->setFrom(SMTP_USER, WEBSITE_TITLE);
    $mail->addAddress($email, $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Verify your email for ' . WEBSITE_TITLE;

    // Clean, mobile-friendly HTML
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #111; line-height:1.4;'>
            <h2 style='margin-bottom:0.2em;'>Confirm your email</h2>
            <p style='margin-top:0.2em;'>Hi <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
            <p>Click the button below to verify your email address for <strong>" . htmlspecialchars(WEBSITE_TITLE) . "</strong>.</p>
            <p style='text-align:center;'>
                <a href='" . htmlspecialchars($verifyUrl) . "' style='display:inline-block; padding:12px 20px; text-decoration:none; border-radius:6px; background:#1d72f3; color:#fff;'>Verify email</a>
            </p>
            <p style='font-size:90%; color:#666;'>This link expires in 24 hours. If you didn't create an account, you can ignore this email.</p>
            <hr>
            <p style='font-size:85%; color:#888;'>If the button doesn't work, copy-paste this link into your browser:<br><a href='" . htmlspecialchars($verifyUrl) . "'>" . htmlspecialchars($verifyUrl) . "</a></p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        'status' => true,
        'msg' => 'An email has been sent to your email address containing an activation link. Please check your email and click on the link to activate your account (It may get in the email spam box).'
    ]);
    exit;

} catch (Exception $e) {
    // Log the detailed error server-side (don’t reveal raw error to users)
    error_log("PHPMailer error sending verification to {$email}: " . $mail->ErrorInfo);
    echo json_encode([
        'status' => false,
        'msg' => 'Failed to send verification email right now. Please try again later.'
    ]);
    exit;
}

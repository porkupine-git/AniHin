<?php
// verify-send.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=utf-8");

/* --------------------------------------------------------------------------
   AUTH CHECK
--------------------------------------------------------------------------- */

$userId = $_SESSION['userID'] ?? null;

if (!$userId) {
    echo json_encode(['status' => false, 'msg' => 'You are not logged in']);
    exit;
}

/* --------------------------------------------------------------------------
   FETCH USER EMAIL
--------------------------------------------------------------------------- */

$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => false,
        'msg' => 'A valid email is required',
        'received_email' => $email
    ]);
    exit;
}

/* --------------------------------------------------------------------------
   RATE LIMITING (IP + EMAIL)
--------------------------------------------------------------------------- */

if (!defined('DISABLE_EMAIL_RATE_LIMIT') || DISABLE_EMAIL_RATE_LIMIT !== true) {

    $ip  = getClientIP();
    $now = time();

    try {
        // Per-IP: 1 / 60s
        $stmt = $conn->prepare(
            "SELECT last_sent FROM email_verification_rate
             WHERE ip = ? ORDER BY last_sent DESC LIMIT 1"
        );
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 60)) {
            echo json_encode([
                'status' => false,
                'msg' => 'Please wait before requesting another verification email.'
            ]);
            exit;
        }

        // Per-email: 1 / 5min
        $stmt = $conn->prepare(
            "SELECT last_sent FROM email_verification_rate
             WHERE email = ? ORDER BY last_sent DESC LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && strtotime($row['last_sent']) > ($now - 300)) {
            echo json_encode([
                'status' => false,
                'msg' => 'A verification email was recently sent. Please check your inbox.'
            ]);
            exit;
        }
    } catch (\Throwable $e) {
        error_log("Rate limit check failed: " . $e->getMessage());
    }
}

/* --------------------------------------------------------------------------
   USER LOOKUP
--------------------------------------------------------------------------- */

$stmt = $conn->prepare(
    "SELECT id, username, is_verified FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['status' => false, 'msg' => 'Email not found']);
    exit;
}

if ((int)$user['is_verified'] === 1) {
    echo json_encode(['status' => true, 'msg' => 'Email is already verified']);
    exit;
}

/* --------------------------------------------------------------------------
   TOKEN GENERATION
--------------------------------------------------------------------------- */

$tokenPlain = bin2hex(random_bytes(32)); // sent to user
$tokenHash  = hash('sha256', $tokenPlain);
$expires    = time() + 86400; // 24 hours (UNIX)

/* --------------------------------------------------------------------------
   STORE TOKEN
--------------------------------------------------------------------------- */

try {
    $stmt = $conn->prepare(
        "UPDATE users
         SET verification_token_hash = ?, verification_expires = ?
         WHERE id = ?"
    );
    $stmt->bind_param("sii", $tokenHash, $expires, $user['id']);
    $stmt->execute();
    $stmt->close();
} catch (\Throwable $e) {
    error_log("Token update failed for user {$user['id']}: " . $e->getMessage());
    echo json_encode(['status' => false, 'msg' => 'Server error. Try again later.']);
    exit;
}

/* --------------------------------------------------------------------------
   LOG RATE LIMIT (NON-BLOCKING)
--------------------------------------------------------------------------- */

try {
    $stmt = $conn->prepare(
        "INSERT INTO email_verification_rate (email, ip, last_sent)
         VALUES (?, ?, NOW())"
    );
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();
} catch (\Throwable $e) {
    error_log("Rate limit insert failed: " . $e->getMessage());
}

/* --------------------------------------------------------------------------
   PREPARE EMAIL
--------------------------------------------------------------------------- */

$verifyUrl = rtrim(WEBSITE_URL, '/') .
    '/auth/account-verification?token=' . urlencode($tokenPlain);

$htmlBody = "
<div style='font-family:Arial,sans-serif;color:#111;line-height:1.5'>
    <h2>Confirm your email</h2>
    <p>Hi <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
    <p>Please verify your email address for <strong>" . htmlspecialchars(WEBSITE_TITLE) . "</strong>.</p>
    <p style='text-align:center;margin:30px 0'>
        <a href='" . htmlspecialchars($verifyUrl) . "'
           style='background:#1d72f3;color:#fff;padding:12px 22px;
                  border-radius:6px;text-decoration:none;font-weight:bold'>
            Verify Email
        </a>
    </p>
    <p style='font-size:90%;color:#666'>
        This link expires in 24 hours. If you did not create this account,
        you can safely ignore this email.
    </p>
    <hr>
    <p style='font-size:85%;color:#888'>
        If the button doesn't work, copy and paste this link:<br>
        <a href='" . htmlspecialchars($verifyUrl) . "'>" . htmlspecialchars($verifyUrl) . "</a>
    </p>
</div>
";

/* --------------------------------------------------------------------------
   SEND EMAIL
--------------------------------------------------------------------------- */

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    // IMPORTANT: sender must be a real / verified address
    $mail->setFrom(SMTP_EMAIL, WEBSITE_TITLE);
    $mail->addReplyTo(SMTP_EMAIL, WEBSITE_TITLE);

    $mail->addAddress($email, $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Verify your email for ' . WEBSITE_TITLE;
    $mail->Body    = $htmlBody;

    $mail->send();

    echo json_encode([
        'status' => true,
        'msg' => 'Verification email sent. Please check your inbox (or spam folder).'
    ]);
    exit;

} catch (Exception $e) {
    error_log("PHPMailer error ({$email}): " . $mail->ErrorInfo);
    echo json_encode([
        'status' => false,
        'msg' => 'Failed to send verification email. Please try again later.'
    ]);
    exit;
}
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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/mailer/Exception.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
header("Content-Type: application/json; charset=utf-8");

$cf_token = $_POST['cf-turnstile-response'] ?? '';
if (!verify_turnstile($cf_token)) {
    echo json_encode(['status'=>false,'msg'=>'Turnstile verification failed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!$email) {
    echo json_encode(['status' => false, 'msg' => 'Email is required']);
    exit;
}
// ✅ Step 1: Check if email exists
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();
if (!$user) {
    echo json_encode(['status' => false, 'msg' => 'No account found with this email']);
    exit;
}
// ✅ Step 2: Generate a new random password
function generateRandomPassword($length = 10) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#\$%^&*"), 0, $length);
}
$newPassword = generateRandomPassword();
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
// ✅ Step 3: Update user's password in the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashedPassword, $user['id']);
$stmt->execute();
$stmt->close();
// ✅ Step 4: Send the new password via email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '93b51b001@smtp-brevo.com';
    $mail->Password   = 'jfybLsdWph8PxGSt';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom('mojahidalam8092@gmail.com', $websiteTitle);
    $mail->addAddress($email, $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Your New Password for ' . $websiteTitle;
    $mail->Body = "
        Hi <b>{$user['username']}</b>,<br><br>
        We have reset your password as requested.<br><br>
        <b>New Password:</b> <code>$newPassword</code><br><br>
        Please log in using this password and change it immediately from your profile settings.<br><br>
        Thanks,<br>
        <b>$websiteTitle</b> Team
    ";
    $mail->send();
    echo json_encode(['status' => true, 'msg' => 'A new password has been sent to your email.']);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/reset-log.txt', "❌ Reset Email Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
    echo json_encode(['status' => false, 'msg' => 'Failed to send email. Try again later.']);
}
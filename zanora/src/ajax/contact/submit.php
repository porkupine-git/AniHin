<?php
/**
 * /src/ajax/contact/submit.php
 * Handles Contact Form Submissions and records them in the database
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'msg' => 'Method not allowed.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/_config.php';

$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$name = trim($_POST['name'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'msg' => 'Please provide a valid email address.']);
    exit;
}

if (empty($subject) || mb_strlen($subject) < 2) {
    echo json_encode(['status' => false, 'msg' => 'Subject is too short (minimum 2 characters).']);
    exit;
}

if (empty($message) || mb_strlen($message) < 5) {
    echo json_encode(['status' => false, 'msg' => 'Message is too short (minimum 5 characters).']);
    exit;
}

if (mb_strlen($subject) > 255) {
    $subject = mb_substr($subject, 0, 255);
}

if (mb_strlen($message) > 4000) {
    $message = mb_substr($message, 0, 4000);
}

// IP & User Agent
$ip = function_exists('getClientIP') ? getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Self-healing: ensure contact_messages table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `name` VARCHAR(100) NULL,
        `email` VARCHAR(255) NOT NULL,
        `subject` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT NULL,
        `status` ENUM('unread', 'read', 'in_progress', 'resolved', 'spam') DEFAULT 'unread',
        `admin_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Rate-limiting check: max 5 submissions per 10 minutes per IP
$rateLimitStmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
if ($rateLimitStmt) {
    $rateLimitStmt->bind_param("s", $ip);
    $rateLimitStmt->execute();
    $rlRes = $rateLimitStmt->get_result()->fetch_assoc();
    $rateLimitStmt->close();
    if ($rlRes && (int)$rlRes['count'] >= 5) {
        echo json_encode(['status' => false, 'msg' => 'Too many submissions. Please wait a few minutes before sending another inquiry.']);
        exit;
    }
}

// Check logged in user
$userId = null;
if (isset($_SESSION['userID'])) {
    $userId = (int)$_SESSION['userID'];
    if (empty($name) && !empty($_SESSION['username'])) {
        $name = $_SESSION['username'];
    }
}

// Insert into contact_messages
$stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'unread')");
if (!$stmt) {
    error_log("Failed to prepare contact insert: " . $conn->error);
    echo json_encode(['status' => false, 'msg' => 'An error occurred while saving your message. Please try again later.']);
    exit;
}

$stmt->bind_param("issssss", $userId, $name, $email, $subject, $message, $ip, $userAgent);
if ($stmt->execute()) {
    $stmt->close();
    echo json_encode([
        'status' => true,
        'msg' => 'Thank you! Your message has been received. Our team will review your inquiry shortly.'
    ]);
} else {
    $err = $stmt->error;
    $stmt->close();
    error_log("Contact insert execute failed: " . $err);
    echo json_encode(['status' => false, 'msg' => 'Failed to submit inquiry. Please try again.']);
}

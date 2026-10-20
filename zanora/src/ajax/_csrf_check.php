<?php
// _csrf_check.php - CSRF Protection for state-modifying AJAX requests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrfPost   = $_POST['csrf_token'] ?? '';
$csrfGet    = $_GET['csrf_token'] ?? '';
$csrfToken  = $csrfHeader ?: ($csrfPost ?: $csrfGet);

if (empty($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode([
        'status' => false,
        'msg'    => 'CSRF token validation failed. Request blocked.'
    ]);
    exit;
}
?>

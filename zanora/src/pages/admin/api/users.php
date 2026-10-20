<?php
// api/users.php - User Accounts Search & Inspection Endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

enforceAdminAuth();

$action = $_GET['action'] ?? '';
$response = [];

switch ($action) {
    case 'search_users':
        checkAdminPermission('admin');
        $query = $_GET['query'] ?? '';
        $response = searchUsers($query);
        break;
    case 'get_user_details':
        checkAdminPermission('admin');
        $id = intval($_GET['id'] ?? 0);
        $response = getUserDetails($id);
        break;
    default:
        http_response_code(400);
        $response = ['error' => 'Invalid action specified.'];
}

echo json_encode($response);
exit;

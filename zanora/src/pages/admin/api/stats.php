<?php
// api/stats.php - JSON Telemetry & Statistics Endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

enforceAdminAuth();

$action = $_GET['action'] ?? '';
$response = [];

switch ($action) {
    case 'get_live_stats':
        checkAdminPermission('moderator');
        $response = getLiveStats();
        break;
    case 'get_daily_stats':
        checkAdminPermission('moderator');
        $response = getDailyStats();
        break;
    case 'get_traffic_data':
        checkAdminPermission('moderator');
        $response = getTrafficData();
        break;
    case 'get_server_health':
        checkAdminPermission('admin');
        $response = getServerHealth();
        break;
    default:
        http_response_code(400);
        $response = ['error' => 'Invalid action specified.'];
}

echo json_encode($response);
exit;

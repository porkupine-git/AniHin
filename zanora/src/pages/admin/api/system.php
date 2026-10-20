<?php
// api/system.php - Cache, Database & System Operations Endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

enforceAdminAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = [];

switch ($action) {
    case 'clear_redis_cache':
        checkAdminPermission('super_admin');
        $response = clearRedisCache();
        break;
    case 'db_optimize':
        checkAdminPermission('super_admin');
        $response = optimizeDatabase();
        break;
    case 'toggle_maintenance_quick':
        checkAdminPermission('super_admin');
        $val = isset($_POST['maintenance_mode_quick']) ? '1' : '0';
        $stmt = @$conn->prepare("INSERT INTO site_config (name, value) VALUES ('maintenance_mode', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        if ($stmt) {
            $stmt->bind_param("s", $val);
            $stmt->execute();
            $stmt->close();
            logAdminAction(($val === '1' ? "Enabled" : "Disabled") . " maintenance mode via quick toggle.");
            $response = ['success'=>true, 'message'=>'Maintenance mode ' . ($val === '1' ? 'enabled' : 'disabled') . '!'];
        } else {
            $response = ['success'=>false, 'message'=>'Database error'];
        }
        break;
    default:
        http_response_code(400);
        $response = ['error' => 'Invalid action specified.'];
}

echo json_encode($response);
exit;

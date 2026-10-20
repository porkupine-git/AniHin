<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/_config.php';

try {
    if (!$conn || $conn->connect_error) {
        throw new Exception("DB connection not available");
    }

    // Clean up old sessions (users inactive for more than 5 minutes)
    $conn->query("DELETE FROM active_users WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    
    // Get current session ID
    $session_id = session_id();
    $user_id = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
    $ip_address = function_exists('getClientIP') ? getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $current_page = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Insert or update current user
    $stmt = $conn->prepare("INSERT INTO active_users (session_id, user_id, ip_address, user_agent, current_page) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           user_id = VALUES(user_id),
                           ip_address = VALUES(ip_address),
                           user_agent = VALUES(user_agent),
                           current_page = VALUES(current_page),
                           last_activity = CURRENT_TIMESTAMP");
    if ($stmt) {
        $stmt->bind_param("sisss", $session_id, $user_id, $ip_address, $user_agent, $current_page);
        $stmt->execute();
        $stmt->close();
    }
    
    // Get count of active users
    $res = $conn->query("SELECT COUNT(*) as count FROM active_users");
    $count = $res ? ($res->fetch_assoc()['count'] ?? 0) : 0;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['count' => (int)$count]);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error', 'msg' => $e->getMessage()]);
}
?>
<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Auth check ---
if (empty($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Not logged in']);
    exit;
}

$userId = intval($_SESSION['userID']);

// ----------------------------------------------------
// 🔹 1️⃣ QUICK ACTION — POST ?action=quick (single option)
// ----------------------------------------------------
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_REQUEST['action']) &&
    $_REQUEST['action'] === 'quick'
) {
    $option = $_POST['option'] ?? $_GET['option'] ?? null;
    $value  = $_POST['value'] ?? $_GET['value'] ?? null;

    if (!$option) {
        echo json_encode(['status' => false, 'msg' => 'Missing option']);
        exit;
    }

    // Allowed options
    $allowed = [
        'auto_play', 'auto_next', 'auto_skip_intro',
        'show_comments_at_home', 'public_watch_list',
        'anime_name', 'notify_ignore_lang',
        'enable_dub', 'play_original_audio', 'live_status'
    ];

    if (!in_array($option, $allowed, true)) {
        echo json_encode(['status' => false, 'msg' => 'Invalid option']);
        exit;
    }

    // Normalize
    $val = $value;
    if (in_array($option, ['auto_play','auto_next','auto_skip_intro','show_comments_at_home','public_watch_list', 'enable_dub', 'play_original_audio', 'live_status'], true)) {
        $val = (int)$value;
        $type = 'ii';
    } else {
        if ($option === 'anime_name') {
            $val = ($val === 'jp') ? 'jp' : 'en';
        } elseif ($option === 'notify_ignore_lang') {
            $val = in_array($val, ['sub','dub'], true) ? $val : '';
        }
        $type = 'si';
    }

    // Update
    $stmt = $conn->prepare("UPDATE users SET {$option} = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['status' => false, 'msg' => 'Database error']);
        exit;
    }

    $stmt->bind_param(substr($type, 0, 1) . 'i', $val, $userId);
    $stmt->execute();
    $stmt->close();

    if ($option === 'live_status' && (int)$val === 0) {
        $del = $conn->prepare("DELETE FROM live_watching WHERE user_id = ?");
        $del->bind_param("i", $userId);
        $del->execute();
        $del->close();
    }

    echo json_encode(['status' => true, 'msg' => 'Your changes have been saved.']);
    exit;
}

// ----------------------------------------------------
// 🔹 2️⃣ FULL UPDATE — POST (JSON body)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
	$post = json_decode($input, true);

    if (!is_array($post)) {
	    $post = $_POST;
	}
    
    if (!is_array($post) || count($post) === 0) {
	    echo json_encode(['status' => false, 'msg' => 'Invalid or missing data']);
	    exit;
	}

    $fields = [];
    $params = [];
    $types = '';

    $map = [
        'auto_play' => 'i',
        'auto_next' => 'i',
        'auto_skip_intro' => 'i',
        'show_comments_at_home' => 'i',
        'public_watch_list' => 'i',
        'enable_dub' => 'i',
        'play_original_audio' => 'i',
        'live_status' => 'i'
    ];

    foreach ($map as $key => $type) {
        if (isset($post[$key])) {
            $fields[] = "$key = ?";
            $params[] = (int)$post[$key];
            $types .= $type;
        }
    }

    if (isset($post['anime_name'])) {
        $fields[] = "anime_name = ?";
        $params[] = ($post['anime_name'] === 'jp') ? 'jp' : 'en';
        $types .= 's';
    }

    if (isset($post['notify_ignore_folders'])) {
        if (is_array($post['notify_ignore_folders'])) {
            $valid = array_filter($post['notify_ignore_folders'], fn($v) => in_array((int)$v, [1,2,3,4,5]));
            $fields[] = "notify_ignore_folders = ?";
            $params[] = json_encode(array_values($valid));
            $types .= 's';
        }
    }

    if (isset($post['notify_ignore_lang'])) {
        $lang = in_array($post['notify_ignore_lang'], ['sub','dub'], true) ? $post['notify_ignore_lang'] : '';
        $fields[] = "notify_ignore_lang = ?";
        $params[] = $lang;
        $types .= 's';
    }

    if (count($fields) === 0) {
        echo json_encode(['status' => false, 'msg' => 'No valid settings to update']);
        exit;
    }

    $params[] = $userId;
    $types .= 'i';
    $stmt = $conn->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['status' => false, 'msg' => 'Database error']);
        exit;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    if (isset($post['live_status']) && (int)$post['live_status'] === 0) {
        $del = $conn->prepare("DELETE FROM live_watching WHERE user_id = ?");
        $del->bind_param("i", $userId);
        $del->execute();
        $del->close();
    }

    echo json_encode(['status' => true, 'msg' => 'Your settings have been updated.']);
    exit;
}

// ----------------------------------------------------
// 🔹 GET — Fetch settings
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT 
            auto_play, auto_next, auto_skip_intro,
            show_comments_at_home, public_watch_list,
            anime_name, notify_ignore_folders, notify_ignore_lang,
            live_status
        FROM users WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    $data['notify_ignore_folders'] = json_decode($data['notify_ignore_folders'] ?? '[]', true);
    echo json_encode(['status' => true, 'data' => $data]);
    exit;
}

echo json_encode(['status' => false, 'msg' => 'Invalid request']);
exit;
?>
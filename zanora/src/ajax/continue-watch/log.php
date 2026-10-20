<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
// /ajax/continue-watch/log/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header("Content-Type: application/json; charset=utf-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

/*
Frontend (x-www-form-urlencoded via $.post):
  movie_id        (slug)  -> anime_id
  episode_id      (int)
  episode_number  (int)
  time            (int seconds)
  percent         (0..100)
  duration        (int seconds)
*/
$userId        = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;
$animeId       = isset($_POST['movie_id'])       ? trim($_POST['movie_id'])      : '';
$episodeId     = isset($_POST['episode_id'])     ? (int)$_POST['episode_id']     : 0;
$episodeNumber = isset($_POST['episode_number']) ? (int)$_POST['episode_number'] : 0;
$timeSec       = isset($_POST['time'])           ? (int)$_POST['time']           : 0;
$percent       = isset($_POST['percent'])        ? (int)$_POST['percent']        : 0;
$durationSec   = isset($_POST['duration'])       ? (int)$_POST['duration']       : 0;

if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}
if ($animeId === '' || $episodeId <= 0 || $episodeNumber <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data']);
    exit;
}

/* clamp/sanitize */
$timeSec     = max(0, $timeSec);
$durationSec = max(0, $durationSec);
if ($durationSec > 0 && $timeSec > $durationSec) $timeSec = $durationSec;
$percent = max(0, min(100, $percent));

/* completion rule: >=80% or <=30s left */
$completeByPercent = ($percent >= 80);
$completeByTail    = ($durationSec > 0 && ($durationSec - $timeSec) <= 30);
$shouldMarkWatched = $completeByPercent || $completeByTail;

$conn->begin_transaction();

try {
    // 1) Upsert the user's latest progress for this anime (one row per user+anime)
    //    We ONLY touch columns we actually use now.
    $sql = "
        INSERT INTO watch_history
            (user_id, anime_id, episode_id, episode_number, progress, progress_percent, duration, created_at, updated_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            episode_id       = VALUES(episode_id),
            episode_number   = VALUES(episode_number),
            progress         = VALUES(progress),
            progress_percent = VALUES(progress_percent),
            duration         = VALUES(duration),
            updated_at       = NOW()
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare watch_history failed: '.$conn->error);
    $stmt->bind_param(
        'isiiiii',
        $userId, $animeId, $episodeId, $episodeNumber, $timeSec, $percent, $durationSec
    );
    if (!$stmt->execute()) throw new Exception('Exec watch_history failed: '.$stmt->error);
    $stmt->close();

    // Fetch live_status setting
    $liveStatus = 0;
    $chk = $conn->prepare("SELECT live_status FROM users WHERE id = ? LIMIT 1");
    $chk->bind_param("i", $userId);
    $chk->execute();
    $chkRes = $chk->get_result()->fetch_assoc();
    if ($chkRes) {
        $liveStatus = (int)$chkRes['live_status'];
    }
    $chk->close();

    // 1.5) Upsert user's current live watch status if enabled
    if ($liveStatus === 1) {
        $stmtLive = $conn->prepare("
            INSERT INTO live_watching (user_id, anime_id, episode_id, episode_number, progress, duration)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                anime_id = VALUES(anime_id),
                episode_id = VALUES(episode_id),
                episode_number = VALUES(episode_number),
                progress = VALUES(progress),
                duration = VALUES(duration)
        ");
        $stmtLive->bind_param("isiiii", $userId, $animeId, $episodeId, $episodeNumber, $timeSec, $durationSec);
        $stmtLive->execute();
        $stmtLive->close();
    } else {
        $stmtDelLive = $conn->prepare("DELETE FROM live_watching WHERE user_id = ?");
        $stmtDelLive->bind_param("i", $userId);
        $stmtDelLive->execute();
        $stmtDelLive->close();
    }

    // 2) Mark watched when basically finished (append to CSV once)
    if ($shouldMarkWatched) {
        $episodeStr = (string)$episodeNumber;
        $sqlWatched = "
            INSERT INTO watched_episode (user_id, anime_id, episodes_watched, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                episodes_watched = IF(
                    FIND_IN_SET(?, episodes_watched) > 0,
                    episodes_watched,
                    CONCAT(IFNULL(episodes_watched, ''), IF(episodes_watched IS NULL OR episodes_watched = '', '', ','), ?)
                ),
                updated_at = NOW()
        ";
        $stmt2 = $conn->prepare($sqlWatched);
        if (!$stmt2) throw new Exception('Prepare watched_episode failed: '.$conn->error);
        $stmt2->bind_param('issss', $userId, $animeId, $episodeStr, $episodeStr, $episodeStr);
        if (!$stmt2->execute()) throw new Exception('Exec watched_episode failed: '.$stmt2->error);
        $stmt2->close();
    }

    // Hook: AniList progress sync queue
    $checkLinked = $conn->prepare("SELECT anilist_id FROM users WHERE id = ? AND anilist_token IS NOT NULL LIMIT 1");
    $checkLinked->bind_param("i", $userId);
    $checkLinked->execute();
    $isLinked = $checkLinked->get_result()->fetch_assoc();
    $checkLinked->close();

    if ($isLinked) {
        $wlType = 1; 
        $wlScore = 0;
        $wlNotes = '';
        $wlStmt = $conn->prepare("SELECT type, score, notes FROM watchlist WHERE user_id = ? AND anime_id = ? LIMIT 1");
        $wlStmt->bind_param("is", $userId, $animeId);
        $wlStmt->execute();
        if ($wlRow = $wlStmt->get_result()->fetch_assoc()) {
            $wlType = (int)$wlRow['type'];
            $wlScore = (int)$wlRow['score'];
            $wlNotes = $wlRow['notes'];
        }
        $wlStmt->close();

        $qStatus = 'pending';
        $syncSql = "
            INSERT INTO sync_queue (user_id, anime_id, type, episodes_watched, score, progress, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                type = VALUES(type), 
                episodes_watched = VALUES(episodes_watched),
                progress = VALUES(progress),
                status = 'pending',
                error_count = 0,
                updated_at = NOW()
        ";
        $syncStmt = $conn->prepare($syncSql);
        $syncStmt->bind_param("isiiiis", $userId, $animeId, $wlType, $episodeNumber, $wlScore, $episodeNumber, $qStatus);
        $syncStmt->execute();
        $syncStmt->close();

        // Asynchronous background call to process sync
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $syncUrl = (getenv('WEBSITE_URL') ?: ($protocol . "://" . $_SERVER['SERVER_NAME'])) . '/user/anilist/sync?ajax=1';
        $ch = curl_init($syncUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_NOSIGNAL => 1
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('continue-watch/log error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
} finally {
    $conn->close();
}

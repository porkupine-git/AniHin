<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
header('Content-Type: application/json');

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
if ($userId <= 0) {
    echo json_encode(['watching' => false]);
    exit;
}

$liveWatching = null;
$stmtLive = $conn->prepare("
    SELECT lw.*, ta.title, ta.poster 
    FROM live_watching lw 
    INNER JOIN users u ON lw.user_id = u.id
    LEFT JOIN tracked_anime ta ON lw.anime_id = ta.anime_id 
    WHERE lw.user_id = ? AND u.live_status = 1 AND lw.updated_at >= NOW() - INTERVAL 30 SECOND
    LIMIT 1
");

if ($stmtLive) {
    $stmtLive->bind_param("i", $userId);
    $stmtLive->execute();
    $liveWatching = $stmtLive->get_result()->fetch_assoc();
    $stmtLive->close();

    if ($liveWatching) {
        $liveTitle = !empty($liveWatching['title']) ? $liveWatching['title'] : ucwords(str_replace('-', ' ', $liveWatching['anime_id']));
        $livePoster = !empty($liveWatching['poster']) ? $liveWatching['poster'] : '/images/no_poster.jpg';
        $liveProgress = (int)$liveWatching['progress'];
        $liveDuration = (int)$liveWatching['duration'];
        $livePercent = $liveDuration > 0 ? ($liveProgress / $liveDuration) * 100 : 0;

        echo json_encode([
            'watching' => true,
            'anime_id' => $liveWatching['anime_id'],
            'episode_id' => $liveWatching['episode_id'],
            'episode_number' => $liveWatching['episode_number'],
            'progress' => $liveProgress,
            'duration' => $liveDuration,
            'percent' => $livePercent,
            'title' => $liveTitle,
            'poster' => $livePoster
        ]);
        exit;
    }
}

echo json_encode(['watching' => false]);
exit;

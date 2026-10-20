<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/component/anime/qtip.php');
header("Content-Type: application/json; charset=utf-8");

$userId = $_SESSION['userID'] ?? null;
if (!$userId) {
    echo json_encode(['status' => false, 'msg' => 'Unauthorized']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$clientList = $data['lists'] ?? [];
$completedTasks = $data['completed_tasks'] ?? [];

// Helper: Resolve AniList ID for a local anime slug/ID
function resolveAniListId($animeSlug, $conn) {
    if (empty($animeSlug)) return null;

    if (ctype_digit((string)$animeSlug)) {
        return (int)$animeSlug;
    }

    $stmt = $conn->prepare("SELECT anilist_id FROM tracked_anime WHERE anime_id = ? LIMIT 1");
    $stmt->bind_param("s", $animeSlug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row && $row['anilist_id']) {
        return (int)$row['anilist_id'];
    }

    $animeData = fetchAnimeData($animeSlug);
    if ($animeData && isset($animeData['anilistId'])) {
        $anilistId = (int)$animeData['anilistId'];
        
        $stmt = $conn->prepare("
            INSERT INTO tracked_anime (anime_id, title, poster, anilist_id, last_checked)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE anilist_id = VALUES(anilist_id), last_checked = NOW()
        ");
        $stmt->bind_param("sssi", $animeSlug, $animeData['title'], $animeData['poster'], $anilistId);
        $stmt->execute();
        $stmt->close();
        return $anilistId;
    }
    return null;
}

// 1. Process completed queue tasks
if (!empty($completedTasks) && is_array($completedTasks)) {
    foreach ($completedTasks as $qId) {
        $stmt = $conn->prepare("UPDATE sync_queue SET status = 'completed' WHERE id = ? AND user_id = ?");
        $qIdInt = (int)$qId;
        $stmt->bind_param("ii", $qIdInt, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// Map status helpers
function mapAniListToLocalStatus($status) {
    return match (strtoupper($status ?? '')) {
        'CURRENT'   => 1,
        'PAUSED'    => 2,
        'PLANNING'  => 3,
        'DROPPED'   => 4,
        'COMPLETED' => 5,
        'REPEATING' => 1,
        default     => 3
    };
}

function mapLocalToAniListStatus($type) {
    return match ((int)$type) {
        1 => 'CURRENT',
        2 => 'PAUSED',
        3 => 'PLANNING',
        4 => 'DROPPED',
        5 => 'COMPLETED',
        default => 'PLANNING'
    };
}

$aniListMap = [];
foreach ($clientList as $entry) {
    if (isset($entry['mediaId'])) {
        $aniListMap[(int)$entry['mediaId']] = $entry;
    }
}

// Fetch local Watchlist
$localWatchlist = [];
$res = $conn->query("SELECT w.anime_id, w.type, w.score, w.notes, w.updated_at, t.anilist_id FROM watchlist w LEFT JOIN tracked_anime t ON w.anime_id = t.anime_id WHERE w.user_id = " . (int)$userId);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $localWatchlist[] = $row;
    }
}

// Resolve any missing AniList IDs in local watchlist
foreach ($localWatchlist as &$item) {
    if (!$item['anilist_id']) {
        $anilistId = resolveAniListId($item['anime_id'], $conn);
        if ($anilistId) {
            $item['anilist_id'] = $anilistId;
        }
    }
}
unset($item);

$needs_push = [];
$processedAniListIds = [];
$pulledCount = 0;

// 2. Perform comparison and synchronization for existing local items
foreach ($localWatchlist as $local) {
    $anilistId = $local['anilist_id'] ? (int)$local['anilist_id'] : null;
    if (!$anilistId) continue;

    $processedAniListIds[] = $anilistId;

    if (isset($aniListMap[$anilistId])) {
        $alEntry = $aniListMap[$anilistId];
        $alUpdated = (int)($alEntry['updatedAt'] ?? 0);
        $locUpdated = strtotime($local['updated_at'] ?? '1970-01-01');

        if ($locUpdated > $alUpdated + 10) {
            // Local is newer. Push to AniList (collect for client-side execution from user IP)
            $stmtHist = $conn->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ? ORDER BY episode_number DESC LIMIT 1");
            $stmtHist->bind_param("is", $userId, $local['anime_id']);
            $stmtHist->execute();
            $histRow = $stmtHist->get_result()->fetch_assoc();
            $stmtHist->close();
            $progress = $histRow ? (int)$histRow['episode_number'] : 0;

            $needs_push[] = [
                'mediaId'  => $anilistId,
                'status'   => mapLocalToAniListStatus($local['type']),
                'progress' => $progress,
                'score'    => (float)($local['score'] ?? 0),
                'notes'    => $local['notes'] ?? ''
            ];
        } elseif ($alUpdated > $locUpdated + 10) {
            // AniList is newer. Pull to local database.
            $newType = mapAniListToLocalStatus($alEntry['status'] ?? '');
            $score = (int)($alEntry['score'] ?? 0);
            $notes = $alEntry['notes'] ?? '';
            $alProgress = (int)($alEntry['progress'] ?? 0);

            $stmtUpd = $conn->prepare("UPDATE watchlist SET type = ?, score = ?, notes = ?, updated_at = FROM_UNIXTIME(?) WHERE user_id = ? AND anime_id = ?");
            $stmtUpd->bind_param("iissis", $newType, $score, $notes, $alUpdated, $userId, $local['anime_id']);
            $stmtUpd->execute();
            $stmtUpd->close();

            if ($alProgress > 0) {
                $stmtProg = $conn->prepare("
                    INSERT INTO watch_history (user_id, anime_id, episode_number, progress_percent, created_at, updated_at)
                    VALUES (?, ?, ?, 100, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
                    ON DUPLICATE KEY UPDATE episode_number = VALUES(episode_number), progress_percent = 100, updated_at = VALUES(updated_at)
                ");
                $stmtProg->bind_param("isiii", $userId, $local['anime_id'], $alProgress, $alUpdated, $alUpdated);
                $stmtProg->execute();
                $stmtProg->close();
            }
            $pulledCount++;
        }
    } else {
        // Local watchlist entry doesn't exist on AniList. Push it.
        $stmtHist = $conn->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ? ORDER BY episode_number DESC LIMIT 1");
        $stmtHist->bind_param("is", $userId, $local['anime_id']);
        $stmtHist->execute();
        $histRow = $stmtHist->get_result()->fetch_assoc();
        $stmtHist->close();
        $progress = $histRow ? (int)$histRow['episode_number'] : 0;

        $needs_push[] = [
            'mediaId'  => $anilistId,
            'status'   => mapLocalToAniListStatus($local['type']),
            'progress' => $progress,
            'score'    => (float)($local['score'] ?? 0),
            'notes'    => $local['notes'] ?? ''
        ];
    }
}

// 3. Pull missing AniList items (entries on AniList not yet in local watchlist)
foreach ($aniListMap as $anilistId => $alEntry) {
    if (in_array($anilistId, $processedAniListIds, true)) continue;

    $stmt = $conn->prepare("SELECT anime_id FROM tracked_anime WHERE anilist_id = ? LIMIT 1");
    $stmt->bind_param("i", $anilistId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $animeSlug = $row ? $row['anime_id'] : null;

    if (!$animeSlug) {
        $animeData = fetchAnimeData($anilistId);
        $animeSlug = (string)$anilistId;
        if ($animeData && !empty($animeData['title'])) {
            $stmtTrack = $conn->prepare("
                INSERT INTO tracked_anime (anime_id, title, poster, anilist_id, last_checked)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE title = VALUES(title), poster = VALUES(poster), last_checked = NOW()
            ");
            $stmtTrack->bind_param("sssi", $animeSlug, $animeData['title'], $animeData['poster'], $anilistId);
            $stmtTrack->execute();
            $stmtTrack->close();
        }
    }

    if ($animeSlug) {
        $newType = mapAniListToLocalStatus($alEntry['status'] ?? '');
        $score = (int)($alEntry['score'] ?? 0);
        $notes = $alEntry['notes'] ?? '';
        $alUpdated = (int)($alEntry['updatedAt'] ?? time());
        $alProgress = (int)($alEntry['progress'] ?? 0);

        $stmtIns = $conn->prepare("
            INSERT INTO watchlist (user_id, anime_id, type, score, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
            ON DUPLICATE KEY UPDATE type = VALUES(type), score = VALUES(score), notes = VALUES(notes), updated_at = VALUES(updated_at)
        ");
        $stmtIns->bind_param("isiisii", $userId, $animeSlug, $newType, $score, $notes, $alUpdated, $alUpdated);
        $stmtIns->execute();
        $stmtIns->close();

        if ($alProgress > 0) {
            $stmtProg = $conn->prepare("
                INSERT INTO watch_history (user_id, anime_id, episode_number, progress_percent, created_at, updated_at)
                VALUES (?, ?, ?, 100, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
                ON DUPLICATE KEY UPDATE episode_number = VALUES(episode_number), progress_percent = 100, updated_at = VALUES(updated_at)
            ");
            $stmtProg->bind_param("isiii", $userId, $animeSlug, $alProgress, $alUpdated, $alUpdated);
            $stmtProg->execute();
            $stmtProg->close();
        }
        $pulledCount++;
    }
}

// 4. Fetch pending queue tasks to process client-side from user IP
$queueRes = $conn->query("SELECT * FROM sync_queue WHERE user_id = " . (int)$userId . " AND status = 'pending' LIMIT 15");
if ($queueRes && $queueRes->num_rows > 0) {
    while ($task = $queueRes->fetch_assoc()) {
        $qAnilistId = resolveAniListId($task['anime_id'], $conn);
        if (!$qAnilistId) {
            $conn->query("UPDATE sync_queue SET status = 'failed', last_error = 'Failed to resolve AniList ID' WHERE id = " . (int)$task['id']);
            continue;
        }

        $needs_push[] = [
            'mediaId'  => $qAnilistId,
            'status'   => mapLocalToAniListStatus($task['type']),
            'progress' => (int)$task['progress'],
            'score'    => (float)$task['score'],
            'notes'    => $task['notes'] ?? null,
            'queueId'  => (int)$task['id']
        ];
    }
}

echo json_encode([
    'status' => true,
    'pulled' => $pulledCount,
    'pushed' => count($needs_push),
    'needs_push' => $needs_push,
    'msg' => "Synchronization successful! Pulled $pulledCount items."
]);
exit;

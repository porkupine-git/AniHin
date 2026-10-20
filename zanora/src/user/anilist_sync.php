<?php
// anilist_sync.php - Dynamic two-way AniList synchronizer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/component/anime/qtip.php');

$userId = $_SESSION['userID'] ?? null;
if (!$userId) {
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'msg' => 'Unauthorized']);
    exit;
}

// 1. Fetch user tokens
$stmt = $conn->prepare("SELECT anilist_id, anilist_token, anilist_token_expires, anilist_refresh_token FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userTokens = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userTokens || !$userTokens['anilist_token']) {
    if (isset($internal_sync) && $internal_sync) {
        return;
    }
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'msg' => 'AniList account not linked.']);
        exit;
    }
    header("Location: /user/connections");
    exit;
}

$accessToken = $userTokens['anilist_token'];
$anilistUserId = $userTokens['anilist_id'];

// 2. Token refresh mechanism (if expired)
if (!empty($userTokens['anilist_refresh_token']) && !empty($userTokens['anilist_token_expires']) && $userTokens['anilist_token_expires'] < time()) {
    $clientId = getenv('ANILIST_CLIENT_ID');
    $clientSecret = getenv('ANILIST_CLIENT_SECRET');
    $ch = curl_init("https://anilist.co/api/v2/oauth/token");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type'    => 'refresh_token',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $userTokens['anilist_refresh_token']
        ]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 15
    ]);
    $refreshResponse = curl_exec($ch);
    $refreshHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($refreshHttpCode === 200 && $refreshResponse) {
        $refreshData = json_decode($refreshResponse, true);
        $accessToken = $refreshData['access_token'] ?? $accessToken;
        $newRefreshToken = $refreshData['refresh_token'] ?? $userTokens['anilist_refresh_token'];
        $newExpiresAt = time() + ($refreshData['expires_in'] ?? 0);

        $stmt = $conn->prepare("UPDATE users SET anilist_token = ?, anilist_refresh_token = ?, anilist_token_expires = ? WHERE id = ?");
        $stmt->bind_param("ssii", $accessToken, $newRefreshToken, $newExpiresAt, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// 3. Status mapping helpers
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

function mapAniListToLocalStatus($status) {
    return match (strtoupper($status)) {
        'CURRENT'   => 1,
        'PAUSED'    => 2,
        'PLANNING'  => 3,
        'DROPPED'   => 4,
        'COMPLETED' => 5,
        'REPEATING' => 1,
        default     => 3
    };
}

// Helper: Query AniList GraphQL API
function runAniListGraphQL($query, $variables, $token) {
    $ch = curl_init("https://graphql.anilist.co");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $query, 'variables' => $variables]),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT        => 20
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 429) {
        // Rate limit exceeded
        return ['error' => 'rate_limit', 'retry_after' => 60];
    }

    if ($httpCode !== 200 || !$res) {
        return ['error' => 'network_error', 'details' => $error];
    }

    return json_decode($res, true);
}

// Helper: Resolve AniList ID for a local anime slug
function resolveAniListId($animeSlug, $conn) {
    // Check tracked_anime
    $stmt = $conn->prepare("SELECT anilist_id FROM tracked_anime WHERE anime_id = ? LIMIT 1");
    $stmt->bind_param("s", $animeSlug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row && $row['anilist_id']) {
        return (int)$row['anilist_id'];
    }

    // Call API / scrape
    $animeData = fetchAnimeData($animeSlug);
    if ($animeData && isset($animeData['anilistId'])) {
        $anilistId = (int)$animeData['anilistId'];
        
        // Save back
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

// 4. Fetch AniList collection
$graphqlQuery = '
query ($userId: Int) {
  MediaListCollection(userId: $userId, type: ANIME) {
    lists {
      entries {
        mediaId
        status
        progress
        score(format: POINT_10)
        notes
        updatedAt
      }
    }
  }
}';

$aniListCollection = runAniListGraphQL($graphqlQuery, ['userId' => $anilistUserId], $accessToken);

if (isset($aniListCollection['error'])) {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'msg' => 'AniList API rate limit or network error. Please try again.']);
        exit;
    }
    die("AniList Sync Error: " . $aniListCollection['error']);
}

// Flatten AniList entries
$aniListMap = [];
$lists = $aniListCollection['data']['MediaListCollection']['lists'] ?? [];
foreach ($lists as $list) {
    $entries = $list['entries'] ?? [];
    foreach ($entries as $entry) {
        $aniListMap[(int)$entry['mediaId']] = $entry;
    }
}

// Fetch local Watchlist
$localWatchlist = [];
$res = $conn->query("SELECT w.anime_id, w.type, w.score, w.notes, w.updated_at, t.anilist_id FROM watchlist w LEFT JOIN tracked_anime t ON w.anime_id = t.anime_id WHERE w.user_id = " . (int)$userId);
while ($row = $res->fetch_assoc()) {
    $localWatchlist[] = $row;
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

// 5. Two-Way Sync Logic
$pushed = 0;
$pulled = 0;

$processedAniListIds = [];

// Mutation for saving list entry on AniList
$saveMutation = '
mutation ($mediaId: Int, $status: MediaListStatus, $progress: Int, $score: Float, $notes: String) {
  SaveMediaListEntry(mediaId: $mediaId, status: $status, progress: $progress, score: $score, notes: $notes) {
    id
    status
    progress
  }
}';

foreach ($localWatchlist as $local) {
    $anilistId = $local['anilist_id'] ? (int)$local['anilist_id'] : null;
    if (!$anilistId) continue;

    $processedAniListIds[] = $anilistId;

    if (isset($aniListMap[$anilistId])) {
        // Entry exists on both. Compare update times.
        $alEntry = $aniListMap[$anilistId];
        $alUpdated = (int)$alEntry['updatedAt'];
        $locUpdated = strtotime($local['updated_at']);

        if ($locUpdated > $alUpdated + 10) {
            // Local is newer. Push to AniList.
            $status = mapLocalToAniListStatus($local['type']);
            // Get episode progress from history
            $stmtHist = $conn->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ? ORDER BY episode_number DESC LIMIT 1");
            $stmtHist->bind_param("is", $userId, $local['anime_id']);
            $stmtHist->execute();
            $histRow = $stmtHist->get_result()->fetch_assoc();
            $stmtHist->close();
            $progress = $histRow ? (int)$histRow['episode_number'] : 0;

            $res = runAniListGraphQL($saveMutation, [
                'mediaId'  => $anilistId,
                'status'   => $status,
                'progress' => $progress,
                'score'    => (float)$local['score'],
                'notes'    => $local['notes']
            ], $accessToken);
            if (isset($res['error']) && $res['error'] === 'rate_limit') {
                break;
            }
            $pushed++;
            usleep(300000); // 300ms delay to prevent rate limits
        } elseif ($alUpdated > $locUpdated + 10) {
            // AniList is newer. Pull to local.
            $newType = mapAniListToLocalStatus($alEntry['status']);
            $score = (int)$alEntry['score'];
            $notes = $alEntry['notes'];
            $alProgress = (int)$alEntry['progress'];

            // Update local watchlist
            $stmtUpd = $conn->prepare("UPDATE watchlist SET type = ?, score = ?, notes = ?, updated_at = FROM_UNIXTIME(?) WHERE user_id = ? AND anime_id = ?");
            $stmtUpd->bind_param("iissis", $newType, $score, $notes, $alUpdated, $userId, $local['anime_id']);
            $stmtUpd->execute();
            $stmtUpd->close();

            // Update local progress/history
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
            $pulled++;
        }
    } else {
        // Local watchlist entry doesn't exist on AniList. Push it.
        $status = mapLocalToAniListStatus($local['type']);
        
        $stmtHist = $conn->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ? ORDER BY episode_number DESC LIMIT 1");
        $stmtHist->bind_param("is", $userId, $local['anime_id']);
        $stmtHist->execute();
        $histRow = $stmtHist->get_result()->fetch_assoc();
        $stmtHist->close();
        $progress = $histRow ? (int)$histRow['episode_number'] : 0;

        $res = runAniListGraphQL($saveMutation, [
            'mediaId'  => $anilistId,
            'status'   => $status,
            'progress' => $progress,
            'score'    => (float)$local['score'],
            'notes'    => $local['notes']
        ], $accessToken);
        if (isset($res['error']) && $res['error'] === 'rate_limit') {
            break;
        }
        $pushed++;
        usleep(300000); // 300ms delay to prevent rate limits
    }
}

// 6. Pull missing AniList items (entries on AniList but not local)
foreach ($aniListMap as $anilistId => $alEntry) {
    if (in_array($anilistId, $processedAniListIds, true)) continue;

    // We need to resolve local slug by looking up tracked_anime, or scrape
    $stmt = $conn->prepare("SELECT anime_id FROM tracked_anime WHERE anilist_id = ? LIMIT 1");
    $stmt->bind_param("i", $anilistId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $animeSlug = $row ? $row['anime_id'] : null;

    if (!$animeSlug) {
        // Scrape or fetch details from Jikan/MAL/Vercel API via GraphQL mapping
        // We'll search by AniList ID via backend API info route:
        $infoUrl = rtrim($zpi, '/') . '/info?id=' . $anilistId;
        $ch = curl_init($infoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10
        ]);
        $infoRes = curl_exec($ch);
        curl_close($ch);

        if ($infoRes) {
            $infoData = json_decode($infoRes, true);
            if (!empty($infoData['success']) && !empty($infoData['results']['data'])) {
                // Resolved slug!
                $animeSlug = $infoData['results']['data']['data_id'] ?? $infoData['results']['data']['id'];
                
                // Save to tracked_anime
                $title = $infoData['results']['data']['title'] ?? '';
                $poster = $infoData['results']['data']['poster'] ?? '';
                $stmtIns = $conn->prepare("INSERT INTO tracked_anime (anime_id, title, poster, anilist_id, last_checked) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE anilist_id = VALUES(anilist_id)");
                $stmtIns->bind_param("sssi", $animeSlug, $title, $poster, $anilistId);
                $stmtIns->execute();
                $stmtIns->close();
            }
        }
    }

    if ($animeSlug) {
        $newType = mapAniListToLocalStatus($alEntry['status']);
        $score = (int)$alEntry['score'];
        $notes = $alEntry['notes'];
        $alUpdated = (int)$alEntry['updatedAt'];
        $alProgress = (int)$alEntry['progress'];

        // Insert into local watchlist
        $stmtIns = $conn->prepare("
            INSERT INTO watchlist (user_id, anime_id, type, score, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
            ON DUPLICATE KEY UPDATE type = VALUES(type), score = VALUES(score), notes = VALUES(notes), updated_at = VALUES(updated_at)
        ");
        $stmtIns->bind_param("isiisss", $userId, $animeSlug, $newType, $score, $notes, $alUpdated, $alUpdated);
        $stmtIns->execute();
        $stmtIns->close();

        // Sync local progress
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
        $pulled++;
    }
}

// 7. Handle sync queue tasks (if any failed previously)
$queueRes = $conn->query("SELECT * FROM sync_queue WHERE user_id = " . (int)$userId . " AND status = 'pending' LIMIT 10");
if ($queueRes && $queueRes->num_rows > 0) {
    while ($task = $queueRes->fetch_assoc()) {
        $qAnilistId = resolveAniListId($task['anime_id'], $conn);
        if (!$qAnilistId) {
            $conn->query("UPDATE sync_queue SET status = 'failed', last_error = 'Failed to resolve AniList ID' WHERE id = " . (int)$task['id']);
            continue;
        }

        $qStatus = mapLocalToAniListStatus($task['type']);
        $syncResult = runAniListGraphQL($saveMutation, [
            'mediaId'  => $qAnilistId,
            'status'   => $qStatus,
            'progress' => (int)$task['progress'],
            'score'    => (float)$task['score'],
            'notes'    => $task['notes'] ?? null
        ], $accessToken);

        if (isset($syncResult['error'])) {
            if ($syncResult['error'] === 'rate_limit') {
                break; // Stop execution on rate limit
            }
            $errCount = (int)$task['error_count'] + 1;
            $status = ($errCount >= 5) ? 'failed' : 'pending';
            $stmtUp = $conn->prepare("UPDATE sync_queue SET error_count = ?, status = ?, last_error = ? WHERE id = ?");
            $errDetails = $syncResult['error'];
            $stmtUp->bind_param("issi", $errCount, $status, $errDetails, $task['id']);
            $stmtUp->execute();
            $stmtUp->close();
        } else {
            $conn->query("UPDATE sync_queue SET status = 'completed' WHERE id = " . (int)$task['id']);
        }
        usleep(300000); // 300ms delay to prevent rate limits
    }
}

// If internal sync, return without logging or echoing
if (isset($internal_sync) && $internal_sync) {
    return;
}

// Write to Audit Logs
$logAction = "Completed two-way AniList synchronization. Pushed: $pushed, Pulled: $pulled.";
$ip = getClientIP();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$stmtLog = $conn->prepare("INSERT INTO audit_logs (user_id, username, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
$username = $_SESSION['username'] ?? 'System';
$stmtLog->bind_param("issss", $userId, $username, $logAction, $ip, $ua);
$stmtLog->execute();
$stmtLog->close();

header('Content-Type: application/json');
echo json_encode([
    'status' => true,
    'msg' => "Sync complete! Pushed $pushed entries to AniList and pulled $pulled entries to local.",
    'details' => ['pushed' => $pushed, 'pulled' => $pulled]
]);
exit;
?>

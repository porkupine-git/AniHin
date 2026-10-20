<?php
// /src/lib/mal_cache.php

function fetchMalStatsFromApi(int $malId): array {
    if ($malId <= 0) {
        return ['score' => null, 'members' => 0];
    }

    $url = 'https://api.jikan.moe/v4/anime/' . $malId;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'YourSiteBot/1.0',
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    $score   = null;
    $members = 0;

    if ($resp) {
        $json = json_decode($resp, true);
        if (!empty($json['data'])) {
            $d = $json['data'];
            if (isset($d['score']) && $d['score'] !== null) {
                $score = (float)$d['score'];
            }
            if (isset($d['members'])) {
                $members = (int)$d['members'];
            }
        }
    }

    return ['score' => $score, 'members' => $members];
}

/**
 * Ensure mal_score/mal_members are fresh (24h cache).
 * Returns array: ['score' => float|null, 'members' => int]
 */
function ensureMalStatsCached(mysqli $conn, string $animeId): array {
    // 1) load from DB
    $stmt = $conn->prepare("
        SELECT id, mal_id, mal_score, mal_members, mal_last_sync
        FROM tracked_anime
        WHERE anime_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return ['score' => null, 'members' => 0];
    }

    $stmt->bind_param("s", $animeId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return ['score' => null, 'members' => 0];
    }

    $id          = (int)$row['id'];
    $malId       = (int)($row['mal_id'] ?? 0);
    $dbScore     = $row['mal_score'];
    $dbMembers   = (int)($row['mal_members'] ?? 0);
    $lastSyncStr = $row['mal_last_sync'];

    // 2) check 24h freshness
    $needUpdate = false;
    if ($malId <= 0) {
        // no MAL id → cannot fetch
        return ['score' => null, 'members' => 0];
    }

    if ($lastSyncStr === null) {
        $needUpdate = true;
    } else {
        $lastSync = strtotime($lastSyncStr);
        if ($lastSync === false || (time() - $lastSync) >= 86400) {
            $needUpdate = true;
        }
    }

    if ($needUpdate) {
        // 3) fetch from API
        $stats = fetchMalStatsFromApi($malId);
        $newScore   = $stats['score'];
        $newMembers = $stats['members'];

        // 4) save back to DB
        $stmt2 = $conn->prepare("
            UPDATE tracked_anime
            SET mal_score = ?, mal_members = ?, mal_last_sync = NOW()
            WHERE id = ?
        ");
        if ($stmt2) {
            // mal_score is DECIMAL so use double or string
            if ($newScore === null) {
                // bind as null via tricks: use string & allow null, or cast 0 if you prefer
                $scoreParam = null;
            } else {
                $scoreParam = $newScore;
            }

            $stmt2->bind_param("dii", $scoreParam, $newMembers, $id);
            // If null binding complains, you can separate null case and run a second query.
            $stmt2->execute();
            $stmt2->close();
        }

        return [
            'score'   => $newScore,
            'members' => $newMembers,
        ];
    }

    // 5) still fresh → use DB values
    return [
        'score'   => $dbScore !== null ? (float)$dbScore : null,
        'members' => $dbMembers,
    ];
}

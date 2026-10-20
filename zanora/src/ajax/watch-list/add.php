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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
header("Content-Type: application/json; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/component/anime/qtip.php');

function resolveAniListId($animeSlug, $conn) {
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

function getAniListSyncPayload($userId, $movieId, $type, $progress, $score, $notes, $conn) {
    $stmt = $conn->prepare("SELECT anilist_token FROM users WHERE id = ? AND anilist_token IS NOT NULL LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) return null;

    $anilistId = resolveAniListId($movieId, $conn);
    if (!$anilistId) return null;

    $statusMap = [0=>'PLANNING', 1=>'CURRENT', 2=>'PAUSED', 3=>'PLANNING', 4=>'DROPPED', 5=>'COMPLETED'];
    $status = $statusMap[(int)$type] ?? 'PLANNING';

    return [
        'mediaId'  => $anilistId,
        'status'   => $status,
        'progress' => $progress,
        'score'    => (float)$score,
        'notes'    => $notes,
        'token'    => $user['anilist_token']
    ];
}

// Auth (same as before)
if (!isset($_SESSION['userID'])) {
    if (isset($_COOKIE['userToken'])) {
        $token = $_COOKIE['userToken'];
        $stmt = $conn->prepare("SELECT id FROM users WHERE login_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $_SESSION['userID'] = $user['id'];
        }
        $stmt->close();
    }
}
if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => false, 'msg' => 'Unauthorized']);
    exit;
}

// Input handling
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data) || empty($data)) {
    $data = $_POST ?: $_GET;
}
$type     = isset($data['type']) ? (int)$data['type'] : 3;
$movieId  = $data['movieId'] ?? null;  // full slug
$page     = $data['page']    ?? '';
if ($movieId === null) {
    echo json_encode(['status' => false, 'msg' => 'Invalid input']);
    exit;
}
$userId = (int)$_SESSION['userID'];

// Helper: QTIP HTML (HiAnime-style)
function renderQtipHtml($movieId, $isAdded, $currentType) {
    ob_start(); ?>
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-block btn-fav">
        <i class="fas <?= $isAdded ? 'fa-check' : 'fa-plus' ?>"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dropqtip" aria-labelledby="ssc-list">
        <?php
        $statuses = [1=>'Watching',2=>'On-Hold',3=>'Plan to Watch',4=>'Dropped',5=>'Completed'];
        foreach ($statuses as $st => $label): ?>
            <a class="wl-item dropdown-item<?= ($isAdded && (int)$st === (int)$currentType ? ' added' : '') ?>"
               data-type="<?= (int)$st ?>"
               data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
               data-page="qtip"
               href="javascript:;">
               <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?= ($isAdded && (int)$st === (int)$currentType ? ' <i class="fas fa-check ml-2"></i>' : '') ?>
            </a>
        <?php endforeach; ?>
        <?php if ($isAdded): ?>
            <a class="wl-item dropdown-item text-danger" data-type="0" data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-page="qtip" href="javascript:;">Remove</a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Helper: DETAIL HTML (details page)
function renderDetailHtml($movieId, $isAdded, $currentType) {
    ob_start(); ?>
    <div class="dr-fav" id="watch-list-content">
        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-radius btn-light">
            <?php if ($isAdded): ?>
                <i class="fas fa-edit mr-2"></i>Edit Watch List
            <?php else: ?>
                <i class="fas fa-plus mr-2"></i>Add to List
            <?php endif; ?>
        </a>

        <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
            <?php
            $statuses = [1=>'Watching',2=>'On-Hold',3=>'Plan to Watch',4=>'Dropped',5=>'Completed'];
            foreach ($statuses as $st => $label): ?>
                <a class="wl-item dropdown-item<?= ($isAdded && (int)$st === (int)$currentType ? ' added' : '') ?>"
                   data-type="<?= (int)$st ?>"
                   data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                   data-page="detail"
                   href="javascript:;">
                   <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?= ($isAdded && (int)$st === (int)$currentType ? ' <i class="fas fa-check ml-2"></i>' : '') ?>
                </a>
            <?php endforeach; ?>

            <?php if ($isAdded): ?>
                <a class="wl-item dropdown-item text-danger" data-type="0" data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-page="detail" href="javascript:;">
                    <i class="fas fa-trash mr-2"></i>Remove
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// NEW: WATCH HTML (watch page)
function renderWatchHtml($movieId, $isAdded, $currentType) {
    ob_start(); ?>
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-sm">
        <?php if ($isAdded): ?>
            <i class="fas fa-edit mr-2"></i>Edit Watch List
        <?php else: ?>
            <i class="fas fa-plus mr-2"></i>Add to List
        <?php endif; ?>
    </a>

<div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
    <?php
    $statuses = [1=>'Watching',2=>'On-Hold',3=>'Plan to Watch',4=>'Dropped',5=>'Completed'];
    foreach ($statuses as $st => $label): ?>
        <a class="wl-item dropdown-item<?= ($isAdded && (int)$st === (int)$currentType ? ' added' : '') ?>"
           data-type="<?= (int)$st ?>"
           data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
           data-page="watch"
           href="javascript:;"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?= ($isAdded && (int)$st === (int)$currentType ? '<i class="fas fa-check ml-2"></i>' : '') ?>

        </a>
    <?php endforeach; ?>

    <?php if ($isAdded): ?>
        <a class="wl-item dropdown-item text-danger"
           data-page="watch"
           data-movieid="<?= htmlspecialchars($movieId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
           href="javascript:;"
           data-type="0">Remove</a>
    <?php endif; ?>
</div>

<?php
    return ob_get_clean();
}

try {
    // REMOVE
    if ((int)$type === 0) {
        $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND anime_id = ?");
        $stmt->bind_param('is', $userId, $movieId);
        $stmt->execute();
        $stmt->close();

        // Hook: AniList remove sync queue
        $checkLinked = $conn->prepare("SELECT anilist_token FROM users WHERE id = ? AND anilist_token IS NOT NULL LIMIT 1");
        $checkLinked->bind_param("i", $userId);
        $checkLinked->execute();
        $isLinked = $checkLinked->get_result()->fetch_assoc();
        $checkLinked->close();

        $syncPayload = null;
        if ($isLinked) {
            $qStatus = 'pending';
            $syncSql = "
                INSERT INTO sync_queue (user_id, anime_id, type, episodes_watched, score, progress, status, created_at, updated_at)
                VALUES (?, ?, 0, 0, 0, 0, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    type = 0, 
                    status = 'pending',
                    error_count = 0,
                    updated_at = NOW()
            ";
            $syncStmt = $conn->prepare($syncSql);
            $syncStmt->bind_param("iss", $userId, $movieId, $qStatus);
            $syncStmt->execute();
            $syncStmt->close();

            $syncPayload = getAniListSyncPayload($userId, $movieId, 0, 0, 0, null, $conn);
        }

        // If page requested, return matching html
        if ($page === 'qtip' || $page === 'detail' || $page === 'watch') {
            if ($page === 'qtip') {
                $html = renderQtipHtml($movieId, false, null);
            } elseif ($page === 'detail') {
                $html = renderDetailHtml($movieId, false, null);
            } else { // watch
                $html = renderWatchHtml($movieId, false, null);
            }
            echo json_encode(['status'=>true,'msg'=>'Removed from watchlist','html'=>$html,'sync_payload'=>$syncPayload]);
            exit;
        }

        echo json_encode(['status'=>true,'msg'=>'Removed from watchlist','sync_payload'=>$syncPayload]);
        exit;
    }

    // INSERT / UPDATE
    $check = $conn->prepare("SELECT type FROM watchlist WHERE user_id = ? AND anime_id = ? LIMIT 1");
    $check->bind_param("is", $userId, $movieId);
    $check->execute();
    $existingRow = $check->get_result()->fetch_assoc();
    $check->close();

    $sql = "INSERT INTO watchlist (user_id, anime_id, type, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE type = ?, updated_at = NOW()";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param('isis', $userId, $movieId, $type, $type);
    $stmt->execute();
    $stmt->close();

    // Hook: AniList add/update sync queue
    $checkLinked = $conn->prepare("SELECT anilist_token FROM users WHERE id = ? AND anilist_token IS NOT NULL LIMIT 1");
    $checkLinked->bind_param("i", $userId);
    $checkLinked->execute();
    $isLinked = $checkLinked->get_result()->fetch_assoc();
    $checkLinked->close();

    $syncPayload = null;
    if ($isLinked) {
        // Fetch current progress
        $stmtHist = $conn->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ? ORDER BY episode_number DESC LIMIT 1");
        $stmtHist->bind_param("is", $userId, $movieId);
        $stmtHist->execute();
        $histRow = $stmtHist->get_result()->fetch_assoc();
        $stmtHist->close();
        $progress = $histRow ? (int)$histRow['episode_number'] : 0;

        // Fetch score and notes from watchlist to include in payload
        $stmtWatch = $conn->prepare("SELECT score, notes FROM watchlist WHERE user_id = ? AND anime_id = ? LIMIT 1");
        $stmtWatch->bind_param("is", $userId, $movieId);
        $stmtWatch->execute();
        $watchRow = $stmtWatch->get_result()->fetch_assoc();
        $stmtWatch->close();
        $score = $watchRow ? (float)$watchRow['score'] : 0.0;
        $notes = $watchRow ? $watchRow['notes'] : null;

        $qStatus = 'pending';
        $syncSql = "
            INSERT INTO sync_queue (user_id, anime_id, type, episodes_watched, score, progress, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 0, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                type = VALUES(type), 
                episodes_watched = VALUES(episodes_watched),
                progress = VALUES(progress),
                status = 'pending',
                error_count = 0,
                updated_at = NOW()
        ";
        $syncStmt = $conn->prepare($syncSql);
        $syncStmt->bind_param("isiiis", $userId, $movieId, $type, $progress, $progress, $qStatus);
        $syncStmt->execute();
        $syncStmt->close();

        $syncPayload = getAniListSyncPayload($userId, $movieId, $type, $progress, $score, $notes, $conn);
    }

    // Determine post-update state for rendering
    $isAdded = true;
    $currentType = (int)$type;

    if ($page === 'qtip' || $page === 'detail' || $page === 'watch') {
        if ($page === 'qtip') {
            $html = renderQtipHtml($movieId, $isAdded, $currentType);
        } elseif ($page === 'detail') {
            $html = renderDetailHtml($movieId, $isAdded, $currentType);
        } else { // watch
            $html = renderWatchHtml($movieId, $isAdded, $currentType);
        }
        echo json_encode(['status'=>true,'msg'=>'This anime has been added to Watch List.','html'=>$html,'sync_payload'=>$syncPayload]);
        exit;
    }

    echo json_encode(['status'=>true,'msg'=>'This anime has been added to Watch List.','sync_payload'=>$syncPayload]);
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => false, 'msg' => 'Error: ' . $e->getMessage()]);
    exit;
}

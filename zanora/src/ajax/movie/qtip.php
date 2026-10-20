<?php
// Block non-AJAX access
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

// Get id param
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    http_response_code(400);
    exit('<div class="pre-qtip-content">Invalid Anime ID</div>');
}

// ================== FETCH ANIME DATA WITH DB CACHE ==================
$cacheKey = 'qtip_' . $id;
$apiUrl   = rtrim($zpi, '/') . "/qtip/{$id}";

$data = getCachedApiJson($cacheKey, $apiUrl, 3600);

if (!$data || empty($data['success']) || empty($data['results'])) {
    http_response_code(404);
    exit('<div class="pre-qtip-content">Anime not found</div>');
}

$anime = $data['results'];

// Extract anime slug/id used in watchlist (same logic you used before)
$animeSlugId = '';
if (!empty($anime['watchLink'])) {
    $parts = explode('/', $anime['watchLink']);
    $animeSlugId = end($parts);
}

// Determine current user's watchlist status
$isInWatchlist = false;
$currentStatus = null;
$userId = $_SESSION['userID'] ?? null;

if ($userId && $animeSlugId) {
    try {
        // Detect binding type automatically (i = int, s = string)
        $bindType = is_numeric($animeSlugId) ? "ii" : "is";

        $stmt = $conn->prepare("SELECT `type` FROM `watchlist` WHERE user_id = ? AND anime_id = ? LIMIT 1");
        $stmt->bind_param($bindType, $userId, $animeSlugId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $currentStatus = (int)$row['type'];
            $isInWatchlist = $currentStatus > 0;
        }

        $stmt->close();
    } catch (Throwable $e) {
        error_log("QTIP Watchlist DB Error: " . $e->getMessage());
        $isInWatchlist = false;
        $currentStatus = null;
    }
}

// Limit description to 300 words
$description =
    trim($anime['description'] ?? '');

$words =
    preg_split('/\s+/', $description);

if (count($words) > 50) {

    $description =
        implode(
            ' ',
            array_slice($words, 0, 50)
        ) . '...';
}

// Helper: render watchlist dropdown HTML (same format wl-up.php will return)
function renderWatchlistHtml($movieId, $isInWatchlist, $currentStatus) {
    ob_start(); ?>
    <div class="dr-tip-fav" id="wl-qtip-<?= htmlspecialchars($movieId) ?>">
        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-block btn-fav">
            <i class="fas <?= $isInWatchlist ? 'fa-check' : 'fa-plus' ?>"></i>
        </a>

        <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dropqtip" aria-labelledby="ssc-list">
            <?php
            $statuses = [
                1 => "Watching",
                2 => "On-Hold",
                3 => "Plan to Watch",
                4 => "Dropped",
                5 => "Completed"
            ];
            foreach ($statuses as $type => $label): ?>
                <a class="wl-item dropdown-item<?= ($isInWatchlist && $type === $currentStatus ? ' added' : '') ?>"
                   data-type="<?= $type ?>"
                   data-movieid="<?= htmlspecialchars($movieId) ?>"
                   data-page="qtip"
                   href="javascript:;">
                    <?= htmlspecialchars($label) ?><?= ($isInWatchlist && $type === $currentStatus ? ' <i class="fas fa-check ml-2"></i>' : '') ?>
                </a>
            <?php endforeach; ?>

            <?php if ($isInWatchlist): ?>
                <a class="wl-item dropdown-item text-danger"
                   data-type="0"
                   data-movieid="<?= htmlspecialchars($movieId) ?>"
                   data-page="qtip"
                   href="javascript:;">Remove</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

$watchlistHtml = '';
if ($animeSlugId) {
    $watchlistHtml = renderWatchlistHtml($animeSlugId, $isInWatchlist, $currentStatus);
}
?>
<div class="pre-qtip-content">
    <div class="pre-qtip-title"><?= htmlspecialchars($anime['title'] ?? 'Unknown Title') ?></div>

    <div class="pre-qtip-detail">
        <span class="pqd-li mr-3">
            <i class="fas fa-star mr-1 text-warning"></i><?= htmlspecialchars($anime['rating'] ?? 'N/A') ?>
        </span>
        <span class="pqd-li">
            <div class="tick">
                <?php if (!empty($anime['quality'])): ?>
                    <div class="tick-item tick-quality"><?= htmlspecialchars($anime['quality']) ?></div>
                <?php endif; ?>
                <?php if (!empty($anime['subCount'])): ?>
                    <div class="tick-item tick-sub">
                        <i class="fas fa-closed-captioning mr-1"></i><?= htmlspecialchars($anime['subCount']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($anime['dubCount'])): ?>
                    <div class="tick-item tick-dub">
                        <i class="fas fa-microphone mr-1"></i><?= htmlspecialchars($anime['dubCount']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </span>

        <span class="pqd-li badge badge-quality float-right ml-2"><?= htmlspecialchars($anime['type'] ?? '') ?></span>
        <div class="clearfix"></div>
    </div>

    <div class="pre-qtip-description"><?= htmlspecialchars($description) ?></div>

    <?php if (!empty($anime['japaneseTitle'])): ?>
        <div class="pre-qtip-line"><span class="stick">Japanese:</span> <span class="stick-text"><?= htmlspecialchars($anime['japaneseTitle']) ?></span></div>
    <?php endif; ?>

    <?php if (!empty($anime['Synonyms'])): ?>
        <div class="pre-qtip-line"><span class="stick">Synonyms:</span> <span class="stick-text"><?= htmlspecialchars($anime['Synonyms']) ?></span></div>
    <?php endif; ?>

    <div class="pre-qtip-line"><span class="stick">Aired:</span> <span class="stick-text"><?= htmlspecialchars($anime['airedDate'] ?? 'N/A') ?></span></div>
    <div class="pre-qtip-line"><span class="stick">Status:</span> <span class="stick-text"><?= htmlspecialchars($anime['status'] ?? 'Unknown') ?></span></div>

    <?php if (!empty($anime['genres'])): ?>
        <div class="pre-qtip-line">
            <span class="stick">Genres:</span>
            <?php foreach ($anime['genres'] as $i => $genre): ?>
                <a href="/genre/<?= strtolower(str_replace(' ', '-', htmlspecialchars($genre))) ?>" title="<?= htmlspecialchars($genre) ?>">
                    <?= htmlspecialchars($genre) ?>
                </a><?= $i < count($anime['genres']) - 1 ? ', ' : '' ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="pre-qtip-button">
        <a href="<?= htmlspecialchars($anime['watchLink']) ?>" class="btn btn-block btn-play">
            <i class="fa fa-play mr-2"></i>Watch now
        </a>

        <?= $watchlistHtml ?>
    </div>
</div>

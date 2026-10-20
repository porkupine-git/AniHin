<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header("Content-Type: application/json; charset=utf-8");

// Ensure TTL defined
if (!defined('API_CACHE_TTL')) {
    define('API_CACHE_TTL', 600); // 10 minutes
}

/* -----------------------------
   Helpers
------------------------------*/
function esc($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Extracts the animeId (slug) from:
 *   /ajax/character/list/one-piece-100?page=2  => "one-piece-100"
 *   ?animeId=one-piece-100                      => "one-piece-100"
 */
function getAnimeIdFromRequest(): ?string {
    if (!empty($_GET['animeId'])) {
        $slug = trim((string)$_GET['animeId']);
        return preg_match('/^[A-Za-z0-9._-]+$/', $slug) ? $slug : null;
    }
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $seg  = explode('/', trim($path, '/'));
    $slug = end($seg);
    $slug = urldecode($slug);
    return ($slug && preg_match('/^[A-Za-z0-9._-]+$/', $slug)) ? $slug : null;
}

/** Build the character list HTML */
function renderCharactersHtml(string $animeId, array $data, int $currentPage, int $totalPages): string {
    ob_start(); ?>
<div class="bac-list-wrap mb-3" id="character-list">
    <?php foreach ($data as $row):
        $c = $row['character'] ?? null;
        if (!$c) continue;
        $charId   = esc($c['id'] ?? '');
        $charName = esc($c['name'] ?? '');
        $charCast = esc($c['cast'] ?? '');
        $charImg  = esc($c['poster'] ?? '');
        // voice actors list
        $vas = is_array($row['voiceActors'] ?? null) ? $row['voiceActors'] : [];
    ?>
    <div class="bac-item">
        <div class="per-info ltr">
            <a href="/character/<?= $charId ?>" class="pi-avatar">
                <img class="lazyload" data-src="<?= $charImg ?>" alt="<?= $charName ?>">
            </a>
            <div class="pi-detail">
                <h4 class="pi-name"><a href="/character/<?= $charId ?>"><?= $charName ?></a></h4>
                <?php if ($charCast !== ''): ?>
                    <span class="pi-cast"><?= $charCast ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($vas)): ?>
            <div class="per-info per-info-xx">
                <div class="pix-list">
                    <?php foreach ($vas as $va):
                        $vaId  = esc($va['id'] ?? '');
                        $vaImg = esc($va['poster'] ?? '');
                        $vaName= esc($va['name'] ?? '');
                        if ($vaId === '') continue;
                    ?>
                    <a href="/people/<?= $vaId ?>" data-toggle="tooltip" title="<?= $vaName ?>" class="pi-avatar">
                        <img class="lazyload" data-src="<?= $vaImg ?>" alt="<?= $vaName ?>">
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="clearfix"></div>
    </div>
    <?php endforeach; ?>
    <div class="clearfix"></div>
</div>

<div class="loading-relative" style="display: none;">
    <div class="loading">
        <div class="span1"></div>
        <div class="span2"></div>
        <div class="span3"></div>
    </div>
</div>

<?php
    // Will output nothing if total pages <= 1
    echo renderPagination($animeId, $currentPage, $totalPages);
    return ob_get_clean();
}

/** Build the pagination HTML (hidden when <= 1 page) */
function renderPagination(string $animeId, int $currentPage, int $totalPages): string {
    // Hide pagination entirely when there are no pages or only one page
    if ($totalPages <= 1) {
        return '';
    }

    // Normalize
    if ($currentPage < 1) $currentPage = 1;
    if ($currentPage > $totalPages) $currentPage = $totalPages;

    // compact window around current
    $window = 3;
    $start = max(1, $currentPage - $window + 1);
    $end   = min($totalPages, max($start, $currentPage + $window - 1));

    ob_start(); ?>
<div class="pre-pagination">
    <nav aria-label="Page navigation">
        <ul class="pagination mb-0">
            <?php if ($currentPage > 1):
                $prev = $currentPage - 1; ?>
                <li class="page-item">
                    <a title="First" href="javascript:;" class="page-link"
                       onclick="paginationGetData(this)"
                       data-container="#characters-content"
                       data-url="/ajax/character/list/<?= esc($animeId) ?>?page=1">&laquo;</a>
                </li>
                <li class="page-item">
                    <a title="Previous" href="javascript:;" class="page-link"
                       onclick="paginationGetData(this)"
                       data-container="#characters-content"
                       data-url="/ajax/character/list/<?= esc($animeId) ?>?page=<?= $prev ?>">&rsaquo;</a>
                </li>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <?php if ($i === $currentPage): ?>
                        <a href="javascript:;" class="page-link"><?= $i ?></a>
                    <?php else: ?>
                        <a title="Page <?= $i ?>" href="javascript:;" class="page-link"
                           onclick="paginationGetData(this)"
                           data-container="#characters-content"
                           data-url="/ajax/character/list/<?= esc($animeId) ?>?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages):
                $next = $currentPage + 1; ?>
                <li class="page-item">
                    <a title="Next" href="javascript:;" class="page-link"
                       onclick="paginationGetData(this)"
                       data-container="#characters-content"
                       data-url="/ajax/character/list/<?= esc($animeId) ?>?page=<?= $next ?>">&rsaquo;</a>
                </li>
                <li class="page-item">
                    <a title="Last" href="javascript:;" class="page-link"
                       onclick="paginationGetData(this)"
                       data-container="#characters-content"
                       data-url="/ajax/character/list/<?= esc($animeId) ?>?page=<?= $totalPages ?>">&raquo;</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php
    return ob_get_clean();
}

/* -----------------------------
   Controller
------------------------------*/
$animeId = getAnimeIdFromRequest();
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

if (!$animeId) {
    echo json_encode(['status'=>false, 'html'=>''], JSON_UNESCAPED_SLASHES);
    exit;
}

// Build API url
$base            = rtrim($zpi ?? '', '/');
$characterApiUrl = $base . '/character/list/' . rawurlencode($animeId) . '?page=' . $page;

// ✅ Use DB cache (api_cache) with 10-min TTL
$cacheKey = 'charlist_' . $animeId . '_page_' . $page;
$api = getCachedApiJson($cacheKey, $characterApiUrl, 3600);

if (!$api || empty($api['success']) || empty($api['results']['data'])) {
    // graceful empty state (no pagination shown)
    $emptyHtml = '
<div class="bac-list-wrap mb-3" id="character-list">
    <div class="text-center text-muted py-4">No characters found.</div>
</div>';
    echo json_encode(['status'=>true, 'html'=>$emptyHtml], JSON_UNESCAPED_SLASHES);
    exit;
}

$currentPage = (int)($api['results']['currentPage'] ?? $page);
$totalPages  = (int)($api['results']['totalPages'] ?? 1);
if ($totalPages < 1) $totalPages = 1;

$data = $api['results']['data'];

$html = renderCharactersHtml($animeId, $data, $currentPage, $totalPages);

echo json_encode(['status'=>true, 'html'=>$html], JSON_UNESCAPED_SLASHES);
exit;

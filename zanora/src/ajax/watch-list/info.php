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
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header("Content-Type: application/json; charset=utf-8");

/**
 * Get movieId as a SLUG string.
 */
function getMovieIdFromRequest(): ?string {
    if (!empty($_GET['movieId'])) {
        return trim((string)$_GET['movieId']);
    }

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    $slug = end($segments);
    $slug = urldecode($slug);

    // allow slug characters like letters, digits, dot, underscore, hyphen
    if ($slug && preg_match('/^[A-Za-z0-9._-]+$/', $slug)) {
        return $slug;
    }
    return null;
}

/**
 * Render dropdown HTML. Uses full slug for data-movieid and adapts to page.
 */
function renderHtmlInfo(string $movieSlug, bool $isAdded, ?int $currentType, string $page): string {
    $statuses = [1=>'Watching',2=>'On-Hold',3=>'Plan to Watch',4=>'Dropped',5=>'Completed'];

    // Your examples use btn-sm on watch page; keep original style on detail page
    $btnClass = ($page === 'watch') ? 'btn btn-sm' : 'btn btn-radius btn-light';

    $movieIdAttr = htmlspecialchars($movieSlug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $pageAttr    = htmlspecialchars($page, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    ob_start(); ?>
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="<?= $btnClass ?>">
        <?php if ($isAdded): ?>
            <i class="fas fa-edit mr-2"></i>Edit Watch List
        <?php else: ?>
            <i class="fas fa-plus mr-2"></i>Add to List
        <?php endif; ?>
    </a>

<div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
<?php foreach ($statuses as $st => $label): ?>
    <?php
        $isCurrent = ($isAdded && $st == $currentType);
        $addedClass = $isCurrent ? ' added' : '';
        $checkIcon  = $isCurrent ? '<i class="fas fa-check ml-2"></i>' : '';
    ?>
    <a class="wl-item dropdown-item<?= $addedClass ?>"
       data-type="<?= (int)$st ?>"
       data-movieid="<?= $movieIdAttr ?>"
       data-page="<?= $pageAttr ?>"
       href="javascript:;"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><?= $checkIcon ?>

    </a>
<?php endforeach; ?>

<?php if ($isAdded): ?>
    <a class="wl-item dropdown-item text-danger"
       data-page="<?= $pageAttr ?>"
       data-movieid="<?= $movieIdAttr ?>"
       href="javascript:;"
       data-type="0"><i class="fas fa-trash mr-2"></i>Remove</a>
<?php endif; ?>
</div>

<?php
    return ob_get_clean();
}

$page     = $_GET['page'] ?? 'detail';
$movieSlug = getMovieIdFromRequest();

// Only accept known pages and a valid slug
if (!in_array($page, ['detail','watch'], true) || !$movieSlug) {
    echo json_encode(['status'=>false,'msg'=>'Invalid input']);
    exit;
}

$isAdded = false;
$currentType = null;

if ($userId) {
    // anime_id is a STRING (full slug) now → bind as "s"
    $stmt = $conn->prepare("SELECT type FROM watchlist WHERE user_id = ? AND anime_id = ? LIMIT 1");
    $stmt->bind_param("is", $userId, $movieSlug);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $isAdded = true;
        $currentType = (int)$row['type'];
    }
    $stmt->close();
}

$html = renderHtmlInfo($movieSlug, $isAdded, $currentType, $page);

echo json_encode([
    'status' => true,
    'html'   => $html
]);
exit;

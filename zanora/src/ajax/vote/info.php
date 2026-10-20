<?php
header("Content-Type: application/json; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🧠 Extract ID from path instead of query
$requestUri = $_SERVER['REQUEST_URI'];
$movieId = basename($requestUri); // Get the last part after slash

// Strip off any query string
$movieId = strtok($movieId, '?');

if (!$movieId) {
    echo json_encode(['status' => false, 'msg' => 'Invalid or missing ID']);
    exit;
}

$userMark = null;

// 🧩 Fetch user’s existing vote if logged in
if ($userId) {
    $stmt = $conn->prepare("SELECT mark FROM votes WHERE movie_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param('si', $movieId, $userId);
    $stmt->execute();
    $stmt->bind_result($userMark);
    $stmt->fetch();
    $stmt->close();
}

// 🧮 Fetch vote totals
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN mark = 1 THEN 1 ELSE 0 END) AS boring,
        SUM(CASE WHEN mark = 5 THEN 1 ELSE 0 END) AS great,
        SUM(CASE WHEN mark = 10 THEN 1 ELSE 0 END) AS amazing
    FROM votes
    WHERE movie_id = ?
");
$stmt->bind_param('s', $movieId);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total = $totals['total'] ?? 0;
$boring = $totals['boring'] ?? 0;
$great = $totals['great'] ?? 0;
$amazing = $totals['amazing'] ?? 0;

$average = $total ? round((1 * $boring + 5 * $great + 10 * $amazing) / $total, 1) : '0.0';

// 🧱 Build the HTML block (exact structure you showed)
ob_start();
?>
<div class="block-rating <?= $userMark ? 'rated' : '' ?>">
    <div class="rating-result">
        <div class="rr-mark float-left">
            <strong><i class="fas fa-star text-warning mr-2"></i><?= $average ?></strong>
        </div>
        <div class="rr-title float-right">Vote now</div>
        <div class="clearfix"></div>
    </div>
    <div class="description">What do you think about this anime?</div>
    <div class="button-rate">
        <button type="button" 
                class="btn btn-emo rate-bad btn-vote <?= $userMark == 1 ? 'emo-rated' : '' ?>" 
                data-mark="1" data-id="<?= htmlspecialchars($movieId) ?>">😩<span class="ml-2">Boring</span></button>

        <button type="button" 
                class="btn btn-emo rate-normal btn-vote <?= $userMark == 5 ? 'emo-rated' : '' ?>" 
                data-mark="5" data-id="<?= htmlspecialchars($movieId) ?>">😃<span class="ml-2">Great</span></button>

        <button type="button" 
                class="btn btn-emo rate-good btn-vote <?= $userMark == 10 ? 'emo-rated' : '' ?>" 
                data-mark="10" data-id="<?= htmlspecialchars($movieId) ?>">🤩<span class="ml-2">Amazing</span></button>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</div>
<?php
$html = ob_get_clean();

// ✅ Final JSON response
echo json_encode([
    'status' => true,
    'html' => $html
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

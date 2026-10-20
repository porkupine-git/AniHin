<?php
// /ajax/v2/episode/list.php
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');

header("Content-Type: application/json; charset=utf-8");

// Ensure TTL constant
if (!defined('API_CACHE_TTL')) {
    define('API_CACHE_TTL', 600); // 10 minutes
}

/* ---------------- helpers ---------------- */

function getSlugFromRequest(): ?string {
    // Expecting path like /ajax/v2/episode/list/{slug}
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));
    $slug  = end($parts); // last segment is the slug
    $slug  = urldecode($slug);
    // allow letters, digits, dot, underscore, hyphen
    return ($slug && preg_match('/^[A-Za-z0-9._-]+$/', $slug)) ? $slug : null;
}

// You can keep this if used elsewhere, but it's no longer used for episodes
function curl_json(string $url, int $timeout = 12): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'User-Agent: Mozilla/5.0',
        ],
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($res === false) return [null, $err ?: 'cURL error'];
    $json = json_decode($res, true);
    if (!is_array($json)) return [null, 'Invalid JSON from API'];
    return [$json, null];
}

function pad_range_num(int $n, int $width): string {
    // cap width to 3 -> at most two leading zeros (001..999)
    $width = max(1, min(3, $width));
    return str_pad((string)$n, $width, '0', STR_PAD_LEFT);
}

/* ---------------- main ---------------- */

try {
    $slug = getSlugFromRequest();
    if (!$slug) {
        echo json_encode(['status'=>false,'msg'=>'Invalid slug']);
        exit;
    }

    $episodesUrl = rtrim($zpi, '/').'/episodes/'.rawurlencode($slug);
    $cacheKey    = 'episodes_' . $slug;

    // ✅ Use global cached API helper (api_cache + 10min TTL + stale fallback)
    $api = getCachedApiJson($cacheKey, $episodesUrl, 300);

    if (
        !$api ||
        empty($api['success']) ||
        empty($api['results']['episodes']) ||
        !is_array($api['results']['episodes'])
    ) {
        echo json_encode(['status'=>false,'msg'=>'Failed to load episodes']);
        exit;
    }

    $episodes   = $api['results']['episodes'];
    $totalItems = (int)($api['results']['totalEpisodes'] ?? count($episodes));

    // Rules:
    //  - mini when > 50
    //  - dropdown only when > 100
    //  - seasons-block-max only when > 100
    $useMini      = $totalItems > 50;
    $showDropdown = $totalItems > 100;
    $addMaxClass  = $totalItems > 100;

    $pageSize = 100; // 001-100, 101-200, ...
    $numPages = $showDropdown ? (int)ceil($totalItems / $pageSize) : 1;

    // padding width for labels, capped at 3 to avoid 0001
    $padWidth = min(3, max(1, strlen((string)$totalItems)));

    // Build pages only when dropdown is shown (>100)
    $pages = [];
    if ($showDropdown) {
        for ($p = 1; $p <= $numPages; $p++) $pages[$p] = [];
        foreach ($episodes as $ep) {
            $epNo = (int)($ep['episode_no'] ?? 0);
            if ($epNo <= 0) $epNo = 1;
            $pIdx = (int)floor(($epNo - 1) / $pageSize) + 1;
            $pIdx = max(1, min($numPages, $pIdx));
            $pages[$pIdx][] = $ep;
        }
    }

    // (Optional) mapping for legacy continue-watch by number
    $epNoToId = [];
    foreach ($episodes as $ep) {
        $eid = (int)($ep['episode_id'] ?? 0);
        $eno = (int)($ep['episode_no'] ?? 0);
        if ($eid > 0 && $eno > 0 && !isset($epNoToId[$eno])) $epNoToId[$eno] = $eid;
    }

    // ---------- Build HTML ----------
    ob_start();
    ?>
<div class="seasons-block <?= $addMaxClass ? 'seasons-block-max' : '' ?>">
    <div id="detail-ss-list" class="detail-seasons">
        <div class="detail-infor-content">
            <div class="ss-choice">
                <div class="ssc-list">
                    <div id="ssc-list" class="ssc-button">
                        <div class="ssc-label">List of episodes:</div>
                        <?php if ($showDropdown): ?>
                            <div class="ssc-name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-list mr-3"></i>
                                <span id="current-page"></span>
                                <i class="fas fa-angle-down ml-2"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-model" aria-labelledby="ssc-list">
                                <?php for ($p = 1; $p <= $numPages; $p++):
                                    $start = ($p - 1) * $pageSize + 1;
                                    $end   = min($p * $pageSize, $totalItems);
                                    $label = "EPS: " . pad_range_num($start, $padWidth) . "-" . pad_range_num($end, $padWidth);
                                    ?>
                                    <a data-page="<?= $p ?>" href="javascript:;"
                                       class="dropdown-item ep-page-item <?= $p === 1 ? 'active' : '' ?>">
                                        <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        <i style="<?= $p === 1 ? '' : 'display: none;' ?>" class="fas fa-check ic-active ml-2"></i>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ssc-quick">
                    <div class="sscq-icon"><i class="fas fa-search"></i></div>
                    <input id="search-ep" class="form-control" type="text" placeholder="Number of Ep" autocomplete="off">
                </div>
                <div class="clearfix"></div>
            </div>

            <?php if (!$useMini): ?>
                <!-- Standard list for <= 50 episodes -->
                <div class="ss-list">
                    <?php foreach ($episodes as $ep):
                        $epNo     = (int)($ep['episode_no'] ?? 0);
                        $epId     = (string)($ep['episode_id'] ?? '');
                        $title    = trim((string)($ep['title'] ?? ("Episode ".$epNo)));
                        $jtitle   = trim((string)($ep['japanese_title'] ?? ''));
                        $isFiller = !empty($ep['filler']);

                        $epIdAttr   = htmlspecialchars($epId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $titleAttr  = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $jtitleAttr = htmlspecialchars($jtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $hrefAttr   = htmlspecialchars("/watch/{$slug}?ep={$epId}", ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $classes    = "ssl-item ".($isFiller ? "ssl-item-filler " : "")."ep-item";
                    ?>
                    <a title="<?= $titleAttr ?>"
                       class="<?= $classes ?>"
                       data-number="<?= (int)$epNo ?>"
                       data-id="<?= $epIdAttr ?>"
                       href="<?= $hrefAttr ?>">
                        <div class="ssli-order" title="<?= $titleAttr ?>"><?= (int)$epNo ?></div>
                        <div class="ssli-detail">
                            <div class="ep-name e-dynamic-name"
                                 data-jname="<?= $jtitleAttr !== '' ? $jtitleAttr : $titleAttr ?>"
                                 title="<?= $titleAttr ?>">
                                 <?= $titleAttr ?>
                            </div>
                        </div>
                        <div class="ssli-btn">
                            <div class="btn btn-circle"><i class="fas fa-play"></i></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($showDropdown): ?>
                <!-- Mini + pages of 100 when > 100 episodes -->
                <?php for ($p = 1; $p <= $numPages; $p++): ?>
                    <div id="episodes-page-<?= $p ?>" class="ss-list ss-list-min" data-page="<?= $p ?>" style="display: none;">
                        <?php foreach ($pages[$p] as $ep):
                            $epNo     = (int)($ep['episode_no'] ?? 0);
                            $epId     = (string)($ep['episode_id'] ?? '');
                            $title    = trim((string)($ep['title'] ?? ("Episode ".$epNo)));
                            $jtitle   = trim((string)($ep['japanese_title'] ?? ''));
                            $isFiller = !empty($ep['filler']);

                            $epIdAttr   = htmlspecialchars($epId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $titleAttr  = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $jtitleAttr = htmlspecialchars($jtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $hrefAttr   = htmlspecialchars("/watch/{$slug}?ep={$epId}", ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $classes    = "ssl-item ".($isFiller ? "ssl-item-filler " : "")."ep-item";
                        ?>
                        <a title="<?= $titleAttr ?>"
                           class="<?= $classes ?>"
                           data-number="<?= (int)$epNo ?>"
                           data-id="<?= $epIdAttr ?>"
                           href="<?= $hrefAttr ?>">
                            <div class="ssli-order" title="<?= $titleAttr ?>"><?= (int)$epNo ?></div>
                            <div class="ssli-detail">
                                <div class="ep-name e-dynamic-name"
                                     data-jname="<?= $jtitleAttr !== '' ? $jtitleAttr : $titleAttr ?>"
                                     title="<?= $titleAttr ?>">
                                     <?= $titleAttr ?>
                                </div>
                            </div>
                            <div class="ssli-btn">
                                <div class="btn btn-circle"><i class="fas fa-play"></i></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>

            <?php else: ?>
                <!-- Mini WITHOUT dropdown for 51..100 episodes -->
                <!-- Match HiAnime markup: wrap in one hidden page container -->
                <div id="episodes-page-1" class="ss-list ss-list-min" data-page="1" style="display: none;">
                    <?php foreach ($episodes as $ep):
                        $epNo     = (int)($ep['episode_no'] ?? 0);
                        $epId     = (string)($ep['episode_id'] ?? '');
                        $title    = trim((string)($ep['title'] ?? ("Episode ".$epNo)));
                        $jtitle   = trim((string)($ep['japanese_title'] ?? ''));
                        $isFiller = !empty($ep['filler']);

                        $epIdAttr   = htmlspecialchars($epId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $titleAttr  = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $jtitleAttr = htmlspecialchars($jtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $hrefAttr   = htmlspecialchars("/watch/{$slug}?ep={$epId}", ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $classes    = "ssl-item ".($isFiller ? "ssl-item-filler " : "")."ep-item";
                    ?>
                    <a title="<?= $titleAttr ?>"
                       class="<?= $classes ?>"
                       data-number="<?= (int)$epNo ?>"
                       data-id="<?= $epIdAttr ?>"
                       href="<?= $hrefAttr ?>">
                        <div class="ssli-order" title="<?= $titleAttr ?>"><?= (int)$epNo ?></div>
                        <div class="ssli-detail">
                            <div class="ep-name e-dynamic-name"
                                 data-jname="<?= $jtitleAttr !== '' ? $jtitleAttr : $titleAttr ?>"
                                 title="<?= $titleAttr ?>">
                                 <?= $titleAttr ?>
                            </div>
                        </div>
                        <div class="ssli-btn">
                            <div class="btn btn-circle"><i class="fas fa-play"></i></div>
                        </div>
                        <div class="clearfix"></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <div class="clearfix"></div>
</div>
    <?php
    $html = ob_get_clean();

    // ---------- continueWatch ----------
    $payload = [
        'status'      => true,
        'html'        => $html,
        'totalItems'  => count($episodes),
    ];

    $userId = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : null;
    if ($userId && isset($conn) && !$conn->connect_error) {
        $stmt = $conn->prepare("
            SELECT episode_id, episode_number, progress
            FROM watch_history
            WHERE user_id = ? AND anime_id = ?
            ORDER BY updated_at DESC
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param('is', $userId, $slug);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $savedEpisodeId = (int)($row['episode_id'] ?? 0);
                $savedTimeSec   = (int)round((float)($row['progress'] ?? 0));

                if ($savedEpisodeId > 0) {
                    $payload['continueWatch'] = [
                        'episode_id' => $savedEpisodeId,
                        'time'       => $savedTimeSec,
                    ];
                } else {
                    $legacyNo = (int)($row['episode_number'] ?? 0);
                    if ($legacyNo > 0 && isset($epNoToId[$legacyNo])) {
                        $payload['continueWatch'] = [
                            'episode_id' => (int)$epNoToId[$legacyNo],
                            'time'       => $savedTimeSec,
                        ];
                    }
                }
            }
            $stmt->close();
        }
    }

    echo json_encode($payload);
    exit;

} catch (Throwable $e) {
    error_log("Episode list error: ".$e->getMessage());
    echo json_encode(['status'=>false,'msg'=>'Server error']);
    exit;
}
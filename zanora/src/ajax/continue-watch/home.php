<?php
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
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/component/anime/qtip.php');

$response = ['status' => false, 'html' => ''];

if (!isset($_SESSION['userID'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['userID'];
$sql = "SELECT *
        FROM watch_history
        WHERE user_id = ?
        AND (anime_id, updated_at) IN (
            SELECT anime_id, MAX(updated_at)
            FROM watch_history
            WHERE user_id = ?
            GROUP BY anime_id
        )
        ORDER BY updated_at DESC
        LIMIT 12";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $result->num_rows > 0) {
    ob_start(); // capture HTML output
    ?>
    <section class="block_area block_area_home">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading"><i class="fas fa-history mr-2"></i>Continue Watching</h2>
            </div>
            <div class="float-right viewmore">
                <a class="btn" href="/user/continue-watching">View more<i class="fas fa-angle-right ml-2"></i></a>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content block_area-list film_list film_list-grid">
            <div class="film_list-wrap">
                <?php while ($anime = $result->fetch_assoc()) { ?>
                    <?php
                    $animeId = $anime['anime_id'];
                    $animeData = fetchAnimeData($animeId);
                    if (!$animeData) continue;

                    $progress = (int)($anime['progress'] ?? 0);
                    $duration = (int)($anime['duration'] ?? 0);
                    $percent = (int)($anime['progress_percent'] ?? 0);
                    ?>
                    <div class="flw-item">
                        <div data-movieid="<?= htmlspecialchars($animeData['id']) ?>"
                             class="remove-item remove-cw"
                             data-toggle="tooltip" data-original-title="Remove">
                             <i class="fas fa-times"></i>
                        </div>
                        <div class="film-poster">
                            <?php if ($animeData['adultContent']) { ?>
                                <div class="tick tick-rate">18+</div>
                            <?php } ?>
                            <div class="tick ltr">
                                <?php if (!empty($animeData['subEp'])): ?>
                                    <div class="tick-item tick-sub">
                                        <i class="fas fa-closed-captioning mr-1"></i><?= htmlspecialchars($animeData['subEp']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($animeData['dubEp'])): ?>
                                    <div class="tick-item tick-dub">
                                        <i class="fas fa-microphone mr-1"></i><?= htmlspecialchars($animeData['dubEp']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($animeData['eps'])): ?>
                                    <div class="tick-item tick-eps"><?= htmlspecialchars($animeData['eps']) ?></div>
                                <?php endif; ?>
                            </div>
                            <img loading="lazy" decoding="async" class="film-poster-img lazyload"
                                 data-src="<?= htmlspecialchars($animeData['poster']) ?>"
                                 alt="<?= htmlspecialchars($animeData['title']) ?>">
                            <a class="film-poster-ahref"
                               href="/watch/<?= htmlspecialchars($anime['anime_id']); ?>?ep=<?= htmlspecialchars($anime['episode_id'] ?? '1'); ?>"
                               title="<?= htmlspecialchars($animeData['title']); ?>">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                        <div class="film-detail">
                            <h3 class="film-name">
                                <a href="/<?= htmlspecialchars($anime['anime_id']); ?>"
                                   title="<?= htmlspecialchars($animeData['title'] ?? $animeData['japa']); ?>">
                                    <span class="dynamic-name"
                                          data-title="<?= htmlspecialchars($animeData['title']); ?>"
                                          data-jname="<?= htmlspecialchars($animeData['japa']); ?>">
                                          <?= htmlspecialchars($animeData['title']); ?>
                                    </span>
                                </a>
                            </h3>
                            <div class="fd-bar">
                                <span class="fdb-type">
                                    <i class="fas fa-play-circle"></i> EP <?= htmlspecialchars($anime['episode_number']); ?>
                                </span>
                                <?php if ($duration > 0): ?>
                                    <span class="fdb-time">
                                        <span><?= gmdate("i:s", $progress) ?></span> / <?= gmdate("i:s", $duration) ?>
                                    </span>
                                    <div class="clearfix"></div>
                                    <div class="zr-bar"><span style="width: <?= $percent ?>%;"></span></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </section>
    <div class="clearfix"></div>
    <?php
    $html = ob_get_clean();
    $response = ['status' => true, 'html' => $html];
}

echo json_encode($response);

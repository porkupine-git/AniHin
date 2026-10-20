<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$animeSlug = $_GET['anime'] ?? $_GET['id'] ?? 'solo-leveling-season-2';
$currentEpNum = (int)($_GET['ep'] ?? 1);
if ($currentEpNum < 1) $currentEpNum = 1;

$anime = getAnimeBySlug($animeSlug);
$episodes = $anime['episodes'] ?? [];

// Find current episode data
$currentEpisode = null;
foreach ($episodes as $ep) {
    if ($ep['number'] == $currentEpNum) {
        $currentEpisode = $ep;
        break;
    }
}
if (!$currentEpisode && !empty($episodes)) {
    $currentEpisode = $episodes[0];
    $currentEpNum = $currentEpisode['number'];
}

$page_title = 'Watch ' . ($anime['title'] ?? 'Anime') . ' Episode ' . $currentEpNum . ' (Hindi Dubbed)';
$page_desc = 'Stream ' . ($anime['title'] ?? 'Anime') . ' Episode ' . $currentEpNum . ' in Hindi Dubbed and English Subbed in 1080p HD.';
require_once __DIR__ . '/includes/header.php';
?>

<main class="watch-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav">
            <a href="/index.php"><i class="fa-solid fa-house mr-1"></i> Home</a>
            <span>/</span>
            <a href="/filter.php?type=<?= urlencode($anime['show_type'] ?? 'TV') ?>"><?= e($anime['show_type'] ?? 'TV') ?></a>
            <span>/</span>
            <span class="text-white"><?= e($anime['title']) ?></span>
            <span>/</span>
            <span class="text-accent" id="current-ep-badge">Episode <?= e($currentEpNum) ?></span>
        </nav>

        <div class="watch-layout">
            <!-- Left: Iframe Player & Controls -->
            <div class="player-column">
                <div class="player-wrapper">
                    <!-- Responsive Iframe Embed Frame -->
                    <div class="embed-responsive-frame">
                        <iframe 
                            id="anime-embed-frame" 
                            src="<?= e($currentEpisode['embed'] ?? 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4') ?>" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>

                    <!-- Player Action Bar -->
                    <div class="player-action-bar">
                        <div class="player-controls-left">
                            <button class="ctrl-btn" id="theater-mode-btn" onclick="toggleTheaterMode()">
                                <i class="fa-solid fa-expand mr-1"></i> Expand
                            </button>
                            <span class="text-muted font-weight-bold" id="current-ep-title" style="font-size: 13px;">
                                <?= e($currentEpisode['title'] ?? 'Episode ' . $currentEpNum) ?>
                            </span>
                        </div>

                        <div class="player-controls-right">
                            <?php 
                            $prevEp = $currentEpNum > 1 ? $currentEpNum - 1 : null;
                            $nextEp = $currentEpNum < count($episodes) ? $currentEpNum + 1 : null;
                            ?>
                            <?php if ($prevEp): ?>
                                <a href="/watch.php?anime=<?= e($anime['slug']) ?>&ep=<?= $prevEp ?>" class="ctrl-btn">
                                    <i class="fa-solid fa-backward-step mr-1"></i> Prev
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($nextEp): ?>
                                <a href="/watch.php?anime=<?= e($anime['slug']) ?>&ep=<?= $nextEp ?>" class="ctrl-btn">
                                    Next <i class="fa-solid fa-forward-step ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Server Switching Controls -->
                <div class="server-selection-card">
                    <div class="server-title">
                        <i class="fa-solid fa-server mr-1"></i> Streaming Servers:
                    </div>
                    <div class="server-buttons-group">
                        <button class="server-pill-btn active" onclick="switchServer('Server 1 (Fast HD)', '<?= e($currentEpisode['embed'] ?? 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4') ?>')">
                            Server 1 (Fast HD)
                        </button>
                        <button class="server-pill-btn" onclick="switchServer('Server 2 (Backup)', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4')">
                            Server 2 (Backup)
                        </button>
                        <button class="server-pill-btn" onclick="switchServer('Server 3 (Direct Embed)', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4')">
                            Server 3 (Direct Embed)
                        </button>
                    </div>
                </div>

                <!-- Anime Information Card -->
                <div class="anime-info-panel">
                    <div class="anime-info-poster">
                        <img src="<?= e($anime['poster']) ?>" alt="<?= e($anime['title']) ?>">
                    </div>
                    <div class="anime-info-details">
                        <h1 class="anime-info-title"><?= e($anime['title']) ?></h1>
                        <?php if (!empty($anime['hindi_title'])): ?>
                            <div class="anime-info-hindi-title"><i class="fa-solid fa-language mr-1"></i> <?= e($anime['hindi_title']) ?></div>
                        <?php endif; ?>

                        <div class="anime-info-stats">
                            <?php if (!empty($anime['is_hindi_dubbed'])): ?>
                                <span class="badge-tag badge-dub">Hindi Dubbed</span>
                            <?php endif; ?>
                            <span class="badge-tag badge-hd"><?= e($anime['rating'] ?? 'HD') ?></span>
                            <span class="text-muted">★ <?= e($anime['score']) ?></span>
                            <span class="text-muted">•</span>
                            <span class="text-muted"><?= e($anime['show_type']) ?></span>
                            <span class="text-muted">•</span>
                            <span class="text-muted"><?= e($anime['release_year']) ?></span>
                            <span class="text-muted">•</span>
                            <span class="text-muted"><?= e($anime['status'] ?? 'Currently Airing') ?></span>
                        </div>

                        <p class="anime-info-synopsis"><?= e($anime['synopsis']) ?></p>

                        <div class="genre-tags">
                            <?php 
                            $genreList = is_array($anime['genres'] ?? null) ? $anime['genres'] : explode(',', (string)($anime['genres'] ?? 'Action, Adventure'));
                            foreach ($genreList as $g): 
                                $g = trim($g);
                                if (empty($g)) continue;
                            ?>
                                <a href="/filter.php?genre=<?= urlencode($g) ?>" class="genre-tag-item"><?= e($g) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Episodes List Drawer -->
            <div class="episodes-column">
                <div class="episodes-card">
                    <div class="episodes-header">
                        <h3><i class="fa-solid fa-list-ol text-accent mr-2"></i> Episodes (<?= count($episodes) ?>)</h3>
                        <input type="text" id="episode-search-input" class="ep-search-input" placeholder="Ep number...">
                    </div>

                    <div class="episode-grid-list">
                        <?php foreach ($episodes as $ep): 
                            $isActive = $ep['number'] == $currentEpNum ? 'active' : '';
                        ?>
                            <div class="ep-box-item <?= $isActive ?>" 
                                 data-ep="<?= e($ep['number']) ?>"
                                 title="<?= e($ep['title']) ?>"
                                 onclick="switchEpisode(<?= e($ep['number']) ?>, '<?= e($ep['embed']) ?>', '<?= e(addslashes($ep['title'])) ?>')">
                                <?= e($ep['number']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Anime -->
        <section class="mt-5 mb-5">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="title-bar"></span>
                    <span>You Might Also Like (अन्य एनीमे)</span>
                </h2>
            </div>
            
            <div class="anime-grid">
                <?php 
                $related = array_filter(getAllAnime(), fn($a) => ($a['slug'] ?? '') !== ($anime['slug'] ?? ''));
                foreach (array_slice($related, 0, 5) as $rel): 
                    echo renderAnimeCard($rel);
                endforeach; 
                ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

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
    if ((int)$ep['number'] === $currentEpNum) {
        $currentEpisode = $ep;
        break;
    }
}
if (!$currentEpisode && !empty($episodes)) {
    $currentEpisode = $episodes[0];
    $currentEpNum = (int)$currentEpisode['number'];
}

$epCount = count($episodes);
$prevEp = $currentEpNum > 1 ? $currentEpNum - 1 : null;
$nextEp = $currentEpNum < $epCount ? $currentEpNum + 1 : null;

$bannerImg = !empty($anime['banner']) ? $anime['banner'] : (!empty($anime['poster']) ? $anime['poster'] : 'https://placehold.co/1280x400/101716/D1E8E2?text=AniXoHindi');
$isDub = !empty($anime['is_hindi_dubbed']) || !empty($anime['hindi_title']);

// Check if current anime is in user's watchlist
$isBookmarked = false;
if (isLoggedIn() && function_exists('getHindiPdo')) {
    $pdo = getHindiPdo();
    if ($pdo) {
        $uId = (int)($_SESSION['user']['id'] ?? 0);
        $curId = (int)($anime['id'] ?? 0);
        $bmStmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND (anime_id = ? OR (anime_slug = ? AND anime_slug != '')) LIMIT 1");
        $bmStmt->execute([$uId, $curId, $animeSlug]);
        $isBookmarked = (bool)$bmStmt->fetch();
    }
}

$animeMainTitle = $anime['title'] ?? 'Anime';
$canonicalUrl = 'https://anixohindi.cc/watch.php?anime=' . urlencode($animeSlug) . '&ep=' . $currentEpNum;
$page_title = 'Watch ' . $animeMainTitle . ' Episode ' . $currentEpNum . ($isDub ? ' (Hindi Dubbed)' : ' (Subbed)');
$page_desc = 'Watch ' . $animeMainTitle . ' Episode ' . $currentEpNum . ' in 1080p Full HD Hindi Dubbed online free on AniXoHindi. Authentic Hindi audio, zero buffering, high speed streaming.';
$page_keywords = $animeMainTitle . ' Hindi Dubbed, ' . $animeMainTitle . ' Episode ' . $currentEpNum . ', Watch ' . $animeMainTitle . ' Online, ' . (!empty($anime['hindi_title']) ? $anime['hindi_title'] . ', ' : '') . 'AniXoHindi';
$og_type = 'video.episode';
$og_image = !empty($anime['banner']) ? $anime['banner'] : (!empty($anime['poster']) ? $anime['poster'] : 'https://anixohindi.cc/assets/img/logo.png');

// Structured Data (Schema.org) for Google Video Rich Snippets & Search Carousel
$page_schema_json = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "VideoObject",
            "name" => "Watch " . $animeMainTitle . " Episode " . $currentEpNum . " in Hindi Dubbed",
            "description" => !empty($anime['synopsis']) ? mb_substr(strip_tags($anime['synopsis']), 0, 250) : $page_desc,
            "thumbnailUrl" => array_values(array_filter([$anime['banner'] ?? null, $anime['poster'] ?? null])),
            "uploadDate" => "2024-01-01T00:00:00+05:30",
            "embedUrl" => "https://embed.anixohindi.cc/embed/ani/" . ($anime['anilist_id'] ?? $curId ?? 1) . "/" . $currentEpNum,
            "inLanguage" => ["hi", "ja"],
            "isFamilyFriendly" => true,
            "potentialAction" => [
                "@type" => "WatchAction",
                "target" => $canonicalUrl
            ]
        ],
        [
            "@type" => "TVEpisode",
            "episodeNumber" => $currentEpNum,
            "name" => $animeMainTitle . " Episode " . $currentEpNum,
            "partOfSeries" => [
                "@type" => "TVSeries",
                "name" => $animeMainTitle,
                "alternateName" => $anime['hindi_title'] ?? null,
                "genre" => is_array($anime['genres'] ?? null) ? $anime['genres'] : ['Animation', 'Action'],
                "aggregateRating" => [
                    "@type" => "AggregateRating",
                    "ratingValue" => (float)($anime['score'] ?? 8.5),
                    "bestRating" => 10,
                    "ratingCount" => 1420
                ]
            ]
        ],
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => "https://anixohindi.cc/"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Hindi Dubbed",
                    "item" => "https://anixohindi.cc/browse.php?type=dub"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $animeMainTitle,
                    "item" => "https://anixohindi.cc/watch.php?anime=" . urlencode($animeSlug) . "&ep=1"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 4,
                    "name" => "Episode " . $currentEpNum,
                    "item" => $canonicalUrl
                ]
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Focus / Lights Off Backdrop Overlay -->
<div id="lightsOffOverlay" class="lights-off-overlay" onclick="toggleLightsOff()" aria-label="Exit lights off mode"></div>
<div id="lightsOffNotice" class="lights-off-notice">
    <span><i class="fa-solid fa-lightbulb"></i> Focus Mode Active</span>
    <button type="button" onclick="toggleLightsOff()"><i class="fa-solid fa-xmark mr-1"></i> Exit (Esc)</button>
</div>

<!-- Cinema Ambient Backdrop Banner (Matte & Vignette - No Glow) -->
<div class="watch-ambient-banner" style="background-image: url('<?= e($bannerImg) ?>');">
    <div class="ambient-matte-overlay"></div>
</div>

<main class="watch-container">
    <div class="container">
        <!-- Breadcrumb & Top Indicator Bar -->
        <div class="watch-header-bar">
            <nav class="breadcrumb-nav">
                <a href="/index.php"><i class="fa-solid fa-house"></i> Home</a>
                <span class="breadcrumb-sep">/</span>
                <a href="/browse.php?type=<?= urlencode($anime['show_type'] ?? 'TV') ?>"><?= e($anime['show_type'] ?? 'TV') ?></a>
                <span class="breadcrumb-sep">/</span>
                <a href="/watch.php?anime=<?= e($anime['slug']) ?>&ep=1" class="breadcrumb-anime-title"><?= e($anime['title']) ?></a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-ep-current" id="breadcrumb-ep-badge">EP <?= e($currentEpNum) ?></span>
            </nav>

            <div class="watch-audio-tag">
                <?php if ($isDub): ?>
                    <span class="audio-pill dub"><i class="fa-solid fa-headphones"></i> Hindi Dubbed</span>
                <?php else: ?>
                    <span class="audio-pill sub"><i class="fa-solid fa-closed-captioning"></i> Japanese Audio (Sub)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Watch Cockpit Layout -->
        <div class="watch-layout" id="watchLayout">
            <!-- Left Column: Video Player, Stream Controls, Servers, Dossier -->
            <div class="player-column">
                <div class="player-cockpit-frame">
                    <!-- Top Cockpit Control Deck -->
                    <div class="cockpit-top-bar">
                        <div class="cockpit-left">
                            <span class="cockpit-badge" id="cockpit-ep-badge">EP <?= e($currentEpNum) ?></span>
                            <h2 class="cockpit-title" id="cockpit-ep-title">
                                <?= e($currentEpisode['title'] ?? ('Episode ' . $currentEpNum)) ?>
                            </h2>
                        </div>

                        <div class="cockpit-right">
                            <button type="button" class="cockpit-tool-btn" id="lights-off-btn" onclick="toggleLightsOff()" title="Toggle Focus Mode (Lights Off)">
                                <i class="fa-regular fa-lightbulb"></i>
                                <span class="tool-label">Focus</span>
                            </button>
                            <button type="button" class="cockpit-tool-btn active" id="auto-next-btn" onclick="toggleAutoNext()" title="Toggle Auto-Play Next Episode">
                                <i class="fa-solid fa-forward-step"></i>
                                <span class="tool-label">Auto-Next</span>
                            </button>
                            <button type="button" class="cockpit-tool-btn" id="theater-mode-btn" onclick="toggleTheaterMode()" title="Toggle Wide Theater Mode">
                                <i class="fa-solid fa-expand"></i>
                                <span class="tool-label">Theater</span>
                            </button>
                        </div>
                    </div>

                    <!-- 16:9 Video Embed Player -->
                    <div class="embed-responsive-frame">
                        <iframe 
                            id="anime-embed-frame" 
                            src="<?= e($currentEpisode['embed'] ?? 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4') ?>" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>

                    <!-- Bottom Cockpit Navigation & Specs Deck -->
                    <div class="cockpit-bottom-bar">
                        <div class="cockpit-nav-actions">
                            <button type="button" 
                                    class="cockpit-nav-btn <?= $prevEp ? '' : 'disabled' ?>" 
                                    id="btn-prev-ep"
                                    <?= $prevEp ? 'onclick="goToEpisode(' . $prevEp . ')"' : 'disabled' ?>
                                    title="Previous Episode">
                                <i class="fa-solid fa-backward-step"></i>
                                <span>Prev Ep</span>
                            </button>

                            <button type="button" 
                                    class="cockpit-nav-btn <?= $nextEp ? '' : 'disabled' ?>" 
                                    id="btn-next-ep"
                                    <?= $nextEp ? 'onclick="goToEpisode(' . $nextEp . ')"' : 'disabled' ?>
                                    title="Next Episode">
                                <span>Next Ep</span>
                                <i class="fa-solid fa-forward-step"></i>
                            </button>
                        </div>

                        <div class="cockpit-spec-pills">
                            <span class="spec-pill"><i class="fa-solid fa-circle-check"></i> 1080p FHD</span>
                            <span class="spec-pill hide-mobile"><i class="fa-solid fa-gauge-high"></i> Low Latency</span>
                        </div>

                        <div class="cockpit-quick-tools">
                            <button type="button" 
                                    class="quick-tool-btn <?= $isBookmarked ? 'active-bookmark' : '' ?>" 
                                    id="quickBookmarkBtn"
                                    data-id="<?= (int)($anime['id'] ?? 0) ?>"
                                    data-slug="<?= e($animeSlug) ?>"
                                    data-title="<?= e($anime['title'] ?? '') ?>"
                                    data-poster="<?= e($anime['poster'] ?? '') ?>"
                                    onclick="toggleWatchlistBookmark(this)" 
                                    title="<?= $isBookmarked ? 'In Watchlist (Click to remove)' : 'Add to Watchlist' ?>">
                                <i class="<?= $isBookmarked ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark' ?>" id="quickBookmarkIcon"></i>
                            </button>
                            <button type="button" class="quick-tool-btn" onclick="copyWatchLink()" title="Copy Share Link">
                                <i class="fa-solid fa-share-nodes"></i>
                                <span class="quick-tooltip" id="shareTooltip">Copied!</span>
                            </button>
                            <button type="button" class="quick-tool-btn" onclick="reloadPlayer()" title="Reload Player Stream">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Streaming Server Switcher Station -->
                <div class="streaming-station-card">
                    <div class="station-meta-row">
                        <div class="station-title">
                            <span class="station-pulse-dot"></span>
                            <span>Streaming Server Station / सर्वर चयन</span>
                        </div>
                        <div class="station-hint">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Switch server if video buffers or won't load</span>
                        </div>
                    </div>

                    <div class="server-pill-deck">
                        <button type="button" class="server-station-btn active" id="server-btn-1" data-server="1" onclick="switchServer('Server 1 (Fast HD)', '<?= e($currentEpisode['embed']) ?>', this)">
                            <span class="server-dot online"></span>
                            <span class="server-name">Server 1 (Fast HD)</span>
                            <span class="server-tag">1080p Stream</span>
                        </button>
                        <button type="button" class="server-station-btn" id="server-btn-2" data-server="2" onclick="switchServer('Server 2 (Direct ID)', '<?= e($currentEpisode['embed_id'] ?? $currentEpisode['embed']) ?>', this)">
                            <span class="server-dot online"></span>
                            <span class="server-name">Server 2 (Direct ID)</span>
                            <span class="server-tag">Direct Stream</span>
                        </button>
                        <?php if (!empty($anime['mal_id'])): ?>
                        <button type="button" class="server-station-btn" id="server-btn-3" data-server="3" onclick="switchServer('Server 3 (MAL Fallback)', '<?= e((defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc') . '/embed/mal/' . $anime['mal_id'] . '/' . $currentEpNum) ?>', this)">
                            <span class="server-dot backup"></span>
                            <span class="server-name">Server 3 (MAL Fallback)</span>
                            <span class="server-tag">Backup</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Next-Gen Dual-View Episode Deck -->
            <div class="episodes-column">
                <div class="episodes-deck-card spoiler-blur-active" id="episodesDeckCard">
                    <!-- Deck Header with View Mode Switcher & Anti-Spoiler Shield -->
                    <div class="ep-deck-header">
                        <div class="ep-deck-title-wrap">
                            <div class="ep-deck-icon-box">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="ep-deck-heading">
                                <h3 class="ep-deck-title">Episodes</h3>
                                <span class="ep-deck-count-sub"><span id="episodesFilteredCount"><?= $epCount ?></span> of <?= $epCount ?> Available</span>
                            </div>
                        </div>

                        <!-- Dual-View Toggle Buttons & Spoiler Shield -->
                        <div class="ep-deck-view-switcher">
                            <button type="button" class="view-switch-btn active btn-spoiler-toggle" id="btn-spoiler-toggle" onclick="toggleSpoilerBlur()" title="Spoiler Shield: Blur Episode Images & Descriptions (Hover to peek)">
                                <i class="fa-solid fa-eye-slash" id="spoiler-icon"></i>
                            </button>
                            <span class="switcher-divider"></span>
                            <button type="button" class="view-switch-btn active" id="btn-view-grid" onclick="setEpisodeView('grid')" title="Switch to Grid View">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <button type="button" class="view-switch-btn" id="btn-view-list" onclick="setEpisodeView('list')" title="Switch to Detailed List View">
                                <i class="fa-solid fa-list-ul"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Fast Episode Search / Jump Input -->
                    <div class="ep-deck-search-wrap">
                        <i class="fa-solid fa-magnifying-glass search-ico"></i>
                        <input type="text" id="episode-search-input" class="ep-deck-search-input" placeholder="Search Ep number or title..." oninput="filterEpisodes(this.value)">
                        <button type="button" class="ep-deck-search-clear" id="deck-search-clear" onclick="clearEpSearch()" title="Clear Search">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Mode 1: Compact Grid View (Default) -->
                    <div class="episode-deck-grid" id="episodeDeckGrid">
                        <?php foreach ($episodes as $ep): 
                            $num = (int)$ep['number'];
                            $isActive = $num === $currentEpNum ? 'active' : '';
                            $epTitle = $ep['title'] ?? ('Episode ' . $num);
                        ?>
                            <div class="ep-box-item <?= $isActive ?>" 
                                 data-ep="<?= $num ?>"
                                 data-title="<?= e(strtolower($epTitle)) ?>"
                                 title="<?= e($epTitle) ?>"
                                 onclick="switchEpisode(<?= $num ?>, '<?= e($ep['embed']) ?>', '<?= e(addslashes($epTitle)) ?>', '<?= e($ep['embed_id'] ?? $ep['embed']) ?>', '<?= !empty($anime['mal_id']) ? e((defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc') . '/embed/mal/' . $anime['mal_id'] . '/' . $num) : '' ?>')">
                                <span class="ep-box-num"><?= $num ?></span>
                                <?php if ($isDub): ?>
                                    <span class="ep-box-badge">HI</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mode 2: Detailed List View with Thumbnails & Descriptions -->
                    <div class="episode-deck-list" id="episodeDeckList" style="display: none;">
                        <?php foreach ($episodes as $ep): 
                            $num = (int)$ep['number'];
                            $isActive = $num === $currentEpNum ? 'active' : '';
                            $epTitle = $ep['title'] ?? ('Episode ' . $num);
                            $thumb = !empty($ep['image']) ? $ep['image'] : $bannerImg;
                            $overview = !empty($ep['overview']) ? $ep['overview'] : '';
                        ?>
                            <div class="ep-list-card <?= $isActive ?>" 
                                 data-ep="<?= $num ?>"
                                 data-title="<?= e(strtolower($epTitle)) ?>"
                                 onclick="switchEpisode(<?= $num ?>, '<?= e($ep['embed']) ?>', '<?= e(addslashes($epTitle)) ?>', '<?= e($ep['embed_id'] ?? $ep['embed']) ?>', '<?= !empty($anime['mal_id']) ? e((defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc') . '/embed/mal/' . $anime['mal_id'] . '/' . $num) : '' ?>')">
                                <div class="ep-list-thumb-wrap">
                                    <img src="<?= e($thumb) ?>" alt="<?= e($epTitle) ?>" loading="lazy" class="ep-list-thumb">
                                    <span class="ep-list-duration">24m</span>
                                    <div class="ep-list-play-overlay">
                                        <i class="fa-solid fa-play"></i>
                                    </div>
                                    <div class="ep-list-spoiler-badge">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </div>
                                </div>

                                <div class="ep-list-info">
                                    <div class="ep-list-meta-row">
                                        <span class="ep-list-badge">EP <?= $num ?></span>
                                        <?php if ($isDub): ?>
                                            <span class="ep-list-dub-pill">Hindi Dub</span>
                                        <?php endif; ?>
                                        <?php if ($isActive): ?>
                                            <span class="ep-list-playing-tag"><i class="fa-solid fa-volume-high"></i> Playing</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ep-list-title" title="<?= e($epTitle) ?>"><?= e($epTitle) ?></div>
                                    <?php if (!empty($overview)): ?>
                                        <div class="ep-list-overview" title="<?= e($overview) ?>"><?= e($overview) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- No Episodes Found Notice -->
                    <div class="ep-deck-empty-state" id="deckEmptyState" style="display: none;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <p>No episode matching your search.</p>
                        <button type="button" class="btn-clear-search" onclick="clearEpSearch()">View All Episodes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Studio Anime Dossier Card (Full-Width Stretched Across Screen) -->
        <div class="anime-dossier-card full-width-dossier">
            <div class="dossier-poster-wrap">
                <img src="<?= e($anime['poster']) ?>" alt="<?= e($anime['title']) ?>" class="dossier-poster-img" loading="lazy">
                <div class="dossier-poster-badge">
                    <i class="fa-solid fa-star"></i> <?= e($anime['score'] ?? '8.5') ?>
                </div>
            </div>

            <div class="dossier-body">
                <div class="dossier-top-meta-row">
                    <div class="dossier-header-group">
                        <h1 class="dossier-title"><?= e($anime['title']) ?></h1>
                        <?php if (!empty($anime['hindi_title'])): ?>
                            <div class="dossier-hindi-title">
                                <i class="fa-solid fa-language"></i> <?= e($anime['hindi_title']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dossier-quick-actions">
                        <button type="button" 
                                class="btn-dossier-action <?= $isBookmarked ? 'active-bookmark' : '' ?>" 
                                id="btnBookmarkWatch" 
                                data-id="<?= (int)($anime['id'] ?? 0) ?>"
                                data-slug="<?= e($animeSlug) ?>"
                                data-title="<?= e($anime['title'] ?? '') ?>"
                                data-poster="<?= e($anime['poster'] ?? '') ?>"
                                onclick="toggleWatchlistBookmark(this)"
                                title="<?= $isBookmarked ? 'In Watchlist (Click to remove)' : 'Add to Watchlist' ?>">
                            <i class="<?= $isBookmarked ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark' ?>" id="bookmarkWatchIcon"></i> 
                            <span id="bookmarkWatchText"><?= $isBookmarked ? 'In Watchlist' : 'Watchlist' ?></span>
                        </button>
                        <button type="button" class="btn-dossier-action" onclick="copyWatchLink()" title="Share Anime Link">
                            <i class="fa-solid fa-share-nodes"></i> <span>Share</span>
                        </button>
                        <a href="/browse.php?type=<?= urlencode($anime['show_type'] ?? 'TV') ?>" class="btn-dossier-action">
                            <i class="fa-solid fa-film"></i> <span><?= e($anime['show_type'] ?? 'TV') ?> Series</span>
                        </a>
                    </div>
                </div>

                <!-- Meta Chips -->
                <div class="dossier-meta-chips">
                    <?php if ($isDub): ?>
                        <span class="dossier-chip chip-dub"><i class="fa-solid fa-headphones"></i> Hindi Dub</span>
                    <?php endif; ?>
                    <span class="dossier-chip"><?= e($anime['show_type'] ?? 'TV') ?></span>
                    <span class="dossier-chip"><?= e($anime['release_year'] ?? '2024') ?></span>
                    <span class="dossier-chip"><?= e($anime['rating'] ?? 'HD') ?></span>
                    <span class="dossier-chip"><?= count($episodes) ?> Episodes</span>
                    <span class="dossier-chip chip-status"><?= e($anime['status'] ?? 'Currently Airing') ?></span>
                </div>

                <!-- Synopsis with Expand/Collapse & Anti-Spoiler -->
                <div class="dossier-synopsis-header">
                    <span class="dossier-synopsis-label"><i class="fa-solid fa-align-left"></i> Story Synopsis</span>
                    <button type="button" class="btn-spoiler-text active" id="btn-synopsis-blur" onclick="toggleSynopsisBlur()" title="Spoiler Shield: Blur / Unblur Story Synopsis">
                        <i class="fa-solid fa-eye-slash" id="synopsis-eye-icon"></i>
                        <span id="synopsis-eye-text">Spoiler Shield</span>
                    </button>
                </div>
                <div class="dossier-synopsis-wrap">
                    <p class="dossier-synopsis spoiler-blurred" id="dossierSynopsis" onclick="toggleSynopsisBlur()">
                        <?= e($anime['synopsis'] ?? 'No synopsis available for this title.') ?>
                    </p>
                    <?php if (strlen($anime['synopsis'] ?? '') > 220): ?>
                        <button type="button" class="btn-synopsis-toggle" id="synopsisToggleBtn" onclick="toggleSynopsis()">
                            <span>Read More</span> <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Genre Pills -->
                <div class="dossier-genres">
                    <?php 
                    $genreList = is_array($anime['genres'] ?? null) ? $anime['genres'] : explode(',', (string)($anime['genres'] ?? 'Action, Adventure'));
                    foreach ($genreList as $g): 
                        $g = trim($g);
                        if (empty($g)) continue;
                    ?>
                        <a href="/browse.php?genre=<?= urlencode($g) ?>" class="dossier-genre-pill"><?= e($g) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- You Might Also Like Section -->
        <section class="watch-recommendations-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="title-bar"></span>
                    <span>You Might Also Like (अन्य एनीमे)</span>
                </h2>
                <a href="/browse.php" class="section-more-link">
                    <span>Explore All</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
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

<script>
// Pass initial episode context to JS
window.WATCH_ANIME_SLUG = <?= json_encode($anime['slug']) ?>;
window.WATCH_CURRENT_EP = <?= (int)$currentEpNum ?>;
window.WATCH_TOTAL_EPS = <?= (int)$epCount ?>;
window.WATCH_EPISODES = <?= json_encode(array_map(function($e) {
    return [
        'number' => (int)$e['number'],
        'title' => $e['title'] ?? ('Episode ' . $e['number']),
        'embed' => $e['embed'] ?? ''
    ];
}, $episodes)) ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

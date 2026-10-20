<?php
$page_title = 'Watch Free Hindi Dubbed Anime Online in Full HD';
$page_desc = 'Watch popular anime in Hindi Dubbed and English Subbed. Stream Bleach TYBW, One Piece, Death Note in 1080p without ads.';
require_once __DIR__ . '/includes/header.php';

$allAnime = getAllAnime();

// Spotlight Hero: pick top featured anime dynamically from the live API
$spotlight = null;
foreach ($allAnime as $a) {
    if (!empty($a['banner']) && !empty($a['hindi_title']) && ($a['score'] ?? 0) >= 8.5) {
        $spotlight = enrichAnimeDetails($a);
        break;
    }
}
if (!$spotlight && !empty($allAnime)) {
    $spotlight = enrichAnimeDetails($allAnime[0]);
}

// 1. Newly Released & Currently Airing from db.anixo.buzz
$newlyReleased = array_filter($allAnime, fn($a) => ($a['status'] ?? '') === 'Currently Airing');

// 2. Popular Hindi Dubbed Anime (AniZip & db.anixo.buzz Localized)
$hindiDubbed = array_filter($allAnime, fn($a) => !empty($a['hindi_title']));
if (count($hindiDubbed) < 8) {
    $existingIds = array_flip(array_column($hindiDubbed, 'id'));
    foreach ($allAnime as $a) {
        if (!empty($a['is_hindi_dubbed']) && !isset($existingIds[$a['id']])) {
            $hindiDubbed[] = $a;
            $existingIds[$a['id']] = true;
            if (count($hindiDubbed) >= 12) break;
        }
    }
}

// 3. Top Rated Anime from db.anixo.buzz
$topRated = $allAnime;
usort($topRated, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
?>

<main>
    <!-- Spotlight Hero Section (100% Dynamic from db.anixo.buzz API) -->
    <!-- 3D Infinite Fan-Deck Spotlight Hero Section (Matching Reference Image) -->
    <?php
    // Curate 12 premium spotlight anime (Hindi dubs + Top popular from db.anixo.buzz)
    $spotlightList = [];
    $seenDeck = [];
    foreach ($hindiDubbed as $a) {
        if (!isset($seenDeck[$a['id']])) {
            $spotlightList[] = $a;
            $seenDeck[$a['id']] = true;
            if (count($spotlightList) >= 6) break;
        }
    }
    foreach ($allAnime as $a) {
        if (!isset($seenDeck[$a['id']])) {
            $spotlightList[] = $a;
            $seenDeck[$a['id']] = true;
            if (count($spotlightList) >= 12) break;
        }
    }

    // Enrich spotlight items with high-resolution TMDB backdrops & official PNG logos
    foreach ($spotlightList as &$aItem) {
        $tmdbArt = getTmdbAnimeArt($aItem['title'], $aItem['release_year'] ?? null);
        if ($tmdbArt) {
            if (!empty($tmdbArt['backdrop'])) {
                $aItem['banner'] = $tmdbArt['backdrop'];
            }
            if (!empty($tmdbArt['logo'])) {
                $aItem['logo'] = $tmdbArt['logo'];
            }
        }
    }
    unset($aItem);

    $spotlightCount = count($spotlightList);
    $initialAnime = $spotlightList[0] ?? null;
    ?>

    <?php if (!empty($spotlightList) && $initialAnime): ?>
    <section class="hero-spotlight-deck" id="heroSpotlightDeck">
        <!-- Dynamic Full-Bleed Backdrops for Smooth Crossfade -->
        <div class="deck-backdrop-container">
            <div class="deck-backdrop-bg active" id="deckBackdrop1" style="background-image: url('<?= e($initialAnime['banner'] ?? $initialAnime['poster']) ?>');"></div>
            <div class="deck-backdrop-bg" id="deckBackdrop2"></div>
            <div class="deck-backdrop-overlay"></div>
        </div>


        <!-- Screen-Edge Navigation Arrows (Attached to far left & right screen edges) -->
        <button class="deck-nav-btn deck-nav-prev" id="deckPrevBtn" aria-label="Previous Anime">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="deck-nav-btn deck-nav-next" id="deckNextBtn" aria-label="Next Anime">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <!-- Center 3D Curved Fan-Deck of Cards -->
        <div class="deck-stage-wrapper">
            <div class="deck-cards-track" id="deckCardsTrack">
                <?php foreach ($spotlightList as $idx => $item): ?>
                    <div class="deck-card-item" data-index="<?= $idx ?>" title="<?= e($item['title']) ?>">
                        <div class="deck-card-inner">
                            <img src="<?= e($item['poster']) ?>" alt="<?= e($item['title']) ?>" loading="eager">
                            <div class="deck-card-glow"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Center-Bottom Active Anime Info -->
        <div class="deck-info-container">
            <!-- Metadata Row (Format, Score, Episodes, Year, Dub) -->
            <div class="deck-meta-row" id="deckMetaRow">
                <span class="deck-meta-pill" id="deckMetaFormat">• <?= e($initialAnime['show_type'] ?? 'TV') ?></span>
                <span class="deck-meta-sep">|</span>
                <span class="deck-meta-pill">SCORE — <span class="score-highlight" id="deckMetaScore"><?= round(((float)($initialAnime['score'] ?? 8.2)) * 10) ?>%</span></span>
                <span class="deck-meta-sep">|</span>
                <span class="deck-meta-pill" id="deckMetaEpisodes"><?= e($initialAnime['episodes_count'] ?? 12) ?> EP</span>
                <span class="deck-meta-sep">|</span>
                <span class="deck-meta-pill" id="deckMetaYear"><?= e($initialAnime['release_year'] ?? 2025) ?></span>
                <?php if (!empty($initialAnime['hindi_title']) || !empty($initialAnime['is_hindi_dubbed'])): ?>
                    <span class="deck-meta-sep">|</span>
                    <span class="deck-meta-pill deck-meta-dub" id="deckMetaDub"><i class="fa-solid fa-headphones mr-1"></i> HINDI DUB</span>
                <?php endif; ?>
            </div>

            <!-- Stylized Anime Logo (TMDB) with Fallback to Stylized Text Title -->
            <div class="deck-title-area" id="deckTitleArea">
                <img class="deck-title-logo" id="deckTitleLogo" src="<?= e($initialAnime['logo'] ?? '') ?>" alt="<?= e($initialAnime['title']) ?>" style="<?= empty($initialAnime['logo']) ? 'display: none;' : '' ?>">
                <h1 class="deck-title" id="deckTitle" style="<?= !empty($initialAnime['logo']) ? 'display: none;' : '' ?>"><?= e($initialAnime['title']) ?></h1>
            </div>

            <!-- Hindi Title / Subtitle -->
            <div class="deck-hindi-title" id="deckHindiTitle">
                <?php if (!empty($initialAnime['hindi_title'])): ?>
                    <i class="fa-solid fa-language mr-1"></i><?= e($initialAnime['hindi_title']) ?>
                <?php endif; ?>
            </div>

            <!-- Genres Row -->
            <?php 
            $gText = !empty($initialAnime['genres']) ? (is_array($initialAnime['genres']) ? implode(' — ', array_slice($initialAnime['genres'], 0, 3)) : str_replace(', ', ' — ', $initialAnime['genres'])) : 'ACTION — ADVENTURE — FANTASY';
            ?>
            <div class="deck-genres" id="deckGenres"><?= e($gText) ?></div>

            <!-- Action Buttons -->
            <div class="deck-actions">
                <a href="/watch.php?anime=<?= e($initialAnime['slug']) ?>&ep=1" class="btn-deck-watch" id="deckWatchBtn">
                    <i class="fa-solid fa-play mr-2"></i> WATCH NOW
                </a>
                <a href="/filter.php" class="btn-deck-browse">
                    BROWSE CATALOGUE
                </a>
            </div>
        </div>

    </section>

    <!-- Pass Spotlight JSON to Client Script -->
    <script>
        window.SPOTLIGHT_DECK_DATA = <?= json_encode(array_values(array_map(function($a) {
            $rawScore = (float)($a['score'] ?? 8.2);
            $scorePct = round($rawScore * 10) . '%';
            $genres = !empty($a['genres']) ? (is_array($a['genres']) ? implode(' — ', array_slice($a['genres'], 0, 3)) : str_replace(', ', ' — ', $a['genres'])) : 'ACTION — ADVENTURE';
            return [
                'id' => $a['id'],
                'title' => $a['title'],
                'hindi_title' => $a['hindi_title'] ?? '',
                'poster' => $a['poster'],
                'banner' => $a['banner'] ?? $a['poster'],
                'logo' => $a['logo'] ?? '',
                'score_pct' => $scorePct,
                'format' => strtoupper($a['show_type'] ?? 'TV'),
                'episodes' => ($a['episodes_count'] ?? 12) . ' EP',
                'year' => $a['release_year'] ?? 2025,
                'genres' => $genres,
                'slug' => $a['slug'],
                'is_dub' => !empty($a['is_hindi_dubbed']) || !empty($a['hindi_title'])
            ];
        }, $spotlightList))) ?>;
    </script>
    <?php endif; ?>

    <!-- Mobile Quick Filter Chips (Touch Momentum Scrollable) -->
    <div class="mobile-chips-bar" aria-label="Quick Category Filter">
        <div class="container">
            <div class="chips-scroll-track">
                <a href="/filter.php" class="chip-pill active"><i class="fa-solid fa-fire text-accent-gold mr-1"></i> All</a>
                <a href="/filter.php?type=dub" class="chip-pill"><i class="fa-solid fa-headphones mr-1"></i> Hindi Dubbed</a>
                <a href="/filter.php?status=Currently+Airing" class="chip-pill"><i class="fa-solid fa-bolt mr-1"></i> Currently Airing</a>
                <a href="/filter.php?type=Movie" class="chip-pill"><i class="fa-solid fa-film mr-1"></i> Movies</a>
                <a href="/filter.php?type=sub" class="chip-pill"><i class="fa-solid fa-closed-captioning mr-1"></i> Subbed</a>
                <a href="/filter.php?genre=Action" class="chip-pill">Action</a>
                <a href="/filter.php?genre=Adventure" class="chip-pill">Adventure</a>
                <a href="/filter.php?genre=Fantasy" class="chip-pill">Fantasy</a>
                <a href="/filter.php?genre=Comedy" class="chip-pill">Comedy</a>
                <a href="/filter.php?genre=Romance" class="chip-pill">Romance</a>
            </div>
        </div>
    </div>

    <!-- Section 1: Newly Released Anime (Live from db.anixo.buzz) -->
    <section class="container mt-3">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Newly Released & Airing Anime (नई रिलीज़्ड एनीमे)</span>
            </h2>
            <a href="/filter.php?status=Currently+Airing" class="text-secondary small font-weight-bold">
                View Airing Catalog <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($newlyReleased, 0, 12) as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 2: Popular in Hindi Dub (AniZip & db.anixo.buzz Localized) -->
    <section class="container mt-5">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Popular in Hindi Dub (हिंदी डब एनीमे)</span>
            </h2>
            <a href="/filter.php?type=dub" class="text-secondary small font-weight-bold">
                View All Dubs <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($hindiDubbed, 0, 12) as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 3: Top Rated Anime -->
    <section class="container mt-5">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Top Rated Anime Series</span>
            </h2>
            <a href="/filter.php?sort=score" class="text-secondary small font-weight-bold">
                Browse All <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($topRated, 0, 12) as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 4: Genre Quick Access -->
    <section class="container mt-5 mb-5">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Explore by Genres (शैली के अनुसार)</span>
            </h2>
        </div>
        
        <div class="genre-tags">
            <?php 
            $genres = [
                'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 
                'Horror', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 
                'Supernatural', 'Super Power', 'Suspense', 'Thriller'
            ];
            foreach ($genres as $genre):
            ?>
                <a href="/filter.php?genre=<?= urlencode($genre) ?>" class="genre-tag-item">
                    <?= e($genre) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Watch Free Hindi Dubbed Anime Online in 1080p Full HD';
$page_desc = 'Stream 220+ verified Hindi Dubbed anime series and movies in 1080p Full HD online free. Watch Naruto, Solo Leveling, Bleach, Death Note with authentic Hindi audio on AniXoHindi.';
$page_keywords = 'Hindi Dubbed Anime, Watch Hindi Dubbed Anime Online, Free Anime Streaming, Hindi Anime 1080p, Naruto Hindi Dub, Bleach Hindi, Solo Leveling Hindi, Death Note Hindi, AniXoHindi';

$page_schema_json = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => [
        [
            "@type" => "Question",
            "name" => "Where can I watch Hindi dubbed anime online for free?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "You can watch full episodes of popular Hindi dubbed anime for free on AniXoHindi.cc with high-speed 1080p Full HD streaming, zero buffering, and no mandatory subscription."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Which popular anime series are available in Hindi dub on AniXoHindi?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "AniXoHindi features over 220+ verified Hindi dubbed anime series and movies, including Naruto, Naruto Shippuden, Bleach: Thousand-Year Blood War, Solo Leveling, Death Note, Demon Slayer, Jujutsu Kaisen, and Black Clover."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Do I need to create an account or pay to stream anime?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "No payment or credit card is required. All anime streams are 100% free. Creating a free account allows you to sync your watchlist, bookmark episodes, and track your watch history across devices."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "What video quality is available on AniXoHindi?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "All episodes stream in adaptive 1080p Full HD and 720p HD with crystal-clear Hindi dub audio channels optimized for mobile and desktop displays."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "How frequently are new Hindi dubbed anime episodes updated?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Episodes are updated on a daily basis as soon as official Hindi dub dubbing releases and new seasons air."
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';

// 100% Authoritative Hindi Anime Catalog from our verified Database
$allAnime = getAllAnime(['sort' => 'popular']);
$trendingDubs = array_slice($allAnime, 0, 12);
$latestDubs = getAllAnime(['sort' => 'latest']);
$topRated = getAllAnime(['sort' => 'score']);
$hindiMovies = getAllAnime(['type' => 'Movie']);

// Curate 12 premium spotlight anime from verified Hindi catalog
$spotlightList = [];
$seenDeck = [];
foreach ($allAnime as $a) {
    if (!isset($seenDeck[$a['id']]) && (!empty($a['banner']) || !empty($a['poster']))) {
        $spotlightList[] = $a;
        $seenDeck[$a['id']] = true;
        if (count($spotlightList) >= 12) break;
    }
}
$initialAnime = $spotlightList[0] ?? null;
?>

<main>
    <!-- Spotlight Hero Section (100% Dynamic from db.anixo.buzz API) -->
    <!-- 3D Infinite Fan-Deck Spotlight Hero Section (Matching Reference Image) -->
    <?php
// Spotlight deck curated directly from verified Hindi catalog

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
                <a href="/browse.php" class="btn-deck-browse">
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
                <a href="/browse.php" class="chip-pill active"><i class="fa-solid fa-fire text-accent-gold mr-1"></i> All</a>
                <a href="/browse.php?type=dub" class="chip-pill"><i class="fa-solid fa-headphones mr-1"></i> Hindi Dubbed</a>
                <a href="/browse.php?status=Currently+Airing" class="chip-pill"><i class="fa-solid fa-bolt mr-1"></i> Currently Airing</a>
                <a href="/browse.php?type=Movie" class="chip-pill"><i class="fa-solid fa-film mr-1"></i> Movies</a>
                <a href="/browse.php?type=sub" class="chip-pill"><i class="fa-solid fa-closed-captioning mr-1"></i> Subbed</a>
                <a href="/browse.php?genre=Action" class="chip-pill">Action</a>
                <a href="/browse.php?genre=Adventure" class="chip-pill">Adventure</a>
                <a href="/browse.php?genre=Fantasy" class="chip-pill">Fantasy</a>
                <a href="/browse.php?genre=Comedy" class="chip-pill">Comedy</a>
                <a href="/browse.php?genre=Romance" class="chip-pill">Romance</a>
            </div>
        </div>
    </div>

    <!-- Section 1: Trending Hindi Dubbed Anime -->
    <section class="container mt-3">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Trending Hindi Dubbed Anime (लोकप्रिय हिंदी डब)</span>
            </h2>
            <a href="/browse.php?type=dub" class="text-secondary small font-weight-bold">
                View All <?= count($allAnime) ?> Dubs <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach ($trendingDubs as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 2: Latest Added Hindi Releases -->
    <section class="container mt-5">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Latest Hindi Dubbed Releases (ताज़ा हिंदी डब रिलीज़)</span>
            </h2>
            <a href="/browse.php?sort=latest" class="text-secondary small font-weight-bold">
                View Latest <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($latestDubs, 0, 12) as $anime): ?>
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
            <a href="/browse.php?sort=score" class="text-secondary small font-weight-bold">
                Browse All <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($topRated, 0, 12) as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 4: Hindi Dubbed Anime Movies -->
    <?php if (!empty($hindiMovies)): ?>
    <section class="container mt-5">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-bar"></span>
                <span>Hindi Dubbed Anime Movies (हिंदी डब फिल्में)</span>
            </h2>
            <a href="/browse.php?type=Movie" class="text-secondary small font-weight-bold">
                View Movies <i class="fa-solid fa-angle-right ml-1"></i>
            </a>
        </div>
        
        <div class="anime-grid">
            <?php foreach (array_slice($hindiMovies, 0, 8) as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section 5: Genre Quick Access -->
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
                <a href="/browse.php?genre=<?= urlencode($genre) ?>" class="genre-tag-item">
                    <?= e($genre) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 6: SEO Authority Content & FAQ for High Google Ranking -->
    <section class="container mt-5 mb-5 seo-authority-section" aria-label="About AniXoHindi and Hindi Dub FAQ">
        <div class="seo-card">
            <h2 class="seo-title">
                <i class="fa-solid fa-circle-play text-accent mr-2"></i>
                AniXoHindi – Ultimate Destination for Free Hindi Dubbed Anime
            </h2>
            <p class="seo-description">
                AniXoHindi is India's premier dedicated streaming platform providing authentic, high-definition <strong>Hindi Dubbed Anime</strong> series and movies. With our comprehensive catalog of 220+ verified titles, anime lovers across India, Nepal, and worldwide can experience legendary sagas like <em>Naruto</em>, <em>Solo Leveling</em>, <em>Bleach: TYBW</em>, and <em>Death Note</em> in their native Hindi language with studio-grade audio clarity.
            </p>

            <div class="seo-features-grid">
                <div class="seo-feature-box">
                    <div class="seo-feature-icon"><i class="fa-solid fa-bolt"></i></div>
                    <h3>1080p Ultra HD Streaming</h3>
                    <p>Adaptive bitrate high-speed players guarantee bufferless playback on Jio, Airtel, and broadband connections.</p>
                </div>
                <div class="seo-feature-box">
                    <div class="seo-feature-icon"><i class="fa-solid fa-language"></i></div>
                    <h3>Verified Hindi Audio</h3>
                    <p>Every episode is screened and labeled with official and fan-favorite Hindi dub tracks.</p>
                </div>
                <div class="seo-feature-box">
                    <div class="seo-feature-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                    <h3>Mobile & Desktop Optimized</h3>
                    <p>Responsive theatre layout, focus mode, and keyboard shortcuts tailored for immersive anime binging.</p>
                </div>
                <div class="seo-feature-box">
                    <div class="seo-feature-icon"><i class="fa-solid fa-bookmark"></i></div>
                    <h3>Google Sync Watchlist</h3>
                    <p>Easily bookmark your current series and resume exactly where you left off with one-click Google Sign-In.</p>
                </div>
            </div>

            <div class="seo-faq-wrapper mt-4">
                <h3 class="seo-faq-title">
                    <i class="fa-solid fa-circle-question text-accent mr-2"></i>
                    Frequently Asked Questions (अक्सर पूछे जाने वाले सवाल)
                </h3>
                <div class="faq-accordion">
                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>Where can I watch Hindi dubbed anime online for free?</span>
                            <i class="fa-solid fa-chevron-down faq-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>You can watch full episodes of popular Hindi dubbed anime for free on <strong>AniXoHindi.cc</strong> with high-speed 1080p Full HD streaming, zero buffering, and no subscription fees.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>Which popular anime series are available in Hindi dub on AniXoHindi?</span>
                            <i class="fa-solid fa-chevron-down faq-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>AniXoHindi features over 220+ verified Hindi dubbed anime series and movies, including <em>Naruto</em>, <em>Naruto Shippuden</em>, <em>Bleach: Thousand-Year Blood War</em>, <em>Solo Leveling</em>, <em>Death Note</em>, <em>Demon Slayer</em>, and <em>Jujutsu Kaisen</em>.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>Do I need to create an account or pay to stream anime?</span>
                            <i class="fa-solid fa-chevron-down faq-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>No payment or credit card is ever required. All anime streams are 100% free. Creating an optional free account allows you to sync your watchlist and bookmark episodes across devices.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>What video quality is available on AniXoHindi?</span>
                            <i class="fa-solid fa-chevron-down faq-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>All episodes stream in adaptive 1080p Full HD and 720p HD with crystal-clear Hindi dub audio channels optimized for both mobile screens and large monitors.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>How frequently are new Hindi dubbed anime episodes updated?</span>
                            <i class="fa-solid fa-chevron-down faq-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>Our catalog is refreshed continuously as newly dubbed Hindi episodes and licensed anime releases arrive.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

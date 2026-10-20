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
    <?php if ($spotlight): ?>
    <section class="hero-spotlight">
        <div class="container">
            <div class="spotlight-card">
                <div class="spotlight-backdrop" style="background-image: url('<?= e($spotlight['banner']) ?>');"></div>
                <div class="spotlight-gradient"></div>
                
                <div class="spotlight-content">
                    <div class="spotlight-tag">
                        <i class="fa-solid fa-fire mr-1"></i> #1 Spotlight Anime
                    </div>
                    <h1 class="spotlight-title"><?= e($spotlight['title']) ?></h1>
                    
                    <div class="spotlight-meta">
                        <?php if (!empty($spotlight['hindi_title'])): ?>
                            <span class="badge-tag badge-dub"><i class="fa-solid fa-language mr-1"></i> <?= e($spotlight['hindi_title']) ?></span>
                        <?php else: ?>
                            <span class="badge-tag badge-dub">Hindi Dubbed</span>
                        <?php endif; ?>
                        <span class="badge-tag badge-hd">HD</span>
                        <span><i class="fa-solid fa-star text-accent mr-1"></i><?= e($spotlight['score']) ?></span>
                        <span>•</span>
                        <span><?= e($spotlight['show_type']) ?></span>
                        <span>•</span>
                        <span><?= e($spotlight['release_year']) ?></span>
                    </div>
                    
                    <p class="spotlight-desc">
                        <?= e($spotlight['synopsis']) ?>
                    </p>
                    
                    <div class="spotlight-buttons">
                        <a href="/watch.php?anime=<?= e($spotlight['slug']) ?>&ep=1" class="btn-primary-custom">
                            <i class="fa-solid fa-play"></i> Watch Episode 1
                        </a>
                        <a href="/watch.php?anime=<?= e($spotlight['slug']) ?>&ep=1" class="btn-secondary-custom">
                            <i class="fa-solid fa-circle-info"></i> Details & Episodes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section 1: Newly Released Anime (Live from db.anixo.buzz) -->
    <section class="container mt-4">
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
            <?php foreach (array_slice($newlyReleased, 0, 8) as $anime): ?>
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
            <?php foreach (array_slice($hindiDubbed, 0, 8) as $anime): ?>
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
            <?php foreach (array_slice($topRated, 0, 8) as $anime): ?>
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

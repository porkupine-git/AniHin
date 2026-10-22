<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$user = currentUser();
$bookmarkedAnime = [];

if ($user) {
    $userId = (int)($user['id'] ?? 0);
    $pdo = function_exists('getHindiPdo') ? getHindiPdo() : null;
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT anime_id, anime_slug, anime_title, anime_poster, created_at FROM bookmarks WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $rawBookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch catalog to enrich with full details (scores, genres, episodes)
        $catalog = buildHindiCatalog();
        $catalogMap = [];
        foreach ($catalog as $item) {
            $catalogMap[$item['slug']] = $item;
            $catalogMap['id_' . ($item['id'] ?? '')] = $item;
        }

        foreach ($rawBookmarks as $bm) {
            $slug = $bm['anime_slug'];
            $idKey = 'id_' . $bm['anime_id'];
            if (isset($catalogMap[$slug])) {
                $bookmarkedAnime[] = $catalogMap[$slug];
            } elseif (isset($catalogMap[$idKey])) {
                $bookmarkedAnime[] = $catalogMap[$idKey];
            } else {
                // Fallback using stored bookmark record
                $bookmarkedAnime[] = [
                    'id' => $bm['anime_id'],
                    'slug' => $bm['anime_slug'],
                    'title' => $bm['anime_title'],
                    'poster' => $bm['anime_poster'],
                    'score' => 8.5,
                    'show_type' => 'TV',
                    'episodes_count' => 1,
                    'release_year' => 'HD',
                    'status' => 'Hindi Dubbed',
                    'genres' => ['Hindi Dub']
                ];
            }
        }
    }
}

$page_title = 'My Watchlist - ' . SITE_NAME;
$page_desc = 'Your personal bookmarked Hindi Dubbed anime collection on AniXoHindi.';
$page_robots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>

<main class="browse-page-wrapper">
    <div class="container py-4">

        <!-- Watchlist Header -->
        <div class="section-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="section-title">
                    <span class="title-bar"></span>
                    <span><i class="fa-regular fa-bookmark mr-2"></i> My Watchlist</span>
                </h1>
                <p class="text-muted small mt-1">Keep track of your favorite Hindi Dubbed anime and continue where you left off.</p>
            </div>
            <?php if ($user && !empty($bookmarkedAnime)): ?>
                <span class="card-chip chip-dub" style="font-size: 0.82rem; padding: 6px 14px;">
                    <i class="fa-solid fa-layer-group mr-1"></i> <?= count($bookmarkedAnime) ?> <?= count($bookmarkedAnime) === 1 ? 'Anime' : 'Animes' ?> Saved
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$user): ?>
            <!-- Non-Logged In State -->
            <div class="browse-empty-state" style="padding: 60px 20px; background: rgba(18, 24, 22, 0.7); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; text-align: center; max-width: 620px; margin: 40px auto;">
                <div class="empty-icon-wrap" style="width: 72px; height: 72px; font-size: 2rem; margin: 0 auto 18px auto; background: rgba(222, 122, 88, 0.12); color: #de7a58; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <h2 style="font-size: 1.45rem; font-weight: 700; margin-bottom: 10px; color: #fff;">Sign in to view your Watchlist</h2>
                <p class="empty-desc" style="color: #9ab; max-width: 440px; margin: 0 auto 24px auto; line-height: 1.5;">
                    Sign in with Google or AniList to save your favorite Hindi anime series, track watch progress, and access them anytime.
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <a href="/auth/google.php" class="btn-primary-custom" style="padding: 10px 22px; font-size: 0.95rem; background: #ea4335; border-color: #ea4335;">
                        <i class="fa-brands fa-google mr-2"></i> Sign in with Google
                    </a>
                    <a href="/auth/anilist.php" class="btn-secondary-custom" style="padding: 10px 22px; font-size: 0.95rem;">
                        <i class="fa-solid fa-a mr-2"></i> Sign in with AniList
                    </a>
                </div>
            </div>

        <?php elseif (empty($bookmarkedAnime)): ?>
            <!-- Empty Watchlist State -->
            <div class="browse-empty-state" style="padding: 70px 20px; background: rgba(18, 24, 22, 0.5); border: 1px dashed rgba(255,255,255,0.1); border-radius: 16px; text-align: center; max-width: 600px; margin: 40px auto;">
                <div class="empty-icon-wrap" style="width: 72px; height: 72px; font-size: 2rem; margin: 0 auto 18px auto; background: rgba(209, 232, 226, 0.08); color: #D1E8E2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-regular fa-bookmark"></i>
                </div>
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 8px; color: #fff;">Your Watchlist is Empty</h2>
                <p class="empty-desc" style="color: #9ab; max-width: 420px; margin: 0 auto 24px auto; line-height: 1.5;">
                    Explore our collection of 220+ verified Hindi Dubbed anime and click the <strong>"Watchlist"</strong> button to save titles here.
                </p>
                <a href="/browse.php" class="btn-primary-custom" style="padding: 12px 28px; font-size: 0.95rem;">
                    <i class="fa-solid fa-compass mr-2"></i> Explore Hindi Anime
                </a>
            </div>

        <?php else: ?>
            <!-- Populated Watchlist Grid -->
            <div class="anime-grid">
                <?php foreach ($bookmarkedAnime as $anime): ?>
                    <?= renderAnimeCard($anime) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

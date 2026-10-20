<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$filter_type = $_GET['type'] ?? 'all';
$filter_genre = $_GET['genre'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_search = $_GET['keyword'] ?? $_GET['q'] ?? '';

$params = [
    'type' => $filter_type,
    'genre' => $filter_genre,
    'status' => $filter_status,
    'q' => $filter_search
];

if ($filter_type === 'dub') {
    $params['hindi_dub'] = true;
    $params['type'] = 'all';
}

$results = getAllAnime($params);

$page_title = 'Browse & Filter Anime - ' . SITE_NAME;
$page_desc = 'Explore our complete library of Hindi Dubbed and Subbed anime series and movies powered by live anime intelligence.';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
    <div class="section-header">
        <h1 class="section-title">
            <span class="title-bar"></span>
            <span>Browse Anime Catalog</span>
        </h1>
        <div class="text-muted small">Showing <?= count($results) ?> results</div>
    </div>

    <!-- Filter Control Box -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 20px; margin-bottom: 30px;">
        <form action="/filter.php" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                <div>
                    <label class="text-muted small font-weight-bold">SEARCH KEYWORD</label>
                    <input type="text" name="keyword" value="<?= e($filter_search) ?>" placeholder="Title, genre, etc..." class="search-input-wrap" style="width: 100%; border: 1px solid var(--border-subtle); color: #fff; background: var(--bg-main); padding: 9px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                </div>

                <div>
                    <label class="text-muted small font-weight-bold">AUDIO FORMAT</label>
                    <select name="type" class="search-input-wrap" style="width: 100%; border: 1px solid var(--border-subtle); color: #fff; background: var(--bg-main); padding: 9px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                        <option value="all" <?= $filter_type == 'all' ? 'selected' : '' ?>>All Formats</option>
                        <option value="dub" <?= $filter_type == 'dub' ? 'selected' : '' ?>>Hindi Dubbed</option>
                        <option value="sub" <?= $filter_type == 'sub' ? 'selected' : '' ?>>English Subbed</option>
                        <option value="TV" <?= $filter_type == 'TV' ? 'selected' : '' ?>>TV Series</option>
                        <option value="Movie" <?= $filter_type == 'Movie' ? 'selected' : '' ?>>Movies</option>
                    </select>
                </div>

                <div>
                    <label class="text-muted small font-weight-bold">GENRE</label>
                    <select name="genre" class="search-input-wrap" style="width: 100%; border: 1px solid var(--border-subtle); color: #fff; background: var(--bg-main); padding: 9px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                        <option value="all">All Genres</option>
                        <?php 
                        $allGenres = [
                            'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 
                            'Horror', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 
                            'Supernatural', 'Super Power', 'Suspense', 'Thriller'
                        ];
                        foreach ($allGenres as $g): 
                        ?>
                            <option value="<?= e($g) ?>" <?= strcasecmp($filter_genre, $g) === 0 ? 'selected' : '' ?>><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-muted small font-weight-bold">STATUS</label>
                    <select name="status" class="search-input-wrap" style="width: 100%; border: 1px solid var(--border-subtle); color: #fff; background: var(--bg-main); padding: 9px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                        <option value="all">All Status</option>
                        <option value="Currently Airing" <?= $filter_status == 'Currently Airing' ? 'selected' : '' ?>>Currently Airing (Releasing)</option>
                        <option value="Finished Airing" <?= $filter_status == 'Finished Airing' ? 'selected' : '' ?>>Finished Airing</option>
                    </select>
                </div>

                <div style="display: flex; align-items: flex-end; gap: 8px;">
                    <button type="submit" class="btn-primary-custom" style="flex: 1; justify-content: center;">
                        <i class="fa-solid fa-filter"></i> Apply
                    </button>
                    <a href="/filter.php" class="btn-secondary-custom" style="padding: 10px 14px;" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Filtered Results Grid -->
    <div class="anime-grid">
        <?php if (empty($results)): ?>
            <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: var(--text-muted);">
                <i class="fa-solid fa-film fa-3x mb-3 text-muted"></i>
                <h3 class="text-white font-weight-bold">No Anime Found</h3>
                <p>Try resetting or changing your filter criteria.</p>
                <a href="/filter.php" class="btn-secondary-custom mt-3">Reset All Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($results as $anime): ?>
                <?= renderAnimeCard($anime) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

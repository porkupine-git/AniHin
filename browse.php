<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

// Sanitize & Parse Filter Parameters
$filter_type = trim($_GET['type'] ?? 'all');
$filter_genre = trim($_GET['genre'] ?? 'all');
$filter_status = trim($_GET['status'] ?? 'all');
$filter_search = trim($_GET['keyword'] ?? $_GET['q'] ?? '');
$filter_sort = trim($_GET['sort'] ?? 'popular');

$params = [
    'type' => $filter_type,
    'genre' => $filter_genre,
    'status' => $filter_status,
    'q' => $filter_search,
    'sort' => $filter_sort
];

if ($filter_type === 'dub') {
    $params['hindi_dub'] = true;
    $params['type'] = 'all';
}

$results = getAllAnime($params);
$totalResults = count($results);

// All Genres List for Select
$allGenres = [
    'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 
    'Horror', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 
    'Supernatural', 'Super Power', 'Suspense', 'Thriller'
];

if ($filter_type === 'dub') {
    $page_title = 'Watch Hindi Dubbed Anime List - 1080p Full HD Free Online';
    $page_desc = 'Browse our complete catalog of 220+ verified Hindi Dubbed anime series and movies. Fast 1080p streaming with authentic Hindi audio on AniXoHindi.';
} elseif ($filter_type === 'Movie') {
    $page_title = 'Watch Hindi Dubbed Anime Movies in 1080p Full HD Online';
    $page_desc = 'Stream popular anime movies in Hindi Dubbed online free with zero buffering on AniXoHindi.';
} elseif ($filter_genre !== 'all') {
    $page_title = 'Best ' . e($filter_genre) . ' Hindi Dubbed Anime Series to Watch Online';
    $page_desc = 'Explore top rated ' . e($filter_genre) . ' anime in Hindi Dubbed on AniXoHindi. Stream episodes in HD 1080p.';
} elseif (!empty($filter_search)) {
    $page_title = 'Search results for "' . e($filter_search) . '" - Hindi Dubbed Anime';
    $page_desc = 'Watch Hindi Dubbed episodes of ' . e($filter_search) . ' in 1080p Full HD on AniXoHindi.';
} else {
    $page_title = 'Browse Hindi Dubbed Anime Catalog - 1080p Full HD Stream';
    $page_desc = 'Browse complete library of 220+ verified Hindi Dubbed anime series and movies. Free high-speed streaming on AniXoHindi.';
}

$page_keywords = 'Hindi Dubbed Anime, Watch Anime Online, Anime in Hindi, Free Anime Streaming, AniXoHindi Catalog';

$page_schema_json = [
    "@context" => "https://schema.org",
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
            "name" => "Browse Anime Catalog",
            "item" => "https://anixohindi.cc/browse.php"
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="browse-page-wrapper">
    <div class="container py-4">

        <!-- Simple Section Header -->
        <div class="section-header mb-3">
            <h1 class="section-title">
                <span class="title-bar"></span>
                <span>Browse Anime Catalog</span>
            </h1>
            <div class="text-muted small">Showing <?= $totalResults ?> results</div>
        </div>

        <!-- Clean, Simple Filter Box (Search Bar + Audio Format, Genre, Status, Sort By) -->
        <div class="browse-simple-box">
            <form action="/browse.php" method="GET" id="browseSimpleForm">
                
                <!-- 1. Search Bar -->
                <div class="browse-search-bar-wrap">
                    <i class="fa-solid fa-magnifying-glass search-leading-icon"></i>
                    <input type="text" 
                           name="keyword" 
                           id="browseKeywordInput"
                           value="<?= e($filter_search) ?>" 
                           placeholder="Search anime title, Hindi name, or keyword..."
                           class="browse-simple-search-input"
                           autocomplete="off">
                    <?php if (!empty($filter_search)): ?>
                        <a href="/browse.php?type=<?= urlencode($filter_type) ?>&genre=<?= urlencode($filter_genre) ?>&status=<?= urlencode($filter_status) ?>&sort=<?= urlencode($filter_sort) ?>" class="btn-clear-search" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- 2. Dropdown Controls Row (Audio Format, Genre, Status, Sort By + Apply/Reset) -->
                <div class="browse-controls-row">
                    
                    <!-- Audio Format -->
                    <div class="browse-control-item">
                        <label for="filter-type-select" class="browse-label">AUDIO FORMAT</label>
                        <div class="browse-select-wrap">
                            <select name="type" id="filter-type-select" class="browse-select-field">
                                <option value="all" <?= $filter_type == 'all' ? 'selected' : '' ?>>All Formats</option>
                                <option value="dub" <?= $filter_type == 'dub' ? 'selected' : '' ?>>Hindi Dubbed</option>
                                <option value="sub" <?= $filter_type == 'sub' ? 'selected' : '' ?>>English Subbed</option>
                                <option value="TV" <?= $filter_type == 'TV' ? 'selected' : '' ?>>TV Series</option>
                                <option value="Movie" <?= $filter_type == 'Movie' ? 'selected' : '' ?>>Movies</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-caret"></i>
                        </div>
                    </div>

                    <!-- Genre -->
                    <div class="browse-control-item">
                        <label for="filter-genre-select" class="browse-label">GENRE</label>
                        <div class="browse-select-wrap">
                            <select name="genre" id="filter-genre-select" class="browse-select-field">
                                <option value="all">All Genres</option>
                                <?php foreach ($allGenres as $g): ?>
                                    <option value="<?= e($g) ?>" <?= strcasecmp($filter_genre, $g) === 0 ? 'selected' : '' ?>>
                                        <?= e($g) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down select-caret"></i>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="browse-control-item">
                        <label for="filter-status-select" class="browse-label">STATUS</label>
                        <div class="browse-select-wrap">
                            <select name="status" id="filter-status-select" class="browse-select-field">
                                <option value="all">All Status</option>
                                <option value="Currently Airing" <?= $filter_status == 'Currently Airing' ? 'selected' : '' ?>>Currently Airing</option>
                                <option value="Finished Airing" <?= $filter_status == 'Finished Airing' ? 'selected' : '' ?>>Finished Airing</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-caret"></i>
                        </div>
                    </div>

                    <!-- Sort By -->
                    <div class="browse-control-item">
                        <label for="filter-sort-select" class="browse-label">SORT BY</label>
                        <div class="browse-select-wrap">
                            <select name="sort" id="filter-sort-select" class="browse-select-field">
                                <option value="popular" <?= $filter_sort == 'popular' ? 'selected' : '' ?>>Most Popular</option>
                                <option value="score" <?= $filter_sort == 'score' ? 'selected' : '' ?>>Highest Rated</option>
                                <option value="latest" <?= $filter_sort == 'latest' ? 'selected' : '' ?>>Latest Release</option>
                                <option value="az" <?= $filter_sort == 'az' ? 'selected' : '' ?>>Title (A - Z)</option>
                                <option value="episodes" <?= $filter_sort == 'episodes' ? 'selected' : '' ?>>Most Episodes</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-caret"></i>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="browse-control-item browse-btns-item">
                        <label class="browse-label" style="opacity: 0; pointer-events: none;">ACTIONS</label>
                        <div class="browse-btns-wrap">
                            <button type="submit" class="btn-simple-apply">
                                <i class="fa-solid fa-filter"></i> Apply
                            </button>
                            <a href="/browse.php" class="btn-simple-reset" title="Reset Filters">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </form>
        </div>

        <!-- Anime Cards Grid -->
        <div class="anime-grid">
            <?php if (empty($results)): ?>
                <div class="browse-empty-state">
                    <div class="empty-icon-wrap">
                        <i class="fa-solid fa-film"></i>
                    </div>
                    <h3 class="empty-title">No Anime Found</h3>
                    <p class="empty-desc">
                        Try resetting or changing your search keyword and filter criteria.
                    </p>
                    <a href="/browse.php" class="btn-primary-custom">
                        <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($results as $anime): ?>
                    <?= renderAnimeCard($anime) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

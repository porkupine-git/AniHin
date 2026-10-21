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

// All Genres List for Pills & Select
$allGenres = [
    'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 
    'Horror', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 
    'Supernatural', 'Super Power', 'Suspense', 'Thriller'
];

// Active filters count for mobile indicator
$activeFiltersCount = 0;
if ($filter_type !== 'all') $activeFiltersCount++;
if ($filter_genre !== 'all') $activeFiltersCount++;
if ($filter_status !== 'all') $activeFiltersCount++;
if (!empty($filter_search)) $activeFiltersCount++;
if ($filter_sort !== 'popular') $activeFiltersCount++;

// Page SEO Metadata
$page_title = 'Browse Anime Catalog (हिंदी डब & Subbed) - ' . SITE_NAME;
$page_desc = 'Explore our full library of Hindi Dubbed, Subbed, and simulcast anime. Filter by genre, format, audio, release status, and top ratings.';
require_once __DIR__ . '/includes/header.php';
?>

<main class="browse-page-wrapper">
    <div class="container py-4">

        <!-- Top Breadcrumb & Studio Header -->
        <div class="browse-header-banner">
            <div class="browse-breadcrumb">
                <a href="/index.php"><i class="fa-solid fa-house"></i> Home</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">Browse Catalog</span>
            </div>

            <div class="browse-title-row">
                <div class="browse-title-block">
                    <h1 class="browse-main-title">
                        <span class="browse-title-accent"></span>
                        <span>Browse Anime Catalog</span>
                        <span class="browse-hindi-sub">एनीमे लाइब्रेरी</span>
                    </h1>
                    <p class="browse-subtitle">
                        Discover 100% dynamic Hindi Dubbed, Subbed series, and anime movies in full HD.
                    </p>
                </div>

                <div class="browse-stats-capsule">
                    <div class="stat-bubble">
                        <span class="stat-num"><?= $totalResults ?></span>
                        <span class="stat-lbl">Titles Found</span>
                    </div>
                    <div class="stat-bubble-sep"></div>
                    <div class="stat-pill-badge">
                        <span class="pulse-dot"></span>
                        <span>Live Catalog</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preset Exploration Ribbon (One-Touch Quick Filters) -->
        <div class="browse-preset-ribbon" aria-label="Quick Category Tabs">
            <div class="preset-track">
                <a href="/browse.php" class="preset-pill <?= ($filter_type === 'all' && $filter_status === 'all' && $filter_sort === 'popular' && empty($filter_search) && $filter_genre === 'all') ? 'active' : '' ?>">
                    <i class="fa-solid fa-fire text-sand"></i>
                    <span>All Anime</span>
                </a>
                <a href="/browse.php?type=dub" class="preset-pill <?= ($filter_type === 'dub') ? 'active' : '' ?>">
                    <i class="fa-solid fa-headphones text-peach"></i>
                    <span>Hindi Dubbed</span>
                    <span class="preset-hot-tag">HI</span>
                </a>
                <a href="/browse.php?status=Currently+Airing" class="preset-pill <?= ($filter_status === 'Currently Airing') ? 'active' : '' ?>">
                    <i class="fa-solid fa-bolt text-teal"></i>
                    <span>Currently Airing</span>
                </a>
                <a href="/browse.php?type=Movie" class="preset-pill <?= ($filter_type === 'Movie') ? 'active' : '' ?>">
                    <i class="fa-solid fa-film"></i>
                    <span>Movies</span>
                </a>
                <a href="/browse.php?sort=score" class="preset-pill <?= ($filter_sort === 'score') ? 'active' : '' ?>">
                    <i class="fa-solid fa-star text-sand"></i>
                    <span>Top Rated</span>
                </a>
                <a href="/browse.php?type=TV" class="preset-pill <?= ($filter_type === 'TV') ? 'active' : '' ?>">
                    <i class="fa-solid fa-tv"></i>
                    <span>TV Series</span>
                </a>
                <a href="/browse.php?type=sub" class="preset-pill <?= ($filter_type === 'sub') ? 'active' : '' ?>">
                    <i class="fa-solid fa-closed-captioning"></i>
                    <span>English Subbed</span>
                </a>
            </div>
        </div>

        <!-- Mobile Filter Expand / Collapse Toggle Button -->
        <div class="browse-mobile-filter-bar">
            <button type="button" class="btn-browse-mobile-toggle" id="btnToggleBrowseFilters">
                <i class="fa-solid fa-sliders"></i>
                <span>Filter & Search</span>
                <?php if ($activeFiltersCount > 0): ?>
                    <span class="filter-count-badge"><?= $activeFiltersCount ?></span>
                <?php endif; ?>
                <i class="fa-solid fa-chevron-down toggle-chevron" id="browseToggleChevron"></i>
            </button>
            <?php if ($activeFiltersCount > 0): ?>
                <a href="/browse.php" class="btn-browse-quick-reset" title="Reset all filters">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </div>

        <!-- Studio Filter Console (Card) -->
        <div class="browse-console-card" id="browseConsoleCard">
            <form action="/browse.php" method="GET" id="browseFilterForm">
                
                <!-- Row 1: Search Keyword Bar -->
                <div class="browse-search-row">
                    <div class="browse-search-input-group">
                        <i class="fa-solid fa-magnifying-glass search-leading-icon"></i>
                        <input type="text" 
                               name="keyword" 
                               id="browseKeywordInput"
                               value="<?= e($filter_search) ?>" 
                               placeholder="Search by anime title, Hindi name (e.g. नारुतो, ब्लीच), or keyword..."
                               class="browse-search-input"
                               autocomplete="off">
                        <?php if (!empty($filter_search)): ?>
                            <button type="button" class="btn-clear-keyword" id="btnClearKeyword" title="Clear search">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 2: Precision Dropdown Selects -->
                <div class="browse-selects-grid">
                    
                    <!-- 1. Audio / Format -->
                    <div class="browse-field-item">
                        <label for="filter-type-select" class="browse-field-label">
                            <i class="fa-solid fa-headphones"></i> AUDIO / FORMAT
                        </label>
                        <div class="browse-custom-select-wrap">
                            <select name="type" id="filter-type-select" class="browse-select-control">
                                <option value="all" <?= $filter_type == 'all' ? 'selected' : '' ?>>All Formats</option>
                                <option value="dub" <?= $filter_type == 'dub' ? 'selected' : '' ?>>🎧 Hindi Dubbed (हिंदी)</option>
                                <option value="sub" <?= $filter_type == 'sub' ? 'selected' : '' ?>>🔤 English Subbed</option>
                                <option value="TV" <?= $filter_type == 'TV' ? 'selected' : '' ?>>📺 TV Series</option>
                                <option value="Movie" <?= $filter_type == 'Movie' ? 'selected' : '' ?>>🎬 Movies</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow-icon"></i>
                        </div>
                    </div>

                    <!-- 2. Genre -->
                    <div class="browse-field-item">
                        <label for="filter-genre-select" class="browse-field-label">
                            <i class="fa-solid fa-tags"></i> GENRE
                        </label>
                        <div class="browse-custom-select-wrap">
                            <select name="genre" id="filter-genre-select" class="browse-select-control">
                                <option value="all">All Genres</option>
                                <?php foreach ($allGenres as $g): ?>
                                    <option value="<?= e($g) ?>" <?= strcasecmp($filter_genre, $g) === 0 ? 'selected' : '' ?>>
                                        <?= e($g) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow-icon"></i>
                        </div>
                    </div>

                    <!-- 3. Airing Status -->
                    <div class="browse-field-item">
                        <label for="filter-status-select" class="browse-field-label">
                            <i class="fa-solid fa-signal"></i> STATUS
                        </label>
                        <div class="browse-custom-select-wrap">
                            <select name="status" id="filter-status-select" class="browse-select-control">
                                <option value="all">All Status</option>
                                <option value="Currently Airing" <?= $filter_status == 'Currently Airing' ? 'selected' : '' ?>>⚡ Currently Airing</option>
                                <option value="Finished Airing" <?= $filter_status == 'Finished Airing' ? 'selected' : '' ?>>✅ Finished Airing</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow-icon"></i>
                        </div>
                    </div>

                    <!-- 4. Sort Order -->
                    <div class="browse-field-item">
                        <label for="filter-sort-select" class="browse-field-label">
                            <i class="fa-solid fa-arrow-down-wide-short"></i> SORT BY
                        </label>
                        <div class="browse-custom-select-wrap">
                            <select name="sort" id="filter-sort-select" class="browse-select-control">
                                <option value="popular" <?= $filter_sort == 'popular' ? 'selected' : '' ?>>🔥 Most Popular</option>
                                <option value="score" <?= $filter_sort == 'score' ? 'selected' : '' ?>>⭐ Highest Rated</option>
                                <option value="latest" <?= $filter_sort == 'latest' ? 'selected' : '' ?>>📅 Latest Release</option>
                                <option value="az" <?= $filter_sort == 'az' ? 'selected' : '' ?>>🔤 Title (A - Z)</option>
                                <option value="episodes" <?= $filter_sort == 'episodes' ? 'selected' : '' ?>>🎬 Most Episodes</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow-icon"></i>
                        </div>
                    </div>

                    <!-- 5. Action Buttons -->
                    <div class="browse-field-item browse-actions-item">
                        <div class="browse-btn-row">
                            <button type="submit" class="btn-browse-apply">
                                <i class="fa-solid fa-filter"></i> Apply
                            </button>
                            <a href="/browse.php" class="btn-browse-reset" title="Reset All Filters">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Genre Quick-Picks Cloud (Collapsible) -->
                <div class="browse-genre-cloud-row">
                    <span class="genre-cloud-label">Quick Genres:</span>
                    <div class="genre-cloud-list">
                        <?php 
                        $quickGenres = ['Action', 'Adventure', 'Fantasy', 'Comedy', 'Drama', 'Romance', 'Sci-Fi', 'Supernatural'];
                        foreach ($quickGenres as $qg): 
                            $isActive = (strcasecmp($filter_genre, $qg) === 0);
                        ?>
                            <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['genre' => $isActive ? 'all' : $qg])) ?>" 
                               class="genre-cloud-tag <?= $isActive ? 'active' : '' ?>">
                                <?= e($qg) ?>
                                <?php if ($isActive): ?><i class="fa-solid fa-xmark ml-1"></i><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </form>
        </div>

        <!-- Active Filter Dismissible Pills Bar -->
        <?php if ($activeFiltersCount > 0): ?>
            <div class="browse-active-tags-bar">
                <span class="active-tags-title"><i class="fa-solid fa-filter text-peach"></i> Active Filters:</span>
                <div class="active-tags-list">
                    <?php if (!empty($filter_search)): ?>
                        <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['keyword' => '', 'q' => ''])) ?>" class="active-filter-tag">
                            <span>Keyword: "<?= e($filter_search) ?>"</span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($filter_type !== 'all'): ?>
                        <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['type' => 'all'])) ?>" class="active-filter-tag">
                            <span>Format: <?= e($filter_type === 'dub' ? 'Hindi Dubbed' : ($filter_type === 'sub' ? 'Subbed' : $filter_type)) ?></span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($filter_genre !== 'all'): ?>
                        <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['genre' => 'all'])) ?>" class="active-filter-tag">
                            <span>Genre: <?= e($filter_genre) ?></span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($filter_status !== 'all'): ?>
                        <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['status' => 'all'])) ?>" class="active-filter-tag">
                            <span>Status: <?= e($filter_status) ?></span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($filter_sort !== 'popular'): ?>
                        <a href="/browse.php?<?= http_build_query(array_merge($_GET, ['sort' => 'popular'])) ?>" class="active-filter-tag">
                            <span>Sort: <?= e(ucfirst($filter_sort)) ?></span>
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>

                    <a href="/browse.php" class="active-filter-clear-all">
                        <i class="fa-solid fa-trash-can mr-1"></i> Clear All
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Results Counter & Fast Sorting Bar -->
        <div class="browse-results-bar">
            <div class="results-left-meta">
                <span class="results-count-text">
                    Showing <strong class="text-white"><?= $totalResults ?></strong> <?= ($totalResults === 1) ? 'anime' : 'anime titles' ?>
                    <?php if (!empty($filter_search)): ?>
                        for <span class="results-highlight-query">"<?= e($filter_search) ?>"</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="results-right-meta">
                <span class="text-muted small">Live catalog • 1080p Ultra HD</span>
            </div>
        </div>

        <!-- Anime Grid -->
        <div class="anime-grid browse-catalog-grid" id="browseCatalogGrid">
            <?php if (empty($results)): ?>
                <div class="browse-empty-state">
                    <div class="empty-icon-wrap">
                        <i class="fa-solid fa-film"></i>
                    </div>
                    <h3 class="empty-title">No Anime Matches Found</h3>
                    <p class="empty-desc">
                        No titles matched your current filter criteria. Try searching with broader keywords or resetting your filters.
                    </p>
                    <div class="empty-action-btns">
                        <a href="/browse.php" class="btn-primary-custom">
                            <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                        </a>
                        <a href="/browse.php?type=dub" class="btn-secondary-custom">
                            <i class="fa-solid fa-headphones"></i> Explore Hindi Dubbed
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($results as $anime): ?>
                    <?= renderAnimeCard($anime) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
// Mobile Filter Collapse Logic
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('btnToggleBrowseFilters');
    const consoleCard = document.getElementById('browseConsoleCard');
    const chevron = document.getElementById('browseToggleChevron');
    const clearKeywordBtn = document.getElementById('btnClearKeyword');
    const keywordInput = document.getElementById('browseKeywordInput');

    if (toggleBtn && consoleCard) {
        toggleBtn.addEventListener('click', function() {
            consoleCard.classList.toggle('mobile-open');
            if (chevron) {
                chevron.classList.toggle('rotated');
            }
        });
    }

    if (clearKeywordBtn && keywordInput) {
        clearKeywordBtn.addEventListener('click', function() {
            keywordInput.value = '';
            keywordInput.focus();
            clearKeywordBtn.style.display = 'none';
        });

        keywordInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                clearKeywordBtn.style.display = 'flex';
            } else {
                clearKeywordBtn.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

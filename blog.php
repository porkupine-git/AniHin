<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/blog_helper.php';

$animeParam = trim($_GET['anime'] ?? $_GET['slug'] ?? $_GET['id'] ?? '');

// =========================================================================
// MODE 1: INDIVIDUAL ANIME BLOG ARTICLE (1400+ Words Comprehensive Guide)
// =========================================================================
if (!empty($animeParam)) {
    $anime = getAnimeBySlug($animeParam);
    if (!$anime) {
        header('Location: /blog.php');
        exit;
    }

    $anime = enrichAnimeDetails($anime);
    $blog = generateAnimeBlogContent($anime);

    $page_title = $blog['article_title'];
    $page_desc = $blog['meta_description'];
    $page_keywords = $blog['keywords'];
    $canonicalUrl = 'https://anixohindi.cc/blog.php?anime=' . urlencode($anime['slug']);
    $og_type = 'article';
    $og_image = !empty($anime['banner']) ? $anime['banner'] : $anime['poster'];

    // Schema.org Structured Data: Article + Breadcrumbs + FAQPage
    $page_schema_json = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "Article",
                "@id" => $canonicalUrl . "#article",
                "isPartOf" => [
                    "@type" => "WebPage",
                    "@id" => $canonicalUrl
                ],
                "headline" => $blog['article_title'],
                "description" => $blog['meta_description'],
                "image" => $og_image,
                "wordCount" => $blog['word_count'],
                "inLanguage" => "hi-IN",
                "datePublished" => date('c', strtotime($anime['created_at'] ?? '2026-01-01')),
                "dateModified" => date('c'),
                "author" => [
                    "@type" => "Organization",
                    "name" => "AniXoHindi Editorial Desk",
                    "url" => "https://anixohindi.cc/"
                ],
                "publisher" => [
                    "@type" => "Organization",
                    "name" => "AniXoHindi",
                    "url" => "https://anixohindi.cc/",
                    "logo" => [
                        "@type" => "ImageObject",
                        "url" => "https://anixohindi.cc/assets/img/logo.png"
                    ]
                ],
                "mainEntityOfPage" => $canonicalUrl
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
                        "name" => "Anime Guides & Blog",
                        "item" => "https://anixohindi.cc/blog.php"
                    ],
                    [
                        "@type" => "ListItem",
                        "position" => 3,
                        "name" => $anime['title'] . " Hindi Dubbed Guide",
                        "item" => $canonicalUrl
                    ]
                ]
            ],
            [
                "@type" => "FAQPage",
                "mainEntity" => array_map(function($faq) {
                    return [
                        "@type" => "Question",
                        "name" => $faq['q'],
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => $faq['a']
                        ]
                    ];
                }, $blog['faq_items'])
            ]
        ]
    ];

    require_once __DIR__ . '/includes/header.php';

    // Fetch 3 related anime for recommendations
    $allCatalog = getAllAnime(['sort' => 'popular']);
    $relatedAnime = [];
    foreach ($allCatalog as $cand) {
        if ($cand['slug'] !== $anime['slug']) {
            $relatedAnime[] = $cand;
            if (count($relatedAnime) >= 3) break;
        }
    }
    ?>

    <main class="blog-article-page">
        <!-- Hero Article Header -->
        <section class="blog-hero" style="background-image: linear-gradient(180deg, rgba(16,23,22,0.85) 0%, rgba(16,23,22,0.98) 100%), url('<?= e($og_image) ?>');">
            <div class="container">
                <nav class="blog-breadcrumbs" aria-label="Breadcrumb">
                    <a href="/index.php"><i class="fa-solid fa-house"></i> Home</a>
                    <span class="bc-sep"><i class="fa-solid fa-angle-right"></i></span>
                    <a href="/blog.php">Anime Guides & Blog</a>
                    <span class="bc-sep"><i class="fa-solid fa-angle-right"></i></span>
                    <span class="bc-current"><?= e($anime['title']) ?> Guide</span>
                </nav>

                <div class="blog-hero-content">
                    <div class="blog-badges">
                        <span class="blog-badge badge-dub"><i class="fa-solid fa-headphones"></i> Hindi Dubbed</span>
                        <span class="blog-badge badge-quality"><i class="fa-solid fa-circle-check"></i> 1080p Full HD</span>
                        <span class="blog-badge badge-score"><i class="fa-solid fa-star"></i> <?= e($blog['score']) ?>/10</span>
                        <span class="blog-badge badge-year"><?= e($blog['year']) ?></span>
                    </div>

                    <h1 class="blog-article-h1"><?= e($blog['article_title']) ?></h1>

                    <div class="blog-meta-row">
                        <div class="blog-author">
                            <i class="fa-solid fa-user-pen text-accent"></i>
                            <span>AniXoHindi Editorial Desk</span>
                        </div>
                        <span class="blog-meta-divider">•</span>
                        <div class="blog-date">
                            <i class="fa-regular fa-calendar"></i>
                            <span>Updated <?= date('F Y') ?></span>
                        </div>
                        <span class="blog-meta-divider">•</span>
                        <div class="blog-reading-time">
                            <i class="fa-regular fa-clock"></i>
                            <span><?= e($blog['read_minutes']) ?> min read (~<?= e($blog['word_count']) ?> words)</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mt-4 mb-5">
            <div class="blog-grid-layout">
                <!-- Main Article Column -->
                <article class="blog-main-content">
                    <!-- Top Prominent Call-to-Action Box (Above the fold) -->
                    <div class="blog-cta-box top-cta">
                        <div class="cta-text">
                            <h3><i class="fa-solid fa-bolt text-accent"></i> Watch <?= e($anime['title']) ?> in Hindi Dubbed Online</h3>
                            <p>Stream all <?= e($blog['ep_count']) ?> episodes in ultra-crisp 1080p resolution with crystal-clear Hindi dub audio on AniXoHindi.</p>
                        </div>
                        <a href="/watch.php?anime=<?= urlencode($anime['slug']) ?>&ep=1" class="btn-blog-cta pulse-cta">
                            <i class="fa-solid fa-play mr-2"></i> Stream Episode 1 Now
                        </a>
                    </div>

                    <!-- Quick Facts Overview Table -->
                    <div class="blog-specs-card">
                        <h3 class="specs-title"><i class="fa-solid fa-circle-info text-accent"></i> Quick Series Overview & Metadata</h3>
                        <div class="specs-grid">
                            <div class="spec-item">
                                <span class="spec-label">Official Title</span>
                                <span class="spec-val"><?= e($anime['title']) ?></span>
                            </div>
                            <?php if (!empty($blog['english_title'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">English Title</span>
                                <span class="spec-val"><?= e($blog['english_title']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="spec-item">
                                <span class="spec-label">Audio Dub</span>
                                <span class="spec-val text-accent-gold">Hindi Dubbed (Dual Audio / Multi-Audio)</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Episodes Available</span>
                                <span class="spec-val"><?= e($blog['ep_count']) ?> Episodes in 1080p FHD</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Animation Studio</span>
                                <span class="spec-val"><?= e($blog['studio']) ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Primary Genres</span>
                                <span class="spec-val"><?= e(implode(', ', $blog['genre_list'])) ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Release Year</span>
                                <span class="spec-val"><?= e($blog['year']) ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Viewer Score</span>
                                <span class="spec-val text-accent-peach"><?= e($blog['score']) ?> / 10 (Popular)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 1: Plot Overview & Deep Narrative Breakdown -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-book-open text-accent mr-2"></i> Storyline & Narrative Breakdown (कहानी की विस्तृत रूपरेखा)</h2>
                        <div class="blog-prose">
                            <p><?= nl2br(e($blog['synopsis'])) ?></p>
                            <p><?= $blog['plot_deep_dive']['premise'] ?></p>
                            <p><?= $blog['plot_deep_dive']['lore'] ?></p>
                            <p><?= $blog['plot_deep_dive']['stakes'] ?></p>
                        </div>
                    </section>

                    <!-- Section 2: In-Depth Hindi Dubbing Review -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-headphones text-accent mr-2"></i> Comprehensive Hindi Dub Review: Voice Acting & Localization Quality</h2>
                        <div class="blog-prose">
                            <p><?= $blog['dub_review']['localization'] ?></p>
                            <div class="blog-highlight-box">
                                <div class="highlight-icon"><i class="fa-solid fa-quote-left"></i></div>
                                <p><?= $blog['dub_review']['acting'] ?></p>
                            </div>
                            <p><?= $blog['dub_review']['audiomix'] ?></p>
                        </div>
                    </section>

                    <!-- Section 3: Hindi Dub vs Japanese Sub Comparison Table -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-scale-balanced text-accent mr-2"></i> Hindi Dub vs. Japanese Sub: Which Version Should You Watch?</h2>
                        <p class="text-secondary small mb-3">A practical comparison for Indian viewers deciding between Hindi dubbed audio and original Japanese with subtitles.</p>
                        <div class="comparison-table-wrapper">
                            <table class="comparison-table">
                                <thead>
                                    <tr>
                                        <th>Comparison Factor</th>
                                        <th>Hindi Dubbed Version</th>
                                        <th>Japanese Subbed Version</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blog['comparison'] as $row): ?>
                                    <tr>
                                        <td class="comp-feature"><?= e($row['feature']) ?></td>
                                        <td class="comp-dub"><i class="fa-solid fa-check text-accent mr-1"></i> <?= e($row['dub']) ?></td>
                                        <td class="comp-sub"><?= e($row['sub']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Section 4: Key Characters & Voice Cast -->
                    <?php if (!empty($blog['characters'])): ?>
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-users text-accent mr-2"></i> Key Characters & Cast Guide (पात्र और आवाज कलाकार)</h2>
                        <p class="text-secondary small mb-3">Meet the core cast of characters driving the narrative in <?= e($anime['title']) ?>.</p>
                        <div class="characters-grid">
                            <?php foreach ($blog['characters'] as $char): ?>
                            <div class="character-card">
                                <?php if (!empty($char['image'])): ?>
                                <img src="<?= e($char['image']) ?>" alt="<?= e($char['name']) ?>" loading="lazy" class="char-img">
                                <?php else: ?>
                                <div class="char-img-placeholder"><i class="fa-solid fa-user"></i></div>
                                <?php endif; ?>
                                <div class="char-info">
                                    <h4 class="char-name"><?= e($char['name']) ?></h4>
                                    <span class="char-role"><?= e($char['role']) ?></span>
                                    <span class="char-va"><i class="fa-solid fa-microphone"></i> <?= e($char['va']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Section 5: Complete Episode Guide & Streaming Playlist -->
                    <?php if (!empty($anime['episodes'])): ?>
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-list-ol text-accent mr-2"></i> Complete Episode Guide & Streaming Links</h2>
                        <p class="text-secondary small mb-3">Click on any episode to launch our buffer-free 1080p video player with authentic Hindi audio.</p>
                        <div class="episodes-blog-list">
                            <?php foreach (array_slice($anime['episodes'], 0, 16) as $ep): ?>
                            <a href="/watch.php?anime=<?= urlencode($anime['slug']) ?>&ep=<?= (int)$ep['number'] ?>" class="ep-blog-row">
                                <div class="ep-row-left">
                                    <span class="ep-num-pill">EP <?= (int)$ep['number'] ?></span>
                                    <span class="ep-title-text"><?= !empty($ep['title']) ? e($ep['title']) : e($anime['title'] . ' Episode ' . $ep['number'] . ' (Hindi Dubbed)') ?></span>
                                </div>
                                <span class="ep-row-btn"><i class="fa-solid fa-play"></i> Watch Episode <?= (int)$ep['number'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Section 6: How to Stream Guide -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-circle-play text-accent mr-2"></i> How to Watch <?= e($anime['title']) ?> in Hindi Dubbed on AniXoHindi</h2>
                        <div class="streaming-steps-card">
                            <ul class="streaming-steps-list">
                                <?php foreach ($blog['streaming_steps'] as $step): ?>
                                <li><i class="fa-solid fa-circle-check text-accent mr-2"></i> <?= $step ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </section>

                    <!-- Mid / Bottom High-Impact Call to Action Cockpit -->
                    <div class="blog-cta-box bottom-cta">
                        <div class="cta-inner">
                            <span class="cta-tag">1080p Ultra High Definition</span>
                            <h2>Ready to Watch <?= e($anime['title']) ?> in Hindi Dubbed?</h2>
                            <p>Stream all episodes in full 1080p HD without interruptions, slow buffering, or spammy redirects on AniXoHindi.</p>
                            <a href="/watch.php?anime=<?= urlencode($anime['slug']) ?>&ep=1" class="btn-blog-cta btn-large pulse-cta">
                                <i class="fa-solid fa-circle-play mr-2"></i> Start Watching Episode 1 in Hindi (1080p)
                            </a>
                        </div>
                    </div>

                    <!-- Section 7: 8 Search-Intent FAQs -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-circle-question text-accent mr-2"></i> Frequently Asked Questions About <?= e($anime['title']) ?> Hindi Dub</h2>
                        <div class="faq-accordion">
                            <?php foreach ($blog['faq_items'] as $faq): ?>
                            <details class="faq-item">
                                <summary class="faq-question">
                                    <span><?= e($faq['q']) ?></span>
                                    <i class="fa-solid fa-chevron-down faq-chevron"></i>
                                </summary>
                                <div class="faq-answer">
                                    <p><?= e($faq['a']) ?></p>
                                </div>
                            </details>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Section 8: Related Search Keywords & LSI Tags Cloud -->
                    <section class="blog-section">
                        <h2><i class="fa-solid fa-tags text-accent mr-2"></i> Related Search Queries & Hindi Anime Tags</h2>
                        <div class="lsi-tags-cloud">
                            <?php foreach ($blog['lsi_keywords'] as $tag): ?>
                            <a href="/browse.php?keyword=<?= urlencode($tag) ?>" class="lsi-tag-pill"><?= e($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </article>

                <!-- Sidebar Column -->
                <aside class="blog-sidebar">
                    <!-- Anime Poster Card -->
                    <div class="sidebar-card poster-card">
                        <img src="<?= e($anime['poster'] ?? $og_image) ?>" alt="<?= e($anime['title']) ?>" class="sidebar-poster-img">
                        <div class="sidebar-poster-info">
                            <h3><?= e($anime['title']) ?></h3>
                            <div class="sidebar-genres">
                                <?php foreach ($blog['genre_list'] as $g): ?>
                                <a href="/browse.php?genre=<?= urlencode($g) ?>" class="sidebar-genre-pill"><?= e($g) ?></a>
                                <?php endforeach; ?>
                            </div>
                            <a href="/watch.php?anime=<?= urlencode($anime['slug']) ?>&ep=1" class="btn-sidebar-watch">
                                <i class="fa-solid fa-play mr-1"></i> Stream Hindi Dubbed
                            </a>
                        </div>
                    </div>

                    <!-- Recommended Anime Recommendations -->
                    <div class="sidebar-card">
                        <h4 class="sidebar-title"><i class="fa-solid fa-fire text-accent mr-2"></i> Recommended Hindi Anime</h4>
                        <div class="sidebar-related-list">
                            <?php foreach ($relatedAnime as $rel): ?>
                            <a href="/blog.php?anime=<?= urlencode($rel['slug']) ?>" class="sidebar-rel-item">
                                <img src="<?= e($rel['poster']) ?>" alt="<?= e($rel['title']) ?>" class="sidebar-rel-thumb">
                                <div class="sidebar-rel-details">
                                    <span class="sidebar-rel-title"><?= e($rel['title']) ?></span>
                                    <span class="sidebar-rel-meta"><?= e($rel['release_year'] ?? '') ?> • Hindi Dub</span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <?php
    require_once __DIR__ . '/includes/footer.php';
} else {
    // =========================================================================
    // MODE 2: BLOG DIRECTORY / HUB PAGE (All 220+ Anime Articles)
    // =========================================================================
    $page_title = 'Hindi Dubbed Anime Blog & Complete Streaming Guides | AniXoHindi';
    $page_desc = 'Explore in-depth Hindi Dubbed anime reviews, episode guides, storyline breakdowns, and streaming guides for over 220+ verified anime on AniXoHindi.';
    $page_keywords = 'Anime Blog, Hindi Anime Guides, Anime in Hindi Reviews, Watch Anime in Hindi, AniXoHindi Blog';
    $canonicalUrl = 'https://anixohindi.cc/blog.php';

    $page_schema_json = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "AniXoHindi Anime Blog & Streaming Guides",
        "description" => $page_desc,
        "url" => $canonicalUrl,
        "breadcrumb" => [
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
                    "name" => "Anime Guides & Blog",
                    "item" => "https://anixohindi.cc/blog.php"
                ]
            ]
        ]
    ];

    require_once __DIR__ . '/includes/header.php';

    $allCatalog = getAllAnime(['sort' => 'popular']);
    $searchQuery = trim($_GET['q'] ?? '');
    $filterGenre = trim($_GET['genre'] ?? '');

    if (!empty($searchQuery)) {
        $allCatalog = array_filter($allCatalog, function($item) use ($searchQuery) {
            return stripos($item['title'], $searchQuery) !== false;
        });
    }
    if (!empty($filterGenre)) {
        $allCatalog = array_filter($allCatalog, function($item) use ($filterGenre) {
            $genres = is_array($item['genres'] ?? '') ? $item['genres'] : explode(',', (string)($item['genres'] ?? ''));
            foreach ($genres as $g) {
                if (strcasecmp(trim($g), $filterGenre) === 0) return true;
            }
            return false;
        });
    }
    ?>

    <main class="blog-hub-page">
        <section class="blog-hub-hero">
            <div class="container text-center">
                <span class="hub-pill"><i class="fa-solid fa-newspaper text-accent mr-1"></i> Editorial & Guides</span>
                <h1 class="hub-title">Hindi Dubbed Anime Blog & Streaming Guides</h1>
                <p class="hub-subtitle">Explore comprehensive storylines, reviews, character guides, and direct streaming links for 220+ verified Hindi Dubbed anime.</p>
                
                <form action="/blog.php" method="GET" class="hub-search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search anime guide (e.g. Solo Leveling, Naruto, Bleach)...">
                    <button type="submit" class="btn-hub-search">Search</button>
                </form>
            </div>
        </section>

        <section class="container mt-5 mb-5">
            <div class="hub-header-row">
                <h2>Available Anime Guides (<?= count($allCatalog) ?>)</h2>
                <div class="hub-genres">
                    <a href="/blog.php" class="hub-genre-pill <?= empty($filterGenre) ? 'active' : '' ?>">All</a>
                    <?php foreach (['Action', 'Adventure', 'Fantasy', 'Supernatural', 'Thriller'] as $g): ?>
                    <a href="/blog.php?genre=<?= urlencode($g) ?>" class="hub-genre-pill <?= $filterGenre === $g ? 'active' : '' ?>"><?= e($g) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="blog-cards-grid">
                <?php foreach ($allCatalog as $item): ?>
                <div class="blog-card">
                    <div class="blog-card-thumb">
                        <img src="<?= e(!empty($item['banner']) ? $item['banner'] : $item['poster']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                        <span class="blog-card-badge">Hindi Dubbed</span>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            <span><i class="fa-regular fa-clock"></i> 8 min read</span>
                            <span>•</span>
                            <span><?= e($item['release_year'] ?? '2024') ?></span>
                        </div>
                        <h3 class="blog-card-title"><?= e($item['title']) ?> in Hindi Dubbed: Complete Guide</h3>
                        <p class="blog-card-excerpt">
                            <?= e(substr(strip_tags($item['synopsis'] ?? ($item['title'] . ' is a verified Hindi dubbed anime series available in 1080p Full HD.')), 0, 110)) ?>...
                        </p>
                        <div class="blog-card-actions">
                            <a href="/blog.php?anime=<?= urlencode($item['slug']) ?>" class="btn-read-guide">
                                Read Guide <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="/watch.php?anime=<?= urlencode($item['slug']) ?>&ep=1" class="btn-watch-direct" title="Watch in 1080p">
                                <i class="fa-solid fa-play"></i> Watch
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php 
    require_once __DIR__ . '/includes/footer.php';
}

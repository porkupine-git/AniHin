<?php
/**
 * Database Connection & 100% Dynamic API Catalog Provider
 * All anime data is fetched live from https://db.anixo.buzz (AniList, AniKoto & AniZip)
 * Zero hardcoded anime.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/anixo_api.php';

$db_connected = false;
$conn = null;

// Fast non-blocking check: avoids 4-second Windows localhost connection timeout when MySQL is offline
if (empty($_SESSION['db_offline']) || (time() - ($_SESSION['db_offline'] ?? 0)) > 60) {
    $fp = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 0.05);
    if ($fp) {
        fclose($fp);
        unset($_SESSION['db_offline']);
        try {
            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME);
            if ($conn && !$conn->connect_error) {
                $conn->set_charset("utf8mb4");
                $db_connected = true;
            }
        } catch (Exception $e) {
            $db_connected = false;
        }
    } else {
        $_SESSION['db_offline'] = time();
    }
}

// In-memory cache of unified API catalog for instantaneous page execution
$GLOBAL_CATALOG_CACHE = null;

/**
 * Build 100% dynamic catalog from db.anixo.buzz API
 */
function buildUnifiedCatalog() {
    global $GLOBAL_CATALOG_CACHE;
    if ($GLOBAL_CATALOG_CACHE !== null) {
        return $GLOBAL_CATALOG_CACHE;
    }

    $catalog = [];
    $seen = [];

    // 1. Fetch Newly Released / Currently Airing anime from db.anixo.buzz
    $releasingRaw = getAnixoNewlyReleased(24);
    foreach ($releasingRaw as $raw) {
        $id = $raw['anilist_id'] ?? null;
        if ($id && !isset($seen[$id])) {
            $formatted = formatAnixoItem($raw);
            $formatted['is_releasing'] = true;
            $seen[$id] = true;
            $seen[$formatted['slug']] = true;
            $catalog[] = $formatted;
        }
    }

    // 2. Fetch Hindi Dubbed Localized anime from db.anixo.buzz (AniZip)
    $hindiRaw = getAnixoHindiDubbed(35);
    foreach ($hindiRaw as $raw) {
        $id = $raw['anilist_id'] ?? null;
        if ($id && !isset($seen[$id])) {
            $formatted = formatAnixoItem($raw);
            $formatted['is_hindi_dubbed'] = 1;
            $seen[$id] = true;
            $seen[$formatted['slug']] = true;
            $catalog[] = $formatted;
        } elseif ($id && isset($seen[$id])) {
            // Enrich existing item with Hindi title
            foreach ($catalog as &$existing) {
                if ($existing['id'] == $id && empty($existing['hindi_title']) && !empty($raw['hindi_title'])) {
                    $existing['hindi_title'] = $raw['hindi_title'];
                    $existing['is_hindi_dubbed'] = 1;
                    break;
                }
            }
            unset($existing);
        }
    }

    // 3. Fetch Most Popular anime from db.anixo.buzz
    $popularRaw = getAnixoPopular(24);
    foreach ($popularRaw as $raw) {
        $id = $raw['anilist_id'] ?? null;
        if ($id && !isset($seen[$id])) {
            $formatted = formatAnixoItem($raw);
            $seen[$id] = true;
            $seen[$formatted['slug']] = true;
            $catalog[] = $formatted;
        }
    }

    // 4. Fetch Top Rated anime from db.anixo.buzz
    $topRatedRaw = getAnixoTopRated(16);
    foreach ($topRatedRaw as $raw) {
        $id = $raw['anilist_id'] ?? null;
        if ($id && !isset($seen[$id])) {
            $formatted = formatAnixoItem($raw);
            $seen[$id] = true;
            $seen[$formatted['slug']] = true;
            $catalog[] = $formatted;
        }
    }

    $GLOBAL_CATALOG_CACHE = $catalog;
    return $catalog;
}

/**
 * Get all anime matching filter criteria
 */
function getAllAnime($filter = []) {
    global $db_connected, $conn;

    // Use MySQL database if connected and populated
    if ($db_connected) {
        $sql = "SELECT * FROM anime WHERE 1=1";
        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $sql .= " AND show_type = '" . $conn->real_escape_string($filter['type']) . "'";
        }
        if (!empty($filter['genre']) && $filter['genre'] !== 'all') {
            $sql .= " AND genres LIKE '%" . $conn->real_escape_string($filter['genre']) . "%'";
        }
        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $sql .= " AND status = '" . $conn->real_escape_string($filter['status']) . "'";
        }
        if (isset($filter['hindi_dub']) && $filter['hindi_dub']) {
            $sql .= " AND is_hindi_dubbed = 1";
        }
        $sql .= " ORDER BY score DESC";
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            $list = [];
            while ($row = $res->fetch_assoc()) {
                $list[] = $row;
            }
            return $list;
        }
    }

    // Pure dynamic API catalog from db.anixo.buzz
    $list = buildUnifiedCatalog();

    // Filter by audio / format
    if (!empty($filter['type']) && $filter['type'] !== 'all') {
        if ($filter['type'] === 'dub') {
            $list = array_filter($list, fn($a) => !empty($a['is_hindi_dubbed']) || !empty($a['hindi_title']));
        } elseif ($filter['type'] === 'sub') {
            $list = array_filter($list, fn($a) => !empty($a['is_subbed']));
        } else {
            $list = array_filter($list, fn($a) => strcasecmp($a['show_type'] ?? '', $filter['type']) === 0);
        }
    }

    // Filter by genre
    if (!empty($filter['genre']) && $filter['genre'] !== 'all') {
        $g = strtolower($filter['genre']);
        $list = array_filter($list, fn($a) => stripos($a['genres'] ?? '', $g) !== false);
    }

    // Filter by status (Currently Airing vs Finished Airing)
    if (!empty($filter['status']) && $filter['status'] !== 'all') {
        $list = array_filter($list, fn($a) => strcasecmp($a['status'] ?? '', $filter['status']) === 0);
    }

    // Filter by Hindi dub explicitly
    if (!empty($filter['hindi_dub'])) {
        $list = array_filter($list, fn($a) => !empty($a['is_hindi_dubbed']) || !empty($a['hindi_title']));
    }

    // Filter by category
    if (!empty($filter['category'])) {
        if ($filter['category'] === 'releasing') {
            $list = array_filter($list, fn($a) => ($a['status'] ?? '') === 'Currently Airing');
        } elseif ($filter['category'] === 'hindi') {
            $list = array_filter($list, fn($a) => !empty($a['is_hindi_dubbed']) || !empty($a['hindi_title']));
        }
    }

    // Search query filter
    if (!empty($filter['q'])) {
        $q = strtolower(trim($filter['q']));
        $list = array_filter($list, function($a) use ($q) {
            return str_contains(strtolower($a['title'] ?? ''), $q) ||
                   str_contains(strtolower($a['hindi_title'] ?? ''), $q) ||
                   str_contains(strtolower($a['japanese_title'] ?? ''), $q) ||
                   str_contains(strtolower($a['genres'] ?? ''), $q);
        });
    }

    // Sort order support
    $sort = $filter['sort'] ?? 'popular';
    if ($sort === 'score' || $sort === 'rating') {
        usort($list, fn($a, $b) => (float)($b['score'] ?? 0) <=> (float)($a['score'] ?? 0));
    } elseif ($sort === 'az' || $sort === 'title') {
        usort($list, fn($a, $b) => strcasecmp($a['title'] ?? '', $b['title'] ?? ''));
    } elseif ($sort === 'latest' || $sort === 'year') {
        usort($list, fn($a, $b) => (int)($b['release_year'] ?? 0) <=> (int)($a['release_year'] ?? 0));
    } elseif ($sort === 'episodes') {
        usort($list, fn($a, $b) => (int)($b['episodes_count'] ?? 0) <=> (int)($a['episodes_count'] ?? 0));
    }

    return array_values($list);
}

/**
 * Fetch Anime by ID or Slug with complete episode list from db.anixo.buzz
 */
function getAnimeBySlug($slugOrId) {
    global $db_connected, $conn;
    $identifier = trim((string)$slugOrId);
    $catalog = buildUnifiedCatalog();

    // 1. Check dynamic catalog by ID or slug
    foreach ($catalog as $item) {
        if ((string)$item['id'] === $identifier || 
            (string)($item['anilist_id'] ?? '') === $identifier || 
            ($item['slug'] ?? '') === $identifier) {
            
            return enrichAnimeDetails($item);
        }
    }

    // 2. If it's a numeric ID, fetch directly from db.anixo.buzz detail endpoint
    if (is_numeric($identifier)) {
        $id = (int)$identifier;
        $detail = getAnixoDetail($id);
        if ($detail) {
            $anizip = getAnixoAniZip($id, $detail['mal_id'] ?? 0);
            $formatted = formatAnixoItem($detail, $detail, $anizip);
            return enrichAnimeDetails($formatted);
        }
    }

    // 3. Fallback: Search db.anixo.buzz API for matching slug/title
    $searchResults = searchAnixoCatalog($identifier, 3);
    if (!empty($searchResults)) {
        return enrichAnimeDetails($searchResults[0]);
    }

    // 4. Default to first anime in the live catalog
    return enrichAnimeDetails($catalog[0]);
}

/**
 * Enrich anime with description, high-res banner, and full episode list from AniZip
 */
function enrichAnimeDetails($anime) {
    $id = $anime['anilist_id'] ?? $anime['id'] ?? null;
    
    // Fetch deeper detail from db.anixo.buzz
    if ($id && (empty($anime['banner']) || empty($anime['synopsis']) || strlen($anime['synopsis']) < 50 || $anime['genres'] === 'Action, Adventure, Fantasy')) {
        $detail = getAnixoDetail($id);
        if ($detail) {
            if (!empty($detail['banner_image'])) {
                $anime['banner'] = $detail['banner_image'];
            }
            if (!empty($detail['description'])) {
                $desc = strip_tags($detail['description']);
                $desc = html_entity_decode($desc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $anime['synopsis'] = preg_replace('/\s+/', ' ', trim($desc));
            }
            if (!empty($detail['genres']) && is_array($detail['genres'])) {
                $anime['genres'] = implode(', ', $detail['genres']);
            }
            if (empty($anime['hindi_title'])) {
                $anizip = getAnixoAniZip($id, $detail['mal_id'] ?? 0);
                $hi = resolveHindiTitle($anime['title'], $anime['japanese_title'], $anizip['titles']['hi'] ?? null, $detail['synonyms'] ?? []);
                if ($hi) {
                    $anime['hindi_title'] = $hi;
                    $anime['is_hindi_dubbed'] = 1;
                }
            }
        }
    }

    // Populate episode list if empty
    if (empty($anime['episodes'])) {
        $anime['episodes'] = getEpisodesForAnime($anime);
        $anime['episodes_count'] = count($anime['episodes']);
    }

    return $anime;
}

/**
 * Render Ultra-Unique AniXo Glass-Pocket Anime Card
 */
function renderAnimeCard($anime) {
    $slug = e($anime['slug'] ?? ('anime-' . ($anime['id'] ?? '1')));
    $title = e($anime['title'] ?? 'Anime Series');
    $hindiTitle = !empty($anime['hindi_title']) ? e($anime['hindi_title']) : '';
    $poster = e($anime['poster'] ?? 'https://placehold.co/300x420/111816/D1E8E2?text=AniXo');
    $rawScore = (float)($anime['score'] ?? 8.2);
    $score = $rawScore > 0 ? number_format($rawScore, 1) : '8.2';
    $format = strtoupper(e($anime['show_type'] ?? 'TV'));
    $epCount = (int)($anime['episodes_count'] ?? 0);
    $epText = ($format === 'MOVIE') ? 'Movie' : ($epCount > 0 ? $epCount . ' Eps' : '12+ Eps');
    $year = e($anime['release_year'] ?? '2025');
    $isAiring = ($anime['status'] ?? '') === 'Currently Airing';
    $isDub = !empty($anime['is_hindi_dubbed']) || !empty($anime['hindi_title']);

    // Fallback genre display when Hindi title is not available
    $genresRaw = $anime['genres'] ?? '';
    if (is_array($genresRaw)) {
        $genreSubtitle = implode(' • ', array_slice($genresRaw, 0, 2));
    } elseif (!empty($genresRaw)) {
        $genreSubtitle = $genresRaw;
    } else {
        $genreSubtitle = 'Japanese • English Subs';
    }

    ob_start();
    ?>
    <div class="anime-card">
        <!-- Top Studio Bevel Accent Line -->
        <div class="card-studio-line"></div>

        <a href="/watch.php?anime=<?= $slug ?>&ep=1" class="anime-card-poster">
            <img src="<?= $poster ?>" alt="<?= $title ?>" loading="lazy">
            
            <!-- Floating Top Badges -->
            <div class="card-chips-top">
                <?php if ($isDub): ?>
                    <span class="card-chip chip-dub">
                        <i class="fa-solid fa-headphones"></i>
                        <span>Hindi Dub</span>
                    </span>
                <?php else: ?>
                    <span class="card-chip chip-sub">
                        <i class="fa-solid fa-closed-captioning"></i>
                        <span>Sub</span>
                    </span>
                <?php endif; ?>

                <?php if ($isAiring): ?>
                    <span class="card-chip chip-airing">
                        <span class="pulse-dot"></span>
                        <span>Airing</span>
                    </span>
                <?php else: ?>
                    <span class="card-chip chip-format"><?= $format ?></span>
                <?php endif; ?>
            </div>

            <!-- Cinematic Center Play Portal on Hover -->
            <div class="card-play-overlay">
                <div class="card-play-orb">
                    <i class="fa-solid fa-play"></i>
                </div>
                <div class="card-play-label">
                    <span>Watch Now</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <!-- Bottom Floating Glass Flight Capsule -->
            <div class="card-poster-capsule">
                <div class="capsule-score">
                    <i class="fa-solid fa-star"></i>
                    <span><?= $score ?></span>
                </div>
                <div class="capsule-divider"></div>
                <div class="capsule-eps">
                    <i class="fa-solid fa-layer-group"></i>
                    <span><?= $epText ?></span>
                </div>
            </div>
        </a>

        <!-- Card Body Info -->
        <div class="anime-card-body">
            <h3 class="anime-card-title">
                <a href="/watch.php?anime=<?= $slug ?>&ep=1" title="<?= $title ?>"><?= $title ?></a>
            </h3>

            <?php if (!empty($hindiTitle)): ?>
                <div class="anime-card-bhasha" title="<?= $hindiTitle ?>">
                    <span class="bhasha-badge"><i class="fa-solid fa-language"></i> HI</span>
                    <span class="bhasha-text"><?= $hindiTitle ?></span>
                </div>
            <?php else: ?>
                <div class="anime-card-bhasha bhasha-sub" title="<?= e($genreSubtitle) ?>">
                    <span class="bhasha-badge bhasha-badge-sub"><i class="fa-solid fa-closed-captioning"></i> SUB</span>
                    <span class="bhasha-text"><?= e($genreSubtitle) ?></span>
                </div>
            <?php endif; ?>

            <div class="anime-card-footer">
                <span class="card-meta-text"><?= $year ?> • <?= $format ?></span>
                <a href="/watch.php?anime=<?= $slug ?>&ep=1" class="card-stream-btn" title="Stream <?= $title ?>">
                    <i class="fa-solid fa-play"></i>
                </a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


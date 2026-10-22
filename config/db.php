<?php
/**
 * AniXoHindi - Authoritative Hindi Database Connection & Catalog Provider
 * Powered directly by the master Hindi database (telegram_hindi_anime & telegram_hindi_episodes).
 * Zero generic non-Hindi clutter. 100% verified Hindi Dubbed anime catalog.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/anixo_api.php';

// MySQL Connection (Used for user accounts, bookmarks, OAuth if enabled)
$db_connected = false;
$conn = null;
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

/**
 * SQLite PDO Connection to authoritative Hindi Database
 */
function getHindiPdo() {
    static $pdo = null;
    static $tried = false;
    if ($tried) return $pdo;
    $tried = true;

    $path = defined('HINDI_DB_PATH') ? HINDI_DB_PATH : '/www/wwwroot/anilist_database/anilist.db';
    if (!file_exists($path)) {
        return null;
    }

    try {
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]);
        return $pdo;
    } catch (Exception $e) {
        error_log('Hindi SQLite connection error: ' . $e->getMessage());
        return null;
    }
}

// In-memory catalog cache for instantaneous single-request execution
$GLOBAL_HINDI_CATALOG = null;

/**
 * Helper to slugify anime title for clean SEO URLs
 */
function slugifyAnime($title, $id = null) {
    $text = preg_replace('~[^\pL\d]+~u', '-', (string)$title);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $slug = strtolower($text);
    return !empty($slug) ? $slug : ('anime-' . ($id ?? rand(100, 999)));
}

/**
 * Build authoritative Hindi Dubbed anime catalog directly from master database
 */
function buildHindiCatalog() {
    global $GLOBAL_HINDI_CATALOG;
    if ($GLOBAL_HINDI_CATALOG !== null) {
        return $GLOBAL_HINDI_CATALOG;
    }

    $catalog = [];
    $pdo = getHindiPdo();

    // 1. Direct local SQLite query (ultra-fast 30ms on VPS)
    if ($pdo) {
        try {
            $sql = "
                SELECT 
                    tha.id as hindi_id,
                    tha.anilist_id,
                    tha.mal_id,
                    COALESCE(m.title_english, tha.title_english, tha.title_romaji) as title,
                    tha.title_english,
                    tha.title_romaji,
                    m.title_native,
                    m.description as synopsis,
                    COALESCE(m.cover_image, '') as cover_image,
                    COALESCE(m.banner_image, m.cover_image, '') as banner_image,
                    m.genres,
                    m.average_score,
                    COALESCE(tha.year, m.year) as release_year,
                    COALESCE(m.format, 'TV') as show_type,
                    tha.status as hindi_status,
                    m.status as anilist_status,
                    tha.total_episodes,
                    zm.titles_json,
                    COUNT(the.id) as uploaded_count,
                    GROUP_CONCAT(the.episode_number) as uploaded_episodes
                FROM telegram_hindi_anime tha
                JOIN telegram_hindi_episodes the ON tha.id = the.anime_id AND the.is_uploaded = 1
                LEFT JOIN anime m ON tha.anilist_id = m.anilist_id
                LEFT JOIN anizip_mappings zm ON tha.anilist_id = zm.anilist_id
                GROUP BY tha.id
                ORDER BY tha.title_english ASC;
            ";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $catalog[] = formatHindiDbItem($row);
            }

            if (!empty($catalog)) {
                $GLOBAL_HINDI_CATALOG = $catalog;
                return $catalog;
            }
        } catch (Exception $e) {
            error_log('buildHindiCatalog SQLite error: ' . $e->getMessage());
        }
    }

    // 2. Fallback to https://embed.anixohindi.cc/api/catalog (for local dev or if SQLite is inaccessible)
    $cacheFile = ANIXO_CACHE_DIR . '/hindi_catalog.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 600)) {
        $cached = @file_get_contents($cacheFile);
        if ($cached) {
            $data = json_decode($cached, true);
            if (!empty($data) && is_array($data)) {
                $GLOBAL_HINDI_CATALOG = $data;
                return $data;
            }
        }
    }

    $apiUrl = (defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc') . '/api/catalog';
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($res)) {
        $json = json_decode($res, true);
        if (!empty($json['anime']) && is_array($json['anime'])) {
            foreach ($json['anime'] as $row) {
                $catalog[] = formatHindiDbItem($row);
            }
            @file_put_contents($cacheFile, json_encode($catalog));
            $GLOBAL_HINDI_CATALOG = $catalog;
            return $catalog;
        }
    }

    // 3. Last fallback: Stale cache
    if (file_exists($cacheFile)) {
        $cached = @file_get_contents($cacheFile);
        if ($cached) {
            $data = json_decode($cached, true);
            if (!empty($data) && is_array($data)) {
                $GLOBAL_HINDI_CATALOG = $data;
                return $data;
            }
        }
    }

    return [];
}

/**
 * Format a raw database row into an authentic frontend anime item
 */
function formatHindiDbItem($row) {
    $id = (int)($row['hindi_id'] ?? $row['id'] ?? 0);
    $aniId = !empty($row['anilist_id']) ? (int)$row['anilist_id'] : null;
    $malId = !empty($row['mal_id']) ? (int)$row['mal_id'] : null;

    $engTitle = trim($row['title_english'] ?? '');
    $mTitle = trim($row['title'] ?? '');
    if (!empty($engTitle) && (stripos($engTitle, 'Season') !== false || stripos($engTitle, '(S') !== false || stripos($engTitle, 'Cour') !== false || stripos($engTitle, 'Part') !== false || stripos($engTitle, 'Movie') !== false)) {
        $title = $engTitle;
    } else {
        $title = !empty($mTitle) ? $mTitle : (!empty($engTitle) ? $engTitle : ('Anime #' . $id));
    }
    $slug = slugifyAnime($title, $id);

    // Parse Genres
    $genres = [];
    if (!empty($row['genres'])) {
        if (is_array($row['genres'])) {
            $genres = $row['genres'];
        } else {
            $decoded = json_decode($row['genres'], true);
            if (is_array($decoded)) {
                $genres = $decoded;
            } else {
                $genres = array_map('trim', explode(',', $row['genres']));
            }
        }
    }
    if (empty($genres)) {
        $genres = ['Action', 'Adventure', 'Fantasy'];
    }

    // Extract Hindi localized title
    $hindiTitle = '';
    if (!empty($row['titles_json'])) {
        $titles = is_array($row['titles_json']) ? $row['titles_json'] : json_decode($row['titles_json'], true);
        if (!empty($titles['hi'])) {
            $hindiTitle = trim($titles['hi']);
        }
    }
    if (empty($hindiTitle)) {
        $lookupKey = strtolower($title);
        if (isset(HINDI_ANIME_TITLES[$lookupKey])) {
            $hindiTitle = HINDI_ANIME_TITLES[$lookupKey];
        } else {
            // Check substrings
            foreach (HINDI_ANIME_TITLES as $enFranchise => $hiFranchise) {
                if (stripos($lookupKey, $enFranchise) !== false) {
                    $hindiTitle = $hiFranchise;
                    break;
                }
            }
        }
    }

    // Clean synopsis
    $synopsis = $row['synopsis'] ?? '';
    if (!empty($synopsis)) {
        $synopsis = strip_tags($synopsis);
        $synopsis = html_entity_decode($synopsis, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $synopsis = preg_replace('/\s+/', ' ', trim($synopsis));
    }
    if (empty($synopsis)) {
        $synopsis = "Watch {$title} in Hindi Dubbed Full HD 1080p with zero ads and bufferless streaming.";
    }

    $rawScore = (float)($row['average_score'] ?? 82);
    $score = $rawScore > 10 ? round($rawScore / 10, 1) : ($rawScore > 0 ? round($rawScore, 1) : 8.5);

    $poster = !empty($row['cover_image']) ? $row['cover_image'] : 'https://placehold.co/300x420/111816/D1E8E2?text=' . urlencode($title);
    $banner = !empty($row['banner_image']) ? $row['banner_image'] : $poster;

    $epCount = (int)($row['uploaded_count'] ?? 1);
    $showType = strtoupper($row['show_type'] ?? 'TV');
    if ($showType === 'MOVIE' && $epCount <= 1) {
        $showTypeDisplay = 'Movie';
    } else {
        $showTypeDisplay = 'TV';
    }

    $uploadedEps = [];
    if (!empty($row['uploaded_episodes'])) {
        $epParts = explode(',', (string)$row['uploaded_episodes']);
        foreach ($epParts as $epP) {
            $n = (int)trim($epP);
            if ($n > 0) $uploadedEps[] = $n;
        }
        sort($uploadedEps);
    }
    if (empty($uploadedEps) && $epCount > 0) {
        for ($i = 1; $i <= $epCount; $i++) $uploadedEps[] = $i;
    }

    $status = 'Completed';
    if (($row['anilist_status'] ?? '') === 'RELEASING' || ($row['hindi_status'] ?? '') === 'In-Progress') {
        $status = 'Currently Airing';
    }

    return [
        'id' => $id,
        'anilist_id' => $aniId,
        'mal_id' => $malId,
        'title' => $title,
        'hindi_title' => $hindiTitle,
        'japanese_title' => $row['title_native'] ?? $row['title_romaji'] ?? '',
        'slug' => $slug,
        'poster' => $poster,
        'banner' => $banner,
        'synopsis' => $synopsis,
        'show_type' => $showTypeDisplay,
        'rating' => '16+',
        'score' => $score,
        'release_year' => $row['release_year'] ?? '2024',
        'status' => $status,
        'is_hindi_dubbed' => 1,
        'is_subbed' => 1,
        'genres' => $genres,
        'episodes_count' => $epCount,
        'uploaded_episodes' => $uploadedEps
    ];
}

/**
 * Get all anime matching filter criteria from authoritative Hindi catalog
 */
function getAllAnime($filter = []) {
    $list = buildHindiCatalog();

    // Filter by audio format / show_type
    if (!empty($filter['type']) && $filter['type'] !== 'all') {
        if ($filter['type'] === 'Movie') {
            $list = array_filter($list, fn($a) => strcasecmp($a['show_type'], 'Movie') === 0);
        } elseif ($filter['type'] === 'TV') {
            $list = array_filter($list, fn($a) => strcasecmp($a['show_type'], 'TV') === 0);
        }
        // 'dub' and 'sub' apply to 100% of our catalog
    }

    // Filter by genre
    if (!empty($filter['genre']) && $filter['genre'] !== 'all') {
        $targetGenre = strtolower(trim($filter['genre']));
        $list = array_filter($list, function($a) use ($targetGenre) {
            $genres = is_array($a['genres']) ? $a['genres'] : explode(',', (string)$a['genres']);
            foreach ($genres as $g) {
                if (stripos(trim($g), $targetGenre) !== false) return true;
            }
            return false;
        });
    }

    // Filter by status (Currently Airing vs Completed)
    if (!empty($filter['status']) && $filter['status'] !== 'all') {
        $status = $filter['status'];
        $list = array_filter($list, fn($a) => strcasecmp($a['status'], $status) === 0);
    }

    // Search query filter
    $q = trim($filter['q'] ?? $filter['keyword'] ?? '');
    if (!empty($q)) {
        $qLower = strtolower($q);
        $list = array_filter($list, function($a) use ($qLower) {
            $titleMatch = str_contains(strtolower($a['title'] ?? ''), $qLower);
            $hiMatch = !empty($a['hindi_title']) && str_contains(strtolower($a['hindi_title']), $qLower);
            $jaMatch = !empty($a['japanese_title']) && str_contains(strtolower($a['japanese_title']), $qLower);
            $gMatch = false;
            $genres = is_array($a['genres']) ? $a['genres'] : [$a['genres']];
            foreach ($genres as $g) {
                if (str_contains(strtolower((string)$g), $qLower)) {
                    $gMatch = true;
                    break;
                }
            }
            return $titleMatch || $hiMatch || $jaMatch || $gMatch;
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
    } else {
        // 'popular': combination of score and episode count
        usort($list, function($a, $b) {
            $scoreA = ((float)($a['score'] ?? 8)) * log10(max(2, (int)($a['episodes_count'] ?? 1) + 1));
            $scoreB = ((float)($b['score'] ?? 8)) * log10(max(2, (int)($b['episodes_count'] ?? 1) + 1));
            return $scoreB <=> $scoreA;
        });
    }

    return array_values($list);
}

/**
 * Fetch Anime by ID, AniList ID, or Slug with complete episode list
 */
function getAnimeBySlug($slugOrId) {
    $identifier = trim((string)$slugOrId);
    $catalog = buildHindiCatalog();
    if (empty($catalog)) return null;

    $matched = null;

    // 1. Direct slug or ID match
    foreach ($catalog as $item) {
        if (($item['slug'] ?? '') === $identifier || 
            (string)($item['id'] ?? '') === $identifier || 
            (string)($item['anilist_id'] ?? '') === $identifier) {
            $matched = $item;
            break;
        }
    }

    // 2. Fuzzy slug match
    if (!$matched) {
        $cleanIdent = strtolower(str_replace('-', ' ', $identifier));
        foreach ($catalog as $item) {
            $cleanTitle = strtolower(str_replace('-', ' ', $item['title']));
            if (str_contains($cleanTitle, $cleanIdent) || str_contains($cleanIdent, $cleanTitle)) {
                $matched = $item;
                break;
            }
        }
    }

    // 3. Fallback to first anime in catalog
    if (!$matched) {
        $matched = $catalog[0];
    }

    return enrichAnimeDetails($matched);
}

/**
 * Enrich anime with complete structured episode list and direct embed links
 */
function enrichAnimeDetails($anime) {
    if (!empty($anime['episodes'])) {
        return $anime;
    }

    $hindiId = (int)($anime['id'] ?? 0);
    $aniId = !empty($anime['anilist_id']) ? (int)$anime['anilist_id'] : null;
    $embedBase = defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc';
    $episodes = [];

    $pdo = getHindiPdo();
    if ($pdo && $hindiId > 0) {
        try {
            // Fetch uploaded episodes
            $stmt = $pdo->prepare("
                SELECT id, episode_number, episode_title, episode_thumbnail, duration_seconds, telegram_message_id
                FROM telegram_hindi_episodes
                WHERE anime_id = ? AND is_uploaded = 1
                ORDER BY episode_number ASC;
            ");
            $stmt->execute([$hindiId]);
            $dbEps = $stmt->fetchAll();

            // Fetch AniZip metadata for thumbnails and official titles
            $anizipEps = [];
            if ($aniId) {
                $zipStmt = $pdo->prepare("SELECT episodes_json FROM anizip_mappings WHERE anilist_id = ?;");
                $zipStmt->execute([$aniId]);
                $zipRow = $zipStmt->fetch();
                if ($zipRow && !empty($zipRow['episodes_json'])) {
                    $anizipEps = json_decode($zipRow['episodes_json'], true) ?: [];
                }
            }

            foreach ($dbEps as $e) {
                $num = (int)$e['episode_number'];
                $zip = $anizipEps[(string)$num] ?? [];
                
                $title = !empty($e['episode_title']) ? $e['episode_title'] : ($zip['title']['en'] ?? $zip['title']['ja'] ?? ('Episode ' . $num));
                $image = !empty($e['episode_thumbnail']) ? $e['episode_thumbnail'] : ($zip['image'] ?? $anime['banner'] ?? $anime['poster']);
                $overview = $zip['overview'] ?? $zip['summary'] ?? '';
                $durSec = (int)($e['duration_seconds'] ?? 0);
                $duration = $durSec > 0 ? round($durSec / 60) . 'm' : '24m';

                $embedIdUrl = "{$embedBase}/embed/id/{$hindiId}/{$num}";
                $embedAniUrl = $aniId ? "{$embedBase}/embed/ani/{$aniId}/{$num}" : $embedIdUrl;
                $isMultiPart = (stripos($anime['title'], 'Season') !== false || stripos($anime['title'], '(S') !== false || stripos($anime['title'], 'Cour') !== false);
                $embedUrl = $isMultiPart ? $embedIdUrl : $embedAniUrl;

                $episodes[] = [
                    'number' => $num,
                    'title' => 'Ep ' . $num . ': ' . $title,
                    'clean_title' => $title,
                    'image' => $image,
                    'duration' => $duration,
                    'overview' => $overview,
                    'embed' => $embedUrl,
                    'embed_id' => $embedIdUrl,
                    'embed_ani' => $embedAniUrl
                ];
            }
        } catch (Exception $e) {
            error_log('enrichAnimeDetails DB error: ' . $e->getMessage());
        }
    }

    // Remote API fallback if SQLite not accessible locally
    if (empty($episodes)) {
        $lookupId = $aniId ?: $hindiId;
        $apiUrl = "{$embedBase}/api/anime/{$lookupId}";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!empty($res)) {
            $json = json_decode($res, true);
            if (!empty($json['episodes']) && is_array($json['episodes'])) {
                foreach ($json['episodes'] as $epItem) {
                    $num = (int)$epItem['number'];
                    $embedUrl = $aniId ? "{$embedBase}/embed/ani/{$aniId}/{$num}" : "{$embedBase}/embed/id/{$hindiId}/{$num}";
                    $episodes[] = [
                        'number' => $num,
                        'title' => $epItem['title'] ?? ('Episode ' . $num),
                        'clean_title' => $epItem['clean_title'] ?? ('Episode ' . $num),
                        'image' => $epItem['image'] ?? $anime['banner'] ?? $anime['poster'],
                        'duration' => $epItem['duration'] ?? '24m',
                        'overview' => $epItem['overview'] ?? '',
                        'embed' => $embedUrl,
                        'embed_id' => "{$embedBase}/embed/id/{$hindiId}/{$num}"
                    ];
                }
            }
        }
    }

    // Default sequential fallback from uploaded_episodes list
    if (empty($episodes)) {
        $numList = !empty($anime['uploaded_episodes']) ? $anime['uploaded_episodes'] : range(1, max(1, (int)($anime['episodes_count'] ?? 12)));
        foreach ($numList as $num) {
            $embedUrl = $aniId ? "{$embedBase}/embed/ani/{$aniId}/{$num}" : "{$embedBase}/embed/id/{$hindiId}/{$num}";
            $episodes[] = [
                'number' => (int)$num,
                'title' => 'Episode ' . $num,
                'clean_title' => 'Episode ' . $num,
                'image' => $anime['banner'] ?? $anime['poster'],
                'duration' => '24m',
                'overview' => '',
                'embed' => $embedUrl,
                'embed_id' => "{$embedBase}/embed/id/{$hindiId}/{$num}"
            ];
        }
    }

    usort($episodes, fn($a, $b) => $a['number'] <=> $b['number']);
    $anime['episodes'] = $episodes;
    $anime['episodes_count'] = count($episodes);

    return $anime;
}

/**
 * Render Ultra-Unique AniXo Glass-Pocket Anime Card
 */
// Helper to fetch all bookmarks for logged-in user (cached in memory)
function getUserBookmarkIds() {
    static $cached = null;
    if ($cached !== null) return $cached;
    $cached = [];
    if (!empty($_SESSION['user']['id']) && function_exists('getHindiPdo')) {
        $pdo = getHindiPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT anime_id, anime_slug FROM bookmarks WHERE user_id = ?");
                $stmt->execute([(int)$_SESSION['user']['id']]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    if (!empty($r['anime_id'])) $cached['id_' . $r['anime_id']] = true;
                    if (!empty($r['anime_slug'])) $cached['slug_' . $r['anime_slug']] = true;
                }
            } catch (Exception $e) {}
        }
    }
    return $cached;
}

function renderAnimeCard($anime) {
    $slug = e($anime['slug'] ?? ('anime-' . ($anime['id'] ?? '1')));
    $title = e($anime['title'] ?? 'Anime Series');
    $hindiTitle = !empty($anime['hindi_title']) ? e($anime['hindi_title']) : '';
    $poster = e($anime['poster'] ?? 'https://placehold.co/300x420/111816/D1E8E2?text=AniXo');
    $rawScore = (float)($anime['score'] ?? 8.5);
    $score = $rawScore > 0 ? number_format($rawScore, 1) : '8.5';
    $format = strtoupper(e($anime['show_type'] ?? 'TV'));
    $epCount = (int)($anime['episodes_count'] ?? 0);
    $epText = ($format === 'MOVIE') ? 'Movie' : ($epCount > 0 ? $epCount . ' Eps' : 'Hindi Dub');
    $year = e($anime['release_year'] ?? '2024');
    $isAiring = ($anime['status'] ?? '') === 'Currently Airing';
    $userBookmarks = getUserBookmarkIds();
    $cardId = (int)($anime['id'] ?? 0);
    $isBookmarkedCard = !empty($userBookmarks['id_' . $cardId]) || !empty($userBookmarks['slug_' . $slug]);

    $genresRaw = $anime['genres'] ?? '';
    if (is_array($genresRaw)) {
        $genreSubtitle = implode(' • ', array_slice($genresRaw, 0, 2));
    } elseif (!empty($genresRaw)) {
        $genreSubtitle = $genresRaw;
    } else {
        $genreSubtitle = 'Hindi Dub • Full HD';
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
                <span class="card-chip chip-dub">
                    <i class="fa-solid fa-headphones"></i>
                    <span>Hindi Dub</span>
                </span>

                <?php if ($isAiring): ?>
                    <span class="card-chip chip-airing">
                        <span class="pulse-dot"></span>
                        <span>Airing</span>
                    </span>
                <?php else: ?>
                    <span class="card-chip chip-format"><?= $format ?></span>
                <?php endif; ?>

                <button type="button" 
                        class="card-chip-bookmark <?= $isBookmarkedCard ? 'is-bookmarked' : '' ?>" 
                        data-id="<?= $cardId ?>"
                        data-slug="<?= $slug ?>"
                        data-title="<?= $title ?>"
                        data-poster="<?= $poster ?>"
                        onclick="event.preventDefault(); event.stopPropagation(); toggleWatchlistBookmark(this);" 
                        title="<?= $isBookmarkedCard ? 'In Watchlist (Click to remove)' : 'Add to Watchlist' ?>">
                    <i class="<?= $isBookmarkedCard ? 'fa-solid fa-bookmark text-accent-gold' : 'fa-regular fa-bookmark' ?>"></i>
                </button>
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

            <!-- Bottom Floating Card Specs Bar -->
            <div class="card-specs-bar">
                <div class="card-score-badge">
                    <i class="fa-solid fa-star"></i>
                    <span><?= $score ?></span>
                </div>
                <div class="card-ep-count">
                    <i class="fa-solid fa-layer-group"></i>
                    <span><?= $epText ?></span>
                </div>
            </div>
        </a>

        <!-- Card Body -->
        <div class="anime-card-body">
            <h3 class="card-title">
                <a href="/watch.php?anime=<?= $slug ?>&ep=1" title="<?= $title ?>"><?= $title ?></a>
            </h3>

            <!-- Hindi Subtitle or Genre Pill -->
            <div class="card-subtitle-row">
                <?php if (!empty($hindiTitle)): ?>
                    <span class="card-hindi-tag" title="<?= $hindiTitle ?>">
                        <i class="fa-solid fa-language"></i>
                        <span><?= $hindiTitle ?></span>
                    </span>
                <?php else: ?>
                    <span class="card-genre-sub"><?= e($genreSubtitle) ?></span>
                <?php endif; ?>
            </div>

            <div class="card-meta">
                <span class="meta-tag"><?= $format ?></span>
                <span class="meta-sep">•</span>
                <span class="meta-year"><?= $year ?></span>
                <span class="meta-sep">•</span>
                <span class="meta-audio-tag"><i class="fa-solid fa-headphones text-accent-gold mr-1"></i>Hindi</span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

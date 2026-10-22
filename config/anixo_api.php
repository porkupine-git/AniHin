<?php
/**
 * AniXo Intelligence Platform API Client
 * Source: https://db.anixo.buzz
 * Handles newly released anime, Hindi dubbed localized catalog, genres, episode data, and caching.
 */

require_once __DIR__ . '/config.php';

// Cache directory for blazing fast performance and offline resilience
define('ANIXO_CACHE_DIR', __DIR__ . '/../cache');
if (!is_dir(ANIXO_CACHE_DIR)) {
    @mkdir(ANIXO_CACHE_DIR, 0777, true);
}

// Built-in Hindi Localized Title Dictionary for popular franchises
const HINDI_ANIME_TITLES = [
    'solo leveling' => 'सोलो लेवलिंग',
    'solo leveling season 2' => 'सोलो लेवलिंग सीजन 2',
    'jujutsu kaisen' => 'जुजुत्सु काइसेन',
    'demon slayer' => 'डेमन स्लेयर: किमेत्सु नो याइबा',
    'attack on titan' => 'अटैक ऑन टाइटन',
    'naruto' => 'नारुतो',
    'naruto shippuden' => 'नारुतो शिपूडेन',
    'one piece' => 'वन पीस',
    'bleach' => 'ब्लीच: थाउजेंड-ईयर ब्लड वॉर',
    'death note' => 'डेथ नोट',
    'chainsaw man' => 'चेनसो मैन',
    'dragon ball z' => 'ड्रैगन बॉल ज़ेड',
    'my hero academia' => 'माई हीरो एकेडेमिया',
    'black clover' => 'ब्लैक क्लोवर',
    'tokyo revengers' => 'टोक्यो रिवेंजर्स',
    'spy x family' => 'स्पाई × फैमिली',
    'the seven deadly sins' => 'सात खतरनाक पाप',
    'delicious in dungeon' => 'तहखाने के चटोरे',
    'sakamoto days' => 'साकामोतो डेज़',
    'spirited away' => 'स्पिरिटेड अवे',
    'howl‘s moving castle' => 'हाउल का चलता-फिरता महल',
    'howl\'s moving castle' => 'हाउल का चलता-फिरता महल',
    'jojo\'s bizarre adventure' => 'जोजो के अनोखे कारनामे',
    'the way of the househusband' => 'तात्सु के कारनामे',
    'princess mononoke' => 'राजकुमारी मोनोनोके',
    'castle in the sky' => 'हवाई किला',
    'wind breaker' => 'विंड ब्रेकर',
    'kaiju no. 8' => 'काइजू नं. 8',
    'dandadan' => 'डंडाडैन',
    'the summer hikaru died' => 'द समर हिकारू डाइड',
    'look back' => 'लुक बैक'
];

/**
 * Perform a cached HTTP GET to db.anixo.buzz
 */
function anixoApiGet($url, $ttl = 3600) {
    $cacheKey = md5($url);
    $cacheFile = ANIXO_CACHE_DIR . '/anixo_' . $cacheKey . '.json';

    // Check if fresh cache exists
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $content = @file_get_contents($cacheFile);
        if ($content) {
            $data = json_decode($content, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    // cURL request to db.anixo.buzz
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AniXoHindi/1.0 (Mozilla/5.0; Compatible)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);
        if ($data !== null) {
            @file_put_contents($cacheFile, $response);
            return $data;
        }
    }

    // Stale cache fallback if API timed out or offline
    if (file_exists($cacheFile)) {
        $content = @file_get_contents($cacheFile);
        if ($content) {
            $data = json_decode($content, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    return null;
}

/**
 * Fetch Newly Released / Currently Airing Anime from db.anixo.buzz
 */
function getAnixoNewlyReleased($limit = 24) {
    $url = 'https://db.anixo.buzz/?api=search&status=RELEASING&limit=' . (int)$limit;
    $res = anixoApiGet($url, 1800); // 30 min cache for newly released
    return $res['results'] ?? [];
}

/**
 * Fetch Hindi Dubbed / Localized Anime from AniZip
 */
function getAnixoHindiDubbed($limit = 30) {
    $url = 'https://db.anixo.buzz/?api=anizip_search&filter=hindi&limit=' . (int)$limit;
    $res = anixoApiGet($url, 7200); // 2 hr cache
    return $res['results'] ?? [];
}

/**
 * Fetch Most Popular Anime
 */
function getAnixoPopular($limit = 24) {
    $url = 'https://db.anixo.buzz/?api=search&sort=popularity&limit=' . (int)$limit;
    $res = anixoApiGet($url, 7200);
    return $res['results'] ?? [];
}

/**
 * Fetch Highest Rated Anime
 */
function getAnixoTopRated($limit = 24) {
    $url = 'https://db.anixo.buzz/?api=search&sort=score&limit=' . (int)$limit;
    $res = anixoApiGet($url, 7200);
    return $res['results'] ?? [];
}

/**
 * Fetch Full Detail for an Anime by AniList ID
 */
function getAnixoDetail($anilistId) {
    if (!$anilistId) return null;
    $url = 'https://db.anixo.buzz/?api=detail&id=' . (int)$anilistId;
    return anixoApiGet($url, 43200); // 12 hours cache
}

/**
 * Fetch AniZip Episodes and Language Titles
 */
function getAnixoAniZip($anilistId, $malId = 0) {
    if (!$anilistId) return null;
    $url = 'https://db.anixo.buzz/?api=anizip&id=' . (int)$anilistId . ($malId ? '&mal_id=' . (int)$malId : '');
    return anixoApiGet($url, 43200); // 12 hours cache
}

/**
 * Search Anime across both AniList & AniZip collections
 */
function searchAnixoCatalog($query, $limit = 12) {
    $query = trim($query);
    if (strlen($query) < 2) return [];

    // Prioritize authoritative Hindi database search
    if (function_exists('getAllAnime')) {
        $results = getAllAnime(['q' => $query]);
        if (!empty($results)) {
            return array_slice($results, 0, $limit);
        }
    }

    return [];
}

/**
 * Helper to resolve Hindi title from AniZip, Dictionary, or Synonyms
 */
function resolveHindiTitle($titleEnglish, $titleRomaji, $anizipTitleHi = null, $synonyms = []) {
    if (!empty($anizipTitleHi)) {
        return $anizipTitleHi;
    }

    $t1 = strtolower(trim($titleEnglish ?? ''));
    $t2 = strtolower(trim($titleRomaji ?? ''));

    // Direct lookup in Hindi Dictionary
    if (isset(HINDI_ANIME_TITLES[$t1])) return HINDI_ANIME_TITLES[$t1];
    if (isset(HINDI_ANIME_TITLES[$t2])) return HINDI_ANIME_TITLES[$t2];

    // Substring match
    foreach (HINDI_ANIME_TITLES as $engKey => $hiVal) {
        if (str_contains($t1, $engKey) || str_contains($t2, $engKey)) {
            return $hiVal;
        }
    }

    // Check synonyms for Devanagari script (\p{Devanagari})
    if (is_array($synonyms)) {
        foreach ($synonyms as $syn) {
            if (preg_match('/[\x{0900}-\x{097F}]/u', $syn)) {
                return $syn;
            }
        }
    }

    return null;
}

/**
 * Slugify a title into a clean URL-friendly slug
 */
function createSlug($title, $id) {
    $slug = strtolower($title ?? '');
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: ('anime-' . $id);
}

/**
 * Standardize an API result item into the AniXoHindi anime structure
 */
function formatAnixoItem($raw, $detail = null, $anizip = null) {
    $id = (int)($raw['anilist_id'] ?? $detail['anilist_id'] ?? 0);
    $titleEng = $raw['title_english'] ?? $detail['title_english'] ?? $raw['title_romaji'] ?? $detail['title_romaji'] ?? 'Anime';
    $titleRom = $raw['title_romaji'] ?? $detail['title_romaji'] ?? $titleEng;

    // Check Hindi title
    $hiFromRaw = $raw['hindi_title'] ?? null;
    $hiFromZip = $anizip['titles']['hi'] ?? null;
    $synonyms = $detail['synonyms'] ?? [];
    $hindiTitle = resolveHindiTitle($titleEng, $titleRom, $hiFromRaw ?: $hiFromZip, $synonyms);

    // Slug
    $slug = !empty($raw['anikoto_slug']) ? $raw['anikoto_slug'] : createSlug($titleEng, $id);

    // Score
    $rawScore = $raw['average_score'] ?? $detail['average_score'] ?? 82;
    $score = $rawScore > 10 ? round($rawScore / 10, 1) : round($rawScore, 1);

    // Poster and Banner
    $poster = $raw['cover_image'] ?? $detail['cover_image'] ?? 'https://s4.anilist.co/file/anilistcdn/media/anime/cover/medium/default.jpg';
    $banner = $detail['banner_image'] ?? $raw['banner_image'] ?? $poster;

    // Status
    $rawStatus = $raw['status'] ?? $detail['status'] ?? 'RELEASING';
    $status = ($rawStatus === 'RELEASING') ? 'Currently Airing' : 'Finished Airing';

    // Format / Show Type
    $showType = $raw['format'] ?? $detail['format'] ?? 'TV';

    // Year
    $year = $raw['year'] ?? $detail['year'] ?? 2025;

    // Genres
    $genres = 'Action, Adventure, Fantasy';
    if (!empty($detail['genres']) && is_array($detail['genres'])) {
        $genres = implode(', ', $detail['genres']);
    } elseif (!empty($raw['genres'])) {
        $genres = is_array($raw['genres']) ? implode(', ', $raw['genres']) : $raw['genres'];
    }

    // Synopsis
    $synopsis = 'Stream ' . $titleEng . ' in Full HD with Hindi Dub and English Sub on AniXoHindi.';
    if (!empty($detail['description'])) {
        $cleanDesc = strip_tags($detail['description']);
        $cleanDesc = html_entity_decode($cleanDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cleanDesc = preg_replace('/\s+/', ' ', trim($cleanDesc));
        if (strlen($cleanDesc) > 10) {
            $synopsis = $cleanDesc;
        }
    }

    // Episodes count
    $epCount = (int)($raw['episodes'] ?? $raw['episode_count'] ?? $detail['episodes'] ?? 12);
    if ($epCount <= 0) $epCount = 12;

    $hasDub = !empty($hindiTitle) || !empty($raw['anikoto_dub']) || !empty($raw['is_dub']) || ($raw['dub_count'] ?? 0) > 0;

    return [
        'id' => $id,
        'anilist_id' => $id,
        'mal_id' => $raw['mal_id'] ?? $detail['mal_id'] ?? $id,
        'slug' => $slug,
        'title' => $titleEng,
        'hindi_title' => $hindiTitle,
        'japanese_title' => $titleRom,
        'poster' => $poster,
        'banner' => $banner,
        'synopsis' => $synopsis,
        'show_type' => $showType,
        'rating' => '16+',
        'score' => $score,
        'release_year' => $year,
        'status' => $status,
        'is_hindi_dubbed' => $hasDub ? 1 : 0,
        'is_subbed' => 1,
        'genres' => $genres,
        'episodes_count' => $epCount,
        'episodes' => []
    ];
}

/**
 * Generate or fetch structured episode list for the watch player
 */
function getEpisodesForAnime($anime) {
    $id = $anime['anilist_id'] ?? $anime['id'] ?? null;
    $malId = $anime['mal_id'] ?? 0;
    $episodes = [];

    // Try AniZip first
    if ($id) {
        $zipData = getAnixoAniZip($id, $malId);
        $zipEps = $zipData['episodes'] ?? [];
        if (!empty($zipEps)) {
            foreach ($zipEps as $numStr => $epData) {
                $num = (int)$numStr;
                if ($num <= 0) continue;
                $title = $epData['title']['en'] ?? $epData['title']['ja'] ?? ('Episode ' . $num);
                $img = $epData['image'] ?? $anime['banner'] ?? $anime['poster'];
                $airDate = $epData['airDate'] ?? null;

                $embedBase = defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc';
                $embedUrl = $id ? "{$embedBase}/embed/ani/{$id}/{$num}" : "{$embedBase}/embed/id/{$anime['id']}/{$num}";

                $episodes[] = [
                    'number' => $num,
                    'title' => 'Ep ' . $num . ': ' . $title,
                    'air_date' => $airDate,
                    'image' => $img,
                    'overview' => $epData['overview'] ?? '',
                    'embed' => $embedUrl
                ];
            }
        }
    }

    // Fallback if AniZip has no episodes
    if (empty($episodes)) {
        $count = $anime['episodes_count'] ?? 12;
        if ($count <= 0) $count = 12;
        if ($count > 36) $count = 24; // sensible limit for display

        for ($i = 1; $i <= $count; $i++) {
            $embedBase = defined('HINDI_EMBED_BASE') ? HINDI_EMBED_BASE : 'https://embed.anixohindi.cc';
            $embedUrl = $id ? "{$embedBase}/embed/ani/{$id}/{$i}" : "{$embedBase}/embed/id/{$anime['id']}/{$i}";

            $episodes[] = [
                'number' => $i,
                'title' => 'Episode ' . $i,
                'air_date' => null,
                'image' => $anime['poster'],
                'overview' => '',
                'embed' => $embedUrl
            ];
        }
    }

    // Sort by episode number ascending
    usort($episodes, fn($a, $b) => $a['number'] <=> $b['number']);
    return $episodes;
}

/**
 * TMDB API Helper - Cached HTTP GET request
 */
function tmdbApiGet($endpoint, $params = [], $ttl = 1209600) {
    if (!defined('TMDB_API_KEY') || empty(TMDB_API_KEY)) {
        return null;
    }

    $params['api_key'] = TMDB_API_KEY;
    $url = 'https://api.themoviedb.org/3/' . ltrim($endpoint, '/') . '?' . http_build_query($params);

    $cacheKey = md5($url);
    $cacheFile = ANIXO_CACHE_DIR . '/tmdb_' . $cacheKey . '.json';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $content = @file_get_contents($cacheFile);
        if ($content) {
            $data = json_decode($content, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AniXoHindi/1.0 (TMDB Client)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (defined('TMDB_ACCESS_TOKEN') && !empty(TMDB_ACCESS_TOKEN)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . TMDB_ACCESS_TOKEN,
            'Accept: application/json'
        ]);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);
        if ($data !== null) {
            @file_put_contents($cacheFile, $response);
            return $data;
        }
    }

    // Stale cache fallback if TMDB is offline
    if (file_exists($cacheFile)) {
        $content = @file_get_contents($cacheFile);
        if ($content) {
            $data = json_decode($content, true);
            if ($data !== null) return $data;
        }
    }

    return null;
}

/**
 * Clean anime title for TMDB search matching
 */
function cleanAnimeTitleForTmdb($title) {
    $title = trim($title);
    if (stripos($title, "JoJo's Bizarre Adventure") !== false) {
        return "JoJo's Bizarre Adventure";
    }
    // Remove typical anime season/arc suffixes
    $cleaned = preg_replace('/\s+(Season|Part|Cour|\b\d+(st|nd|rd|th)\s+Season|\b\d+(st|nd|rd|th)\s+Stage)\b.*$/i', '', $title);
    $cleaned = preg_replace('/\s+:\s+.*$/', '', $cleaned);
    $cleaned = preg_replace('/\s+-\s+.*$/', '', $cleaned);
    $cleaned = preg_replace('/\s+\(TV\)/i', '', $cleaned);
    $cleaned = trim($cleaned);
    return !empty($cleaned) ? $cleaned : $title;
}

/**
 * Fetch high-res backdrop and logo from TMDB with persistent file caching
 */
function getTmdbAnimeArt($title, $year = null) {
    if (empty($title)) return null;

    $cacheKey = md5('tmdb_art_' . strtolower(trim($title)));
    $cacheFile = ANIXO_CACHE_DIR . '/art_' . $cacheKey . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 1209600)) {
        $cached = @file_get_contents($cacheFile);
        if ($cached) {
            $decoded = json_decode($cached, true);
            if ($decoded !== null && (!empty($decoded['backdrop']) || !empty($decoded['logo']))) {
                return $decoded;
            }
        }
    }

    $queriesToTry = [];
    $clean = cleanAnimeTitleForTmdb($title);
    $queriesToTry[] = $clean;
    if ($clean !== $title) {
        $queriesToTry[] = $title;
    }
    if (strpos($title, ':') !== false) {
        $beforeColon = trim(explode(':', $title)[0]);
        if (!in_array($beforeColon, $queriesToTry)) {
            $queriesToTry[] = $beforeColon;
        }
    }

    $foundItem = null;
    $mediaType = 'tv';

    foreach ($queriesToTry as $q) {
        // Try TV search first
        $params = ['query' => $q];
        if ($year) {
            $params['first_air_date_year'] = $year;
        }
        $searchRes = tmdbApiGet('search/tv', $params);
        if (empty($searchRes['results']) && isset($params['first_air_date_year'])) {
            unset($params['first_air_date_year']);
            $searchRes = tmdbApiGet('search/tv', $params);
        }

        if (!empty($searchRes['results'])) {
            // Sort by vote count and popularity to prioritize the main series over obscure specials
            usort($searchRes['results'], function($a, $b) {
                $aVotes = $a['vote_count'] ?? 0;
                $bVotes = $b['vote_count'] ?? 0;
                if ($aVotes !== $bVotes) return $bVotes <=> $aVotes;
                return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
            });
            $foundItem = $searchRes['results'][0];
            $mediaType = 'tv';
            break;
        }

        // Try Movie search
        $movieParams = ['query' => $q];
        if ($year) {
            $movieParams['primary_release_year'] = $year;
        }
        $searchMovie = tmdbApiGet('search/movie', $movieParams);
        if (empty($searchMovie['results']) && isset($movieParams['primary_release_year'])) {
            unset($movieParams['primary_release_year']);
            $searchMovie = tmdbApiGet('search/movie', $movieParams);
        }

        if (!empty($searchMovie['results'])) {
            usort($searchMovie['results'], function($a, $b) {
                $aVotes = $a['vote_count'] ?? 0;
                $bVotes = $b['vote_count'] ?? 0;
                if ($aVotes !== $bVotes) return $bVotes <=> $aVotes;
                return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
            });
            $foundItem = $searchMovie['results'][0];
            $mediaType = 'movie';
            break;
        }
    }

    if (!$foundItem) {
        return null;
    }

    $tmdbId = $foundItem['id'];
    $backdropUrl = !empty($foundItem['backdrop_path']) 
        ? 'https://image.tmdb.org/t/p/original' . $foundItem['backdrop_path'] 
        : null;

    // Fetch Images for official Logos (omit language filter to get all worldwide logos)
    $logoUrl = null;
    $imgRes = tmdbApiGet($mediaType . '/' . $tmdbId . '/images');

    if (!empty($imgRes['logos'])) {
        $logos = $imgRes['logos'];
        // Sort logos: prioritize transparent PNGs with horizontal aspect ratio (typical anime logos)
        usort($logos, function($a, $b) {
            $aPath = $a['file_path'] ?? '';
            $bPath = $b['file_path'] ?? '';
            $aIsPng = (strtolower(substr($aPath, -4)) === '.png') ? 6 : 0;
            $bIsPng = (strtolower(substr($bPath, -4)) === '.png') ? 6 : 0;

            $aLangCode = strtolower($a['iso_639_1'] ?? '');
            $bLangCode = strtolower($b['iso_639_1'] ?? '');

            $aLang = ($aLangCode === 'en') ? 10 : (empty($aLangCode) ? 8 : (($aLangCode === 'hi') ? 7 : (($aLangCode === 'ja') ? 5 : 2)));
            $bLang = ($bLangCode === 'en') ? 10 : (empty($bLangCode) ? 8 : (($bLangCode === 'hi') ? 7 : (($bLangCode === 'ja') ? 5 : 2)));

            // Prefer horizontal logos (aspect ratio between 1.5 and 7.0)
            $aAspect = (float)($a['aspect_ratio'] ?? 1);
            $bAspect = (float)($b['aspect_ratio'] ?? 1);
            $aRatioBonus = ($aAspect >= 1.5 && $aAspect <= 8.0) ? 3 : 0;
            $bRatioBonus = ($bAspect >= 1.5 && $bAspect <= 8.0) ? 3 : 0;

            $aScore = $aLang + $aIsPng + $aRatioBonus + (($a['vote_average'] ?? 0) * 0.2);
            $bScore = $bLang + $bIsPng + $bRatioBonus + (($b['vote_average'] ?? 0) * 0.2);

            return $bScore <=> $aScore;
        });

        if (!empty($logos[0]['file_path'])) {
            $logoUrl = 'https://image.tmdb.org/t/p/w500' . $logos[0]['file_path'];
        }
    }

    // High quality backdrop selection (prioritize top voted ~16:9 landscape backdrop)
    if (!empty($imgRes['backdrops'])) {
        $backdrops = $imgRes['backdrops'];
        usort($backdrops, function($a, $b) {
            $aAspect = (float)($a['aspect_ratio'] ?? 1.78);
            $bAspect = (float)($b['aspect_ratio'] ?? 1.78);
            $aAspectScore = (abs($aAspect - 1.78) < 0.15) ? 5 : 0;
            $bAspectScore = (abs($bAspect - 1.78) < 0.15) ? 5 : 0;
            $aScore = $aAspectScore + ($a['vote_count'] ?? 0);
            $bScore = $bAspectScore + ($b['vote_count'] ?? 0);
            return $bScore <=> $aScore;
        });

        if (!empty($backdrops[0]['file_path'])) {
            $backdropUrl = 'https://image.tmdb.org/t/p/original' . $backdrops[0]['file_path'];
        }
    }

    // If TV series entry has no logo, check franchise movies for official logo
    if (empty($logoUrl) && $mediaType === 'tv') {
        $movieSearch = tmdbApiGet('search/movie', ['query' => $clean]);
        if (!empty($movieSearch['results'])) {
            $movieId = $movieSearch['results'][0]['id'];
            $movieImgs = tmdbApiGet('movie/' . $movieId . '/images');
            if (!empty($movieImgs['logos'])) {
                foreach ($movieImgs['logos'] as $ml) {
                    if (strtolower(substr($ml['file_path'] ?? '', -4)) === '.png') {
                        $logoUrl = 'https://image.tmdb.org/t/p/w500' . $ml['file_path'];
                        break;
                    }
                }
            }
        }
    }

    $result = [
        'tmdb_id' => $tmdbId,
        'title' => $foundItem['name'] ?? $foundItem['title'] ?? $title,
        'backdrop' => $backdropUrl,
        'logo' => $logoUrl
    ];

    @file_put_contents($cacheFile, json_encode($result));
    return $result;
}

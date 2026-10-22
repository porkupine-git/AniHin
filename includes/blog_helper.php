<?php
/**
 * AniXoHindi - Programmatic SEO Blog Engine Helper
 * Automatically generates high-ranking, rich anime blog articles with character details,
 * Hindi dubbing analysis, episode streaming guides, and Schema.org Article structured data.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Fetch rich character and studio metadata from AniList GraphQL with disk cache.
 */
function getAniListRichDetails($anilistId) {
    $anilistId = (int)$anilistId;
    if ($anilistId <= 0) return null;

    $cacheFile = __DIR__ . '/../cache/articles/' . $anilistId . '.json';
    
    // Return cache if fresher than 14 days
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 1209600)) {
        $json = @file_get_contents($cacheFile);
        if ($json) {
            $data = json_decode($json, true);
            if (!empty($data)) return $data;
        }
    }

    $query = '
    query ($id: Int) {
      Media (id: $id, type: ANIME) {
        id
        title { english romaji native }
        description(asHtml: false)
        studios(isMain: true) { nodes { name } }
        characters(sort: ROLE, perPage: 6) {
          edges {
            role
            node {
              name { full }
              image { medium }
            }
            voiceActors(language: JAPANESE) {
              name { full }
            }
          }
        }
      }
    }';

    $payload = json_encode(['query' => $query, 'variables' => ['id' => $anilistId]]);

    $ch = curl_init('https://graphql.anilist.co');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: AniXoHindi/2.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        $media = $data['data']['Media'] ?? null;
        if ($media) {
            @file_put_contents($cacheFile, json_encode($media, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $media;
        }
    }

    return null;
}

/**
 * Generate full programmatic article content for an anime
 */
function generateAnimeBlogContent($anime) {
    $title = $anime['title'] ?? 'Anime';
    $englishTitle = !empty($anime['english_title']) && $anime['english_title'] !== $title ? $anime['english_title'] : '';
    $slug = $anime['slug'] ?? 'anime';
    $genres = !empty($anime['genres']) ? (is_array($anime['genres']) ? $anime['genres'] : explode(',', (string)$anime['genres'])) : ['Action', 'Fantasy'];
    $genres = array_map('trim', $genres);
    $genreListStr = implode(', ', array_slice($genres, 0, 3));
    $year = $anime['release_year'] ?? date('Y');
    $score = !empty($anime['score']) ? number_format((float)$anime['score'], 1) : '8.5';
    $epCount = !empty($anime['episodes']) ? count($anime['episodes']) : ($anime['total_episodes'] ?? '12+');
    $isMovie = ($anime['type'] ?? '') === 'Movie';

    // Enrich with AniList live details if available
    $aniDetails = null;
    if (!empty($anime['anilist_id'])) {
        $aniDetails = getAniListRichDetails($anime['anilist_id']);
    }

    $studio = 'Top Animation Studio';
    if (!empty($aniDetails['studios']['nodes'][0]['name'])) {
        $studio = $aniDetails['studios']['nodes'][0]['name'];
    }

    $characters = [];
    if (!empty($aniDetails['characters']['edges'])) {
        foreach ($aniDetails['characters']['edges'] as $edge) {
            $charName = $edge['node']['name']['full'] ?? '';
            if ($charName) {
                $characters[] = [
                    'name' => $charName,
                    'role' => $edge['role'] ?? 'Supporting',
                    'image' => $edge['node']['image']['medium'] ?? '',
                    'va' => $edge['voiceActors'][0]['name']['full'] ?? 'Original Cast'
                ];
            }
        }
    }

    // Default synopsis
    $rawSynopsis = !empty($anime['synopsis']) ? $anime['synopsis'] : ($aniDetails['description'] ?? '');
    $cleanSynopsis = trim(strip_tags($rawSynopsis));
    if (empty($cleanSynopsis)) {
        $cleanSynopsis = $title . ' is an acclaimed ' . $genreListStr . ' anime series celebrated for its gripping storyline, dynamic battle choreography, and memorable character arcs.';
    }

    // Format article headline and descriptions
    $articleTitle = "Watch " . $title . " in Hindi Dubbed: Complete Storyline, Review & Episode Guide";
    $metaDesc = "Complete guide to watch " . $title . " in Hindi Dubbed. Read storyline breakdown, characters, Hindi voice acting review, and stream all " . $epCount . " episodes in 1080p HD on AniXoHindi.";
    $keywords = $title . " in Hindi, " . $title . " Hindi Dubbed, Watch " . $title . " Online, " . $title . " Hindi Episodes, " . $title . " Review Hindi, Anime in Hindi, AniXoHindi";

    // Dynamic Hindi Dub Analysis Content
    $dubAnalysis = "The Hindi dubbed adaptation of <strong>" . htmlspecialchars($title) . "</strong> brings an authentic, electrifying dimension to Indian anime enthusiasts. Unlike literal translations, the localization team behind this release captures the nuanced emotional tone, fierce battle cries, and humorous banter with cultural resonance while staying faithful to the Japanese canon. Voice artists deliver compelling performances that amplify tension during climactic story arcs, making it an ideal watch for both long-time otaku and newcomers seeking high-quality Hindi dubbing.";

    // Dynamic FAQs
    $faqItems = [
        [
            'q' => "Where can I watch " . $title . " in Hindi Dubbed for free?",
            'a' => "You can stream all available episodes of " . $title . " in authentic Hindi Dubbed online for free in 1080p Full HD on AniXoHindi.cc with high-speed, zero-buffering playback."
        ],
        [
            'q' => "Is " . $title . " fully dubbed in Hindi?",
            'a' => "Yes, " . $title . " features high-definition Hindi audio tracks synchronized with 1080p video masters, accessible directly through our dedicated streaming cockpit."
        ],
        [
            'q' => "How many episodes of " . $title . " are available?",
            'a' => "Our verified catalog hosts " . $epCount . " episodes of " . $title . ", complete with episode titles, high-resolution preview thumbnails, and direct one-click playback."
        ],
        [
            'q' => "Do I need to sign up to stream " . $title . " on AniXoHindi?",
            'a' => "No registration or subscription is required. All episodes are 100% free. Users can optionally log in with Google to save " . $title . " to their personal watchlist."
        ]
    ];

    // Estimated read time
    $totalWordCount = str_word_count($cleanSynopsis . ' ' . $dubAnalysis) + 400;
    $readMinutes = max(3, ceil($totalWordCount / 200));

    return [
        'article_title' => $articleTitle,
        'meta_description' => $metaDesc,
        'keywords' => $keywords,
        'synopsis' => $cleanSynopsis,
        'dub_analysis' => $dubAnalysis,
        'studio' => $studio,
        'characters' => $characters,
        'faq_items' => $faqItems,
        'genre_list' => $genres,
        'read_minutes' => $readMinutes,
        'score' => $score,
        'year' => $year,
        'ep_count' => $epCount,
        'is_movie' => $isMovie,
        'english_title' => $englishTitle
    ];
}

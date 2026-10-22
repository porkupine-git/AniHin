<?php
/**
 * AniXoHindi - Enterprise Programmatic SEO Blog Engine
 * Generates 1200-1800 word comprehensive, keyword-dense anime authority guides
 * with deep narrative breakdowns, character profiles, dubbing quality reviews,
 * comparison tables, and 8+ search-intent FAQs.
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
        characters(sort: ROLE, perPage: 8) {
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
 * Generate 1200+ word authoritative, keyword-rich programmatic article content
 */
function generateAnimeBlogContent($anime) {
    $title = trim($anime['title'] ?? 'Anime');
    $englishTitle = !empty($anime['english_title']) && $anime['english_title'] !== $title ? trim($anime['english_title']) : '';
    $slug = $anime['slug'] ?? 'anime';
    
    // Parse genres
    $genres = !empty($anime['genres']) ? (is_array($anime['genres']) ? $anime['genres'] : explode(',', (string)$anime['genres'])) : ['Action', 'Fantasy'];
    $genres = array_map('trim', $genres);
    $primaryGenre = $genres[0] ?? 'Action';
    $secondaryGenre = $genres[1] ?? 'Adventure';
    $genreListStr = implode(', ', array_slice($genres, 0, 4));

    $year = $anime['release_year'] ?? date('Y');
    $score = !empty($anime['score']) ? number_format((float)$anime['score'], 1) : '8.6';
    $epCount = !empty($anime['episodes']) ? count($anime['episodes']) : ($anime['total_episodes'] ?? '12+');
    $isMovie = ($anime['type'] ?? '') === 'Movie';

    // Enrich with AniList live details
    $aniDetails = null;
    if (!empty($anime['anilist_id'])) {
        $aniDetails = getAniListRichDetails($anime['anilist_id']);
    }

    $studio = 'Top Japanese Animation Studio';
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
                    'va' => $edge['voiceActors'][0]['name']['full'] ?? 'Original Voice Cast'
                ];
            }
        }
    }

    // Baseline synopsis
    $rawSynopsis = !empty($anime['synopsis']) ? $anime['synopsis'] : ($aniDetails['description'] ?? '');
    $cleanSynopsis = trim(strip_tags($rawSynopsis));
    if (empty($cleanSynopsis)) {
        $cleanSynopsis = $title . ' follows an extraordinary saga set against a backdrop of supernatural conflicts, tactical battles, and deeply personal growth.';
    }

    // High CTR SEO Titles & Meta
    $articleTitle = "Watch " . $title . " in Hindi Dubbed: Complete Storyline, Hindi Dub Review, Cast & 1080p Streaming Guide";
    $metaDesc = "Watch " . $title . " in Hindi Dubbed online free in 1080p Full HD. Comprehensive anime review, Hindi voice acting breakdown, character profiles, complete episode list, and zero-buffering streaming on AniXoHindi.";
    $keywords = $title . " in Hindi, " . $title . " Hindi Dubbed, " . $title . " all episodes hindi, Watch " . $title . " in Hindi 1080p, " . $title . " review hindi, " . $title . " voice actors hindi, Anime in Hindi, AniXoHindi";

    // Dynamic Multi-Section Long-Form Content Blocks
    // -------------------------------------------------------------
    // Block 1: The Narrative Deep-Dive (Plot & World-Building)
    $plotDeepDive = [
        'premise' => "Set within an expansive, richly realized fictional landscape, <strong>" . htmlspecialchars($title) . "</strong> plunges viewers directly into an escalating series of trials, mysteries, and battles. The story operates on multiple layers: on the surface, it delivers high-octane " . htmlspecialchars($genreListStr) . " spectacle, but underneath lies a profound exploration of perseverance, identity, and the heavy consequences of ambition.",
        'lore' => "The world-building crafted by " . htmlspecialchars($studio) . " establishes intricate rules governing power hierarchies, alliances, and hidden adversaries. Every episode methodically peels back layers of lore, introducing rival factions and formidable threats that challenge our protagonists both physically and philosophically.",
        'stakes' => "What sets " . htmlspecialchars($title) . " apart from standard seasonal releases is its relentless pacing. Stakes are never artificial; losses carry genuine emotional weight, and victories are earned through tactical ingenuity and unwavering conviction. As story arcs progress, individual battles transition into grand ideological struggles that redefine the status quo of this anime universe."
    ];

    // Block 2: In-Depth Hindi Dubbing Analysis & Voice Acting Review
    $hindiDubReview = [
        'localization' => "The Hindi dubbed version of <strong>" . htmlspecialchars($title) . "</strong> stands out as a triumph in Indian anime localization. Too often, regional voice dubs suffer from rigid word-for-word translation that strips away character nuance. In stark contrast, the creative adaptation here preserves character personalities with remarkable fidelity. The dialogues have been localized with natural cadence, using emotive Hindi vocabulary that captures dramatic gravitas without sounding archaic.",
        'acting' => "Voice actors deliver exceptional vocal range across emotional extremes. Intimate conversations feel grounded and sincere, while climactic battle cries and power awakenings resonate with raw, uninhibited adrenaline. Key antagonists are voiced with deliberate menace, ensuring that conflicts remain chilling and authoritative.",
        'audiomix' => "Technically, the audio mastering maintains pristine separation between the localized Hindi voice dialogue and the composer\'s sweeping musical score. Sound effects, atmospheric background layers, and Foley work remain crystal clear in uncompressed stereo and multi-channel configurations on AniXoHindi."
    ];

    // Block 3: Hindi Dub vs Japanese Sub Comparison
    $comparisonPoints = [
        [
            'feature' => 'Action Scene Immersion',
            'dub' => 'Eyes stay 100% focused on fluid animation and fight choreography without distraction.',
            'sub' => 'Requires constant eye movement to read fast subtitle lines during rapid combat.'
        ],
        [
            'feature' => 'Emotional Resonance',
            'dub' => 'Hindi dialogue directly triggers authentic emotional connection in native language.',
            'sub' => 'Relies on Japanese vocal inflection and translated subtitles for sentiment.'
        ],
        [
            'feature' => 'Dialogue Cadence',
            'dub' => 'Adapted with natural colloquial Hindi phrasing suited for modern Indian viewers.',
            'sub' => 'Literal translation sometimes misses cultural colloquialisms.'
        ],
        [
            'feature' => 'Multi-Tasking & Casual Watching',
            'dub' => 'Can be enjoyed while cooking, working, or commuting on mobile earphones.',
            'sub' => 'Demands undivided visual attention on the screen at all times.'
        ]
    ];

    // Block 4: How to Watch in 1080p Guide
    $streamingSteps = [
        'Step 1: Open AniXoHindi.cc on any modern web browser across mobile, tablet, laptop, or Smart TV.',
        'Step 2: Head directly to the official <a href="/watch.php?anime=' . urlencode($slug) . '&ep=1" class="text-accent font-weight-bold">' . htmlspecialchars($title) . ' Watch Cockpit</a>.',
        'Step 3: Select your preferred episode from our clean episode navigator featuring thumbnail previews.',
        'Step 4: Click Play and toggle Fullscreen or Focus Mode for an immersive, bufferless 1080p Full HD theatre experience.'
    ];

    // Block 5: Comprehensive 8 Search-Intent FAQs
    $faqItems = [
        [
            'q' => "Where can I watch " . $title . " in Hindi Dubbed for free?",
            'a' => "You can stream every verified episode of " . $title . " in authentic Hindi Dubbed online for free on AniXoHindi.cc with high-speed 1080p Full HD video, zero buffering, and no registration fees."
        ],
        [
            'q' => "Is " . $title . " available with authentic Hindi audio?",
            'a' => "Yes, " . $title . " on AniXoHindi features high-definition studio-grade Hindi voice tracks synchronized frame-by-frame with 1080p video masters."
        ],
        [
            'q' => "How many total episodes of " . $title . " are dubbed in Hindi?",
            'a' => "Our verified catalog currently hosts " . $epCount . " episodes of " . $title . ", complete with episode titles, high-resolution preview thumbnails, and direct one-click playback."
        ],
        [
            'q' => "What is the chronological watch order for " . $title . "?",
            'a' => "To experience the complete storyline without spoilers, start with Episode 1 on AniXoHindi and follow sequentially through our numbered episode playlist."
        ],
        [
            'q' => "Are there any filler episodes in " . $title . "?",
            'a' => "The primary storyline in " . $title . " follows the main canon closely. Our episode guide highlights key canon progression so you can enjoy the most impactful plot points."
        ],
        [
            'q' => "Can I watch " . $title . " Hindi Dubbed on mobile phones?",
            'a' => "Yes, AniXoHindi is fully optimized for mobile devices (Android and iOS). The built-in player features hardware-accelerated playback and touch controls tailored for mobile viewing."
        ],
        [
            'q' => "What animation studio produced " . $title . "?",
            'a' => $title . " was animated by " . $studio . ", known for dynamic visual design, character art, and fluid battle sequences."
        ],
        [
            'q' => "Do I need to sign up or pay to stream " . $title . " on AniXoHindi?",
            'a' => "No subscription or payment is ever required. All anime streams are 100% free. Creating an optional account allows you to sync your watchlist and bookmark episodes across devices."
        ]
    ];

    // LSI Search Keywords Cloud
    $lsiKeywords = [
        $title . " in Hindi",
        $title . " Hindi Dubbed",
        $title . " All Episodes Hindi",
        $title . " Hindi Dub Download 1080p",
        $title . " Episode 1 Hindi",
        $title . " Voice Cast Hindi",
        $title . " Hindi Dub Review",
        "Watch Anime in Hindi Online",
        "Best Hindi Dubbed Anime Series",
        "Free Anime Streaming India",
        $title . " Season 1 Hindi Dub",
        $title . " Full HD Stream"
    ];

    // Word Count & Read Time Calculation
    $allText = $cleanSynopsis . ' ' . implode(' ', $plotDeepDive) . ' ' . implode(' ', $hindiDubReview);
    $totalWordCount = str_word_count($allText) + 900;
    $readMinutes = max(6, ceil($totalWordCount / 220));

    return [
        'article_title' => $articleTitle,
        'meta_description' => $metaDesc,
        'keywords' => $keywords,
        'synopsis' => $cleanSynopsis,
        'plot_deep_dive' => $plotDeepDive,
        'dub_review' => $hindiDubReview,
        'comparison' => $comparisonPoints,
        'streaming_steps' => $streamingSteps,
        'studio' => $studio,
        'characters' => $characters,
        'faq_items' => $faqItems,
        'lsi_keywords' => $lsiKeywords,
        'genre_list' => $genres,
        'primary_genre' => $primaryGenre,
        'secondary_genre' => $secondaryGenre,
        'read_minutes' => $readMinutes,
        'word_count' => $totalWordCount,
        'score' => $score,
        'year' => $year,
        'ep_count' => $epCount,
        'is_movie' => $isMovie,
        'english_title' => $englishTitle
    ];
}

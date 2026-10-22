<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');

$catalog = buildHindiCatalog();
$baseUrl = 'https://anixohindi.cc';
$today = date('Y-m-d');

$genres = [
    'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 
    'Horror', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 
    'Supernatural', 'Super Power', 'Suspense', 'Thriller'
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Core Hub Pages -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/browse.php?type=dub</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.95</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/browse.php?type=Movie</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/browse.php?status=Currently+Airing</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.90</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/browse.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>

    <!-- Genre Category Hubs -->
    <?php foreach ($genres as $genre): ?>
    <url>
        <loc><?= $baseUrl ?>/browse.php?genre=<?= urlencode($genre) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.75</priority>
    </url>
    <?php endforeach; ?>

    <!-- Individual Hindi Anime Series & Episodes -->
    <?php foreach ($catalog as $item): 
        $slug = $item['slug'];
        $title = htmlspecialchars($item['title'] ?? 'Anime', ENT_XML1, 'UTF-8');
        $desc = htmlspecialchars(!empty($item['synopsis']) ? mb_substr(strip_tags($item['synopsis']), 0, 200) : "Watch {$title} Hindi Dubbed in 1080p Full HD on AniXoHindi.", ENT_XML1, 'UTF-8');
        $poster = htmlspecialchars($item['poster'] ?? '', ENT_XML1, 'UTF-8');
        $banner = htmlspecialchars($item['banner'] ?? $item['poster'] ?? '', ENT_XML1, 'UTF-8');
        $embedId = (int)($item['anilist_id'] ?? $item['id'] ?? 1);
        $watchUrl = "{$baseUrl}/watch.php?anime=" . urlencode($slug) . "&amp;ep=1";
    ?>
    <url>
        <loc><?= $watchUrl ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
        <?php if (!empty($poster)): ?>
        <image:image>
            <image:loc><?= $poster ?></image:loc>
            <image:title><?= $title ?> Hindi Dubbed Poster</image:title>
        </image:image>
        <?php endif; ?>
        <video:video>
            <video:thumbnail_loc><?= !empty($banner) ? $banner : $poster ?></video:thumbnail_loc>
            <video:title><?= $title ?> - Episode 1 (Hindi Dubbed)</video:title>
            <video:description><?= $desc ?></video:description>
            <video:player_loc allow_embed="yes">https://embed.anixohindi.cc/embed/ani/<?= $embedId ?>/1</video:player_loc>
            <video:family_friendly>yes</video:family_friendly>
            <video:in_language>hi</video:in_language>
        </video:video>
    </url>
    <?php endforeach; ?>

</urlset>

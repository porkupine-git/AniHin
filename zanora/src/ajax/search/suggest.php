<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
header("Content-Type: application/json; charset=utf-8");

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword'] ?? '');

    if ($keyword === '') {
        $noKeyword = '
            <a href="javascript:;" class="nav-item">
                <span>No keyword provided</span>
                <div class="clearfix"></div>
            </a>';
        echo json_encode(["status" => true, "html" => $noKeyword]);
        exit;
    }

    // 🔑 Unique cache key per keyword
    $cacheKey = 'search_suggest_' . md5($keyword);

    // 🔗 API endpoint
    $apiUrl = "$zpi/search/suggest?keyword=" . urlencode($keyword);

    try {
        // ✅ Get JSON from API with DB cache (api_cache table)
        $data = getCachedApiJson($cacheKey, $apiUrl, 300);

        // If completely failed (no API + no stale cache)
        if (!$data) {
            $errorHtml = '
                <a href="javascript:;" class="nav-item">
                    <span>Failed to fetch results. Please try again later.</span>
                    <div class="clearfix"></div>
                </a>';
            echo json_encode(["status" => true, "html" => $errorHtml]);
            exit;
        }

        // ✅ Check valid structure
        if (
            isset($data['success']) &&
            $data['success'] &&
            isset($data['results']) &&
            is_array($data['results']) &&
            count($data['results']) > 0
        ) {
            $results = $data['results'];
            $html = "";

            foreach ($results as $anime) {
                $html .= '
                    <a href="/' . htmlspecialchars($anime['id']) . '" class="nav-item" data-id="' . htmlspecialchars($anime['data_id'] ?? '') . '">
                        <div class="film-poster">
                            <img loading="lazy" decoding="async" data-src="' . htmlspecialchars($anime['poster']) . '" class="film-poster-img lazyload" alt="' . htmlspecialchars($anime['title']) . '">
                        </div>
                        <div class="srp-detail">
                            <h3 class="film-name" class="dynamic-name" data-jname="' . htmlspecialchars($anime['japanese_title'] ?? '') . '">' . htmlspecialchars($anime['title'] ?? 'Untitled') . '</h3>
                            <div class="alias-name">' . htmlspecialchars($anime['japanese_title'] ?? '') . '</div>
                            <div class="film-infor">
                                <span>' . htmlspecialchars($anime['releaseDate'] ?? '') . '</span>
                                <i class="dot"></i>' . htmlspecialchars($anime['showType'] ?? '') . '
                                <i class="dot"></i><span>' . htmlspecialchars($anime['duration'] ?? '') . '</span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </a>';
            }

            // ✅ Add “View All Results” link at bottom
            $html .= '
                <a href="/search?keyword=' . urlencode($keyword) . '" class="nav-item nav-bottom">
                    View all results<i class="fa fa-angle-right ml-2"></i>
                </a>';

            echo json_encode(["status" => true, "html" => $html]);
        } else {
            // ✅ No result
            $noResult = '
                <a href="javascript:;" class="nav-item">
                    <span>No results found!</span>
                    <div class="clearfix"></div>
                </a>';
            echo json_encode(["status" => true, "html" => $noResult]);
        }
    } catch (Throwable $e) {
        $errorHtml = '
            <a href="javascript:;" class="nav-item">
                <span>Error: ' . htmlspecialchars($e->getMessage()) . '</span>
                <div class="clearfix"></div>
            </a>';
        echo json_encode(["status" => true, "html" => $errorHtml]);
    }
} else {
    $noKeyword = '
        <a href="javascript:;" class="nav-item">
            <span>No keyword provided</span>
            <div class="clearfix"></div>
        </a>';
    echo json_encode(["status" => true, "html" => $noKeyword]);
}

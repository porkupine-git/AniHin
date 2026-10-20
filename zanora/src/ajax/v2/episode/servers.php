<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');

header("Content-Type: application/json; charset=utf-8");

define('SERVERS_CACHE_TTL', 3600);

$episodeId = $_GET['episodeId'] ?? null;

if (!$episodeId) {

    echo json_encode([
        'status' => false,
        'msg' => 'Missing episodeId'
    ]);

    exit;
}

if (!preg_match('/^([0-9]+)-([0-9]+)$/', $episodeId, $matches)) {

    echo json_encode([
        'status' => false,
        'msg' => 'Invalid episodeId'
    ]);

    exit;
}

$animeId  = $matches[1];
$episodeNo = $matches[2];

$cacheKey = "servers:" . $episodeId;

$cached = cacheGet($cacheKey);

if ($cached !== null) {

    echo json_encode(
        $cached,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$apiUrl = rtrim($zpi, '/') . '/servers/' . $episodeId;

$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_USERAGENT => "Mozilla/5.0"
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {

    echo json_encode([
        'status' => false,
        'msg' => 'Failed to fetch servers'
    ]);

    curl_close($ch);

    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (
    !$data ||
    empty($data['success'])
) {

    echo json_encode([
        'status' => false,
        'msg' => 'Invalid server response'
    ]);

    exit;
}

$subServers = $data['sub'] ?? [];
$dubServers = $data['dub'] ?? [];

function mapServerName($name, $raw = false) {

    $mapped = match($name) {

        'HD-1' => 'Aika',
        'HD-2' => 'Envy',
        'HD-3' => 'Levi',

        'HD-4' => $raw
            ? 'yuki'
            : '
<span class="yuki-server">
    <svg class="yuki-crown" viewBox="0 0 24 24" fill="currentColor">
        <path d="M5 16L3 7l5 4 4-6 4 6 5-4-2 9H5zm0 2h14v2H5v-2z"/>
    </svg>
    Yuki
</span>',

        default => $raw
            ? strtolower($name)
            : $name
    };

    return $mapped;
}

ob_start();
?>

<div class="ps_-status servers-status">

    <div class="content">

        <div class="server-notice">

            <strong>
                Currently watching
                <b>Episode <?= (int)$episodeNo ?></b>
            </strong>

            Click on the servers manually in case of error.

        </div>

    </div>

</div>

<div class="servers-content">

    <div class="loading-relative"
         id="servers-loading"
         style="display:none;">

        <div class="loading">

            <div class="span1"></div>
            <div class="span2"></div>
            <div class="span3"></div>

        </div>

    </div>

    <?php if (!empty($subServers)): ?>

    <div class="ps_-block ps_-block-sub servers-sub">

        <div class="ps__-title">

            <i class="fa-regular fa-closed-captioning"></i>
            SUB:

        </div>

        <div class="ps__-list">

            <?php foreach ($subServers as $index => $server): ?>

                <?php

                    $serverName = mapServerName(
                        $server['serverName'] ?? 'Server'
                    );
                    
                    $serverSlug = mapServerName(
                        $server['serverName'] ?? 'Server',
                        true
                    );

                    $serverId = htmlspecialchars(
                        $server['serverId'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                ?>

                <div class="item server-item"
                     data-id="<?= $serverId ?>"
                     data-type="sub"
                     data-server-id="<?= $serverSlug ?>-sub">

                    <button
                        class="btn <?= $index === 0 ? 'active' : '' ?>">

                        <?= $serverName ?>

                    </button>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

    <?php endif; ?>

    <?php if (!empty($dubServers)): ?>

    <div class="ps_-block ps_-block-dub servers-dub">

        <div class="ps__-title">

            <i class="fa-solid fa-microphone-lines"></i>
            DUB:

        </div>

        <div class="ps__-list">

            <?php foreach ($dubServers as $index => $server): ?>

                <?php

                    $serverName = mapServerName(
                        $server['serverName'] ?? 'Server'
                    );
                    
                    $serverSlug = mapServerName(
                        $server['serverName'] ?? 'Server',
                        true
                    );

                    $serverId = htmlspecialchars(
                        $server['serverId'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                ?>

                <div class="item server-item"
                     data-id="<?= $serverId ?>"
                     data-type="dub"
                     data-server-id="<?= $serverSlug ?>-dub">

                    <button
                        class="btn <?= $index === 0 ? 'active' : '' ?>">

                        <?= $serverName ?>

                    </button>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="clearfix"></div>

        <div id="source-guide"></div>

    </div>

    <?php endif; ?>

</div>

<?php

$html = ob_get_clean();

$output = [
    'status' => true,
    'html' => $html
];

cacheSet(
    $cacheKey,
    $output,
    SERVERS_CACHE_TTL
);

echo json_encode(
    $output,
    JSON_UNESCAPED_UNICODE
);

exit;
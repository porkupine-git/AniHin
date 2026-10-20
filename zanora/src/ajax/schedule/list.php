<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$tzOffset = isset($_GET['tzOffset']) ? $_GET['tzOffset'] : -300;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$api_url = "$zpi/schedule?tzOffset=$tzOffset&date=$date";

$response = @file_get_contents($api_url);
if ($response === FALSE) {
    echo json_encode(["html" => "<div>Error loading schedule</div>"]);
    exit;
}

$data = json_decode($response, true);
if (!isset($data['success']) || !$data['success'] || empty($data['results'])) {
    echo json_encode(["html" => "<div>No schedule available</div>"]);
    exit;
}

$html = "";
foreach ($data['results'] as $anime) {
    $id = htmlspecialchars($anime['id']);
    $time = htmlspecialchars($anime['time']);
    $title = htmlspecialchars($anime['title']);
    $jname = htmlspecialchars($anime['japanese_title']);
    $episode = htmlspecialchars($anime['episode_no']);

    $html .= "
    <li>
        <a href='/$id' class='tsl-link'>
            <div class='time'>$time</div>
            <div class='film-detail'>
                <h3 class='film-name dynamic-name' data-jname='$jname'>$title</h3>
                <div class='fd-play'>
                    <button type='button' class='btn btn-sm btn-play'>
                        <i class='fas fa-play mr-2'></i>Episode $episode
                    </button>
                </div>
            </div>
        </a>
    </li>";
}

echo json_encode(["html" => $html]);
?>

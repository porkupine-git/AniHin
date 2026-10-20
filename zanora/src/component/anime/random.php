<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
$getAnime = file_get_contents("$zpi/random");
$getAnime = json_decode($getAnime, true);
if (isset($getAnime['success']) && $getAnime['success'] === true && isset($getAnime['results'])) {
    $animeId = $getAnime['results']['id'];
    $newURL = "$websiteUrl/$animeId";
    header('Location: '.$newURL);
    exit;
} else {  
    header('Location: '.$websiteUrl.'/404');
    exit;
}
?>
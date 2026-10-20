<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

function fetchAnimeData($animeId) {
    global $zpi;

    if (!$animeId) return false;

    if (ctype_digit((string)$animeId)) {

        $realAniListId =
            (int)$animeId;

    } else {

    $searchTitle =
        trim($animeId);

    if (
        substr_count(
            $searchTitle,
        '-'
        ) >= 2
    ) {

        $searchTitle =
            preg_replace(
                '/-\d+$/',
                '',
                $searchTitle
            );
    }

    $resolveCacheKey =
        "resolve_" .
        md5($searchTitle);

    $realAniListId =
        redis()->get($resolveCacheKey);

    if (!$realAniListId) {

        $resolveUrl =
            $zpi .
            "/search/suggest?keyword=" .
            urlencode($searchTitle);

        $resolveResponse =
            @file_get_contents(
                $resolveUrl
            );

        if (!$resolveResponse) {
            return false;
        }

        $resolveJson =
            json_decode(
                $resolveResponse,
                true
            );

        if (
            empty($resolveJson["success"]) ||
            empty($resolveJson["results"][0]["id"])
        ) {
            return false;
        }

        $realAniListId =
            $resolveJson["results"][0]["id"];

        redis()->setex(
            $resolveCacheKey,
            86400,
            $realAniListId
        );
    }
    }

    $cacheKey =
        "anime_" .
        $realAniListId;

    $url =
        "$zpi/info?id=" .
        $realAniListId;

    $apiData =
        getCachedApiJson(
            $cacheKey,
            $url,
            86400
        );

    // If total failure and no cache
    if (
        !$apiData ||
        empty($apiData["success"]) ||
        empty($apiData["results"]["data"])
    ) {
        return false;
    }

    // Extract main parts
    $data       = $apiData["results"]["data"];
    $animeInfo  = $data["animeInfo"] ?? [];
    $tvInfo     = $animeInfo["tvInfo"] ?? [];
    $seasons    = $data["seasons"] ?? [];

    /* ------------------------------
        1. Seasons
    ------------------------------ */
    $seasonList = [];
    foreach ($seasons as $season) {
        $seasonList[] = [
            "id"        => $season["id"] ?? null,
            "name"      => $season["title"] ?? null,
            "title"     => $season["season"] ?? null,
            "poster"    => $season["season_poster"] ?? null,
            "isCurrent" => (($season["id"] ?? null) == $realAniListId)
        ];
    }

    /* ------------------------------
        2. Characters & Voice Actors
    ------------------------------ */
    $chList = [];
    foreach ($data["charactersVoiceActors"] ?? [] as $character) {
        $actors = [];
        foreach ($character["voiceActors"] ?? [] as $actor) {
            $actors[] = [
                "id"     => $actor["id"] ?? null,
                "name"   => $actor["name"] ?? "Unknown voice actor",
                "poster" => $actor["poster"] ?? null
            ];
        }

        $chList[] = [
            "character" => [
                "id"     => $character["character"]["id"] ?? null,
                "name"   => $character["character"]["name"] ?? "Unknown character",
                "poster" => $character["character"]["poster"] ?? null,
                "cast"   => $character["character"]["cast"] ?? null
            ],
            "voiceActors" => $actors
        ];
    }

    /* ------------------------------
        3. Helper for Related/Popular/Recommended
    ------------------------------ */
    $mapList = function ($list) {
        $arr = [];
        foreach ($list as $a) {
            $arr[] = [
                "id"         => $a["id"] ?? null,
                "data_id"    => $a["data_id"] ?? null,
                "name"       => $a["title"] ?? ($a["japanese_title"] ?? null),
                "japa"       => $a["japanese_title"] ?? null,
                "poster"     => $a["poster"] ?? null,
                "duration"   => $a["tvInfo"]["duration"] ?? null,
                "type"       => $a["tvInfo"]["showType"] ?? null,
                "adultContent" => $a["adultContent"] ?? false,
                "episodes" => [
                    "sub" => $a["tvInfo"]["episodeInfo"]["sub"] ?? ($a["tvInfo"]["episodeInfo"]["eps"] ?? null),
                    "dub" => $a["tvInfo"]["episodeInfo"]["dub"] ?? null,
                    "eps" => $a["tvInfo"]["episodeInfo"]["eps"] ?? null
                ]
            ];
        }
        return $arr;
    };

    $recommendedAnimeList = $mapList($data["recommended_data"] ?? []);
    $popularAnimeList     = $mapList($data["popular_data"] ?? []);
    $relatedAnimeList     = $mapList($data["related_data"] ?? []);

    /* ------------------------------
        4. FINAL normalised structure
    ------------------------------ */
    
    return [
        "id"        => $data["id"] ?? null,
        "malId"     => $data["malId"] ?? null,
        "anilistId" => $data["id"] ?? null,
        "poster" => $data["poster"] ?? "/images/no-thumb.jpg",
        "banner" => $data["banner"] ?? $data["poster"],
        "data_id"   => $data["data_id"] ?? null,
        "title"     => $data["title"] ?? null,
        "japa"      => $data["japanese_title"] ?? null,
        "japanese"  => $animeInfo["Japanese"] ?? null,
        "synonyms"  => $animeInfo["synonyms"] ?? null,
        "overview"  => $animeInfo["Overview"] ?? "No description",
        "showType"  => $tvInfo["showType"] ?? null,
        "rating"    => $tvInfo["rating"] ?? null,
        "subEp"     => $tvInfo["sub"] ?? null,
        "dubEp"     => $tvInfo["dub"] ?? 0,
        "eps"       => $tvInfo["eps"] ?? null,
        "aired"     => $animeInfo["Aired"] ?? null,
        "premiered" => $animeInfo["Premiered"] ?? null,
        "malscore"  => $animeInfo["MAL Score"] ?? null,
        "status"    => $animeInfo["Status"] ?? null,
        "genres"    => $animeInfo["Genres"] ?? [],
        "quality"   => $tvInfo["quality"] ?? null,
        "duration"  => $tvInfo["duration"] ?? ($animeInfo["Duration"] ?? null),

        "actors"           => $chList,
        "studio"           => $animeInfo["Studios"] ?? null,
        "producer"         => $animeInfo["Producers"] ?? [],

        "season"           => $seasonList,
        "relatedAnimes"    => $relatedAnimeList,
        "popularAnimes"    => $popularAnimeList,
        "recommendedAnimes"=> $recommendedAnimeList,

        "adultContent" => $data["adultContent"] ?? false,
        "trailers"     => $animeInfo["trailers"] ?? []
    ];
}

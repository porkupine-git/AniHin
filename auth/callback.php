<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';

if (!$code) {
    header('Location: /index.php?error=no_code');
    exit;
}

$userData = null;

if ($provider === 'google') {
    // Exchange code for Google Access Token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $tokenJson = json_decode($res, true);

    if (!empty($tokenJson['access_token'])) {
        // Fetch user info from Google
        $userUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenJson['access_token'];
        $userRes = @file_get_contents($userUrl);
        $googleUser = json_decode($userRes, true);

        if (!empty($googleUser['id'])) {
            $userData = [
                'provider' => 'google',
                'oauth_id' => $googleUser['id'],
                'username' => $googleUser['name'] ?? 'GoogleUser',
                'email' => $googleUser['email'] ?? null,
                'avatar' => $googleUser['picture'] ?? null
            ];
        }
    }
} elseif ($provider === 'anilist') {
    // Exchange code for AniList Access Token
    $tokenUrl = 'https://anilist.co/api/v2/oauth/token';
    $postData = [
        'grant_type' => 'authorization_code',
        'client_id' => ANILIST_CLIENT_ID,
        'client_secret' => ANILIST_CLIENT_SECRET,
        'redirect_uri' => ANILIST_REDIRECT_URI,
        'code' => $code
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $tokenJson = json_decode($res, true);

    if (!empty($tokenJson['access_token'])) {
        // Query AniList GraphQL for Viewer details
        $query = 'query { Viewer { id name avatar { medium } } }';
        $chGql = curl_init('https://graphql.anilist.co');
        curl_setopt_array($chGql, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['query' => $query]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $tokenJson['access_token']
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $gqlRes = curl_exec($chGql);
        curl_close($chGql);
        $gqlData = json_decode($gqlRes, true);

        if (!empty($gqlData['data']['Viewer']['id'])) {
            $v = $gqlData['data']['Viewer'];
            $userData = [
                'provider' => 'anilist',
                'oauth_id' => (string)$v['id'],
                'username' => $v['name'],
                'email' => null,
                'avatar' => $v['avatar']['medium'] ?? null
            ];
        }
    }
}

if ($userData) {
    // Save to Database if connected
    if ($db_connected && $conn) {
        $stmt = $conn->prepare("INSERT INTO users (oauth_provider, oauth_id, username, email, avatar) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE username = VALUES(username), avatar = VALUES(avatar)");
        $stmt->bind_param("sssss", $userData['provider'], $userData['oauth_id'], $userData['username'], $userData['email'], $userData['avatar']);
        $stmt->execute();
        $userData['id'] = $stmt->insert_id ?: $conn->insert_id;
    }

    $_SESSION['user'] = $userData;
    header('Location: /index.php?login_success=1');
    exit;
}

header('Location: /index.php?error=auth_failed');
exit;

<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$provider = $_GET['provider'] ?? ($_GET['state'] ?? 'google');
$code = $_GET['code'] ?? '';

if (!$code) {
    header('Location: /index.php?error=no_code');
    exit;
}

$userData = null;

if ($provider === 'google') {
    // Exchange authorization code for Google Access Token
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
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'AniXoHindi/1.0'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $tokenJson = json_decode($res, true);

    if (!empty($tokenJson['access_token'])) {
        // Fetch user profile info with Bearer Authorization header
        $userUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $chUser = curl_init($userUrl);
        curl_setopt_array($chUser, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $tokenJson['access_token'],
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'AniXoHindi/1.0'
        ]);
        $userRes = curl_exec($chUser);
        curl_close($chUser);
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
    } else {
        error_log('Google token error: ' . ($res ?: 'No response'));
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
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'AniXoHindi/1.0'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $tokenJson = json_decode($res, true);

    if (!empty($tokenJson['access_token'])) {
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
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'AniXoHindi/1.0'
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
    // Save to SQLite users table
    $pdo = function_exists('getHindiPdo') ? getHindiPdo() : null;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (oauth_provider, oauth_id, username, email, avatar) 
                VALUES (?, ?, ?, ?, ?) 
                ON CONFLICT(oauth_provider, oauth_id) DO UPDATE SET 
                    username = excluded.username, 
                    avatar = excluded.avatar, 
                    last_login = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                $userData['provider'],
                $userData['oauth_id'],
                $userData['username'],
                $userData['email'],
                $userData['avatar']
            ]);

            $idStmt = $pdo->prepare("SELECT id FROM users WHERE oauth_provider = ? AND oauth_id = ?");
            $idStmt->execute([$userData['provider'], $userData['oauth_id']]);
            $uRow = $idStmt->fetch();
            if ($uRow) {
                $userData['id'] = (int)$uRow['id'];
            }
        } catch (Exception $e) {
            error_log('User save error: ' . $e->getMessage());
        }
    }

    if (empty($userData['id'])) {
        $userData['id'] = 1;
    }

    $_SESSION['user'] = $userData;

    // Return to the page user was previously on
    $returnUrl = $_SESSION['auth_redirect_after'] ?? '/index.php?login_success=1';
    unset($_SESSION['auth_redirect_after']);
    header('Location: ' . $returnUrl);
    exit;
}

header('Location: /index.php?error=auth_failed');
exit;

<?php
// anilist_callback.php - Process AniList OAuth callback
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// 1. Validate state to prevent CSRF
if (!$state || !isset($_SESSION['anilist_oauth_state']) || $state !== $_SESSION['anilist_oauth_state']) {
    die("OAuth state validation failed. Possible CSRF attempt.");
}
unset($_SESSION['anilist_oauth_state']);

if (!$code) {
    die("Authorization code missing.");
}

$clientId = getenv('ANILIST_CLIENT_ID') ?: 'placeholder_client_id';
$clientSecret = getenv('ANILIST_CLIENT_SECRET') ?: 'placeholder_client_secret';
$redirectUri = getenv('ANILIST_REDIRECT_URI') ?: (WEBSITE_URL . '/auth/anilist/callback');

// 2. Exchange code for access token
$ch = curl_init("https://anilist.co/api/v2/oauth/token");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'grant_type'    => 'authorization_code',
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri'  => $redirectUri,
        'code'          => $code
    ]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT        => 15
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    die("Failed to retrieve access token from AniList. Error code: " . $httpCode);
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? '';
$refreshToken = $tokenData['refresh_token'] ?? '';
$expiresIn = $tokenData['expires_in'] ?? 0;
$expiresAt = time() + $expiresIn;

if (!$accessToken) {
    die("Access token not found in OAuth response.");
}

// 3. Fetch user details from AniList API (GraphQL)
$graphqlQuery = 'query { Viewer { id name avatar { large } } }';
$ch = curl_init("https://graphql.anilist.co");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'query' => $graphqlQuery
    ]),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT        => 15
]);
$profileResponse = curl_exec($ch);
$profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($profileHttpCode !== 200 || !$profileResponse) {
    die("Failed to fetch profile details from AniList API.");
}

$profileData = json_decode($profileResponse, true);
$viewer = $profileData['data']['Viewer'] ?? null;

if (!$viewer) {
    die("Failed to parse Viewer details from AniList response.");
}

$anilistId = (int)$viewer['id'];
$anilistUsername = $viewer['name'];
$anilistAvatar = $viewer['avatar']['large'] ?? '';

// 4. Handle linking/login
$currentUserId = $_SESSION['userID'] ?? null;

if ($currentUserId) {
    // A user is already logged in locally. Try to link their account.
    // FIRST: Check if this AniList ID is already linked to another local account
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE anilist_id = ? AND id != ? LIMIT 1");
    $stmt->bind_param("ii", $anilistId, $currentUserId);
    $stmt->execute();
    $dupUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dupUser) {
        // Already linked to someone else!
        die("This AniList account is already connected to another Zanora profile (" . htmlspecialchars($dupUser['username']) . "). Please disconnect it there first.");
    }

    // Link current user
    $stmt = $conn->prepare("
        UPDATE users 
        SET anilist_id = ?, anilist_username = ?, anilist_avatar = ?, 
            anilist_token = ?, anilist_refresh_token = ?, anilist_token_expires = ?
        WHERE id = ?
    ");
    $stmt->bind_param("issssii", $anilistId, $anilistUsername, $anilistAvatar, $accessToken, $refreshToken, $expiresAt, $currentUserId);
    $stmt->execute();
    $stmt->close();

    header("Location: /user/connections");
    exit();
} else {
    // No user logged in locally. Try to log them in via linked AniList account, or create a new user.
    $stmt = $conn->prepare("SELECT id, username, roles FROM users WHERE anilist_id = ? LIMIT 1");
    $stmt->bind_param("i", $anilistId);
    $stmt->execute();
    $existingUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingUser) {
        $userId = $existingUser['id'];
        $username = $existingUser['username'];
        $roles = (int)$existingUser['roles'];

        // Update tokens
        $stmt = $conn->prepare("UPDATE users SET anilist_token = ?, anilist_refresh_token = ?, anilist_token_expires = ? WHERE id = ?");
        $stmt->bind_param("ssii", $accessToken, $refreshToken, $expiresAt, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        // Create new account (Guest conversion / registration via AniList)
        // Ensure username is unique
        $baseUsername = $anilistUsername;
        $username = $baseUsername;
        $counter = 1;
        while (true) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$exists) break;
            $username = $baseUsername . $counter;
            $counter++;
        }

        $dummyEmail = $username . "@anilist.zanora.lol";
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
        $publicTokenPlain = bin2hex(random_bytes(32));
        $publicTokenHashed = hash('sha256', $publicTokenPlain);
        $roles = 0;

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, image, is_verified, public_token, created_at, 
                               anilist_id, anilist_username, anilist_avatar, anilist_token, anilist_refresh_token, anilist_token_expires)
            VALUES (?, ?, ?, ?, 1, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssisssssi", $username, $dummyEmail, $hashedPassword, $anilistAvatar, $publicTokenHashed, 
                           $anilistId, $anilistUsername, $anilistAvatar, $accessToken, $refreshToken, $expiresAt);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();
    }

    // Authenticate session
    session_regenerate_id(true);
    $_SESSION['userID'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['roles'] = $roles;

    // Set persistent session token
    $tokenPlain = bin2hex(random_bytes(32));
    $tokenHashed = hash('sha256', $tokenPlain);
    $sessionExpires = time() + (86400 * 30); // 30 days

    $stmt2 = $conn->prepare("
        INSERT INTO user_sessions (user_id, token_hash, expires_at, created_at, ip, user_agent)
        VALUES (?, ?, FROM_UNIXTIME(?), NOW(), ?, ?)
    ");
    $ip = getClientIP();
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt2->bind_param("isiss", $userId, $tokenHashed, $sessionExpires, $ip, $agent);
    $stmt2->execute();
    $stmt2->close();

    setcookie('session_token', $tokenPlain, [
        'expires'  => $sessionExpires,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    header("Location: /home");
    exit();
}
?>

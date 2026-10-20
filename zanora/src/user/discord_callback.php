<?php
// discord_callback.php - Process Discord OAuth callback
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// 1. Validate state to prevent CSRF
if (!$state || !isset($_SESSION['discord_oauth_state']) || $state !== $_SESSION['discord_oauth_state']) {
    die("OAuth state validation failed. Possible CSRF attempt.");
}
unset($_SESSION['discord_oauth_state']);

if (!$code) {
    die("Authorization code missing.");
}

$clientId = getenv('DISCORD_CLIENT_ID') ?: 'placeholder_client_id';
$clientSecret = getenv('DISCORD_CLIENT_SECRET') ?: 'placeholder_client_secret';
$redirectUri = getenv('DISCORD_REDIRECT_URI') ?: 'https://zanora.lol/auth/discord/callback';

// 2. Exchange code for access token
$ch = curl_init("https://discord.com/api/oauth2/token");
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
    die("Failed to retrieve access token from Discord. Error code: " . $httpCode);
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? '';
$refreshToken = $tokenData['refresh_token'] ?? '';
$expiresIn = $tokenData['expires_in'] ?? 0;
$expiresAt = time() + $expiresIn;

if (!$accessToken) {
    die("Access token not found in OAuth response.");
}

// 3. Fetch user details from Discord API
$ch = curl_init("https://discord.com/api/users/@me");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT        => 15
]);
$userResponse = curl_exec($ch);
$userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userHttpCode !== 200 || !$userResponse) {
    die("Failed to fetch profile details from Discord API.");
}

$userData = json_decode($userResponse, true);
$discordId = $userData['id'] ?? '';
$discordUsername = $userData['username'] ?? '';
$avatarHash = $userData['avatar'] ?? '';

if (!$discordId) {
    die("Failed to parse Discord ID.");
}

// Build Discord avatar URL
if ($avatarHash) {
    $discordAvatar = "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png";
} else {
    $disc = (int)($userData['discriminator'] ?? 0);
    if ($disc > 0) {
        $avatarIndex = $disc % 5;
    } else {
        $avatarIndex = ((int)$discordId >> 22) % 6;
    }
    $discordAvatar = "https://cdn.discordapp.com/embed/avatars/{$avatarIndex}.png";
}

// 4. Handle linking/login
$currentUserId = $_SESSION['userID'] ?? null;

if ($currentUserId) {
    // Check if this Discord account is already connected to another local profile
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE discord_id = ? AND id != ? LIMIT 1");
    $stmt->bind_param("si", $discordId, $currentUserId);
    $stmt->execute();
    $dupUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dupUser) {
        die("This Discord account is already connected to another Zanora profile (" . htmlspecialchars($dupUser['username']) . "). Please disconnect it there first.");
    }

    // Link current user
    $stmt = $conn->prepare("
        UPDATE users 
        SET discord_id = ?, discord_username = ?, discord_avatar = ?, 
            discord_token = ?, discord_refresh_token = ?, discord_token_expires = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sssssii", $discordId, $discordUsername, $discordAvatar, $accessToken, $refreshToken, $expiresAt, $currentUserId);
    $stmt->execute();
    $stmt->close();

    header("Location: /user/connections");
    exit();
} else {
    // No user logged in locally. Try to log them in via linked Discord account, or create a new user.
    $stmt = $conn->prepare("SELECT id, username, roles FROM users WHERE discord_id = ? LIMIT 1");
    $stmt->bind_param("s", $discordId);
    $stmt->execute();
    $existingUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingUser) {
        $userId = $existingUser['id'];
        $username = $existingUser['username'];
        $roles = (int)$existingUser['roles'];

        // Update tokens
        $stmt = $conn->prepare("UPDATE users SET discord_token = ?, discord_refresh_token = ?, discord_token_expires = ? WHERE id = ?");
        $stmt->bind_param("ssii", $accessToken, $refreshToken, $expiresAt, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        // Create new account
        $baseUsername = $discordUsername;
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

        $dummyEmail = $username . "@discord.zanora.lol";
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
        $publicTokenPlain = bin2hex(random_bytes(32));
        $publicTokenHashed = hash('sha256', $publicTokenPlain);
        $roles = 0;

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, image, is_verified, public_token, created_at, 
                               discord_id, discord_username, discord_avatar, discord_token, discord_refresh_token, discord_token_expires)
            VALUES (?, ?, ?, ?, 1, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssisssssi", $username, $dummyEmail, $hashedPassword, $discordAvatar, $publicTokenHashed, 
                           $discordId, $discordUsername, $discordAvatar, $accessToken, $refreshToken, $expiresAt);
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

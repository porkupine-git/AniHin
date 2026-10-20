<?php
require_once __DIR__ . '/src/helpers/env_helper.php';

// Disable default mysqli exceptions in PHP 8.1+ to prevent unhandled 500 crashes
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

$db_host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? "localhost");
$db_user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? "root");
$db_pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? "");
$db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? "test");

$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn && !$conn->connect_error) {
    @$conn->set_charset("utf8mb4");
}

// ✅ Force PHP timezone to UTC
date_default_timezone_set('UTC');

// ✅ Force MySQL session timezone to UTC
if ($conn && !$conn->connect_error) {
    @$conn->query("SET time_zone = '+00:00'");
}

if (session_status() === PHP_SESSION_NONE) {
    // Shared session cookies across subdomains (e.g. yuki.zanora.lol and zanora.lol)
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = explode(':', $host)[0]; // strip port
    
    if (!filter_var($host, FILTER_VALIDATE_IP) && strpos($host, '.') !== false) {
        if (preg_match('/(?P<domain>[a-z0-9\-]+\.[a-z0-9\-]+)$/i', $host, $regs)) {
            $baseDomain = '.' . $regs['domain'];
            if ($regs['domain'] !== 'localhost') {
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => $baseDomain,
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax' // Must be Lax to support cookies on POST/GET redirects from third-party sites like AniList/Discord
                ]);
            }
        }
    }
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('fix_avatar_url')) {
    function fix_avatar_url(?string $avatar): string {
        if (empty($avatar)) {
            return '/public/images/no-avatar.jpeg';
        }
        if (preg_match('#^https?://[^/]*zanora\.(?:lol|xyz)(/avatar/.*)$#i', $avatar, $m)) {
            return $m[1];
        }
        return $avatar;
    }
}

if (!function_exists('redis')) {
    function redis(): Redis {
        static $redis = null;

        if ($redis === null) {
            $redis = new Redis();
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = (int)(getenv('REDIS_PORT') ?: 6379);
            $redis->connect($host, $port);
            $auth = getenv('REDIS_AUTH');
            if (!empty($auth)) {
                $redis->auth($auth);
            }
        }

        return $redis;
    }
}

if (!function_exists('cacheGet')) {
    function cacheGet(string $key): ?array {
        $data = redis()->get($key);
        return $data ? json_decode($data, true) : null;
    }
}

if (!function_exists('cacheSet')) {
    function cacheSet(string $key, array $value, int $ttl = 300): void {
        redis()->setex($key, $ttl, json_encode($value));
    }
}

if (!function_exists('cacheRemember')) {
    function cacheRemember(string $key, int $ttl, callable $callback): ?array {
        $cached = cacheGet($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();

        if ($value !== null) {
            cacheSet($key, $value, $ttl);
        }

        return $value;
    }
}

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo("Database connection failed.");
}

$websiteTitle = getenv('WEBSITE_TITLE') ?: "Zanora";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$websiteUrl = getenv('WEBSITE_URL') ?: "{$protocol}://{$_SERVER['SERVER_NAME']}";
$websiteUrlh = parse_url($websiteUrl, PHP_URL_HOST) ?: $_SERVER['SERVER_NAME'];
$contactEmail = getenv('CONTACT_EMAIL') ?: "";
$version = getenv('VERSION') ?: "";

// Social Links
$discord = getenv('DISCORD_URL') ?: "#";
$reddit = getenv('REDDIT_URL') ?: "#";
$telegram = getenv('TELEGRAM_URL') ?: "#";
$instagram = getenv('INSTAGRAM_URL') ?: "#";

// APIs
$zpi = getenv('ZPI_API_URL') ?: "";
$player = getenv('PLAYER_URL') ?: "";

$banner = getenv('BANNER_IMAGE') ?: "/images/capture.png?v=1";
$websiteLogo = getenv('WEBSITE_LOGO') ?: "/images/logo.png?v=1.2";
$favicon = getenv('FAVICON_PATH') ?: "/favicon.ico";

// SMTP sender
if (!defined('SMTP_EMAIL')) {
    define('SMTP_EMAIL', getenv('SMTP_EMAIL') ?: 'noreply@shinora.fun');
}

/* ===========================================================
   🔐 SAFE CONSTANT DEFINITIONS (NO WARNINGS)
   =========================================================== */

if (!defined('CF_TURNSTILE_SITE_KEY')) {
    define('CF_TURNSTILE_SITE_KEY', getenv('CF_TURNSTILE_SITE_KEY') ?: '');
}

if (!defined('CF_TURNSTILE_SECRET_KEY')) {
    define('CF_TURNSTILE_SECRET_KEY', getenv('CF_TURNSTILE_SECRET_KEY') ?: '');
}

if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
}

if (!defined('SMTP_USER')) {
    define('SMTP_USER', getenv('SMTP_USER') ?: '');
}

if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
}

if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
}

if (!defined('WEBSITE_TITLE')) {
    define('WEBSITE_TITLE', $websiteTitle);
}

if (!defined('WEBSITE_URL')) {
    define('WEBSITE_URL', $websiteUrl);
}

if (!defined('DISABLE_EMAIL_RATE_LIMIT')) {
    define('DISABLE_EMAIL_RATE_LIMIT', getenv('DISABLE_EMAIL_RATE_LIMIT') === 'true' || getenv('DISABLE_EMAIL_RATE_LIMIT') === '1');
}

if (!defined('DISABLE_PASSWORD_RESET_RATE_LIMIT')) {
    define('DISABLE_PASSWORD_RESET_RATE_LIMIT', getenv('DISABLE_PASSWORD_RESET_RATE_LIMIT') === 'true' || getenv('DISABLE_PASSWORD_RESET_RATE_LIMIT') === '1');
}

/* ===========================================================
   📢 ADS & SITE SETTINGS CONFIGURATION HELPERS
   =========================================================== */
if (!function_exists('getSiteSetting')) {
function getSiteSetting(string $name, string $default = ''): string {
    static $settingsCache = null;
    global $conn;
    
    if ($settingsCache === null) {
        $settingsCache = [];
        if ($conn instanceof mysqli && $conn->connect_errno === 0) {
            $res = $conn->query("SELECT name, value FROM site_config");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $settingsCache[$row['name']] = $row['value'];
                }
            }
        }
    }
    
    return $settingsCache[$name] ?? $default;
}
}

if (!function_exists('areAdsEnabled')) {
function areAdsEnabled(): bool {
    return getSiteSetting('enable_ads', '0') === '1';
}
}

if (!function_exists('renderPopunderAd')) {
function renderPopunderAd(): void {
    if (areAdsEnabled() && getSiteSetting('enable_popunder_ads', '1') === '1') {
        $code = getSiteSetting('popunder_ad_code', '//wz.unledpierced.com/rC94QxHlNRv/143348');
        if (!empty($code)) {
            if (stripos($code, '<script') !== false) {
                echo "\n<!-- Global Popunder Ad -->\n" . $code . "\n";
            } else {
                $escaped = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
                echo "\n<!-- Global Popunder Ad -->\n<script type=\"text/javascript\" src=\"{$escaped}\" data-cfasync=\"false\" async></script>\n";
            }
        }
    }
}
}

if (!function_exists('renderBannerAd')) {
function renderBannerAd(): void {
    if (areAdsEnabled() && getSiteSetting('enable_banner_ads', '0') === '1') {
        $code = getSiteSetting('banner_ad_code', '');
        if (!empty($code)) {
            echo "\n<!-- Display Banner Ad -->\n" . $code . "\n";
        }
    }
}
}

// Global maintenance mode check
$maintenanceMode = false;
if ($conn && !$conn->connect_error) {
    try {
        $site_config_query = @$conn->query("SELECT value FROM site_config WHERE name = 'maintenance_mode' LIMIT 1");
        if ($site_config_query && $row = $site_config_query->fetch_assoc()) {
            $maintenanceMode = ($row['value'] === '1' || $row['value'] === 'true' || (int)$row['value'] === 1);
        }
    } catch (Throwable $e) {}
}

if ($maintenanceMode) {
    $isAdmin = false;
    if (isset($_SESSION['userID'])) {
        $stmt = $conn->prepare("SELECT roles FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['userID']);
            $stmt->execute();
            $userRole = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($userRole && (int)$userRole['roles'] === 1) {
                $isAdmin = true;
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $_SESSION['userID'];
                $_SESSION['admin_username'] = $_SESSION['username'] ?? 'Admin';
                $_SESSION['admin_role'] = 'super_admin';
            }
        }
    }
    
    $whitelist_ips = [];
    if ($conn instanceof mysqli && !$conn->connect_error) {
        try {
            $ip_query = @$conn->query("SELECT value FROM site_config WHERE name = 'maintenance_ip_whitelist' LIMIT 1");
            if ($ip_query && $row = $ip_query->fetch_assoc()) {
                $whitelist_ips = array_map('trim', explode(',', $row['value']));
            }
        } catch (Throwable $e) {}
    }
    $client_ip = getClientIP();
    
    if (!$isAdmin && !in_array($client_ip, $whitelist_ips, true)) {
        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $is_static = preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|json)$/i', $request_path);
        
        // Exclude static assets, admin panel, and maintenance pages from redirection
        if (!$is_static && strpos($request_path, '/admin_panel') === false && strpos($request_path, '/admin') === false && strpos($request_path, '/maintenance') === false) {
            // If AJAX call, return JSON instead of HTML page
            $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['REQUEST_URI'] ?? '', '/ajax/') !== false);
            
            // Prevent Nginx and browser from caching the 503 response
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('X-Accel-Buffering: no');
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                http_response_code(503);
                echo json_encode(['status' => false, 'maintenance' => true, 'msg' => 'Website is currently in maintenance mode.']);
                exit();
            }
            
            http_response_code(503);
            header("Retry-After: 3600");
            include_once($_SERVER['DOCUMENT_ROOT'] . '/maintenance.php');
            exit();
        }
    } else {
        // Output debug statement to PHP server logs to explain why the user bypassed maintenance
        $bypassReason = $isAdmin ? "Active Admin Session" : "IP Address ($client_ip) Whitelisted";
        error_log("[Zanora Maintenance Bypass] Bypassed for connection. Reason: " . $bypassReason);
    }
}

/* ===========================================================
   📢 ADVERTISEMENT SYSTEM CONFIGURATION & FREQUENCY POLICY
   =========================================================== */

$dbAdsEnabled = getSiteSetting('enable_ads', '1');
$dbPopupAdEnabled = getSiteSetting('enable_popunder_ads', '1');
$dbPopupAdSrc = getSiteSetting('popunder_ad_code', '//wz.unledpierced.com/rC94QxHlNRv/143348');
$dbAdPolicy = getSiteSetting('ad_policy_profile', 'balanced');
$dbAdCooldown = (int)getSiteSetting('ad_cooldown_ms', '0');
$dbAdSessionMax = (int)getSiteSetting('ad_session_max', '0');
$dbAdInitialDelay = (int)getSiteSetting('ad_initial_delay_ms', '0');

if (!defined('ADS_ENABLED')) {
    define('ADS_ENABLED', ($dbAdsEnabled === '1' || $dbAdsEnabled === 'true'));
}

if (!defined('POPUP_AD_ENABLED')) {
    define('POPUP_AD_ENABLED', ($dbPopupAdEnabled === '1' || $dbPopupAdEnabled === 'true'));
}

if (!defined('POPUP_AD_SRC')) {
    define('POPUP_AD_SRC', !empty($dbPopupAdSrc) ? $dbPopupAdSrc : '//wz.unledpierced.com/rC94QxHlNRv/143348');
}

// Preset Policies: 'conservative', 'balanced', 'aggressive'
if (!defined('AD_POLICY_PROFILE')) {
    define('AD_POLICY_PROFILE', !empty($dbAdPolicy) ? $dbAdPolicy : 'balanced');
}

// Configurable Cooldown & Session Limits
// - Conservative: 10 min cooldown (600,000ms), max 2 ads/session, 15s initial delay
// - Balanced:     5 min cooldown (300,000ms), max 3 ads/session, 10s initial delay
// - Aggressive:   2 min cooldown (120,000ms), max 5 ads/session, 5s initial delay
if (!defined('AD_COOLDOWN_MS')) {
    if ($dbAdCooldown > 0) {
        define('AD_COOLDOWN_MS', $dbAdCooldown);
    } else {
        switch (AD_POLICY_PROFILE) {
            case 'conservative':
                define('AD_COOLDOWN_MS', 600000); // 10 minutes
                break;
            case 'aggressive':
                define('AD_COOLDOWN_MS', 120000); // 2 minutes
                break;
            case 'balanced':
            default:
                define('AD_COOLDOWN_MS', 300000); // 5 minutes
                break;
        }
    }
}

if (!defined('AD_SESSION_MAX')) {
    if ($dbAdSessionMax > 0) {
        define('AD_SESSION_MAX', $dbAdSessionMax);
    } else {
        switch (AD_POLICY_PROFILE) {
            case 'conservative':
                define('AD_SESSION_MAX', 2);
                break;
            case 'aggressive':
                define('AD_SESSION_MAX', 5);
                break;
            case 'balanced':
            default:
                define('AD_SESSION_MAX', 3);
                break;
        }
    }
}

if (!defined('AD_INITIAL_DELAY_MS')) {
    if ($dbAdInitialDelay > 0) {
        define('AD_INITIAL_DELAY_MS', $dbAdInitialDelay);
    } else {
        switch (AD_POLICY_PROFILE) {
            case 'conservative':
                define('AD_INITIAL_DELAY_MS', 15000); // 15 seconds
                break;
            case 'aggressive':
                define('AD_INITIAL_DELAY_MS', 5000); // 5 seconds
                break;
            case 'balanced':
            default:
                define('AD_INITIAL_DELAY_MS', 10000); // 10 seconds
                break;
        }
    }
}

if (!defined("GLOBAL_AD_CODE")) {
    if (ADS_ENABLED && POPUP_AD_ENABLED && !empty(POPUP_AD_SRC)) {
        $rawAdSrc = trim(POPUP_AD_SRC);
        if (stripos($rawAdSrc, '<script') !== false) {
            define("GLOBAL_AD_CODE", "\n<!-- Global Ad Provider -->\n" . $rawAdSrc . "\n");
        } else {
            $escapedSrc = htmlspecialchars($rawAdSrc, ENT_QUOTES, 'UTF-8');
            define("GLOBAL_AD_CODE", "\n<!-- Global Ad Provider -->\n<script type=\"text/javascript\" src=\"{$escapedSrc}\" data-cfasync=\"false\" async></script>\n");
        }
    } else {
        define("GLOBAL_AD_CODE", "");
    }
}

if (!defined("WATCH_AD_CODE")) {
    define("WATCH_AD_CODE", GLOBAL_AD_CODE);
}

/* ===========================================================
   🎨 ASSET VERSIONING
   =========================================================== */

$cssFile  = $_SERVER['DOCUMENT_ROOT'].'/css/styles.min.css';
$cssFile1 = $_SERVER['DOCUMENT_ROOT'].'/css/home.css';
$cssFile2 = $_SERVER['DOCUMENT_ROOT'].'/css/styles.css';
$jsFile   = $_SERVER['DOCUMENT_ROOT'].'/js/app.min.js';
$jsFile1  = $_SERVER['DOCUMENT_ROOT'].'/js/watch.min.js';
$jsFile2  = $_SERVER['DOCUMENT_ROOT'].'/sw.js';
$jsFile3  = $_SERVER['DOCUMENT_ROOT'].'/js/live.js';

$versionCss  = file_exists($cssFile)  ? md5_file($cssFile)  : time();
$versionCss1 = file_exists($cssFile1) ? md5_file($cssFile1) : time();
$versionCss2 = file_exists($cssFile2) ? md5_file($cssFile2) : time();
$versionJs   = file_exists($jsFile)   ? md5_file($jsFile)   : time();
$versionJs1  = file_exists($jsFile1)  ? md5_file($jsFile1)  : time();
$versionJs2  = file_exists($jsFile2)  ? md5_file($jsFile2)  : time();
$versionJs3  = file_exists($jsFile3)  ? md5_file($jsFile3)  : time();

/* ===========================================================
   🧠 GLOBAL API CACHE HELPERS
   =========================================================== */
   
if (!function_exists('fetchApi')) {
    function fetchApi(string $url): string|false {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

if (!function_exists('getCachedApiJson')) {
    function getCachedApiJson(string $cacheKey, string $url, int $ttl = 3600): ?array {

    $fullKey = "api:" . $cacheKey;

    $cached = cacheGet($fullKey);

    if ($cached !== null) {
        return $cached;
    }

    $response = fetchApi($url);

    if ($response !== false) {
        $decoded = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (
                empty($decoded["success"]) ||
                empty($decoded["results"])
            ) {

                return $decoded;
            }

            cacheSet(
                $fullKey,
                $decoded,
                $ttl
            );

            return $decoded;
        }
    }

    return $cached; // fallback to old cache if exists
}
}

// --------------------------------------------------------------------------
// GLOBAL SITE ANNOUNCEMENT & PLATFORM UPDATE SYSTEM
// --------------------------------------------------------------------------
if (!function_exists('zanoraMarkdownToHtml')) {
    function zanoraMarkdownToHtml($text) {
        if (empty($text)) return '';
        
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        // Horizontal divider: --- or ***
        $text = preg_replace('/^(?:---|\*\*\*)\s*$/m', '<hr style="border:0; border-top:1px solid rgba(255,255,255,0.08); margin:12px 0;">', $text);

        // Feature / Status Badges: [NEW], [FIXED], [IMPROVED], [FEATURE], [UPDATE]
        $text = preg_replace('/\[NEW\]/i', '<span style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">NEW</span>', $text);
        $text = preg_replace('/\[FIXED\]/i', '<span style="background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">FIXED</span>', $text);
        $text = preg_replace('/\[IMPROVED\]/i', '<span style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.3); font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">IMPROVED</span>', $text);
        $text = preg_replace('/\[FEATURE\]/i', '<span style="background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3); font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">FEATURE</span>', $text);

        // Headings:
        // # Main Heading
        $text = preg_replace('/^# (.*?)$/m', '<h2 style="color:#ffffff; font-weight:700; font-size:15px; margin:14px 0 6px 0; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:4px; font-family:\'Poppins\', sans-serif;">$1</h2>', $text);
        // ## Subheading
        $text = preg_replace('/^## (.*?)$/m', '<h3 style="color:#ffffff; font-weight:600; font-size:13.5px; margin:10px 0 4px 0; font-family:\'Poppins\', sans-serif;">$1</h3>', $text);
        // ### Small Heading
        $text = preg_replace('/^### (.*?)$/m', '<h4 style="color:#e4e4e7; font-weight:600; font-size:12.5px; margin:8px 0 3px 0; font-family:\'Poppins\', sans-serif;">$1</h4>', $text);

        // Blockquotes: > quote
        $text = preg_replace('/^&gt; (.*?)$/m', '<blockquote style="border-left:3px solid rgba(255,255,255,0.3); padding-left:10px; margin:6px 0; color:#a1a1aa; font-style:italic; font-size:12px;">$1</blockquote>', $text);

        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong style="color:#ffffff; font-weight:700;">$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/', '<strong style="color:#ffffff; font-weight:700;">$1</strong>', $text);

        // Italic: *text* or _text_
        $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/', '<em style="color:#d4d4d8;">$1</em>', $text);

        // Inline Code: `code`
        $text = preg_replace('/`([^`]+)`/', '<code style="background:rgba(255,255,255,0.08); color:#f43f5e; padding:1px 5px; border-radius:4px; font-size:11.5px; font-family:monospace;">$1</code>', $text);

        // Links: [text](url)
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" rel="noopener" style="color:#60a5fa; text-decoration:underline;">$1</a>', $text);

        // Bullet Lists: - item, * item, + item
        $text = preg_replace('/^[*\-+]\s+(.*?)$/m', '<div style="display:flex; align-items:flex-start; gap:8px; margin:3px 0; font-size:12.5px; color:#d4d4d8;"><span style="color:#a1a1aa; font-size:7px; margin-top:5px;">●</span><div>$1</div></div>', $text);

        // Numbered Lists: 1. item
        $text = preg_replace('/^(\d+)\.\s+(.*?)$/m', '<div style="display:flex; align-items:flex-start; gap:8px; margin:3px 0; font-size:12.5px; color:#d4d4d8;"><span style="color:#a1a1aa; font-weight:600; font-size:11px; min-width:14px;">$1.</span><div>$2</div></div>', $text);

        // Spacing: convert double newlines to paragraph break and single newlines to <br>
        $text = preg_replace('/\n{2,}/', '<div style="height:8px;"></div>', $text);
        $text = nl2br($text);

        return '<div style="font-size:12.5px; line-height:1.6; color:#d4d4d8; font-family:\'Poppins\', sans-serif;">' . $text . '</div>';
    }
}

$announcement_active = '0';
$announcement_text = '';
$announcement_type = 'banner';
$announcement_author = 'Staff';
$update_active = '0';
$update_title = 'Platform Update';
$update_text = '';

// Fetch configs
if ($conn instanceof mysqli && !$conn->connect_error) {
    try {
        $ann_query = @$conn->query("SELECT name, value FROM site_config WHERE name IN ('announcement_active', 'announcement_text', 'announcement_type', 'announcement_author', 'update_active')");
        if ($ann_query) {
            while ($row = $ann_query->fetch_assoc()) {
                if ($row['name'] === 'announcement_active') $announcement_active = $row['value'];
                if ($row['name'] === 'announcement_text') $announcement_text = $row['value'];
                if ($row['name'] === 'announcement_type') $announcement_type = $row['value'];
                if ($row['name'] === 'announcement_author') $announcement_author = $row['value'];
                if ($row['name'] === 'update_active') $update_active = $row['value'];
            }
        }
    } catch (Throwable $e) {}

    // Fetch active platform update if active
    if ($update_active === '1') {
        try {
            $update_res = @$conn->query("SELECT title, content FROM platform_updates WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            if ($update_res && $row = $update_res->fetch_assoc()) {
                $update_title = $row['title'];
                $update_text = $row['content'];
            }
        } catch (Throwable $e) {}
    }
}

// Set up output buffer injection (Smartlink Ad System & Announcements)
if (!defined('ZANORA_OB_REGISTERED')) {
    define('ZANORA_OB_REGISTERED', true);

    ob_start(function($buffer) use ($announcement_active, $announcement_text, $announcement_type, $announcement_author, $update_active, $update_title, $update_text) {
        $pos = stripos($buffer, '<body');
        if ($pos === false) {
            return $buffer;
        }

        $injection = '';

        // 1. Site-wide Popup / Popunder Advertisement System
        if (defined('ADS_ENABLED') && ADS_ENABLED && defined('POPUP_AD_ENABLED') && POPUP_AD_ENABLED) {
            $src = defined('POPUP_AD_SRC') ? trim(POPUP_AD_SRC) : '//wz.unledpierced.com/rC94QxHlNRv/143348';
            if (!empty($src) && stripos($buffer, $src) === false) {
                if (defined('GLOBAL_AD_CODE') && !empty(GLOBAL_AD_CODE)) {
                    $injection .= GLOBAL_AD_CODE;
                } elseif (stripos($src, '<script') !== false) {
                    $injection .= "\n<!-- Global Ad Provider -->\n" . $src . "\n";
                } else {
                    $injection .= "\n<!-- Global Ad Provider -->\n<script type=\"text/javascript\" src=\"" . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . "\" data-cfasync=\"false\" async></script>\n";
                }
            }
        }

        // 2. Announcements & Platform Updates (if active)
        if (($announcement_active === '1' && !empty($announcement_text)) || ($update_active === '1' && !empty($update_text))) {
            // CSS styles for animations and mobile responsive centering
            $injection .= '<style>';
            $injection .= '@keyframes slideInUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }';
            $injection .= '@media (max-width: 550px) {';
            $injection .= '    #zanora-announcement-modal {';
            $injection .= '        left: 15px !important;';
            $injection .= '        right: 15px !important;';
            $injection .= '        bottom: 20px !important;';
            $injection .= '        max-width: none !important;';
            $injection .= '        margin: 0 auto !important;';
            $injection .= '    }';
            $injection .= '    .updates-card {';
            $injection .= '        max-height: 90vh !important;';
            $injection .= '        margin: 8px !important;';
            $injection .= '        border-radius: 16px !important;';
            $injection .= '    }';
            $injection .= '}';
            $injection .= '</style>';
            
            // Render Announcement if enabled
            if ($announcement_active === '1' && !empty($announcement_text)) {
                if ($announcement_type === 'banner') {
                    $injection .= '<div id="zanora-announcement-banner" class="zanora-announcement-bar banner" style="display:none; background:rgba(18, 18, 18, 0.95); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border-bottom:1px solid rgba(255, 255, 255, 0.12); color:#ffffff; padding:10px 20px; text-align:center; position:relative; z-index:9999; font-family:\'Poppins\', sans-serif; font-size:13px; font-weight:500; align-items:center; justify-content:center; gap:14px; box-shadow:0 8px 32px rgba(0, 0, 0, 0.6);">';
                    $injection .= '<span style="background:#ffffff; color:#000000; font-weight:700; font-size:10px; text-transform:uppercase; letter-spacing:0.8px; padding:2px 8px; border-radius:12px; flex-shrink:0;">Notice</span>';
                    $injection .= '<div class="announcement-content" style="flex:1; color:#e5e5e5; line-height:1.4;">' . $announcement_text . '</div>';
                    $injection .= '<button onclick="dismissAnnouncement(\'banner\')" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); color:#ffffff; cursor:pointer; font-size:13px; width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.background=\'#ffffff\'; this.style.color=\'#000000\';" onmouseout="this.style.background=\'rgba(255,255,255,0.1)\'; this.style.color=\'#ffffff\';"><i class="fas fa-times"></i></button>';
                    $injection .= '</div>';
                } elseif ($announcement_type === 'modal') {
                    $injection .= '<div id="zanora-announcement-modal" class="zanora-announcement-bar modal-popup" style="display:none; position:fixed; bottom:24px; right:24px; background:rgba(18, 18, 20, 0.96); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border:1px solid rgba(255, 255, 255, 0.12); color:#ffffff; padding:18px 20px; border-radius:14px; z-index:99999; font-family:\'Poppins\', sans-serif; max-width:380px; box-shadow:0 20px 40px rgba(0, 0, 0, 0.8); animation:slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);">';
                    $injection .= '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:8px;">';
                    $injection .= '<span style="font-weight:700; font-size:12px; letter-spacing:0.8px; text-transform:uppercase; color:#ffffff; display:flex; align-items:center; gap:6px;"><i class="fas fa-bullhorn" style="color:#ffffff;"></i> ' . htmlspecialchars($announcement_author) . '</span>';
                    $injection .= '<button onclick="dismissAnnouncement(\'modal\')" style="background:rgba(255,255,255,0.08); border:none; color:#a0a0a0; cursor:pointer; font-size:13px; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.color=\'#ffffff\'; this.style.background=\'rgba(255,255,255,0.2)\';" onmouseout="this.style.color=\'#a0a0a0\'; this.style.background=\'rgba(255,255,255,0.08)\';"><i class="fas fa-times"></i></button>';
                    $injection .= '</div>';
                    $injection .= '<div class="announcement-content" style="font-size:13px; line-height:1.5; color:#d4d4d8;">' . $announcement_text . '</div>';
                    $injection .= '</div>';
                }
            }
            
            // Render Platform Updates Modal if enabled (with Collapsible Show More)
            if ($update_active === '1' && !empty($update_text)) {
                $injection .= '<div id="zanora-announcement-updates" class="zanora-announcement-bar updates-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:999999; align-items:center; justify-content:center; padding:16px; font-family:\'Poppins\', sans-serif;">';
                $injection .= '  <div class="updates-card" style="background:rgba(18, 18, 20, 0.98); border:1px solid rgba(255,255,255,0.12); border-radius:18px; max-width:520px; width:100%; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 30px 60px rgba(0,0,0,0.9), 0 0 40px rgba(255,255,255,0.03); animation:slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); overflow:hidden;">';
                
                // Header
                $injection .= '    <div class="updates-header" style="display:flex; align-items:center; justify-content:space-between; padding:16px 22px; border-bottom:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.02);">';
                $injection .= '      <div style="display:flex; align-items:center; gap:10px;">';
                $injection .= '        <div style="width:32px; height:32px; border-radius:8px; background:rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px;"><i class="fas fa-sparkles"></i></div>';
                $injection .= '        <div>';
                $injection .= '          <span style="font-size:10px; font-weight:700; letter-spacing:0.8px; text-transform:uppercase; color:#a1a1aa; display:block;">Release Notes</span>';
                $injection .= '          <h3 style="font-weight:700; font-size:14.5px; color:#ffffff; margin:0; line-height:1.2;">' . htmlspecialchars($update_title) . '</h3>';
                $injection .= '        </div>';
                $injection .= '      </div>';
                $injection .= '      <button onclick="dismissAnnouncement(\'updates\')" style="background:rgba(255,255,255,0.06); border:none; color:#a0a0a0; cursor:pointer; font-size:13px; width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.color=\'#ffffff\'; this.style.background=\'rgba(255,255,255,0.15)\';" onmouseout="this.style.color=\'#a0a0a0\'; this.style.background=\'rgba(255,255,255,0.06)\';"><i class="fas fa-times"></i></button>';
                $injection .= '    </div>';
                
                // Body with Collapsible Wrapper & Fade Gradient
                $injection .= '    <div style="position:relative; padding:18px 22px; overflow:hidden;">';
                $injection .= '      <div id="zanora-update-content-wrapper" style="max-height:220px; overflow-y:auto; transition:max-height 0.35s ease; padding-right:4px;">';
                $injection .= '        ' . zanoraMarkdownToHtml($update_text);
                $injection .= '      </div>';
                $injection .= '      <div id="zanora-update-fade-overlay" style="display:none; position:absolute; bottom:0; left:0; width:100%; height:55px; background:linear-gradient(transparent, rgba(18, 18, 20, 0.98)); pointer-events:none;"></div>';
                $injection .= '    </div>';
                
                // Footer with Show More / Less toggle & Got It button
                $injection .= '    <div class="updates-footer" style="padding:12px 22px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.02);">';
                $injection .= '      <button id="zanora-update-showmore-btn" onclick="toggleUpdateExpand()" style="display:none; background:transparent; border:none; color:#a1a1aa; font-size:12px; font-weight:600; cursor:pointer; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color=\'#ffffff\';" onmouseout="this.style.color=\'#a1a1aa\';">';
                $injection .= '        <span id="zanora-showmore-text">Show More</span> <i id="zanora-showmore-icon" class="fas fa-chevron-down" style="font-size:10px;"></i>';
                $injection .= '      </button>';
                $injection .= '      <div style="flex:1;"></div>';
                $injection .= '      <button onclick="dismissAnnouncement(\'updates\')" style="background:#ffffff; color:#000000; border:none; padding:7px 22px; border-radius:8px; font-weight:600; font-size:12.5px; cursor:pointer; transition:all 0.2s; box-shadow:0 3px 12px rgba(255,255,255,0.15);" onmouseover="this.style.transform=\'scale(1.02)\';" onmouseout="this.style.transform=\'scale(1)\';">Got It</button>';
                $injection .= '    </div>';
                
                $injection .= '  </div>';
                $injection .= '</div>';
            }
            
            // Javascript client checker and dismiss handlers for announcements
            $injection .= '<script>';
            $injection .= 'var isUpdateExpanded = false;';
            $injection .= 'function checkUpdateContentHeight() {';
            $injection .= '    var wrapper = document.getElementById("zanora-update-content-wrapper");';
            $injection .= '    var fade = document.getElementById("zanora-update-fade-overlay");';
            $injection .= '    var btn = document.getElementById("zanora-update-showmore-btn");';
            $injection .= '    if (wrapper && wrapper.scrollHeight > 230) {';
            $injection .= '        if (fade) fade.style.display = "block";';
            $injection .= '        if (btn) btn.style.display = "inline-flex";';
            $injection .= '    }';
            $injection .= '}';
            $injection .= 'function toggleUpdateExpand() {';
            $injection .= '    var wrapper = document.getElementById("zanora-update-content-wrapper");';
            $injection .= '    var fade = document.getElementById("zanora-update-fade-overlay");';
            $injection .= '    var text = document.getElementById("zanora-showmore-text");';
            $injection .= '    var icon = document.getElementById("zanora-showmore-icon");';
            $injection .= '    if (!wrapper) return;';
            $injection .= '    isUpdateExpanded = !isUpdateExpanded;';
            $injection .= '    if (isUpdateExpanded) {';
            $injection .= '        wrapper.style.maxHeight = "600px";';
            $injection .= '        if (fade) fade.style.display = "none";';
            $injection .= '        if (text) text.innerText = "Show Less";';
            $injection .= '        if (icon) icon.className = "fas fa-chevron-up";';
            $injection .= '    } else {';
            $injection .= '        wrapper.style.maxHeight = "220px";';
            $injection .= '        if (fade) fade.style.display = "block";';
            $injection .= '        if (text) text.innerText = "Show More";';
            $injection .= '        if (icon) icon.className = "fas fa-chevron-down";';
            $injection .= '    }';
            $injection .= '}';
            $injection .= 'document.addEventListener("DOMContentLoaded", function() {';
            if ($announcement_active === '1' && !empty($announcement_text)) {
                $injection .= '    const type = ' . json_encode($announcement_type) . ';';
                $injection .= '    const textHash = ' . json_encode(md5($announcement_text)) . ';';
                $injection .= '    const dismissedHash = localStorage.getItem("zanora_announcement_dismissed");';
                $injection .= '    if (dismissedHash !== textHash) {';
                $injection .= '        if (type === "banner") {';
                $injection .= '            const banner = document.getElementById("zanora-announcement-banner");';
                $injection .= '            if (banner) banner.style.setProperty("display", "flex", "important");';
                $injection .= '        } else if (type === "modal") {';
                $injection .= '            const modal = document.getElementById("zanora-announcement-modal");';
                $injection .= '            if (modal) modal.style.setProperty("display", "block", "important");';
                $injection .= '        }';
                $injection .= '    }';
            }
            if ($update_active === '1' && !empty($update_text)) {
                $injection .= '    const updateHash = ' . json_encode(md5($update_text)) . ';';
                $injection .= '    const dismissedUpdate = localStorage.getItem("zanora_update_dismissed");';
                $injection .= '    if (dismissedUpdate !== updateHash) {';
                $injection .= '        const updates = document.getElementById("zanora-announcement-updates");';
                $injection .= '        if (updates) {';
                $injection .= '            updates.style.setProperty("display", "flex", "important");';
                $injection .= '            setTimeout(checkUpdateContentHeight, 50);';
                $injection .= '        }';
                $injection .= '    }';
            }
            $injection .= '});';
            $injection .= 'function dismissAnnouncement(type) {';
            $injection .= '    if (type === "banner") {';
            $injection .= '        const textHash = ' . json_encode(md5($announcement_text)) . ';';
            $injection .= '        localStorage.setItem("zanora_announcement_dismissed", textHash);';
            $injection .= '        const banner = document.getElementById("zanora-announcement-banner");';
            $injection .= '        if (banner) banner.style.display = "none";';
            $injection .= '    } else if (type === "modal") {';
            $injection .= '        const textHash = ' . json_encode(md5($announcement_text)) . ';';
            $injection .= '        localStorage.setItem("zanora_announcement_dismissed", textHash);';
            $injection .= '        const modal = document.getElementById("zanora-announcement-modal");';
            $injection .= '        if (modal) modal.style.display = "none";';
            $injection .= '    } else if (type === "updates") {';
            $injection .= '        const updateHash = ' . json_encode(md5($update_text)) . ';';
            $injection .= '        localStorage.setItem("zanora_update_dismissed", updateHash);';
            $injection .= '        const updates = document.getElementById("zanora-announcement-updates");';
            $injection .= '        if (updates) updates.style.display = "none";';
            $injection .= '    }';
            $injection .= '}';
            $injection .= '</script>';
        }

        $endBodyPos = strpos($buffer, '>', $pos);
        if ($endBodyPos !== false && !empty($injection)) {
            $buffer = substr_replace($buffer, $injection, $endBodyPos + 1, 0);
        }

        return $buffer;
    });
}
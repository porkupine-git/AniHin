<?php
// index.php - Modular Router & Main Controller for Zanora Admin OS
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Include Component Libraries
require_once __DIR__ . '/components/cards.php';
require_once __DIR__ . '/components/badges.php';
require_once __DIR__ . '/components/tables.php';
require_once __DIR__ . '/components/alerts.php';

// Enforce Admin Authentication
enforceAdminAuth();

// Ensure required admin tables exist without needing to open /db-config first
if (function_exists('ensureAdminTablesExist')) {
    ensureAdminTablesExist();
}

// Direct Database Backup File Download Handler
$page = $_GET['page'] ?? 'dashboard';
if ($page === 'db_backup') {
    checkAdminPermission('super_admin');
    downloadDatabaseBackup();
    exit;
}

// Global Site Config Data Binding for Layout
$site_settings = [];
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        $res = @$conn->query("SELECT * FROM site_config");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $site_settings[$row['name']] = $row['value'];
            }
        }
    } catch (Throwable $e) {
        error_log("Site config query error in admin: " . $e->getMessage());
    }
}

// Fetch Live Stats for Navbar & Sidebar
try {
    $live_stats = getLiveStats();
} catch (Throwable $e) {
    error_log("Live stats load error in admin: " . $e->getMessage());
    $live_stats = [
        'live_visitors' => 0,
        'today_visitors' => 0,
        'today_unique' => 0,
        'today_pageviews' => 0,
        'popular_page' => '/',
        'total_users' => 0,
        'new_users_today' => 0,
        'top_pages' => [],
        'visitors_list' => [],
        'recent_users' => [],
        'recent_audit' => [],
        'unread_inquiries' => 0,
        'pending_reports' => 0,
        'total_pending_alerts' => 0,
        'server_time' => date('H:i:s')
    ];
}

// Render Page Assembly
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="admin-main">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="admin-content-body">
            <?php
            switch ($page) {
                case 'analytics':
                    require_once __DIR__ . '/analytics.php';
                    break;
                case 'users':
                    require_once __DIR__ . '/users.php';
                    break;
                case 'roles':
                    require_once __DIR__ . '/roles.php';
                    break;
                case 'admins':
                    require_once __DIR__ . '/admins.php';
                    break;
                case 'moderation':
                    require_once __DIR__ . '/moderation.php';
                    break;
                case 'reports':
                    require_once __DIR__ . '/reports.php';
                    break;
                case 'config':
                    require_once __DIR__ . '/config.php';
                    break;
                case 'database':
                    require_once __DIR__ . '/database.php';
                    break;
                case 'logs':
                    require_once __DIR__ . '/logs.php';
                    break;
                case 'dashboard':
                default:
                    require_once __DIR__ . '/dashboard.php';
                    break;
            }
            ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

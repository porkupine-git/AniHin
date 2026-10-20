<?php
/**
 * Zanora Database Initializer & Migration Tool (/db-config)
 * 
 * Sets up and verifies all database tables, columns, indexes, and initial configurations.
 * This runs ONLY when opened explicitly at /db-config or /db-config.php.
 */

require_once __DIR__ . '/_config.php';

$startTime = microtime(true);
$results = [];
$errors = [];
$warnings = [];

if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
}

// 1. Table Definitions
$tableSchemas = [
    'site_config' => "CREATE TABLE IF NOT EXISTS `site_config` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `value` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'failed_logins' => "CREATE TABLE IF NOT EXISTS `failed_logins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(255) NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'audit_logs' => "CREATE TABLE IF NOT EXISTS `audit_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `username` VARCHAR(255) NOT NULL,
        `action` TEXT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'sync_queue' => "CREATE TABLE IF NOT EXISTS `sync_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `anime_id` VARCHAR(255) NOT NULL,
        `type` INT NOT NULL,
        `episodes_watched` INT DEFAULT 0,
        `score` INT DEFAULT 0,
        `progress` INT DEFAULT 0,
        `status` VARCHAR(50) DEFAULT 'pending',
        `error_count` INT DEFAULT 0,
        `last_error` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'platform_updates' => "CREATE TABLE IF NOT EXISTS `platform_updates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) DEFAULT 'Platform Update',
        `content` TEXT,
        `is_active` TINYINT(1) DEFAULT 1,
        `author` VARCHAR(100) DEFAULT 'Staff',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'live_watching' => "CREATE TABLE IF NOT EXISTS `live_watching` (
        `user_id` INT PRIMARY KEY,
        `anime_id` VARCHAR(255) NOT NULL,
        `episode_id` INT NOT NULL,
        `episode_number` INT NOT NULL,
        `progress` INT NOT NULL,
        `duration` INT NOT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'site_visits' => "CREATE TABLE IF NOT EXISTS `site_visits` (
        `date` DATE PRIMARY KEY,
        `visitors` INT NOT NULL DEFAULT 0,
        `unique_visitors` INT NOT NULL DEFAULT 0,
        `page_views` INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'live_visitors' => "CREATE TABLE IF NOT EXISTS `live_visitors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` VARCHAR(255) NOT NULL UNIQUE,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT,
        `current_page` TEXT,
        `last_active` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'traffic_sources' => "CREATE TABLE IF NOT EXISTS `traffic_sources` (
        `date` DATE PRIMARY KEY,
        `direct` INT NOT NULL DEFAULT 0,
        `search` INT NOT NULL DEFAULT 0,
        `social` INT NOT NULL DEFAULT 0,
        `referral` INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'daily_unique_visitors' => "CREATE TABLE IF NOT EXISTS `daily_unique_visitors` (
        `date` DATE NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`date`, `ip_address`),
        INDEX `idx_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'contact_messages' => "CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `name` VARCHAR(100) NULL,
        `email` VARCHAR(255) NOT NULL,
        `subject` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT NULL,
        `status` ENUM('unread', 'read', 'in_progress', 'resolved', 'spam') DEFAULT 'unread',
        `admin_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'comment_reports' => "CREATE TABLE IF NOT EXISTS `comment_reports` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `comment_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `type` INT NOT NULL DEFAULT 1,
        `status` VARCHAR(50) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_comment_type` (`comment_id`, `type`),
        INDEX `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'active_users' => "CREATE TABLE IF NOT EXISTS `active_users` (
        `session_id` VARCHAR(255) PRIMARY KEY,
        `user_id` INT NULL,
        `ip_address` VARCHAR(45),
        `user_agent` TEXT,
        `current_page` VARCHAR(500),
        `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_last_activity` (`last_activity`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'user_roles' => "CREATE TABLE IF NOT EXISTS `user_roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `color` VARCHAR(30) DEFAULT '#6366f1',
        `permissions` TEXT,
        `is_default` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// Execute Table Creations
foreach ($tableSchemas as $tableName => $sql) {
    if ($conn->query($sql)) {
        $results[] = [
            'type' => 'table',
            'name' => $tableName,
            'status' => 'OK',
            'detail' => 'Table verified / created successfully.'
        ];
    } else {
        $errors[] = [
            'type' => 'table',
            'name' => $tableName,
            'detail' => $conn->error
        ];
    }
}

// 2. Column Verifications
$columnChecks = [
    'users' => [
        'anilist_id' => 'INT NULL DEFAULT NULL',
        'anilist_username' => 'VARCHAR(255) NULL DEFAULT NULL',
        'anilist_avatar' => 'VARCHAR(500) NULL DEFAULT NULL',
        'anilist_token' => 'TEXT NULL DEFAULT NULL',
        'anilist_refresh_token' => 'TEXT NULL DEFAULT NULL',
        'anilist_token_expires' => 'INT NULL DEFAULT NULL',
        'discord_id' => 'VARCHAR(100) NULL DEFAULT NULL',
        'discord_username' => 'VARCHAR(255) NULL DEFAULT NULL',
        'discord_avatar' => 'VARCHAR(500) NULL DEFAULT NULL',
        'discord_token' => 'TEXT NULL DEFAULT NULL',
        'discord_refresh_token' => 'TEXT NULL DEFAULT NULL',
        'discord_token_expires' => 'INT NULL DEFAULT NULL',
        'mute_until' => 'DATETIME NULL DEFAULT NULL',
        'is_admin' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'is_blocked' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'roles' => 'VARCHAR(255) NULL DEFAULT NULL',
        'live_status' => 'TINYINT(1) NOT NULL DEFAULT 0'
    ],
    'watchlist' => [
        'score' => 'INT NULL DEFAULT 0',
        'notes' => 'TEXT NULL DEFAULT NULL'
    ],
    'comments' => [
        'is_spoil' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'status' => "VARCHAR(50) DEFAULT 'active'"
    ]
];

foreach ($columnChecks as $table => $columns) {
    $tableExists = $conn->query("SHOW TABLES LIKE '$table'");
    if ($tableExists && $tableExists->num_rows > 0) {
        foreach ($columns as $column => $definition) {
            $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($check && $check->num_rows === 0) {
                if ($conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
                    $results[] = [
                        'type' => 'column',
                        'name' => "$table.$column",
                        'status' => 'ADDED',
                        'detail' => "Column added to `$table` ($definition)"
                    ];
                } else {
                    $warnings[] = [
                        'type' => 'column',
                        'name' => "$table.$column",
                        'detail' => "Could not add column: " . $conn->error
                    ];
                }
            } else {
                $results[] = [
                    'type' => 'column',
                    'name' => "$table.$column",
                    'status' => 'EXISTS',
                    'detail' => "Column verified in `$table`."
                ];
            }
        }
    } else {
        $warnings[] = [
            'type' => 'table',
            'name' => $table,
            'detail' => "Table `$table` does not exist yet; skipping column checks."
        ];
    }
}

// 3. Index Verifications
$indexChecks = [
    'watchlist' => ['idx_user_anime' => 'ALTER TABLE watchlist ADD INDEX idx_user_anime (user_id, anime_id)'],
    'watch_history' => ['idx_user_anime_history' => 'ALTER TABLE watch_history ADD INDEX idx_user_anime_history (user_id, anime_id)'],
    'live_visitors' => ['idx_last_active' => 'ALTER TABLE live_visitors ADD INDEX idx_last_active (last_active)'],
    'failed_logins' => ['idx_ip_time' => 'ALTER TABLE failed_logins ADD INDEX idx_ip_time (ip_address, attempt_time)'],
    'audit_logs' => ['idx_created_at' => 'ALTER TABLE audit_logs ADD INDEX idx_created_at (created_at)'],
    'sync_queue' => ['idx_user_status' => 'ALTER TABLE sync_queue ADD INDEX idx_user_status (user_id, status)'],
    'contact_messages' => ['idx_status' => 'ALTER TABLE contact_messages ADD INDEX idx_status (status)']
];

foreach ($indexChecks as $table => $indexes) {
    $tableExists = $conn->query("SHOW TABLES LIKE '$table'");
    if ($tableExists && $tableExists->num_rows > 0) {
        foreach ($indexes as $indexName => $sql) {
            $res = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'");
            if ($res && $res->num_rows === 0) {
                if ($conn->query($sql)) {
                    $results[] = [
                        'type' => 'index',
                        'name' => "$table.$indexName",
                        'status' => 'CREATED',
                        'detail' => "Performance index added."
                    ];
                } else {
                    $warnings[] = [
                        'type' => 'index',
                        'name' => "$table.$indexName",
                        'detail' => "Failed to add index: " . $conn->error
                    ];
                }
            } else {
                $results[] = [
                    'type' => 'index',
                    'name' => "$table.$indexName",
                    'status' => 'EXISTS',
                    'detail' => "Performance index verified."
                ];
            }
        }
    }
}

// 4. Default Seed Configurations
$defaultConfigs = [
    'maintenance_mode' => '0',
    'maintenance_ip_whitelist' => '127.0.0.1',
    'maintenance_countdown' => '',
    'maintenance_message' => 'We are performing scheduled database maintenance. We will be back shortly!',
    'enable_ads' => '1',
    'enable_popunder_ads' => '1',
    'popunder_ad_code' => '//wz.unledpierced.com/rC94QxHlNRv/143348',
    'ad_policy_profile' => 'balanced',
    'website_title' => 'Zanora'
];

foreach ($defaultConfigs as $name => $val) {
    $stmt = $conn->prepare("INSERT IGNORE INTO site_config (name, value) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $name, $val);
        $stmt->execute();
        $stmt->close();
    }
}

$executionTime = round((microtime(true) - $startTime) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Migration - <?= htmlspecialchars($websiteTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <style>
        :root {
            --bg-base: #0a0e1a;
            --bg-card: #111827;
            --bg-inner: #162032;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.4);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --emerald: #10b981;
            --emerald-bg: rgba(16, 185, 129, 0.12);
            --rose: #f43f5e;
            --rose-bg: rgba(244, 63, 94, 0.12);
            --amber: #f59e0b;
            --amber-bg: rgba(245, 158, 11, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(16, 185, 129, 0.06) 0%, transparent 40%);
        }
        .container {
            width: 100%;
            max-width: 860px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 1px 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
        }
        .header {
            padding: 32px 36px 24px;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            background: linear-gradient(to right, rgba(99, 102, 241, 0.05), transparent);
        }
        .header-title-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 8px 20px var(--primary-glow);
        }
        .header h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }
        .header p {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }
        .badge-timer {
            background: var(--bg-inner);
            border: 1px solid var(--border-subtle);
            color: var(--emerald);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'JetBrains Mono', monospace;
        }
        .metrics-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            padding: 24px 36px;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid var(--border-subtle);
        }
        .metric-card {
            background: var(--bg-inner);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 14px 18px;
        }
        .metric-card .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }
        .metric-card .value {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-top: 4px;
        }
        .content {
            padding: 28px 36px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .log-box {
            background: #070a12;
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 16px;
            max-height: 380px;
            overflow-y: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .log-entry {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.02);
            transition: background 0.15s;
        }
        .log-entry:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .log-name {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
        }
        .status-pill {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        .status-ok { background: var(--emerald-bg); color: var(--emerald); border: 1px solid rgba(16, 185, 129, 0.25); }
        .status-added { background: var(--primary-glow); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
        .status-warn { background: var(--amber-bg); color: var(--amber); border: 1px solid rgba(245, 158, 11, 0.25); }
        .status-err { background: var(--rose-bg); color: var(--rose); border: 1px solid rgba(244, 63, 94, 0.25); }

        .actions-footer {
            padding: 20px 36px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            border-top: 1px solid var(--border-subtle);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        .btn-secondary {
            background: var(--bg-inner);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
        }
        .btn-secondary:hover {
            border-color: var(--border-hover);
            color: #fff;
        }
        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
        }
        .btn-ghost:hover {
            color: #fff;
        }
        .tip-callout {
            margin-top: 20px;
            padding: 14px 18px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            font-size: 12.5px;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tip-callout i {
            color: #818cf8;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title-wrap">
                <div class="icon-badge">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <h1>Zanora Database Migration & Setup</h1>
                    <p>Tables, schemas, columns & performance indexes initialized.</p>
                </div>
            </div>
            <div class="badge-timer">
                <i class="fas fa-bolt"></i> <?= $executionTime ?> ms
            </div>
        </div>

        <div class="metrics-strip">
            <div class="metric-card">
                <div class="label">Tables Checked</div>
                <div class="value" style="color: #818cf8;"><?= count($tableSchemas) ?></div>
            </div>
            <div class="metric-card">
                <div class="label">Operations OK</div>
                <div class="value" style="color: var(--emerald);"><?= count($results) ?></div>
            </div>
            <div class="metric-card">
                <div class="label">Warnings</div>
                <div class="value" style="color: <?= count($warnings) > 0 ? 'var(--amber)' : 'var(--text-muted)' ?>;"><?= count($warnings) ?></div>
            </div>
            <div class="metric-card">
                <div class="label">Errors</div>
                <div class="value" style="color: <?= count($errors) > 0 ? 'var(--rose)' : 'var(--text-muted)' ?>;"><?= count($errors) ?></div>
            </div>
        </div>

        <div class="content">
            <div class="section-title">
                <i class="fas fa-list-check" style="color: var(--primary);"></i> Execution Diagnostic Log
            </div>
            <div class="log-box">
                <?php foreach ($results as $item): ?>
                    <div class="log-entry">
                        <div class="log-name">
                            <i class="fas <?= $item['type'] === 'table' ? 'fa-table' : ($item['type'] === 'column' ? 'fa-columns' : 'fa-bolt') ?>" style="color: var(--primary); font-size: 11px;"></i>
                            <span><?= htmlspecialchars($item['name']) ?></span>
                        </div>
                        <span class="status-pill status-<?= strtolower($item['status']) === 'ok' ? 'ok' : 'added' ?>">
                            <?= htmlspecialchars($item['status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($warnings as $w): ?>
                    <div class="log-entry">
                        <div class="log-name">
                            <i class="fas fa-triangle-exclamation" style="color: var(--amber); font-size: 11px;"></i>
                            <span><?= htmlspecialchars($w['name']) ?>: <?= htmlspecialchars($w['detail']) ?></span>
                        </div>
                        <span class="status-pill status-warn">WARN</span>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($errors as $e): ?>
                    <div class="log-entry">
                        <div class="log-name">
                            <i class="fas fa-circle-xmark" style="color: var(--rose); font-size: 11px;"></i>
                            <span><?= htmlspecialchars($e['name']) ?>: <?= htmlspecialchars($e['detail']) ?></span>
                        </div>
                        <span class="status-pill status-err">ERROR</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tip-callout">
                <i class="fas fa-circle-info"></i>
                <div>
                    <strong>Fast Loading Enabled:</strong> Schema checks have been permanently decoupled from <code>_config.php</code>. All frontend pages now load at maximum speed without executing repetitive DDL queries. Run <code>/db-config</code> whenever you modify database structures.
                </div>
            </div>
        </div>

        <div class="actions-footer">
            <div style="display: flex; gap: 10px;">
                <a href="/db-config" class="btn btn-secondary">
                    <i class="fas fa-rotate"></i> Re-run Setup
                </a>
                <a href="/contact" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i> Test Contact Page
                </a>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/admin" class="btn btn-primary">
                    <i class="fas fa-gauge-high"></i> Open Admin Panel
                </a>
                <a href="/" class="btn btn-ghost">
                    <i class="fas fa-house"></i> Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>

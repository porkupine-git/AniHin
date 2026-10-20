<?php
// database.php - Database Storage & Cache Optimization Tools Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('super_admin');

$health = getServerHealth();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Database & Cache Tools</h1>
        <p class="page-subtitle">Monitor MySQL storage usage, execute table index optimizations, and flush Redis cache.</p>
    </div>
</div>

<div class="stats-grid" style="margin-bottom: 28px;">
    <?php
    renderStatCard("MySQL Database Size", $health['db_size'], "Data & index memory space", "fa-database", "db-size");
    renderStatCard("Redis Cache Status", $health['redis_status'], "Redis In-Memory Store (127.0.0.1:6379)", "fa-bolt", "redis-status");
    ?>
</div>

<div class="form-container">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; color: #fff;">Operational Maintenance Triggers</h3>
    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">Safe background procedures to optimize performance and download database snapshots.</p>

    <div style="display: flex; flex-wrap: wrap; gap: 14px;">
        <button onclick="triggerCacheClear()" class="btn-primary" style="background: #27272a; color: #fff; border: 1px solid var(--border-strong);">
            <i class="fas fa-trash-can mr-2"></i> Flush Redis Cache
        </button>
        <button onclick="triggerDbOptimize()" class="btn-primary" style="background: #27272a; color: #fff; border: 1px solid var(--border-strong);">
            <i class="fas fa-gears mr-2"></i> Optimize MySQL Tables
        </button>
        <a href="?page=db_backup" class="btn-primary" style="text-decoration: none; background: #ffffff; color: #000000;">
            <i class="fas fa-download mr-2"></i> Download SQL Backup
        </a>
    </div>
</div>

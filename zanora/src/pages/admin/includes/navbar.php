<?php
// includes/navbar.php - Sticky Header Navigation with Command Palette Trigger
$currentPage = $_GET['page'] ?? 'dashboard';
$pageTitles = [
    'dashboard'  => 'Executive Console',
    'analytics'  => 'Deep Traffic & Visitor Analytics',
    'users'      => 'User Account Management',
    'roles'      => 'Roles & Permissions Matrix',
    'admins'     => 'Staff & System Admins',
    'moderation' => 'Content & Comments Moderation',
    'reports'    => 'Inquiries & Content Reports',
    'config'     => 'System Configurations',
    'database'   => 'Database & Cache Management',
    'logs'       => 'Audit Logs & Event Auditing'
];
$title = $pageTitles[$currentPage] ?? 'Control Panel';
?>
<header class="admin-navbar">
    <div class="navbar-left">
        <button class="mobile-sidebar-toggle" id="mobile-sidebar-btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumbs">
            <span class="crumb-root"><i class="fas fa-terminal"></i> Admin</span>
            <i class="fas fa-chevron-right crumb-sep"></i>
            <span class="crumb-current"><?= htmlspecialchars($title) ?></span>
        </div>
    </div>

    <div class="navbar-center">
        <button class="command-palette-trigger" onclick="openCommandPalette()">
            <i class="fas fa-search"></i>
            <span>Search or command...</span>
            <kbd class="shortcut-kbd"><span class="cmd-key">⌘</span>K</kbd>
        </button>
    </div>

    <div class="navbar-right">
        <?php if (!empty($live_stats['total_pending_alerts']) && $live_stats['total_pending_alerts'] > 0): ?>
            <a href="?page=reports" class="navbar-stat-pill alert-pill" title="Unread Inquiries & Reports" style="text-decoration: none; border-color: rgba(244, 63, 94, 0.4); background: rgba(244, 63, 94, 0.12); color: #fb7185;">
                <i class="fas fa-bell"></i>
                <span><?= $live_stats['total_pending_alerts'] ?> New Inquiries</span>
            </a>
        <?php endif; ?>
        <div class="navbar-stat-pill" title="Live CPU Core Load">
            <i class="fas fa-microchip"></i>
            <span id="nav-cpu-load">CPU --%</span>
        </div>
        <div class="navbar-stat-pill" title="Active Visitors (5 mins)">
            <span class="live-dot"></span>
            <span id="nav-live-visitors"><?= $live_stats['live_visitors'] ?? 0 ?> Active</span>
        </div>
        
        <div class="navbar-user-dropdown">
            <button class="dropdown-trigger" id="user-dropdown-btn">
                <div class="mini-avatar"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?></div>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="user-dropdown-menu">
                <div class="dropdown-header">
                    <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>
                    <small><?= strtoupper($_SESSION['admin_role'] ?? 'super_admin') ?></small>
                </div>
                <div class="dropdown-divider"></div>
                <a href="?page=config" class="dropdown-item"><i class="fas fa-gear"></i> System Config</a>
                <a href="?page=logs" class="dropdown-item"><i class="fas fa-list-check"></i> Audit Logs</a>
                <div class="dropdown-divider"></div>
                <a href="?logout=true" class="dropdown-item logout-item"><i class="fas fa-power-off"></i> Exit Admin</a>
            </div>
        </div>
    </div>
</header>

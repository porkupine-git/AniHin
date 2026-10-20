<?php
// includes/sidebar.php - Sleek Monochrome Collapsible Sidebar Navigation
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-brand">
        <a href="/admin" class="brand-logo">
            <div class="brand-icon">Z</div>
            <div class="brand-text">
                <span class="brand-name">ZANORA</span>
                <span class="brand-badge">ENTERPRISE</span>
            </div>
        </a>
        <button class="sidebar-toggle-btn" id="sidebar-toggle" title="Toggle Sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <!-- Quick Maintenance Control -->
    <div class="sidebar-widget">
        <form method="POST" id="sidebar-maintenance-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="toggle_maintenance_quick">
            <label class="maintenance-toggle-label">
                <span class="toggle-text"><i class="fas fa-tools"></i> Maintenance</span>
                <input type="checkbox" name="maintenance_mode_quick" value="1" <?= ($site_settings['maintenance_mode']??'0') === '1' ? 'checked' : '' ?> onchange="document.getElementById('sidebar-maintenance-form').submit()" class="toggle-checkbox">
            </label>
        </form>
    </div>

    <div class="sidebar-menu-wrapper">
        <div class="menu-section-title">PLATFORM CONTROL</div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-grid-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="?page=analytics" class="nav-item <?= $currentPage === 'analytics' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="?page=users" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="?page=roles" class="nav-item <?= $currentPage === 'roles' ? 'active' : '' ?>">
                        <i class="fas fa-shield-halved"></i>
                        <span>Roles & Access</span>
                    </a>
                </li>
                <li>
                    <a href="?page=admins" class="nav-item <?= $currentPage === 'admins' ? 'active' : '' ?>">
                        <i class="fas fa-user-gear"></i>
                        <span>System Admins</span>
                    </a>
                </li>
                <li>
                    <a href="?page=moderation" class="nav-item <?= $currentPage === 'moderation' ? 'active' : '' ?>">
                        <i class="fas fa-comment-slash"></i>
                        <span>Content Mod</span>
                    </a>
                </li>
                <li>
                    <a href="?page=reports" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>Reports & Inbox</span>
                        <?php if (!empty($live_stats['total_pending_alerts']) && $live_stats['total_pending_alerts'] > 0): ?>
                            <span class="sidebar-badge-count" style="margin-left: auto; background: #f43f5e; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 10px;"><?= $live_stats['total_pending_alerts'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="menu-section-title">SYSTEM & DATA</div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="?page=config" class="nav-item <?= $currentPage === 'config' ? 'active' : '' ?>">
                        <i class="fas fa-sliders"></i>
                        <span>Site Config</span>
                    </a>
                </li>
                <li>
                    <a href="?page=database" class="nav-item <?= $currentPage === 'database' ? 'active' : '' ?>">
                        <i class="fas fa-database"></i>
                        <span>DB & Cache</span>
                    </a>
                </li>
                <li>
                    <a href="?page=logs" class="nav-item <?= $currentPage === 'logs' ? 'active' : '' ?>">
                        <i class="fas fa-list-check"></i>
                        <span>Audit & Logs</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Sidebar Profile / Exit -->
    <div class="sidebar-footer">
        <div class="user-profile-badge">
            <div class="avatar-circle"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'S', 0, 1)) ?></div>
            <div class="user-meta">
                <span class="user-name"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Staff') ?></span>
                <span class="user-role"><?= strtoupper($_SESSION['admin_role'] ?? 'MOD') ?></span>
            </div>
        </div>
        <a href="?logout=true" class="logout-button" title="Exit Administration Center">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</aside>

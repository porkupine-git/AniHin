<?php
// logs.php - Security Logs & Event Auditing Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('admin');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Security & Audit Logs</h1>
        <p class="page-subtitle">Track administrative actions, system events, and failed security authentication attempts.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Admin Audit Trail -->
    <div class="table-container">
        <?php renderTableToolbar('audit-logs-table', 'Filter audit actions...'); ?>
        <div style="padding: 16px 20px 8px 20px;">
            <h3 style="font-size: 14px; font-weight: 600; color: #fff;"><i class="fas fa-shield-halved mr-2"></i>Admin Audit Trail (Latest 50)</h3>
        </div>
        <table class="admin-table" id="audit-logs-table">
            <thead>
                <tr>
                    <th>Admin User</th>
                    <th>Action Executed</th>
                    <th>IP Address & Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $logs = $conn->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50");
                if ($logs && $logs->num_rows > 0):
                while ($log = $logs->fetch_assoc()):
                ?>
                <tr>
                    <td><strong style="color: #fff;"><?= htmlspecialchars($log['username']) ?></strong></td>
                    <td><span style="color: var(--text-secondary);"><?= htmlspecialchars($log['action']) ?></span></td>
                    <td>
                        <small style="font-family: var(--font-mono); color: #fff;"><?= htmlspecialchars($log['ip_address']) ?></small><br>
                        <small style="color: var(--text-muted);"><?= $log['created_at'] ?></small>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No audit entries recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Failed Login Attempts -->
    <div class="table-container">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);">
            <h3 style="font-size: 14px; font-weight: 600; color: var(--danger-mono);"><i class="fas fa-triangle-exclamation mr-2"></i>Failed Login Attempts</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Target User</th>
                    <th>IP & Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $fails = $conn->query("SELECT * FROM failed_logins ORDER BY attempt_time DESC LIMIT 20");
                if ($fails && $fails->num_rows > 0):
                while ($f = $fails->fetch_assoc()):
                ?>
                <tr>
                    <td><strong style="color: #fff;"><?= htmlspecialchars($f['username']) ?></strong></td>
                    <td>
                        <small style="font-family: var(--font-mono); color: #fff;"><?= htmlspecialchars($f['ip_address']) ?></small><br>
                        <small style="color: var(--text-muted);"><?= $f['attempt_time'] ?></small>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="2" style="text-align: center; color: var(--text-muted);">No failed login attempts.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

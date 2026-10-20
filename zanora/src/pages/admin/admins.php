<?php
// admins.php - Staff & System Admins Control Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    
    if ($_POST['action'] === 'add_admin_user') {
        $res = addAdminUser($_POST);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=admins');
        exit;
    }
    if ($_POST['action'] === 'update_admin_user') {
        $res = updateAdminUser($_POST);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=admins');
        exit;
    }
    if ($_POST['action'] === 'delete_admin_user') {
        $res = deleteAdminUser($_POST['admin_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=admins');
        exit;
    }
}

$admin_users = getAllAdminUsers();

$editing_admin = null;
if (isset($_GET['edit_admin'])) {
    $stmt = $conn->prepare("SELECT id, username, email, is_blocked FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit_admin']);
    $stmt->execute();
    $editing_admin = $stmt->get_result()->fetch_assoc();
    if ($editing_admin) {
        $editing_admin['is_active'] = $editing_admin['is_blocked'] ? 0 : 1;
        $editing_admin['role'] = 'super_admin';
    }
    $stmt->close();
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Staff & System Admins</h1>
        <p class="page-subtitle">Manage administrative staff credentials, authorization levels, and active status.</p>
    </div>
    <?php if (!$editing_admin): ?>
        <button class="btn-primary" onclick="document.getElementById('add-admin-form').style.display='block'"><i class="fas fa-user-plus mr-1"></i> Register System Admin</button>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<?php if ($editing_admin): ?>
    <div class="form-container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 700;">Edit Admin Access: <?= htmlspecialchars($editing_admin['username']) ?></h2>
            <a href="?page=admins" class="btn-icon-action"><i class="fas fa-xmark"></i></a>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="update_admin_user">
            <input type="hidden" name="admin_id" value="<?= $editing_admin['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($editing_admin['username']) ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Admin Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editing_admin['email']) ?>" required class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>New Access Password (leave blank to keep)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Authorization Role</label>
                    <select name="role" class="form-control">
                        <option value="super_admin" <?= $editing_admin['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="admin" <?= $editing_admin['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="moderator" <?= $editing_admin['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                    </select>
                </div>
            </div>
            <div style="margin: 20px 0;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= $editing_admin['is_active'] ? 'checked' : '' ?> class="toggle-checkbox"> Active Account
                </label>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn-primary">Save Admin Credentials</button>
                <a href="?page=admins" class="btn-secondary" style="text-decoration: none;">Cancel</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Add Admin Form -->
    <div id="add-admin-form" class="form-container" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 700;">Register System Administrator</h2>
            <button onclick="document.getElementById('add-admin-form').style.display='none'" class="btn-icon-action"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add_admin_user">
            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Access Password</label>
                    <input type="password" name="password" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="moderator">Moderator</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Register Admin</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Admin Username</th>
                    <th>Email Address</th>
                    <th>Clearance Role</th>
                    <th>Status</th>
                    <th>Registered Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_users as $admin): ?>
                <tr>
                    <td><strong style="color: #fff;"><?= htmlspecialchars((string)($admin['username'] ?? '')) ?></strong></td>
                    <td><?= htmlspecialchars((string)($admin['email'] ?? '')) ?></td>
                    <td><span style="font-size: 11px; font-weight: 600; font-family: var(--font-mono); padding: 2px 8px; border-radius: 10px; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid var(--border-subtle);"><?= strtoupper((string)($admin['role'] ?? 'STAFF')) ?></span></td>
                    <td>
                        <?php if ($admin['is_active']): renderStatusBadge('active', 'Active'); else: renderStatusBadge('deactive', 'Deactivated'); endif; ?>
                    </td>
                    <td><?= date('Y-m-d', strtotime($admin['created_at'])) ?></td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="?page=admins&edit_admin=<?= $admin['id'] ?>" class="btn-icon-action" title="Edit Credentials"><i class="fas fa-gear"></i></a>
                            <?php if ($admin['role'] !== 'super_admin'): ?>
                                <button onclick="confirmDeleteAdmin(<?= $admin['id'] ?>)" class="btn-icon-action danger" title="Revoke Admin Access"><i class="fas fa-trash-can"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function confirmDeleteAdmin(adminId) {
    if (confirm('Revoke access privileges for this administrator?')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.innerHTML = `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"><input type="hidden" name="action" value="delete_admin_user"><input type="hidden" name="admin_id" value="${adminId}">`;
        document.body.appendChild(f);
        f.submit();
    }
}
</script>

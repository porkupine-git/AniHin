<?php
// roles.php - Roles & Permission Matrix Manager Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    
    if ($_POST['action'] === 'create_role') {
        $res = createRole($_POST);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=roles');
        exit;
    }
    if ($_POST['action'] === 'update_role') {
        $res = updateRole($_POST);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=roles');
        exit;
    }
    if ($_POST['action'] === 'delete_role') {
        $res = deleteRole($_POST['role_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=roles');
        exit;
    }
}

$all_roles = getAllRoles();

$editing_role = null;
if (isset($_GET['edit_role'])) {
    $editing_role = getRoleDetails($_GET['edit_role']);
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Roles & Access Control</h1>
        <p class="page-subtitle">Configure custom user roles, permissions tags, and aesthetic color indicators.</p>
    </div>
    <?php if (!$editing_role): ?>
        <button class="btn-primary" onclick="document.getElementById('create-role-form').style.display='block'"><i class="fas fa-plus mr-1"></i> Add Custom Role</button>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<?php if ($editing_role): ?>
    <div class="form-container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 700;">Edit Role: <?= htmlspecialchars($editing_role['name']) ?></h2>
            <a href="?page=roles" class="btn-icon-action"><i class="fas fa-xmark"></i></a>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="role_id" value="<?= $editing_role['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Role Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editing_role['name']) ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Role Slug</label>
                    <input type="text" name="slug" value="<?= htmlspecialchars($editing_role['slug']) ?>" required class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Color Identifier Tag</label>
                <input type="color" name="color" value="<?= $editing_role['color'] ?>" style="width: 60px; height: 38px; border: none; background: transparent; cursor: pointer;">
            </div>
            <div class="form-group">
                <label>Permissions (comma-separated list)</label>
                <textarea name="permissions" rows="3" class="form-control"><?= htmlspecialchars($editing_role['permissions']) ?></textarea>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn-primary">Apply Changes</button>
                <a href="?page=roles" class="btn-secondary" style="text-decoration: none;">Cancel</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Create Role Modal/Container -->
    <div id="create-role-form" class="form-container" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 700;">Create Custom Access Role</h2>
            <button onclick="document.getElementById('create-role-form').style.display='none'" class="btn-icon-action"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="create_role">
            <div class="form-row">
                <div class="form-group">
                    <label>Role Name</label>
                    <input type="text" name="name" required class="form-control" placeholder="e.g. VIP Member">
                </div>
                <div class="form-group">
                    <label>Role Slug</label>
                    <input type="text" name="slug" required class="form-control" placeholder="e.g. vip_member">
                </div>
            </div>
            <div class="form-group">
                <label>Tag Color</label>
                <input type="color" name="color" value="#ffffff" style="width: 60px; height: 38px; border: none; background: transparent; cursor: pointer;">
            </div>
            <div class="form-group">
                <label>Permissions List</label>
                <textarea name="permissions" rows="2" class="form-control" placeholder="moderate_comments, edit_users"></textarea>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Generate Role</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Role Label</th>
                    <th>Identifier Slug</th>
                    <th>Permissions Scope</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_roles as $role): ?>
                <tr>
                    <td>
                        <?php renderRoleTag($role['name'], $role['color']); ?>
                    </td>
                    <td><code style="font-family: var(--font-mono); color: var(--text-muted);"><?= htmlspecialchars($role['slug']) ?></code></td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <?php foreach (explode(',', $role['permissions']) as $perm): if (trim($perm) === '') continue; ?>
                                <span style="font-size: 11px; background: #27272a; border: 1px solid var(--border-subtle); padding: 2px 8px; border-radius: 12px; color: var(--text-secondary);"><?= htmlspecialchars($perm) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="?page=roles&edit_role=<?= $role['id'] ?>" class="btn-icon-action" title="Edit Role"><i class="fas fa-pen-to-square"></i></a>
                            <?php if (!$role['is_default']): ?>
                                <button onclick="confirmDeleteRole(<?= $role['id'] ?>)" class="btn-icon-action danger" title="Delete Role"><i class="fas fa-trash-can"></i></button>
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
function confirmDeleteRole(roleId) {
    if (confirm('Delete this custom access role?')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.innerHTML = `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"><input type="hidden" name="action" value="delete_role"><input type="hidden" name="role_id" value="${roleId}">`;
        document.body.appendChild(f);
        f.submit();
    }
}
</script>

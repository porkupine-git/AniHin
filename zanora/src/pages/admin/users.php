<?php
// users.php - User Accounts Administration Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('admin');

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    
    if ($_POST['action'] === 'update_user') {
        $res = updateUser($_POST);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        $edit_id = intval($_POST['user_id'] ?? 0);
        header('Location: ?page=users' . ($edit_id ? "&edit_user=$edit_id" : ''));
        exit;
    }
    if ($_POST['action'] === 'delete_user') {
        $res = deleteUser($_POST['user_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=users');
        exit;
    }
    if ($_POST['action'] === 'delete_user_post') {
        $res = deleteUserPost($_POST['post_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        $edit_id = intval($_POST['user_id'] ?? 0);
        header('Location: ?page=users' . ($edit_id ? "&edit_user=$edit_id&tab=posts" : ''));
        exit;
    }
    if ($_POST['action'] === 'delete_user_post_comment') {
        $res = deleteUserPostComment($_POST['comment_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        $edit_id = intval($_POST['user_id'] ?? 0);
        header('Location: ?page=users' . ($edit_id ? "&edit_user=$edit_id&tab=post_comments" : ''));
        exit;
    }
    if ($_POST['action'] === 'delete_user_anime_comment') {
        $res = deleteUserAnimeComment($_POST['comment_id']);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        $edit_id = intval($_POST['user_id'] ?? 0);
        header('Location: ?page=users' . ($edit_id ? "&edit_user=$edit_id&tab=anime_comments" : ''));
        exit;
    }
}

$current_page = isset($_GET['user_page']) ? intval($_GET['user_page']) : 1;
$users_data = getAllUsers($current_page);

$editing_user = null;
$active_tab = $_GET['tab'] ?? 'settings';

if (isset($_GET['edit_user'])) {
    $editing_user = getUserDetails($_GET['edit_user']);
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">User Accounts Administration</h1>
        <p class="page-subtitle">Manage user profiles, inspect posts, comments, clearance ranks, and security statuses.</p>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<?php if ($editing_user): ?>
    <div class="form-container" style="max-width: 1000px; margin-bottom: 30px;">
        <!-- User Profile Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <?php $avatarUrl = !empty($editing_user['avatar']) ? $editing_user['avatar'] : (!empty($editing_user['image']) ? $editing_user['image'] : 'https://via.placeholder.com/60'); ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" style="width: 54px; height: 54px; border-radius: 50%; border: 2px solid var(--border-subtle); object-fit: cover;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #fff; margin: 0 0 4px 0;">
                        <?= htmlspecialchars($editing_user['username']) ?>
                    </h2>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-muted);">
                        <span><i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($editing_user['email']) ?></span>
                        <span>•</span>
                        <span><i class="fas fa-calendar mr-1"></i>Joined <?= date('M d, Y', strtotime($editing_user['created_at'])) ?></span>
                        <span>•</span>
                        <span><i class="fas fa-award mr-1"></i><?= htmlspecialchars($editing_user['rank'] ?? 'Member') ?> (<?= number_format($editing_user['points'] ?? 0) ?> pts)</span>
                    </div>
                </div>
            </div>
            <a href="?page=users" class="btn-icon-action" style="font-size: 16px;"><i class="fas fa-xmark"></i></a>
        </div>

        <!-- Navigation Tabs -->
        <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-subtle); margin-bottom: 24px; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; padding-bottom: 4px;">
            <a href="?page=users&edit_user=<?= $editing_user['id'] ?>&tab=settings" class="btn-secondary" style="border-radius: 6px 6px 0 0; border-bottom: none; <?= $active_tab === 'settings' ? 'background: #ffffff; color: #000; font-weight: 700;' : 'color: var(--text-muted);' ?> padding: 8px 16px; text-decoration: none; font-size: 13px;">
                <i class="fas fa-sliders mr-2"></i>Account Configurations
            </a>
            <a href="?page=users&edit_user=<?= $editing_user['id'] ?>&tab=posts" class="btn-secondary" style="border-radius: 6px 6px 0 0; border-bottom: none; <?= $active_tab === 'posts' ? 'background: #ffffff; color: #000; font-weight: 700;' : 'color: var(--text-muted);' ?> padding: 8px 16px; text-decoration: none; font-size: 13px;">
                <i class="fas fa-newspaper mr-2"></i>User Posts (<?= count($editing_user['posts'] ?? []) ?>)
            </a>
            <a href="?page=users&edit_user=<?= $editing_user['id'] ?>&tab=post_comments" class="btn-secondary" style="border-radius: 6px 6px 0 0; border-bottom: none; <?= $active_tab === 'post_comments' ? 'background: #ffffff; color: #000; font-weight: 700;' : 'color: var(--text-muted);' ?> padding: 8px 16px; text-decoration: none; font-size: 13px;">
                <i class="fas fa-comments mr-2"></i>Post Comments (<?= count($editing_user['post_comments'] ?? []) ?>)
            </a>
            <a href="?page=users&edit_user=<?= $editing_user['id'] ?>&tab=anime_comments" class="btn-secondary" style="border-radius: 6px 6px 0 0; border-bottom: none; <?= $active_tab === 'anime_comments' ? 'background: #ffffff; color: #000; font-weight: 700;' : 'color: var(--text-muted);' ?> padding: 8px 16px; text-decoration: none; font-size: 13px;">
                <i class="fas fa-film mr-2"></i>Anime Comments (<?= count($editing_user['anime_comments'] ?? []) ?>)
            </a>
        </div>

        <!-- Tab 1: Account Configurations -->
        <?php if ($active_tab === 'settings'): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" value="<?= $editing_user['id'] ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?= htmlspecialchars($editing_user['username']) ?>" readonly class="form-control" style="opacity: 0.7;">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($editing_user['email']) ?>" readonly class="form-control" style="opacity: 0.7;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>User Points</label>
                        <input type="number" name="points" value="<?= $editing_user['points'] ?? 0 ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Clearance Rank</label>
                        <input type="text" name="rank" value="<?= htmlspecialchars($editing_user['rank'] ?? 'Member') ?>" class="form-control">
                    </div>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 18px; margin: 20px 0; padding: 16px; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: #fff;">
                        <input type="checkbox" name="is_verified" value="1" <?= $editing_user['is_verified'] ? 'checked' : '' ?> class="toggle-checkbox"> Verified Account
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: #fff;">
                        <input type="checkbox" name="is_admin" value="1" <?= $editing_user['is_admin'] ? 'checked' : '' ?> class="toggle-checkbox"> System Admin Access
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: #ef4444; font-weight: 600;">
                        <input type="checkbox" name="is_blocked" value="1" <?= $editing_user['is_blocked'] ? 'checked' : '' ?> class="toggle-checkbox"> Blocked Account
                    </label>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn-primary">Apply Configurations</button>
                    <a href="?page=users" class="btn-secondary" style="text-decoration: none;">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <!-- Tab 2: Community Posts -->
        <?php if ($active_tab === 'posts'): ?>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Post Title</th>
                            <th>Status</th>
                            <th>Likes</th>
                            <th>Created Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($editing_user['posts'])): foreach ($editing_user['posts'] as $post): ?>
                        <tr>
                            <td>
                                <strong style="color: #fff; display: block; font-size: 13px;"><?= htmlspecialchars($post['title']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden; max-width: 400px; white-space: nowrap;">
                                    <?= htmlspecialchars(mb_substr($post['content'], 0, 80)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($post['status']) ?>
                                </span>
                            </td>
                            <td style="color: #fff; font-weight: 600;"><?= number_format($post['like_count'] ?? 0) ?></td>
                            <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($post['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <form method="POST" onsubmit="return confirm('Delete this post permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete_user_post">
                                        <input type="hidden" name="user_id" value="<?= $editing_user['id'] ?>">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <button type="submit" class="btn-icon-action danger" title="Delete Post"><i class="fas fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">No community posts submitted by this user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Tab 3: Community Post Comments -->
        <?php if ($active_tab === 'post_comments'): ?>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Comment Text</th>
                            <th>Target Post</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($editing_user['post_comments'])): foreach ($editing_user['post_comments'] as $pc): ?>
                        <tr>
                            <td>
                                <div style="color: #fff; font-size: 13px;"><?= htmlspecialchars($pc['content']) ?></div>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">
                                    <?= htmlspecialchars($pc['post_title'] ?? ('Post #' . $pc['post_id'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($pc['status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($pc['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <form method="POST" onsubmit="return confirm('Delete this comment permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete_user_post_comment">
                                        <input type="hidden" name="user_id" value="<?= $editing_user['id'] ?>">
                                        <input type="hidden" name="comment_id" value="<?= $pc['id'] ?>">
                                        <button type="submit" class="btn-icon-action danger" title="Delete Comment"><i class="fas fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">No post comments written by this user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Tab 4: Anime Comments -->
        <?php if ($active_tab === 'anime_comments'): ?>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Anime Comment</th>
                            <th>Anime / Ep ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($editing_user['anime_comments'])): foreach ($editing_user['anime_comments'] as $ac): ?>
                        <tr>
                            <td>
                                <div style="color: #fff; font-size: 13px;"><?= htmlspecialchars($ac['content']) ?></div>
                                <?php if ($ac['is_spoil']): ?>
                                    <span style="font-size: 10px; background: rgba(239,68,68,0.2); color: #ef4444; padding: 2px 6px; border-radius: 3px; font-weight: 700;">SPOILER</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-family: var(--font-mono); color: #fff; font-size: 12px;">
                                    <?= htmlspecialchars($ac['movie_id']) ?><?= $ac['episode_id'] ? ' (Ep '.$ac['episode_id'].')' : '' ?>
                                </code>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($ac['status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($ac['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <form method="POST" onsubmit="return confirm('Delete this anime comment permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete_user_anime_comment">
                                        <input type="hidden" name="user_id" value="<?= $editing_user['id'] ?>">
                                        <input type="hidden" name="comment_id" value="<?= $ac['id'] ?>">
                                        <button type="submit" class="btn-icon-action danger" title="Delete Anime Comment"><i class="fas fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">No anime episode comments written by this user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
<?php else: ?>
    <div class="table-container">
        <?php renderTableToolbar('users-table', 'Search accounts by username or email...'); ?>
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="admin-table" id="users-table">
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>Email Address</th>
                        <th>Registration Date</th>
                        <th>Security Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <?php foreach ($users_data['users'] as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php $listAvatar = !empty($user['avatar']) ? $user['avatar'] : (!empty($user['image']) ? $user['image'] : 'https://via.placeholder.com/40'); ?>
                                <img src="<?= htmlspecialchars($listAvatar) ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border-subtle); object-fit: cover;">
                                <div>
                                    <strong style="color: #fff;"><?= htmlspecialchars($user['username']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($user['rank'] ?? 'Member') ?> (<?= number_format($user['points'] ?? 0) ?> pts)</div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <?php if ($user['is_verified']): renderStatusBadge('verified'); endif; ?>
                                <?php if ($user['is_admin']): renderStatusBadge('admin'); endif; ?>
                                <?php if ($user['is_blocked']): renderStatusBadge('blocked'); endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="?page=users&edit_user=<?= $user['id'] ?>" class="btn-icon-action" title="Edit Configuration & Inspect Content"><i class="fas fa-gear"></i></a>
                                <button onclick="confirmDeleteUser(<?= $user['id'] ?>)" class="btn-icon-action danger" title="Delete Profile"><i class="fas fa-trash-can"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($users_data['page'], $users_data['total_pages'], '?page=users'); ?>
    </div>
<?php endif; ?>

<script>
function confirmDeleteUser(userId) {
    if (confirm('Are you sure you want to permanently delete this user profile?')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.innerHTML = `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="${userId}">`;
        document.body.appendChild(f);
        f.submit();
    }
}
</script>

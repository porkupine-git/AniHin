<?php
// moderation.php - Content & Comments Moderation Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('moderator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'moderate_comment') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    
    $res = moderateComment($_POST['comment_id'], $_POST['status'], $_POST['table_type'] ?? 'episode');
    $_SESSION['message'] = $res['message'];
    $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
    header('Location: ?page=moderation');
    exit;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Content & Comments Moderation</h1>
        <p class="page-subtitle">Review community comments, moderate episode discussions, and hide flagged content.</p>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<!-- Episode Comments Table -->
<div class="table-container">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);">
        <h3 style="font-size: 15px; font-weight: 600; color: #fff;"><i class="fas fa-comments mr-2"></i>Latest Episode Comments</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Author & Item Info</th>
                <th>Content Snippet</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cRes = $conn->query("SELECT c.id, c.movie_id, c.episode_id, c.content, c.status, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 15");
            if ($cRes && $cRes->num_rows > 0):
            while ($comment = $cRes->fetch_assoc()):
            ?>
            <tr>
                <td>
                    <strong style="color: #fff;"><?= htmlspecialchars((string)($comment['username'] ?? '')) ?></strong>
                    <div style="font-size: 11px; color: var(--text-muted);">Anime ID: <?= htmlspecialchars((string)($comment['movie_id'] ?? '')) ?> (Ep: <?= htmlspecialchars((string)($comment['episode_id'] ?? '')) ?>)</div>
                </td>
                <td><p style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);"><?= htmlspecialchars((string)($comment['content'] ?? '')) ?></p></td>
                <td>
                    <?php if ($comment['status'] === 'active'): renderStatusBadge('active', 'Active'); else: renderStatusBadge('hidden', 'Hidden'); endif; ?>
                </td>
                <td style="text-align: right;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="moderate_comment">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <input type="hidden" name="table_type" value="episode">
                        <?php if ($comment['status'] === 'active'): ?>
                            <input type="hidden" name="status" value="hidden">
                            <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; background: rgba(239, 68, 68, 0.1); color: var(--danger-mono); border-color: rgba(239, 68, 68, 0.2);"><i class="fas fa-eye-slash mr-1"></i> Hide</button>
                        <?php else: ?>
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; background: rgba(16, 185, 129, 0.1); color: var(--success-mono); border-color: rgba(16, 185, 129, 0.2);"><i class="fas fa-check mr-1"></i> Approve</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No recent episode comments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Community Post Comments Table -->
<div class="table-container">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);">
        <h3 style="font-size: 15px; font-weight: 600; color: #fff;"><i class="fas fa-paper-plane mr-2"></i>Latest Community Post Comments</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Comment Author</th>
                <th>Content Snippet</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cpRes = $conn->query("SELECT c.id, c.content, c.status, c.created_at, u.username FROM post_comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 15");
            if ($cpRes && $cpRes->num_rows > 0):
            while ($comment = $cpRes->fetch_assoc()):
            ?>
            <tr>
                <td><strong style="color: #fff;"><?= htmlspecialchars((string)($comment['username'] ?? '')) ?></strong></td>
                <td><p style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);"><?= htmlspecialchars((string)($comment['content'] ?? '')) ?></p></td>
                <td>
                    <?php if ($comment['status'] === 'visible'): renderStatusBadge('active', 'Visible'); else: renderStatusBadge('hidden', 'Hidden'); endif; ?>
                </td>
                <td style="text-align: right;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="moderate_comment">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <input type="hidden" name="table_type" value="community">
                        <?php if ($comment['status'] === 'visible'): ?>
                            <input type="hidden" name="status" value="hidden">
                            <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; background: rgba(239, 68, 68, 0.1); color: var(--danger-mono); border-color: rgba(239, 68, 68, 0.2);"><i class="fas fa-eye-slash mr-1"></i> Hide</button>
                        <?php else: ?>
                            <input type="hidden" name="status" value="visible">
                            <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; background: rgba(16, 185, 129, 0.1); color: var(--success-mono); border-color: rgba(16, 185, 129, 0.2);"><i class="fas fa-check mr-1"></i> Approve</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No community post comments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

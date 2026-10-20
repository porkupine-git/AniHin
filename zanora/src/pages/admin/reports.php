<?php
// reports.php - User Inquiries & Content Moderation Reports Console
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('moderator');

// Handle Form & Status Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }

    if ($_POST['action'] === 'update_inquiry_status') {
        $id = intval($_POST['inquiry_id'] ?? 0);
        $status = $_POST['status'] ?? 'unread';
        $notes = $_POST['admin_notes'] ?? null;
        $res = updateContactMessageStatus($id, $status, $notes);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=reports&tab=inquiries' . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''));
        exit;
    }

    if ($_POST['action'] === 'delete_inquiry') {
        $id = intval($_POST['inquiry_id'] ?? 0);
        $res = deleteContactMessage($id);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=reports&tab=inquiries' . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''));
        exit;
    }

    if ($_POST['action'] === 'dismiss_comment_report') {
        $id = intval($_POST['report_id'] ?? 0);
        $res = dismissCommentReport($id);
        $_SESSION['message'] = $res['message'];
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=reports&tab=comments');
        exit;
    }

    if ($_POST['action'] === 'moderate_comment_from_report') {
        $comment_id = intval($_POST['comment_id'] ?? 0);
        $status = $_POST['status'] ?? 'hidden';
        $res = moderateComment($comment_id, $status, 'episode');
        if (isset($_POST['report_id'])) {
            dismissCommentReport((int)$_POST['report_id']);
        }
        $_SESSION['message'] = $res['message'] . ' and report resolved.';
        $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
        header('Location: ?page=reports&tab=comments');
        exit;
    }
}

$active_tab = $_GET['tab'] ?? 'inquiries';
$status_filter = $_GET['status'] ?? '';
$search_query = trim($_GET['q'] ?? '');
$current_page = max(1, intval($_GET['p'] ?? 1));

$inquiry_counts = getContactMessageCounts();
$inquiries_data = getContactMessages($current_page, 15, $status_filter, $search_query);
$comment_reports = getCommentReportsDetailed(50);
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-flag mr-2" style="color: var(--accent-rose);"></i>Inquiries & Content Reports</h1>
        <p class="page-subtitle">Track user contact inquiries, bug reports, feature requests, and flagged community comments.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="?page=reports&tab=inquiries" class="btn-secondary" style="font-size: 13px;">
            <i class="fas fa-rotate mr-1"></i> Refresh
        </a>
        <a href="/contact" target="_blank" class="btn-primary" style="font-size: 13px; text-decoration: none;">
            <i class="fas fa-arrow-up-right-from-square mr-1"></i> Public Contact Form
        </a>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<!-- Top Metrics Row -->
<div class="kpi-row" style="margin-bottom: 24px;">
    <?php
    renderKpiCard("Unread Inquiries", number_format($inquiry_counts['unread']), "Needs staff review");
    renderKpiCard("In Progress", number_format($inquiry_counts['in_progress']), "Under active investigation");
    renderKpiCard("Resolved Total", number_format($inquiry_counts['resolved']), "Successfully addressed");
    renderKpiCard("Comment Reports", number_format(count($comment_reports)), "Flagged community comments");
    ?>
</div>

<!-- Tab Navigation -->
<div class="tab-navigation">
    <a href="?page=reports&tab=inquiries" class="tab-btn <?= $active_tab === 'inquiries' ? 'active' : '' ?>">
        <i class="fas fa-envelope-open-text"></i> Contact Inquiries 
        <?php if ($inquiry_counts['unread'] > 0): ?>
            <span class="badge-pill badge-primary" style="padding: 2px 7px; font-size: 10px; margin-left: 4px;"><?= $inquiry_counts['unread'] ?></span>
        <?php endif; ?>
    </a>
    <a href="?page=reports&tab=comments" class="tab-btn <?= $active_tab === 'comments' ? 'active' : '' ?>">
        <i class="fas fa-shield-halved"></i> Flagged Comments
        <?php if (count($comment_reports) > 0): ?>
            <span class="badge-pill badge-danger" style="padding: 2px 7px; font-size: 10px; margin-left: 4px;"><?= count($comment_reports) ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if ($active_tab === 'inquiries'): ?>
    <!-- Contact Messages Module -->
    <div class="table-container">
        <!-- Sub-filter Navigation & Search -->
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="?page=reports&tab=inquiries" class="btn-subfilter <?= empty($status_filter) ? 'active' : '' ?>">
                    All (<?= $inquiry_counts['all'] ?>)
                </a>
                <a href="?page=reports&tab=inquiries&status=unread" class="btn-subfilter <?= $status_filter === 'unread' ? 'active' : '' ?>" style="color: var(--accent-amber);">
                    <i class="fas fa-circle" style="font-size: 7px;"></i> Unread (<?= $inquiry_counts['unread'] ?>)
                </a>
                <a href="?page=reports&tab=inquiries&status=in_progress" class="btn-subfilter <?= $status_filter === 'in_progress' ? 'active' : '' ?>" style="color: var(--accent-blue);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 9px;"></i> In Progress (<?= $inquiry_counts['in_progress'] ?>)
                </a>
                <a href="?page=reports&tab=inquiries&status=resolved" class="btn-subfilter <?= $status_filter === 'resolved' ? 'active' : '' ?>" style="color: var(--accent-emerald);">
                    <i class="fas fa-check" style="font-size: 9px;"></i> Resolved (<?= $inquiry_counts['resolved'] ?>)
                </a>
                <a href="?page=reports&tab=inquiries&status=spam" class="btn-subfilter <?= $status_filter === 'spam' ? 'active' : '' ?>" style="color: var(--text-muted);">
                    Spam (<?= $inquiry_counts['spam'] ?>)
                </a>
            </div>

            <form method="GET" style="display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="tab" value="inquiries">
                <?php if (!empty($status_filter)): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <?php endif; ?>
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 11px; color: var(--text-muted); font-size: 12px;"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search subject, email..." class="form-control" style="padding-left: 32px; height: 36px; width: 230px; font-size: 12px;">
                </div>
                <button type="submit" class="btn-secondary" style="height: 36px; padding: 0 14px; font-size: 12px;">Search</button>
                <?php if (!empty($search_query)): ?>
                    <a href="?page=reports&tab=inquiries<?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?>" class="btn-secondary" style="height: 36px; display: flex; align-items: center; padding: 0 10px;" title="Clear Search"><i class="fas fa-xmark"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 250px;">Sender</th>
                    <th>Subject & Message Snippet</th>
                    <th style="width: 140px;">Status</th>
                    <th style="width: 160px;">Submitted</th>
                    <th style="width: 180px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inquiries_data['messages'])): foreach ($inquiries_data['messages'] as $msg): ?>
                    <tr style="<?= $msg['status'] === 'unread' ? 'background: rgba(99, 102, 241, 0.04);' : '' ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--bg-surface); border: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--accent-primary); font-size: 13px;">
                                    <?= strtoupper(substr($msg['name'] ?: $msg['email'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #fff;">
                                        <?= htmlspecialchars($msg['name'] ?: 'Visitor') ?>
                                        <?php if (!empty($msg['username'])): ?>
                                            <span class="badge-pill badge-primary" style="font-size: 9px; padding: 1px 5px; margin-left: 4px;">User: <?= htmlspecialchars($msg['username']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="font-size: 11.5px; color: var(--text-secondary); text-decoration: none;">
                                        <i class="fas fa-envelope mr-1" style="font-size: 10px;"></i><?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="javascript:void(0)" onclick="openInquiryModal(<?= htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8') ?>)" style="text-decoration: none; display: block;">
                                <div style="font-weight: 600; color: #fff; margin-bottom: 2px;">
                                    <?= htmlspecialchars($msg['subject']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); max-width: 440px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </a>
                        </td>
                        <td>
                            <?php 
                            switch ($msg['status']) {
                                case 'unread':
                                    renderStatusBadge('pending', 'Unread');
                                    break;
                                case 'in_progress':
                                    echo '<span class="badge-pill badge-info"><i class="fas fa-spinner fa-spin mr-1"></i>In Progress</span>';
                                    break;
                                case 'resolved':
                                    renderStatusBadge('active', 'Resolved');
                                    break;
                                case 'spam':
                                    renderStatusBadge('blocked', 'Spam');
                                    break;
                                case 'read':
                                default:
                                    renderStatusBadge('inactive', 'Read');
                                    break;
                            }
                            ?>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #fff;"><?= date('M d, Y', strtotime($msg['created_at'])) ?></div>
                            <small style="color: var(--text-muted); font-size: 11px;"><?= date('h:i A', strtotime($msg['created_at'])) ?></small>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <button type="button" class="btn-secondary" style="padding: 5px 10px; font-size: 11px;" onclick="openInquiryModal(<?= htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>

                                <form method="POST" style="display: inline;" onsubmit="return confirm('Permanently delete this inquiry?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete_inquiry">
                                    <input type="hidden" name="inquiry_id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="btn-secondary" style="padding: 5px 8px; font-size: 11px; color: var(--danger-mono); border-color: rgba(239, 68, 68, 0.2);" title="Delete Inquiry">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 48px; color: var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                            No contact inquiries found matching the selected filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($inquiries_data['total_pages'] > 1): ?>
            <div style="padding: 16px 20px; border-top: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 12px; color: var(--text-muted);">
                    Showing page <?= $inquiries_data['page'] ?> of <?= $inquiries_data['total_pages'] ?> (<?= $inquiries_data['total'] ?> total inquiries)
                </span>
                <div style="display: flex; gap: 6px;">
                    <?php if ($inquiries_data['page'] > 1): ?>
                        <a href="?page=reports&tab=inquiries&p=<?= $inquiries_data['page'] - 1 ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?><?= !empty($search_query) ? '&q=' . urlencode($search_query) : '' ?>" class="btn-secondary" style="padding: 4px 10px; font-size: 12px;">Previous</a>
                    <?php endif; ?>
                    <?php if ($inquiries_data['page'] < $inquiries_data['total_pages']): ?>
                        <a href="?page=reports&tab=inquiries&p=<?= $inquiries_data['page'] + 1 ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?><?= !empty($search_query) ? '&q=' . urlencode($search_query) : '' ?>" class="btn-secondary" style="padding: 4px 10px; font-size: 12px;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- Comment Reports Module -->
    <div class="table-container">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 15px; font-weight: 600; color: #fff;">
                <i class="fas fa-flag mr-2" style="color: var(--accent-rose);"></i>Flagged Community Comments
            </h3>
            <span style="font-size: 12px; color: var(--text-muted);"><?= count($comment_reports) ?> active reports awaiting moderation</span>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reported Reason & Reporter</th>
                    <th>Flagged Content & Context</th>
                    <th>Reported Date</th>
                    <th style="text-align: right;">Moderation Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($comment_reports)): foreach ($comment_reports as $cr): ?>
                    <tr>
                        <td>
                            <div style="margin-bottom: 4px;">
                                <?php if ((int)$cr['type'] === 1): ?>
                                    <span class="badge-pill badge-danger"><i class="fas fa-triangle-exclamation mr-1"></i>Spam Flag</span>
                                <?php elseif ((int)$cr['type'] === 2): ?>
                                    <span class="badge-pill badge-warning"><i class="fas fa-eye-slash mr-1"></i>Spoiler</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-secondary">Flagged</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">
                                Reporter: <strong style="color: #fff;"><?= htmlspecialchars($cr['reporter_username'] ?: 'User #' . $cr['user_id']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 460px; color: var(--text-primary); font-size: 13px; line-height: 1.4; margin-bottom: 4px;">
                                <?= htmlspecialchars($cr['comment_content'] ?: '[Comment content unavailable or deleted]') ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                Author: <span style="color: var(--text-secondary);"><?= htmlspecialchars($cr['author_username'] ?: 'Unknown') ?></span>
                                <?php if (!empty($cr['movie_id'])): ?>
                                    • Anime: <code style="color: var(--accent-blue);"><?= htmlspecialchars($cr['movie_id']) ?></code> (Ep: <?= $cr['episode_id'] ?>)
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #fff;"><?= date('M d, Y', strtotime($cr['created_at'])) ?></div>
                            <small style="color: var(--text-muted); font-size: 11px;"><?= date('h:i A', strtotime($cr['created_at'])) ?></small>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <!-- Hide Comment -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="moderate_comment_from_report">
                                    <input type="hidden" name="comment_id" value="<?= $cr['comment_id'] ?>">
                                    <input type="hidden" name="report_id" value="<?= $cr['id'] ?>">
                                    <input type="hidden" name="status" value="hidden">
                                    <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; color: var(--accent-rose); border-color: rgba(244, 63, 94, 0.3);">
                                        <i class="fas fa-eye-slash mr-1"></i> Hide Comment
                                    </button>
                                </form>

                                <!-- Dismiss Report -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="dismiss_comment_report">
                                    <input type="hidden" name="report_id" value="<?= $cr['id'] ?>">
                                    <button type="submit" class="btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                        <i class="fas fa-check mr-1"></i> Dismiss Report
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 48px; color: var(--text-muted);">
                            <i class="fas fa-shield-check" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.4; color: var(--accent-emerald);"></i>
                            All clean! There are no pending community comment reports right now.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Inquiry Detail Modal -->
<div id="inquiry-detail-modal" class="admin-modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeInquiryModal()"></div>
    <div class="modal-card" style="max-width: 650px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">Inquiry Details</h3>
            </div>
            <button onclick="closeInquiryModal()" class="btn-icon-action"><i class="fas fa-xmark"></i></button>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="update_inquiry_status">
            <input type="hidden" name="inquiry_id" id="modal-inquiry-id" value="">

            <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                <!-- Header Meta Strip -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px;">Sender</div>
                        <div style="font-size: 14px; font-weight: 700; color: #fff;" id="modal-sender-name"></div>
                        <a href="#" id="modal-sender-email-link" style="font-size: 12px; color: var(--accent-blue); text-decoration: none;">
                            <i class="fas fa-envelope mr-1"></i><span id="modal-sender-email"></span>
                        </a>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px;">Date Submitted</div>
                        <div style="font-size: 13px; font-weight: 600; color: #fff;" id="modal-date"></div>
                        <div style="font-size: 11px; color: var(--text-muted);" id="modal-ip"></div>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Subject</label>
                    <div id="modal-subject" style="font-size: 15px; font-weight: 700; color: #fff; background: var(--bg-card); padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);"></div>
                </div>

                <!-- Message Body -->
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Message Body</label>
                    <div id="modal-message" style="font-size: 13.5px; line-height: 1.6; color: var(--text-primary); background: var(--bg-input); padding: 14px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); max-height: 220px; overflow-y: auto; white-space: pre-wrap;"></div>
                </div>

                <!-- Status & Notes Controls -->
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Update Status</label>
                        <select name="status" id="modal-status-select" class="form-control">
                            <option value="unread">Unread / New</option>
                            <option value="read">Mark as Read</option>
                            <option value="in_progress">In Progress / Investigating</option>
                            <option value="resolved">Resolved</option>
                            <option value="spam">Flag as Spam</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; display: flex; align-items: flex-end;">
                        <a href="#" id="modal-reply-btn" class="btn-secondary" style="width: 100%; height: 42px; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                            <i class="fas fa-reply" style="color: var(--accent-primary);"></i> Reply via Email Client
                        </a>
                    </div>
                </div>

                <div class="form-group">
                    <label>Staff Internal Notes <small style="color: var(--text-muted); font-weight: 400;">(Visible to staff only)</small></label>
                    <textarea name="admin_notes" id="modal-admin-notes" class="form-control" rows="2" placeholder="Add resolution notes, ticket references, or context..."></textarea>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" onclick="closeInquiryModal()" class="btn-secondary">Close</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-floppy-disk mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-subfilter {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all var(--transition-fast);
}
.btn-subfilter:hover {
    color: #fff;
    border-color: var(--border-strong);
    background: var(--bg-card-hover);
}
.btn-subfilter.active {
    background: var(--accent-glow);
    border-color: var(--accent-primary);
    color: #fff;
}
.admin-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
}
.modal-card {
    position: relative;
    background: var(--bg-modal);
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-modal);
    width: 100%;
    overflow: hidden;
    animation: modalSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-body {
    padding: 22px 24px;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border-subtle);
    background: rgba(0, 0, 0, 0.15);
}
</style>

<script>
function openInquiryModal(msg) {
    document.getElementById('modal-inquiry-id').value = msg.id;
    document.getElementById('modal-sender-name').textContent = msg.name ? msg.name : (msg.username ? msg.username : 'Visitor');
    document.getElementById('modal-sender-email').textContent = msg.email;
    document.getElementById('modal-sender-email-link').href = 'mailto:' + encodeURIComponent(msg.email);
    document.getElementById('modal-date').textContent = msg.created_at;
    document.getElementById('modal-ip').textContent = 'IP: ' + (msg.ip_address || 'N/A');
    document.getElementById('modal-subject').textContent = msg.subject;
    document.getElementById('modal-message').textContent = msg.message;
    document.getElementById('modal-status-select').value = msg.status || 'unread';
    document.getElementById('modal-admin-notes').value = msg.admin_notes || '';
    
    var replySubject = 'Re: ' + msg.subject;
    var replyBody = "\n\n--- Original Inquiry ---\n" + msg.message;
    document.getElementById('modal-reply-btn').href = 'mailto:' + encodeURIComponent(msg.email) + '?subject=' + encodeURIComponent(replySubject) + '&body=' + encodeURIComponent(replyBody);

    document.getElementById('inquiry-detail-modal').style.display = 'flex';
}

function closeInquiryModal() {
    document.getElementById('inquiry-detail-modal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeInquiryModal();
    }
});
</script>

<?php
// dashboard.php - Executive Console Overview Page
require_once __DIR__ . '/includes/functions.php';

if (!isset($live_stats)) {
    $live_stats = getLiveStats();
}
$daily_stats = getDailyStats();
$traffic_data = getTrafficData();
$system_stats = getSystemStats();
$recent_inquiries = getContactMessages(1, 5);
$recent_comment_reports = getCommentReportsDetailed(5);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Executive Console</h1>
        <p class="page-subtitle">Real-time platform telemetry, user metrics, and operational performance.</p>
    </div>
    <button class="btn-secondary" onclick="refreshStats()"><i class="fas fa-rotate mr-1"></i> Refresh Console</button>
</div>

<!-- Primary Stats Grid -->
<div class="stats-grid">
    <?php
    renderStatCard("Live Connections", $live_stats['live_visitors'], "Active in last 5 minutes", "fa-tower-broadcast", "live-visitors");
    renderStatCard("Users Registered", $live_stats['total_users'], "Growth today: +" . $live_stats['new_users_today'], "fa-users-gear", "total-users");
    renderStatCard("Today's Page Views", $live_stats['today_pageviews'], "Unique IP: <span id='today-unique'>" . $live_stats['today_unique'] . "</span>", "fa-eye", "today-pageviews");
    renderStatCard("Reports & Inquiries", $live_stats['total_pending_alerts'] ?? 0, "Unread: <strong style='color:var(--accent-amber);'>" . ($live_stats['unread_inquiries'] ?? 0) . "</strong> | Flagged: <strong style='color:var(--accent-rose);'>" . ($live_stats['pending_reports'] ?? 0) . "</strong>", "fa-envelope-open-text", "unread-reports");
    ?>
</div>

<!-- Secondary KPI Metrics Row -->
<div class="kpi-row">
    <?php
    renderKpiCard("Avg Daily Visitors", number_format($system_stats['avg_daily']));
    renderKpiCard("Peak Visitors Day", $system_stats['peak_day'] !== 'N/A' ? date('M d, Y', strtotime($system_stats['peak_day'])) : 'N/A', number_format($system_stats['peak_visitors']) . " visits");
    renderKpiCard("All-Time Page Views", number_format($system_stats['total_pageviews']));
    renderKpiCard("All-Time Unique IPs", number_format($system_stats['total_unique']));
    ?>
</div>

<!-- Tab Navigation -->
<div class="tab-navigation">
    <button class="tab-btn active" id="tab-btn-overview" onclick="switchTab('overview')"><i class="fas fa-chart-pie"></i> Overview</button>
    <button class="tab-btn" id="tab-btn-reports" onclick="switchTab('reports')">
        <i class="fas fa-envelope-open-text"></i> Inquiries & Reports
        <?php if (!empty($live_stats['total_pending_alerts']) && $live_stats['total_pending_alerts'] > 0): ?>
            <span class="badge-pill badge-danger" style="font-size: 10px; padding: 1px 6px; margin-left: 4px;"><?= $live_stats['total_pending_alerts'] ?></span>
        <?php endif; ?>
    </button>
    <button class="tab-btn" id="tab-btn-active-users" onclick="switchTab('active-users')"><i class="fas fa-users"></i> Active Stream</button>
    <button class="tab-btn" id="tab-btn-system-audit" onclick="switchTab('system-audit')"><i class="fas fa-list-check"></i> System Audit</button>
</div>

<!-- Tab 1: Overview Charts -->
<div class="tab-content active" id="tab-content-overview">
    <div class="admin-grid-2-1">
        <div class="table-container" style="padding: 24px;">
            <h3 style="font-size: 15px; margin-bottom: 16px;"><i class="fas fa-chart-area mr-2"></i>Traffic Overview (Last 30 Days)</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="dailyChartCanvas"></canvas>
            </div>
        </div>

        <div class="table-container" style="padding: 24px;">
            <h3 style="font-size: 15px; margin-bottom: 16px;"><i class="fas fa-chart-pie mr-2"></i>Traffic Sources</h3>
            <div style="height: 240px; position: relative;">
                <canvas id="trafficChartCanvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tab 2: Recent Reports & Inquiries Stream -->
<div class="tab-content" id="tab-content-reports">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;">
                <i class="fas fa-inbox mr-2" style="color: var(--accent-indigo, #818cf8);"></i>Pending Inquiries & User Reports
            </h3>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                Latest submissions requiring staff triage or moderation.
            </p>
        </div>
        <a href="index.php?page=reports" class="btn-primary" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
            <i class="fas fa-arrow-up-right-from-square mr-1"></i> Open Full Reports Hub
        </a>
    </div>

    <div class="admin-grid-2">
        <!-- Recent Contact Inquiries -->
        <div class="table-container" style="padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #fff; margin: 0;">
                    <i class="fas fa-envelope mr-2" style="color: var(--accent-indigo, #818cf8);"></i>Latest Contact Messages
                </h4>
                <a href="index.php?page=reports&tab=inquiries" style="font-size: 12px; color: var(--accent-indigo, #818cf8); text-decoration: none;">View All &rarr;</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th style="text-align: right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_inquiries['messages'])): foreach ($recent_inquiries['messages'] as $inq): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #fff; font-size: 13px;"><?= htmlspecialchars($inq['name']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($inq['email']) ?></div>
                            </td>
                            <td>
                                <div style="color: var(--text-primary); font-size: 13px; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($inq['subject']) ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClassMap = [
                                    'new' => 'badge-danger',
                                    'unread' => 'badge-danger',
                                    'read' => 'badge-info',
                                    'in_progress' => 'badge-warning',
                                    'replied' => 'badge-success',
                                    'resolved' => 'badge-success',
                                    'spam' => 'badge-secondary'
                                ];
                                $badgeClass = $statusClassMap[$inq['status'] ?? ''] ?? 'badge-secondary';
                                ?>
                                <span class="badge-pill <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($inq['status'])) ?></span>
                            </td>
                            <td style="text-align: right; color: var(--text-muted); font-size: 11px; white-space: nowrap;">
                                <?= date('M d, H:i', strtotime($inq['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 18px 0;">No contact messages yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Flagged Comments -->
        <div class="table-container" style="padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #fff; margin: 0;">
                    <i class="fas fa-flag mr-2" style="color: var(--accent-rose, #f43f5e);"></i>Reported Comments
                </h4>
                <a href="index.php?page=reports&tab=comments" style="font-size: 12px; color: var(--accent-rose, #f43f5e); text-decoration: none;">View All &rarr;</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Reported Item</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_comment_reports)): foreach ($recent_comment_reports as $rep): ?>
                        <tr>
                            <td>
                                <div style="color: #fff; font-size: 12px; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($rep['comment_content'] ?: 'Comment #' . $rep['comment_id']) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    By: <?= htmlspecialchars($rep['comment_author'] ?? 'User') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-pill badge-warning" style="font-size: 11px;"><?= htmlspecialchars($rep['type'] ?? 'Flagged') ?></span>
                            </td>
                            <td>
                                <span class="badge-pill <?= ($rep['status'] === 'resolved' ? 'badge-success' : 'badge-danger') ?>">
                                    <?= ucfirst(htmlspecialchars($rep['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="index.php?page=reports&tab=comments" class="btn-icon" title="Review Report" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background: rgba(99,102,241,0.12); color: var(--accent-indigo, #818cf8);">
                                    <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 18px 0;">No active comment reports.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tab 3: Active Stream -->
<div class="tab-content" id="tab-content-active-users">
    <div class="admin-grid-2">
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 14px; margin-bottom: 14px;"><i class="fas fa-globe mr-2"></i>Top Visited Paths</h3>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Page Path</th>
                            <th style="text-align: right;">Active Viewers</th>
                        </tr>
                    </thead>
                    <tbody id="top-pages-body">
                        <?php if (!empty($live_stats['top_pages'])): foreach($live_stats['top_pages'] as $p): ?>
                        <tr>
                            <td><code style="color: var(--text-primary); font-family: var(--font-mono);"><?= htmlspecialchars($p['page']) ?></code></td>
                            <td style="text-align: right; font-weight: 700; color: #fff;"><?= $p['count'] ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="2" style="text-align: center; color: var(--text-muted);">No active visitor data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 14px; margin-bottom: 14px;"><i class="fas fa-user-clock mr-2"></i>Live Visitors Stream</h3>
            <div id="live-visitors-list" style="display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto;">
                <?php if (!empty($live_stats['visitors_list'])): foreach($live_stats['visitors_list'] as $v): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: var(--bg-surface); border-radius: var(--radius-sm); font-size: 12px;">
                    <div>
                        <span style="font-family: var(--font-mono); font-weight: 600; color: #fff;"><?= htmlspecialchars($v['ip']) ?></span>
                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($v['page']) ?></div>
                    </div>
                    <div style="text-align: right; color: var(--text-muted);">
                        <div><?= htmlspecialchars($v['browser']) ?></div>
                        <div style="font-size: 10px;"><?= $v['time'] ?></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 20px 0;">No active visitor stream.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tab 4: System Audit -->
<div class="tab-content" id="tab-content-system-audit">
    <div class="admin-grid-2">
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 14px; margin-bottom: 14px;"><i class="fas fa-user-plus mr-2"></i>Recent Signups</h3>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th style="text-align: right;">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($live_stats['recent_users'])): foreach($live_stats['recent_users'] as $u): ?>
                        <tr>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($u['username']) ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                            <td style="text-align: right; color: var(--text-muted);"><?= $u['date'] ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No recent signups.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 14px; margin-bottom: 14px;"><i class="fas fa-shield mr-2"></i>Recent Admin Audit Log</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto;">
                <?php if (!empty($live_stats['recent_audit'])): foreach($live_stats['recent_audit'] as $a): ?>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 8px 12px; background: var(--bg-surface); border-radius: var(--radius-sm); font-size: 12px;">
                    <div>
                        <strong style="color: #fff;"><?= htmlspecialchars($a['username']) ?></strong>
                        <div style="color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($a['action']) ?></div>
                    </div>
                    <div style="text-align: right; color: var(--text-muted); font-family: var(--font-mono); font-size: 11px;">
                        <div><?= htmlspecialchars($a['ip']) ?></div>
                        <div><?= $a['date'] ?></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 20px 0;">No audit entries.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function initDashboardCharts() {
        try {
            const dailyData = <?= json_encode($daily_stats) ?>;
            const trafficData = <?= json_encode($traffic_data) ?>;
            renderDailyChart('dailyChartCanvas', dailyData);
            renderTrafficChart('trafficChartCanvas', trafficData);
        } catch (e) {
            console.error('Dashboard charts error:', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
</script>

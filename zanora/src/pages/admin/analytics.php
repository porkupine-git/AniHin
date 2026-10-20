<?php
// analytics.php - Comprehensive Traffic & Visitor Analytics Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('moderator');

$system_stats = getSystemStats();
$daily_stats = getDailyStats();
$country_traffic = getCountryTrafficData();
$page_views = getPageViews(30);

$total_country_visitors = array_sum(array_column($country_traffic, 'count'));
if ($total_country_visitors === 0) $total_country_visitors = 1;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Deep Traffic & Geographic Analytics</h1>
        <p class="page-subtitle">Comprehensive visitor metrics, global country traffic heatmap, and daily usage trends.</p>
    </div>
</div>

<div class="kpi-row">
    <?php
    renderKpiCard("Total Recorded Visitors", number_format($system_stats['total_visitors']));
    renderKpiCard("Total Unique Visitors", number_format($system_stats['total_unique']));
    renderKpiCard("Total Page Views", number_format($system_stats['total_pageviews']));
    renderKpiCard("30-Day Avg Daily", number_format($system_stats['avg_daily']));
    ?>
</div>


<!-- Global Country Traffic Map & Country Table -->
<div class="admin-grid-3-2">
    <div class="table-container" style="padding: 24px;">
        <h3 style="font-size: 15px; margin-bottom: 16px; font-weight: 700; color: #fff;">
            <i class="fas fa-earth-americas mr-2" style="color: var(--accent-blue, #60a5fa);"></i>Global Country Traffic Heatmap
        </h3>
        <div id="countryMapContainer" style="height: 360px; width: 100%; border-radius: 8px; border: 1px solid var(--border-subtle); background: #09090b; overflow: hidden;"></div>
    </div>

    <div class="table-container" style="padding: 24px;">
        <h3 style="font-size: 15px; margin-bottom: 16px; font-weight: 700; color: #fff;">
            <i class="fas fa-flag mr-2"></i>Top Traffic by Country
        </h3>
        <div style="max-height: 360px; overflow-y: auto; overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th style="text-align: right;">Visitors</th>
                        <th style="text-align: right;">Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($country_traffic as $ct): 
                        $pct = round(($ct['count'] / $total_country_visitors) * 100, 1);
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 18px;"><?= $ct['flag'] ?></span>
                                <span style="font-weight: 600; color: #fff;"><?= htmlspecialchars($ct['name']) ?></span>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #fff;"><?= number_format($ct['count']) ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                <div style="width: 50px; height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?= min(100, $pct * 2) ?>%; height: 100%; background: linear-gradient(90deg, #6366f1, #06b6d4); border-radius: 3px;"></div>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; color: var(--text-muted); width: 32px; text-align: right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Timeline Line Chart & Most Viewed Pages -->
<div class="admin-grid-2-1">
    <div class="table-container" style="padding: 24px;">
        <h3 style="font-size: 15px; margin-bottom: 16px;"><i class="fas fa-chart-line mr-2"></i>Daily Visitors & Page Views Timeline</h3>
        <div style="height: 320px; position: relative;">
            <canvas id="analyticsChartCanvas"></canvas>
        </div>
    </div>

    <div class="table-container" style="padding: 24px;">
        <h3 style="font-size: 15px; margin-bottom: 16px;"><i class="fas fa-list-ol mr-2"></i>Most Viewed Pages</h3>
        <div style="max-height: 320px; overflow-y: auto; overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th style="text-align: right;">Total Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($page_views)): foreach ($page_views as $pv): ?>
                    <tr>
                        <td><code style="font-family: var(--font-mono); color: #fff;"><?= htmlspecialchars($pv['page']) ?></code></td>
                        <td style="text-align: right; font-weight: 600; color: #fff;"><?= number_format($pv['total_views']) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="2" style="text-align: center; color: var(--text-muted);">No pageview data recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function initAnalyticsCharts() {
        try {
            const dailyData = <?= json_encode($daily_stats) ?>;
            renderDailyChart('analyticsChartCanvas', dailyData);
        } catch (e) { console.error('Daily line chart init error:', e); }

        try {
            const countriesData = <?= json_encode($country_traffic) ?>;
            renderCountryTrafficMap('countryMapContainer', countriesData);
        } catch (e) { console.error('Country map init error:', e); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalyticsCharts);
    } else {
        initAnalyticsCharts();
    }
</script>

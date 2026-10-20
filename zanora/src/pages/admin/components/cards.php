<?php
// components/cards.php - Reusable Monochrome Stat & Metric Cards

function renderStatCard($title, $value, $subtitle, $iconClass, $id = '') {
    ?>
    <div class="stat-card <?= $id ? 'stat-card-' . htmlspecialchars($id) : '' ?>">
        <div class="stat-card-header">
            <span class="stat-title"><?= htmlspecialchars($title) ?></span>
            <div class="stat-icon"><i class="fas <?= htmlspecialchars($iconClass) ?>"></i></div>
        </div>
        <div class="stat-value" <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>><?= htmlspecialchars($value) ?></div>
        <div class="stat-subtitle"><?= $subtitle ?></div>
    </div>
    <?php
}

function renderKpiCard($title, $value, $detail = '') {
    ?>
    <div class="kpi-card">
        <div class="kpi-title"><?= htmlspecialchars($title) ?></div>
        <div class="kpi-value"><?= htmlspecialchars($value) ?></div>
        <?php if ($detail): ?>
            <div class="kpi-detail"><?= $detail ?></div>
        <?php endif; ?>
    </div>
    <?php
}

<?php
// components/alerts.php - Reusable Alert Banners & Toast Notifications

function renderAlertBanner($message, $type = 'success') {
    if (empty($message)) return;
    $icon = $type === 'success' ? 'fa-circle-check' : ($type === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-info');
    ?>
    <div class="alert-banner alert-<?= htmlspecialchars($type) ?>">
        <div class="alert-content">
            <i class="fas <?= $icon ?>"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
        <button class="alert-close-btn" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php
}

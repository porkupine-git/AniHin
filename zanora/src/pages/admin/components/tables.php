<?php
// components/tables.php - Enterprise Data Tables with Search, Filter & Bulk Export

function renderTableToolbar($tableId, $searchPlaceholder = 'Filter records...') {
    ?>
    <div class="table-toolbar">
        <div class="table-search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" class="table-search-input" data-table="<?= htmlspecialchars($tableId) ?>" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" onkeyup="filterTable('<?= htmlspecialchars($tableId) ?>', this.value)">
        </div>
        <div class="table-export-actions">
            <button class="btn-table-action" onclick="exportTableToCSV('<?= htmlspecialchars($tableId) ?>')" title="Export Table Data to CSV">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
            <button class="btn-table-action" onclick="exportTableToJSON('<?= htmlspecialchars($tableId) ?>')" title="Export Table Data to JSON">
                <i class="fas fa-code"></i> JSON
            </button>
            <button class="btn-table-action" onclick="copyTableToClipboard('<?= htmlspecialchars($tableId) ?>')" title="Copy Table to Clipboard">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <?php
}

function renderPagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return;
    ?>
    <div class="pagination-wrapper">
        <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
        <div class="pagination-controls">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $baseUrl ?>&user_page=<?= $currentPage - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php endif; ?>

            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                <a href="<?= $baseUrl ?>&user_page=<?= $i ?>" class="page-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $baseUrl ?>&user_page=<?= $currentPage + 1 ?>" class="page-btn">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

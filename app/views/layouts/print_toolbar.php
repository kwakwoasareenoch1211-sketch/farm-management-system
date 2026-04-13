<?php
/**
 * Reusable Print/Export Toolbar
 * - Print: full page with all rows visible
 * - Export CSV (Excel-compatible with BOM)
 * - Scroll indicator showing row count
 */
$printTitle    = $printTitle    ?? 'Report';
$printSubtitle = $printSubtitle ?? ('Generated: ' . date('d M Y H:i'));
$exportUrl     = $exportUrl     ?? null;
$exportExcelUrl = $exportExcelUrl ?? ($exportUrl ? $exportUrl . '&format=excel' : null);
?>
<?php include BASE_PATH . 'app/views/layouts/print_styles.php'; ?>

<!-- Print Header (hidden on screen, shown when printing) -->
<div class="print-header">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <h3><?= htmlspecialchars($printTitle) ?></h3>
            <p><?= htmlspecialchars($printSubtitle) ?></p>
        </div>
        <div style="text-align:right;font-size:9pt;color:#666;">
            <div>Poultry Farm Management System</div>
            <div>Printed: <?= date('d M Y H:i') ?></div>
        </div>
    </div>
</div>

<!-- Toolbar (hidden when printing) -->
<div class="d-flex gap-2 flex-wrap align-items-center mb-3 d-print-none" id="reportToolbar">
    <button onclick="printFullReport()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print Full Report
    </button>

    <?php if ($exportUrl): ?>
    <a href="<?= htmlspecialchars($exportUrl) ?>&format=csv" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
    </a>
    <a href="<?= htmlspecialchars($exportUrl) ?>&format=excel" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
    </a>
    <?php endif; ?>

    <!-- Row count badge - updated by JS -->
    <span class="badge rounded-pill text-bg-light border ms-1" id="rowCountBadge" style="font-size:12px;">
        <i class="bi bi-table me-1"></i><span id="rowCountText">Loading...</span>
    </span>

    <!-- Scroll indicator -->
    <span class="text-muted small ms-1" id="scrollHint" style="display:none;">
        <i class="bi bi-arrows-expand me-1"></i> Scroll to see all rows
    </span>
</div>

<!-- Scroll-aware table wrapper - applied via JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count all table rows
    const tables = document.querySelectorAll('table');
    let totalRows = 0;
    tables.forEach(t => {
        const bodyRows = t.querySelectorAll('tbody tr');
        bodyRows.forEach(r => {
            // Don't count "no records" placeholder rows
            if (!r.querySelector('td[colspan]') || r.querySelector('td[colspan]').textContent.trim().length < 5) {
                totalRows++;
            }
        });
    });

    const badge = document.getElementById('rowCountText');
    const hint  = document.getElementById('scrollHint');
    if (badge) badge.textContent = totalRows + ' record' + (totalRows !== 1 ? 's' : '');

    // Add max-height scroll to large tables
    document.querySelectorAll('.table-responsive').forEach(wrapper => {
        const rows = wrapper.querySelectorAll('tbody tr').length;
        if (rows > 15) {
            wrapper.style.maxHeight = '500px';
            wrapper.style.overflowY = 'auto';
            wrapper.style.border = '1px solid #e2e8f0';
            wrapper.style.borderRadius = '8px';
            if (hint) hint.style.display = 'inline';

            // Sticky header
            const thead = wrapper.querySelector('thead');
            if (thead) {
                thead.querySelectorAll('th').forEach(th => {
                    th.style.position = 'sticky';
                    th.style.top = '0';
                    th.style.background = '#f8fafc';
                    th.style.zIndex = '10';
                    th.style.boxShadow = '0 1px 0 #e2e8f0';
                });
            }
        }
    });
});

function printFullReport() {
    // Remove max-height before printing so all rows show
    document.querySelectorAll('.table-responsive').forEach(w => {
        w.dataset.origMaxHeight = w.style.maxHeight;
        w.style.maxHeight = 'none';
        w.style.overflow = 'visible';
    });
    window.print();
    // Restore after print
    setTimeout(() => {
        document.querySelectorAll('.table-responsive').forEach(w => {
            if (w.dataset.origMaxHeight) {
                w.style.maxHeight = w.dataset.origMaxHeight;
                w.style.overflow = 'auto';
            }
        });
    }, 1000);
}
</script>

<?php
$printTitle     = $printTitle     ?? 'Report';
$printSubtitle  = $printSubtitle  ?? ('Generated: ' . date('d M Y H:i'));
$exportUrl      = $exportUrl      ?? null;
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

    <span class="badge rounded-pill text-bg-light border ms-1" style="font-size:12px;">
        <i class="bi bi-table me-1"></i><span id="rowCountText">counting...</span>
    </span>
    <span class="text-muted small d-none" id="scrollHint">
        <i class="bi bi-arrows-expand me-1"></i> Scroll to see all rows
    </span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count rows
    let total = 0;
    document.querySelectorAll('table tbody tr').forEach(r => {
        const td = r.querySelector('td[colspan]');
        if (!td || td.textContent.trim().length > 10) total++;
    });
    const el = document.getElementById('rowCountText');
    if (el) el.textContent = total + ' record' + (total !== 1 ? 's' : '');

    // Add scroll to large tables
    document.querySelectorAll('.table-responsive').forEach(w => {
        const rows = w.querySelectorAll('tbody tr').length;
        if (rows > 15) {
            w.style.maxHeight = '480px';
            w.style.overflowY = 'auto';
            w.style.border = '1px solid #e2e8f0';
            w.style.borderRadius = '8px';
            const hint = document.getElementById('scrollHint');
            if (hint) hint.classList.remove('d-none');

            // Sticky header
            w.querySelectorAll('thead th').forEach(th => {
                th.style.position = 'sticky';
                th.style.top = '0';
                th.style.background = '#f8fafc';
                th.style.zIndex = '5';
                th.style.boxShadow = '0 1px 0 #e2e8f0';
            });
        }
    });
});

function printFullReport() {
    // Step 1: Remove ALL scroll/height constraints
    document.querySelectorAll('.table-responsive, .main-content, .app-shell, body, html').forEach(el => {
        el.style.maxHeight = 'none';
        el.style.height = 'auto';
        el.style.overflow = 'visible';
    });

    // Step 2: Show all hidden rows (in case any are display:none)
    document.querySelectorAll('table tbody tr').forEach(r => {
        r.style.display = '';
    });

    // Step 3: Print
    window.print();

    // Step 4: Restore scroll after print
    setTimeout(() => location.reload(), 500);
}
</script>

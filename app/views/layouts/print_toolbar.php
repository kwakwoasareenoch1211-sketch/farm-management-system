<?php
/**
 * Reusable Print/Export Toolbar
 * Usage: include with $printTitle, $printSubtitle, $exportUrl (optional)
 */
$printTitle    = $printTitle    ?? 'Report';
$printSubtitle = $printSubtitle ?? ('Printed: ' . date('d M Y H:i'));
$exportUrl     = $exportUrl     ?? null;
?>
<?php include BASE_PATH . 'app/views/layouts/print_styles.php'; ?>

<!-- Print Header (hidden on screen, shown when printing) -->
<div class="print-header">
    <h3><?= htmlspecialchars($printTitle) ?></h3>
    <p><?= htmlspecialchars($printSubtitle) ?> &nbsp;|&nbsp; Poultry Farm Management System</p>
</div>

<!-- Toolbar (hidden when printing) -->
<div class="d-flex gap-2 flex-wrap align-items-center mb-3 d-print-none">
    <button onclick="printPage()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print Full Report
    </button>
    <?php if ($exportUrl): ?>
    <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
    </a>
    <?php endif; ?>
    <span class="text-muted small ms-2">
        <i class="bi bi-info-circle me-1"></i>
        Print includes all records with full details
    </span>
</div>

<script>
function printPage() {
    // Ensure all table rows are visible before printing
    document.querySelectorAll('table tbody tr').forEach(r => r.style.display = '');
    window.print();
}
</script>

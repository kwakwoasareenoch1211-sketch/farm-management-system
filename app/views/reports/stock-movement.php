<?php
$printTitle    = 'Stock Movement Report';
$printSubtitle = 'Generated: ' . date('d M Y H:i') . ' | Poultry Farm Management System';
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';
?><?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$monthlyBreakdown = $monthlyBreakdown ?? [];

$totalIn = (float)($totals['total_in_qty'] ?? 0);
$totalOut = (float)($totals['total_out_qty'] ?? 0);
$totalInValue = (float)($totals['total_in_value'] ?? 0);
$totalOutValue = (float)($totals['total_out_value'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Stock Movement Report</h2>
            <p class="text-muted mb-0">All stock in and stock out transactions.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Stock In (Units)</div><div class="fs-4 fw-bold text-success"><?= number_format($totalIn, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Stock Out (Units)</div><div class="fs-4 fw-bold text-danger"><?= number_format($totalOut, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Stock In Value</div><div class="fs-4 fw-bold text-success">GHS <?= number_format($totalInValue, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Stock Out Value</div><div class="fs-4 fw-bold text-danger">GHS <?= number_format($totalOutValue, 2) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Movement Records</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Farm</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Reference</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['movement_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($row['movement_type'] ?? '') === 'stock_in' ? 'success' : 'danger' ?>">
                                                    <?= ($row['movement_type'] ?? '') === 'stock_in' ? 'Stock In' : 'Stock Out' ?>
                                                </span>
                                            </td>
                                            <td><?= number_format((float)($row['quantity'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($row['reference_type'] ?? '') ?> <?= htmlspecialchars((string)($row['reference_id'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No movement data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Monthly Breakdown</h5>
                    <?php if (!empty($monthlyBreakdown)): ?>
                        <?php foreach ($monthlyBreakdown as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['month_label'] ?? '') ?></div>
                                <div class="small mt-1 text-success">In: <strong><?= number_format((float)($row['stock_in_qty'] ?? 0), 2) ?></strong></div>
                                <div class="small text-danger">Out: <strong><?= number_format((float)($row['stock_out_qty'] ?? 0), 2) ?></strong></div>
                                <div class="small">Net: <strong><?= number_format((float)($row['net_qty'] ?? 0), 2) ?></strong></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No monthly breakdown available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

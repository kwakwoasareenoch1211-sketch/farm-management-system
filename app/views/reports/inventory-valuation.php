<?php
$printTitle    = 'Inventory Valuation Report';
$printSubtitle = 'Generated: ' . date('d M Y H:i') . ' | Poultry Farm Management System';
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';
?><?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$categorySummary = $categorySummary ?? [];

$totalItems = (int)($totals['total_items'] ?? 0);
$totalValue = (float)($totals['total_stock_value'] ?? 0);
$totalUnits = (float)($totals['total_stock_units'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Inventory Valuation Report</h2>
            <p class="text-muted mb-0">Current monetary value of all inventory on hand.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Items</div><div class="fs-4 fw-bold"><?= number_format($totalItems) ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Units on Hand</div><div class="fs-4 fw-bold"><?= number_format($totalUnits, 2) ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Inventory Value</div><div class="fs-4 fw-bold text-success">GHS <?= number_format($totalValue, 2) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Item Valuation</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Farm</th>
                                    <th>Unit</th>
                                    <th>Qty on Hand</th>
                                    <th>Unit Cost</th>
                                    <th>Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <?php
                                        $stock = (float)($row['current_stock'] ?? 0);
                                        $cost = (float)($row['unit_cost'] ?? 0);
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['unit_of_measure'] ?? '') ?></td>
                                            <td><?= number_format($stock, 2) ?></td>
                                            <td>GHS <?= number_format($cost, 2) ?></td>
                                            <td class="fw-semibold">GHS <?= number_format($stock * $cost, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No inventory data available.</td></tr>
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
                    <h5 class="fw-bold mb-3">Value by Category</h5>
                    <?php if (!empty($categorySummary)): ?>
                        <?php foreach ($categorySummary as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['category'] ?? 'Uncategorized') ?></div>
                                <div class="small mt-1">Items: <strong><?= number_format((int)($row['total_items'] ?? 0)) ?></strong></div>
                                <div class="small">Value: <strong>GHS <?= number_format((float)($row['total_value'] ?? 0), 2) ?></strong></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No category data available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

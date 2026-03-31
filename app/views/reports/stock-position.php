<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$categorySummary = $categorySummary ?? [];

$totalItems = (int)($totals['total_items'] ?? 0);
$totalStockValue = (float)($totals['total_stock_value'] ?? 0);
$totalStockUnits = (float)($totals['total_stock_units'] ?? 0);
$lowStockCount = (int)($totals['low_stock_count'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Stock Position Report</h2>
            <p class="text-muted mb-0">Current stock levels, values, and reorder status for all inventory items.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items" class="btn btn-dark">Manage Items</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Items</div><div class="fs-4 fw-bold"><?= number_format($totalItems) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Stock Units</div><div class="fs-4 fw-bold"><?= number_format($totalStockUnits, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Stock Value</div><div class="fs-4 fw-bold">GHS <?= number_format($totalStockValue, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Low Stock Items</div><div class="fs-4 fw-bold text-danger"><?= number_format($lowStockCount) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">All Inventory Items</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Farm</th>
                                    <th>Unit</th>
                                    <th>Current Stock</th>
                                    <th>Reorder Level</th>
                                    <th>Unit Cost</th>
                                    <th>Stock Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <?php
                                        $stock = (float)($row['current_stock'] ?? 0);
                                        $reorder = (float)($row['reorder_level'] ?? 0);
                                        $isLow = $stock <= $reorder;
                                        ?>
                                        <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                                            <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['unit_of_measure'] ?? '') ?></td>
                                            <td><?= number_format($stock, 2) ?></td>
                                            <td><?= number_format($reorder, 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format($stock * (float)($row['unit_cost'] ?? 0), 2) ?></td>
                                            <td><span class="badge bg-<?= $isLow ? 'danger' : 'success' ?>"><?= $isLow ? 'Low' : 'OK' ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center text-muted py-4">No inventory data available.</td></tr>
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
                    <h5 class="fw-bold mb-3">By Category</h5>
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

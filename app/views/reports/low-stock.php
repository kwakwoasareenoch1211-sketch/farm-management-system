<?php
$reportRows = $reportRows ?? [];
$totalLow = count($reportRows);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Low Stock Report</h2>
            <p class="text-muted mb-0">Items at or below reorder level that require restocking.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/receipts/create" class="btn btn-dark">Receive Stock</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <?php if ($totalLow > 0): ?>
        <div class="alert alert-danger mb-4">
            <strong><?= $totalLow ?> item(s)</strong> are at or below reorder level and need restocking.
        </div>
    <?php else: ?>
        <div class="alert alert-success mb-4">All inventory items are above reorder level.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
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
                            <th>Shortage</th>
                            <th>Unit Cost</th>
                            <th>Restock Cost Est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportRows)): ?>
                            <?php foreach ($reportRows as $row): ?>
                                <?php
                                $stock = (float)($row['current_stock'] ?? 0);
                                $reorder = (float)($row['reorder_level'] ?? 0);
                                $shortage = max(0, $reorder - $stock);
                                $unitCost = (float)($row['unit_cost'] ?? 0);
                                ?>
                                <tr class="table-warning">
                                    <td class="fw-semibold"><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['unit_of_measure'] ?? '') ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($stock, 2) ?></td>
                                    <td><?= number_format($reorder, 2) ?></td>
                                    <td class="text-danger"><?= number_format($shortage, 2) ?></td>
                                    <td>GHS <?= number_format($unitCost, 2) ?></td>
                                    <td>GHS <?= number_format($shortage * $unitCost, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No low stock items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

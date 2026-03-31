<?php $inventoryItems = $inventoryItems ?? []; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Low Stock Items</h2>
            <p class="text-muted mb-0">Items that have reached or fallen below reorder level.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/inventory" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Reorder</th>
                            <th>Unit Cost</th>
                            <th>Farm</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($inventoryItems) && isset($inventoryItems[0]) && is_array($inventoryItems[0])): ?>
                            <?php foreach ($inventoryItems as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['category'] ?? '') ?></td>
                                    <td><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($item['reorder_level'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($item['farm_name'] ?? '') ?></td>
                                    <td><span class="badge bg-danger">Low Stock</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No low-stock items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $inventoryItems = $inventoryItems ?? []; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Inventory Items</h2>
            <p class="text-muted mb-0">Manage all stock-controlled inventory items.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory" class="btn btn-outline-secondary">Dashboard</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items/create" class="btn btn-dark">Add Item</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Reorder</th>
                            <th>Unit Cost</th>
                            <th>Status</th>
                            <th>Farm</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($inventoryItems) && isset($inventoryItems[0]) && is_array($inventoryItems[0])): ?>
                            <?php foreach ($inventoryItems as $item): ?>
                                <?php $itemId = (int)($item['id'] ?? 0); ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['category'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['unit_of_measure'] ?? '') ?></td>
                                    <td><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($item['reorder_level'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php if (($item['status'] ?? '') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($item['status'] ?? 'inactive')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['farm_name'] ?? '') ?></td>
                                    <td>
                                        <?php if ($itemId > 0): ?>
                                            <div class="d-flex gap-2">
                                                <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items/edit?id=<?= $itemId ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items/delete?id=<?= $itemId ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">Delete</a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No inventory items available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
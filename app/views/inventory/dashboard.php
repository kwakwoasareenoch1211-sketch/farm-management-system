<?php
$base = rtrim(BASE_URL, '/');
$totalItems      = $totalItems ?? 0;
$totalStockValue = $totalStockValue ?? 0;
$lowStockItems   = $lowStockItems ?? [];
$categoryBreakdown = $categoryBreakdown ?? [];
$recentMovements = $recentMovements ?? [];
$monthlyIn       = $monthlyIn ?? 0;
$monthlyOut      = $monthlyOut ?? 0;
$items           = $items ?? [];
?>
<style>
.inv-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.inv-kpi{border-radius:16px;padding:20px;background:#fff;border:1px solid #eef2f7;height:100%;}
.inv-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;}
.inv-kpi .val{font-size:1.5rem;font-weight:700;margin-bottom:3px;}
.inv-kpi .sub{font-size:11px;color:#94a3b8;}
.badge-cat{border-radius:8px;padding:4px 10px;font-size:11px;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Inventory Management</span>
        <h2 class="fw-bold mb-1">Inventory Dashboard</h2>
        <p class="text-muted mb-0">Track physical stock items, receipts, and issues. Separate from poultry operational records.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/inventory/items/create" class="btn btn-dark btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Item
        </a>
        <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-outline-success btn-sm">
            <i class="bi bi-box-arrow-in-down me-1"></i> Receive Stock
        </a>
        <a href="<?= $base ?>/inventory/issues/create" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-box-arrow-up me-1"></i> Issue Stock
        </a>
    </div>
</div>

<?php if (!empty($lowStockItems)): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="border-radius:14px;">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong><?= count($lowStockItems) ?> item(s) are at or below reorder level.</strong>
        <a href="<?= $base ?>/inventory/low-stock" class="ms-2 btn btn-sm btn-warning">View Low Stock</a>
    </div>
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="inv-kpi">
            <div class="lbl">Total Items</div>
            <div class="val text-dark"><?= number_format($totalItems) ?></div>
            <div class="sub">Distinct inventory items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="inv-kpi">
            <div class="lbl">Stock Value</div>
            <div class="val text-success">GHS <?= number_format($totalStockValue, 2) ?></div>
            <div class="sub">Current stock × unit cost</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="inv-kpi">
            <div class="lbl">This Month In</div>
            <div class="val text-primary"><?= number_format($monthlyIn, 2) ?></div>
            <div class="sub">Units received this month</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="inv-kpi">
            <div class="lbl">This Month Out</div>
            <div class="val text-warning"><?= number_format($monthlyOut, 2) ?></div>
            <div class="sub">Units issued this month</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Category Breakdown -->
    <div class="col-md-4">
        <div class="inv-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>By Category</h6>
            <?php if (!empty($categoryBreakdown)): ?>
                <?php foreach ($categoryBreakdown as $cat => $data): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:#f8fafc;">
                    <div>
                        <span class="fw-semibold small"><?= htmlspecialchars(ucfirst($cat)) ?></span>
                        <span class="text-muted small ms-2"><?= $data['count'] ?> items</span>
                    </div>
                    <span class="fw-bold small">GHS <?= number_format($data['value'], 0) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small">No items yet. <a href="<?= $base ?>/inventory/items/create">Add items</a></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="inv-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h6>
            <div class="d-grid gap-2">
                <a href="<?= $base ?>/inventory/items" class="btn btn-outline-dark btn-sm text-start">
                    <i class="bi bi-box-seam me-2"></i>View All Items
                </a>
                <a href="<?= $base ?>/inventory/receipts" class="btn btn-outline-success btn-sm text-start">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Stock Receipts
                </a>
                <a href="<?= $base ?>/inventory/issues" class="btn btn-outline-warning btn-sm text-start">
                    <i class="bi bi-box-arrow-up me-2"></i>Stock Issues
                </a>
                <a href="<?= $base ?>/inventory/low-stock" class="btn btn-outline-danger btn-sm text-start">
                    <i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert
                    <?php if (!empty($lowStockItems)): ?>
                        <span class="badge text-bg-danger ms-1"><?= count($lowStockItems) ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= $base ?>/reports/stock-position" class="btn btn-outline-primary btn-sm text-start">
                    <i class="bi bi-clipboard-data me-2"></i>Stock Position Report
                </a>
                <a href="<?= $base ?>/reports/stock-movement" class="btn btn-outline-secondary btn-sm text-start">
                    <i class="bi bi-arrow-left-right me-2"></i>Movement Report
                </a>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="col-md-4">
        <div class="inv-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Low Stock Items</h6>
            <?php if (!empty($lowStockItems)): ?>
                <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:#fff5f5;">
                    <div>
                        <div class="fw-semibold small"><?= htmlspecialchars($item['item_name']) ?></div>
                        <div class="text-muted" style="font-size:11px;">Reorder: <?= $item['reorder_level'] ?> <?= $item['unit_of_measure'] ?? '' ?></div>
                    </div>
                    <span class="badge text-bg-danger"><?= number_format((float)$item['current_stock'], 1) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (count($lowStockItems) > 5): ?>
                    <a href="<?= $base ?>/inventory/low-stock" class="small text-danger">+<?= count($lowStockItems) - 5 ?> more</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-3">
                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    <p class="text-muted small mt-2">All items are well stocked</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- All Items Table -->
<div class="inv-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>All Inventory Items</h6>
        <a href="<?= $base ?>/inventory/items/create" class="btn btn-sm btn-dark">
            <i class="bi bi-plus-circle me-1"></i> Add Item
        </a>
    </div>

    <?php if (!empty($items)): ?>
    <div class="table-responsive">
        <table class="table align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Stock Value</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                    $stock = (float)($item['current_stock'] ?? 0);
                    $reorder = (float)($item['reorder_level'] ?? 0);
                    $value = $stock * (float)($item['unit_cost'] ?? 0);
                    $isLow = $reorder > 0 && $stock <= $reorder;
                    $statusBadge = $isLow ? 'danger' : ($stock == 0 ? 'secondary' : 'success');
                    $statusLabel = $isLow ? 'Low' : ($stock == 0 ? 'Out' : 'OK');
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars(ucfirst($item['category'] ?? 'Other')) ?></span></td>
                    <td class="<?= $isLow ? 'text-danger fw-bold' : '' ?>"><?= number_format($stock, 2) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($item['unit_of_measure'] ?? '') ?></td>
                    <td>GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?></td>
                    <td class="fw-semibold">GHS <?= number_format($value, 2) ?></td>
                    <td class="text-muted small"><?= number_format($reorder, 0) ?></td>
                    <td><span class="badge text-bg-<?= $statusBadge ?>"><?= $statusLabel ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= $base ?>/inventory/items/edit?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="<?= $base ?>/inventory/items/delete?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">Del</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-boxes fs-1 mb-3 d-block"></i>
        <p>No inventory items yet.</p>
        <a href="<?= $base ?>/inventory/items/create" class="btn btn-dark">Add First Item</a>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Movements -->
<?php if (!empty($recentMovements)): ?>
<div class="inv-card p-4 mt-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-arrow-left-right me-2"></i>Recent Stock Movements</h6>
    <div class="table-responsive">
        <table class="table align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentMovements as $mv): ?>
                <tr>
                    <td class="small"><?= htmlspecialchars($mv['movement_date'] ?? '') ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($mv['item_name'] ?? 'Unknown') ?></td>
                    <td>
                        <span class="badge text-bg-<?= ($mv['movement_type'] ?? '') === 'receipt' ? 'success' : 'warning' ?>">
                            <?= ucfirst($mv['movement_type'] ?? '') ?>
                        </span>
                    </td>
                    <td><?= number_format((float)($mv['quantity'] ?? 0), 2) ?> <?= htmlspecialchars($mv['unit_of_measure'] ?? '') ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($mv['reference_no'] ?? '-') ?></td>
                    <td class="small text-muted"><?= htmlspecialchars(substr($mv['notes'] ?? '', 0, 40)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

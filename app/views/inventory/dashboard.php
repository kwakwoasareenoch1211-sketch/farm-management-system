<?php
$totals = $totals ?? [];
$currentMonthMovements = $currentMonthMovements ?? [];
$monthlyMovementBreakdown = $monthlyMovementBreakdown ?? [];
$recentInventoryActivities = $recentInventoryActivities ?? [];
$categorySummary = $categorySummary ?? [];
$farmSummary = $farmSummary ?? [];
$lowStockItems = $lowStockItems ?? [];
$topValuedItems = $topValuedItems ?? [];
$criticalItems = $criticalItems ?? [];
$alerts = $alerts ?? [];

$totalItems = (int)($totals['total_items'] ?? 0);
$totalStockUnits = (float)($totals['total_stock_units'] ?? 0);
$totalStockValue = (float)($totals['total_stock_value'] ?? 0);
$lowStockCount = (int)($totals['low_stock_count'] ?? 0);
$activeItems = (int)($totals['active_items'] ?? 0);
$inactiveItems = (int)($totals['inactive_items'] ?? 0);

$stockInQty = (float)($currentMonthMovements['stock_in_qty'] ?? 0);
$stockOutQty = (float)($currentMonthMovements['stock_out_qty'] ?? 0);
$stockInValue = (float)($currentMonthMovements['stock_in_value'] ?? 0);
$stockOutValue = (float)($currentMonthMovements['stock_out_value'] ?? 0);
?>

<style>
    .inventory-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        min-height: 100%;
        border-radius: 28px;
        padding: 1rem;
    }

    .inventory-hero {
        border: 0;
        border-radius: 28px;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,0.16), transparent 30%),
            linear-gradient(135deg, #0f172a 0%, #1e293b 42%, #0f766e 100%);
        color: #fff;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.22);
    }

    .inventory-card {
        border: 0;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }

    .inventory-stat {
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(226, 232, 240, 0.85);
        padding: 1.1rem 1rem;
        height: 100%;
    }

    .inventory-stat .label {
        font-size: 0.84rem;
        color: #64748b;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .inventory-stat .value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
    }

    .inventory-stat .meta {
        color: #94a3b8;
        font-size: 0.8rem;
        margin-top: 0.35rem;
    }

    .inventory-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.95rem 1rem;
    }

    .inventory-section-title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0;
    }

    .alert-box {
        border-radius: 18px;
        padding: 0.95rem 1rem;
        border: 1px solid transparent;
        height: 100%;
    }

    .alert-box.warning {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .alert-box.danger {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .alert-box.secondary {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.28rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .mini-badge.green {
        background: #dcfce7;
        color: #166534;
    }

    .mini-badge.red {
        background: #fee2e2;
        color: #991b1b;
    }

    .mini-badge.blue {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .mini-badge.amber {
        background: #fef3c7;
        color: #92400e;
    }

    .table > :not(caption) > * > * {
        padding: 0.9rem 0.8rem;
        vertical-align: middle;
    }

    .table thead th {
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 700;
        border-bottom-width: 1px;
    }

    .metric-pill {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 18px;
        padding: 0.95rem 1rem;
        text-align: center;
        height: 100%;
    }

    .metric-pill .metric-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .metric-pill .metric-label {
        color: rgba(255,255,255,0.8);
        font-size: 0.84rem;
        margin-top: 0.25rem;
    }
</style>

<div class="inventory-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <span class="badge rounded-pill text-bg-dark px-3 py-2 mb-2">Inventory Control</span>
            <h2 class="fw-bold mb-1">Inventory Dashboard</h2>
            <p class="text-muted mb-0">Track stock balance, receipts, issues, low-stock alerts, and inventory valuation.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items/create" class="btn btn-dark"><i class="bi bi-plus-circle me-1"></i>Add Item</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/receipts/create" class="btn btn-success"><i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/issues/create" class="btn btn-danger"><i class="bi bi-box-arrow-up me-1"></i>Issue Stock</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items" class="btn btn-outline-secondary"><i class="bi bi-list-ul me-1"></i>All Items</a>
        </div>
    </div>

    <div class="inventory-hero p-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-5">
                <div class="mb-2 text-uppercase small fw-semibold text-white-50">Operations overview</div>
                <h3 class="fw-bold mb-2">Real-time stock visibility for better farm planning</h3>
                <p class="mb-0 text-white-50">
                    Review inventory exposure, identify urgent replenishment gaps, and compare inflow versus outflow activity for the current month.
                </p>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="metric-pill">
                            <div class="metric-value"><?= number_format($totalItems) ?></div>
                            <div class="metric-label">Tracked Items</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="metric-pill">
                            <div class="metric-value"><?= number_format($totalStockUnits, 2) ?></div>
                            <div class="metric-label">Stock Units</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="metric-pill">
                            <div class="metric-value">GHS <?= number_format($totalStockValue, 2) ?></div>
                            <div class="metric-label">Inventory Value</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="metric-pill">
                            <div class="metric-value"><?= number_format($lowStockCount) ?></div>
                            <div class="metric-label">Low Stock</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($alerts)): ?>
        <div class="row g-3 mb-4">
            <?php foreach ($alerts as $alert): ?>
                <div class="col-lg-4">
                    <div class="alert-box <?= htmlspecialchars($alert['type'] ?? 'secondary') ?>">
                        <div class="fw-bold mb-1"><?= htmlspecialchars($alert['title'] ?? '') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($alert['message'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Active Items</div>
                <div class="value text-success"><?= number_format($activeItems) ?></div>
                <div class="meta">Ready for use</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Inactive Items</div>
                <div class="value text-secondary"><?= number_format($inactiveItems) ?></div>
                <div class="meta">Archived or disabled</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Month Stock In</div>
                <div class="value text-primary"><?= number_format($stockInQty, 2) ?></div>
                <div class="meta">GHS <?= number_format($stockInValue, 2) ?></div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Month Stock Out</div>
                <div class="value text-danger"><?= number_format($stockOutQty, 2) ?></div>
                <div class="meta">GHS <?= number_format($stockOutValue, 2) ?></div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Net Movement</div>
                <div class="value"><?= number_format($stockInQty - $stockOutQty, 2) ?></div>
                <div class="meta">Units this month</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="inventory-stat">
                <div class="label">Net Value</div>
                <div class="value">GHS <?= number_format($stockInValue - $stockOutValue, 2) ?></div>
                <div class="meta">Monthly balance</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Critical Reorder Items</h5>
                    <span class="mini-badge amber"><?= number_format(count($criticalItems)) ?></span>
                </div>

                <?php if (!empty($criticalItems)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($criticalItems as $item): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($item['farm_name'] ?? '-') ?></div>
                                <div class="small">
                                    Stock:
                                    <strong><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></strong>
                                    |
                                    Reorder:
                                    <strong><?= number_format((float)($item['reorder_level'] ?? 0), 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/receipts/create" class="btn btn-warning btn-sm w-100 mt-3">
                        <i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock Now
                    </a>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No urgent reorder pressure detected.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Top Valued Items</h5>
                    <span class="mini-badge green">High Value</span>
                </div>

                <?php if (!empty($topValuedItems)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($topValuedItems as $item): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($item['farm_name'] ?? '-') ?></div>
                                <div class="small">
                                    Value:
                                    <strong>GHS <?= number_format((float)($item['stock_value'] ?? 0), 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/reports/inventory-valuation" class="btn btn-outline-success btn-sm w-100 mt-3">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>View Full Report
                    </a>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No item valuation data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Low Stock Watchlist</h5>
                    <span class="mini-badge red"><?= number_format($lowStockCount) ?></span>
                </div>

                <?php if (!empty($lowStockItems)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($item['farm_name'] ?? '-') ?></div>
                                <div class="small">
                                    Current:
                                    <strong><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></strong>
                                    |
                                    Threshold:
                                    <strong><?= number_format((float)($item['reorder_level'] ?? 0), 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/low-stock" class="btn btn-outline-danger btn-sm w-100 mt-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>View All Low Stock
                    </a>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No low stock alerts right now.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Inventory Actions -->
    <div class="inventory-card p-4 mb-4">
        <h5 class="inventory-section-title mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Inventory Actions</h5>
        <div class="row g-3">
            <?php
            $invActions = [
                ['icon'=>'bi-box-seam','color'=>'#3b82f6','label'=>'All Items','desc'=>'View and manage inventory items','url'=>rtrim(BASE_URL, '/').'/inventory/items'],
                ['icon'=>'bi-box-arrow-in-down','color'=>'#22c55e','label'=>'Receive Stock','desc'=>'Record incoming inventory','url'=>rtrim(BASE_URL, '/').'/inventory/receipts'],
                ['icon'=>'bi-box-arrow-up','color'=>'#ef4444','label'=>'Issue Stock','desc'=>'Record outgoing inventory','url'=>rtrim(BASE_URL, '/').'/inventory/issues'],
                ['icon'=>'bi-exclamation-triangle','color'=>'#f59e0b','label'=>'Low Stock','desc'=>'Items below reorder level','url'=>rtrim(BASE_URL, '/').'/inventory/low-stock'],
                ['icon'=>'bi-file-earmark-bar-graph','color'=>'#8b5cf6','label'=>'Stock Position','desc'=>'Current inventory status','url'=>rtrim(BASE_URL, '/').'/reports/stock-position'],
                ['icon'=>'bi-arrow-left-right','color'=>'#06b6d4','label'=>'Stock Movement','desc'=>'Transaction history','url'=>rtrim(BASE_URL, '/').'/reports/stock-movement'],
                ['icon'=>'bi-currency-dollar','color'=>'#10b981','label'=>'Valuation','desc'=>'Inventory value report','url'=>rtrim(BASE_URL, '/').'/reports/inventory-valuation'],
                ['icon'=>'bi-plus-circle','color'=>'#64748b','label'=>'Add New Item','desc'=>'Create inventory item','url'=>rtrim(BASE_URL, '/').'/inventory/items/create'],
            ];
            foreach ($invActions as $a):
            ?>
                <div class="col-6 col-md-3">
                    <a href="<?= htmlspecialchars($a['url']) ?>" class="action-tile" style="border-radius:16px;padding:20px;border:2px solid #e2e8f0;background:#fff;text-align:center;text-decoration:none;color:#1e293b;display:block;transition:all .2s;">
                        <span style="color:<?= $a['color'] ?>;font-size:2.5rem;display:block;margin-bottom:10px;"><i class="bi <?= $a['icon'] ?>"></i></span>
                        <div class="fw-semibold small mt-2"><?= htmlspecialchars($a['label']) ?></div>
                        <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($a['desc']) ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Category Summary</h5>
                    <span class="mini-badge blue">By Category</span>
                </div>

                <?php if (!empty($categorySummary)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($categorySummary as $row): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars(ucfirst($row['category'] ?? 'general')) ?></div>
                                <div class="small text-muted">
                                    Items: <?= number_format((int)($row['total_items'] ?? 0)) ?> |
                                    Stock: <?= number_format((float)($row['total_stock'] ?? 0), 2) ?> |
                                    Value: GHS <?= number_format((float)($row['total_value'] ?? 0), 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No category analytics available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Farm Summary</h5>
                    <span class="mini-badge blue">By Farm</span>
                </div>

                <?php if (!empty($farmSummary)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($farmSummary as $row): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars($row['farm_name'] ?? '-') ?></div>
                                <div class="small text-muted">
                                    Items: <?= number_format((int)($row['total_items'] ?? 0)) ?> |
                                    Stock: <?= number_format((float)($row['total_stock'] ?? 0), 2) ?> |
                                    Value: GHS <?= number_format((float)($row['total_value'] ?? 0), 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No farm summary available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">Recent Inventory Activities</h5>
                    <span class="mini-badge blue">Latest</span>
                </div>

                <?php if (!empty($recentInventoryActivities)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Movement</th>
                                    <th>Qty</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInventoryActivities as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('M d, Y', strtotime($row['activity_date'] ?? ''))) ?></td>
                                        <td><?= htmlspecialchars($row['item_name'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $actType = $row['activity_type'] ?? '';
                                            $badge = 'secondary';
                                            $label = ucfirst(str_replace('_', ' ', $actType));
                                            if ($actType === 'stock_movement') { $badge = 'blue'; $label = 'Stock'; }
                                            elseif ($actType === 'feed_usage') { $badge = 'amber'; $label = 'Feed'; }
                                            elseif ($actType === 'medication_usage') { $badge = 'red'; $label = 'Medication'; }
                                            elseif ($actType === 'vaccination_usage') { $badge = 'green'; $label = 'Vaccination'; }
                                            ?>
                                            <span class="mini-badge <?= $badge ?>"><?= htmlspecialchars($label) ?></span>
                                        </td>
                                        <td>
                                            <?php if (($row['movement_type'] ?? '') === 'receipt'): ?>
                                                <span class="mini-badge green">Receipt</span>
                                            <?php elseif (($row['movement_type'] ?? '') === 'issue'): ?>
                                                <span class="mini-badge red">Issue</span>
                                            <?php else: ?>
                                                <span class="mini-badge amber"><?= htmlspecialchars(ucfirst($row['movement_type'] ?? '')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format((float)($row['quantity'] ?? 0), 2) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($row['reference_no'] ?? $row['notes'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No inventory movement activity has been recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="inventory-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="inventory-section-title">6-Month Movement Snapshot</h5>
                    <span class="mini-badge blue">Trend</span>
                </div>

                <?php if (!empty($monthlyMovementBreakdown)): ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($monthlyMovementBreakdown as $row): ?>
                            <div class="inventory-soft">
                                <div class="fw-semibold"><?= htmlspecialchars($row['month_label'] ?? '') ?></div>
                                <div class="small text-muted">
                                    In: <?= number_format((float)($row['stock_in_qty'] ?? 0), 2) ?> |
                                    Out: <?= number_format((float)($row['stock_out_qty'] ?? 0), 2) ?>
                                </div>
                                <div class="small text-muted">
                                    Net Qty: <?= number_format((float)($row['net_qty'] ?? 0), 2) ?> |
                                    Net Value: GHS <?= number_format((float)($row['net_value'] ?? 0), 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="inventory-soft text-muted">No monthly movement trend available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
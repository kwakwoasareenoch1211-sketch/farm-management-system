<?php
$totalBirds       = (float)($summary['total_birds']    ?? 0);
$activeBatches    = (int)($summary['active_batches']   ?? 0);
$totalBatches     = (int)($summary['total_batches']    ?? 0);
$totalEggs        = (float)($summary['total_eggs']     ?? 0);
$totalMortality   = (float)($summary['total_mortality']?? 0);
$lowStockCount    = (int)($summary['low_stock_count']  ?? 0);
$lowStockItems    = $lowStockItems ?? [];
$totalFeedKg      = (float)($summary['total_feed_used_kg'] ?? 0);

// Inventory data
$inventoryTotals = $inventoryTotals ?? [];
$currentMonthMovements = $currentMonthMovements ?? [];
$recentInventoryActivities = $recentInventoryActivities ?? [];
$categorySummary = $categorySummary ?? [];
$topValuedItems = $topValuedItems ?? [];
$criticalItems = $criticalItems ?? [];

// Separated by category for business logic
$feedItems = $feedItems ?? [];
$medicationItems = $medicationItems ?? [];
$otherItems = $otherItems ?? [];
$feedTotals = $feedTotals ?? [];

$totalStockValue = (float)($inventoryTotals['total_stock_value'] ?? 0);
$stockInQty = (float)($currentMonthMovements['stock_in_qty'] ?? 0);
$stockOutQty = (float)($currentMonthMovements['stock_out_qty'] ?? 0);
$totalFeedRecords = (int)($feedTotals['total_records'] ?? 0);
$totalFeedCost = (float)($feedTotals['total_feed_cost'] ?? 0);

$avgFcr           = (float)($extraMetrics['average_fcr']         ?? 0);
$avgWeightKg      = (float)($extraMetrics['average_weight_kg']   ?? 0);
$vacOverdue       = (int)($extraMetrics['vaccination_overdue']   ?? 0);
$vacDueSoon       = (int)($extraMetrics['vaccination_due_soon']  ?? 0);
$medRecords       = (int)($extraMetrics['medication_records']    ?? 0);
$medCost          = (float)($extraMetrics['medication_cost']     ?? 0);

$mortalityRate    = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;
$eggRate          = $totalBirds > 0 ? ($totalEggs / max(1, $activeBatches * 30)) * 100 : 0;

$healthStatus     = $mortalityRate >= 5 ? 'Critical' : ($mortalityRate >= 2 ? 'Caution' : 'Good');
$healthClass      = $mortalityRate >= 5 ? 'danger'   : ($mortalityRate >= 2 ? 'warning' : 'success');

$base = rtrim(BASE_URL, '/');
?>
<style>
.pou-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.pou-hero{border-radius:22px;background:linear-gradient(135deg,#14532d 0%,#166534 50%,#15803d 100%);color:#fff;padding:28px;}
.pou-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;box-shadow:0 4px 16px rgba(15,23,42,.05);}
.pou-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;}
.pou-kpi .val{font-size:1.4rem;font-weight:700;margin-bottom:3px;}
.pou-kpi .sub{font-size:11px;color:#94a3b8;}
.pou-soft{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px 16px;}
.pou-alert{border-radius:14px;padding:14px 16px;margin-bottom:10px;}
.action-tile{border-radius:16px;padding:20px;border:2px solid #e2e8f0;background:#fff;text-align:center;text-decoration:none;color:#1e293b;display:block;transition:all .2s;}
.action-tile:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(15,23,42,.15);border-color:#3b82f6;color:#1e293b;}
.action-tile .tile-icon{font-size:2.5rem;margin-bottom:10px;display:block;}
.status-panel{border-radius:18px;padding:22px;color:#fff;height:100%;}
</style>

<!-- Header -->
<?php if (!empty($lowStockItems)): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-3" style="border-radius:16px;">
    <i class="bi bi-exclamation-triangle-fill mt-1 fs-5"></i>
    <div>
        <strong><?= count($lowStockItems) ?> inventory item(s) are low on stock.</strong>
        Feed and medication recording may fail if stock runs out.
        <div class="mt-2">
            <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-warning btn-sm me-2"><i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock</a>
            <a href="<?= $base ?>/inventory/low-stock" class="btn btn-outline-warning btn-sm">View Low Stock</a>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-success mb-2 px-3 py-2">Unified Operations Center</span>
        <h2 class="fw-bold mb-1">Poultry & Inventory Operations</h2>
        <p class="text-muted mb-0">Complete farm management: batches, eggs, feed, health, medication, and inventory - all in one place.</p>
    </div>

<?php
// Owner Stats Section
$ownerStats = $ownerStats ?? [];
if (!empty($ownerStats)):
?>
<!-- OWNER BREAKDOWN -->
<div class="row g-4 mb-4 w-100">
    <?php foreach ($ownerStats as $owner):
        $totalCost = $owner['feed_cost'] + $owner['med_cost'] + $owner['vac_cost'];
        $mortalityPct = $owner['birds'] > 0 ? round(($owner['mortality'] / ($owner['birds'] + $owner['mortality'])) * 100, 1) : 0;
    ?>
    <div class="col-md-6">
        <div class="pou-card p-4 h-100" style="border-left:4px solid <?= $owner['color'] ?>;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:44px;height:44px;background:<?= $owner['color'] ?>;font-size:18px;flex-shrink:0;">
                    <?= strtoupper(substr($owner['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= htmlspecialchars($owner['name']) ?></div>
                    <div class="text-muted small">@<?= htmlspecialchars($owner['username']) ?></div>
                </div>
                <span class="badge ms-auto rounded-pill px-3" style="background:<?= $owner['color'] ?>20;color:<?= $owner['color'] ?>;">
                    <?= $owner['batches'] ?> batch<?= $owner['batches'] != 1 ? 'es' : '' ?>
                </span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4 text-center">
                    <div class="pou-soft">
                        <div class="fw-bold fs-5" style="color:<?= $owner['color'] ?>"><?= number_format($owner['birds']) ?></div>
                        <div class="small text-muted">Live Birds</div>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="pou-soft">
                        <div class="fw-bold fs-5 text-warning"><?= number_format($owner['eggs']) ?></div>
                        <div class="small text-muted">Eggs</div>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="pou-soft">
                        <div class="fw-bold fs-5 <?= $mortalityPct > 5 ? 'text-danger' : 'text-success' ?>"><?= $mortalityPct ?>%</div>
                        <div class="small text-muted">Mortality</div>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-4 text-center">
                    <div class="small text-muted">Feed Cost</div>
                    <div class="fw-semibold small">GHS <?= number_format($owner['feed_cost'], 0) ?></div>
                </div>
                <div class="col-4 text-center" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                    <div class="small text-muted">Med Cost</div>
                    <div class="fw-semibold small">GHS <?= number_format($owner['med_cost'], 0) ?></div>
                </div>
                <div class="col-4 text-center">
                    <div class="small text-muted">Total Cost</div>
                    <div class="fw-semibold small text-danger">GHS <?= number_format($totalCost, 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/batches/create"         class="btn btn-dark btn-sm"><i class="bi bi-plus-circle me-1"></i>New Batch</a>
        <a href="<?= $base ?>/egg-production/create"  class="btn btn-warning btn-sm"><i class="bi bi-egg me-1"></i>Log Eggs</a>
        <a href="<?= $base ?>/feed/create"            class="btn btn-success btn-sm"><i class="bi bi-basket2 me-1"></i>Record Feed</a>
        <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-info btn-sm"><i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock</a>
    </div>
</div>

<!-- Hero -->
<div class="pou-hero mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <h5 class="fw-bold mb-1">Poultry Performance Snapshot</h5>
            <p class="text-white-50 small mb-0">Live flock status, production, health, and feeding overview.</p>
            <div class="mt-3">
                <span class="badge bg-<?= $healthClass ?> px-3 py-2 fs-6">Flock Health: <?= $healthStatus ?></span>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3"><div class="fs-3 fw-bold"><?= number_format($totalBirds) ?></div><div class="small text-white-50">Total Birds</div></div>
                <div class="col-6 col-md-3"><div class="fs-3 fw-bold"><?= number_format($activeBatches) ?></div><div class="small text-white-50">Active Batches</div></div>
                <div class="col-6 col-md-3"><div class="fs-3 fw-bold"><?= number_format($totalEggs) ?></div><div class="small text-white-50">Total Eggs</div></div>
                <div class="col-6 col-md-3"><div class="fs-3 fw-bold text-<?= $mortalityRate >= 5 ? 'warning' : 'white' ?>"><?= number_format($totalMortality) ?></div><div class="small text-white-50">Total Mortality</div></div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Grid -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #22c55e;"><div class="lbl">Total Birds</div><div class="val"><?= number_format($totalBirds) ?></div><div class="sub">Live birds across all batches</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #3b82f6;"><div class="lbl">Active Batches</div><div class="val"><?= number_format($activeBatches) ?></div><div class="sub"><?= number_format($totalBatches) ?> total batches</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #f59e0b;"><div class="lbl">Total Eggs</div><div class="val"><?= number_format($totalEggs) ?></div><div class="sub">All recorded egg production</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #ef4444;"><div class="lbl">Total Mortality</div><div class="val text-danger"><?= number_format($totalMortality) ?></div><div class="sub">Mortality rate: <?= number_format($mortalityRate, 2) ?>%</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #06b6d4;"><div class="lbl">Feed Used</div><div class="val"><?= number_format($totalFeedKg, 2) ?> kg</div><div class="sub">Total feed consumption</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #8b5cf6;"><div class="lbl">Avg Weight</div><div class="val"><?= number_format($avgWeightKg, 3) ?> kg</div><div class="sub">Latest average bird weight</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid #10b981;"><div class="lbl">Stock Value</div><div class="val">GHS <?= number_format($totalStockValue, 2) ?></div><div class="sub">Total inventory value</div></div></div>
    <div class="col-6 col-md-3"><div class="pou-kpi" style="border-left:4px solid <?= $lowStockCount > 0 ? '#ef4444' : '#22c55e' ?>;"><div class="lbl">Low Stock Items</div><div class="val <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($lowStockCount) ?></div><div class="sub">Items at/below reorder level</div></div></div>
</div>

<!-- Health + Vaccination + Medication -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="status-panel" style="background:linear-gradient(135deg,<?= $healthClass === 'success' ? '#16a34a,#15803d' : ($healthClass === 'warning' ? '#f59e0b,#d97706' : '#ef4444,#dc2626') ?>);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="small text-white-50">Flock Health Status</div>
                    <div class="fs-2 fw-bold"><?= $healthStatus ?></div>
                </div>
                <i class="bi bi-shield-check fs-2"></i>
            </div>
            <div class="text-white-50 small">Mortality rate: <?= number_format($mortalityRate, 2) ?>% of total birds.</div>
            <div class="mt-3">
                <a href="<?= $base ?>/mortality" class="btn btn-light btn-sm w-100">View Mortality Records</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-warning me-2"></i>Vaccination Status</h6>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Overdue</span>
                <span class="fw-bold text-danger"><?= number_format($vacOverdue) ?></span>
            </div>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Due Soon</span>
                <span class="fw-bold text-warning"><?= number_format($vacDueSoon) ?></span>
            </div>
            <?php if ($vacOverdue > 0): ?>
                <div class="alert alert-danger py-2 small mt-2 mb-0"><?= $vacOverdue ?> vaccination(s) are overdue. Act now.</div>
            <?php elseif ($vacDueSoon > 0): ?>
                <div class="alert alert-warning py-2 small mt-2 mb-0"><?= $vacDueSoon ?> vaccination(s) due soon.</div>
            <?php else: ?>
                <div class="alert alert-success py-2 small mt-2 mb-0">All vaccinations are up to date.</div>
            <?php endif; ?>
            <a href="<?= $base ?>/vaccination" class="btn btn-outline-warning btn-sm w-100 mt-2">Manage Vaccinations</a>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-capsule-pill text-primary me-2"></i>Medication Activity</h6>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Total Records</span>
                <span class="fw-bold"><?= number_format($medRecords) ?></span>
            </div>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Total Cost</span>
                <span class="fw-bold text-danger">GHS <?= number_format($medCost, 2) ?></span>
            </div>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Low Stock Items</span>
                <span class="fw-bold <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($lowStockCount) ?></span>
            </div>
            <a href="<?= $base ?>/medication" class="btn btn-outline-primary btn-sm w-100 mt-2">Manage Medication</a>
        </div>
    </div>
</div>

<!-- Inventory Stock Management -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-success me-2"></i>Stock Movement (This Month)</h6>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Stock In</span>
                <span class="fw-bold text-success"><?= number_format($stockInQty, 2) ?> units</span>
            </div>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Stock Out</span>
                <span class="fw-bold text-danger"><?= number_format($stockOutQty, 2) ?> units</span>
            </div>
            <div class="pou-soft mb-2 d-flex justify-content-between">
                <span class="small text-muted">Net Movement</span>
                <span class="fw-bold"><?= number_format($stockInQty - $stockOutQty, 2) ?> units</span>
            </div>
            <a href="<?= $base ?>/reports/stock-movement" class="btn btn-outline-success btn-sm w-100 mt-2">View Stock Movement</a>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Critical Reorder Items</h6>
            <?php if (!empty($criticalItems)): ?>
                <div class="d-grid gap-2">
                    <?php foreach (array_slice($criticalItems, 0, 3) as $item): ?>
                        <div class="pou-soft">
                            <div class="fw-semibold small"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                            <div class="small text-muted">
                                Stock: <?= number_format((float)($item['current_stock'] ?? 0), 2) ?> | 
                                Reorder: <?= number_format((float)($item['reorder_level'] ?? 0), 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-warning btn-sm w-100 mt-2">Receive Stock Now</a>
            <?php else: ?>
                <div class="alert alert-success py-2 small mb-0">All stock levels are healthy.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-currency-dollar text-info me-2"></i>Top Valued Items</h6>
            <?php if (!empty($topValuedItems)): ?>
                <div class="d-grid gap-2">
                    <?php foreach (array_slice($topValuedItems, 0, 3) as $item): ?>
                        <div class="pou-soft">
                            <div class="fw-semibold small"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                            <div class="small text-muted">
                                Value: GHS <?= number_format((float)($item['stock_value'] ?? 0), 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= $base ?>/reports/inventory-valuation" class="btn btn-outline-info btn-sm w-100 mt-2">Full Valuation Report</a>
            <?php else: ?>
                <div class="pou-soft text-muted small">No valuation data available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pou-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Operations</h6>
    <div class="row g-3">
        <?php
        $actions = [
            ['icon'=>'bi-egg-fried','color'=>'#f59e0b','label'=>'Batches',       'desc'=>'Manage all poultry batches',         'url'=>$base.'/batches'],
            ['icon'=>'bi-basket2-fill','color'=>'#22c55e','label'=>'Feed Records',   'desc'=>'Record and track feed usage',         'url'=>$base.'/feed'],
            ['icon'=>'bi-egg','color'=>'#eab308','label'=>'Egg Production', 'desc'=>'Daily egg collection records',        'url'=>$base.'/egg-production'],
            ['icon'=>'bi-heart-pulse','color'=>'#ef4444','label'=>'Mortality',      'desc'=>'Log and review bird losses',          'url'=>$base.'/mortality'],
            ['icon'=>'bi-shield-check','color'=>'#3b82f6','label'=>'Vaccination',    'desc'=>'Vaccination schedules and records',   'url'=>$base.'/vaccination'],
            ['icon'=>'bi-capsule-pill','color'=>'#8b5cf6','label'=>'Medication',     'desc'=>'Treatment records and costs',         'url'=>$base.'/medication'],
            ['icon'=>'bi-box-seam','color'=>'#10b981','label'=>'Inventory Items',      'desc'=>'Manage all inventory items',      'url'=>$base.'/inventory/items'],
            ['icon'=>'bi-box-arrow-in-down','color'=>'#06b6d4','label'=>'Receive Stock','desc'=>'Record incoming inventory',      'url'=>$base.'/inventory/receipts/create'],
        ];
        foreach ($actions as $a):
        ?>
            <div class="col-6 col-md-3">
                <a href="<?= htmlspecialchars($a['url']) ?>" class="action-tile">
                    <span class="tile-icon" style="color:<?= $a['color'] ?>;"><i class="bi <?= $a['icon'] ?>"></i></span>
                    <div class="fw-semibold small mt-2"><?= htmlspecialchars($a['label']) ?></div>
                    <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($a['desc']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Inventory Activity -->
<?php if (!empty($recentInventoryActivities)): ?>
<div class="pou-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Recent Inventory Activity</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentInventoryActivities as $row): ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars(date('M d', strtotime($row['activity_date'] ?? ''))) ?></td>
                        <td class="small fw-semibold"><?= htmlspecialchars($row['item_name'] ?? '-') ?></td>
                        <td>
                            <?php
                            $type = $row['movement_type'] ?? '';
                            $badge = $type === 'receipt' ? 'success' : 'danger';
                            ?>
                            <span class="badge bg-<?= $badge ?> badge-sm"><?= htmlspecialchars(ucfirst($type)) ?></span>
                        </td>
                        <td class="small"><?= number_format((float)($row['quantity'] ?? 0), 2) ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($row['reference_no'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="<?= $base ?>/reports/stock-movement" class="btn btn-outline-primary btn-sm w-100">View All Movements</a>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="pou-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Poultry Actions</h6>
    <div class="row g-3">
        <?php
        $actions = [
            ['icon'=>'bi-egg-fried','color'=>'#f59e0b','label'=>'Batches',       'desc'=>'Manage all poultry batches',         'url'=>$base.'/batches'],
            ['icon'=>'bi-basket2-fill','color'=>'#22c55e','label'=>'Feed Records',   'desc'=>'Record and track feed usage',         'url'=>$base.'/feed'],
            ['icon'=>'bi-egg','color'=>'#eab308','label'=>'Egg Production', 'desc'=>'Daily egg collection records',        'url'=>$base.'/egg-production'],
            ['icon'=>'bi-heart-pulse','color'=>'#ef4444','label'=>'Mortality',      'desc'=>'Log and review bird losses',          'url'=>$base.'/mortality'],
            ['icon'=>'bi-shield-check','color'=>'#3b82f6','label'=>'Vaccination',    'desc'=>'Vaccination schedules and records',   'url'=>$base.'/vaccination'],
            ['icon'=>'bi-capsule-pill','color'=>'#8b5cf6','label'=>'Medication',     'desc'=>'Treatment records and costs',         'url'=>$base.'/medication'],
            ['icon'=>'bi-speedometer2','color'=>'#06b6d4','label'=>'Weight Tracking','desc'=>'Bird weight and FCR monitoring',      'url'=>$base.'/weights'],
            ['icon'=>'bi-box-seam','color'=>'#64748b','label'=>'Inventory',      'desc'=>'Feed and medicine stock levels',      'url'=>$base.'/inventory'],
        ];
        foreach ($actions as $a):
        ?>
            <div class="col-6 col-md-3">
                <a href="<?= htmlspecialchars($a['url']) ?>" class="action-tile">
                    <span class="tile-icon" style="color:<?= $a['color'] ?>;"><i class="bi <?= $a['icon'] ?>"></i></span>
                    <div class="fw-semibold small mt-2"><?= htmlspecialchars($a['label']) ?></div>
                    <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($a['desc']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Performance summary -->
<div class="pou-card p-4">
    <h6 class="fw-bold mb-3">Performance Summary</h6>
    <div class="row g-3">
        <div class="col-md-3"><div class="pou-soft text-center"><div class="small text-muted">Mortality Rate</div><div class="fw-bold fs-5 text-<?= $mortalityRate >= 5 ? 'danger' : ($mortalityRate >= 2 ? 'warning' : 'success') ?>"><?= number_format($mortalityRate, 2) ?>%</div></div></div>
        <div class="col-md-3"><div class="pou-soft text-center"><div class="small text-muted">Avg FCR</div><div class="fw-bold fs-5"><?= number_format($avgFcr, 3) ?></div></div></div>
        <div class="col-md-3"><div class="pou-soft text-center"><div class="small text-muted">Avg Weight</div><div class="fw-bold fs-5"><?= number_format($avgWeightKg, 3) ?> kg</div></div></div>
        <div class="col-md-3"><div class="pou-soft text-center"><div class="small text-muted">Feed Used</div><div class="fw-bold fs-5"><?= number_format($totalFeedKg, 2) ?> kg</div></div></div>
    </div>
</div>

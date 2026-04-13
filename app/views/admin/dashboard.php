<?php
$summary = $summary ?? [];
$recentActivities = $recentActivities ?? [];
$lowStockItems = $lowStockItems ?? [];
$mt = $monitorTotals ?? [];

$totalBirds       = (float)($summary['total_birds'] ?? 0);
$totalEggs        = (float)($summary['total_eggs'] ?? 0);
$totalMortality   = (float)($summary['total_mortality'] ?? 0);
$totalRevenue     = (float)($mt['total_revenue'] ?? $summary['sales_revenue'] ?? 0);
$totalExpenses    = (float)($mt['total_expenses'] ?? $summary['expenses_value'] ?? 0);
$totalAssets      = (float)($mt['total_assets'] ?? $assets ?? 0);
$totalLiabilities = (float)($mt['total_liabilities'] ?? $liabilities ?? 0);
$netProfit        = (float)($mt['net_profit'] ?? ($totalRevenue - $totalExpenses));
$workingCapital   = (float)($mt['working_capital'] ?? ($totalAssets - $totalLiabilities));
$profitMarginPct  = (float)($mt['profit_margin'] ?? 0);
$liquidityRatio   = (float)($liquidityRatio ?? 0);
$roi              = (float)($roi ?? 0);
$monthlyCombined  = $monthlyCombined ?? [];
$base             = rtrim(BASE_URL, '/');

$healthScore           = $healthScore ?? 0;
$healthLabel           = $healthLabel ?? 'N/A';
$healthClass           = $healthClass ?? 'secondary';
$goingConcernStatus    = $goingConcernStatus ?? 'N/A';
$goingConcernClass     = $goingConcernClass ?? 'secondary';
$decisionRecommendation = $decisionRecommendation ?? 'N/A';
$decisionClass         = $decisionClass ?? 'secondary';
$topRisk               = $topRisk ?? 'No risk data available.';
$topAction             = $topAction ?? 'No action data available.';
$topBatch              = $topBatch ?? [];
$worstBatch            = $worstBatch ?? [];
$lossMakingBatches     = $lossMakingBatches ?? [];
$strongBatches         = $strongBatches ?? [];
?>

<style>
    .admin-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }
    .admin-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #111827 0%, #1f2937 50%, #374151 100%);
        color: #fff;
        padding: 24px;
        box-shadow: 0 14px 32px rgba(17, 24, 39, 0.22);
    }
    .admin-kpi {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #eef2f7;
        height: 100%;
    }
    .admin-kpi .title { color: #64748b; font-size: 13px; margin-bottom: 6px; }
    .admin-kpi .value { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
    .admin-kpi .meta  { font-size: 12px; color: #94a3b8; }
    .admin-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        height: 100%;
    }
    .admin-note {
        border-left: 4px solid #2563eb;
        background: #eff6ff;
        border-radius: 14px;
        padding: 12px 14px;
    }
    .admin-risk {
        border-left: 4px solid #dc2626;
        background: #fef2f2;
        border-radius: 14px;
        padding: 12px 14px;
    }
    .admin-good {
        border-left: 4px solid #16a34a;
        background: #f0fdf4;
        border-radius: 14px;
        padding: 12px 14px;
    }
    .quick-action-btn {
        border-radius: 14px !important;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.12);
    }
    .module-tile {
        border-radius: 18px;
        padding: 22px 16px;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: transform .15s, box-shadow .15s;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1e293b;
    }
    .module-tile:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15,23,42,.12);
        color: #1e293b;
    }
    .module-tile .tile-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        display: block;
    }
    .module-tile .tile-label {
        font-size: 13px;
        font-weight: 600;
    }
    .activity-badge { font-size: 11px; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .trend-table th { font-size: 12px; color: #64748b; font-weight: 600; }
    .trend-table td { font-size: 13px; }
</style>

<!-- ── Page Header ── -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Business Command Center</span>
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">Full business overview across poultry, finance, economics, inventory, and decision intelligence.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="badge text-bg-<?= $healthClass ?> px-3 py-2 rounded-pill">Health: <?= htmlspecialchars($healthLabel) ?></span>
        <span class="badge text-bg-<?= $goingConcernClass ?> px-3 py-2 rounded-pill">Going Concern: <?= htmlspecialchars($goingConcernStatus) ?></span>
        <span class="badge text-bg-<?= $decisionClass ?> px-3 py-2 rounded-pill">Decision: <?= htmlspecialchars($decisionRecommendation) ?></span>
    </div>
</div>

<!-- ── Quick Action Buttons ── -->
<div class="admin-card p-3 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small fw-semibold me-1">Quick Actions:</span>
        <a href="<?= $base ?>/batches/create"      class="quick-action-btn btn btn-sm btn-outline-dark"><i class="bi bi-plus-circle"></i> New Batch</a>
        <a href="<?= $base ?>/egg-production/create" class="quick-action-btn btn btn-sm btn-outline-warning"><i class="bi bi-egg"></i> Log Eggs</a>
        <a href="<?= $base ?>/mortality/create"    class="quick-action-btn btn btn-sm btn-outline-danger"><i class="bi bi-heartbreak"></i> Log Mortality</a>
        <a href="<?= $base ?>/sales/create"        class="quick-action-btn btn btn-sm btn-outline-success"><i class="bi bi-cart-plus"></i> New Sale</a>
        <a href="<?= $base ?>/expenses/create"     class="quick-action-btn btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i> Add Expense</a>
        <a href="<?= $base ?>/inventory/items/create" class="quick-action-btn btn btn-sm btn-outline-info"><i class="bi bi-box-seam"></i> Add Stock</a>
        <a href="<?= $base ?>/reports"             class="quick-action-btn btn btn-sm btn-dark"><i class="bi bi-bar-chart-line"></i> Reports</a>
    </div>
</div>

<!-- ── Hero Strip ── -->
<div class="admin-hero mb-4">
    <div class="row g-4 text-center">
        <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold"><?= number_format($healthScore) ?>%</div>
            <div class="small text-white-50">Business Health</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold">GHS <?= number_format($totalRevenue, 2) ?></div>
            <div class="small text-white-50">Total Revenue</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold">GHS <?= number_format($totalAssets, 2) ?></div>
            <div class="small text-white-50">Assets Strength</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold">GHS <?= number_format($totalLiabilities, 2) ?></div>
            <div class="small text-white-50">Liability Pressure</div>
        </div>
    </div>
</div>

<!-- ── Financial KPI Row ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #22c55e;">
            <div class="title">Revenue</div>
            <div class="value text-success">GHS <?= number_format($totalRevenue, 2) ?></div>
            <div class="meta"><a href="<?= $base ?>/financial" class="text-muted">View Financial →</a></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #ef4444;">
            <div class="title">Expenses</div>
            <div class="value text-danger">GHS <?= number_format($totalExpenses, 2) ?></div>
            <div class="meta"><a href="<?= $base ?>/expenses" class="text-muted">View Expenses →</a></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #06b6d4;">
            <div class="title">Assets</div>
            <div class="value text-info">GHS <?= number_format($totalAssets, 2) ?></div>
            <div class="meta"><a href="<?= $base ?>/inventory" class="text-muted">View Inventory →</a></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #f59e0b;">
            <div class="title">Liabilities</div>
            <div class="value text-warning">GHS <?= number_format($totalLiabilities, 2) ?></div>
            <div class="meta">Net: <strong class="<?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></strong></div>
        </div>
    </div>
</div>

<!-- ── Poultry + Working Capital KPI Row (FIXED) ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #8b5cf6;">
            <div class="title">Current Live Birds</div>
            <div class="value"><?= number_format($totalBirds, 0) ?></div>
            <div class="meta">Live birds after mortality deductions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #f59e0b;">
            <div class="title">Total Egg Production</div>
            <div class="value"><?= number_format($totalEggs, 0) ?></div>
            <div class="meta">All recorded eggs produced</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #ef4444;">
            <div class="title">Total Mortality</div>
            <div class="value text-danger"><?= number_format($totalMortality, 0) ?></div>
            <div class="meta">All mortality recorded in the system</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid <?= $workingCapital >= 0 ? '#22c55e' : '#ef4444' ?>;">
            <div class="title">Working Capital</div>
            <div class="value <?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($workingCapital, 2) ?></div>
            <div class="meta">Assets minus liabilities</div>
        </div>
    </div>
</div>

<!-- ── Ratios Row ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #22c55e;">
            <div class="title">Net Profit</div>
            <div class="value <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></div>
            <div class="meta">Revenue minus expenses</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #06b6d4;">
            <div class="title">Profit Margin</div>
            <div class="value"><?= number_format($profitMarginPct, 2) ?>%</div>
            <div class="meta">Net profit as % of revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #8b5cf6;">
            <div class="title">Liquidity Ratio</div>
            <div class="value"><?= number_format($liquidityRatio, 2) ?></div>
            <div class="meta">Ability to cover obligations</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-kpi" style="border-left:4px solid #f59e0b;">
            <div class="title">ROI</div>
            <div class="value"><?= number_format($roi, 2) ?>%</div>
            <div class="meta">Return on operating cost</div>
        </div>
    </div>
</div>

<!-- ── Risk / Action / Low Stock ── -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Top Risk</h5>
                <span class="badge text-bg-danger rounded-pill px-3 py-2">Risk</span>
            </div>
            <div class="admin-risk">
                <div class="small"><?= htmlspecialchars($topRisk) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Top Management Action</h5>
                <span class="badge text-bg-primary rounded-pill px-3 py-2">Action</span>
            </div>
            <div class="admin-note">
                <div class="small"><?= htmlspecialchars($topAction) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Low Stock Alerts</h5>
                <span class="badge text-bg-warning rounded-pill px-3 py-2"><?= number_format((float)($summary['low_stock_count'] ?? 0)) ?></span>
            </div>
            <?php if (!empty($lowStockItems)): ?>
                <?php foreach (array_slice($lowStockItems, 0, 3) as $item): ?>
                    <div class="admin-soft mb-2">
                        <div class="fw-semibold"><?= htmlspecialchars($item['item_name']) ?></div>
                        <div class="small text-muted">Stock: <?= number_format((float)$item['current_stock'], 2) ?> | Reorder: <?= number_format((float)$item['reorder_level'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
                <a href="<?= $base ?>/inventory/low-stock" class="btn btn-sm btn-outline-warning mt-2 w-100">View All Low Stock</a>
            <?php else: ?>
                <div class="admin-good">
                    <div class="small">No low stock alerts right now.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Best / Worst Batch ── -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Best Batch</h5>
                <span class="badge text-bg-success rounded-pill px-3 py-2">Strength</span>
            </div>
            <?php if (!empty($topBatch)): ?>
                <div class="admin-soft">
                    <div class="fw-semibold mb-1">
                        <?= htmlspecialchars(($topBatch['batch_code'] ?? '-') . (!empty($topBatch['batch_name']) ? ' — ' . $topBatch['batch_name'] : '')) ?>
                    </div>
                    <div class="small text-muted">Gross Profit: GHS <?= number_format((float)($topBatch['gross_profit'] ?? 0), 2) ?></div>
                    <div class="small text-muted">Profit Margin: <?= number_format((float)($topBatch['profit_margin'] ?? 0), 2) ?>%</div>
                </div>
            <?php else: ?>
                <div class="text-muted small">No batch performance data available.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Weakest Batch</h5>
                <span class="badge text-bg-danger rounded-pill px-3 py-2">Attention</span>
            </div>
            <?php if (!empty($worstBatch)): ?>
                <div class="admin-soft">
                    <div class="fw-semibold mb-1">
                        <?= htmlspecialchars(($worstBatch['batch_code'] ?? '-') . (!empty($worstBatch['batch_name']) ? ' — ' . $worstBatch['batch_name'] : '')) ?>
                    </div>
                    <div class="small text-muted">Gross Profit: GHS <?= number_format((float)($worstBatch['gross_profit'] ?? 0), 2) ?></div>
                    <div class="small text-muted">Profit Margin: <?= number_format((float)($worstBatch['profit_margin'] ?? 0), 2) ?>%</div>
                </div>
            <?php else: ?>
                <div class="text-muted small">No weak batch performance data available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Monthly Trend + System Summary ── -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Monthly Trend</h5>
                <a href="<?= $base ?>/reports/profit-loss" class="badge text-bg-primary rounded-pill px-3 py-2 text-decoration-none">Full Report</a>
            </div>
            <?php if (!empty($monthlyCombined)): ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle trend-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Expenses</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice(array_reverse($monthlyCombined), 0, 6) as $row):
                                $mNet = (float)($row['sales_revenue'] ?? $row['revenue'] ?? 0) - (float)($row['total_expense'] ?? $row['expenses'] ?? 0);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['month_label'] ?? $row['month'] ?? $row['period'] ?? '-') ?></td>
                                    <td class="text-end text-success">GHS <?= number_format((float)($row['sales_revenue'] ?? $row['revenue'] ?? 0), 2) ?></td>
                                    <td class="text-end text-danger">GHS <?= number_format((float)($row['total_expense'] ?? $row['expenses'] ?? 0), 2) ?></td>
                                    <td class="text-end fw-semibold <?= $mNet >= 0 ? 'text-success' : 'text-danger' ?>">
                                        GHS <?= number_format($mNet, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted small">No monthly trend data available yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">System Summary</h5>
                <span class="badge text-bg-primary rounded-pill px-3 py-2">Overview</span>
            </div>
            <div class="admin-soft mb-2">Users: <strong><?= number_format((int)($summary['users'] ?? 0)) ?></strong></div>
            <div class="admin-soft mb-2">Customers: <strong><?= number_format((int)($summary['customers'] ?? 0)) ?></strong></div>
            <div class="admin-soft mb-2">Total Batches: <strong><?= number_format((int)($summary['total_batches'] ?? 0)) ?></strong></div>
            <div class="admin-soft mb-2">Active Batches: <strong><?= number_format((int)($summary['active_batches'] ?? 0)) ?></strong></div>
            <div class="admin-soft mb-2">Total Feed Used: <strong><?= number_format((float)($summary['total_feed_used_kg'] ?? 0), 2) ?> Kg</strong></div>
            <div class="admin-soft">Total Stock Value: <strong>GHS <?= number_format((float)($summary['total_stock_value'] ?? 0), 2) ?></strong></div>
        </div>
    </div>
</div>

<!-- ── Recent Activities + Strong/Loss Batches ── -->
<?php include BASE_PATH . 'app/views/partials/dashboard_charts.php'; ?>
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recent Business Activities</h5>
                <span class="badge text-bg-dark rounded-pill px-3 py-2">Live Feed</span>
            </div>
            <?php if (!empty($recentActivities)): ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size:12px;">Date</th>
                                <th style="font-size:12px;">Type</th>
                                <th style="font-size:12px;">Title</th>
                                <th style="font-size:12px;" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $activityColors = [
                                'sale'      => 'success',
                                'sales'     => 'success',
                                'expense'   => 'danger',
                                'expenses'  => 'danger',
                                'batch'     => 'primary',
                                'mortality' => 'dark',
                                'egg'       => 'warning',
                                'feed'      => 'info',
                                'stock'     => 'secondary',
                                'inventory' => 'secondary',
                            ];
                            foreach ($recentActivities as $row):
                                $typeKey = strtolower(trim($row['activity_type'] ?? ''));
                                $badgeColor = 'secondary';
                                foreach ($activityColors as $k => $c) {
                                    if (str_contains($typeKey, $k)) { $badgeColor = $c; break; }
                                }
                            ?>
                                <tr>
                                    <td class="small text-muted"><?= htmlspecialchars($row['activity_date']) ?></td>
                                    <td>
                                        <span class="activity-badge badge text-bg-<?= $badgeColor ?>">
                                            <?= htmlspecialchars($row['activity_type']) ?>
                                        </span>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($row['title']) ?></td>
                                    <td class="text-end small fw-semibold">GHS <?= number_format((float)$row['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted small">No recent activities available yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Batch Highlights</h5>
                <span class="badge text-bg-secondary rounded-pill px-3 py-2">Batches</span>
            </div>
            <?php if (!empty($strongBatches)): ?>
                <div class="mb-2">
                    <div class="small fw-semibold text-success mb-1"><i class="bi bi-arrow-up-circle-fill"></i> Strong Batches</div>
                    <?php foreach (array_slice($strongBatches, 0, 2) as $b): ?>
                        <div class="admin-soft mb-2">
                            <div class="fw-semibold small"><?= htmlspecialchars($b['batch_code'] ?? '-') ?></div>
                            <div class="small text-muted">Margin: <?= number_format((float)($b['profit_margin'] ?? 0), 1) ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($lossMakingBatches)): ?>
                <div>
                    <div class="small fw-semibold text-danger mb-1"><i class="bi bi-arrow-down-circle-fill"></i> Loss-Making Batches</div>
                    <?php foreach (array_slice($lossMakingBatches, 0, 2) as $b): ?>
                        <div class="admin-soft mb-2">
                            <div class="fw-semibold small"><?= htmlspecialchars($b['batch_code'] ?? '-') ?></div>
                            <div class="small text-muted">Margin: <?= number_format((float)($b['profit_margin'] ?? 0), 1) ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (empty($strongBatches) && empty($lossMakingBatches)): ?>
                <div class="text-muted small">No batch highlight data available.</div>
            <?php endif; ?>
            <a href="<?= $base ?>/reports/batch-performance" class="btn btn-sm btn-outline-dark mt-3 w-100">View Batch Report</a>
        </div>
    </div>
</div>

<!-- ── Module Quick Access Grid ── -->
<div class="admin-card p-4 mb-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Module Quick Access</h5>
        <span class="badge text-bg-dark rounded-pill px-3 py-2">All Modules</span>
    </div>
    <div class="row g-3">
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/poultry" class="module-tile">
                <span class="tile-icon">🐔</span>
                <span class="tile-label">Poultry</span>
                <div class="small text-muted mt-1">Batches, feed, mortality</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/financial" class="module-tile">
                <span class="tile-icon">💰</span>
                <span class="tile-label">Financial</span>
                <div class="small text-muted mt-1">Revenue, expenses, P&amp;L</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/economic" class="module-tile">
                <span class="tile-icon">📊</span>
                <span class="tile-label">Economic</span>
                <div class="small text-muted mt-1">Health, going concern</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/inventory" class="module-tile">
                <span class="tile-icon">📦</span>
                <span class="tile-label">Inventory</span>
                <div class="small text-muted mt-1">Stock, items, receipts</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/reports" class="module-tile">
                <span class="tile-icon">📋</span>
                <span class="tile-label">Reports</span>
                <div class="small text-muted mt-1">All business reports</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/sales" class="module-tile">
                <span class="tile-icon">🛒</span>
                <span class="tile-label">Sales</span>
                <div class="small text-muted mt-1">Orders, customers</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/users" class="module-tile">
                <span class="tile-icon">👥</span>
                <span class="tile-label">Users</span>
                <div class="small text-muted mt-1">Manage user accounts</div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <a href="<?= $base ?>/settings" class="module-tile">
                <span class="tile-icon">⚙️</span>
                <span class="tile-label">Settings</span>
                <div class="small text-muted mt-1">System configuration</div>
            </a>
        </div>
    </div>
</div>

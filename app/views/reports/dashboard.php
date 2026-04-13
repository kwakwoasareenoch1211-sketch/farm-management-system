<?php
$printTitle    = 'Farm Management Reports Dashboard';
$printSubtitle = 'Generated: ' . date('d M Y H:i') . ' | Poultry Farm Management System';
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';

$totals = $totals ?? [];
$monthlyRevenueVsExpense = $monthlyRevenueVsExpense ?? [];
$recentActivities = $recentActivities ?? [];
$smartSignals = $smartSignals ?? [];
$topProfitableBatches = $topProfitableBatches ?? [];
$highMortalityBatches = $highMortalityBatches ?? [];
$lowStockPressure = $lowStockPressure ?? [];

$totalSales = (float)($totals['total_sales'] ?? 0);
$totalExpenses = (float)($totals['total_expenses'] ?? 0);
$netProfit = (float)($totals['net_profit'] ?? 0);
$inventoryValue = (float)($totals['inventory_value'] ?? 0);
$lowStockCount = (int)($totals['low_stock_count'] ?? 0);
$totalItems = (int)($totals['total_items'] ?? 0);
$totalBatches = (int)($totals['total_batches'] ?? 0);
$totalMortality = (float)($totals['total_mortality'] ?? 0);
$totalEggs = (float)($totals['total_eggs'] ?? 0);
$totalFeedKg = (float)($totals['total_feed_kg'] ?? 0);
$totalFeedCost = (float)($totals['total_feed_cost'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Reports Dashboard</h2>
            <p class="text-muted mb-0">Intelligent reporting center for farm decisions.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap d-print-none">
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports/batch-performance" class="btn btn-dark">Batch Performance</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports/feed" class="btn btn-outline-secondary">Feed Report</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports/profit-loss" class="btn btn-outline-secondary">Profit &amp; Loss</a>
        </div>
    </div>

    <!-- QUICK EXPORT / PRINT ALL REPORTS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-download text-primary me-2"></i>Quick Access — Print or Export Any Report</h5>
            <div class="row g-2">
                <?php
                $reportLinks = [
                    ['label'=>'Batch Performance',    'url'=>'reports/batch-performance',    'icon'=>'bi-clipboard-data',    'color'=>'#3b82f6'],
                    ['label'=>'Feed Report',           'url'=>'reports/feed',                 'icon'=>'bi-basket',            'color'=>'#f59e0b'],
                    ['label'=>'Mortality Report',      'url'=>'reports/mortality',            'icon'=>'bi-heart-pulse',       'color'=>'#d63384'],
                    ['label'=>'Vaccination Report',    'url'=>'reports/vaccination',          'icon'=>'bi-shield-check',      'color'=>'#22c55e'],
                    ['label'=>'Medication Report',     'url'=>'reports/medication',           'icon'=>'bi-capsule',           'color'=>'#ef4444'],
                    ['label'=>'Weight Report',         'url'=>'reports/weight',               'icon'=>'bi-speedometer2',      'color'=>'#8b5cf6'],
                    ['label'=>'Egg Production',        'url'=>'reports/egg-production',       'icon'=>'bi-egg-fried',         'color'=>'#f97316'],
                    ['label'=>'Sales Report',          'url'=>'reports/sales',                'icon'=>'bi-cart3',             'color'=>'#10b981'],
                    ['label'=>'Expense Report',        'url'=>'reports/expenses',             'icon'=>'bi-wallet2',           'color'=>'#0d6efd'],
                    ['label'=>'Profit & Loss',         'url'=>'reports/profit-loss',          'icon'=>'bi-graph-up',          'color'=>'#16a34a'],
                    ['label'=>'Forecast',              'url'=>'reports/forecast',             'icon'=>'bi-graph-up-arrow',    'color'=>'#7c3aed'],
                    ['label'=>'Business Health',       'url'=>'reports/business-health',      'icon'=>'bi-heart',             'color'=>'#dc2626'],
                    ['label'=>'Stock Position',        'url'=>'reports/stock-position',       'icon'=>'bi-boxes',             'color'=>'#64748b'],
                    ['label'=>'Stock Movement',        'url'=>'reports/stock-movement',       'icon'=>'bi-arrow-left-right',  'color'=>'#0891b2'],
                    ['label'=>'Low Stock',             'url'=>'reports/low-stock',            'icon'=>'bi-exclamation-triangle','color'=>'#ea580c'],
                    ['label'=>'Decisions',             'url'=>'reports/decisions',            'icon'=>'bi-lightbulb',         'color'=>'#ca8a04'],
                    ['label'=>'Custom Report',         'url'=>'reports/custom',               'icon'=>'bi-file-earmark-text', 'color'=>'#475569'],
                    ['label'=>'Export Center',         'url'=>'reports/export',               'icon'=>'bi-download',          'color'=>'#1d4ed8'],
                ];
                $base = rtrim(BASE_URL, '/');
                foreach ($reportLinks as $rl):
                ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="<?= $base ?>/<?= $rl['url'] ?>" class="d-block text-decoration-none p-2 rounded-3 text-center"
                       style="border:1px solid #e2e8f0;background:#fff;transition:all .15s;"
                       onmouseover="this.style.borderColor='<?= $rl['color'] ?>';this.style.background='<?= $rl['color'] ?>10'"
                       onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
                        <i class="bi <?= $rl['icon'] ?>" style="font-size:1.5rem;color:<?= $rl['color'] ?>;display:block;margin-bottom:4px;"></i>
                        <div style="font-size:11px;font-weight:600;color:#374151;"><?= $rl['label'] ?></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 pt-3 border-top d-flex gap-2 align-items-center">
                <i class="bi bi-info-circle text-muted"></i>
                <span class="text-muted small">Each report page has its own <strong>Print</strong> and <strong>Export CSV</strong> buttons for saving or sharing.</span>
            </div>
        </div>
    </div>

    <?php if (!empty($smartSignals)): ?>
        <div class="row g-3 mb-4">
            <?php foreach ($smartSignals as $signal): ?>
                <div class="col-md-6">
                    <div class="alert alert-<?= ($signal['type'] ?? '') === 'danger' ? 'danger' : (($signal['type'] ?? '') === 'warning' ? 'warning' : 'info') ?> mb-0">
                        <strong><?= htmlspecialchars($signal['title'] ?? '') ?></strong><br>
                        <?= htmlspecialchars($signal['message'] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Total Sales</div><div class="fs-4 fw-bold">GHS <?= number_format($totalSales, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Total Expenses</div><div class="fs-4 fw-bold">GHS <?= number_format($totalExpenses, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Net Profit / Loss</div><div class="fs-4 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Inventory Value</div><div class="fs-4 fw-bold">GHS <?= number_format($inventoryValue, 2) ?></div></div></div></div>

        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Low Stock Items</div><div class="fs-4 fw-bold text-danger"><?= number_format($lowStockCount) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Animal Batches</div><div class="fs-4 fw-bold"><?= number_format($totalBatches) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Total Mortality</div><div class="fs-4 fw-bold text-danger"><?= number_format($totalMortality, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Egg Production</div><div class="fs-4 fw-bold"><?= number_format($totalEggs, 0) ?></div></div></div></div>

        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Feed Used</div><div class="fs-4 fw-bold"><?= number_format($totalFeedKg, 2) ?> kg</div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Feed Cost</div><div class="fs-4 fw-bold">GHS <?= number_format($totalFeedCost, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Tracked Items</div><div class="fs-4 fw-bold"><?= number_format($totalItems) ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="text-muted small">Reporting Status</div><div class="fs-4 fw-bold">Live</div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Revenue vs Expenses Trend</h5>
                    <?php if (!empty($monthlyRevenueVsExpense)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Revenue</th>
                                        <th>Expenses</th>
                                        <th>Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthlyRevenueVsExpense as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['month_label'] ?? '') ?></td>
                                            <td>GHS <?= number_format((float)($row['revenue'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['expenses'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['net'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No monthly data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Recent Activities</h5>
                    <?php if (!empty($recentActivities)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentActivities as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['activity_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['activity_date'] ?? '') ?></td>
                                            <td>GHS <?= number_format((float)($row['amount'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No recent activities available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Top Profitable Batches</h5>
                    <?php foreach ($topProfitableBatches as $row): ?>
                        <div class="border rounded-4 p-3 mb-2 bg-light">
                            <div class="fw-semibold"><?= htmlspecialchars($row['batch_code'] ?? '') ?> - <?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($row['production_purpose'] ?? '') ?></div>
                            <div class="small mt-1">Gross Profit: <strong>GHS <?= number_format((float)($row['gross_profit'] ?? 0), 2) ?></strong></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Highest Mortality Batches</h5>
                    <?php foreach ($highMortalityBatches as $row): ?>
                        <div class="border rounded-4 p-3 mb-2 bg-light">
                            <div class="fw-semibold"><?= htmlspecialchars($row['batch_code'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                            <div class="small mt-1">Mortality: <strong><?= number_format((float)($row['total_mortality'] ?? 0), 2) ?></strong> (<?= number_format((float)($row['mortality_rate'] ?? 0), 2) ?>%)</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Low Stock Pressure</h5>
                    <?php foreach ($lowStockPressure as $row): ?>
                        <div class="border rounded-4 p-3 mb-2 bg-light">
                            <div class="fw-semibold"><?= htmlspecialchars($row['item_name'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($row['farm_name'] ?? '') ?></div>
                            <div class="small mt-1">Shortage: <strong><?= number_format((float)($row['shortage_qty'] ?? 0), 2) ?></strong></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Quick Report Access</h5>
            <div class="row g-3">
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/batch-performance" class="btn btn-outline-dark w-100">Batch Performance</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/feed" class="btn btn-outline-dark w-100">Feed Report</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/mortality" class="btn btn-outline-dark w-100">Mortality Report</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/vaccination" class="btn btn-outline-dark w-100">Vaccination Report</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/stock-position" class="btn btn-outline-dark w-100">Stock Position</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/low-stock" class="btn btn-outline-dark w-100">Low Stock</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/sales" class="btn btn-outline-dark w-100">Sales Report</a></div>
                <div class="col-md-3"><a href="<?= rtrim(BASE_URL, '/') ?>/reports/expenses" class="btn btn-outline-dark w-100">Expense Report</a></div>
            </div>
        </div>
    </div>
</div>
<?php
$mt  = $monitorTotals ?? [];
$totalRevenue     = (float)($mt['total_revenue'] ?? 0);
$totalExpenses    = (float)($mt['total_expenses'] ?? 0);
$totalAssets      = (float)($mt['total_assets'] ?? 0);
$totalLiabilities = (float)($mt['total_liabilities'] ?? 0);
$netProfit        = (float)($mt['net_profit'] ?? 0);
$workingCapital   = (float)($mt['working_capital'] ?? 0);
$profitMargin     = (float)($mt['profit_margin'] ?? 0);
$debtRatio        = (float)($mt['debt_ratio'] ?? 0);

// Compute health score (0-100)
$score = 0;
$score += $profitMargin >= 25 ? 25 : ($profitMargin >= 15 ? 20 : ($profitMargin >= 5 ? 12 : ($profitMargin > 0 ? 6 : 0)));
$score += $workingCapital > 0 ? 20 : 0;
$score += $debtRatio <= 30 ? 20 : ($debtRatio <= 60 ? 12 : 4);
$score += count($lossMaking ?? []) === 0 ? 20 : (count($strong ?? []) > count($lossMaking ?? []) ? 12 : 4);
$score += $netProfit > 0 ? 15 : 0;

$healthLabel = $score >= 80 ? 'Strong' : ($score >= 55 ? 'Stable' : ($score >= 30 ? 'Caution' : 'At Risk'));
$healthClass = $score >= 80 ? 'success' : ($score >= 55 ? 'warning' : ($score >= 30 ? 'warning' : 'danger'));

$base = rtrim(BASE_URL, '/');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Business Health Report</h2>
            <p class="text-muted mb-0">Comprehensive health assessment across profitability, liquidity, and operational efficiency.</p>
        </div>
        <a href="<?= $base ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Health score hero -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <div style="font-size:4rem;font-weight:900;" class="text-<?= $healthClass ?>"><?= $score ?></div>
                    <div class="fs-5 fw-bold">/ 100</div>
                    <span class="badge bg-<?= $healthClass ?> px-3 py-2 mt-2 fs-6"><?= $healthLabel ?></span>
                </div>
                <div class="col-md-8">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3"><div class="fs-5 fw-bold text-success">GHS <?= number_format($totalRevenue, 2) ?></div><div class="small text-white-50">Revenue</div></div>
                        <div class="col-6 col-md-3"><div class="fs-5 fw-bold text-danger">GHS <?= number_format($totalExpenses, 2) ?></div><div class="small text-white-50">Expenses</div></div>
                        <div class="col-6 col-md-3"><div class="fs-5 fw-bold text-info">GHS <?= number_format($totalAssets, 2) ?></div><div class="small text-white-50">Assets</div></div>
                        <div class="col-6 col-md-3"><div class="fs-5 fw-bold text-warning">GHS <?= number_format($totalLiabilities, 2) ?></div><div class="small text-white-50">Liabilities</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Net Profit</div><div class="fs-4 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Profit Margin</div><div class="fs-4 fw-bold <?= $profitMargin >= 15 ? 'text-success' : 'text-warning' ?>"><?= number_format($profitMargin, 1) ?>%</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Working Capital</div><div class="fs-4 fw-bold <?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($workingCapital, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Debt Ratio</div><div class="fs-4 fw-bold <?= $debtRatio <= 50 ? 'text-success' : 'text-danger' ?>"><?= number_format($debtRatio, 1) ?>%</div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Smart signals -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Smart Health Signals</h5>
                    <?php if (!empty($smartSignals)): ?>
                        <?php foreach ($smartSignals as $s): ?>
                            <div class="alert alert-<?= htmlspecialchars($s['type']) ?> mb-2">
                                <strong><?= htmlspecialchars($s['title']) ?>:</strong> <?= htmlspecialchars($s['message']) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-success">No critical signals detected. Business appears healthy.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Batch health -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Batch Health Summary</h5>
                    <div class="row g-3 text-center mb-3">
                        <div class="col-6"><div class="border rounded-4 p-3"><div class="fs-3 fw-bold text-success"><?= count($strong ?? []) ?></div><div class="small text-muted">Profitable Batches</div></div></div>
                        <div class="col-6"><div class="border rounded-4 p-3"><div class="fs-3 fw-bold text-danger"><?= count($lossMaking ?? []) ?></div><div class="small text-muted">Loss-Making Batches</div></div></div>
                    </div>
                    <?php if (!empty($highMortality)): ?>
                        <h6 class="fw-semibold mb-2">High Mortality Batches</h6>
                        <?php foreach ($highMortality as $b): ?>
                            <div class="border rounded-4 p-2 mb-2 bg-light d-flex justify-content-between">
                                <span><?= htmlspecialchars($b['batch_code'] ?? '') ?> <?= htmlspecialchars($b['batch_name'] ?? '') ?></span>
                                <span class="text-danger fw-bold"><?= number_format((float)($b['mortality_rate'] ?? 0), 1) ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly trend -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Monthly Health Trend</h5>
            <?php if (!empty($monthlyCombined)): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($monthlyCombined as $m): ?>
                                <?php $n = (float)($m['net_position'] ?? 0); ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['month_label'] ?? '') ?></td>
                                    <td class="text-success">GHS <?= number_format((float)($m['sales_revenue'] ?? 0), 2) ?></td>
                                    <td class="text-danger">GHS <?= number_format((float)($m['total_expense'] ?? 0), 2) ?></td>
                                    <td class="fw-bold <?= $n >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($n, 2) ?></td>
                                    <td><span class="badge bg-<?= $n >= 0 ? 'success' : 'danger' ?>"><?= $n >= 0 ? 'Healthy' : 'Loss' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No monthly data available yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low stock -->
    <?php if (!empty($lowStockPressure)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Low Stock Pressure</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Item</th><th>Farm</th><th>Current Stock</th><th>Reorder Level</th><th>Shortage</th></tr></thead>
                    <tbody>
                        <?php foreach ($lowStockPressure as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                <td class="text-danger"><?= number_format((float)($row['current_stock'] ?? 0), 2) ?></td>
                                <td><?= number_format((float)($row['reorder_level'] ?? 0), 2) ?></td>
                                <td class="fw-bold text-danger"><?= number_format((float)($row['shortage_qty'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

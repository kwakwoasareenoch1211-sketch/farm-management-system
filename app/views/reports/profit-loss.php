<?php
$printTitle    = 'Profit & Loss Report';
$printSubtitle = 'Generated: ' . date('d M Y H:i') . ' | Poultry Farm Management System';
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';
?><?php
$financeTotals = $financeTotals ?? [];
$currentMonthTotals = $currentMonthTotals ?? [];
$monthlyCombined = $monthlyCombined ?? [];

$totalRevenue = (float)($financeTotals['sales_revenue'] ?? 0);
$totalExpenses = (float)($financeTotals['total_expense'] ?? 0);
$netProfit = (float)($financeTotals['net_position'] ?? 0);

$monthRevenue = (float)($currentMonthTotals['sales_revenue'] ?? 0);
$monthExpenses = (float)($currentMonthTotals['total_expense'] ?? 0);
$monthNet = (float)($currentMonthTotals['net_position'] ?? 0);

$profitMargin = $totalRevenue > 0 ? (($netProfit / $totalRevenue) * 100) : 0;
$monthMargin = $monthRevenue > 0 ? (($monthNet / $monthRevenue) * 100) : 0;
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Profit &amp; Loss Report</h2>
            <p class="text-muted mb-0">Revenue, expenses, and net position overview.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Revenue</div><div class="fs-4 fw-bold text-success">GHS <?= number_format($totalRevenue, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Expenses</div><div class="fs-4 fw-bold text-danger">GHS <?= number_format($totalExpenses, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Net Profit / Loss</div><div class="fs-4 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Profit Margin</div><div class="fs-4 fw-bold <?= $profitMargin >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($profitMargin, 1) ?>%</div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12"><div class="card border-0 shadow-sm rounded-4 bg-light"><div class="card-body"><strong>This Month:</strong> Revenue GHS <?= number_format($monthRevenue, 2) ?> &nbsp;|&nbsp; Expenses GHS <?= number_format($monthExpenses, 2) ?> &nbsp;|&nbsp; Net <span class="<?= $monthNet >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($monthNet, 2) ?></span> &nbsp;|&nbsp; Margin <?= number_format($monthMargin, 1) ?>%</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Expense Breakdown</h5>
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr><td>Feed Expenses</td><td class="text-end">GHS <?= number_format((float)($financeTotals['feed_expense'] ?? 0), 2) ?></td></tr>
                            <tr><td>Medication Expenses</td><td class="text-end">GHS <?= number_format((float)($financeTotals['medication_expense'] ?? 0), 2) ?></td></tr>
                            <tr><td>Vaccination Expenses</td><td class="text-end">GHS <?= number_format((float)($financeTotals['vaccination_expense'] ?? 0), 2) ?></td></tr>
                            <tr><td>Direct Expenses</td><td class="text-end">GHS <?= number_format((float)($financeTotals['direct_expense'] ?? 0), 2) ?></td></tr>
                            <tr class="fw-bold table-light"><td>Total Expenses</td><td class="text-end text-danger">GHS <?= number_format($totalExpenses, 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Monthly Trend</h5>
                    <?php if (!empty($monthlyCombined)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net</th></tr></thead>
                                <tbody>
                                    <?php foreach ($monthlyCombined as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['month_label'] ?? '') ?></td>
                                            <td>GHS <?= number_format((float)($row['sales_revenue'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['total_expense'] ?? 0), 2) ?></td>
                                            <td class="<?= (float)($row['net_position'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($row['net_position'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No monthly data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

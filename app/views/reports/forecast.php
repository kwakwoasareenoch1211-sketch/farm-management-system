<?php
$monthlyCombined = $monthlyCombined ?? [];
$forecastMonths  = $forecastMonths  ?? [];
$avgRevenue      = (float)($avgRevenue ?? 0);
$avgExpense      = (float)($avgExpense ?? 0);
$avgNet          = $avgRevenue - $avgExpense;
$base            = rtrim(BASE_URL, '/');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Forecast Report</h2>
            <p class="text-muted mb-0">Revenue and expense projections based on historical monthly averages.</p>
        </div>
        <a href="<?= $base ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Averages -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Avg Monthly Revenue</div>
                    <div class="fs-4 fw-bold text-success">GHS <?= number_format($avgRevenue, 2) ?></div>
                    <div class="small text-muted">Based on last <?= count($monthlyCombined) ?> months</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Avg Monthly Expenses</div>
                    <div class="fs-4 fw-bold text-danger">GHS <?= number_format($avgExpense, 2) ?></div>
                    <div class="small text-muted">All expense types combined</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Avg Monthly Net</div>
                    <div class="fs-4 fw-bold <?= $avgNet >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($avgNet, 2) ?></div>
                    <div class="small text-muted">Revenue minus expenses</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- 3-month projection -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">3-Month Projection</h5>
                    <p class="text-muted small mb-3">Projected using a 2% monthly revenue growth and 1% expense growth trend.</p>
                    <?php foreach ($forecastMonths as $f): ?>
                        <div class="border rounded-4 p-3 mb-3 bg-light">
                            <div class="fw-semibold mb-2"><?= htmlspecialchars($f['month_label']) ?></div>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="small text-muted">Revenue</div>
                                    <div class="fw-bold text-success small">GHS <?= number_format($f['projected_revenue'], 2) ?></div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Expenses</div>
                                    <div class="fw-bold text-danger small">GHS <?= number_format($f['projected_expense'], 2) ?></div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Net</div>
                                    <div class="fw-bold <?= $f['projected_net'] >= 0 ? 'text-success' : 'text-danger' ?> small">GHS <?= number_format($f['projected_net'], 2) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Historical trend -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Historical Monthly Performance</h5>
                    <?php if (!empty($monthlyCombined)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net</th><th>Trend</th></tr></thead>
                                <tbody>
                                    <?php foreach ($monthlyCombined as $m): ?>
                                        <?php $net = (float)($m['net_position'] ?? 0); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($m['month_label'] ?? '') ?></td>
                                            <td class="text-success">GHS <?= number_format((float)($m['sales_revenue'] ?? 0), 2) ?></td>
                                            <td class="text-danger">GHS <?= number_format((float)($m['total_expense'] ?? 0), 2) ?></td>
                                            <td class="fw-bold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($net, 2) ?></td>
                                            <td><span class="badge bg-<?= $net >= 0 ? 'success' : 'danger' ?>"><?= $net >= 0 ? 'Profit' : 'Loss' ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No historical data available yet. Record sales and expenses to generate forecasts.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body">
            <h6 class="fw-bold mb-2">Forecast Assumptions</h6>
            <ul class="text-muted small mb-0">
                <li>Revenue projection assumes 2% monthly growth based on historical average.</li>
                <li>Expense projection assumes 1% monthly growth based on historical average.</li>
                <li>Projections improve in accuracy as more months of data are recorded.</li>
                <li>Actual results may vary based on flock performance, market prices, and operational changes.</li>
            </ul>
        </div>
    </div>
</div>

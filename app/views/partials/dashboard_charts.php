<?php
/**
 * Dashboard Charts Partial
 * Included in admin, poultry, financial dashboards
 * Requires: $monthlyCombined, $summary, $base
 */
$monthlyCombined = $monthlyCombined ?? [];
$summary = $summary ?? [];

// Prepare chart data
$chartLabels  = array_map(fn($m) => $m['month_label'] ?? $m['month'] ?? '', array_slice($monthlyCombined, -6));
$chartRevenue = array_map(fn($m) => (float)($m['sales_revenue'] ?? $m['revenue'] ?? 0), array_slice($monthlyCombined, -6));
$chartExpense = array_map(fn($m) => (float)($m['total_expense'] ?? $m['expenses'] ?? 0), array_slice($monthlyCombined, -6));
$chartNet     = array_map(fn($m) => (float)($m['sales_revenue'] ?? 0) - (float)($m['total_expense'] ?? 0), array_slice($monthlyCombined, -6));

// Expense breakdown for donut
$feedCost  = (float)($summary['total_feed_cost'] ?? 0);
$medCost   = (float)($summary['total_medication_cost'] ?? 0);
$vacCost   = (float)($summary['total_vaccination_cost'] ?? 0);
$directExp = (float)($summary['expenses_value'] ?? 0);
$birdCost  = (float)($summary['livestock_cost'] ?? 0);
?>

<!-- CHARTS ROW -->
<div class="row g-4 mb-4">

    <!-- Revenue vs Expenses Line Chart -->
    <div class="col-lg-7">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Revenue vs Expenses Trend</h5>
                    <p class="text-muted small mb-0">Last 6 months performance</p>
                </div>
                <a href="<?= $base ?>/reports/profit-loss" class="btn btn-sm btn-outline-primary">Full Report</a>
            </div>
            <?php if (!empty($chartLabels)): ?>
            <canvas id="revenueExpenseChart" height="200"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                No monthly data yet. Add sales and expenses to see trends.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expense Breakdown Donut -->
    <div class="col-lg-5">
        <div class="admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Expense Breakdown</h5>
                    <p class="text-muted small mb-0">By category</p>
                </div>
            </div>
            <?php if (($feedCost + $medCost + $vacCost + $directExp + $birdCost) > 0): ?>
            <canvas id="expenseDonutChart" height="200"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                No expense data yet.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row g-4 mb-4">

    <!-- Poultry Stats Bar Chart -->
    <div class="col-lg-6">
        <div class="admin-card p-4 h-100">
            <h5 class="fw-bold mb-3">Flock Overview</h5>
            <div class="row g-3">
                <?php
                $flockStats = [
                    ['label'=>'Live Birds',    'value'=>(float)($summary['total_birds'] ?? 0),    'color'=>'#22c55e', 'icon'=>'bi-feather',      'suffix'=>'birds'],
                    ['label'=>'Total Eggs',    'value'=>(float)($summary['total_eggs'] ?? 0),     'color'=>'#f59e0b', 'icon'=>'bi-egg-fried',    'suffix'=>'eggs'],
                    ['label'=>'Mortality',     'value'=>(float)($summary['total_mortality'] ?? 0),'color'=>'#ef4444', 'icon'=>'bi-heart-pulse',  'suffix'=>'birds'],
                    ['label'=>'Feed Used',     'value'=>(float)($summary['total_feed_used_kg'] ?? 0),'color'=>'#3b82f6','icon'=>'bi-basket2',   'suffix'=>'kg'],
                    ['label'=>'Active Batches','value'=>(int)($summary['active_batches'] ?? 0),   'color'=>'#8b5cf6', 'icon'=>'bi-collection',   'suffix'=>'batches'],
                    ['label'=>'Customers',     'value'=>(int)($summary['customers'] ?? 0),        'color'=>'#10b981', 'icon'=>'bi-people',       'suffix'=>''],
                ];
                foreach ($flockStats as $stat):
                ?>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:40px;height:40px;background:<?= $stat['color'] ?>20;flex-shrink:0;">
                            <i class="bi <?= $stat['icon'] ?>" style="color:<?= $stat['color'] ?>;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="color:<?= $stat['color'] ?>"><?= number_format($stat['value']) ?> <?= $stat['suffix'] ?></div>
                            <div class="text-muted" style="font-size:11px;"><?= $stat['label'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Financial Health Gauges -->
    <div class="col-lg-6">
        <div class="admin-card p-4 h-100">
            <h5 class="fw-bold mb-3">Financial Health Indicators</h5>
            <?php
            $totalRevenue  = (float)($mt['total_revenue'] ?? 0);
            $totalExpenses = (float)($mt['total_expenses'] ?? 0);
            $totalAssets   = (float)($mt['total_assets'] ?? 0);
            $totalLiab     = (float)($mt['total_liabilities'] ?? 0);
            $netProfit     = $totalRevenue - $totalExpenses;
            $profitMarginPct = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
            $debtRatio     = $totalAssets > 0 ? ($totalLiab / $totalAssets) * 100 : 0;
            $liquidityR    = $totalLiab > 0 ? ($totalAssets / $totalLiab) : ($totalAssets > 0 ? 99 : 0);

            $indicators = [
                ['label'=>'Profit Margin',    'value'=>$profitMarginPct, 'max'=>100, 'good'=>15, 'warn'=>5,  'suffix'=>'%', 'fmt'=>'%.1f%%'],
                ['label'=>'Liquidity Ratio',  'value'=>min($liquidityR, 5)*20, 'max'=>100, 'good'=>40, 'warn'=>20, 'suffix'=>'x', 'display'=>number_format($liquidityR,2).'x'],
                ['label'=>'Debt Ratio',       'value'=>100-$debtRatio,  'max'=>100, 'good'=>60, 'warn'=>30, 'suffix'=>'%', 'display'=>number_format($debtRatio,1).'% debt'],
                ['label'=>'ROI',              'value'=>min(max($roi,0),100), 'max'=>100, 'good'=>20, 'warn'=>5, 'suffix'=>'%', 'display'=>number_format($roi,1).'%'],
            ];
            foreach ($indicators as $ind):
                $pct = min(100, max(0, $ind['value']));
                $barColor = $pct >= $ind['good'] ? '#22c55e' : ($pct >= $ind['warn'] ? '#f59e0b' : '#ef4444');
            ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold small"><?= $ind['label'] ?></span>
                    <span class="fw-bold small" style="color:<?= $barColor ?>"><?= $ind['display'] ?? number_format($ind['value'], 1) . $ind['suffix'] ?></span>
                </div>
                <div class="progress" style="height:10px;border-radius:5px;">
                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:5px;transition:width .6s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($chartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels  = <?= json_encode(array_values($chartLabels)) ?>;
    const revenue = <?= json_encode(array_values($chartRevenue)) ?>;
    const expense = <?= json_encode(array_values($chartExpense)) ?>;
    const net     = <?= json_encode(array_values($chartNet)) ?>;

    // Revenue vs Expenses Line Chart
    const ctx1 = document.getElementById('revenueExpenseChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Revenue', data: revenue, borderColor: '#22c55e', backgroundColor: '#22c55e20', fill: true, tension: 0.4, pointRadius: 4 },
                    { label: 'Expenses', data: expense, borderColor: '#ef4444', backgroundColor: '#ef444420', fill: true, tension: 0.4, pointRadius: 4 },
                    { label: 'Net Profit', data: net, borderColor: '#3b82f6', backgroundColor: 'transparent', borderDash: [5,5], tension: 0.4, pointRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: ctx => 'GHS ' + ctx.parsed.y.toLocaleString('en', {minimumFractionDigits:2}) } } },
                scales: { y: { ticks: { callback: v => 'GHS ' + v.toLocaleString() } } }
            }
        });
    }

    // Expense Donut
    const ctx2 = document.getElementById('expenseDonutChart');
    if (ctx2) {
        const expData = [<?= $feedCost ?>, <?= $medCost ?>, <?= $vacCost ?>, <?= $directExp ?>, <?= $birdCost ?>];
        const expLabels = ['Feed', 'Medication', 'Vaccination', 'Direct Expenses', 'Livestock Purchase'];
        const nonZero = expLabels.filter((_, i) => expData[i] > 0);
        const nonZeroData = expData.filter(v => v > 0);
        if (nonZeroData.length > 0) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: nonZero,
                    datasets: [{ data: nonZeroData, backgroundColor: ['#f59e0b','#ef4444','#22c55e','#3b82f6','#8b5cf6'], borderWidth: 2 }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => ctx.label + ': GHS ' + ctx.parsed.toLocaleString('en', {minimumFractionDigits:2}) } }
                    }
                }
            });
        }
    }
})();
</script>
<?php endif; ?>

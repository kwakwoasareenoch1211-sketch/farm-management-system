<?php
$totalSales        = (float)($totals['total_sales']         ?? 0);
$currentMonthSales = (float)($totals['current_month_sales'] ?? 0);
$todaySales        = (float)($totals['today_sales']         ?? 0);
$totalPaid         = (float)($totals['total_paid']          ?? 0);
$totalOutstanding  = (float)($totals['total_outstanding']   ?? 0);
$paidSales         = (float)($totals['paid_sales']          ?? 0);
$partialSales      = (float)($totals['partial_sales']       ?? 0);
$unpaidSales       = (float)($totals['unpaid_sales']        ?? 0);
$totalRecords      = (int)($totals['total_records']         ?? 0);

$debtPayoff   = $debtPayoff   ?? [];
$pricing      = $pricing      ?? [];
$projections  = $projections  ?? [];
$monthlyTrend = $monthlyTrend ?? [];
$growth       = $growth       ?? [];

$totalDebt       = (float)($debtPayoff['total_liabilities']   ?? 0);
$monthsToPayoff  = $debtPayoff['months_to_payoff']            ?? null;
$avgMonthlyNet   = (float)($debtPayoff['avg_monthly_net']     ?? 0);
$debtStatus      = $debtPayoff['status']                      ?? 'unknown';

$overallMargin   = (float)($pricing['overall_margin']         ?? 0);
$grossProfit     = (float)($pricing['gross_profit']           ?? 0);
$totalCost       = (float)($pricing['total_cost']             ?? 0);

$base = rtrim(BASE_URL, '/');

$debtStatusMap = [
    'debt_free'  => ['label'=>'Debt Free',    'class'=>'success'],
    'manageable' => ['label'=>'Manageable',   'class'=>'info'],
    'moderate'   => ['label'=>'Moderate Risk','class'=>'warning'],
    'high_risk'  => ['label'=>'High Risk',    'class'=>'danger'],
    'critical'   => ['label'=>'Critical',     'class'=>'danger'],
    'unknown'    => ['label'=>'No Data',      'class'=>'secondary'],
];
$ds = $debtStatusMap[$debtStatus] ?? $debtStatusMap['unknown'];
?>
<style>
.si-card{border-radius:20px;background:#fff;border:0;box-shadow:0 8px 28px rgba(15,23,42,.07);}
.si-hero{border-radius:22px;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1d4ed8 100%);color:#fff;padding:28px;}
.si-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;box-shadow:0 4px 14px rgba(15,23,42,.05);}
.si-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;}
.si-kpi .val{font-size:1.45rem;font-weight:700;margin-bottom:3px;}
.si-kpi .sub{font-size:11px;color:#94a3b8;}
.si-soft{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:13px 15px;margin-bottom:8px;}
.si-soft .lbl{font-size:12px;color:#64748b;margin-bottom:2px;}
.si-soft .val{font-size:1.1rem;font-weight:700;}
.strategy-card{border-radius:16px;padding:18px;border:2px solid;height:100%;}
.proj-row{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px;margin-bottom:10px;}
.urgency-high{border-left:4px solid #ef4444;}
.urgency-medium{border-left:4px solid #f59e0b;}
.urgency-low{border-left:4px solid #22c55e;}
.urgency-good{border-left:4px solid #3b82f6;}
.urgency-neutral{border-left:4px solid #94a3b8;}
.trend-bar{height:6px;border-radius:3px;background:#e2e8f0;overflow:hidden;}
.trend-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#3b82f6,#1d4ed8);}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-primary mb-2 px-3 py-2">Sales Intelligence Center</span>
        <h2 class="fw-bold mb-1">Sales Dashboard</h2>
        <p class="text-muted mb-0">Revenue projections, pricing strategy, debt payoff timeline, and growth intelligence.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/sales/create" class="btn btn-dark btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Sale</a>
        <a href="<?= $base ?>/financial" class="btn btn-outline-dark btn-sm"><i class="bi bi-cash-stack me-1"></i>Financial</a>
        <a href="<?= $base ?>/reports/sales" class="btn btn-outline-primary btn-sm"><i class="bi bi-bar-chart me-1"></i>Reports</a>
    </div>
</div>

<!-- Hero -->
<div class="si-hero mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <h5 class="fw-bold mb-1">Revenue Intelligence</h5>
            <p class="text-white-50 small mb-2">Live projections from your active batches. Prices, margins, and debt payoff auto-computed.</p>
            <span class="badge bg-<?= $ds['class'] ?> px-3 py-2">Debt Status: <?= $ds['label'] ?></span>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3"><div class="fs-4 fw-bold">GHS <?= number_format($todaySales, 2) ?></div><div class="small text-white-50">Today</div></div>
                <div class="col-6 col-md-3"><div class="fs-4 fw-bold">GHS <?= number_format($currentMonthSales, 2) ?></div><div class="small text-white-50">This Month</div></div>
                <div class="col-6 col-md-3"><div class="fs-4 fw-bold">GHS <?= number_format($totalSales, 2) ?></div><div class="small text-white-50">Total Revenue</div></div>
                <div class="col-6 col-md-3"><div class="fs-4 fw-bold <?= $overallMargin >= 20 ? 'text-success' : 'text-warning' ?>"><?= number_format($overallMargin, 1) ?>%</div><div class="small text-white-50">Profit Margin</div></div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="si-kpi" style="border-left:4px solid #22c55e;"><div class="lbl">Total Revenue</div><div class="val">GHS <?= number_format($totalSales, 2) ?></div><div class="sub">All recorded sales</div></div></div>
    <div class="col-6 col-md-3"><div class="si-kpi" style="border-left:4px solid #3b82f6;"><div class="lbl">Collected</div><div class="val">GHS <?= number_format($totalPaid, 2) ?></div><div class="sub">Cash in hand</div></div></div>
    <div class="col-6 col-md-3"><div class="si-kpi" style="border-left:4px solid #f59e0b;"><div class="lbl">Outstanding</div><div class="val">GHS <?= number_format($totalOutstanding, 2) ?></div><div class="sub">Receivables balance</div></div></div>
    <div class="col-6 col-md-3"><div class="si-kpi" style="border-left:4px solid <?= $grossProfit >= 0 ? '#22c55e' : '#ef4444' ?>;"><div class="lbl">Gross Profit</div><div class="val <?= $grossProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($grossProfit, 2) ?></div><div class="sub">Revenue minus all costs</div></div></div>
</div>

<!-- Pricing Intelligence + Debt Payoff -->
<div class="row g-4 mb-4">
    <!-- Pricing -->
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-tags text-primary me-2"></i>Smart Pricing Intelligence</h6>
            
            <!-- Market Reference -->
            <div class="alert alert-<?= ($pricing['price_source'] ?? 'defaults') === 'actual_sales' ? 'success' : 'info' ?> py-2 mb-3 small">
                <strong><?= ($pricing['price_source'] ?? 'defaults') === 'actual_sales' ? '✓ Learning from your sales' : 'Using default prices' ?>:</strong><br>
                🥚 Eggs: <?= number_format((float)($pricing['market_egg_price'] ?? 2.50), 2) ?> GHS per egg<?= ($pricing['has_egg_sales'] ?? false) ? ' (from your sales)' : ' (default: 2.50-3.00)' ?><br>
                🍗 Meat: <?= number_format((float)($pricing['market_meat_price_kg'] ?? 100), 2) ?> GHS per kg<?= ($pricing['has_meat_sales'] ?? false) ? ' (from your sales)' : ' (default: varies by size)' ?><br>
                <?php if (($pricing['has_meat_sales'] ?? false)): ?>
                Live bird: <?= number_format((float)($pricing['market_meat_price_bird'] ?? 200), 2) ?> GHS average
                <?php else: ?>
                Live bird: 150-300 GHS (1.5-3.0kg at 100 GHS/kg)
                <?php endif; ?>
            </div>
            
            <!-- Egg Pricing -->
            <div class="si-soft"><div class="lbl">Your Cost per Egg</div><div class="val">GHS <?= number_format((float)($pricing['cost_per_egg'] ?? 0), 4) ?></div></div>
            <div class="si-soft">
                <div class="lbl">Recommended Egg Price <span class="badge bg-success ms-1">+35% margin</span></div>
                <div class="val text-success">GHS <?= number_format((float)($pricing['rec_price_per_egg'] ?? 0), 4) ?></div>
                <div class="small text-muted mt-1">Market rate: GHS <?= number_format((float)($pricing['market_egg_price'] ?? 2.50), 4) ?>/egg<?= ($pricing['has_egg_sales'] ?? false) ? ' (learned from your sales)' : '' ?></div>
            </div>
            
            <!-- Meat Pricing per kg -->
            <div class="si-soft"><div class="lbl">Your Cost per kg (meat)</div><div class="val">GHS <?= number_format((float)($pricing['cost_per_kg'] ?? 0), 2) ?></div></div>
            <div class="si-soft">
                <div class="lbl">Recommended Meat Price/kg <span class="badge bg-success ms-1">+35% margin</span></div>
                <div class="val text-success">GHS <?= number_format((float)($pricing['rec_price_per_kg'] ?? 0), 2) ?></div>
                <div class="small text-muted mt-1">Market rate: GHS <?= number_format((float)($pricing['market_meat_price_kg'] ?? 100), 2) ?>/kg<?= ($pricing['has_meat_sales'] ?? false) ? ' (learned from your sales)' : '' ?></div>
            </div>
            
            <!-- Meat Pricing per bird -->
            <div class="si-soft"><div class="lbl">Your Cost per Bird</div><div class="val">GHS <?= number_format((float)($pricing['cost_per_bird'] ?? 0), 2) ?></div></div>
            <div class="si-soft">
                <div class="lbl">Recommended Bird Price <span class="badge bg-success ms-1">+35% margin</span></div>
                <div class="val text-success">GHS <?= number_format((float)($pricing['rec_price_per_bird'] ?? 0), 2) ?></div>
                <div class="small text-muted mt-1">
                    <?php if (($pricing['has_meat_sales'] ?? false)): ?>
                        Market average: GHS <?= number_format((float)($pricing['market_meat_price_bird'] ?? 200), 2) ?>/bird (from your sales)
                    <?php else: ?>
                        Market range: 150-300 GHS depending on size (1.5-3.0kg)
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-3 p-3 rounded-3 <?= $overallMargin >= 30 ? 'bg-success' : ($overallMargin >= 15 ? 'bg-warning' : 'bg-danger') ?> bg-opacity-10">
                <div class="small fw-bold">Overall Margin: <?= number_format($overallMargin, 1) ?>%</div>
                <div class="small text-muted"><?= $overallMargin >= 30 ? 'Healthy — keep pricing consistent.' : ($overallMargin >= 15 ? 'Moderate — consider small price increases.' : 'Low — review pricing urgently.') ?></div>
            </div>
        </div>
    </div>

    <!-- Debt Payoff -->
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-credit-card text-danger me-2"></i>Debt Payoff Timeline</h6>
            <?php if ($totalDebt <= 0): ?>
                <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i>No outstanding debt. Business is debt-free.</div>
            <?php else: ?>
                <div class="si-soft"><div class="lbl">Total Liabilities</div><div class="val text-danger">GHS <?= number_format($totalDebt, 2) ?></div></div>
                <div class="si-soft"><div class="lbl">Avg Monthly Net Revenue</div><div class="val">GHS <?= number_format($avgMonthlyNet, 2) ?></div></div>
                <div class="si-soft"><div class="lbl">Estimated Payoff</div><div class="val <?= $monthsToPayoff !== null && $monthsToPayoff <= 12 ? 'text-success' : 'text-warning' ?>"><?= $monthsToPayoff !== null ? $monthsToPayoff . ' months' : 'Insufficient revenue' ?></div></div>
                <div class="si-soft"><div class="lbl">Revenue Needed (12-month plan)</div><div class="val">GHS <?= number_format((float)($debtPayoff['revenue_needed_12m'] ?? 0), 2) ?>/mo</div></div>
                <div class="si-soft"><div class="lbl">Revenue Needed (6-month plan)</div><div class="val text-warning">GHS <?= number_format((float)($debtPayoff['revenue_needed_6m'] ?? 0), 2) ?>/mo</div></div>
                <div class="mt-3 p-3 rounded-3 bg-<?= $ds['class'] ?> bg-opacity-10">
                    <div class="small fw-bold">Status: <?= $ds['label'] ?></div>
                    <div class="small text-muted">
                        <?php if ($debtStatus === 'critical'): ?>Revenue is not covering expenses. Immediate action needed.
                        <?php elseif ($debtStatus === 'high_risk'): ?>Debt payoff will take over 18 months. Increase revenue or cut costs.
                        <?php elseif ($debtStatus === 'moderate'): ?>On track but slow. Aim to increase monthly net by 20%.
                        <?php else: ?>Good trajectory. Maintain current revenue levels.<?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Growth Strategies -->
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow text-success me-2"></i>Revenue Strategies</h6>
            <?php if (!empty($growth['strategies'])): ?>
                <?php foreach ($growth['strategies'] as $s): ?>
                    <div class="si-soft">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small"><?= $s['icon'] ?> <?= htmlspecialchars($s['title']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($s['action']) ?></div>
                            </div>
                            <span class="badge bg-<?= $s['color'] ?> ms-2">GHS <?= number_format((float)$s['value'], 2) ?></span>
                        </div>
                        <div class="small text-muted mt-1"><?= htmlspecialchars($s['label']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted small">Add batches and sales records to generate strategy recommendations.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Batch Projections -->
<?php if (!empty($projections)): ?>
<div class="si-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-calculator text-info me-2"></i>Batch Revenue Projections & Break-Even Analysis</h6>
    <div class="alert alert-<?= ($pricing['price_source'] ?? 'defaults') === 'actual_sales' ? 'success' : 'primary' ?> mb-3 py-2 small">
        <strong>Smart Analysis:</strong> 
        <?php if (($pricing['price_source'] ?? 'defaults') === 'actual_sales'): ?>
            Prices learned from your actual sales history. Eggs at <?= number_format((float)($pricing['market_egg_price'] ?? 2.50), 2) ?> GHS/egg, Meat at <?= number_format((float)($pricing['market_meat_price_kg'] ?? 100), 2) ?> GHS/kg.
        <?php else: ?>
            Using default prices: Eggs 2.50-3.00 GHS/egg, Meat varies by size (150-300 GHS per bird). System will learn from your sales.
        <?php endif; ?>
        Recommendations include 35% profit margin.
    </div>
    <div class="table-responsive">
        <table class="table align-middle small">
            <thead class="table-light">
                <tr>
                    <th>Batch</th>
                    <th>Purpose</th>
                    <th>Birds</th>
                    <th>Total Cost</th>
                    <th>Already Sold</th>
                    <th>Remaining Cost</th>
                    <th>Min Price/Egg</th>
                    <th>Rec Price/Egg</th>
                    <th>Min Price/kg</th>
                    <th>Rec Price/kg</th>
                    <th>Proj Egg Rev/mo</th>
                    <th>Proj Meat Rev</th>
                    <th>Strategy</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projections as $p): ?>
                    <?php $urgClass = 'urgency-' . ($p['strategy']['urgency'] ?? 'neutral'); ?>
                    <tr class="<?= $urgClass ?>">
                        <td><strong><?= htmlspecialchars($p['batch_code']) ?></strong><?php if ($p['batch_name']): ?><br><small class="text-muted"><?= htmlspecialchars($p['batch_name']) ?></small><?php endif; ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['purpose']) ?></span></td>
                        <td><?= number_format($p['birds']) ?></td>
                        <td>GHS <?= number_format($p['total_cost'], 2) ?></td>
                        <td class="text-success">GHS <?= number_format($p['already_sold'], 2) ?></td>
                        <td class="<?= $p['remaining_cost'] > 0 ? 'text-danger' : 'text-success' ?>">GHS <?= number_format($p['remaining_cost'], 2) ?></td>
                        <td>GHS <?= number_format($p['min_price_per_egg'], 4) ?></td>
                        <td class="text-success fw-bold">GHS <?= number_format($p['rec_price_per_egg'], 4) ?></td>
                        <td>GHS <?= number_format($p['min_price_per_kg'], 2) ?></td>
                        <td class="text-success fw-bold">GHS <?= number_format($p['rec_price_per_kg'], 2) ?></td>
                        <td class="text-warning fw-bold">GHS <?= number_format($p['proj_egg_rev_month'], 2) ?></td>
                        <td class="text-danger fw-bold">GHS <?= number_format($p['proj_meat_revenue'], 2) ?></td>
                        <td>
                            <?php $u = $p['strategy']['urgency'] ?? 'neutral'; $uc = ['high'=>'danger','medium'=>'warning','good'=>'success','low'=>'secondary','neutral'=>'secondary'][$u] ?? 'secondary'; ?>
                            <span class="badge bg-<?= $uc ?>"><?= htmlspecialchars($p['strategy']['recommendation']) ?></span>
                            <div style="font-size:10px;color:#64748b;margin-top:3px;"><?= htmlspecialchars($p['strategy']['reason']) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Monthly Trend -->
<?php if (!empty($monthlyTrend)): ?>
<div class="si-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-primary me-2"></i>Monthly Revenue Trend (Last 12 Months)</h6>
    <?php
    $maxRev = max(array_column($monthlyTrend, 'revenue') ?: [1]);
    ?>
    <div class="table-responsive">
        <table class="table align-middle small">
            <thead class="table-light"><tr><th>Month</th><th>Revenue</th><th>Collected</th><th>Transactions</th><th>Growth</th><th>Bar</th></tr></thead>
            <tbody>
                <?php foreach ($monthlyTrend as $t): ?>
                    <?php $pct = $maxRev > 0 ? ((float)$t['revenue'] / $maxRev) * 100 : 0; ?>
                    <tr>
                        <td><?= htmlspecialchars($t['month']) ?></td>
                        <td class="fw-bold">GHS <?= number_format((float)$t['revenue'], 2) ?></td>
                        <td>GHS <?= number_format((float)$t['collected'], 2) ?></td>
                        <td><?= number_format((int)$t['transactions']) ?></td>
                        <td>
                            <?php $g = (float)$t['growth_pct']; ?>
                            <span class="badge bg-<?= $g > 0 ? 'success' : ($g < 0 ? 'danger' : 'secondary') ?>">
                                <?= $g > 0 ? '+' : '' ?><?= number_format($g, 1) ?>%
                            </span>
                        </td>
                        <td style="width:120px;"><div class="trend-bar"><div class="trend-fill" style="width:<?= $pct ?>%"></div></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Payment Status + Sales by Type + Top Customers -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3">Payment Status</h6>
            <div class="si-soft"><div class="lbl">Paid</div><div class="val text-success">GHS <?= number_format($paidSales, 2) ?></div></div>
            <div class="si-soft"><div class="lbl">Partial</div><div class="val text-warning">GHS <?= number_format($partialSales, 2) ?></div></div>
            <div class="si-soft"><div class="lbl">Unpaid</div><div class="val text-danger">GHS <?= number_format($unpaidSales, 2) ?></div></div>
            <?php if ($totalOutstanding > 0): ?>
                <div class="alert alert-warning py-2 small mt-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i>GHS <?= number_format($totalOutstanding, 2) ?> outstanding. Follow up with customers.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3">Sales by Type</h6>
            <?php if (!empty($salesByType)): foreach ($salesByType as $t): ?>
                <div class="si-soft">
                    <div class="d-flex justify-content-between">
                        <div class="lbl"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$t['sale_type']))) ?></div>
                        <span class="badge bg-secondary"><?= $t['total_records'] ?> records</span>
                    </div>
                    <div class="val">GHS <?= number_format((float)$t['total_amount'], 2) ?></div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-muted small">No sales recorded yet.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="si-card p-4 h-100">
            <h6 class="fw-bold mb-3">Top Customers</h6>
            <?php if (!empty($topCustomers)): foreach ($topCustomers as $c): ?>
                <div class="si-soft">
                    <div class="d-flex justify-content-between">
                        <div class="lbl"><?= htmlspecialchars($c['customer_name']) ?></div>
                        <span class="badge bg-primary"><?= $c['total_sales_count'] ?> sales</span>
                    </div>
                    <div class="val">GHS <?= number_format((float)$c['total_sales_amount'], 2) ?></div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-muted small">No customer sales yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Sales Table -->
<div class="si-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-1">Recent Sales Records</h6>
            <p class="text-muted small mb-0">All entries auto-reflect on financial and reports pages.</p>
        </div>
        <a href="<?= $base ?>/sales/create" class="btn btn-dark btn-sm"><i class="bi bi-plus me-1"></i>Add Sale</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle small">
            <thead class="table-light">
                <tr><th>Date</th><th>Invoice</th><th>Type</th><th>Item</th><th>Batch</th><th>Customer</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): foreach ($records as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['sale_date']) ?></td>
                        <td><?= htmlspecialchars($r['invoice_no'] ?? '-') ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$r['sale_type']))) ?></td>
                        <td><?= htmlspecialchars($r['item_name']) ?></td>
                        <td><?= htmlspecialchars(($r['batch_code'] ?? '-') . (!empty($r['batch_name']) ? ' — '.$r['batch_name'] : '')) ?></td>
                        <td><?= htmlspecialchars($r['customer_name'] ?? '-') ?></td>
                        <td class="fw-bold">GHS <?= number_format((float)$r['total_amount'], 2) ?></td>
                        <td>GHS <?= number_format((float)$r['amount_paid'], 2) ?></td>
                        <td><?php
                            $sc = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$r['payment_status'] ?? ''] ?? 'secondary';
                        ?><span class="badge bg-<?= $sc ?>"><?= ucfirst($r['payment_status'] ?? '') ?></span></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/sales/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= $base ?>/sales/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this sale?')">Del</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No sales records yet. <a href="<?= $base ?>/sales/create">Add your first sale →</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

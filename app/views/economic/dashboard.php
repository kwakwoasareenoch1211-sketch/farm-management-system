<?php
// ── Pull all data from controller ─────────────────────────────────────────
$mt  = $monitorTotals  ?? [];
$mm  = $monitorMonth   ?? [];
$ba  = $businessAnalysis ?? [];
$base= rtrim(BASE_URL, '/');

$totalCapital     = (float)($totalCapital     ?? 0);
$totalRevenue     = (float)($totalRevenue     ?? 0);
$totalExpenses    = (float)($totalExpenses    ?? 0);
$totalAssets      = (float)($totalAssets      ?? 0);
$totalLiabilities = (float)($totalLiabilities ?? 0);
$totalInvestments = (float)($totalInvestments ?? 0);
$retainedProfit   = (float)($retainedProfit   ?? 0);
$ownerEquity      = (float)($ownerEquity      ?? 0);
$netWorth         = (float)($netWorth         ?? 0);
$workingCapital   = (float)($workingCapital   ?? 0);
$profitMargin     = (float)($profitMargin     ?? 0);
$debtRatio        = (float)($debtRatio        ?? 0);
$capitalROI       = (float)($capitalROI       ?? 0);
$capitalEfficiency= (float)($capitalEfficiency?? 0);
$expenseRatio     = (float)($expenseRatio     ?? 0);
$capitalAdequacy  = (float)($capitalAdequacy  ?? 0);
$monthRevenue     = (float)($monthRevenue     ?? 0);
$monthExpense     = (float)($monthExpense     ?? 0);
$monthNet         = (float)($monthNet         ?? 0);
$liquidityRatio   = (float)($liquidityRatio   ?? 0);
$roi              = (float)($roi              ?? 0);
$healthScore      = (int)($healthScore        ?? 0);
$healthLabel      = $healthLabel              ?? 'N/A';
$healthClass      = $healthClass              ?? 'secondary';
$trendSignal      = $trendSignal              ?? 'Flat';
$businessStage    = $businessStage            ?? 'Unknown';
$goingConcernStatus  = $goingConcernStatus    ?? 'N/A';
$goingConcernClass   = $goingConcernClass     ?? 'secondary';
$goingConcernMessage = $goingConcernMessage   ?? '';
$decisionRecommendation = $decisionRecommendation ?? 'N/A';
$decisionClass    = $decisionClass            ?? 'secondary';
$decisionReason   = $decisionReason           ?? '';
$decisions        = $decisions                ?? [];
$risks            = $risks                    ?? [];
$strengths        = $strengths                ?? [];
$recommendations  = $recommendations         ?? [];
$lossMakingBatches= $lossMakingBatches        ?? [];
$strongBatches    = $strongBatches            ?? [];
$topBatch         = $topBatch                 ?? null;
$worstBatch       = $worstBatch               ?? null;
$monthlyCombined  = $monthlyCombined          ?? [];
$diff             = $diff                     ?? [];

$chartLabels  = array_column($monthlyCombined, 'month_label');
$chartRevenue = array_map(fn($m) => (float)($m['sales_revenue'] ?? 0), $monthlyCombined);
$chartExpense = array_map(fn($m) => (float)($m['total_expense']  ?? 0), $monthlyCombined);
$chartNet     = array_map(fn($m) => (float)($m['net_position']   ?? 0), $monthlyCombined);

$monthMargin  = $monthRevenue > 0 ? ($monthNet / $monthRevenue) * 100 : 0;
?>
<style>
.eco-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.eco-hero{border-radius:22px;background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#334155 100%);color:#fff;padding:28px;}
.eco-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;box-shadow:0 4px 16px rgba(15,23,42,.05);}
.eco-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;}
.eco-kpi .val{font-size:1.35rem;font-weight:700;margin-bottom:3px;}
.eco-kpi .sub{font-size:11px;color:#94a3b8;}
.eco-soft{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px 16px;}
.eco-insight{border-left:4px solid #16a34a;background:#f0fdf4;border-radius:12px;padding:12px 14px;margin-bottom:8px;}
.eco-risk{border-left:4px solid #dc2626;background:#fef2f2;border-radius:12px;padding:12px 14px;margin-bottom:8px;}
.eco-tip{border-left:4px solid #2563eb;background:#eff6ff;border-radius:12px;padding:12px 14px;margin-bottom:8px;}
.score-ring{width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:900;margin:0 auto 8px;border:4px solid;}
.dec-card{border-radius:14px;border:1px solid #e2e8f0;padding:16px;margin-bottom:10px;background:#fff;}
.fin-bar{height:8px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-top:6px;}
.fin-bar-fill{height:100%;border-radius:999px;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Economic Intelligence · <?= htmlspecialchars($businessStage) ?> Stage</span>
        <h2 class="fw-bold mb-1">Economic Dashboard</h2>
        <p class="text-muted mb-0">Full financial monitoring — capital, revenue, expenses, assets, liabilities, and decision intelligence.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/financial"       class="btn btn-outline-dark btn-sm">Financial Dashboard</a>
        <a href="<?= $base ?>/business-health" class="btn btn-outline-dark btn-sm">Business Health</a>
        <a href="<?= $base ?>/going-concern"   class="btn btn-outline-dark btn-sm">Going Concern</a>
        <a href="<?= $base ?>/decision-support" class="btn btn-dark btn-sm">Decision Support</a>
    </div>
</div>

<!-- HERO: Accounting equation + health score -->
<div class="eco-hero mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-3 text-center">
            <div class="score-ring border-<?= $healthClass ?> text-<?= $healthClass ?>"><?= $healthScore ?></div>
            <div class="fw-bold small">Health Score / 100</div>
            <span class="badge bg-<?= $healthClass ?> px-3 py-2 mt-1 d-inline-block"><?= htmlspecialchars($healthLabel) ?></span>
            <div class="small text-white-50 mt-1"><?= htmlspecialchars($businessStage) ?> Stage</div>
        </div>
        <div class="col-lg-9">
            <div class="small text-white-50 mb-2 fw-semibold">ACCOUNTING EQUATION: Assets = Liabilities + Owner's Equity</div>
            <div class="row g-3 text-center">
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold text-primary">GHS <?= number_format($totalCapital,2) ?></div><div class="small text-white-50">Capital</div></div>
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold text-success">GHS <?= number_format($totalRevenue,2) ?></div><div class="small text-white-50">Revenue</div></div>
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold text-danger">GHS <?= number_format($totalExpenses,2) ?></div><div class="small text-white-50">Expenses</div></div>
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold text-info">GHS <?= number_format($totalAssets,2) ?></div><div class="small text-white-50">Assets</div></div>
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold text-warning">GHS <?= number_format($totalLiabilities,2) ?></div><div class="small text-white-50">Liabilities</div></div>
                <div class="col-4 col-md-2"><div class="fs-5 fw-bold <?= $netWorth >= 0 ? 'text-success' : 'text-warning' ?>">GHS <?= number_format($netWorth,2) ?></div><div class="small text-white-50">Net Worth</div></div>
            </div>
            <hr class="border-secondary my-3">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3"><div class="fw-bold">GHS <?= number_format($monthRevenue,2) ?></div><div class="small text-white-50">Month Revenue</div></div>
                <div class="col-6 col-md-3"><div class="fw-bold">GHS <?= number_format($monthExpense,2) ?></div><div class="small text-white-50">Month Expenses</div></div>
                <div class="col-6 col-md-3"><div class="fw-bold <?= $monthNet >= 0 ? 'text-success' : 'text-warning' ?>">GHS <?= number_format($monthNet,2) ?></div><div class="small text-white-50">Month Net</div></div>
                <div class="col-6 col-md-3"><div class="fw-bold"><?= htmlspecialchars($trendSignal) ?></div><div class="small text-white-50">Trend Signal</div></div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #22c55e;"><div class="lbl">Retained Profit</div><div class="val <?= $retainedProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($retainedProfit,2) ?></div><div class="sub">Revenue − Expenses</div></div></div>
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #3b82f6;"><div class="lbl">Owner's Equity</div><div class="val text-primary">GHS <?= number_format($ownerEquity,2) ?></div><div class="sub">Capital + Profit</div></div></div>
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #06b6d4;"><div class="lbl">Working Capital</div><div class="val <?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($workingCapital,2) ?></div><div class="sub">Assets − Liabilities</div></div></div>
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #8b5cf6;"><div class="lbl">Profit Margin</div><div class="val <?php 
        if ($profitMargin > 0) {
            echo 'text-success';
        } elseif ($profitMargin < 0) {
            echo 'text-danger';
        } else {
            echo 'text-warning';
        }
    ?>"><?= number_format($profitMargin,1) ?>%</div><div class="sub">Net / Revenue</div></div></div>
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #f59e0b;"><div class="lbl">Capital ROI</div><div class="val <?= $capitalROI >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($capitalROI,1) ?>%</div><div class="sub">Profit / Capital</div></div></div>
    <div class="col-6 col-md-2"><div class="eco-kpi" style="border-left:4px solid #ef4444;"><div class="lbl">Debt Ratio</div><div class="val <?php 
        $debtThreshold = $totalAssets > 0 ? 50 : 0;
        echo $debtRatio < $debtThreshold ? 'text-success' : 'text-danger';
    ?>"><?= number_format($debtRatio,1) ?>%</div><div class="sub">Liabilities / Assets</div></div></div>
</div>

<!-- FINANCIAL MONITORING PANEL: Capital vs everything -->
<div class="eco-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-primary"></i>Financial Monitor — Capital vs All Categories</h6>
        <a href="<?= $base ?>/financial" class="btn btn-outline-primary btn-sm">Financial Dashboard</a>
    </div>
    <div class="row g-3">
        <!-- Capital vs Revenue -->
        <?php $cvr = $diff['capital_vs_revenue'] ?? []; ?>
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">Capital vs Revenue</span>
                    <span class="badge bg-<?php 
                        if (($cvr['status']??'') === 'High Return') {
                            echo 'success';
                        } elseif (($cvr['status']??'') === 'Moderate Return') {
                            echo 'warning';
                        } else {
                            echo 'danger';
                        }
                    ?>"><?= htmlspecialchars($cvr['status'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary">Capital: GHS <?= number_format((float)($cvr['capital']??0),2) ?></span>
                    <span class="text-success">Revenue: GHS <?= number_format((float)($cvr['revenue']??0),2) ?></span>
                </div>
                <div class="fin-bar"><div class="fin-bar-fill bg-success" style="width:<?= min(100, $capitalEfficiency * 50) ?>%"></div></div>
                <div class="small text-muted mt-1">Efficiency: <?= number_format($capitalEfficiency,2) ?>x per GHS capital</div>
            </div>
        </div>
        <!-- Capital vs Expenses -->
        <?php $cve = $diff['capital_vs_expenses'] ?? []; ?>
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">Capital vs Expenses</span>
                    <span class="badge bg-<?php 
                        if (($cve['status']??'') === 'Efficient') {
                            echo 'success';
                        } elseif (($cve['status']??'') === 'Moderate') {
                            echo 'warning';
                        } else {
                            echo 'danger';
                        }
                    ?>"><?= htmlspecialchars($cve['status'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary">Capital: GHS <?= number_format((float)($cve['capital']??0),2) ?></span>
                    <span class="text-danger">Expenses: GHS <?= number_format((float)($cve['expenses']??0),2) ?></span>
                </div>
                <div class="fin-bar"><div class="fin-bar-fill bg-danger" style="width:<?= min(100, $expenseRatio) ?>%"></div></div>
                <div class="small text-muted mt-1">Expense ratio: <?= number_format($expenseRatio,1) ?>% of revenue</div>
            </div>
        </div>
        <!-- Capital vs Liabilities -->
        <?php $cvl = $diff['capital_vs_liability'] ?? []; ?>
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">Capital vs Liabilities</span>
                    <span class="badge bg-<?= ($cvl['status']??'') === 'Healthy' ? 'success' : 'danger' ?>"><?= htmlspecialchars($cvl['status'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary">Capital: GHS <?= number_format((float)($cvl['capital']??0),2) ?></span>
                    <span class="text-warning">Debt: GHS <?= number_format((float)($cvl['liability']??0),2) ?></span>
                </div>
                <?php 
                $capSum = ($cvl['capital']??0) + ($cvl['liability']??0);
                $capPct = $capSum > 0 ? (($cvl['capital']??0) / $capSum) * 100 : (($cvl['capital']??0) > 0 ? 100 : 0);
                ?>
                <div class="fin-bar"><div class="fin-bar-fill bg-primary" style="width:<?= min(100,$capPct) ?>%"></div></div>
                <div class="small text-muted mt-1">Debt/Capital ratio: <?= number_format((float)($cvl['ratio']??0),2) ?>x</div>
            </div>
        </div>
        <!-- Capital vs Assets -->
        <?php $cva = $diff['capital_vs_assets'] ?? []; ?>
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">Capital vs Assets</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary">Equity-funded: <?= number_format((float)($cva['funded_by_capital_pct']??0),1) ?>%</span>
                    <span class="text-warning">Debt-funded: <?= number_format((float)($cva['funded_by_debt_pct']??0),1) ?>%</span>
                </div>
                <div class="fin-bar"><div class="fin-bar-fill bg-primary" style="width:<?= min(100,(float)($cva['funded_by_capital_pct']??0)) ?>%"></div></div>
                <div class="small text-muted mt-1">Total Assets: GHS <?= number_format($totalAssets,2) ?></div>
            </div>
        </div>
        <!-- Capital vs Investments -->
        <?php $cvi = $diff['capital_vs_investment'] ?? []; ?>
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold">Capital vs Investments</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-info">Deployed: <?= number_format((float)($cvi['deployed_pct']??0),1) ?>%</span>
                    <span class="text-muted">Idle: GHS <?= number_format((float)($cvi['idle_capital']??0),2) ?></span>
                </div>
                <div class="fin-bar"><div class="fin-bar-fill bg-info" style="width:<?= min(100,(float)($cvi['deployed_pct']??0)) ?>%"></div></div>
                <div class="small text-muted mt-1">Investments: GHS <?= number_format($totalInvestments,2) ?></div>
            </div>
        </div>
        <!-- Liquidity + Solvency -->
        <div class="col-md-4">
            <div class="eco-soft">
                <div class="small fw-semibold mb-2">Liquidity &amp; Solvency</div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Liquidity Ratio</span>
                    <span class="fw-bold <?= $liquidityRatio >= 1 ? 'text-success' : 'text-danger' ?>"><?= number_format($liquidityRatio,2) ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Debt to Equity</span>
                    <span class="fw-bold"><?= number_format($debtRatio,1) ?>%</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Capital Adequacy</span>
                    <span class="fw-bold <?php 
                        $adequacyThreshold = $totalAssets > 0 ? ($totalAssets / max(1, $totalCapital)) * 100 : 0;
                        echo $capitalAdequacy > $adequacyThreshold ? 'text-success' : 'text-warning';
                    ?>"><?= number_format($capitalAdequacy,1) ?>%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DECISION ENGINE -->
<div class="eco-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Decision Engine — Based on Financial Monitor</h6>
        <div class="d-flex gap-2">
            <span class="badge bg-<?= $decisionClass ?> px-3 py-2"><?= htmlspecialchars($decisionRecommendation) ?></span>
            <a href="<?= $base ?>/reports/decisions" class="btn btn-outline-dark btn-sm">Full Report</a>
        </div>
    </div>
    <div class="alert alert-<?= $decisionClass ?> mb-3">
        <strong><?= htmlspecialchars($decisionRecommendation) ?>:</strong> <?= htmlspecialchars($decisionReason) ?>
    </div>
    <?php
    $priorityOrder = ['high'=>0,'medium'=>1,'low'=>2];
    usort($decisions, fn($a,$b) => ($priorityOrder[$a['priority']]??9) <=> ($priorityOrder[$b['priority']]??9));
    foreach ($decisions as $d):
    ?>
        <div class="dec-card">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-<?= $d['type'] ?>"><?= strtoupper($d['priority']) ?></span>
                    <span class="fw-semibold small"><?= htmlspecialchars($d['title']) ?></span>
                </div>
                <a href="<?= $base . htmlspecialchars($d['link']) ?>" class="btn btn-<?= $d['type'] ?> btn-sm">Act</a>
            </div>
            <div class="small text-muted mb-1"><strong>Why:</strong> <?= htmlspecialchars($d['reason']) ?></div>
            <div class="small"><strong>Action:</strong> <?= htmlspecialchars($d['action']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Chart + Strategic Signals -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Monthly Revenue, Expenses &amp; Net Trend</h6>
                <a href="<?= $base ?>/reports/profit-loss" class="btn btn-outline-dark btn-sm">Full Report</a>
            </div>
            <?php if (!empty($chartLabels)): ?>
                <div style="position:relative;min-height:280px;"><canvas id="ecoTrendChart"></canvas></div>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="bi bi-bar-chart fs-1 d-block mb-3 opacity-25"></i><p>No monthly data yet.</p></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <h6 class="fw-bold mb-3">Going Concern &amp; Strategic Signals</h6>
            <div class="eco-insight">
                <div class="fw-semibold small mb-1">Going Concern: <span class="badge bg-<?= $goingConcernClass ?>"><?= htmlspecialchars($goingConcernStatus) ?></span></div>
                <div class="small text-muted"><?= htmlspecialchars($goingConcernMessage) ?></div>
            </div>
            <div class="eco-soft mb-2">
                <div class="small fw-semibold mb-1">Month Performance</div>
                <div class="d-flex justify-content-between small"><span class="text-muted">Revenue</span><span class="text-success fw-bold">GHS <?= number_format($monthRevenue,2) ?></span></div>
                <div class="d-flex justify-content-between small"><span class="text-muted">Expenses</span><span class="text-danger fw-bold">GHS <?= number_format($monthExpense,2) ?></span></div>
                <div class="d-flex justify-content-between small"><span class="text-muted">Net</span><span class="fw-bold <?= $monthNet >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($monthNet,2) ?></span></div>
                <div class="d-flex justify-content-between small"><span class="text-muted">Margin</span><span class="fw-bold"><?= number_format($monthMargin,1) ?>%</span></div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="<?= $base ?>/reports/business-health" class="btn btn-outline-dark btn-sm w-100">Health Report</a>
                <a href="<?= $base ?>/reports/decisions" class="btn btn-dark btn-sm w-100">Decisions</a>
            </div>
        </div>
    </div>
</div>

<!-- Strengths / Risks / Recommendations -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Strengths</h6>
                <span class="badge bg-success">Positive</span>
            </div>
            <?php if (!empty($strengths)): ?>
                <?php foreach ($strengths as $s): ?><div class="eco-insight small"><?= htmlspecialchars($s) ?></div><?php endforeach; ?>
            <?php else: ?><p class="text-muted small">No strengths detected yet.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Risk Areas</h6>
                <span class="badge bg-danger">Risk</span>
            </div>
            <?php if (!empty($risks)): ?>
                <?php foreach ($risks as $r): ?><div class="eco-risk small"><?= htmlspecialchars($r) ?></div><?php endforeach; ?>
            <?php else: ?><p class="text-muted small">No major risks detected.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recommendations</h6>
                <span class="badge bg-warning text-dark">Action</span>
            </div>
            <?php if (!empty($recommendations)): ?>
                <?php foreach ($recommendations as $rec): ?><div class="eco-tip small"><?= htmlspecialchars($rec) ?></div><?php endforeach; ?>
            <?php else: ?><p class="text-muted small">No actions suggested yet.</p><?php endif; ?>
        </div>
    </div>
</div>

<!-- Top/Worst Batch + Batch Summary -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Top Batch</h6>
                <span class="badge bg-success">Strength</span>
            </div>
            <?php if (!empty($topBatch)): ?>
                <div class="eco-soft">
                    <div class="fw-bold mb-2"><?= htmlspecialchars(($topBatch['batch_code']??'-').(!empty($topBatch['batch_name'])?' — '.$topBatch['batch_name']:'')) ?></div>
                    <div class="row g-2 text-center">
                        <div class="col-6"><div class="small text-muted">Gross Profit</div><div class="fw-bold text-success small">GHS <?= number_format((float)($topBatch['gross_profit']??0),2) ?></div></div>
                        <div class="col-6"><div class="small text-muted">Margin</div><div class="fw-bold small"><?= number_format((float)($topBatch['profit_margin']??0),1) ?>%</div></div>
                    </div>
                </div>
                <a href="<?= $base ?>/batches/view?id=<?= (int)($topBatch['id']??0) ?>" class="btn btn-outline-success btn-sm w-100 mt-2">View Batch</a>
            <?php else: ?><p class="text-muted small">No batch data yet.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Weakest Batch</h6>
                <span class="badge bg-danger">Attention</span>
            </div>
            <?php if (!empty($worstBatch)): ?>
                <div class="eco-soft">
                    <div class="fw-bold mb-2"><?= htmlspecialchars(($worstBatch['batch_code']??'-').(!empty($worstBatch['batch_name'])?' — '.$worstBatch['batch_name']:'')) ?></div>
                    <div class="row g-2 text-center">
                        <div class="col-6"><div class="small text-muted">Gross Profit</div><div class="fw-bold <?= (float)($worstBatch['gross_profit']??0) < 0 ? 'text-danger' : 'text-success' ?> small">GHS <?= number_format((float)($worstBatch['gross_profit']??0),2) ?></div></div>
                        <div class="col-6"><div class="small text-muted">Margin</div><div class="fw-bold small"><?= number_format((float)($worstBatch['profit_margin']??0),1) ?>%</div></div>
                    </div>
                </div>
                <a href="<?= $base ?>/batches/view?id=<?= (int)($worstBatch['id']??0) ?>" class="btn btn-outline-danger btn-sm w-100 mt-2">Review Batch</a>
            <?php else: ?><p class="text-muted small">No batch data yet.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card p-4 h-100">
            <h6 class="fw-bold mb-3">Batch Summary</h6>
            <div class="d-flex justify-content-between border-bottom py-2 small"><span class="text-muted">Profitable Batches</span><span class="fw-bold text-success"><?= count($strongBatches) ?></span></div>
            <div class="d-flex justify-content-between border-bottom py-2 small"><span class="text-muted">Loss-Making Batches</span><span class="fw-bold text-danger"><?= count($lossMakingBatches) ?></span></div>
            <div class="d-flex justify-content-between border-bottom py-2 small"><span class="text-muted">Total Investments</span><span class="fw-bold">GHS <?= number_format($totalInvestments,2) ?></span></div>
            <div class="d-flex justify-content-between py-2 small"><span class="text-muted">Capital Deployed</span><span class="fw-bold"><?= number_format((float)($diff['capital_vs_investment']['deployed_pct']??0),1) ?>%</span></div>
            <a href="<?= $base ?>/batches" class="btn btn-outline-dark btn-sm w-100 mt-2">All Batches</a>
        </div>
    </div>
</div>

<!-- Monthly trend table -->
<div class="eco-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Monthly Financial Trend</h6>
        <a href="<?= $base ?>/reports/profit-loss" class="btn btn-outline-dark btn-sm">Full P&amp;L</a>
    </div>
    <?php if (!empty($monthlyCombined)): ?>
        <div class="table-responsive">
            <table class="table align-middle small">
                <thead class="table-light"><tr><th>Month</th><th>Revenue</th><th>Feed</th><th>Medication</th><th>Vaccination</th><th>Direct</th><th>Total Expense</th><th>Net</th></tr></thead>
                <tbody>
                    <?php foreach ($monthlyCombined as $m): ?>
                        <?php $n = (float)($m['net_position']??0); ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($m['month_label']??'') ?></td>
                            <td class="text-success">GHS <?= number_format((float)($m['sales_revenue']??0),2) ?></td>
                            <td>GHS <?= number_format((float)($m['feed_expense']??0),2) ?></td>
                            <td>GHS <?= number_format((float)($m['medication_expense']??0),2) ?></td>
                            <td>GHS <?= number_format((float)($m['vaccination_expense']??0),2) ?></td>
                            <td>GHS <?= number_format((float)($m['direct_expense']??0),2) ?></td>
                            <td class="text-danger">GHS <?= number_format((float)($m['total_expense']??0),2) ?></td>
                            <td class="fw-bold <?= $n >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($n,2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No monthly data yet. Record sales and expenses to see the trend.</p>
    <?php endif; ?>
</div>

<?php if (!empty($chartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const ctx = document.getElementById('ecoTrendChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                { label:'Revenue',  data:<?= json_encode($chartRevenue) ?>, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.08)', tension:.35, borderWidth:2, fill:true },
                { label:'Expenses', data:<?= json_encode($chartExpense) ?>, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.08)',  tension:.35, borderWidth:2, fill:true },
                { label:'Net',      data:<?= json_encode($chartNet) ?>,     borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.06)', tension:.35, borderWidth:2, fill:false }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            interaction:{ mode:'index', intersect:false },
            plugins:{ legend:{ position:'top' } },
            scales:{ y:{ beginAtZero:true, ticks:{ callback: v => 'GHS '+v.toLocaleString() } } }
        }
    });
})();
</script>
<?php endif; ?>

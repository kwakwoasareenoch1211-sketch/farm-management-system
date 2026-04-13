<?php
$mt  = $monitorTotals    ?? [];
$mmt = $monitorMonthTotals ?? [];
$ba  = $businessAnalysis ?? [];
$diff= $ba['differentiation'] ?? [];
$base= rtrim(BASE_URL, '/');

// Core accounting figures
$totalCapital     = (float)($mt['total_capital']     ?? 0);
$totalRevenue     = (float)($mt['total_revenue']     ?? 0);
$totalExpenses    = (float)($mt['total_expenses']    ?? 0);
$totalAssets      = (float)($mt['total_assets']      ?? 0);
$totalLiabilities = (float)($mt['total_liabilities'] ?? 0);
$totalInvestments = (float)($mt['total_investments'] ?? 0);
$retainedProfit   = (float)($mt['retained_profit']   ?? 0);
$ownerEquity      = (float)($mt['owner_equity']      ?? 0);
$netWorth         = (float)($mt['net_worth']         ?? 0);
$workingCapital   = (float)($mt['working_capital']   ?? 0);
$profitMargin     = (float)($mt['profit_margin']     ?? 0);
$debtRatio        = (float)($mt['debt_ratio']        ?? 0);
$roi              = (float)($mt['roi']               ?? 0);
$capitalUtil      = (float)($mt['capital_utilisation']?? 0);

$monthRevenue     = (float)($mmt['revenue']          ?? 0);
$monthExpense     = (float)($mmt['total_expense']    ?? 0);
$monthNet         = (float)($mmt['net']              ?? 0);

$totalSales       = (float)($salesTotals['total_sales']       ?? 0);
$totalPaid        = (float)($salesTotals['total_paid']        ?? 0);
$totalOutstanding = (float)($salesTotals['total_outstanding'] ?? 0);
$monthSales       = (float)($salesTotals['current_month_sales']?? 0);
$todaySales       = (float)($salesTotals['today_sales']       ?? 0);
$totalSaleRecords = (int)($salesTotals['total_records']       ?? 0);
$monthMargin      = $monthRevenue > 0 ? ($monthNet / $monthRevenue) * 100 : 0;

$stage            = $ba['stage']              ?? 'Unknown';
$capitalEfficiency= (float)($ba['capital_efficiency'] ?? 0);
$expenseRatio     = (float)($ba['expense_ratio']      ?? 0);
$capitalAdequacy  = (float)($ba['capital_adequacy']   ?? 0);

$capitalGroups    = $mt['capital_groups']    ?? [];
$revenueGroups    = $mt['revenue_groups']    ?? [];
$expenseGroups    = $mt['expense_groups']    ?? [];
$assetGroups      = $mt['asset_groups']      ?? [];
$liabilityGroups  = $mt['liability_groups']  ?? [];
$investmentGroups = $mt['investment_groups'] ?? [];

$capitalTotals    = $capitalTotals    ?? [];
$capitalByType    = $capitalByType    ?? [];
$capitalRecords   = $capitalRecords   ?? [];
$investmentTotals = $investmentTotals ?? [];
$investmentByType = $investmentByType ?? [];
$investmentRecords= $investmentRecords?? [];

$capTypeLabels = ['owner_equity'=>'Owner Equity','retained_earnings'=>'Retained Earnings','loan_capital'=>'Loan Capital','grant'=>'Grants','other'=>'Other'];
$invTypeLabels = ['equipment'=>'Equipment','infrastructure'=>'Infrastructure','land'=>'Land','livestock'=>'Livestock','technology'=>'Technology','other'=>'Other'];
?>
<style>
.fin-hero{border-radius:22px;background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#334155 100%);color:#fff;padding:28px;}
.fin-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;box-shadow:0 4px 16px rgba(15,23,42,.06);}
.fin-kpi .lbl{color:#64748b;font-size:12px;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em;}
.fin-kpi .val{font-size:1.3rem;font-weight:700;margin-bottom:3px;}
.fin-kpi .sub{font-size:11px;color:#94a3b8;}
.fin-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.grp-row{border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;}
.grp-label{font-weight:600;font-size:14px;}
.grp-meta{font-size:11px;color:#94a3b8;}
.grp-amount{font-weight:700;font-size:14px;}
.cat-badge{font-size:10px;padding:3px 9px;border-radius:999px;font-weight:700;letter-spacing:.04em;}
.diff-card{border-radius:14px;padding:16px;border:1px solid #e2e8f0;background:#fff;}
.diff-bar{height:8px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-top:6px;}
.diff-bar-fill{height:100%;border-radius:999px;}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Accounting Engine · <?= htmlspecialchars($stage) ?> Stage</span>
        <h2 class="fw-bold mb-1">Financial Dashboard</h2>
        <p class="text-muted mb-0">Comprehensive financial analysis with intelligent expense grouping and asset-liability tracking.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/capital/create"     class="btn btn-primary btn-sm"><i class="bi bi-bank me-1"></i>Add Capital</a>
        <a href="<?= $base ?>/sales/create"       class="btn btn-success btn-sm"><i class="bi bi-cart-plus me-1"></i>New Sale</a>
        <a href="<?= $base ?>/expenses/create"    class="btn btn-danger btn-sm"><i class="bi bi-receipt me-1"></i>Add Expense</a>
        <a href="<?= $base ?>/investments/create" class="btn btn-info btn-sm text-white"><i class="bi bi-graph-up me-1"></i>Add Investment</a>
        <a href="<?= $base ?>/financial/traceability" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calculator me-1"></i>Audit Trail</a>
        <a href="<?= $base ?>/reports/profit-loss" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-bar-graph me-1"></i>P&L Report</a>
    </div>
</div>

<!-- Alerts -->
<?php if (!empty($alerts)): ?>
<div class="mb-4">
    <?php foreach ($alerts as $a): ?>
        <div class="alert alert-<?= $a['type'] ?> mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-<?= $a['type']==='danger'?'exclamation-triangle':($a['type']==='warning'?'exclamation-circle':'info-circle') ?>"></i>
            <div><strong><?= htmlspecialchars($a['title']) ?>:</strong> <?= htmlspecialchars($a['message']) ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ACCOUNTING EQUATION HERO -->
<div class="fin-hero mb-4">
    <div class="row g-0 align-items-center mb-3">
        <div class="col">
            <h5 class="fw-bold mb-0">Accounting Equation: Assets = Liabilities + Owner's Equity</h5>
            <p class="text-white-50 small mb-0">Capital + Retained Profit = Owner's Equity &nbsp;|&nbsp; Net Worth = Equity − Debt</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-<?= $netWorth >= 0 ? 'success' : 'danger' ?> px-3 py-2 fs-6">
                Net Worth: GHS <?= number_format($netWorth, 2) ?>
            </span>
        </div>
    </div>
    <div class="row g-3 text-center">
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-primary">GHS <?= number_format($totalCapital, 2) ?></div>
            <div class="small text-white-50">Capital</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-success">GHS <?= number_format($totalRevenue, 2) ?></div>
            <div class="small text-white-50">Revenue</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-danger">GHS <?= number_format($totalExpenses, 2) ?></div>
            <div class="small text-white-50">Expenses</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-info">GHS <?= number_format($totalAssets, 2) ?></div>
            <div class="small text-white-50">Assets</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-warning">GHS <?= number_format($totalLiabilities, 2) ?></div>
            <div class="small text-white-50">Liabilities</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="fs-5 fw-bold text-light">GHS <?= number_format($totalInvestments, 2) ?></div>
            <div class="small text-white-50">Investments</div>
        </div>
    </div>
</div>

<!-- KPI ROW -->
<!-- DUAL-ENTRY ACCOUNTING NOTE: Batch/Chick Logic -->
<div class="alert alert-info border-0 rounded-4 mb-4 d-flex gap-3 align-items-start">
    <i class="bi bi-info-circle-fill fs-5 mt-1 text-info"></i>
    <div>
        <strong>Batch Accounting — Dual Entry:</strong>
        When you buy chicks, the <strong>cash paid is recorded as an Expense</strong> (Livestock Purchase Cost) because it reduces your profit.
        At the same time, the <strong>live birds are recorded as a Biological Asset</strong> because you own them and they have value.
        As birds die (mortality), the asset value decreases and the loss is written off as an expense.
        When birds are sold, the asset is consumed and revenue is recognized.
        This is correct double-entry accounting — the same transaction appears in both Expenses and Assets.
    </div>
</div>

<!-- ADVANCED FINANCIAL RATIOS -->
<div class="fin-card p-4 mb-4">
    <div class="section-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2 text-primary"></i>Advanced Financial Ratios & Analysis</h6>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-3">
            <div class="eco-soft">
                <div class="small text-muted mb-1">Current Ratio</div>
                <?php 
                $currentRatio = $totalLiabilities > 0 ? $totalAssets / $totalLiabilities : ($totalAssets > 0 ? 999 : 0);
                $currentRatioStatus = $currentRatio >= ($totalAssets * 0.5 / max(1, $totalLiabilities)) ? 'success' : 'warning';
                ?>
                <div class="fs-4 fw-bold text-<?= $currentRatioStatus ?>">
                    <?= number_format($currentRatio, 2) ?>:1
                </div>
                <div class="small text-muted">Assets / Liabilities</div>
                <div class="small text-<?= $currentRatioStatus ?>">
                    <?= $currentRatio > 1 ? 'Positive liquidity position' : 'Liabilities exceed assets' ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="eco-soft">
                <div class="small text-muted mb-1">Return on Assets (ROA)</div>
                <?php 
                $roa = $totalAssets > 0 ? ($retainedProfit / $totalAssets) * 100 : 0;
                $roaStatus = $roa > 0 ? 'success' : ($roa < 0 ? 'danger' : 'warning');
                ?>
                <div class="fs-4 fw-bold text-<?= $roaStatus ?>">
                    <?= number_format($roa, 1) ?>%
                </div>
                <div class="small text-muted">Net Income / Total Assets</div>
                <div class="small text-<?= $roaStatus ?>">
                    <?= $roa > 0 ? 'Assets generating profit' : ($roa < 0 ? 'Assets underperforming' : 'Break-even') ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="eco-soft">
                <div class="small text-muted mb-1">Asset Turnover</div>
                <?php 
                $assetTurnover = $totalAssets > 0 ? $totalRevenue / $totalAssets : 0;
                ?>
                <div class="fs-4 fw-bold text-info">
                    <?= number_format($assetTurnover, 2) ?>x
                </div>
                <div class="small text-muted">Revenue / Total Assets</div>
                <div class="small text-info">
                    GHS <?= number_format($assetTurnover, 2) ?> revenue per GHS asset
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="eco-soft">
                <div class="small text-muted mb-1">Debt-to-Equity</div>
                <?php 
                $debtToEquity = $ownerEquity > 0 ? $totalLiabilities / $ownerEquity : ($totalLiabilities > 0 ? 999 : 0);
                $debtToEquityStatus = $debtToEquity < 1 ? 'success' : ($debtToEquity < 2 ? 'warning' : 'danger');
                ?>
                <div class="fs-4 fw-bold text-<?= $debtToEquityStatus ?>">
                    <?= $debtToEquity >= 999 ? '∞' : number_format($debtToEquity, 2) ?>:1
                </div>
                <div class="small text-muted">Liabilities / Owner's Equity</div>
                <div class="small text-<?= $debtToEquityStatus ?>">
                    <?= $debtToEquity < 1 ? 'Equity exceeds debt' : ($debtToEquity < 2 ? 'Moderate leverage' : 'High debt burden') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">Owner's Equity</div><div class="val text-primary">GHS <?= number_format($ownerEquity, 2) ?></div><div class="sub">Capital + Retained Profit</div></div></div>
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">Retained Profit</div><div class="val <?= $retainedProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($retainedProfit, 2) ?></div><div class="sub">Revenue − Expenses</div></div></div>
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">Working Capital</div><div class="val <?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($workingCapital, 2) ?></div><div class="sub">Assets − Liabilities</div></div></div>
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">Profit Margin</div><div class="val <?= $profitMargin > 0 ? 'text-success' : ($profitMargin < 0 ? 'text-danger' : 'text-warning') ?>"><?= number_format($profitMargin, 1) ?>%</div><div class="sub">Net / Revenue</div></div></div>
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">ROI on Capital</div><div class="val <?= $roi >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($roi, 1) ?>%</div><div class="sub">Profit / Capital</div></div></div>
    <div class="col-md-2"><div class="fin-kpi"><div class="lbl">Debt Ratio</div><div class="val <?= $debtRatio < ($totalAssets > 0 ? 50 : 0) ? 'text-success' : 'text-danger' ?>"><?= number_format($debtRatio, 1) ?>%</div><div class="sub">Liabilities / Assets</div></div></div>
</div>

<!-- CAPITAL DIFFERENTIATION ANALYSIS -->
<div class="fin-card p-4 mb-4">
    <div class="section-header">
        <div class="d-flex align-items-center gap-2">
            <span class="cat-badge bg-primary text-white">CAPITAL ANALYSIS</span>
            <h6 class="fw-bold mb-0">How Capital Differs from Assets, Liabilities, Expenses & Investments</h6>
        </div>
        <a href="<?= $base ?>/capital" class="btn btn-primary btn-sm">Manage Capital</a>
    </div>

    <div class="row g-3">
        <!-- Capital vs Liability -->
        <?php $cvl = $diff['capital_vs_liability'] ?? []; ?>
        <div class="col-md-4">
            <div class="diff-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold small">Capital vs Liability</div>
                        <div class="text-muted" style="font-size:11px;">Capital = what you OWN. Liability = what you OWE.</div>
                    </div>
                    <span class="badge bg-<?= ($cvl['status'] ?? '') === 'Healthy' ? 'success' : 'danger' ?>"><?= htmlspecialchars($cvl['status'] ?? '') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary fw-semibold">Capital: GHS <?= number_format((float)($cvl['capital'] ?? 0), 2) ?></span>
                    <span class="text-warning fw-semibold">Debt: GHS <?= number_format((float)($cvl['liability'] ?? 0), 2) ?></span>
                </div>
                <?php $capPct = ($cvl['capital'] ?? 0) + ($cvl['liability'] ?? 0) > 0 ? (($cvl['capital'] ?? 0) / (($cvl['capital'] ?? 0) + ($cvl['liability'] ?? 0))) * 100 : (($cvl['capital'] ?? 0) > 0 ? 100 : 0); ?>
                <div class="diff-bar"><div class="diff-bar-fill bg-primary" style="width:<?= min(100, $capPct) ?>%"></div></div>
                <div class="text-muted mt-1" style="font-size:11px;">Debt/Capital ratio: <?= number_format((float)($cvl['ratio'] ?? 0), 2) ?>x</div>
            </div>
        </div>

        <!-- Capital vs Assets -->
        <?php $cva = $diff['capital_vs_assets'] ?? []; ?>
        <div class="col-md-4">
            <div class="diff-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold small">Capital vs Assets</div>
                        <div class="text-muted" style="font-size:11px;">Assets are funded by capital + debt. Capital ≠ Asset.</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary fw-semibold">Equity-funded: <?= number_format((float)($cva['funded_by_capital_pct'] ?? 0), 1) ?>%</span>
                    <span class="text-warning fw-semibold">Debt-funded: <?= number_format((float)($cva['funded_by_debt_pct'] ?? 0), 1) ?>%</span>
                </div>
                <div class="diff-bar">
                    <div class="diff-bar-fill bg-primary" style="width:<?= min(100, (float)($cva['funded_by_capital_pct'] ?? 0)) ?>%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">Total Assets: GHS <?= number_format((float)($cva['assets'] ?? 0), 2) ?></div>
            </div>
        </div>

        <!-- Capital vs Investment -->
        <?php $cvi = $diff['capital_vs_investment'] ?? []; ?>
        <div class="col-md-4">
            <div class="diff-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold small">Capital vs Investment</div>
                        <div class="text-muted" style="font-size:11px;">Investment = capital deployed for return. Idle capital earns nothing.</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-info fw-semibold">Deployed: <?= number_format((float)($cvi['deployed_pct'] ?? 0), 1) ?>%</span>
                    <span class="text-muted fw-semibold">Idle: GHS <?= number_format((float)($cvi['idle_capital'] ?? 0), 2) ?></span>
                </div>
                <div class="diff-bar">
                    <div class="diff-bar-fill bg-info" style="width:<?= min(100, (float)($cvi['deployed_pct'] ?? 0)) ?>%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">Investments: GHS <?= number_format((float)($cvi['investments'] ?? 0), 2) ?></div>
            </div>
        </div>

        <!-- Capital vs Expenses -->
        <?php $cve = $diff['capital_vs_expenses'] ?? []; ?>
        <div class="col-md-6">
            <div class="diff-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold small">Capital vs Expenses</div>
                        <div class="text-muted" style="font-size:11px;">Expenses reduce profit, not capital directly. Capital is preserved unless losses exceed equity.</div>
                    </div>
                    <span class="badge bg-<?= ($cve['status'] ?? '') === 'Efficient' ? 'success' : (($cve['status'] ?? '') === 'Moderate' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($cve['status'] ?? '') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary fw-semibold">Capital: GHS <?= number_format((float)($cve['capital'] ?? 0), 2) ?></span>
                    <span class="text-danger fw-semibold">Expenses: GHS <?= number_format((float)($cve['expenses'] ?? 0), 2) ?></span>
                </div>
                <div class="diff-bar">
                    <div class="diff-bar-fill bg-danger" style="width:<?= min(100, (float)($cve['expense_ratio'] ?? 0)) ?>%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">Expense ratio: <?= number_format((float)($cve['expense_ratio'] ?? 0), 1) ?>% of revenue</div>
            </div>
        </div>

        <!-- Capital vs Revenue -->
        <?php $cvr = $diff['capital_vs_revenue'] ?? []; ?>
        <div class="col-md-6">
            <div class="diff-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold small">Capital vs Revenue</div>
                        <div class="text-muted" style="font-size:11px;">Revenue is income earned. Capital is the base that enables earning. Revenue ≠ Capital.</div>
                    </div>
                    <span class="badge bg-<?= ($cvr['status'] ?? '') === 'High Return' ? 'success' : (($cvr['status'] ?? '') === 'Moderate Return' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($cvr['status'] ?? '') ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-primary fw-semibold">Capital: GHS <?= number_format((float)($cvr['capital'] ?? 0), 2) ?></span>
                    <span class="text-success fw-semibold">Revenue: GHS <?= number_format((float)($cvr['revenue'] ?? 0), 2) ?></span>
                </div>
                <div class="diff-bar">
                    <?php $revPct = (float)($cvr['capital'] ?? 0) > 0 ? min(100, ((float)($cvr['revenue'] ?? 0) / (float)$cvr['capital']) * (100 / max(1, (float)($cvr['capital'] ?? 1)))) : 0; ?>
                    <div class="diff-bar-fill bg-success" style="width:<?= $revPct ?>%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">Capital efficiency: <?= number_format((float)($cvr['efficiency'] ?? 0), 2) ?>x (GHS revenue per GHS capital)</div>
            </div>
        </div>
    </div>
</div>

<!-- 6-CATEGORY CLASSIFIED SECTIONS -->
<div class="row g-4 mb-4">
    <!-- CAPITAL -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-primary text-white">CAPITAL</span>
                    <span class="fw-bold small">Source of Funds</span>
                </div>
                <span class="fw-bold text-primary">GHS <?= number_format($totalCapital, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">Owner equity, grants, retained earnings. <strong>Not a liability.</strong> Loan capital is tracked under Liabilities.</div>
            <?php foreach ($capitalGroups as $g): ?>
                <div class="grp-row">
                    <div><div class="grp-label"><?= htmlspecialchars($g['label']) ?></div><div class="grp-meta"><?= htmlspecialchars($g['note'] ?? '') ?> · <a href="<?= htmlspecialchars($g['create_link']) ?>" class="text-primary">+ Add</a></div></div>
                    <div class="grp-amount text-primary">GHS <?= number_format($g['amount'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <a href="<?= $base ?>/capital" class="btn btn-outline-primary btn-sm w-100 mt-2">Manage Capital</a>
        </div>
    </div>

    <!-- REVENUE -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-success text-white">REVENUE</span>
                    <span class="fw-bold small">Income Earned</span>
                </div>
                <span class="fw-bold text-success">GHS <?= number_format($totalRevenue, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">All sales income. Increases retained profit and owner's equity. <strong>Not capital.</strong></div>
            <?php foreach ($revenueGroups as $g): ?>
                <div class="grp-row">
                    <div>
                        <div class="grp-label"><?= htmlspecialchars($g['label']) ?></div>
                        <div class="grp-meta"><?= number_format($g['records']) ?> records
                            <?php if (($g['outstanding'] ?? 0) > 0): ?> · <span class="text-danger">GHS <?= number_format($g['outstanding'], 2) ?> outstanding</span><?php endif; ?>
                            · <a href="<?= htmlspecialchars($g['create_link']) ?>" class="text-success">+ Add</a>
                        </div>
                    </div>
                    <div class="grp-amount text-success">GHS <?= number_format($g['amount'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="mt-2 pt-2 border-top small d-flex justify-content-between">
                <span>Collected: <strong class="text-success">GHS <?= number_format($totalPaid, 2) ?></strong></span>
                <span>Outstanding: <strong class="text-danger">GHS <?= number_format($totalOutstanding, 2) ?></strong></span>
            </div>
            <a href="<?= $base ?>/sales" class="btn btn-outline-success btn-sm w-100 mt-2">View All Sales</a>
        </div>
    </div>

    <!-- EXPENSES -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-danger text-white">EXPENSES</span>
                    <span class="fw-bold small">Costs Incurred</span>
                </div>
                <span class="fw-bold text-danger">GHS <?= number_format($totalExpenses, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">Reduces profit. <strong>Not capital, not assets.</strong> Paid expenses are gone; unpaid become liabilities. Livestock purchase cost is expensed here — live birds are separately tracked as biological assets.</div>
            <?php foreach ($expenseGroups as $g): ?>
                <div class="grp-row">
                    <div><div class="grp-label"><?= htmlspecialchars($g['label']) ?></div><div class="grp-meta"><?= number_format($g['records']) ?> records · <a href="<?= htmlspecialchars($g['create_link']) ?>" class="text-danger">+ Add</a></div></div>
                    <div class="grp-amount text-danger">GHS <?= number_format($g['amount'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <a href="<?= $base ?>/expenses" class="btn btn-outline-danger btn-sm w-100 mt-2">View All Expenses</a>
        </div>
    </div>

    <!-- ASSETS -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-info text-white">ASSETS</span>
                    <span class="fw-bold small">What Business Owns</span>
                </div>
                <span class="fw-bold text-info">GHS <?= number_format($totalAssets, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">Funded by capital + debt. <strong>Assets ≠ Capital.</strong> Assets = Liabilities + Equity.</div>
            <?php foreach ($assetGroups as $g): ?>
                <div class="grp-row">
                    <div>
                        <div class="grp-label"><?= htmlspecialchars($g['label']) ?></div>
                        <div class="grp-meta"><?= htmlspecialchars($g['note'] ?? '') ?>
                            <?php if (!empty($g['dual_note'])): ?>
                                <br><span class="badge bg-warning text-dark" style="font-size:9px;"><?= htmlspecialchars($g['dual_note']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grp-amount text-info">GHS <?= number_format($g['amount'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- LIABILITIES -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-warning text-dark">LIABILITIES</span>
                    <span class="fw-bold small">What Business Owes</span>
                </div>
                <span class="fw-bold text-warning">GHS <?= number_format($totalLiabilities, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">Loan capital + unpaid expenses. <strong>Liabilities reduce net worth.</strong> Must be repaid.</div>
            <?php foreach ($liabilityGroups as $g): ?>
                <div class="grp-row">
                    <div><div class="grp-label"><?= htmlspecialchars($g['label']) ?></div><div class="grp-meta"><?= htmlspecialchars($g['note'] ?? '') ?></div></div>
                    <div class="grp-amount text-warning">GHS <?= number_format($g['amount'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="mt-2 pt-2 border-top small d-flex justify-content-between">
                <span>Working Capital:</span>
                <strong class="<?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($workingCapital, 2) ?></strong>
            </div>
        </div>
    </div>

    <!-- INVESTMENTS -->
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cat-badge bg-secondary text-white">INVESTMENTS</span>
                    <span class="fw-bold small">Capital Deployed</span>
                </div>
                <span class="fw-bold text-secondary">GHS <?= number_format($totalInvestments, 2) ?></span>
            </div>
            <div class="small text-muted mb-3">Long-term assets funded by capital. <strong>Investments are assets, not expenses.</strong> They generate future returns.</div>
            <?php if (!empty($investmentGroups)): ?>
                <?php foreach ($investmentGroups as $g): ?>
                    <div class="grp-row">
                        <div>
                            <div class="grp-label"><?= htmlspecialchars($g['label']) ?></div>
                            <div class="grp-meta">ROI: <?= number_format($g['roi_pct'] ?? 0, 1) ?>% · Depr: GHS <?= number_format($g['annual_depreciation'] ?? 0, 2) ?>/yr</div>
                        </div>
                        <div class="grp-amount text-secondary">GHS <?= number_format($g['amount'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small">No active investments. <a href="<?= $base ?>/investments/create">Add investment</a>.</p>
            <?php endif; ?>
            <a href="<?= $base ?>/investments" class="btn btn-outline-secondary btn-sm w-100 mt-2">Manage Investments</a>
        </div>
    </div>
</div>

<!-- REVENUE DETAIL + MONTH SNAPSHOT -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="fin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-cart3 text-success me-2"></i>Recent Sales</h6>
                <div class="d-flex gap-2">
                    <a href="<?= $base ?>/sales/create" class="btn btn-success btn-sm">+ New Sale</a>
                    <a href="<?= $base ?>/sales" class="btn btn-outline-dark btn-sm">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small">
                    <thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <?php if (!empty($recentSales)): ?>
                            <?php foreach ($recentSales as $s): ?>
                                <?php $st = $s['payment_status'] ?? 'unpaid'; ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['sale_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['invoice_no'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($s['sale_type'] ?? '')) ?></td>
                                    <td class="fw-bold text-success">GHS <?= number_format((float)($s['total_amount'] ?? 0), 2) ?></td>
                                    <td><span class="badge bg-<?= $st==='paid'?'success':($st==='partial'?'warning':'danger') ?>"><?= ucfirst($st) ?></span></td>
                                    <td><a href="<?= $base ?>/sales/edit?id=<?= (int)$s['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No sales yet. <a href="<?= $base ?>/sales/create">Record first sale</a>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <h6 class="fw-bold mb-3">This Month Snapshot</h6>
            <table class="table align-middle small mb-0">
                <tbody>
                    <tr><td class="text-muted">Revenue</td><td class="text-end fw-bold text-success">GHS <?= number_format($monthRevenue, 2) ?></td></tr>
                    <tr><td class="text-muted">Expenses</td><td class="text-end fw-bold text-danger">GHS <?= number_format($monthExpense, 2) ?></td></tr>
                    <tr class="table-light"><td class="fw-bold">Net</td><td class="text-end fw-bold <?= $monthNet >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($monthNet, 2) ?></td></tr>
                    <tr><td class="text-muted">Margin</td><td class="text-end"><?= number_format($monthMargin, 1) ?>%</td></tr>
                    <tr><td class="text-muted">Today Sales</td><td class="text-end">GHS <?= number_format($todaySales, 2) ?></td></tr>
                    <tr><td class="text-muted">Outstanding</td><td class="text-end text-danger">GHS <?= number_format($totalOutstanding, 2) ?></td></tr>
                </tbody>
            </table>
            <div class="mt-3 d-flex gap-2">
                <a href="<?= $base ?>/reports/profit-loss" class="btn btn-dark btn-sm w-100">P&amp;L Report</a>
                <a href="<?= $base ?>/reports/sales" class="btn btn-outline-dark btn-sm w-100">Sales Report</a>
            </div>
        </div>
    </div>
</div>

<!-- MONTHLY TREND -->
<div class="fin-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2"></i>Monthly Revenue vs Expenses Trend</h6>
        <a href="<?= $base ?>/reports/profit-loss" class="btn btn-outline-dark btn-sm">Full Report</a>
    </div>
    <?php if (!empty($monthlyCombined)): ?>
        <div class="table-responsive">
            <table class="table align-middle small">
                <thead><tr><th>Month</th><th>Revenue</th><th>Feed</th><th>Medication</th><th>Vaccination</th><th>Direct</th><th>Total Expense</th><th>Net</th></tr></thead>
                <tbody>
                    <?php foreach ($monthlyCombined as $m): ?>
                        <?php $n = (float)($m['net_position'] ?? 0); ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($m['month_label'] ?? '') ?></td>
                            <td class="text-success">GHS <?= number_format((float)($m['sales_revenue'] ?? 0), 2) ?></td>
                            <td>GHS <?= number_format((float)($m['feed_expense'] ?? 0), 2) ?></td>
                            <td>GHS <?= number_format((float)($m['medication_expense'] ?? 0), 2) ?></td>
                            <td>GHS <?= number_format((float)($m['vaccination_expense'] ?? 0), 2) ?></td>
                            <td>GHS <?= number_format((float)($m['direct_expense'] ?? 0), 2) ?></td>
                            <td class="text-danger">GHS <?= number_format((float)($m['total_expense'] ?? 0), 2) ?></td>
                            <td class="fw-bold <?= $n >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($n, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No monthly data yet.</p>
    <?php endif; ?>
</div>

<!-- RECENT ACTIVITY LEDGER -->
<div class="fin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Financial Activity</h6>
        <a href="<?= $base ?>/reports/sales" class="btn btn-outline-dark btn-sm">Full Ledger</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle small">
            <thead><tr><th>Date</th><th>Category</th><th>Type</th><th>Description</th><th>Direction</th><th>Amount</th></tr></thead>
            <tbody>
                <?php if (!empty($recentFinancialActivities)): ?>
                    <?php foreach ($recentFinancialActivities as $item): ?>
                        <?php $dir = $item['direction'] ?? 'expense'; ?>
                        <tr>
                            <td><?= htmlspecialchars($item['activity_date'] ?? '') ?></td>
                            <td><span class="cat-badge bg-<?= $dir==='revenue'?'success':'danger' ?> text-white"><?= $dir==='revenue'?'Revenue':'Expense' ?></span></td>
                            <td><?= htmlspecialchars($item['activity_type'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['title'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $dir==='revenue'?'success':'danger' ?>"><?= ucfirst($dir) ?></span></td>
                            <td class="fw-semibold <?= $dir==='revenue'?'text-success':'text-danger' ?>">GHS <?= number_format((float)($item['amount'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No financial activity yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Financial Charts - appended
$db2 = Database::connect();
$finChartLabels = []; $finRevData = []; $finExpData = [];
try {
    $rows = $db2->query("
        SELECT DATE_FORMAT(m,'%b %Y') AS lbl,
               COALESCE(SUM(rev),0) AS rev, COALESCE(SUM(exp),0) AS exp
        FROM (
            SELECT DATE_FORMAT(sale_date,'%Y-%m-01') AS m, total_amount AS rev, 0 AS exp FROM sales
            UNION ALL
            SELECT DATE_FORMAT(expense_date,'%Y-%m-01'), 0, amount FROM expenses
        ) t
        WHERE m >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY m ORDER BY m
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $finChartLabels = array_column($rows, 'lbl');
    $finRevData     = array_map(fn($r) => (float)$r['rev'], $rows);
    $finExpData     = array_map(fn($r) => (float)$r['exp'], $rows);
} catch (\Throwable $e) {}
?>
<?php if (!empty($finChartLabels)): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="fin-card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-success me-2"></i>Revenue vs Expenses (12 Months)</h6>
            <canvas id="finRevExpChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fin-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Expense Breakdown</h6>
            <canvas id="finExpDonut" height="220"></canvas>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const lbl = <?= json_encode($finChartLabels) ?>;
    const rev = <?= json_encode($finRevData) ?>;
    const exp = <?= json_encode($finExpData) ?>;
    const net = rev.map((r,i) => r - exp[i]);

    const c1 = document.getElementById('finRevExpChart');
    if (c1) new Chart(c1, {
        type:'line',
        data:{ labels:lbl, datasets:[
            {label:'Revenue', data:rev, borderColor:'#22c55e', backgroundColor:'#22c55e15', fill:true, tension:0.4},
            {label:'Expenses', data:exp, borderColor:'#ef4444', backgroundColor:'#ef444415', fill:true, tension:0.4},
            {label:'Net', data:net, borderColor:'#3b82f6', borderDash:[5,5], tension:0.4}
        ]},
        options:{responsive:true, plugins:{legend:{position:'top'}}, scales:{y:{ticks:{callback:v=>'GHS '+v.toLocaleString()}}}}
    });

    const c2 = document.getElementById('finExpDonut');
    if (c2) {
        const feed = <?= (float)($financeTotals['by_source']['feed']['total'] ?? 0) ?>;
        const med  = <?= (float)($financeTotals['by_source']['medication']['total'] ?? 0) ?>;
        const vac  = <?= (float)($financeTotals['by_source']['vaccination']['total'] ?? 0) ?>;
        const dir  = <?= (float)($financeTotals['by_source']['manual']['total'] ?? 0) ?>;
        const bird = <?= (float)($financeTotals['by_source']['livestock_purchase']['total'] ?? 0) ?>;
        const vals = [feed, med, vac, dir, bird].filter(v=>v>0);
        const lbls = ['Feed','Medication','Vaccination','Direct','Livestock'].filter((_,i)=>[feed,med,vac,dir,bird][i]>0);
        if (vals.length) new Chart(c2, {
            type:'doughnut',
            data:{labels:lbls, datasets:[{data:vals, backgroundColor:['#f59e0b','#ef4444','#22c55e','#3b82f6','#8b5cf6'], borderWidth:2}]},
            options:{responsive:true, plugins:{legend:{position:'bottom', labels:{font:{size:11}}}}}
        });
    }
})();
</script>
<?php endif; ?>

<?php
$r    = $record ?? [];
$base = rtrim(BASE_URL, '/');

$typeLabels = ['equipment'=>'Equipment','infrastructure'=>'Infrastructure','land'=>'Land','livestock'=>'Live Birds','technology'=>'Technology','eggs'=>'Egg Production','other'=>'Other'];
$typeColors = ['equipment'=>'primary','infrastructure'=>'info','land'=>'success','livestock'=>'warning','technology'=>'secondary','eggs'=>'warning','other'=>'dark'];
$typeIcons  = ['equipment'=>'bi-tools','infrastructure'=>'bi-building','land'=>'bi-geo-alt','livestock'=>'bi-feather','technology'=>'bi-cpu','eggs'=>'bi-egg-fried','other'=>'bi-box'];

$type        = $r['investment_type'] ?? 'other';
$amount      = (float)($r['amount']             ?? 0);
$bookVal     = (float)($r['current_book_value'] ?? $amount);
$effectRet   = (float)($r['effective_return']   ?? 0);
$netGain     = (float)($r['net_gain']           ?? 0);
$roi         = (float)($r['roi_pct']            ?? 0);
$isLive      = in_array($type, ['eggs','livestock']);
$lifeYears   = (int)($r['useful_life_years']    ?? 0);
$strategy    = $r['strategy']                   ?? [];
$invShare    = (float)($r['investor_share_pct'] ?? 0);
$deprPct     = $amount > 0 ? min(100, (($amount - $bookVal) / $amount) * 100) : 0;
$returnPct   = $effectRet > 0 ? min(100, ((float)($r['return_to_date'] ?? 0) / $effectRet) * 100) : 0;
?>
<style>
.inv-view-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.metric-box{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px;text-align:center;}
.metric-box .lbl{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;}
.metric-box .val{font-size:1.15rem;font-weight:700;}
.period-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.period-cell{border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px;text-align:center;}
.period-cell .pc-lbl{font-size:11px;color:#94a3b8;text-transform:uppercase;margin-bottom:4px;}
.period-cell .pc-val{font-size:1.1rem;font-weight:700;}
.strategy-panel{border-radius:18px;padding:20px;border:2px solid;}
.inv-hero{border-radius:22px;padding:24px;color:#fff;}
</style>

<!-- Back + header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-<?= $typeColors[$type]??'dark' ?> mb-2 px-3 py-2">
            <i class="bi <?= $typeIcons[$type]??'bi-box' ?> me-1"></i><?= htmlspecialchars($typeLabels[$type]??ucfirst($type)) ?>
        </span>
        <h2 class="fw-bold mb-0"><?= htmlspecialchars($r['title']??'') ?></h2>
        <p class="text-muted small mb-0">
            <?= htmlspecialchars($r['investment_date']??'') ?> &middot;
            <?= htmlspecialchars($r['farm_name']??'') ?> &middot;
            <?= number_format((float)($r['elapsed_months']??0),1) ?> months old
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/investments/edit?id=<?= (int)($r['id']??0) ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="<?= $base ?>/investments" class="btn btn-outline-dark btn-sm">← Back</a>
    </div>
</div>

<!-- Hero banner -->
<div class="inv-hero mb-4" style="background:linear-gradient(135deg,#0f172a,#1e3a5f,#1d4ed8);">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <div class="small text-white-50 mb-1">Amount Invested</div>
            <div class="fs-2 fw-bold">GHS <?= number_format($amount,2) ?></div>
            <?php if (!empty($strategy)): ?>
                <span class="badge bg-<?= $strategy['color']??'secondary' ?> mt-2 px-3 py-2">
                    <?= $strategy['icon']??'' ?> <?= htmlspecialchars($strategy['principle']??'') ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="fs-4 fw-bold <?= $netGain >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netGain,2) ?></div>
                    <div class="small text-white-50">Net Gain</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fs-4 fw-bold <?= $roi >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($roi,1) ?>%</div>
                    <div class="small text-white-50">ROI</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fs-4 fw-bold">GHS <?= number_format($bookVal,2) ?></div>
                    <div class="small text-white-50">Book Value</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fs-4 fw-bold"><?= (float)($r['payback_years']??0) > 0 ? number_format((float)$r['payback_years'],1).' yrs' : 'N/A' ?></div>
                    <div class="small text-white-50">Payback</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live data alert -->
<?php if ($isLive && (float)($r['actual_revenue']??0) > 0): ?>
    <div class="alert alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Live Data Active:</strong> Returns calculated from actual <?= $type === 'eggs' ? 'egg' : 'livestock' ?> sales of
        <strong>GHS <?= number_format((float)$r['actual_revenue'],2) ?></strong> since <?= htmlspecialchars($r['investment_date']??'') ?>.
    </div>
<?php elseif ($isLive): ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>No <?= $type === 'eggs' ? 'egg' : 'livestock' ?> sales recorded yet. Showing projections from expected return.
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left column -->
    <div class="col-lg-5">

        <!-- Investment details -->
        <div class="inv-view-card p-4 mb-4">
            <h6 class="fw-bold mb-3">Investment Details</h6>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><td class="text-muted">Invested</td><td class="fw-bold text-primary">GHS <?= number_format($amount,2) ?></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge bg-<?= $r['status']==='active'?'success':($r['status']==='disposed'?'danger':'secondary') ?>"><?= ucfirst($r['status']??'') ?></span></td></tr>
                    <tr><td class="text-muted">Date</td><td><?= htmlspecialchars($r['investment_date']??'') ?></td></tr>
                    <tr><td class="text-muted">Age</td><td><?= number_format((float)($r['elapsed_months']??0),1) ?> months (<?= number_format((int)($r['elapsed_days']??0)) ?> days)</td></tr>
                    <?php if ($lifeYears > 0): ?>
                    <tr><td class="text-muted">Useful Life</td><td><?= $lifeYears ?> years</td></tr>
                    <tr><td class="text-muted">Remaining Life</td><td><?= number_format((float)($r['remaining_life_years']??0),1) ?> years</td></tr>
                    <?php endif; ?>
                    <?php if (!empty($r['reference_no'])): ?>
                    <tr><td class="text-muted">Reference</td><td><?= htmlspecialchars($r['reference_no']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">Return Source</td><td><span class="badge bg-<?= $isLive && (float)($r['actual_revenue']??0) > 0 ? 'success' : 'secondary' ?>"><?= htmlspecialchars($r['return_source']??'Projected') ?></span></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Financial summary -->
        <div class="inv-view-card p-4 mb-4">
            <h6 class="fw-bold mb-3">Financial Summary</h6>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><td class="text-muted">Amount Invested</td><td class="fw-bold">GHS <?= number_format($amount,2) ?></td></tr>
                    <?php if ($isLive): ?>
                    <tr><td class="text-muted">Actual Revenue</td><td class="fw-bold text-success">GHS <?= number_format((float)($r['actual_revenue']??0),2) ?></td></tr>
                    <?php else: ?>
                    <tr><td class="text-muted">Expected Return</td><td class="fw-bold">GHS <?= number_format($effectRet,2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">Net Gain</td><td class="fw-bold <?= $netGain >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netGain,2) ?></td></tr>
                    <tr><td class="text-muted">ROI</td><td class="fw-bold <?= $roi >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($roi,1) ?>%</td></tr>
                    <tr><td class="text-muted">Book Value</td><td class="fw-bold">GHS <?= number_format($bookVal,2) ?></td></tr>
                    <?php if ((float)($r['depreciation_to_date']??0) > 0): ?>
                    <tr><td class="text-muted">Depreciated</td><td class="text-warning">GHS <?= number_format((float)$r['depreciation_to_date'],2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float)($r['payback_years']??0) > 0): ?>
                    <tr><td class="text-muted">Payback Period</td><td><?= number_format((float)$r['payback_years'],1) ?> yrs (<?= number_format((float)($r['payback_months']??0),0) ?> months)</td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">Return Earned</td><td class="text-success">GHS <?= number_format((float)($r['return_to_date']??0),2) ?></td></tr>
                </tbody>
            </table>
            <?php if ($effectRet > 0): ?>
            <div class="mt-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Return progress</span>
                    <span class="text-success"><?= number_format($returnPct,1) ?>%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:999px;">
                    <div class="progress-bar bg-success" style="width:<?= $returnPct ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Investor return breakdown -->
        <?php if ($invShare > 0): ?>
        <div class="inv-view-card p-4 mb-4" style="border-left:4px solid #8b5cf6;">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-check text-purple me-2"></i>Investor Return Breakdown</h6>
            <div class="alert alert-info py-2 small mb-3">
                Investor holds <strong><?= number_format($invShare,1) ?>%</strong> share.
                Business retains <strong><?= number_format((float)($r['business_retained_pct']??0),1) ?>%</strong>.
            </div>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><td class="text-muted">Investor Total Return</td><td class="fw-bold text-purple">GHS <?= number_format((float)($r['investor_return_total']??0),2) ?></td></tr>
                    <tr><td class="text-muted">Investor Annual Return</td><td class="fw-bold">GHS <?= number_format((float)($r['investor_return_annual']??0),2) ?></td></tr>
                    <tr><td class="text-muted">Investor Monthly Return</td><td class="fw-bold">GHS <?= number_format((float)($r['investor_return_monthly']??0),2) ?></td></tr>
                    <tr><td class="text-muted">Investor Weekly Return</td><td class="fw-bold">GHS <?= number_format((float)($r['investor_return_weekly']??0),2) ?></td></tr>
                    <tr><td class="text-muted">Earned to Date (Investor)</td><td class="fw-bold text-success">GHS <?= number_format((float)($r['investor_return_to_date']??0),2) ?></td></tr>
                </tbody>
            </table>
            <div class="small text-muted mt-2">
                To set investor share, add <code>investor_share:XX</code> in the Notes field (e.g. <code>investor_share:30</code> for 30%).
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right column -->
    <div class="col-lg-7">

        <!-- Return schedule -->
        <div class="inv-view-card p-4 mb-4">
            <h6 class="fw-bold mb-3">
                <?= $isLive ? '📊 Actual Return Rate (from real sales)' : '📈 Projected Return Schedule' ?>
            </h6>
            <div class="period-grid mb-3">
                <div class="period-cell"><div class="pc-lbl">Per Week</div><div class="pc-val text-success">GHS <?= number_format((float)($r['weekly_return']??0),2) ?></div></div>
                <div class="period-cell"><div class="pc-lbl">Per Month</div><div class="pc-val text-success">GHS <?= number_format((float)($r['monthly_return']??0),2) ?></div></div>
                <div class="period-cell"><div class="pc-lbl">Per Year</div><div class="pc-val text-success">GHS <?= number_format((float)($r['annual_return']??0),2) ?></div></div>
            </div>
            <?php if ($invShare > 0): ?>
            <div class="border rounded-3 p-3 bg-light">
                <div class="small fw-semibold text-muted mb-2">Investor's Share (<?= number_format($invShare,1) ?>%)</div>
                <div class="period-grid">
                    <div class="period-cell"><div class="pc-lbl">Per Week</div><div class="pc-val" style="color:#8b5cf6;">GHS <?= number_format((float)($r['investor_return_weekly']??0),2) ?></div></div>
                    <div class="period-cell"><div class="pc-lbl">Per Month</div><div class="pc-val" style="color:#8b5cf6;">GHS <?= number_format((float)($r['investor_return_monthly']??0),2) ?></div></div>
                    <div class="period-cell"><div class="pc-lbl">Per Year</div><div class="pc-val" style="color:#8b5cf6;">GHS <?= number_format((float)($r['investor_return_annual']??0),2) ?></div></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Depreciation schedule -->
        <?php if (in_array($type, ['equipment','infrastructure','technology']) && $lifeYears > 0): ?>
        <div class="inv-view-card p-4 mb-4">
            <h6 class="fw-bold mb-3">📉 Depreciation Schedule (Straight-Line)</h6>
            <div class="period-grid mb-3">
                <div class="period-cell"><div class="pc-lbl">Per Week</div><div class="pc-val text-warning">GHS <?= number_format((float)($r['weekly_depreciation']??0),2) ?></div></div>
                <div class="period-cell"><div class="pc-lbl">Per Month</div><div class="pc-val text-warning">GHS <?= number_format((float)($r['monthly_depreciation']??0),2) ?></div></div>
                <div class="period-cell"><div class="pc-lbl">Per Year</div><div class="pc-val text-warning">GHS <?= number_format((float)($r['annual_depreciation']??0),2) ?></div></div>
            </div>
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Depreciated so far</span>
                <span><?= number_format($deprPct,1) ?>% · <?= number_format((float)($r['remaining_life_years']??0),1) ?> yrs remaining</span>
            </div>
            <div class="progress" style="height:8px;border-radius:999px;">
                <div class="progress-bar bg-warning" style="width:<?= $deprPct ?>%"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Investment strategy panel -->
        <?php if (!empty($strategy)): ?>
        <div class="inv-view-card p-4 mb-4" style="border-left:4px solid var(--bs-<?= $strategy['color']??'secondary' ?>);">
            <h6 class="fw-bold mb-3">🎯 Investment Strategy & Principles</h6>
            <div class="row g-3 mb-3">
                <div class="col-6"><div class="metric-box"><div class="lbl">Principle</div><div class="val"><?= $strategy['icon']??'' ?> <?= htmlspecialchars($strategy['principle']??'') ?></div></div></div>
                <div class="col-6"><div class="metric-box"><div class="lbl">Time Horizon</div><div class="val"><?= htmlspecialchars($strategy['horizon']??'') ?></div></div></div>
                <div class="col-6"><div class="metric-box"><div class="lbl">Risk Level</div><div class="val"><?= htmlspecialchars($strategy['risk']??'') ?></div></div></div>
                <div class="col-6"><div class="metric-box"><div class="lbl">ROI Rating</div><div class="val text-<?= $strategy['roi_class']??'secondary' ?>"><?= htmlspecialchars($strategy['roi_rating']??'') ?></div></div></div>
            </div>
            <div class="border rounded-3 p-3 bg-light">
                <div class="small fw-semibold mb-1">💡 Recommendation</div>
                <div class="small text-muted"><?= htmlspecialchars($strategy['recommendation']??'') ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Year-by-year projection -->
        <?php if ($lifeYears > 0 && $amount > 0): ?>
        <div class="inv-view-card p-4">
            <h6 class="fw-bold mb-3">📅 Year-by-Year Projection</h6>
            <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr><th>Year</th><th>Book Value</th><th>Return Earned</th><th>Depreciation</th><th>Net Position</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $annDepr = $lifeYears > 0 ? $amount / $lifeYears : 0;
                        $annRet  = (float)($r['annual_return'] ?? 0);
                        $maxRet  = $netGain > 0 ? $netGain : 0;
                        for ($y = 1; $y <= $lifeYears; $y++):
                            $bv      = max(0, $amount - $annDepr * $y);
                            $re      = min($maxRet, $annRet * $y);
                            $deprAcc = $annDepr * $y;
                            $net     = $re - $deprAcc;
                            $invRet  = $invShare > 0 ? $re * $invShare / 100 : 0;
                        ?>
                        <tr>
                            <td class="fw-semibold">Year <?= $y ?></td>
                            <td>GHS <?= number_format($bv,2) ?></td>
                            <td class="text-success">GHS <?= number_format($re,2) ?><?php if ($invShare > 0): ?><br><small class="text-muted">Investor: GHS <?= number_format($invRet,2) ?></small><?php endif; ?></td>
                            <td class="text-warning">GHS <?= number_format($deprAcc,2) ?></td>
                            <td class="fw-bold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($net,2) ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Description / Notes -->
<?php if (!empty($r['description']) || !empty($r['notes'])): ?>
<div class="inv-view-card p-4 mt-4">
    <?php if (!empty($r['description'])): ?>
        <h6 class="fw-bold mb-2">Description</h6>
        <p class="text-muted"><?= nl2br(htmlspecialchars($r['description'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($r['notes'])): ?>
        <h6 class="fw-bold mb-2">Notes</h6>
        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($r['notes'])) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$records = $records ?? [];
$totals  = $totals  ?? [];
$byType  = $byType  ?? [];
$base    = rtrim(BASE_URL, '/');

$typeLabels = ['equipment'=>'Equipment','infrastructure'=>'Infrastructure','land'=>'Land','livestock'=>'Live Birds','technology'=>'Technology','eggs'=>'Egg Production','other'=>'Other'];
$typeColors = ['equipment'=>'primary','infrastructure'=>'info','land'=>'success','livestock'=>'warning','technology'=>'secondary','eggs'=>'warning','other'=>'dark'];
$typeIcons  = ['equipment'=>'bi-tools','infrastructure'=>'bi-building','land'=>'bi-geo-alt','livestock'=>'bi-feather','technology'=>'bi-cpu','eggs'=>'bi-egg-fried','other'=>'bi-box'];

$pendingCount = (int)($totals['pending_count'] ?? 0);
$roiPct       = (float)($totals['roi_pct'] ?? 0);
$hasRoi       = (float)($totals['total_actual_revenue'] ?? 0) > 0 || (float)($totals['total_expected_return'] ?? 0) > 0;
?>
<style>
.inv-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.inv-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;box-shadow:0 4px 16px rgba(15,23,42,.05);}
.inv-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;}
.inv-kpi .val{font-size:1.3rem;font-weight:700;margin-bottom:3px;}
.inv-kpi .sub{font-size:11px;color:#94a3b8;}
.time-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
.time-cell{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:8px;text-align:center;}
.time-cell .tc-lbl{font-size:10px;color:#94a3b8;text-transform:uppercase;}
.time-cell .tc-val{font-size:13px;font-weight:700;}
.progress-thin{height:6px;border-radius:999px;}
.source-badge{font-size:10px;padding:2px 8px;border-radius:999px;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Investment Portfolio</h2>
        <p class="text-muted mb-0">Track all business investments. Egg &amp; livestock returns are auto-calculated from actual sales data.</p>
    </div>
    <a href="<?= $base ?>/investments/create" class="btn btn-dark"><i class="bi bi-plus-circle me-1"></i>Add Investment</a>
</div>

<?php if ($pendingCount > 0): ?>
<div class="alert alert-info mb-4">
    <i class="bi bi-hourglass-split me-2"></i>
    <strong><?= $pendingCount ?> investment(s) are pending revenue.</strong>
    No sales recorded yet and no expected return set. ROI and returns will appear once sales are recorded or an expected return is entered.
</div>
<?php endif; ?>

<!-- Portfolio KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #3b82f6;">
            <div class="lbl">Total Invested</div>
            <div class="val text-primary">GHS <?= number_format((float)($totals['total_invested']??0),2) ?></div>
            <div class="sub"><?= number_format((int)($totals['total_records']??0)) ?> investments</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #22c55e;">
            <div class="lbl">Actual Revenue</div>
            <div class="val text-success">GHS <?= number_format((float)($totals['total_actual_revenue']??0),2) ?></div>
            <div class="sub">From egg &amp; bird sales</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #f59e0b;">
            <div class="lbl">Net Gain</div>
            <?php if (!$hasRoi && $pendingCount > 0): ?>
                <div class="val text-secondary">Pending</div>
                <div class="sub">Awaiting revenue data</div>
            <?php else: ?>
                <div class="val <?= (float)($totals['total_net_gain']??0) >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($totals['total_net_gain']??0),2) ?></div>
                <div class="sub">Return minus invested</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #8b5cf6;">
            <div class="lbl">Portfolio ROI</div>
            <?php if (!$hasRoi && $pendingCount > 0): ?>
                <div class="val text-secondary">Pending</div>
                <div class="sub">No revenue yet</div>
            <?php else: ?>
                <div class="val <?= $roiPct >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($roiPct,1) ?>%</div>
                <div class="sub">Overall return rate</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #06b6d4;">
            <div class="lbl">Book Value</div>
            <div class="val">GHS <?= number_format((float)($totals['total_book_value']??0),2) ?></div>
            <div class="sub">After depreciation</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="inv-kpi" style="border-left:4px solid #ef4444;">
            <div class="lbl">Annual Depreciation</div>
            <div class="val text-warning">GHS <?= number_format((float)($totals['annual_depreciation']??0),2) ?></div>
            <div class="sub">Equipment &amp; tech only</div>
        </div>
    </div>
</div>

<!-- Portfolio return schedule -->
<div class="inv-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2 text-primary"></i>Portfolio Return Schedule</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="border rounded-4 p-3 bg-light">
                <div class="fw-semibold small text-success mb-2"><i class="bi bi-graph-up me-1"></i>Return Projections (all investments)</div>
                <?php if ((float)($totals['annual_return']??0) > 0): ?>
                <div class="time-grid">
                    <div class="time-cell"><div class="tc-lbl">Weekly</div><div class="tc-val text-success">GHS <?= number_format((float)($totals['weekly_return']??0),2) ?></div></div>
                    <div class="time-cell"><div class="tc-lbl">Monthly</div><div class="tc-val text-success">GHS <?= number_format((float)($totals['monthly_return']??0),2) ?></div></div>
                    <div class="time-cell"><div class="tc-lbl">Yearly</div><div class="tc-val text-success">GHS <?= number_format((float)($totals['annual_return']??0),2) ?></div></div>
                </div>
                <?php else: ?>
                    <div class="small text-muted">No return projections yet. Set an expected return or record sales to see projections.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded-4 p-3 bg-light">
                <div class="fw-semibold small text-warning mb-2"><i class="bi bi-arrow-down-circle me-1"></i>Depreciation Schedule (equipment/tech)</div>
                <?php if ((float)($totals['annual_depreciation']??0) > 0): ?>
                <div class="time-grid">
                    <div class="time-cell"><div class="tc-lbl">Weekly</div><div class="tc-val text-warning">GHS <?= number_format((float)($totals['weekly_depreciation']??0),2) ?></div></div>
                    <div class="time-cell"><div class="tc-lbl">Monthly</div><div class="tc-val text-warning">GHS <?= number_format((float)($totals['monthly_depreciation']??0),2) ?></div></div>
                    <div class="time-cell"><div class="tc-lbl">Yearly</div><div class="tc-val text-warning">GHS <?= number_format((float)($totals['annual_depreciation']??0),2) ?></div></div>
                </div>
                <?php else: ?>
                    <div class="small text-muted">No depreciable assets (equipment/technology) recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Investment cards -->
<?php if (!empty($records)): ?>
<div class="row g-4 mb-4">
    <?php foreach ($records as $r): ?>
        <?php
        $amount    = (float)($r['amount']             ?? 0);
        $bookVal   = (float)($r['current_book_value'] ?? $amount);
        $deprPct   = $amount > 0 ? min(100, (($amount - $bookVal) / $amount) * 100) : 0;
        $effectRet = (float)($r['effective_return']   ?? 0);
        $returnPct = $effectRet > 0 ? min(100, ((float)($r['return_to_date']??0) / $effectRet) * 100) : 0;
        $typeColor = $typeColors[$r['investment_type']??'other'] ?? 'dark';
        $typeIcon  = $typeIcons[$r['investment_type']??'other']  ?? 'bi-box';
        $isLive    = in_array($r['investment_type']??'', ['eggs','livestock']);
        $isPending = $isLive && (float)($r['actual_revenue']??0) <= 0 && (float)($r['expected_return']??0) <= 0;
        ?>
        <div class="col-lg-6">
            <div class="inv-card p-4 h-100">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-<?= $typeColor ?>"><i class="bi <?= $typeIcon ?> me-1"></i><?= htmlspecialchars($typeLabels[$r['investment_type']??''] ?? ucfirst($r['investment_type']??'')) ?></span>
                            <?php if ($isPending): ?>
                                <span class="source-badge bg-warning text-dark">Pending</span>
                            <?php elseif ($isLive && (float)($r['actual_revenue']??0) > 0): ?>
                                <span class="source-badge bg-success text-white">Live Data</span>
                            <?php else: ?>
                                <span class="source-badge bg-secondary text-white">Projected</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold"><?= htmlspecialchars($r['title']??'') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($r['investment_date']??'') ?> · <?= htmlspecialchars($r['farm_name']??'') ?> · <?= number_format((float)($r['elapsed_months']??0),1) ?> months ago</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?= $r['status']==='active'?'success':($r['status']==='disposed'?'danger':'secondary') ?>"><?= ucfirst($r['status']??'') ?></span>
                        <div class="fw-bold text-primary mt-1">GHS <?= number_format($amount,2) ?></div>
                    </div>
                </div>

                <!-- Status banner -->
                <?php if ($isLive && (float)($r['actual_revenue']??0) > 0): ?>
                    <div class="alert alert-success py-2 small mb-3">
                        <i class="bi bi-check-circle me-1"></i>
                        <strong>Actual Revenue:</strong> GHS <?= number_format((float)$r['actual_revenue'],2) ?> from real sales.
                    </div>
                <?php elseif ($isPending): ?>
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-hourglass-split me-1"></i>
                        <strong>Pending.</strong> No <?= $r['investment_type']==='eggs' ? 'egg' : 'livestock' ?> sales yet.
                        <a href="<?= $base ?>/investments/edit?id=<?= (int)$r['id'] ?>" class="alert-link">Set expected return →</a>
                    </div>
                <?php elseif ($isLive): ?>
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Projected from expected return of GHS <?= number_format((float)($r['expected_return']??0),2) ?>.
                    </div>
                <?php endif; ?>

                <!-- Return rate -->
                <div class="mb-3">
                    <div class="small fw-semibold text-success mb-1">
                        <?= $isLive && (float)($r['actual_revenue']??0) > 0 ? 'Actual Return Rate' : 'Projected Return Rate' ?>
                    </div>
                    <?php if ((float)($r['annual_return']??0) > 0): ?>
                    <div class="time-grid">
                        <div class="time-cell"><div class="tc-lbl">Weekly</div><div class="tc-val text-success">GHS <?= number_format((float)($r['weekly_return']??0),2) ?></div></div>
                        <div class="time-cell"><div class="tc-lbl">Monthly</div><div class="tc-val text-success">GHS <?= number_format((float)($r['monthly_return']??0),2) ?></div></div>
                        <div class="time-cell"><div class="tc-lbl">Yearly</div><div class="tc-val text-success">GHS <?= number_format((float)($r['annual_return']??0),2) ?></div></div>
                    </div>
                    <?php else: ?>
                        <div class="small text-muted">Set an expected return or record sales to see return projections.</div>
                    <?php endif; ?>
                </div>

                <!-- Depreciation (physical assets only) -->
                <?php if (in_array($r['investment_type']??'', ['equipment','infrastructure','technology'])): ?>
                <div class="mb-3">
                    <div class="small fw-semibold text-warning mb-1">Depreciation Schedule</div>
                    <div class="time-grid">
                        <div class="time-cell"><div class="tc-lbl">Weekly</div><div class="tc-val text-warning">GHS <?= number_format((float)($r['weekly_depreciation']??0),2) ?></div></div>
                        <div class="time-cell"><div class="tc-lbl">Monthly</div><div class="tc-val text-warning">GHS <?= number_format((float)($r['monthly_depreciation']??0),2) ?></div></div>
                        <div class="time-cell"><div class="tc-lbl">Yearly</div><div class="tc-val text-warning">GHS <?= number_format((float)($r['annual_depreciation']??0),2) ?></div></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Key metrics -->
                <div class="row g-2 mb-3 text-center">
                    <div class="col-3">
                        <div class="small text-muted">ROI</div>
                        <?php if ($isPending): ?>
                            <div class="fw-bold text-secondary">Pending</div>
                        <?php else: ?>
                            <div class="fw-bold <?= (float)($r['roi_pct']??0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format((float)($r['roi_pct']??0),1) ?>%</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Net Gain</div>
                        <?php if ($isPending): ?>
                            <div class="fw-bold text-secondary">Pending</div>
                        <?php else: ?>
                            <div class="fw-bold <?= (float)($r['net_gain']??0) >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($r['net_gain']??0),2) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Payback</div>
                        <div class="fw-bold"><?= (float)($r['payback_years']??0) > 0 ? number_format((float)$r['payback_years'],1).' yrs' : 'N/A' ?></div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted">Book Value</div>
                        <div class="fw-bold">GHS <?= number_format($bookVal,2) ?></div>
                    </div>
                </div>

                <!-- Depreciation progress bar -->
                <?php if ((float)($r['useful_life_years']??0) > 0 && $deprPct > 0): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Depreciated</span>
                        <span><?= number_format($deprPct,1) ?>% · <?= number_format((float)($r['remaining_life_years']??0),1) ?> yrs remaining</span>
                    </div>
                    <div class="progress progress-thin"><div class="progress-bar bg-warning" style="width:<?= $deprPct ?>%"></div></div>
                </div>
                <?php endif; ?>

                <!-- Return progress bar -->
                <?php if ($effectRet > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Return earned</span>
                        <span class="text-success">GHS <?= number_format((float)($r['return_to_date']??0),2) ?> / GHS <?= number_format($effectRet,2) ?></span>
                    </div>
                    <div class="progress progress-thin"><div class="progress-bar bg-success" style="width:<?= $returnPct ?>%"></div></div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <a href="<?= $base ?>/investments/view?id=<?= (int)$r['id'] ?>" class="btn btn-outline-primary btn-sm">View</a>
                    <a href="<?= $base ?>/investments/edit?id=<?= (int)$r['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                    <a href="<?= $base ?>/investments/delete?id=<?= (int)$r['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="inv-card p-5 text-center text-muted mb-4">
    <i class="bi bi-graph-up fs-1 d-block mb-3 opacity-25"></i>
    <p>No investments yet. <a href="<?= $base ?>/investments/create">Add your first investment</a>.</p>
</div>
<?php endif; ?>

<!-- Investment Principles Guide -->
<div class="inv-card p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Investment Principles Guide</h6>
    <div class="row g-3">
        <?php
        $principles = [
            ['icon'=>'🥚','type'=>'Income',      'color'=>'warning', 'title'=>'Income Investment (Eggs)',       'desc'=>'Recurring revenue from egg production. Returns grow with flock health and size. Best for steady cash flow.'],
            ['icon'=>'🐔','type'=>'Growth',       'color'=>'success', 'title'=>'Growth Investment (Livestock)',  'desc'=>'Capital appreciation through bird growth. Sell at peak weight for maximum return. Time-sensitive.'],
            ['icon'=>'🔧','type'=>'Capital',      'color'=>'primary', 'title'=>'Capital Investment (Equipment)', 'desc'=>'Long-term assets that reduce operating costs. Depreciate over useful life. ROI measured in efficiency gains.'],
            ['icon'=>'🌍','type'=>'Appreciation', 'color'=>'info',    'title'=>'Appreciation Asset (Land)',      'desc'=>'Value increases over time. No depreciation. Productive use (housing, expansion) generates additional income.'],
        ];
        foreach ($principles as $p):
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="border rounded-4 p-3 h-100 border-<?= $p['color'] ?>">
                <div class="fs-2 mb-2"><?= $p['icon'] ?></div>
                <div class="fw-semibold small text-<?= $p['color'] ?> mb-1"><?= $p['type'] ?></div>
                <div class="fw-bold small mb-2"><?= $p['title'] ?></div>
                <div class="text-muted" style="font-size:11px;"><?= $p['desc'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="alert alert-info py-2 small mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Investor Share:</strong> Add <code>investor_share:XX</code> in the Notes field (e.g. <code>investor_share:30</code> for 30%) to auto-calculate investor weekly/monthly/annual returns on the detail page.
    </div>
</div>

<!-- Summary by type -->
<?php if (!empty($byType)): ?>
<div class="inv-card p-4">
    <h6 class="fw-bold mb-3">Summary by Investment Type</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr><th>Type</th><th>Count</th><th>Invested</th><th>Actual Revenue</th><th>Weekly Return</th><th>Monthly Return</th><th>Annual Return</th><th>ROI</th></tr>
            </thead>
            <tbody>
                <?php foreach ($byType as $t): ?>
                    <tr>
                        <td><span class="badge bg-<?= $typeColors[$t['investment_type']??'other']??'dark' ?>"><i class="bi <?= $typeIcons[$t['investment_type']??'other']??'bi-box' ?> me-1"></i><?= htmlspecialchars($typeLabels[$t['investment_type']??'']??ucfirst($t['investment_type']??'')) ?></span></td>
                        <td><?= number_format((int)($t['records']??0)) ?></td>
                        <td class="fw-bold text-primary">GHS <?= number_format((float)($t['total']??0),2) ?></td>
                        <td><?= (float)($t['actual_revenue']??0) > 0 ? '<span class="text-success">GHS '.number_format((float)$t['actual_revenue'],2).'</span>' : '<span class="text-muted">—</span>' ?></td>
                        <td><?= (float)($t['weekly_return']??0) > 0 ? 'GHS '.number_format((float)$t['weekly_return'],2) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= (float)($t['monthly_return']??0) > 0 ? 'GHS '.number_format((float)$t['monthly_return'],2) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= (float)($t['annual_return']??0) > 0 ? 'GHS '.number_format((float)$t['annual_return'],2) : '<span class="text-muted">—</span>' ?></td>
                        <td class="fw-bold">
                            <?php if ($t['roi_pct'] === null): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php else: ?>
                                <span class="<?= (float)$t['roi_pct'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format((float)$t['roi_pct'],1) ?>%</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

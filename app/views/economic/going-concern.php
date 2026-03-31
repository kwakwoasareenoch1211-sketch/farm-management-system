<?php
$currentRevenue = (float)($currentMonthTotals['sales_revenue'] ?? 0);
$currentExpense = (float)($currentMonthTotals['total_expense'] ?? 0);
$currentNet = (float)($currentMonthTotals['net_position'] ?? 0);

$assetStrength = $assets > 0 ? max(0, min(100, (($assets - $liabilities) / max(1, $assets)) * 100)) : 0;
$liquidityStrength = max(0, min(100, $liquidityRatio * 40));
$profitabilityStrength = max(0, min(100, $profitMargin <= 0 ? 10 : $profitMargin * 3));
$overallHealthStrength = max(0, min(100, $healthScore));

$continuityValues = [
    $assetStrength,
    $liquidityStrength,
    $profitabilityStrength,
    $overallHealthStrength
];

$continuityRisks = [];
$continuitySupports = [];
$continuityActions = [];

if ($assets > $liabilities) {
    $continuitySupports[] = 'Assets are higher than liabilities, which strengthens operational continuity.';
} else {
    $continuityRisks[] = 'Liabilities are equal to or greater than assets, which weakens long-term continuity.';
}

if ($currentRevenue >= $currentExpense) {
    $continuitySupports[] = 'Current month revenue is covering current month expenses.';
} else {
    $continuityRisks[] = 'Current month expenses are higher than revenue, which creates pressure on survival.';
}

if ($workingCapital > 0) {
    $continuitySupports[] = 'Working capital is positive, supporting day-to-day operations.';
} else {
    $continuityRisks[] = 'Working capital is weak or negative, making operations more vulnerable.';
}

if ($liquidityRatio >= 1) {
    $continuitySupports[] = 'Liquidity is at a safer level for short-term obligations.';
} else {
    $continuityRisks[] = 'Liquidity is below a safer level and should be improved.';
}

if (count($lossMakingBatches) > 0) {
    $continuityRisks[] = number_format(count($lossMakingBatches)) . ' batch(es) are currently loss-making and reducing business strength.';
} else {
    $continuitySupports[] = 'No batch is currently showing a loss signal.';
}

if ($goingConcernStatus === 'Healthy') {
    $continuityActions[] = 'Maintain discipline in cost control and continue monitoring batch profitability.';
    $continuityActions[] = 'Expansion can be considered gradually, with close attention to liquidity and margins.';
} elseif ($goingConcernStatus === 'Caution') {
    $continuityActions[] = 'Tighten expense control and protect working capital before expansion.';
    $continuityActions[] = 'Review weak cost centers, batch profitability, and customer payment delays.';
} else {
    $continuityActions[] = 'Delay expansion and prioritize business stabilization immediately.';
    $continuityActions[] = 'Reduce pressure from liabilities, improve cash flow, and correct weak operational areas.';
}
?>

<style>
    .gc-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .gc-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #7c2d12 0%, #b45309 50%, #f59e0b 100%);
        color: #fff;
        padding: 24px;
        box-shadow: 0 14px 32px rgba(180, 83, 9, 0.20);
    }

    .gc-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        height: 100%;
    }

    .gc-good {
        border-left: 4px solid #16a34a;
        background: #f0fdf4;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .gc-bad {
        border-left: 4px solid #dc2626;
        background: #fef2f2;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .gc-tip {
        border-left: 4px solid #2563eb;
        background: #eff6ff;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .gc-chart-wrap {
        position: relative;
        min-height: 320px;
    }

    .gc-status-badge {
        font-size: 0.85rem;
        padding: 8px 14px;
        border-radius: 999px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-warning mb-2 px-3 py-2">Continuity Analysis</span>
        <h2 class="fw-bold mb-1">Going Concern</h2>
        <p class="text-muted mb-0">Assess whether the business can continue operating safely and sustainably under current conditions.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= rtrim(BASE_URL, '/') ?>/economic" class="btn btn-outline-secondary btn-sm">Economic Dashboard</a>
        <a href="<?= rtrim(BASE_URL, '/') ?>/business-health" class="btn btn-outline-dark btn-sm">Business Health</a>
        <a href="<?= rtrim(BASE_URL, '/') ?>/decision-support" class="btn btn-dark btn-sm">Decision Support</a>
    </div>
</div>

<div class="gc-hero mb-4 text-center">
    <div class="display-4 fw-bold"><?= htmlspecialchars($goingConcernStatus) ?></div>
    <span class="badge text-bg-<?= $goingConcernClass ?> gc-status-badge"><?= htmlspecialchars($goingConcernStatus) ?></span>
    <div class="mt-2 text-white-50"><?= htmlspecialchars($goingConcernMessage) ?></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Assets</div>
            <div class="fs-4 fw-bold">GHS <?= number_format($assets, 2) ?></div>
            <div class="small text-muted mt-1">Resource strength of the business</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Liabilities</div>
            <div class="fs-4 fw-bold">GHS <?= number_format($liabilities, 2) ?></div>
            <div class="small text-muted mt-1">Outstanding obligations</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Current Revenue</div>
            <div class="fs-4 fw-bold">GHS <?= number_format($currentRevenue, 2) ?></div>
            <div class="small text-muted mt-1">Revenue for the current month</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Current Expenses</div>
            <div class="fs-4 fw-bold">GHS <?= number_format($currentExpense, 2) ?></div>
            <div class="small text-muted mt-1">Cost burden for the current month</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Current Net</div>
            <div class="fs-4 fw-bold <?= $currentNet >= 0 ? 'text-success' : 'text-danger' ?>">
                GHS <?= number_format($currentNet, 2) ?>
            </div>
            <div class="small text-muted mt-1">Revenue minus expenses</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Working Capital</div>
            <div class="fs-4 fw-bold <?= $workingCapital >= 0 ? 'text-success' : 'text-danger' ?>">
                GHS <?= number_format($workingCapital, 2) ?>
            </div>
            <div class="small text-muted mt-1">Assets minus liabilities</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Liquidity Ratio</div>
            <div class="fs-4 fw-bold"><?= number_format($liquidityRatio, 2) ?></div>
            <div class="small text-muted mt-1">Ability to cover short-term pressure</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gc-soft">
            <div class="small text-muted">Health Score</div>
            <div class="fs-4 fw-bold"><?= number_format($healthScore) ?>%</div>
            <div class="small text-muted mt-1">Overall business condition</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="gc-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Continuity Strength Profile</h5>
                <span class="badge text-bg-dark gc-status-badge">Continuity Factors</span>
            </div>

            <div class="gc-chart-wrap">
                <canvas id="goingConcernChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gc-card p-4 h-100">
            <h5 class="fw-bold mb-3">Interpretation</h5>

            <div class="gc-good mb-3">
                <div class="fw-semibold">Supportive sign</div>
                <div class="small">
                    <?= ($assets > $liabilities)
                        ? 'Assets are stronger than liabilities, which supports continuity.'
                        : 'Assets are not comfortably stronger than liabilities, so continuity is less secure.' ?>
                </div>
            </div>

            <div class="gc-bad mb-3">
                <div class="fw-semibold">Pressure sign</div>
                <div class="small">
                    <?= ($currentExpense > $currentRevenue)
                        ? 'Current expenses are above revenue, which weakens continuity.'
                        : 'Revenue is currently covering expenses, which supports continuity.' ?>
                </div>
            </div>

            <div class="gc-tip">
                <div class="fw-semibold">Management meaning</div>
                <div class="small">
                    This page estimates whether the business can remain operational without severe financial strain. It considers asset strength, liabilities, monthly net position, liquidity, health score, and weak batch performance.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="gc-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Supportive Signals</h5>
                <span class="badge text-bg-success gc-status-badge">Positive</span>
            </div>

            <?php if (!empty($continuitySupports)): ?>
                <?php foreach ($continuitySupports as $item): ?>
                    <div class="gc-good mb-2">
                        <div class="small"><?= htmlspecialchars($item) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No supportive continuity signal was detected.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="gc-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Warning Signals</h5>
                <span class="badge text-bg-danger gc-status-badge">Risk</span>
            </div>

            <?php if (!empty($continuityRisks)): ?>
                <?php foreach ($continuityRisks as $item): ?>
                    <div class="gc-bad mb-2">
                        <div class="small"><?= htmlspecialchars($item) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No major continuity warning signal detected right now.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="gc-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Immediate Management Focus</h5>
                <span class="badge text-bg-primary gc-status-badge">Action Plan</span>
            </div>

            <?php if (!empty($continuityActions)): ?>
                <div class="row g-3">
                    <?php foreach ($continuityActions as $item): ?>
                        <div class="col-md-6">
                            <div class="gc-tip h-100">
                                <div class="small"><?= htmlspecialchars($item) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted">No immediate continuity action is available yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const ctx = document.getElementById('goingConcernChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Asset Strength', 'Liquidity', 'Profitability', 'Overall Health'],
            datasets: [{
                label: 'Continuity Score',
                data: <?= json_encode($continuityValues) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
})();
</script>
<?php
$currentRevenue = (float)($currentMonthTotals['sales_revenue'] ?? 0);
$currentExpense = (float)($currentMonthTotals['total_expense'] ?? 0);
$currentNet = (float)($currentMonthTotals['net_position'] ?? 0);

$healthReadiness = max(0, min(100, $healthScore));
$profitReadiness = max(0, min(100, $profitMargin <= 0 ? 10 : $profitMargin * 3));
$liquidityReadiness = max(0, min(100, $liquidityRatio * 40));
$growthReadiness = $trendSignal === 'Positive' ? 85 : ($trendSignal === 'Moderate' ? 60 : 30);

$decisionValues = [
    $healthReadiness,
    $profitReadiness,
    $liquidityReadiness,
    $growthReadiness
];

$decisionSupports = [];
$decisionBlocks = [];
$decisionActions = [];

if ($currentNet > 0) {
    $decisionSupports[] = 'The business is currently generating a positive monthly net result.';
} else {
    $decisionBlocks[] = 'The business is currently operating at a negative monthly net result.';
}

if ($assets > $liabilities) {
    $decisionSupports[] = 'Assets are stronger than liabilities, which improves decision confidence.';
} else {
    $decisionBlocks[] = 'Liabilities are putting pressure on the asset base.';
}

if ($liquidityRatio >= 1) {
    $decisionSupports[] = 'Liquidity is at a safer level for operational commitments.';
} else {
    $decisionBlocks[] = 'Liquidity is weak and may limit expansion or new commitments.';
}

if (count($lossMakingBatches) > 0) {
    $decisionBlocks[] = number_format(count($lossMakingBatches)) . ' batch(es) are currently loss-making and should be reviewed first.';
} else {
    $decisionSupports[] = 'No batch is currently showing a loss signal.';
}

if ($trendSignal === 'Positive') {
    $decisionSupports[] = 'The recent business trend is positive.';
} elseif ($trendSignal === 'Weak') {
    $decisionBlocks[] = 'The recent business trend is weak and does not strongly support growth.';
}

if (!empty($topBatch) && (float)($topBatch['gross_profit'] ?? 0) > 0) {
    $decisionSupports[] = 'Top batch ' . ($topBatch['batch_code'] ?? 'N/A') . ' is contributing positively to business performance.';
}

if (!empty($worstBatch) && (float)($worstBatch['gross_profit'] ?? 0) < 0) {
    $decisionBlocks[] = 'Weakest batch ' . ($worstBatch['batch_code'] ?? 'N/A') . ' is reducing profitability.';
}

if ($decisionRecommendation === 'Expansion Possible') {
    $decisionActions[] = 'Expand gradually rather than aggressively, and keep monthly monitoring active.';
    $decisionActions[] = 'Protect liquidity while growing so expansion does not create cash pressure.';
    $decisionActions[] = 'Use top-performing batches and sales areas as the model for expansion.';
} elseif ($decisionRecommendation === 'Review Operations') {
    $decisionActions[] = 'Review weak batches, cost-heavy activities, and pricing structure before any expansion.';
    $decisionActions[] = 'Reduce the sources of loss and improve revenue efficiency first.';
    $decisionActions[] = 'Strengthen customer payment collection and working capital control.';
} else {
    $decisionActions[] = 'Stabilize cash flow and reduce financial pressure before taking new growth decisions.';
    $decisionActions[] = 'Improve profitability, liquidity, and continuity indicators before expansion.';
    $decisionActions[] = 'Focus on internal corrections and operating discipline.';
}
?>

<style>
    .ds-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .ds-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
        color: #fff;
        padding: 24px;
        box-shadow: 0 14px 32px rgba(37, 99, 235, 0.20);
    }

    .ds-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        height: 100%;
    }

    .ds-note {
        border-left: 4px solid #2563eb;
        background: #eff6ff;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .ds-good {
        border-left: 4px solid #16a34a;
        background: #f0fdf4;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .ds-warn {
        border-left: 4px solid #dc2626;
        background: #fef2f2;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .ds-chart-wrap {
        position: relative;
        min-height: 320px;
    }

    .ds-status-badge {
        font-size: 0.85rem;
        padding: 8px 14px;
        border-radius: 999px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-primary mb-2 px-3 py-2">Decision Intelligence</span>
        <h2 class="fw-bold mb-1">Decision Support</h2>
        <p class="text-muted mb-0">System recommendation based on the condition of the whole business.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= rtrim(BASE_URL, '/') ?>/economic" class="btn btn-outline-secondary btn-sm">Economic Dashboard</a>
        <a href="<?= rtrim(BASE_URL, '/') ?>/business-health" class="btn btn-outline-dark btn-sm">Business Health</a>
        <a href="<?= rtrim(BASE_URL, '/') ?>/going-concern" class="btn btn-dark btn-sm">Going Concern</a>
    </div>
</div>

<div class="ds-hero mb-4 text-center">
    <div class="display-4 fw-bold"><?= htmlspecialchars($decisionRecommendation) ?></div>
    <span class="badge text-bg-<?= $decisionClass ?> ds-status-badge"><?= htmlspecialchars($decisionRecommendation) ?></span>
    <div class="mt-2 text-white-50"><?= htmlspecialchars($decisionReason) ?></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="ds-soft">
            <div class="small text-muted">Health Score</div>
            <div class="fs-4 fw-bold"><?= number_format($healthScore) ?>%</div>
            <div class="small text-muted mt-1">Overall business condition</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ds-soft">
            <div class="small text-muted">Profit Margin</div>
            <div class="fs-4 fw-bold"><?= number_format($profitMargin, 2) ?>%</div>
            <div class="small text-muted mt-1">Current profit strength</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ds-soft">
            <div class="small text-muted">Liquidity Ratio</div>
            <div class="fs-4 fw-bold"><?= number_format($liquidityRatio, 2) ?></div>
            <div class="small text-muted mt-1">Cash-support capacity</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ds-soft">
            <div class="small text-muted">Trend Signal</div>
            <div class="fs-4 fw-bold"><?= htmlspecialchars($trendSignal) ?></div>
            <div class="small text-muted mt-1">Recent direction of performance</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="ds-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Decision Readiness Analysis</h5>
                <span class="badge text-bg-dark ds-status-badge">Recommendation Factors</span>
            </div>

            <div class="ds-chart-wrap">
                <canvas id="decisionSupportChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="ds-card p-4 h-100">
            <h5 class="fw-bold mb-3">Interpretation</h5>

            <div class="ds-good mb-3">
                <div class="fw-semibold">Expansion signal</div>
                <div class="small">
                    <?= $decisionRecommendation === 'Expansion Possible'
                        ? 'The business currently shows enough support for controlled expansion.'
                        : 'The business does not yet show full support for confident expansion.' ?>
                </div>
            </div>

            <div class="ds-warn mb-3">
                <div class="fw-semibold">Main blocking factor</div>
                <div class="small">
                    <?=
                    $currentNet < 0 ? 'Negative monthly net performance is currently limiting expansion confidence.' :
                    (count($lossMakingBatches) > 0 ? 'Weak batch performance is reducing the strength of growth decisions.' :
                    ($liquidityRatio < 1 ? 'Liquidity is too weak for safe expansion at this time.' :
                    'No severe block is visible, but monitoring should remain active.'))
                    ?>
                </div>
            </div>

            <div class="ds-note">
                <div class="fw-semibold">Management meaning</div>
                <div class="small">
                    This page combines business health, profitability, liquidity, trend direction, continuity, and batch strength before recommending whether to expand, stabilize, or review operations.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="ds-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Decision Supports</h5>
                <span class="badge text-bg-success ds-status-badge">Positive</span>
            </div>

            <?php if (!empty($decisionSupports)): ?>
                <?php foreach ($decisionSupports as $item): ?>
                    <div class="ds-good mb-2">
                        <div class="small"><?= htmlspecialchars($item) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No strong support signal detected yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ds-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Decision Blocks</h5>
                <span class="badge text-bg-danger ds-status-badge">Risk</span>
            </div>

            <?php if (!empty($decisionBlocks)): ?>
                <?php foreach ($decisionBlocks as $item): ?>
                    <div class="ds-warn mb-2">
                        <div class="small"><?= htmlspecialchars($item) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No major block detected right now.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ds-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Business Drivers</h5>
                <span class="badge text-bg-primary ds-status-badge">Drivers</span>
            </div>

            <div class="ds-soft mb-2">Strong batches: <strong><?= number_format(count($strongBatches)) ?></strong></div>
            <div class="ds-soft mb-2">Loss-making batches: <strong><?= number_format(count($lossMakingBatches)) ?></strong></div>
            <div class="ds-soft mb-2">Working capital: <strong>GHS <?= number_format($workingCapital, 2) ?></strong></div>
            <div class="ds-soft">Monthly net: <strong>GHS <?= number_format($currentNet, 2) ?></strong></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="ds-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recommended Management Actions</h5>
                <span class="badge text-bg-warning ds-status-badge">Action Plan</span>
            </div>

            <?php if (!empty($decisionActions)): ?>
                <div class="row g-3">
                    <?php foreach ($decisionActions as $item): ?>
                        <div class="col-md-4">
                            <div class="ds-note h-100">
                                <div class="small"><?= htmlspecialchars($item) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted">No specific decision action is available yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const ctx = document.getElementById('decisionSupportChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Health', 'Profitability', 'Liquidity', 'Growth Trend'],
            datasets: [{
                label: 'Decision Readiness',
                data: <?= json_encode($decisionValues) ?>,
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
<style>
    .bh-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .bh-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #0f766e 0%, #0f9f8f 50%, #14b8a6 100%);
        color: #fff;
        padding: 24px;
    }

    .bh-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        height: 100%;
    }

    .bh-note-good {
        border-left: 4px solid #16a34a;
        background: #f0fdf4;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .bh-note-bad {
        border-left: 4px solid #dc2626;
        background: #fef2f2;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .bh-note-tip {
        border-left: 4px solid #2563eb;
        background: #eff6ff;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .bh-chart-wrap {
        position: relative;
        min-height: 340px;
    }
</style>

<?php
$profitabilityFactor = max(0, min(100, $profitMargin <= 0 ? 10 : $profitMargin * 3));
$liquidityFactor = max(0, min(100, $liquidityRatio * 40));
$solvencyFactor = $assets > 0 ? max(0, min(100, (($assets - $liabilities) / max(1, $assets)) * 100)) : 0;
$efficiencyFactor = count($strongBatches) + count($lossMakingBatches) > 0
    ? max(0, min(100, (count($strongBatches) / max(1, count($strongBatches) + count($lossMakingBatches))) * 100))
    : 0;
$growthFactor = $trendSignal === 'Positive' ? 85 : ($trendSignal === 'Moderate' ? 60 : 30);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge rounded-pill text-bg-success mb-2 px-3 py-2">Business Intelligence</span>
        <h2 class="fw-bold mb-1">Business Health</h2>
        <p class="text-muted mb-0">Deep assessment of the farm’s economic condition and performance balance.</p>
    </div>
    <a href="<?= rtrim(BASE_URL, '/') ?>/economic" class="btn btn-outline-secondary">Back</a>
</div>

<div class="bh-hero mb-4 text-center">
    <div class="display-4 fw-bold"><?= number_format($healthScore) ?>%</div>
    <span class="badge text-bg-<?= $healthClass ?> px-3 py-2 rounded-pill"><?= htmlspecialchars($healthLabel) ?></span>
    <div class="mt-2 text-white-50">Overall business health score</div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="bh-soft"><div class="small text-muted">Profit Margin</div><div class="fs-4 fw-bold"><?= number_format($profitMargin, 2) ?>%</div></div></div>
    <div class="col-md-3"><div class="bh-soft"><div class="small text-muted">Liquidity Ratio</div><div class="fs-4 fw-bold"><?= number_format($liquidityRatio, 2) ?></div></div></div>
    <div class="col-md-3"><div class="bh-soft"><div class="small text-muted">Working Capital</div><div class="fs-4 fw-bold">GHS <?= number_format($workingCapital, 2) ?></div></div></div>
    <div class="col-md-3"><div class="bh-soft"><div class="small text-muted">ROI</div><div class="fs-4 fw-bold"><?= number_format($roi, 2) ?>%</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="bh-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Health Factor Analysis</h5>
                <span class="badge text-bg-primary px-3 py-2 rounded-pill">Five Factors</span>
            </div>
            <div class="bh-chart-wrap">
                <canvas id="businessHealthChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="bh-card p-4 h-100">
            <h5 class="fw-bold mb-3">Interpretation</h5>

            <div class="bh-note-good mb-3">
                <div class="fw-semibold">What is working</div>
                <div class="small">
                    <?php
                    if ($healthScore >= 80) {
                        echo 'The business is performing strongly across most health indicators.';
                    } elseif ($healthScore >= 60) {
                        echo 'The business is stable but has some weak areas that need attention.';
                    } else {
                        echo 'The business is under pressure and needs stronger operational and financial control.';
                    }
                    ?>
                </div>
            </div>

            <div class="bh-note-bad mb-3">
                <div class="fw-semibold">Main pressure point</div>
                <div class="small">
                    <?php
                    if ($profitMargin <= 0) {
                        echo 'Profitability is the biggest pressure point right now.';
                    } elseif ($liquidityRatio < 1) {
                        echo 'Liquidity is the main weakness and needs urgent improvement.';
                    } elseif ($liabilities > $assets) {
                        echo 'Solvency pressure is high because liabilities are overtaking assets.';
                    } else {
                        echo 'Operational efficiency should still be monitored closely.';
                    }
                    ?>
                </div>
            </div>

            <div class="bh-note-tip">
                <div class="fw-semibold">Best next move</div>
                <div class="small">
                    <?= !empty($recommendations[0]) ? htmlspecialchars($recommendations[0]) : 'Continue monitoring trends and maintain healthy performance.' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="bh-card p-4 h-100">
            <h5 class="fw-bold mb-3">Strengths</h5>
            <?php if (!empty($strengths)): ?>
                <?php foreach ($strengths as $item): ?>
                    <div class="bh-note-good mb-2"><div class="small"><?= htmlspecialchars($item) ?></div></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No major strengths detected yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bh-card p-4 h-100">
            <h5 class="fw-bold mb-3">Risk Areas</h5>
            <?php if (!empty($risks)): ?>
                <?php foreach ($risks as $item): ?>
                    <div class="bh-note-bad mb-2"><div class="small"><?= htmlspecialchars($item) ?></div></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No major risk detected right now.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bh-card p-4 h-100">
            <h5 class="fw-bold mb-3">Management Actions</h5>
            <?php if (!empty($recommendations)): ?>
                <?php foreach ($recommendations as $item): ?>
                    <div class="bh-note-tip mb-2"><div class="small"><?= htmlspecialchars($item) ?></div></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">No management actions suggested yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const ctx = document.getElementById('businessHealthChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Profitability', 'Liquidity', 'Solvency', 'Efficiency', 'Growth'],
            datasets: [{
                label: 'Health Factors',
                data: [
                    <?= json_encode($profitabilityFactor) ?>,
                    <?= json_encode($liquidityFactor) ?>,
                    <?= json_encode($solvencyFactor) ?>,
                    <?= json_encode($efficiencyFactor) ?>,
                    <?= json_encode($growthFactor) ?>
                ],
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
})();
</script>
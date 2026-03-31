<?php
$traceability = $traceability ?? [];
$principles = $principles ?? [];
$totals = $totals ?? [];
$base = rtrim(BASE_URL, '/');
?>

<style>
.trace-card {
    border-radius: 16px;
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(15,23,42,.04);
}
.trace-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}
.trace-metric {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
}
.trace-value {
    font-size: 1.3rem;
    font-weight: 800;
    color: #3b82f6;
}
.trace-formula {
    background: #f8fafc;
    border-left: 4px solid #3b82f6;
    padding: 12px 16px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    margin: 10px 0;
    color: #1e293b;
}
.trace-source {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin: 3px;
}
.trace-principle {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-top: 10px;
}
.principle-card {
    border-radius: 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 16px;
    margin-bottom: 12px;
}
.component-item {
    background: #f1f5f9;
    padding: 8px 12px;
    border-radius: 8px;
    margin: 6px 0;
    font-size: 13px;
}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Traceability</span>
        <h2 class="fw-bold mb-1">Calculation Audit Trail</h2>
        <p class="text-muted mb-0">Complete breakdown of all financial metrics with source tables, formulas, and accounting principles.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/financial" class="btn btn-outline-dark">Financial Dashboard</a>
        <a href="<?= $base ?>/economic" class="btn btn-outline-dark">Economic Dashboard</a>
    </div>
</div>

<!-- Summary Overview -->
<div class="trace-card mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
    <h5 class="fw-bold mb-3">Current Financial Position</h5>
    <div class="row g-3 text-center">
        <div class="col-md-2">
            <div class="fs-4 fw-bold text-primary">GHS <?= number_format((float)($totals['total_capital'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Capital</div>
        </div>
        <div class="col-md-2">
            <div class="fs-4 fw-bold text-success">GHS <?= number_format((float)($totals['total_revenue'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Revenue</div>
        </div>
        <div class="col-md-2">
            <div class="fs-4 fw-bold text-danger">GHS <?= number_format((float)($totals['total_expenses'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Expenses</div>
        </div>
        <div class="col-md-2">
            <div class="fs-4 fw-bold text-info">GHS <?= number_format((float)($totals['total_assets'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Assets</div>
        </div>
        <div class="col-md-2">
            <div class="fs-4 fw-bold text-warning">GHS <?= number_format((float)($totals['total_liabilities'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Liabilities</div>
        </div>
        <div class="col-md-2">
            <div class="fs-4 fw-bold <?= (float)($totals['net_worth'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($totals['net_worth'] ?? 0), 2) ?></div>
            <div class="small text-white-50">Net Worth</div>
        </div>
    </div>
</div>

<!-- Calculation Traceability -->
<h5 class="fw-bold mb-3"><i class="bi bi-calculator me-2"></i>Detailed Calculation Breakdown</h5>

<?php foreach ($traceability as $metric => $data): ?>
    <div class="trace-card">
        <div class="trace-header">
            <div>
                <div class="trace-metric"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $metric))) ?></div>
                <div class="small text-muted"><?= htmlspecialchars($data['description'] ?? '') ?></div>
            </div>
            <?php if (isset($totals['total_' . $metric])): ?>
                <div class="trace-value">GHS <?= number_format((float)$totals['total_' . $metric], 2) ?></div>
            <?php elseif (isset($totals[$metric])): ?>
                <div class="trace-value">
                    <?php if (in_array($metric, ['profit_margin', 'debt_ratio', 'roi'])): ?>
                        <?= number_format((float)$totals[$metric], 1) ?>%
                    <?php elseif ($metric === 'current_ratio'): ?>
                        <?php 
                        $ratio = (float)($totals['total_liabilities'] ?? 0) > 0 
                            ? (float)($totals['total_assets'] ?? 0) / (float)$totals['total_liabilities'] 
                            : ((float)($totals['total_assets'] ?? 0) > 0 ? 999 : 0);
                        echo number_format($ratio, 2) . ':1';
                        ?>
                    <?php else: ?>
                        GHS <?= number_format((float)$totals[$metric], 2) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="trace-formula">
            <strong>Formula:</strong> <?= htmlspecialchars($data['formula'] ?? 'N/A') ?>
        </div>

        <div class="mb-2">
            <strong class="small text-muted">Source Tables:</strong><br>
            <?php foreach ($data['source_tables'] ?? [] as $table): ?>
                <span class="trace-source"><?= htmlspecialchars($table) ?></span>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($data['components'])): ?>
            <div class="mt-3">
                <strong class="small text-muted">Components:</strong>
                <?php foreach ($data['components'] as $comp => $formula): ?>
                    <div class="component-item">
                        <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $comp))) ?>:</strong> 
                        <?= htmlspecialchars($formula) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="trace-principle">
            <strong><i class="bi bi-book me-1"></i>Accounting Principle:</strong> 
            <?= htmlspecialchars($data['accounting_principle'] ?? 'N/A') ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Accounting Principles Reference -->
<h5 class="fw-bold mb-3 mt-5"><i class="bi bi-mortarboard me-2"></i>Accounting Principles Reference</h5>

<?php foreach ($principles as $principle => $data): ?>
    <div class="principle-card">
        <h6 class="fw-bold text-primary mb-2"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $principle))) ?></h6>
        
        <?php if (isset($data['formula'])): ?>
            <div class="trace-formula mb-2">
                <?= htmlspecialchars($data['formula']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($data['principle'])): ?>
            <div class="mb-2">
                <strong class="small">Principle:</strong> <?= htmlspecialchars($data['principle']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($data['difference'])): ?>
            <div class="mb-2">
                <strong class="small">Key Difference:</strong> <?= htmlspecialchars($data['difference']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($data['example'])): ?>
            <div class="component-item">
                <strong>Example:</strong> <?= htmlspecialchars($data['example']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($data['explanation'])): ?>
            <div class="small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($data['explanation']) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<!-- Data Integrity Note -->
<div class="alert alert-info border-0 rounded-4 mt-4">
    <div class="d-flex gap-3 align-items-start">
        <i class="bi bi-shield-check fs-3 text-info"></i>
        <div>
            <h6 class="fw-bold mb-2">Data Integrity & Real-Time Calculations</h6>
            <p class="mb-2">All financial metrics are calculated in real-time directly from the database. No cached or estimated values are used.</p>
            <ul class="mb-0 small">
                <li>Liabilities show outstanding balance = principal - payments (real-time)</li>
                <li>Assets include current inventory, live birds, receivables, and investments</li>
                <li>Expenses include all operational costs: feed, medication, vaccination, direct expenses, livestock purchase, and mortality losses</li>
                <li>Revenue is total sales from all transactions</li>
                <li>All calculations follow standard accounting principles (GAAP/IFRS)</li>
            </ul>
        </div>
    </div>
</div>


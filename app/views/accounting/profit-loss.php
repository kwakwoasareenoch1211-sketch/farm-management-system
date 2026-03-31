<style>
    .pl-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .pl-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #111827 0%, #1f2937 50%, #374151 100%);
        color: #fff;
        padding: 24px;
        box-shadow: 0 14px 32px rgba(17, 24, 39, 0.22);
    }

    .pl-kpi {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #eef2f7;
        height: 100%;
    }

    .pl-kpi .title {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .pl-kpi .value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .pl-kpi .meta {
        font-size: 12px;
        color: #94a3b8;
    }

    .pl-soft {
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        height: 100%;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Accounting Report</span>
        <h2 class="fw-bold mb-1">Profit & Loss</h2>
        <p class="text-muted mb-0">Revenue, expenses, margins, and profitability summary for the business.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= rtrim(BASE_URL, '/') ?>/financial" class="btn btn-outline-dark btn-sm">Financial Dashboard</a>
        <a href="<?= rtrim(BASE_URL, '/') ?>/sales" class="btn btn-dark btn-sm">Sales Dashboard</a>
    </div>
</div>

<div class="pl-hero mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <h4 class="mb-1 fw-bold">Net Profit Position</h4>
            <p class="mb-0 text-white-50">Overall revenue minus all linked and direct expenses.</p>
        </div>
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-6 col-md-3 text-center">
                    <div class="fs-4 fw-bold">GHS <?= number_format($totalRevenue, 2) ?></div>
                    <div class="small text-white-50">Revenue</div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="fs-4 fw-bold">GHS <?= number_format($totalExpenses, 2) ?></div>
                    <div class="small text-white-50">Expenses</div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="fs-4 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-warning' ?>">
                        GHS <?= number_format($netProfit, 2) ?>
                    </div>
                    <div class="small text-white-50">Net Profit</div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="fs-4 fw-bold"><?= number_format($profitMargin, 2) ?>%</div>
                    <div class="small text-white-50">Margin</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="pl-kpi">
            <div class="title">Total Revenue</div>
            <div class="value">GHS <?= number_format($totalRevenue, 2) ?></div>
            <div class="meta">All recorded business revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pl-kpi">
            <div class="title">Total Expenses</div>
            <div class="value">GHS <?= number_format($totalExpenses, 2) ?></div>
            <div class="meta">All linked and direct costs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pl-kpi">
            <div class="title">Net Profit</div>
            <div class="value <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format($netProfit, 2) ?></div>
            <div class="meta">Profit after expenses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pl-kpi">
            <div class="title">Profit Margin</div>
            <div class="value"><?= number_format($profitMargin, 2) ?>%</div>
            <div class="meta">Net profit over revenue</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="pl-soft">
            <div class="title">Current Month Revenue</div>
            <div class="value fw-bold fs-4">GHS <?= number_format($monthRevenue, 2) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pl-soft">
            <div class="title">Current Month Expenses</div>
            <div class="value fw-bold fs-4">GHS <?= number_format($monthExpenses, 2) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pl-soft">
            <div class="title">Current Month Net</div>
            <div class="value fw-bold fs-4 <?= $monthNet >= 0 ? 'text-success' : 'text-danger' ?>">
                GHS <?= number_format($monthNet, 2) ?>
            </div>
            <div class="small text-muted">Month margin: <?= number_format($monthMargin, 2) ?>%</div>
        </div>
    </div>
</div>

<div class="pl-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Revenue by Sales Type</h5>
        <span class="badge text-bg-primary px-3 py-2 rounded-pill">Revenue Mix</span>
    </div>

    <div class="row g-3">
        <?php if (!empty($salesByType)): ?>
            <?php foreach ($salesByType as $type): ?>
                <div class="col-md-3">
                    <div class="pl-soft">
                        <div class="title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type['sale_type']))) ?></div>
                        <div class="value fw-bold fs-5">GHS <?= number_format((float)$type['total_amount'], 2) ?></div>
                        <div class="small text-muted"><?= number_format((float)($type['total_records'] ?? 0)) ?> record(s)</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-muted">No revenue breakdown available yet.</div>
        <?php endif; ?>
    </div>
</div>
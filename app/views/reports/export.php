<?php
$financeTotals = $financeTotals ?? [];
$salesTotals   = $salesTotals   ?? [];
$expenseTotals = $expenseTotals ?? [];
$totalBatches  = (int)($totalBatches ?? 0);
$base          = rtrim(BASE_URL, '/');

$exports = [
    ['title' => 'Sales Report',           'desc' => 'All sales records with customer, batch, amount, and payment status.',  'url' => $base.'/reports/sales',              'icon' => 'cart3',           'color' => 'success'],
    ['title' => 'Expense Report',         'desc' => 'All expense records grouped by category and date.',                    'url' => $base.'/reports/expenses',           'icon' => 'wallet2',         'color' => 'danger'],
    ['title' => 'Profit & Loss',          'desc' => 'Revenue vs expenses with monthly breakdown and net position.',         'url' => $base.'/reports/profit-loss',        'icon' => 'file-earmark-text','color' => 'dark'],
    ['title' => 'Batch Performance',      'desc' => 'All batches with cost, sales, gross profit, and margin.',              'url' => $base.'/reports/batch-performance',  'icon' => 'clipboard-data',  'color' => 'primary'],
    ['title' => 'Feed Consumption',       'desc' => 'Feed usage records by batch with quantity and cost.',                  'url' => $base.'/reports/feed',               'icon' => 'basket2',         'color' => 'warning'],
    ['title' => 'Mortality Report',       'desc' => 'Bird mortality records by batch with cause and date.',                 'url' => $base.'/reports/mortality',          'icon' => 'heart-pulse',     'color' => 'danger'],
    ['title' => 'Vaccination Report',     'desc' => 'Vaccination records with dosage, cost, and upcoming schedules.',      'url' => $base.'/reports/vaccination',        'icon' => 'shield-check',    'color' => 'info'],
    ['title' => 'Medication Report',      'desc' => 'Medication treatments with quantity used and total cost.',             'url' => $base.'/reports/medication',         'icon' => 'capsule-pill',    'color' => 'secondary'],
    ['title' => 'Egg Production',         'desc' => 'Daily egg production records by batch with tray equivalents.',        'url' => $base.'/reports/egg-production',     'icon' => 'egg-fried',       'color' => 'warning'],
    ['title' => 'Weight Report',          'desc' => 'Weight tracking records with average weight per batch.',               'url' => $base.'/reports/weight',             'icon' => 'speedometer2',    'color' => 'primary'],
    ['title' => 'Stock Position',         'desc' => 'Current inventory levels, values, and reorder status.',               'url' => $base.'/reports/stock-position',     'icon' => 'box-seam',        'color' => 'info'],
    ['title' => 'Low Stock Report',       'desc' => 'Items at or below reorder level with shortage and restock cost.',     'url' => $base.'/reports/low-stock',          'icon' => 'exclamation-triangle','color' => 'warning'],
    ['title' => 'Stock Movement',         'desc' => 'All stock in and stock out transactions with monthly breakdown.',     'url' => $base.'/reports/stock-movement',     'icon' => 'arrow-left-right','color' => 'secondary'],
    ['title' => 'Inventory Valuation',    'desc' => 'Monetary value of all inventory on hand by item and category.',      'url' => $base.'/reports/inventory-valuation','icon' => 'cash-stack',      'color' => 'success'],
    ['title' => 'Business Health',        'desc' => 'Health score, signals, and batch health summary.',                    'url' => $base.'/reports/business-health',    'icon' => 'heart',           'color' => 'danger'],
    ['title' => 'Decision Recommendations','desc'=> 'AI-driven action recommendations based on live data.',                'url' => $base.'/reports/decisions',          'icon' => 'lightbulb',       'color' => 'warning'],
    ['title' => 'Forecast Report',        'desc' => 'Revenue and expense projections for the next 3 months.',              'url' => $base.'/reports/forecast',           'icon' => 'graph-up-arrow',  'color' => 'primary'],
    ['title' => 'Custom Reports',         'desc' => 'Build your own view by selecting sections and date ranges.',          'url' => $base.'/reports/custom',             'icon' => 'sliders',         'color' => 'dark'],
];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Export Center</h2>
            <p class="text-muted mb-0">Access and print all available reports from one place.</p>
        </div>
        <a href="<?= $base ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Summary stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Revenue</div><div class="fs-5 fw-bold text-success">GHS <?= number_format((float)($salesTotals['total_sales'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Expenses</div><div class="fs-5 fw-bold text-danger">GHS <?= number_format((float)($expenseTotals['total_amount'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Batches</div><div class="fs-5 fw-bold"><?= number_format($totalBatches) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Available Reports</div><div class="fs-5 fw-bold"><?= count($exports) ?></div></div></div></div>
    </div>

    <!-- Print tip -->
    <div class="alert alert-info mb-4">
        <i class="bi bi-printer me-2"></i>
        <strong>To export/print:</strong> Open any report below, then use your browser's <strong>Print</strong> function (Ctrl+P / Cmd+P) and select "Save as PDF" to export.
    </div>

    <!-- Report grid -->
    <div class="row g-3">
        <?php foreach ($exports as $e): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-3 p-2 bg-<?= $e['color'] ?> bg-opacity-10">
                                <i class="bi bi-<?= $e['icon'] ?> text-<?= $e['color'] ?> fs-5"></i>
                            </div>
                            <span class="fw-semibold small"><?= htmlspecialchars($e['title']) ?></span>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3"><?= htmlspecialchars($e['desc']) ?></p>
                        <a href="<?= htmlspecialchars($e['url']) ?>" class="btn btn-<?= $e['color'] ?> btn-sm w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open Report
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

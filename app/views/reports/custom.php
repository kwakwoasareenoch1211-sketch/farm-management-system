<?php
$financeTotals    = $financeTotals    ?? [];
$monthlyCombined  = $monthlyCombined  ?? [];
$salesTotals      = $salesTotals      ?? [];
$salesByType      = $salesByType      ?? [];
$expenseTotals    = $expenseTotals    ?? [];
$expenseByCategory= $expenseByCategory?? [];
$batches          = $batches          ?? [];
$lowStockItems    = $lowStockItems    ?? [];
$base             = rtrim(BASE_URL, '/');

// Filter state from GET
$filter = $_GET['filter'] ?? 'all';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Custom Reports</h2>
            <p class="text-muted mb-0">Build your own view by selecting the data sections you need.</p>
        </div>
        <a href="<?= $base ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Filter bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Report Section</label>
                    <select name="filter" class="form-select">
                        <option value="all"      <?= $filter==='all'      ?'selected':'' ?>>All Sections</option>
                        <option value="revenue"  <?= $filter==='revenue'  ?'selected':'' ?>>Revenue Only</option>
                        <option value="expenses" <?= $filter==='expenses' ?'selected':'' ?>>Expenses Only</option>
                        <option value="batches"  <?= $filter==='batches'  ?'selected':'' ?>>Batch Performance</option>
                        <option value="inventory"<?= $filter==='inventory'?'selected':'' ?>>Inventory</option>
                        <option value="monthly"  <?= $filter==='monthly'  ?'selected':'' ?>>Monthly Trend</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue section -->
    <?php if ($filter === 'all' || $filter === 'revenue'): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Revenue Summary</h5>
                <a href="<?= $base ?>/reports/sales" class="btn btn-outline-success btn-sm">Full Sales Report</a>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">Total Revenue</div><div class="fw-bold text-success">GHS <?= number_format((float)($salesTotals['total_sales'] ?? 0), 2) ?></div></div></div>
                <div class="col-md-3"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">Collected</div><div class="fw-bold text-success">GHS <?= number_format((float)($salesTotals['total_paid'] ?? 0), 2) ?></div></div></div>
                <div class="col-md-3"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">Outstanding</div><div class="fw-bold text-danger">GHS <?= number_format((float)($salesTotals['total_outstanding'] ?? 0), 2) ?></div></div></div>
                <div class="col-md-3"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">This Month</div><div class="fw-bold">GHS <?= number_format((float)($salesTotals['current_month_sales'] ?? 0), 2) ?></div></div></div>
            </div>
            <?php if (!empty($salesByType)): ?>
                <table class="table align-middle small">
                    <thead><tr><th>Sale Type</th><th>Records</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php foreach ($salesByType as $r): ?>
                            <tr><td><?= htmlspecialchars(ucfirst($r['sale_type'] ?? '')) ?></td><td><?= number_format((int)($r['total_records'] ?? 0)) ?></td><td class="text-success fw-bold">GHS <?= number_format((float)($r['total_amount'] ?? 0), 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Expenses section -->
    <?php if ($filter === 'all' || $filter === 'expenses'): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Expense Summary</h5>
                <a href="<?= $base ?>/reports/expenses" class="btn btn-outline-danger btn-sm">Full Expense Report</a>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">Total Expenses</div><div class="fw-bold text-danger">GHS <?= number_format((float)($expenseTotals['total_amount'] ?? 0), 2) ?></div></div></div>
                <div class="col-md-4"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">This Month</div><div class="fw-bold text-danger">GHS <?= number_format((float)($expenseTotals['current_month_amount'] ?? 0), 2) ?></div></div></div>
                <div class="col-md-4"><div class="border rounded-4 p-3 text-center"><div class="text-muted small">Today</div><div class="fw-bold">GHS <?= number_format((float)($expenseTotals['today_amount'] ?? 0), 2) ?></div></div></div>
            </div>
            <?php if (!empty($expenseByCategory)): ?>
                <table class="table align-middle small">
                    <thead><tr><th>Category</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($expenseByCategory as $r): ?>
                            <tr><td><?= htmlspecialchars($r['category_name'] ?? 'Uncategorized') ?></td><td class="text-danger fw-bold">GHS <?= number_format((float)($r['total_amount'] ?? 0), 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Batch performance -->
    <?php if ($filter === 'all' || $filter === 'batches'): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Batch Performance</h5>
                <a href="<?= $base ?>/reports/batch-performance" class="btn btn-outline-dark btn-sm">Full Report</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small">
                    <thead><tr><th>Batch</th><th>Purpose</th><th>Birds</th><th>Sales</th><th>Cost</th><th>Profit</th></tr></thead>
                    <tbody>
                        <?php if (!empty($batches)): ?>
                            <?php foreach ($batches as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['batch_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($b['production_purpose'] ?? '')) ?></td>
                                    <td><?= number_format((float)($b['current_quantity'] ?? 0)) ?></td>
                                    <td>GHS <?= number_format((float)($b['total_batch_sales'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($b['total_batch_cost'] ?? 0), 2) ?></td>
                                    <td class="fw-bold <?= (float)($b['gross_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($b['gross_profit'] ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No batch data available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Inventory -->
    <?php if ($filter === 'all' || $filter === 'inventory'): ?>
    <?php if (!empty($lowStockItems)): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Low Stock Items</h5>
                <a href="<?= $base ?>/reports/low-stock" class="btn btn-outline-warning btn-sm">Full Report</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small">
                    <thead><tr><th>Item</th><th>Current Stock</th><th>Reorder Level</th><th>Shortage</th></tr></thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr class="table-warning">
                                <td><?= htmlspecialchars($item['item_name'] ?? '') ?></td>
                                <td class="text-danger"><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></td>
                                <td><?= number_format((float)($item['reorder_level'] ?? 0), 2) ?></td>
                                <td class="fw-bold text-danger"><?= number_format(max(0, (float)($item['reorder_level'] ?? 0) - (float)($item['current_stock'] ?? 0)), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Monthly trend -->
    <?php if ($filter === 'all' || $filter === 'monthly'): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Monthly Trend (Last 12 Months)</h5>
                <a href="<?= $base ?>/reports/profit-loss" class="btn btn-outline-dark btn-sm">P&amp;L Report</a>
            </div>
            <?php if (!empty($monthlyCombined)): ?>
                <div class="table-responsive">
                    <table class="table align-middle small">
                        <thead><tr><th>Month</th><th>Revenue</th><th>Feed</th><th>Medication</th><th>Vaccination</th><th>Direct</th><th>Total Expense</th><th>Net</th></tr></thead>
                        <tbody>
                            <?php foreach ($monthlyCombined as $m): ?>
                                <?php $n = (float)($m['net_position'] ?? 0); ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['month_label'] ?? '') ?></td>
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
                <p class="text-muted">No monthly data available yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

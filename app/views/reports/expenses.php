<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$byCategory = $byCategory ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalAmount = (float)($totals['total_amount'] ?? 0);
$currentMonthAmount = (float)($totals['current_month_amount'] ?? 0);
$todayAmount = (float)($totals['today_amount'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Expense Report</h2>
            <p class="text-muted mb-0">Full expense history and category breakdown.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/expenses/create" class="btn btn-dark">Add Expense</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Expenses</div><div class="fs-4 fw-bold">GHS <?= number_format($totalAmount, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">This Month</div><div class="fs-4 fw-bold">GHS <?= number_format($currentMonthAmount, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Today</div><div class="fs-4 fw-bold">GHS <?= number_format($todayAmount, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Records</div><div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Expense Records</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Batch</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['expense_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['expense_title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                                            <td><?= htmlspecialchars($row['batch_code'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                                            <td>GHS <?= number_format((float)($row['amount'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($row['payment_method'] ?? '') ?></td>
                                            <td>
                                                <?php $status = $row['payment_status'] ?? 'unpaid'; ?>
                                                <span class="badge bg-<?= $status === 'paid' ? 'success' : ($status === 'partial' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No expense data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Expenses by Category</h5>
                    <?php if (!empty($byCategory)): ?>
                        <?php foreach ($byCategory as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></div>
                                <div class="small mt-1">Total: <strong>GHS <?= number_format((float)($row['total_amount'] ?? 0), 2) ?></strong></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No category breakdown available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

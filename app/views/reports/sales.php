<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$byType = $byType ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalSales = (float)($totals['total_sales'] ?? 0);
$totalPaid = (float)($totals['total_paid'] ?? 0);
$totalOutstanding = (float)($totals['total_outstanding'] ?? 0);
$currentMonthSales = (float)($totals['current_month_sales'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Sales Report</h2>
            <p class="text-muted mb-0">Full sales history, revenue breakdown, and payment status.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/sales/create" class="btn btn-dark">Add Sale</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Sales</div><div class="fs-4 fw-bold">GHS <?= number_format($totalSales, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Amount Paid</div><div class="fs-4 fw-bold text-success">GHS <?= number_format($totalPaid, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Outstanding</div><div class="fs-4 fw-bold text-danger">GHS <?= number_format($totalOutstanding, 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">This Month</div><div class="fs-4 fw-bold">GHS <?= number_format($currentMonthSales, 2) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Sales Records</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Batch</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['sale_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['invoice_no'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                                            <td><?= htmlspecialchars($row['batch_code'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['sale_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                            <td>GHS <?= number_format((float)($row['total_amount'] ?? 0), 2) ?></td>
                                            <td>GHS <?= number_format((float)($row['amount_paid'] ?? 0), 2) ?></td>
                                            <td>
                                                <?php $status = $row['payment_status'] ?? 'unpaid'; ?>
                                                <span class="badge bg-<?= $status === 'paid' ? 'success' : ($status === 'partial' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center text-muted py-4">No sales data available.</td></tr>
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
                    <h5 class="fw-bold mb-3">Sales by Type</h5>
                    <?php if (!empty($byType)): ?>
                        <?php foreach ($byType as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars(ucfirst($row['sale_type'] ?? 'Other')) ?></div>
                                <div class="small mt-1">Records: <strong><?= number_format((int)($row['total_records'] ?? 0)) ?></strong></div>
                                <div class="small">Revenue: <strong>GHS <?= number_format((float)($row['total_amount'] ?? 0), 2) ?></strong></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No type breakdown available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

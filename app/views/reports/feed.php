<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];

$totalFeedKg = (float)($totals['total_feed_kg'] ?? 0);
$totalFeedCost = (float)($totals['total_feed_cost'] ?? 0);
$totalRecords = (int)($totals['total_records'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Feed Consumption Report</h2>
            <p class="text-muted mb-0">Track feed usage, cost, and consumption by batch.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Feed Used</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalFeedKg, 2) ?> kg</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Feed Cost</div>
                    <div class="fs-4 fw-bold">GHS <?= number_format($totalFeedCost, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Feed Records</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch Code</th>
                            <th>Batch Name</th>
                            <th>Feed Type</th>
                            <th>Quantity (kg)</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportRows)): ?>
                            <?php foreach ($reportRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['record_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['batch_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['batch_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['feed_type'] ?? '') ?></td>
                                    <td><?= number_format((float)($row['quantity_kg'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['total_cost'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No feed consumption data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
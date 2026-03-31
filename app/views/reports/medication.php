<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$batchRows = $batchRows ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalQuantityUsed = (float)($totals['total_quantity_used'] ?? 0);
$totalCost = (float)($totals['total_cost'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Medication Report</h2>
            <p class="text-muted mb-0">Track medication treatments, quantities, costs, and withdrawal periods by batch.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Medication Records</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Quantity Used</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalQuantityUsed, 3) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Medication Cost</div>
                    <div class="fs-4 fw-bold">GHS <?= number_format($totalCost, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Medication Records</h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch Code</th>
                                    <th>Batch Name</th>
                                    <th>Medication</th>
                                    <th>Condition Treated</th>
                                    <th>Dosage</th>
                                    <th>Quantity</th>
                                    <th>Cost</th>
                                    <th>Administered By</th>
                                    <th>Withdrawal Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['record_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['batch_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['batch_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['medication_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['condition_treated'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['dosage'] ?? '') ?></td>
                                            <td>
                                                <?= number_format((float)($row['quantity_used'] ?? 0), 3) ?>
                                                <?= htmlspecialchars($row['unit'] ?? '') ?>
                                            </td>
                                            <td>GHS <?= number_format((float)($row['total_cost'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($row['administered_by'] ?? '') ?></td>
                                            <td><?= htmlspecialchars((string)($row['withdrawal_period_days'] ?? '')) ?></td>
                                        </tr>
                                        <?php if (!empty($row['notes'])): ?>
                                            <tr>
                                                <td colspan="10" class="small text-muted bg-light">
                                                    <strong>Notes:</strong> <?= htmlspecialchars($row['notes']) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No medication data available.</td>
                                    </tr>
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
                    <h5 class="fw-bold mb-3">Medication Summary by Batch</h5>

                    <?php if (!empty($batchRows)): ?>
                        <?php foreach ($batchRows as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['batch_code'] ?? '') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                                <div class="small mt-1">
                                    Treatments: <strong><?= number_format((float)($row['treatments'] ?? 0)) ?></strong>
                                </div>
                                <div class="small">
                                    Quantity Used: <strong><?= number_format((float)($row['total_quantity_used'] ?? 0), 3) ?></strong>
                                </div>
                                <div class="small">
                                    Cost: <strong>GHS <?= number_format((float)($row['total_cost'] ?? 0), 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No medication batch summary available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
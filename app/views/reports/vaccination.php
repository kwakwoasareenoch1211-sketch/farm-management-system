<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$dueRows = $dueRows ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalDoses = (float)($totals['total_doses'] ?? 0);
$totalCost = (float)($totals['total_cost'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Vaccination Report</h2>
            <p class="text-muted mb-0">Track vaccination activity, dosage usage, and upcoming due schedules.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Vaccination Records</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Doses Used</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalDoses, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Vaccination Cost</div>
                    <div class="fs-4 fw-bold">GHS <?= number_format($totalCost, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Vaccination Records</h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch Code</th>
                                    <th>Batch Name</th>
                                    <th>Vaccine</th>
                                    <th>Disease Target</th>
                                    <th>Dose Qty</th>
                                    <th>Route</th>
                                    <th>Cost</th>
                                    <th>Next Due</th>
                                    <th>Administered By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['record_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['batch_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['batch_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['vaccine_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['disease_target'] ?? '') ?></td>
                                            <td><?= number_format((float)($row['dose_qty'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($row['route'] ?? '') ?></td>
                                            <td>GHS <?= number_format((float)($row['cost_amount'] ?? 0), 2) ?></td>
                                            <td><?= htmlspecialchars($row['next_due_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['administered_by'] ?? '') ?></td>
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
                                        <td colspan="10" class="text-center text-muted py-4">No vaccination data available.</td>
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
                    <h5 class="fw-bold mb-3">Upcoming Due Dates</h5>

                    <?php if (!empty($dueRows)): ?>
                        <?php foreach ($dueRows as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['vaccine_name'] ?? '') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['batch_code'] ?? '') ?> - <?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['disease_target'] ?? '') ?></div>
                                <div class="small mt-1">
                                    Next Due: <strong><?= htmlspecialchars($row['next_due_date'] ?? '') ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No upcoming vaccination schedules found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
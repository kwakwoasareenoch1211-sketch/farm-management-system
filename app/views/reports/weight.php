<?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$batchRows = $batchRows ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalSampleSize = (float)($totals['total_sample_size'] ?? 0);
$totalWeightKg = (float)($totals['total_weight_kg'] ?? 0);
$averageWeightKg = (float)($totals['average_weight_kg'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Weight Report</h2>
            <p class="text-muted mb-0">Track sampled weight performance and compare growth by batch.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Weight Records</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Sample Size</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalSampleSize) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Weight (kg)</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalWeightKg, 3) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Average Weight (kg)</div>
                    <div class="fs-4 fw-bold"><?= number_format($averageWeightKg, 3) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Weight Records</h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch Code</th>
                                    <th>Batch Name</th>
                                    <th>Sample Size</th>
                                    <th>Total Weight (kg)</th>
                                    <th>Average Weight (kg)</th>
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
                                            <td><?= number_format((float)($row['sample_size'] ?? 0)) ?></td>
                                            <td><?= number_format((float)($row['total_weight_kg'] ?? 0), 3) ?></td>
                                            <td><?= number_format((float)($row['average_weight_kg'] ?? 0), 3) ?></td>
                                            <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No weight data available.</td>
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
                    <h5 class="fw-bold mb-3">Weight Summary by Batch</h5>

                    <?php if (!empty($batchRows)): ?>
                        <?php foreach ($batchRows as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['batch_code'] ?? '') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                                <div class="small mt-1">
                                    Records: <strong><?= number_format((float)($row['total_records'] ?? 0)) ?></strong>
                                </div>
                                <div class="small">
                                    Sample Size: <strong><?= number_format((float)($row['total_sample_size'] ?? 0)) ?></strong>
                                </div>
                                <div class="small">
                                    Total Weight: <strong><?= number_format((float)($row['total_weight_kg'] ?? 0), 3) ?> kg</strong>
                                </div>
                                <div class="small">
                                    Average Weight: <strong><?= number_format((float)($row['average_weight_kg'] ?? 0), 3) ?> kg</strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No batch weight summary available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
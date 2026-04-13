<?php
$printTitle    = 'Egg Production Report';
$printSubtitle = 'Generated: ' . date('d M Y H:i') . ' | Poultry Farm Management System';
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';
?><?php
$reportRows = $reportRows ?? [];
$totals = $totals ?? [];
$batchRows = $batchRows ?? [];

$totalRecords = (int)($totals['total_records'] ?? 0);
$totalEggs = (float)($totals['total_eggs'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Egg Production Report</h2>
            <p class="text-muted mb-0">Track egg output and compare production performance by batch.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/reports" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Production Records</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="text-muted small">Total Eggs Produced</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalEggs, 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Egg Production Records</h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch Code</th>
                                    <th>Batch Name</th>
                                    <th>Quantity</th>
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
                                            <td><?= number_format((float)($row['quantity'] ?? 0), 0) ?></td>
                                            <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No egg production data available.</td>
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
                    <h5 class="fw-bold mb-3">Production by Batch</h5>

                    <?php if (!empty($batchRows)): ?>
                        <?php foreach ($batchRows as $row): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light">
                                <div class="fw-semibold"><?= htmlspecialchars($row['batch_code'] ?? '') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['batch_name'] ?? '') ?></div>
                                <div class="small mt-1">
                                    Total Eggs: <strong><?= number_format((float)($row['total_eggs'] ?? 0), 0) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No batch production summary available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
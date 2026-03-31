<?php $batch = $batch ?? null; ?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Batch Details</h2>
            <p class="text-muted mb-0">Detailed performance and cost summary for this batch.</p>
        </div>

        <a href="<?= rtrim(BASE_URL, '/') ?>/batches" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if ($batch): ?>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Batch Information</h5>
                        <p><strong>Batch Code:</strong> <?= htmlspecialchars($batch['batch_code'] ?? '') ?></p>
                        <p><strong>Name:</strong> <?= htmlspecialchars($batch['batch_name'] ?? '') ?></p>
                        <p><strong>Purpose:</strong> <?= htmlspecialchars($batch['production_purpose'] ?? '') ?></p>
                        <p><strong>Subtype:</strong> <?= htmlspecialchars($batch['bird_subtype'] ?? '') ?></p>
                        <p><strong>Breed:</strong> <?= htmlspecialchars($batch['breed'] ?? '') ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($batch['status'] ?? '') ?></p>
                        <p><strong>Start Date:</strong> <?= htmlspecialchars($batch['start_date'] ?? '') ?></p>
                        <p><strong>Expected End Date:</strong> <?= htmlspecialchars($batch['expected_end_date'] ?? '') ?></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Performance Summary</h5>
                        <p><strong>Initial Quantity:</strong> <?= number_format((float)($batch['initial_quantity'] ?? 0)) ?></p>
                        <p><strong>Current Quantity:</strong> <?= number_format((float)($batch['current_quantity'] ?? 0)) ?></p>
                        <p><strong>Mortality:</strong> <?= number_format((float)($batch['total_mortality'] ?? 0), 2) ?></p>
                        <p><strong>Mortality Rate:</strong> <?= number_format((float)($batch['mortality_rate'] ?? 0), 2) ?>%</p>
                        <p><strong>Total Eggs:</strong> <?= number_format((float)($batch['total_eggs'] ?? 0), 2) ?></p>
                        <p><strong>Total Feed:</strong> <?= number_format((float)($batch['total_feed_kg'] ?? 0), 2) ?> kg</p>
                        <p><strong>Latest Average Weight:</strong> <?= number_format((float)($batch['latest_average_weight_kg'] ?? 0), 3) ?> kg</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Financial Summary</h5>
                        <div class="row g-3">
                            <div class="col-md-3"><strong>Purchase Cost:</strong><br>GHS <?= number_format((float)($batch['purchase_cost'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Feed Cost:</strong><br>GHS <?= number_format((float)($batch['total_feed_cost'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Medication Cost:</strong><br>GHS <?= number_format((float)($batch['total_medication_cost'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Vaccination Cost:</strong><br>GHS <?= number_format((float)($batch['total_vaccination_cost'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Total Cost:</strong><br>GHS <?= number_format((float)($batch['total_batch_cost'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Sales:</strong><br>GHS <?= number_format((float)($batch['total_batch_sales'] ?? 0), 2) ?></div>
                            <div class="col-md-3"><strong>Gross Profit:</strong><br><span class="<?= ((float)($batch['gross_profit'] ?? 0) >= 0) ? 'text-success' : 'text-danger' ?>">GHS <?= number_format((float)($batch['gross_profit'] ?? 0), 2) ?></span></div>
                            <div class="col-md-3"><strong>Profit Margin:</strong><br><?= number_format((float)($batch['profit_margin'] ?? 0), 2) ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Batch not found.</div>
    <?php endif; ?>
</div>
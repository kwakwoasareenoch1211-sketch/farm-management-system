<?php
$batches = $batches ?? [];

$printTitle    = 'Batch Management Report';
$printSubtitle = 'Generated: ' . date('d M Y H:i');
$exportUrl     = null;
include BASE_PATH . 'app/views/layouts/print_toolbar.php';
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
        <div>
            <h2 class="fw-bold mb-1">Animal Batches</h2>
            <p class="text-muted mb-0">Manage poultry batches, track performance, and review batch health.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= rtrim(BASE_URL, '/') ?>/poultry" class="btn btn-outline-secondary">Dashboard</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/batches/create" class="btn btn-dark">Create Batch</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Subtype</th>
                            <th>Breed</th>
                            <th>Start Date</th>
                            <th>Initial Qty</th>
                            <th>Current Qty</th>
                            <th>Mortality</th>
                            <th>Eggs</th>
                            <th>Feed (kg)</th>
                            <th>Sales</th>
                            <th>Gross Profit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($batches)): ?>
                            <?php foreach ($batches as $batch): ?>
                                <tr>
                                    <td><?= htmlspecialchars($batch['batch_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($batch['batch_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($batch['production_purpose'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($batch['bird_subtype'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($batch['breed'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($batch['start_date'] ?? '') ?></td>
                                    <td><?= number_format((float)($batch['initial_quantity'] ?? 0)) ?></td>
                                    <td><?= number_format((float)($batch['current_quantity'] ?? 0)) ?></td>
                                    <td><?= number_format((float)($batch['total_mortality'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($batch['total_eggs'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($batch['total_feed_kg'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($batch['total_batch_sales'] ?? 0), 2) ?></td>
                                    <td class="<?= ((float)($batch['gross_profit'] ?? 0) >= 0) ? 'text-success' : 'text-danger' ?>">
                                        GHS <?= number_format((float)($batch['gross_profit'] ?? 0), 2) ?>
                                    </td>
                                    <td><?= htmlspecialchars($batch['status'] ?? '') ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/batches/view?id=<?= (int)($batch['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/batches/edit?id=<?= (int)($batch['id'] ?? 0) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/feed/create?batch_id=<?= (int)($batch['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success">Feed</a>
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/batches/delete?id=<?= (int)($batch['id'] ?? 0) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this batch?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="15" class="text-center text-muted py-4">No batches available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
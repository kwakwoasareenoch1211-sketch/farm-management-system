<?php
$addUrl = rtrim(BASE_URL, '/') . '/weights/create';
$addLabel = 'Add Weight Record';
$pageDesc = 'Track broiler growth and automatically compute average weight per sample.';
require BASE_PATH . 'app/views/partials/poultry-page-top.php';

$totalRecords = (float)($totals['total_records'] ?? 0);
$totalSampledBirds = (float)($totals['total_sampled_birds'] ?? 0);
$totalWeightKg = (float)($totals['total_weight_kg'] ?? 0);
$avgWeightKg = (float)($totals['avg_weight_kg'] ?? 0);
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Weight Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">Saved growth measurement entries</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Sampled Birds</div>
            <div class="value"><?= number_format($totalSampledBirds) ?></div>
            <div class="meta">Birds included in samples</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Total Weight</div>
            <div class="value"><?= number_format($totalWeightKg, 3) ?> kg</div>
            <div class="meta">Combined sampled weight</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Average Weight</div>
            <div class="value"><?= number_format($avgWeightKg, 3) ?> kg</div>
            <div class="meta">Average weight across saved records</div>
        </div>
    </div>
</div>

<div class="work-card p-4">
    <h5 class="fw-bold mb-3">Weight Record List</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Sample Size</th>
                    <th>Total Weight (kg)</th>
                    <th>Average Weight (kg)</th>
                    <th>Current Birds</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date']) ?></td>
                            <td><?= htmlspecialchars($r['batch_code'] . (!empty($r['batch_name']) ? ' - ' . $r['batch_name'] : '')) ?></td>
                            <td><?= number_format((float)$r['sample_size']) ?></td>
                            <td><?= number_format((float)$r['total_weight_kg'], 3) ?></td>
                            <td><?= number_format((float)$r['average_weight_kg'], 3) ?></td>
                            <td><?= number_format((float)$r['current_quantity']) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/weights/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/weights/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this weight record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No weight records yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
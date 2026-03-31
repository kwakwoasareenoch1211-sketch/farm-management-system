<?php
$addUrl = rtrim(BASE_URL, '/') . '/medication/create';
$addLabel = 'Add Medication Record';
$pageDesc = 'Track treatments, medicine quantities, and poultry health intervention cost.';
require BASE_PATH . 'app/views/partials/poultry-page-top.php';

$totalRecords = (float)($totals['total_records'] ?? 0);
$totalQuantity = (float)($totals['total_quantity_used'] ?? 0);
$totalCost = (float)($totals['total_cost'] ?? 0);
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Medication Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">Saved treatment entries</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Quantity Used</div>
            <div class="value"><?= number_format($totalQuantity, 3) ?></div>
            <div class="meta">Total medicine volume used</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Medication Cost</div>
            <div class="value">GHS <?= number_format($totalCost, 2) ?></div>
            <div class="meta">Total treatment spend recorded</div>
        </div>
    </div>
</div>

<div class="work-card p-4">
    <h5 class="fw-bold mb-3">Medication List</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Medication</th>
                    <th>Condition</th>
                    <th>Quantity Used</th>
                    <th>Unit Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date']) ?></td>
                            <td><?= htmlspecialchars($r['batch_code'] . (!empty($r['batch_name']) ? ' - ' . $r['batch_name'] : '')) ?></td>
                            <td><?= htmlspecialchars($r['medication_name']) ?></td>
                            <td><?= htmlspecialchars($r['condition_treated'] ?? '-') ?></td>
                            <td><?= number_format((float)$r['quantity_used'], 3) ?> <?= htmlspecialchars($r['unit'] ?? '') ?></td>
                            <td>GHS <?= number_format((float)$r['unit_cost'], 2) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/medication/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/medication/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this medication record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No medication records yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
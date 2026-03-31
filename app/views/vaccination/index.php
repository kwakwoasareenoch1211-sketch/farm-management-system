<?php
$addUrl = rtrim(BASE_URL, '/') . '/vaccination/create';
$addLabel = 'Add Vaccination Record';
$pageDesc = 'Track vaccines, due dates, and poultry preventive health actions.';
require BASE_PATH . 'app/views/partials/poultry-page-top.php';

$totalRecords = (float)($totals['total_records'] ?? 0);
$overdueCount = (float)($totals['overdue_count'] ?? 0);
$dueSoonCount = (float)($totals['due_soon_count'] ?? 0);
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Vaccination Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">Saved vaccination entries</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Overdue</div>
            <div class="value text-danger"><?= number_format($overdueCount) ?></div>
            <div class="meta">Vaccines with due date already passed</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Due Soon</div>
            <div class="value text-warning"><?= number_format($dueSoonCount) ?></div>
            <div class="meta">Due within the next 7 days</div>
        </div>
    </div>
</div>

<div class="work-card p-4">
    <h5 class="fw-bold mb-3">Vaccination List</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Vaccine</th>
                    <th>Disease Target</th>
                    <th>Next Due</th>
                    <th>Administered By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date']) ?></td>
                            <td><?= htmlspecialchars($r['batch_code'] . (!empty($r['batch_name']) ? ' - ' . $r['batch_name'] : '')) ?></td>
                            <td><?= htmlspecialchars($r['vaccine_name']) ?></td>
                            <td><?= htmlspecialchars($r['disease_target'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['next_due_date'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['administered_by'] ?? '-') ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/vaccination/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/vaccination/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this vaccination record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No vaccination records yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$addUrl = rtrim(BASE_URL, '/') . '/egg-production/create';
$addLabel = 'Add Egg Record';
$pageDesc = 'Capture daily egg collection and automatically improve production analytics.';
require BASE_PATH . 'app/views/partials/poultry-page-top.php';

$totalRecords = (float)($totals['total_records'] ?? 0);
$totalEggs = (float)($totals['total_eggs'] ?? 0);
$totalCracked = (float)($totals['total_cracked'] ?? 0);
$totalSpoiled = (float)($totals['total_spoiled'] ?? 0);
$totalTrays = (float)($totals['total_trays'] ?? 0);
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Egg Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">Saved production entries</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Total Eggs</div>
            <div class="value"><?= number_format($totalEggs) ?></div>
            <div class="meta">All eggs collected</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Cracked + Spoiled</div>
            <div class="value"><?= number_format($totalCracked + $totalSpoiled) ?></div>
            <div class="meta">Quality losses recorded</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="label">Tray Equivalent</div>
            <div class="value"><?= number_format($totalTrays, 2) ?></div>
            <div class="meta">Eggs converted to trays</div>
        </div>
    </div>
</div>

<div class="work-card p-4">
    <h5 class="fw-bold mb-3">Egg Production List</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>Cracked</th>
                    <th>Spoiled</th>
                    <th>Trays</th>
                    <th>Current Birds</th>
                    <th>Egg Rate %</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <?php
                        $currentBirds = (float)($r['current_quantity'] ?? 0);
                        $eggRate = $currentBirds > 0 ? ((float)$r['quantity'] / $currentBirds) * 100 : 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date']) ?></td>
                            <td><?= htmlspecialchars($r['batch_code'] . (!empty($r['batch_name']) ? ' - ' . $r['batch_name'] : '')) ?></td>
                            <td><?= number_format((float)$r['quantity']) ?></td>
                            <td><?= number_format((float)$r['cracked_quantity']) ?></td>
                            <td><?= number_format((float)$r['spoiled_quantity']) ?></td>
                            <td><?= number_format((float)$r['trays_equivalent'], 2) ?></td>
                            <td><?= number_format($currentBirds) ?></td>
                            <td><?= number_format($eggRate, 2) ?>%</td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/egg-production/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/egg-production/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this egg production record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">No egg production records yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
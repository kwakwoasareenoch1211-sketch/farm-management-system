<?php
$records = $records ?? [];
$totals  = $totals  ?? [];
$base    = rtrim(BASE_URL, '/');

$totalRecords  = (int)($totals['total_records']   ?? 0);
$totalFeedKg   = (float)($totals['total_feed_kg'] ?? 0);
$totalFeedCost = (float)($totals['total_feed_cost']?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Feed Management</h2>
        <p class="text-muted mb-0">Record and track feed usage for your batches</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/feed/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Record Feed Usage</a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="text-muted small">Total Feed Records</div>
                <div class="fs-4 fw-bold"><?= number_format($totalRecords) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="text-muted small">Total Feed Used</div>
                <div class="fs-4 fw-bold"><?= number_format($totalFeedKg, 2) ?> kg</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="text-muted small">Total Feed Cost</div>
                <div class="fs-4 fw-bold">GHS <?= number_format($totalFeedCost, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Feed Records -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Feed Records</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Batch</th>
                        <th>Feed Item</th>
                        <th>Quantity (kg)</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['record_date'] ?? '') ?></td>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars(($r['batch_code'] ?? '') . (!empty($r['batch_name']) ? ' — ' . $r['batch_name'] : '')) ?>
                                </td>
                                <td><?= htmlspecialchars($r['feed_name'] ?? $r['feed_item_name'] ?? '-') ?></td>
                                <td><?= number_format((float)($r['quantity_kg'] ?? 0), 2) ?></td>
                                <td>GHS <?= number_format((float)($r['unit_cost'] ?? 0), 2) ?></td>
                                <td class="fw-semibold">GHS <?= number_format((float)($r['quantity_kg'] ?? 0) * (float)($r['unit_cost'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= $base ?>/feed/edit?id=<?= (int)$r['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="<?= $base ?>/feed/delete?id=<?= (int)$r['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this feed record?')">Del</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-2">No feed records yet.</p>
                                <a href="<?= $base ?>/feed/create" class="btn btn-primary btn-sm">Record First Feed Usage</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$addUrl = rtrim(BASE_URL, '/') . '/mortality/create';
$addLabel = 'Add Mortality Record';
$pageDesc = 'Track mortality events and automatically update live batch quantities.';
require BASE_PATH . 'app/views/partials/poultry-page-top.php';

$totalRecords = (float)($totals['total_records'] ?? 0);
$totalMortality = (float)($totals['total_mortality'] ?? 0);
$totalBatches = (float)($totals['total_batches'] ?? 0);
?>

<!-- Losses Link Alert -->
<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong>Track Financial Impact:</strong> Mortality records can be converted to financial losses to track the true cost impact on your business.
    </div>
    <a href="<?= rtrim(BASE_URL, '/') ?>/losses" class="btn btn-primary ms-3">
        <i class="bi bi-graph-down"></i> View Losses & Write-offs
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">Total mortality entries saved</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Total Mortality</div>
            <div class="value"><?= number_format($totalMortality) ?></div>
            <div class="meta">Total birds lost across records</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <div class="label">Affected Batches</div>
            <div class="value"><?= number_format($totalBatches) ?></div>
            <div class="meta">Batches with mortality records</div>
        </div>
    </div>
</div>

<div class="work-card p-4">
    <h5 class="fw-bold mb-3">Mortality Record List</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>Cause</th>
                    <th>Disposal</th>
                    <th>Current Birds</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date'] ?? '') ?></td>
                            <td><?= htmlspecialchars(($r['batch_code'] ?? '') . (!empty($r['batch_name']) ? ' - ' . $r['batch_name'] : '')) ?></td>
                            <td><?= number_format((float)($r['quantity'] ?? 0)) ?></td>
                            <td><?= htmlspecialchars($r['cause'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['disposal_method'] ?? '-') ?></td>
                            <td><?= number_format((float)($r['current_quantity'] ?? 0)) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/mortality/edit?id=<?= (int)($r['id'] ?? 0) ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/mortality/delete?id=<?= (int)($r['id'] ?? 0) ?>" onclick="return confirm('Delete this mortality record?')">Delete</a>
                                    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/recordMortality" style="display:inline;" onsubmit="return confirm('Record this mortality as a financial loss?');">
                                        <input type="hidden" name="mortality_id" value="<?= (int)($r['id'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Record as financial loss">
                                            <i class="bi bi-cash-stack"></i> Record Loss
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No mortality records yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
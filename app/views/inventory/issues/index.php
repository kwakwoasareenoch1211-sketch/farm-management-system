<?php $base = rtrim(BASE_URL, '/'); $rows = $rows ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Stock Issues</h2>
        <p class="text-muted mb-0">All inventory issued from stock.</p>
    </div>
    <a href="<?= $base ?>/inventory/issues/create" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Issue Stock
    </a>
</div>
<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr><th>Date</th><th>Item</th><th>Category</th><th>Qty Issued</th><th>Unit</th><th>Reason</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['issue_date'] ?? '') ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($r['item_name'] ?? '') ?></td>
                    <td><span class="badge text-bg-warning"><?= ucfirst($r['category'] ?? '') ?></span></td>
                    <td><?= number_format((float)($r['quantity_issued'] ?? 0), 2) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($r['unit_of_measure'] ?? '') ?></td>
                    <td class="small"><?= ucfirst(str_replace('_', ' ', $r['issue_reason'] ?? '')) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars(substr($r['notes'] ?? '', 0, 40)) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No issues yet. <a href="<?= $base ?>/inventory/issues/create">Issue stock</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

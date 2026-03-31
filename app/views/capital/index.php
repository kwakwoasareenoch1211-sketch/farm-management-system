<?php
$records = $records ?? [];
$totals  = $totals  ?? [];
$byType  = $byType  ?? [];
$base    = rtrim(BASE_URL, '/');

$typeLabels = [
    'owner_equity'      => 'Owner Equity',
    'retained_earnings' => 'Retained Earnings',
    'loan_capital'      => 'Loan Capital',
    'grant'             => 'Grant',
    'other'             => 'Other',
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Capital Management</h2>
            <p class="text-muted mb-0">Track all capital injections — owner equity, loans, grants, and retained earnings.</p>
        </div>
        <a href="<?= $base ?>/capital/create" class="btn btn-dark"><i class="bi bi-plus-circle me-1"></i>Add Capital</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Total Capital</div><div class="fs-4 fw-bold text-primary">GHS <?= number_format((float)($totals['total_capital'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Owner Equity</div><div class="fs-4 fw-bold">GHS <?= number_format((float)($totals['owner_equity'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Loan Capital</div><div class="fs-4 fw-bold text-warning">GHS <?= number_format((float)($totals['loan_capital'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Retained Earnings</div><div class="fs-4 fw-bold text-success">GHS <?= number_format((float)($totals['retained_earnings'] ?? 0), 2) ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Capital Entries</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Date</th><th>Title</th><th>Type</th><th>Source</th><th>Amount</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if (!empty($records)): ?>
                                    <?php foreach ($records as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['entry_date'] ?? '') ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($r['title'] ?? '') ?></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($typeLabels[$r['capital_type'] ?? ''] ?? ucfirst($r['capital_type'] ?? '')) ?></span></td>
                                            <td><?= htmlspecialchars($r['source_name'] ?? '-') ?></td>
                                            <td class="fw-bold text-primary">GHS <?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                                            <td>
                                                <a href="<?= $base ?>/capital/edit?id=<?= (int)$r['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                                <a href="<?= $base ?>/capital/delete?id=<?= (int)$r['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete?')">Del</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No capital entries yet. <a href="<?= $base ?>/capital/create">Add your first entry</a>.</td></tr>
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
                    <h5 class="fw-bold mb-3">Capital by Type</h5>
                    <?php if (!empty($byType)): ?>
                        <?php foreach ($byType as $t): ?>
                            <div class="border rounded-4 p-3 mb-2 bg-light d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($typeLabels[$t['capital_type'] ?? ''] ?? ucfirst($t['capital_type'] ?? '')) ?></div>
                                    <div class="small text-muted"><?= number_format((int)($t['records'] ?? 0)) ?> entries</div>
                                </div>
                                <div class="fw-bold text-primary">GHS <?= number_format((float)($t['total'] ?? 0), 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No capital data yet.</p>
                    <?php endif; ?>
                    <a href="<?= $base ?>/capital/create" class="btn btn-dark w-100 mt-2">+ Add Capital</a>
                </div>
            </div>
        </div>
    </div>
</div>

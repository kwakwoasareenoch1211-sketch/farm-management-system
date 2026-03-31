<?php $issueRows = $issueRows ?? []; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Stock Issues</h2>
            <p class="text-muted mb-0">Track inventory issued out of stock.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/issues/create" class="btn btn-dark">Create Issue</a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Farm</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($issueRows)): ?>
                            <?php foreach ($issueRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['issue_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                    <td><?= number_format((float)($row['quantity_issued'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($row['issue_reason'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['reference_no'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No stock issues available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
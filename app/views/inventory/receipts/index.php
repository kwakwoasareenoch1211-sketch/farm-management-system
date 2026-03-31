<?php $receiptRows = $receiptRows ?? []; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Stock Receipts</h2>
            <p class="text-muted mb-0">Track inventory received into stock.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/receipts/create" class="btn btn-dark">Create Receipt</a>
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
                            <th>Unit Cost</th>
                            <th>Supplier</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($receiptRows)): ?>
                            <?php foreach ($receiptRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['receipt_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['item_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['farm_name'] ?? '') ?></td>
                                    <td><?= number_format((float)($row['quantity_received'] ?? 0), 2) ?></td>
                                    <td>GHS <?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($row['supplier_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['reference_no'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No stock receipts available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
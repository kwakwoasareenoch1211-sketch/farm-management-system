<div class="finance-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Edit Expense</h2>
            <p class="text-muted mb-0">Update this expense record and keep financial totals accurate.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/expenses" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/expenses/update">
        <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>" <?= (int)$farm['id'] === (int)($record['farm_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($farm['farm_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select">
                    <option value="">No batch linked</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= (int)$b['id'] === (int)($record['batch_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['batch_code'] . (!empty($b['batch_name']) ? ' - ' . $b['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Expense Date</label>
                <input type="date" name="expense_date" class="form-control" value="<?= htmlspecialchars($record['expense_date'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Expense Title</label>
                <input type="text" name="expense_title" class="form-control" value="<?= htmlspecialchars($record['expense_title'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?= htmlspecialchars($record['amount'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>" <?= (int)$category['id'] === (int)($record['category_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">No supplier linked</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int)$supplier['id'] ?>" <?= (int)$supplier['id'] === (int)($record['supplier_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Reference No</label>
                <input type="text" name="reference_no" class="form-control" value="<?= htmlspecialchars($record['reference_no'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <?php $paymentMethod = $record['payment_method'] ?? 'cash'; ?>
                    <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank" <?= $paymentMethod === 'bank' ? 'selected' : '' ?>>Bank</option>
                    <option value="mobile_money" <?= $paymentMethod === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="credit" <?= $paymentMethod === 'credit' ? 'selected' : '' ?>>Credit</option>
                    <option value="other" <?= $paymentMethod === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <?php $paymentStatus = $record['payment_status'] ?? 'paid'; ?>
                <select name="payment_status" class="form-select" required>
                    <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="partial" <?= $paymentStatus === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Created By</label>
                <select name="created_by" class="form-select">
                    <option value="">Not specified</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user['id'] ?>" <?= (int)$user['id'] === (int)($record['created_by'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(($user['full_name'] ?: $user['username']) . ' (#' . $user['id'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($record['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Update Expense</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/expenses" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
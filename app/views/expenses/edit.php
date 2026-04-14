<?php
$record  = $record  ?? [];
$farms   = $farms   ?? [];
$batches = $batches ?? [];
$categories = $categories ?? [];
$suppliers  = $suppliers  ?? [];
$owners     = $owners     ?? [];
$base = rtrim(BASE_URL, '/');
?>
<div class="finance-card p-4" style="max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Expense</h2>
            <p class="text-muted mb-0">Update this expense record.</p>
        </div>
        <a href="<?= $base ?>/expenses" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= $base ?>/expenses/update">
        <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">

        <div class="row g-3">
            <!-- PAID BY SELECTOR -->
            <?php include BASE_PATH . 'app/views/layouts/paid_by_edit_selector.php'; ?>

            <!-- FARM -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>" <?= (int)$farm['id'] === (int)($record['farm_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($farm['farm_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- DATE -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                <input type="date" name="expense_date" class="form-control"
                       value="<?= htmlspecialchars($record['expense_date'] ?? '') ?>" required>
            </div>

            <!-- AMOUNT -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Amount (GHS) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control"
                       value="<?= htmlspecialchars($record['amount'] ?? '') ?>" required>
            </div>

            <!-- DESCRIPTION -->
            <div class="col-12">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control"
                       value="<?= htmlspecialchars($record['description'] ?? '') ?>"
                       placeholder="What was this expense for?" required>
            </div>

            <!-- CATEGORY -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (int)$cat['id'] === (int)($record['category_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- PAYMENT METHOD -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <?php $pm = $record['payment_method'] ?? 'cash'; ?>
                    <option value="cash" <?= $pm === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank_transfer" <?= $pm === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="cheque" <?= $pm === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                    <option value="mobile_money" <?= $pm === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                </select>
            </div>

            <!-- PAYMENT STATUS -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <?php $ps = $record['payment_status'] ?? 'paid'; ?>
                    <option value="paid"    <?= $ps === 'paid'    ? 'selected' : '' ?>>Paid</option>
                    <option value="partial" <?= $ps === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="unpaid"  <?= $ps === 'unpaid'  ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>

            <!-- AMOUNT PAID (shown when partial) -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Amount Paid (GHS)</label>
                <input type="number" step="0.01" min="0" name="amount_paid" class="form-control"
                       value="<?= htmlspecialchars($record['amount_paid'] ?? 0) ?>">
            </div>

            <!-- REFERENCE -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Reference No.</label>
                <input type="text" name="expense_reference" class="form-control"
                       value="<?= htmlspecialchars($record['expense_reference'] ?? '') ?>"
                       placeholder="Receipt / invoice number">
            </div>

            <!-- NOTES -->
            <div class="col-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="2"
                          placeholder="Additional notes"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-dark px-4">
                <i class="bi bi-check-circle me-1"></i> Update Expense
            </button>
            <a href="<?= $base ?>/expenses" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

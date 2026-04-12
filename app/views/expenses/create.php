<div class="finance-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Add Expense</h2>
            <p class="text-muted mb-0">Record a new financial expense for the business.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/expenses" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/expenses/store">
        <div class="row g-3">
            <?php include BASE_PATH . 'app/views/layouts/paid_by_selector.php'; ?>

            <div class="col-md-4">
                <label class="form-label">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>">
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
                        <option value="<?= (int)$b['id'] ?>">
                            <?= htmlspecialchars($b['batch_code'] . (!empty($b['batch_name']) ? ' - ' . $b['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Expense Date</label>
                <input type="date" name="expense_date" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Expense Title</label>
                <input type="text" name="description" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>">
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
                        <option value="<?= (int)$supplier['id'] ?>">
                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Reference No</label>
                <input type="text" name="reference_no" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="credit">Credit</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-select" required onchange="togglePaymentFields()">
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>

            <div class="col-md-4" id="amount_paid_field" style="display:none;">
                <label class="form-label">Amount Paid</label>
                <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="0">
            </div>

            <div class="col-md-4" id="due_date_field" style="display:none;">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Created By</label>
                <select name="created_by" class="form-select">
                    <option value="">Not specified</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user['id'] ?>">
                            <?= htmlspecialchars(($user['full_name'] ?: $user['username']) . ' (#' . $user['id'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="4"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Expense</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/expenses" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function togglePaymentFields() {
    const status = document.getElementById('payment_status').value;
    const amountPaidField = document.getElementById('amount_paid_field');
    const dueDateField = document.getElementById('due_date_field');
    
    if (status === 'partial') {
        amountPaidField.style.display = 'block';
        dueDateField.style.display = 'block';
    } else if (status === 'unpaid') {
        amountPaidField.style.display = 'none';
        dueDateField.style.display = 'block';
        document.getElementById('amount_paid').value = '0';
    } else {
        amountPaidField.style.display = 'none';
        dueDateField.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', togglePaymentFields);
</script>
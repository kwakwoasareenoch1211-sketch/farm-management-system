<?php
$farms = $farms ?? [];
$batches = $batches ?? [];
$customers = $customers ?? [];
?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Invalid farm, batch, or customer selected. Please check the form and try again.
    </div>
<?php endif; ?>

<style>
    .sales-form-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .sales-form-note {
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        color: #475569;
        font-size: 14px;
    }

    .sales-preview-box {
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        padding: 16px;
    }

    .sales-preview-label {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .sales-preview-value {
        font-size: 1.25rem;
        font-weight: 700;
    }
</style>

<div class="sales-form-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Add Sale</h2>
            <p class="text-muted mb-0">Record business revenue and let finance update automatically.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/sales" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="sales-form-note mb-4">
        The system calculates the final amount automatically from:
        <strong>Subtotal - Discount Amount</strong>.
        Payment status is also determined automatically from the amount paid.
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/sales/store">
        <div class="row g-3">
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
                    <?php foreach ($batches as $batch): ?>
                        <option value="<?= (int)$batch['id'] ?>">
                            <?= htmlspecialchars($batch['batch_code'] . (!empty($batch['batch_name']) ? ' - ' . $batch['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">Walk-in / not linked</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= (int)$customer['id'] ?>">
                            <?= htmlspecialchars($customer['customer_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Sale Date</label>
                <input type="date" name="sale_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Invoice No</label>
                <input type="text" name="invoice_no" class="form-control" placeholder="Leave blank to auto-generate">
            </div>

            <div class="col-md-4">
                <label class="form-label">Sale Type</label>
                <select name="sale_type" class="form-select" required>
                    <option value="eggs">Eggs</option>
                    <option value="meat">Meat</option>
                    <option value="live_birds">Live Birds</option>
                    <option value="manure">Manure</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" class="form-control" required placeholder="e.g. Egg crates, Broilers, Manure bags">
            </div>

            <div class="col-md-3">
                <label class="form-label">Subtotal</label>
                <input type="number" step="0.01" min="0" name="subtotal" id="subtotal" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Discount Amount</label>
                <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label class="form-label">Amount Paid</label>
                <input type="number" step="0.01" min="0" name="amount_paid" id="amount_paid" class="form-control" value="0">
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
                <select name="payment_status" id="payment_status" class="form-select">
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid" selected>Unpaid</option>
                </select>
            </div>

            <div class="col-md-4">
                <div class="sales-preview-box h-100">
                    <div class="sales-preview-label">Computed Total</div>
                    <div class="sales-preview-value" id="computed_total">GHS 0.00</div>
                    <small class="text-muted">Calculated from subtotal minus discount.</small>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="4" placeholder="Optional notes about this sale"></textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-dark">Save Sale</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/sales" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    (function () {
        const subtotalInput = document.getElementById('subtotal');
        const discountInput = document.getElementById('discount_amount');
        const amountPaidInput = document.getElementById('amount_paid');
        const paymentStatusSelect = document.getElementById('payment_status');
        const totalBox = document.getElementById('computed_total');

        function toNumber(value) {
            const n = parseFloat(value);
            return isNaN(n) ? 0 : n;
        }

        function updatePreview() {
            const subtotal = Math.max(0, toNumber(subtotalInput.value));
            const discount = Math.max(0, toNumber(discountInput.value));
            const amountPaid = Math.max(0, toNumber(amountPaidInput.value));
            const total = Math.max(0, subtotal - discount);

            totalBox.textContent = 'GHS ' + total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (total <= 0) {
                paymentStatusSelect.value = 'unpaid';
            } else if (amountPaid >= total) {
                paymentStatusSelect.value = 'paid';
            } else if (amountPaid > 0) {
                paymentStatusSelect.value = 'partial';
            } else {
                paymentStatusSelect.value = 'unpaid';
            }
        }

        subtotalInput.addEventListener('input', updatePreview);
        discountInput.addEventListener('input', updatePreview);
        amountPaidInput.addEventListener('input', updatePreview);

        updatePreview();
    })();
</script>
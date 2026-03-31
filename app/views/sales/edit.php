<?php
$record = $record ?? [];
$farms = $farms ?? [];
$batches = $batches ?? [];
$customers = $customers ?? [];

$saleType = $record['sale_type'] ?? 'other';
$paymentMethod = $record['payment_method'] ?? 'cash';
$paymentStatus = $record['payment_status'] ?? 'paid';

$quantity = (float)($record['quantity'] ?? 0);
$unitPrice = (float)($record['unit_price'] ?? 0);
$totalAmount = $quantity * $unitPrice;
?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Invalid farm, batch, or customer selected. Please check the form and try again.
    </div>
<?php endif; ?>


<div class="finance-dashboard-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Edit Sale</h2>
            <p class="text-muted mb-0">Update this sales record and keep revenue totals accurate.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/sales" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
<div class="col-md-3">
    <label class="form-label">Subtotal</label>
    <input type="number" step="0.01" min="0" name="subtotal" class="form-control" value="<?= htmlspecialchars($record['subtotal'] ?? '') ?>" required>
</div>

<div class="col-md-3">
    <label class="form-label">Discount Amount</label>
    <input type="number" step="0.01" min="0" name="discount_amount" class="form-control" value="<?= htmlspecialchars($record['discount_amount'] ?? '0') ?>">
</div>

<div class="col-md-3">
    <label class="form-label">Amount Paid</label>
    <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="<?= htmlspecialchars($record['amount_paid'] ?? '0') ?>">
</div>

<div class="col-md-3">
    <label class="form-label">Computed Total</label>
    <?php
    $subtotalValue = (float)($record['subtotal'] ?? 0);
    $discountValue = (float)($record['discount_amount'] ?? 0);
    $computedTotal = max(0, $subtotalValue - $discountValue);
    ?>
    <input type="text" class="form-control" value="GHS <?= number_format($computedTotal, 2) ?>" disabled>
</div>

<div class="col-md-6">
    <label class="form-label">Invoice No</label>
    <input type="text" name="invoice_no" class="form-control" value="<?= htmlspecialchars($record['invoice_no'] ?? '') ?>">
</div>
</div>
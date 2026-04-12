<?php
$inventoryItems = $inventoryItems ?? [];
$suppliers      = $suppliers ?? [];
$batches        = $batches ?? [];
$base = rtrim(BASE_URL, '/');
?>
<div class="container py-4" style="max-width:800px;">
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Receive Stock</h2>
                <p class="text-muted mb-0">Record inventory received. Feed/Medication items auto-sync to poultry records if a batch is selected.</p>
            </div>
            <a href="<?= $base ?>/inventory/receipts" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        <div class="alert alert-info small mb-4" style="border-radius:12px;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Auto-Sync:</strong> If the item category is <strong>Feed</strong> or <strong>Medication</strong> and you select a batch, a record will automatically be created in the poultry feed/medication section. No double entry needed.
        </div>

        <form method="POST" action="<?= $base ?>/inventory/receipts/store">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Inventory Item <span class="text-danger">*</span></label>
                    <select name="inventory_item_id" class="form-select" required id="itemSelect">
                        <option value="">Select item</option>
                        <?php foreach ($inventoryItems as $item): ?>
                            <option value="<?= (int)$item['id'] ?>" data-category="<?= htmlspecialchars($item['category'] ?? '') ?>">
                                <?= htmlspecialchars($item['item_name']) ?>
                                (<?= htmlspecialchars(ucfirst($item['category'] ?? '')) ?>)
                                — Stock: <?= number_format((float)$item['current_stock'], 1) ?> <?= htmlspecialchars($item['unit_of_measure'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">No supplier</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6" id="batchRow">
                    <label class="form-label fw-semibold">Batch <span class="text-muted small">(required for Feed/Medication auto-sync)</span></label>
                    <select name="batch_id" class="form-select">
                        <option value="">No batch</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>">
                                <?= htmlspecialchars(($b['batch_code'] ?? '') . (!empty($b['batch_name']) ? ' — ' . $b['batch_name'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Quantity Received <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity_received" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Unit Cost (GHS)</label>
                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Reference No</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="Invoice/PO number">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-dark px-4">Save Receipt</button>
                <a href="<?= $base ?>/inventory/receipts" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Show auto-sync notice when feed/medication selected
document.getElementById('itemSelect').addEventListener('change', function() {
    const cat = this.options[this.selectedIndex]?.dataset?.category || '';
    const batchRow = document.getElementById('batchRow');
    if (cat === 'feed' || cat === 'medication') {
        batchRow.style.border = '2px solid #3b82f6';
        batchRow.style.borderRadius = '8px';
        batchRow.style.padding = '8px';
        batchRow.querySelector('label').innerHTML = '<i class="bi bi-arrow-repeat text-primary me-1"></i>Batch <span class="badge text-bg-primary ms-1">Auto-sync enabled</span>';
    } else {
        batchRow.style.border = '';
        batchRow.style.padding = '';
        batchRow.querySelector('label').textContent = 'Batch (optional)';
    }
});
</script>

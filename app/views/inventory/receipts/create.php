<?php 
$inventoryItems = $inventoryItems ?? []; 
$suppliers = $suppliers ?? [];
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Create Stock Receipt</h2>
                <p class="text-muted mb-0">Record inventory received into stock.</p>
            </div>
            <a href="<?= rtrim(BASE_URL,'/') ?>/inventory/receipts" class="btn btn-outline-secondary">Back</a>
        </div>

        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/inventory/receipts/store">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Inventory Item</label>
                    <select name="inventory_item_id" class="form-select" required>
                        <option value="">Select item</option>
                        <?php foreach($inventoryItems as $item): ?>
                            <option value="<?= (int)$item['id'] ?>">
                                <?= htmlspecialchars($item['item_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Select supplier (optional)</option>
                        <?php foreach($suppliers as $supplier): ?>
                            <option value="<?= (int)$supplier['id'] ?>">
                                <?= htmlspecialchars($supplier['supplier_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Receipt Date</label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity Received</label>
                    <input type="number" step="0.01" min="0.01" name="quantity_received" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unit Cost</label>
                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reference No</label>
                    <input type="text" name="reference_no" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-dark">Save Receipt</button>
                <a href="<?= rtrim(BASE_URL,'/') ?>/inventory/receipts" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
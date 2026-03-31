<?php 
$inventoryItems = $inventoryItems ?? []; 
$batches = $batches ?? [];
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Create Stock Issue</h2>
                <p class="text-muted mb-0">Record inventory issued from stock.</p>
            </div>
            <a href="<?= rtrim(BASE_URL,'/') ?>/inventory/issues" class="btn btn-outline-secondary">Back</a>
        </div>

        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/inventory/issues/store">
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
                    <label class="form-label">Batch (Optional)</label>
                    <select name="batch_id" class="form-select">
                        <option value="">Select batch (optional)</option>
                        <?php foreach($batches as $batch): ?>
                            <option value="<?= (int)$batch['id'] ?>">
                                <?= htmlspecialchars($batch['batch_name'] ?? $batch['batch_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity Issued</label>
                    <input type="number" step="0.01" min="0.01" name="quantity_issued" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purpose</label>
                    <select name="issue_reason" class="form-select">
                        <option value="farm_use">Farm Use</option>
                        <option value="treatment">Treatment</option>
                        <option value="feeding">Feeding</option>
                        <option value="damage">Damage</option>
                        <option value="loss">Loss</option>
                        <option value="transfer">Transfer</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-dark">Save Issue</button>
                <a href="<?= rtrim(BASE_URL,'/') ?>/inventory/issues" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
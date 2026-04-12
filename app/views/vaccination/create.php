<?php
$farms = $farms ?? [];
$batches = $batches ?? [];
$inventoryItems = $inventoryItems ?? [];
?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'insufficient_stock'): ?>
    <div class="alert alert-danger">Not enough stock available for the selected vaccine inventory item.</div>
<?php endif; ?>

<div class="inv-card p-4">
    <h2 class="fw-bold mb-3">Add Vaccination Record</h2>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/vaccination/store">
        <div class="row g-3">
<div class="col-md-4">
                <label class="form-label">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>"><?= htmlspecialchars($farm['farm_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batches as $batch): ?>
                        <option value="<?= (int)$batch['id'] ?>">
                            <?= htmlspecialchars(($batch['batch_code'] ?? '') . (!empty($batch['batch_name']) ? ' - ' . $batch['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Inventory Item</label>
                <select name="inventory_item_id" class="form-select">
                    <option value="">No stock link</option>
                    <?php foreach ($inventoryItems as $item): ?>
                        <option value="<?= (int)$item['id'] ?>">
                            <?= htmlspecialchars($item['item_name']) ?> (Stock: <?= number_format((float)$item['current_stock'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Record Date</label>
                <input type="date" name="record_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Vaccine Name</label>
                <input type="text" name="vaccine_name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Dose Quantity</label>
                <input type="number" step="0.01" min="1" name="dose_qty" class="form-control" value="1" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Disease Target</label>
                <input type="text" name="disease_target" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Dosage</label>
                <input type="text" name="dosage" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Route</label>
                <input type="text" name="route" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Cost Amount</label>
                <input type="number" step="0.01" min="0" name="cost_amount" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label class="form-label">Next Due Date</label>
                <input type="date" name="next_due_date" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Administered By</label>
                <input type="text" name="administered_by" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Vaccination Record</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/vaccination" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

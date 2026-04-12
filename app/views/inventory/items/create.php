<?php $farms = $farms ?? []; ?>

<style>
    .inv-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }
</style>

<div class="inv-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Add Inventory Item</h2>
            <p class="text-muted mb-0">Create a stock-controlled item for feed, medication, vaccine, equipment, or supplies.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items" class="btn btn-outline-secondary">Back</a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/inventory/items/store">
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
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="feed">Feed</option>
                    <option value="medication">Medication</option>
                    <option value="vaccine">Vaccine</option>
                    <option value="equipment">Equipment</option>
                    <option value="supplies">Supplies</option>
                    <option value="general">General</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit of Measure</label>
                <input type="text" name="unit_of_measure" class="form-control" value="unit" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Opening Stock</label>
                <input type="number" step="0.01" min="0" name="current_stock" class="form-control" value="0" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Reorder Level</label>
                <input type="number" step="0.01" min="0" name="reorder_level" class="form-control" value="0" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Unit Cost</label>
                <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="0" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Item</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/inventory/items" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
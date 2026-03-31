<div class="work-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Edit Egg Production Record</h2>
            <p class="text-muted mb-0">Update this record and keep egg analytics accurate.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/egg-production" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/egg-production/update">
        <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Farm ID</label>
                <input type="number" name="farm_id" class="form-control" value="<?= htmlspecialchars($record['farm_id'] ?? '1') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= (int)$b['id'] === (int)$record['batch_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['batch_code'] . (!empty($b['batch_name']) ? ' - ' . $b['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Record Date</label>
                <input type="date" name="record_date" class="form-control" value="<?= htmlspecialchars($record['record_date'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($record['quantity'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Cracked Quantity</label>
                <input type="number" name="cracked_quantity" class="form-control" value="<?= htmlspecialchars($record['cracked_quantity'] ?? 0) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Spoiled Quantity</label>
                <input type="number" name="spoiled_quantity" class="form-control" value="<?= htmlspecialchars($record['spoiled_quantity'] ?? 0) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Average Weight (grams)</label>
                <input type="number" step="0.001" name="average_weight_grams" class="form-control" value="<?= htmlspecialchars($record['average_weight_grams'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Update Record</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/egg-production" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
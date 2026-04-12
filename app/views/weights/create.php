<div class="work-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Add Weight Record</h2>
            <p class="text-muted mb-0">Save a weight sample and let the system compute average weight automatically.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/weights" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/weights/store">
        <div class="row g-3">
<div class="col-md-4">
                <label class="form-label">Farm ID</label>
                <input type="number" name="farm_id" class="form-control" value="1" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>">
                            <?= htmlspecialchars($b['batch_code'] . (!empty($b['batch_name']) ? ' - ' . $b['batch_name'] : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Record Date</label>
                <input type="date" name="record_date" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Sample Size</label>
                <input type="number" name="sample_size" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Total Weight (kg)</label>
                <input type="number" step="0.001" name="total_weight_kg" class="form-control" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Record</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/weights" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

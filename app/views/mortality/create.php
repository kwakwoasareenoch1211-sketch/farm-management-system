<div class="work-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Add Mortality Record</h2>
            <p class="text-muted mb-0">Save a mortality event and automatically refresh batch live quantity.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/mortality" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/mortality/store">
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

            <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Cause</label>
                <input type="text" name="cause" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Disposal Method</label>
                <input type="text" name="disposal_method" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Record</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/mortality" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

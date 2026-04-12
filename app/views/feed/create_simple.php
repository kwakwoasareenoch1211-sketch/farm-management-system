<?php
$farms = $farms ?? [];
$batches = $batches ?? [];
$base = rtrim(BASE_URL, '/');
?>

<div class="card border-0 shadow-sm rounded-4 p-4" style="max-width:800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Record Feed Usage</h4>
            <p class="text-muted small mb-0">Enter feed details directly - no inventory lookup needed</p>
        </div>
        <a href="<?= $base ?>/feed" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <form method="POST" action="<?= $base ?>/feed/store" id="feedForm">

        <div class="row g-3">
            <?php include BASE_PATH . 'app/views/layouts/paid_by_selector.php'; ?>
<div class="col-md-6">
                <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>"><?= htmlspecialchars($farm['farm_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                <select name="batch_id" class="form-select" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batches as $batch): ?>
                        <option value="<?= (int)$batch['id'] ?>">
                            <?= htmlspecialchars(($batch['batch_code'] ?? '') . (!empty($batch['batch_name']) ? ' — ' . $batch['batch_name'] : '')) ?>
                            (<?= number_format((int)($batch['current_quantity'] ?? 0)) ?> birds)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Feed Name <span class="text-danger">*</span></label>
                <input type="text" 
                       name="feed_name" 
                       class="form-control" 
                       placeholder="e.g., Starter Feed, Grower Feed, Layer Mash"
                       required>
                <div class="form-text">Enter the name or type of feed used</div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Record Date <span class="text-danger">*</span></label>
                <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                <input type="number" 
                       step="0.01" 
                       min="0.01" 
                       name="quantity_kg" 
                       id="quantityKg" 
                       class="form-control" 
                       placeholder="0.00"
                       required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Unit Cost (GHS/kg) <span class="text-danger">*</span></label>
                <input type="number" 
                       step="0.01" 
                       min="0" 
                       name="unit_cost" 
                       id="unitCost" 
                       class="form-control" 
                       placeholder="0.00"
                       required>
            </div>

            <div class="col-md-12">
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="bi bi-calculator"></i>
                    <div>
                        <strong>Total Cost:</strong> <span id="totalCost" class="fs-5">GHS 0.00</span>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this feed record"></textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-1"></i>Save Feed Record
            </button>
            <a href="<?= $base ?>/feed" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function() {
    const quantityInput = document.getElementById('quantityKg');
    const unitCostInput = document.getElementById('unitCost');
    const totalCostSpan = document.getElementById('totalCost');

    function calculateTotal() {
        const qty = parseFloat(quantityInput.value) || 0;
        const cost = parseFloat(unitCostInput.value) || 0;
        const total = qty * cost;
        totalCostSpan.textContent = 'GHS ' + total.toFixed(2);
    }

    quantityInput.addEventListener('input', calculateTotal);
    unitCostInput.addEventListener('input', calculateTotal);
})();
</script>


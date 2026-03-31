<?php
$record    = $record    ?? [];
$farms     = $farms     ?? [];
$batches   = $batches   ?? [];
$feedItems = $feedItems ?? [];
$base      = rtrim(BASE_URL, '/');
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1">Edit Feed Record</h2>
    <p class="text-muted mb-0">Update feed usage details</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= $base ?>/feed/update">
                    <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Feed Item <span class="text-danger">*</span></label>
                        <select name="inventory_item_id" class="form-select" required>
                            <option value="">-- Select Feed Item --</option>
                            <?php foreach ($feedItems as $item): ?>
                                <?php if (strtolower($item['category'] ?? '') === 'feed'): ?>
                                    <option value="<?= (int)$item['id'] ?>" 
                                            <?= (int)($record['inventory_item_id'] ?? 0) === (int)$item['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['item_name'] ?? '') ?> 
                                        (GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?>/kg)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Select Batch --</option>
                            <?php foreach ($batches as $batch): ?>
                                <option value="<?= (int)$batch['id'] ?>" 
                                        <?= (int)($record['batch_id'] ?? 0) === (int)$batch['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($batch['batch_code'] ?? '') . ' - ' . ($batch['batch_name'] ?? '')) ?>
                                    (<?= (int)($batch['current_quantity'] ?? 0) ?> birds)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_kg" class="form-control" 
                                   step="0.01" min="0.01" 
                                   value="<?= (float)($record['quantity_kg'] ?? 0) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="record_date" class="form-control" 
                                   value="<?= htmlspecialchars($record['record_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
                    </div>

                    <input type="hidden" name="farm_id" value="<?= (int)($record['farm_id'] ?? 1) ?>">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Update Feed Record
                        </button>
                        <a href="<?= $base ?>/feed" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

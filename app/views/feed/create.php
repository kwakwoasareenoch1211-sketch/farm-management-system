<?php
$farms     = $farms     ?? [];
$batches   = $batches   ?? [];
$feedItems = $feedItems ?? [];
$base      = rtrim(BASE_URL, '/');
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1">Record Feed Usage</h2>
    <p class="text-muted mb-0">Select feed item from inventory and record usage</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= $base ?>/feed/store">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Feed Item <span class="text-danger">*</span></label>
                        <select name="inventory_item_id" class="form-select" required id="feedItemSelect">
                            <option value="">-- Select Feed Item --</option>
                            <?php foreach ($feedItems as $item): ?>
                                <?php if (strtolower($item['category'] ?? '') === 'feed'): ?>
                                    <option value="<?= (int)$item['id'] ?>" 
                                            data-cost="<?= (float)($item['unit_cost'] ?? 0) ?>">
                                        <?= htmlspecialchars($item['item_name'] ?? '') ?> 
                                        (GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?>/kg)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Feed items are managed in <a href="<?= $base ?>/feed/items">Feed Items</a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Select Batch --</option>
                            <?php foreach ($batches as $batch): ?>
                                <?php if (($batch['status'] ?? '') === 'active'): ?>
                                    <option value="<?= (int)$batch['id'] ?>">
                                        <?= htmlspecialchars(($batch['batch_code'] ?? '') . ' - ' . ($batch['batch_name'] ?? '')) ?>
                                        (<?= (int)($batch['current_quantity'] ?? 0) ?> birds)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <input type="hidden" name="farm_id" value="1">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Feed Record
                        </button>
                        <a href="<?= $base ?>/feed" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>How It Works</h6>
                <ol class="small mb-0">
                    <li class="mb-2">Select feed item from inventory</li>
                    <li class="mb-2">Choose batch to feed</li>
                    <li class="mb-2">Enter quantity used</li>
                    <li>Save - feed record created</li>
                </ol>
                <hr>
                <p class="small text-muted mb-0">
                    <strong>Note:</strong> Feed items are permanent reference data. Add new items in 
                    <a href="<?= $base ?>/feed/items">Feed Items</a>.
                </p>
            </div>
        </div>
    </div>
</div>

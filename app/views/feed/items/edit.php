<?php
$item = $item ?? [];
$base = rtrim(BASE_URL, '/');
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1">Edit Feed Item</h2>
    <p class="text-muted mb-0">Update feed item details</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= $base ?>/feed/items/update?id=<?= (int)$item['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required 
                               value="<?= htmlspecialchars($item['item_name'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="feed" <?= ($item['category'] ?? '') === 'feed' ? 'selected' : '' ?>>Feed</option>
                                <option value="medication" <?= ($item['category'] ?? '') === 'medication' ? 'selected' : '' ?>>Medication</option>
                                <option value="other" <?= ($item['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unit of Measure</label>
                            <input type="text" name="unit_of_measure" class="form-control" 
                                   value="<?= htmlspecialchars($item['unit_of_measure'] ?? 'kg') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit Cost (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_cost" class="form-control" step="0.01" min="0" required 
                               value="<?= (float)($item['unit_cost'] ?? 0) ?>">
                        <div class="form-text">Cost per unit (e.g., cost per kg)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($item['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Update Feed Item
                        </button>
                        <a href="<?= $base ?>/feed/items" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

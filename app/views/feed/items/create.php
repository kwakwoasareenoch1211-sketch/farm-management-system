<?php
$base = rtrim(BASE_URL, '/');
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1">Add Feed Item</h2>
    <p class="text-muted mb-0">Create a new feed item for use in feed records</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= $base ?>/feed/items/store">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required placeholder="e.g., Starter Feed, Grower Feed">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="feed" selected>Feed</option>
                                <option value="medication">Medication</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unit of Measure</label>
                            <input type="text" name="unit_of_measure" class="form-control" value="kg" placeholder="kg, bags, liters">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit Cost (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_cost" class="form-control" step="0.01" min="0" required placeholder="0.00">
                        <div class="form-text">Cost per unit (e.g., cost per kg)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional information about this feed item"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Feed Item
                        </button>
                        <a href="<?= $base ?>/feed/items" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>About Feed Items</h6>
                <p class="small mb-2">Feed items are permanent reference data used when recording feed usage.</p>
                <p class="small mb-2">Once created, they appear in the feed dropdown for easy selection.</p>
                <p class="small mb-0">You can update the cost anytime to reflect current prices.</p>
            </div>
        </div>
    </div>
</div>

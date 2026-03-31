<?php
$farms       = $farms       ?? [];
$animalTypes = $animalTypes ?? [];
$base        = rtrim(BASE_URL, '/');
?>

<div class="container py-4" style="max-width:860px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Create Batch</h2>
            <p class="text-muted mb-0">Add a new poultry batch for production tracking.</p>
        </div>
        <a href="<?= $base ?>/batches" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_selection'): ?>
        <div class="alert alert-danger mb-3">Invalid farm or animal type selected. Please choose a valid option.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/batches/store">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                        <select name="farm_id" class="form-select" required>
                            <option value="">Select farm</option>
                            <?php foreach ($farms as $f): ?>
                                <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['farm_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Animal Type <span class="text-danger">*</span></label>
                        <select name="animal_type_id" class="form-select" required>
                            <option value="">Select animal type</option>
                            <?php foreach ($animalTypes as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Batch Name</label>
                        <input type="text" name="batch_name" class="form-control" placeholder="e.g. Layer Batch March">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Housing Unit ID</label>
                        <input type="number" name="housing_unit_id" class="form-control" placeholder="Optional">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Production Purpose <span class="text-danger">*</span></label>
                        <select name="production_purpose" class="form-select" required>
                            <option value="eggs">Eggs</option>
                            <option value="meat">Meat (Broiler)</option>
                            <option value="breeding">Breeding</option>
                            <option value="mixed">Mixed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bird Subtype</label>
                        <select name="bird_subtype" class="form-select">
                            <option value="">Select subtype</option>
                            <option value="layers">Layers</option>
                            <option value="broilers">Broilers</option>
                            <option value="cockerels">Cockerels</option>
                            <option value="pullets">Pullets</option>
                            <option value="dual-purpose">Dual-purpose</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Breed</label>
                        <input type="text" name="breed" class="form-control" placeholder="e.g. Ross 308, Isa Brown">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Source / Supplier</label>
                        <input type="text" name="source_name" class="form-control" placeholder="Where birds were sourced">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Expected End Date</label>
                        <input type="date" name="expected_end_date" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Initial Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="initial_quantity" class="form-control" min="1" required placeholder="Number of birds">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unit Cost per Bird (GHS)</label>
                        <input type="number" step="0.01" name="initial_unit_cost" class="form-control" min="0" value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" selected>Active</option>
                            <option value="planned">Planned</option>
                            <option value="completed">Completed</option>
                            <option value="sold">Sold</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Optional notes about this batch"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-4">Save Batch</button>
                    <a href="<?= $base ?>/batches" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$batch       = $batch       ?? [];
$farms       = $farms       ?? [];
$animalTypes = $animalTypes ?? [];
$base        = rtrim(BASE_URL, '/');
?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_selection'): ?>
    <div class="alert alert-danger">Invalid farm or animal type selected.</div>
<?php endif; ?>

<div class="container py-4" style="max-width:860px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Batch</h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($batch['batch_code'] ?? '') ?> — <?= htmlspecialchars($batch['batch_name'] ?? '') ?></p>
        </div>
        <a href="<?= $base ?>/batches/view?id=<?= (int)($batch['id'] ?? 0) ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/batches/update">
                <input type="hidden" name="id" value="<?= (int)($batch['id'] ?? 0) ?>">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                        <select name="farm_id" class="form-select" required>
                            <option value="">Select farm</option>
                            <?php foreach ($farms as $f): ?>
                                <option value="<?= (int)$f['id'] ?>" <?= (int)($batch['farm_id'] ?? 0) === (int)$f['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['farm_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Animal Type <span class="text-danger">*</span></label>
                        <select name="animal_type_id" class="form-select" required>
                            <option value="">Select animal type</option>
                            <?php foreach ($animalTypes as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= (int)($batch['animal_type_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Batch Name</label>
                        <input type="text" name="batch_name" class="form-control" value="<?= htmlspecialchars($batch['batch_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Housing Unit ID</label>
                        <input type="number" name="housing_unit_id" class="form-control" value="<?= (int)($batch['housing_unit_id'] ?? 0) ?: '' ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Production Purpose <span class="text-danger">*</span></label>
                        <select name="production_purpose" class="form-select" required>
                            <?php foreach (['eggs'=>'Eggs','meat'=>'Meat (Broiler)','breeding'=>'Breeding','mixed'=>'Mixed','other'=>'Other'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($batch['production_purpose'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bird Subtype</label>
                        <select name="bird_subtype" class="form-select">
                            <option value="">Select subtype</option>
                            <?php foreach (['layers'=>'Layers','broilers'=>'Broilers','cockerels'=>'Cockerels','pullets'=>'Pullets','dual-purpose'=>'Dual-purpose','other'=>'Other'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($batch['bird_subtype'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Breed</label>
                        <input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($batch['breed'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Source / Supplier</label>
                        <input type="text" name="source_name" class="form-control" value="<?= htmlspecialchars($batch['source_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= htmlspecialchars($batch['purchase_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($batch['start_date'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Expected End Date</label>
                        <input type="date" name="expected_end_date" class="form-control" value="<?= htmlspecialchars($batch['expected_end_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Initial Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="initial_quantity" class="form-control" value="<?= (int)($batch['initial_quantity'] ?? 0) ?>" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unit Cost per Bird (GHS)</label>
                        <input type="number" step="0.01" name="initial_unit_cost" class="form-control" value="<?= number_format((float)($batch['initial_unit_cost'] ?? 0), 2) ?>" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['active'=>'Active','planned'=>'Planned','completed'=>'Completed','sold'=>'Sold','closed'=>'Closed'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($batch['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="3" class="form-control"><?= htmlspecialchars($batch['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-4">Update Batch</button>
                    <a href="<?= $base ?>/batches" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

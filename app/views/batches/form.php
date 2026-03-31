<?php
$batch = $batch ?? [];
?>

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Farm</label>
        <select name="farm_id" class="form-select" required>
            <option value="">Select farm</option>
            <?php foreach (($farms ?? []) as $farm): ?>
                <option value="<?= (int)$farm['id'] ?>" <?= (int)($batch['farm_id'] ?? 0) === (int)$farm['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($farm['farm_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Animal Type</label>
        <select name="animal_type_id" class="form-select" required>
            <option value="">Select animal type</option>
            <?php foreach (($animalTypes ?? []) as $animalType): ?>
                <option value="<?= (int)$animalType['id'] ?>" <?= (int)($batch['animal_type_id'] ?? 0) === (int)$animalType['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($animalType['type_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Housing Unit</label>
        <select name="housing_unit_id" class="form-select">
            <option value="">No housing unit</option>
            <?php foreach (($housingUnits ?? []) as $housingUnit): ?>
                <option value="<?= (int)$housingUnit['id'] ?>" <?= (int)($batch['housing_unit_id'] ?? 0) === (int)$housingUnit['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($housingUnit['unit_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Batch Code</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($batch['batch_code'] ?? 'Auto-generated') ?>" disabled>
    </div>

    <div class="col-md-4">
        <label class="form-label">Batch Name</label>
        <input type="text" name="batch_name" class="form-control" value="<?= htmlspecialchars($batch['batch_name'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Production Purpose</label>
        <?php $purpose = $batch['production_purpose'] ?? 'mixed'; ?>
        <select name="production_purpose" class="form-select" required>
            <option value="eggs" <?= $purpose === 'eggs' ? 'selected' : '' ?>>Eggs</option>
            <option value="meat" <?= $purpose === 'meat' ? 'selected' : '' ?>>Meat</option>
            <option value="breeding" <?= $purpose === 'breeding' ? 'selected' : '' ?>>Breeding</option>
            <option value="mixed" <?= $purpose === 'mixed' ? 'selected' : '' ?>>Mixed</option>
            <option value="other" <?= $purpose === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Bird Subtype</label>
        <?php $subtype = $batch['bird_subtype'] ?? ''; ?>
        <select name="bird_subtype" class="form-select">
            <option value="">Select subtype</option>
            <option value="layers" <?= $subtype === 'layers' ? 'selected' : '' ?>>Layers</option>
            <option value="broilers" <?= $subtype === 'broilers' ? 'selected' : '' ?>>Broilers</option>
            <option value="cockerels" <?= $subtype === 'cockerels' ? 'selected' : '' ?>>Cockerels</option>
            <option value="pullets" <?= $subtype === 'pullets' ? 'selected' : '' ?>>Pullets</option>
            <option value="dual-purpose" <?= $subtype === 'dual-purpose' ? 'selected' : '' ?>>Dual Purpose</option>
            <option value="other" <?= $subtype === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Breed</label>
        <input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($batch['breed'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Source Name</label>
        <input type="text" name="source_name" class="form-control" value="<?= htmlspecialchars($batch['source_name'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Purchase Date</label>
        <input type="date" name="purchase_date" class="form-control" value="<?= htmlspecialchars($batch['purchase_date'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($batch['start_date'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Expected End Date</label>
        <input type="date" name="expected_end_date" class="form-control" value="<?= htmlspecialchars($batch['expected_end_date'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Initial Quantity</label>
        <input type="number" name="initial_quantity" class="form-control" value="<?= htmlspecialchars($batch['initial_quantity'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Initial Unit Cost</label>
        <input type="number" step="0.01" name="initial_unit_cost" class="form-control" value="<?= htmlspecialchars($batch['initial_unit_cost'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <?php $status = $batch['status'] ?? 'active'; ?>
        <select name="status" class="form-select" required>
            <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>>Planned</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
    </div>

    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($batch['notes'] ?? '') ?></textarea>
    </div>
</div>
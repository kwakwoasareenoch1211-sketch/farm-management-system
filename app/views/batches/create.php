<?php
$farms        = $farms        ?? [];
$animalTypes  = $animalTypes  ?? [];
$housingUnits = $housingUnits ?? [];
$owners       = $owners       ?? [];
$base         = rtrim(BASE_URL, '/');

// Breed options by animal type
$breedOptions = [
    'Poultry' => [
        'Layers'   => ['Isa Brown','Lohmann Brown','Bovans Brown','Hisex Brown','Shaver Brown','Novogen Brown','Dekalb White','Hy-Line W-36'],
        'Broilers' => ['Ross 308','Ross 708','Cobb 500','Cobb 700','Arbor Acres','Hubbard Classic','Hubbard Flex','Aviagen'],
        'Cockerels'=> ['Local Cockerel','Improved Cockerel','Sasso','Kuroiler','Rainbow Rooster'],
        'Pullets'  => ['Isa Brown Pullet','Lohmann Pullet','Bovans Pullet'],
        'Other'    => ['Local Breed','Crossbreed','Unknown'],
    ],
    'Cattle'  => ['Friesian','Jersey','Holstein','Zebu','Brahman','Hereford','Angus','Simmental'],
    'Goat'    => ['West African Dwarf','Saanen','Boer','Nubian','Alpine','Toggenburg'],
    'Pig'     => ['Large White','Landrace','Duroc','Hampshire','Berkshire','Local Breed'],
    'Sheep'   => ['Djallonke','Sahel','Peul','Merino','Dorper','Suffolk'],
];
?>

<div class="container py-4" style="max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Create Batch</h2>
            <p class="text-muted mb-0">Add a new animal batch for production tracking.</p>
        </div>
        <a href="<?= $base ?>/batches" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/batches/store" id="batchForm">
                <div class="row g-3">

                    <!-- OWNER SELECTOR -->
<!-- FARM -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                        <select name="farm_id" class="form-select" required>
                            <option value="">Select farm</option>
                            <?php foreach ($farms as $f): ?>
                                <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['farm_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ANIMAL TYPE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Animal Type <span class="text-danger">*</span></label>
                        <select name="animal_type_id" class="form-select" id="animalTypeSelect" required>
                            <option value="">Select animal type</option>
                            <?php foreach ($animalTypes as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" data-type="<?= htmlspecialchars($t['type_name']) ?>">
                                    <?= htmlspecialchars($t['type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- BATCH NAME -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Batch Name</label>
                        <input type="text" name="batch_name" class="form-control" placeholder="e.g. Layer Batch April 2026">
                    </div>

                    <!-- HOUSING UNIT -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Housing Unit</label>
                        <select name="housing_unit_id" class="form-select">
                            <option value="">Select housing unit</option>
                            <?php foreach ($housingUnits as $h): ?>
                                <option value="<?= (int)$h['id'] ?>">
                                    <?= htmlspecialchars($h['unit_name']) ?>
                                    (Capacity: <?= number_format((int)$h['capacity']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- PRODUCTION PURPOSE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Production Purpose <span class="text-danger">*</span></label>
                        <select name="production_purpose" class="form-select" id="purposeSelect" required>
                            <option value="eggs">Eggs (Layers)</option>
                            <option value="meat">Meat (Broilers)</option>
                            <option value="breeding">Breeding</option>
                            <option value="mixed">Mixed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- BIRD SUBTYPE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bird Subtype</label>
                        <select name="bird_subtype" class="form-select" id="subtypeSelect">
                            <option value="">Select subtype</option>
                            <option value="layers">Layers</option>
                            <option value="broilers">Broilers</option>
                            <option value="cockerels">Cockerels</option>
                            <option value="pullets">Pullets</option>
                            <option value="dual-purpose">Dual-purpose</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- BREED -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Breed</label>
                        <select name="breed" class="form-select" id="breedSelect">
                            <option value="">Select breed</option>
                            <!-- Populated by JS based on animal type + subtype -->
                        </select>
                        <input type="text" name="breed_custom" id="breedCustom" class="form-control mt-1 d-none" placeholder="Enter breed name">
                    </div>

                    <!-- SOURCE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Source / Supplier</label>
                        <input type="text" name="source_name" class="form-control" placeholder="Where birds were sourced">
                    </div>

                    <!-- PURCHASE DATE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>

                    <!-- START DATE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- EXPECTED END DATE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Expected End Date</label>
                        <input type="date" name="expected_end_date" class="form-control">
                    </div>

                    <!-- INITIAL QUANTITY -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Initial Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="initial_quantity" class="form-control" min="1" required placeholder="Number of birds/animals">
                    </div>

                    <!-- UNIT COST -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unit Cost per Bird (GHS)</label>
                        <input type="number" step="0.01" name="initial_unit_cost" class="form-control" min="0" value="0">
                    </div>

                    <!-- STATUS -->
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

                    <!-- NOTES -->
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

<script>
const breedData = <?= json_encode($breedOptions) ?>;

const animalTypeSelect = document.getElementById('animalTypeSelect');
const subtypeSelect    = document.getElementById('subtypeSelect');
const breedSelect      = document.getElementById('breedSelect');
const breedCustom      = document.getElementById('breedCustom');
const purposeSelect    = document.getElementById('purposeSelect');

function updateBreeds() {
    const typeName   = animalTypeSelect.options[animalTypeSelect.selectedIndex]?.dataset?.type || '';
    const subtype    = subtypeSelect.value;
    const typeBreeds = breedData[typeName];

    breedSelect.innerHTML = '<option value="">Select breed</option>';

    let breeds = [];
    if (typeBreeds) {
        if (typeof typeBreeds === 'object' && !Array.isArray(typeBreeds)) {
            // Has subtypes (Poultry)
            const subtypeKey = subtype.charAt(0).toUpperCase() + subtype.slice(1);
            breeds = typeBreeds[subtypeKey] || typeBreeds['Other'] || [];
        } else {
            breeds = typeBreeds;
        }
    }

    breeds.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b; opt.textContent = b;
        breedSelect.appendChild(opt);
    });

    // Add "Other (type manually)" option
    const other = document.createElement('option');
    other.value = 'other'; other.textContent = 'Other (type manually)';
    breedSelect.appendChild(other);
}

// Auto-set subtype based on purpose
purposeSelect.addEventListener('change', function() {
    const map = { eggs: 'layers', meat: 'broilers', breeding: 'other' };
    if (map[this.value]) subtypeSelect.value = map[this.value];
    updateBreeds();
});

animalTypeSelect.addEventListener('change', updateBreeds);
subtypeSelect.addEventListener('change', updateBreeds);

breedSelect.addEventListener('change', function() {
    if (this.value === 'other') {
        breedCustom.classList.remove('d-none');
        breedCustom.required = true;
    } else {
        breedCustom.classList.add('d-none');
        breedCustom.required = false;
    }
});

// On submit, if custom breed entered, use it
document.getElementById('batchForm').addEventListener('submit', function() {
    if (breedSelect.value === 'other' && breedCustom.value.trim()) {
        breedSelect.name = '';
        breedCustom.name = 'breed';
    }
});

// Initialize
updateBreeds();
</script>


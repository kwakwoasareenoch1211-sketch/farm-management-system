<?php
$farms = $farms ?? [];
$batches = $batches ?? [];
$base = rtrim(BASE_URL, '/');

$activeBatches = array_filter($batches, fn($b) => ($b['status'] ?? '') === 'active');
$totalBirds = array_sum(array_column($activeBatches, 'current_quantity'));
?>

<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-lightning-charge-fill fs-4"></i>
    <div>
        <strong>Real-Time System:</strong> Type to search inventory. Items appear instantly as you type. Stock levels update in real-time. No manual setup needed.
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4" style="max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Record Feed Usage</h4>
            <p class="text-muted small mb-0">Search inventory items in real-time. Stock and costs auto-populate.</p>
        </div>
        <a href="<?= $base ?>/feed" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <form method="POST" action="<?= $base ?>/feed/store" id="feedForm">

        <!-- ALL BATCHES TOGGLE -->
        <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="allBatchesToggle" name="all_batches" value="1">
                <label class="form-check-label fw-semibold" for="allBatchesToggle">
                    Feed All Batches as One Flock
                </label>
            </div>
            <div class="small text-muted">
                Enable to distribute feed across all <strong><?= count($activeBatches) ?></strong> active batches (<?= number_format($totalBirds) ?> birds)
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Farm <span class="text-danger">*</span></label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>"><?= htmlspecialchars($farm['farm_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6" id="singleBatchRow">
                <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                <select name="batch_id" id="batchSelect" class="form-select" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batches as $batch): ?>
                        <option value="<?= (int)$batch['id'] ?>">
                            <?= htmlspecialchars(($batch['batch_code'] ?? '') . (!empty($batch['batch_name']) ? ' — ' . $batch['batch_name'] : '')) ?>
                            (<?= number_format((int)($batch['current_quantity'] ?? 0)) ?> birds)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 d-none" id="allBatchesSummaryRow">
                <label class="form-label fw-semibold">Batches Covered</label>
                <div class="form-control bg-light" style="height:auto;min-height:38px;">
                    <?php if (!empty($activeBatches)): ?>
                        <?php foreach ($activeBatches as $b): ?>
                            <span class="badge bg-success me-1 mb-1">
                                <?= htmlspecialchars($b['batch_code'] ?? '') ?> (<?= number_format((int)($b['current_quantity'] ?? 0)) ?> birds)
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REAL-TIME SEARCH -->
            <div class="col-md-12">
                <label class="form-label fw-semibold">Search Feed Item <span class="text-danger">*</span></label>
                <input type="text" 
                       id="itemSearch" 
                       class="form-control" 
                       placeholder="Type to search inventory items in real-time..."
                       autocomplete="off"
                       required>
                <input type="hidden" name="inventory_item_id" id="selectedItemId" required>
                
                <!-- Real-time results -->
                <div id="searchResults" class="mt-2 border rounded" style="max-height:300px;overflow-y:auto;display:none;"></div>
                
                <!-- Selected item display -->
                <div id="selectedItem" class="mt-2 d-none">
                    <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="selectedItemName"></strong>
                            <div class="small">
                                Stock: <span id="selectedItemStock"></span> | 
                                Cost: <span id="selectedItemCost"></span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelection()">Change</button>
                    </div>
                </div>

                <div class="mt-2">
                    <a href="<?= $base ?>/inventory/items/create" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-plus-circle me-1"></i>Add New Item
                    </a>
                    <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-sm btn-outline-info" target="_blank">
                        <i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Record Date <span class="text-danger">*</span></label>
                <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" name="quantity_kg" id="quantityKg" class="form-control" required>
                <div id="qtyWarning" class="mt-1 small text-danger d-none"></div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Unit Cost (Auto)</label>
                <input type="number" step="0.01" min="0" name="unit_cost" id="unitCost" class="form-control bg-light" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Total Cost</label>
                <input type="text" id="totalCost" class="form-control bg-light" readonly placeholder="GHS 0.00">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-dark px-4">Save Feed Record</button>
            <a href="<?= $base ?>/feed" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function() {
    const searchInput = document.getElementById('itemSearch');
    const searchResults = document.getElementById('searchResults');
    const selectedItemDiv = document.getElementById('selectedItem');
    const selectedItemId = document.getElementById('selectedItemId');
    const selectedItemName = document.getElementById('selectedItemName');
    const selectedItemStock = document.getElementById('selectedItemStock');
    const selectedItemCost = document.getElementById('selectedItemCost');
    const quantityInput = document.getElementById('quantityKg');
    const unitCostInput = document.getElementById('unitCost');
    const totalCostInput = document.getElementById('totalCost');
    const qtyWarning = document.getElementById('qtyWarning');
    const toggle = document.getElementById('allBatchesToggle');
    const singleRow = document.getElementById('singleBatchRow');
    const allRow = document.getElementById('allBatchesSummaryRow');
    const batchSelect = document.getElementById('batchSelect');

    let currentStock = 0;
    let searchTimeout;

    // Toggle batch selection
    toggle.addEventListener('change', function() {
        const isAll = this.checked;
        singleRow.classList.toggle('d-none', isAll);
        allRow.classList.toggle('d-none', !isAll);
        batchSelect.required = !isAll;
    });

    // Real-time search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('<?= $base ?>/api/inventory/search?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(items => {
                    if (items.length === 0) {
                        searchResults.innerHTML = '<div class="p-3 text-muted">No items found</div>';
                    } else {
                        searchResults.innerHTML = items.map(item => `
                            <div class="p-3 border-bottom hover-bg-light cursor-pointer" 
                                 onclick="selectItem(${item.id}, '${item.item_name.replace(/'/g, "\\'")}', ${item.current_stock}, ${item.unit_cost})"
                                 style="cursor:pointer;">
                                <div class="fw-semibold">${item.item_name}</div>
                                <div class="small text-muted">
                                    Stock: ${parseFloat(item.current_stock).toFixed(2)} ${item.unit_of_measure} | 
                                    Cost: GHS ${parseFloat(item.unit_cost).toFixed(2)}
                                    ${item.current_stock <= 0 ? ' <span class="badge bg-danger">Out of Stock</span>' : ''}
                                </div>
                            </div>
                        `).join('');
                    }
                    searchResults.style.display = 'block';
                })
                .catch(err => {
                    searchResults.innerHTML = '<div class="p-3 text-danger">Error loading items</div>';
                    searchResults.style.display = 'block';
                });
        }, 300);
    });

    // Select item
    window.selectItem = function(id, name, stock, cost) {
        selectedItemId.value = id;
        selectedItemName.textContent = name;
        selectedItemStock.textContent = parseFloat(stock).toFixed(2) + ' kg';
        selectedItemCost.textContent = 'GHS ' + parseFloat(cost).toFixed(2);
        unitCostInput.value = parseFloat(cost).toFixed(2);
        currentStock = parseFloat(stock);

        searchInput.value = name;
        searchResults.style.display = 'none';
        selectedItemDiv.classList.remove('d-none');
        
        validateQuantity();
        calculateTotal();
    };

    // Clear selection
    window.clearSelection = function() {
        selectedItemId.value = '';
        searchInput.value = '';
        selectedItemDiv.classList.add('d-none');
        unitCostInput.value = '0.00';
        currentStock = 0;
        searchInput.focus();
    };

    // Validate quantity
    function validateQuantity() {
        const qty = parseFloat(quantityInput.value) || 0;
        if (qty > currentStock) {
            qtyWarning.textContent = `⚠ Quantity (${qty.toFixed(2)} kg) exceeds available stock (${currentStock.toFixed(2)} kg)`;
            qtyWarning.classList.remove('d-none');
        } else {
            qtyWarning.classList.add('d-none');
        }
    }

    // Calculate total
    function calculateTotal() {
        const qty = parseFloat(quantityInput.value) || 0;
        const cost = parseFloat(unitCostInput.value) || 0;
        totalCostInput.value = 'GHS ' + (qty * cost).toFixed(2);
    }

    quantityInput.addEventListener('input', () => {
        validateQuantity();
        calculateTotal();
    });

    // Form validation
    document.getElementById('feedForm').addEventListener('submit', function(e) {
        const qty = parseFloat(quantityInput.value) || 0;
        if (qty > currentStock) {
            e.preventDefault();
            qtyWarning.classList.remove('d-none');
            quantityInput.focus();
            alert('Insufficient stock! Please reduce quantity or receive more stock.');
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
})();
</script>

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa;
}
</style>

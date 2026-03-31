<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Edit Loss Record</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= rtrim(BASE_URL, '/') ?>/losses">Losses</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/update">
                        <input type="hidden" name="id" value="<?= $loss['id'] ?>">
                        <input type="hidden" name="farm_id" value="<?= $loss['farm_id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Loss Type *</label>
                            <select name="loss_type" class="form-control" required>
                                <option value="mortality" <?= $loss['loss_type'] === 'mortality' ? 'selected' : '' ?>>Mortality Loss</option>
                                <option value="inventory_writeoff" <?= $loss['loss_type'] === 'inventory_writeoff' ? 'selected' : '' ?>>Inventory Write-off</option>
                                <option value="bad_debt" <?= $loss['loss_type'] === 'bad_debt' ? 'selected' : '' ?>>Bad Debt</option>
                                <option value="asset_impairment" <?= $loss['loss_type'] === 'asset_impairment' ? 'selected' : '' ?>>Asset Impairment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Loss Date *</label>
                            <input type="date" name="loss_date" class="form-control" required value="<?= $loss['loss_date'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <input type="text" name="description" class="form-control" required value="<?= htmlspecialchars($loss['description']) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" step="0.01" value="<?= $loss['quantity'] ?? '' ?>" id="quantity">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit Cost</label>
                                <input type="number" name="unit_cost" class="form-control" step="0.01" value="<?= $loss['unit_cost'] ?? '' ?>" id="unitCost">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Loss Amount *</label>
                                <input type="number" name="total_loss_amount" class="form-control" step="0.01" required value="<?= $loss['total_loss_amount'] ?>" id="totalLoss">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($loss['reason'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($loss['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Loss</button>
                            <a href="<?= rtrim(BASE_URL, '/') ?>/losses" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Record Info</h5>
                    <p class="small mb-2"><strong>Created:</strong> <?= date('M d, Y g:i A', strtotime($loss['created_at'])) ?></p>
                    <?php if ($loss['reference_id']): ?>
                    <p class="small mb-0"><strong>Reference ID:</strong> <?= $loss['reference_id'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('quantity')?.addEventListener('input', calculateTotal);
document.getElementById('unitCost')?.addEventListener('input', calculateTotal);

function calculateTotal() {
    const qty = parseFloat(document.getElementById('quantity').value) || 0;
    const cost = parseFloat(document.getElementById('unitCost').value) || 0;
    const total = qty * cost;
    
    if (total > 0) {
        document.getElementById('totalLoss').value = total.toFixed(2);
    }
}
</script>

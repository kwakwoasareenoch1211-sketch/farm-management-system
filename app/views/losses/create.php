<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Record Loss / Write-off</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= rtrim(BASE_URL, '/') ?>/losses">Losses</a></li>
                    <li class="breadcrumb-item active">Record New</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/store">
                        <input type="hidden" name="farm_id" value="0">

                        <div class="mb-3">
                            <label class="form-label">Loss Type *</label>
                            <select name="loss_type" class="form-control" required id="lossType">
                                <option value="">Select Type</option>
                                <option value="mortality">Mortality Loss</option>
                                <option value="inventory_writeoff">Inventory Write-off</option>
                                <option value="bad_debt">Bad Debt</option>
                                <option value="asset_impairment">Asset Impairment</option>
                            </select>
                            <small class="text-muted">Select the type of loss</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Loss Date *</label>
                            <input type="date" name="loss_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <input type="text" name="description" class="form-control" required placeholder="Brief description of the loss">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" step="0.01" id="quantity" placeholder="0.00">
                                <small class="text-muted">Optional for countable items</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit Cost</label>
                                <input type="number" name="unit_cost" class="form-control" step="0.01" id="unitCost" placeholder="0.00">
                                <small class="text-muted">Cost per unit</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Loss Amount *</label>
                                <input type="number" name="total_loss_amount" class="form-control" step="0.01" required id="totalLoss" placeholder="0.00">
                                <small class="text-muted">Total loss value</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Cause or reason for the loss">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Record Loss</button>
                            <a href="<?= rtrim(BASE_URL, '/') ?>/losses" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5>Loss Types Guide</h5>
                    
                    <div class="mb-3">
                        <strong>Mortality Loss</strong>
                        <p class="small mb-0">Loss of livestock due to death. Reduces asset value.</p>
                    </div>

                    <div class="mb-3">
                        <strong>Inventory Write-off</strong>
                        <p class="small mb-0">Damaged, expired, or spoiled inventory items.</p>
                    </div>

                    <div class="mb-3">
                        <strong>Bad Debt</strong>
                        <p class="small mb-0">Uncollectible receivables from customers.</p>
                    </div>

                    <div class="mb-3">
                        <strong>Asset Impairment</strong>
                        <p class="small mb-0">Reduction in asset value due to damage or deterioration.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate total loss
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

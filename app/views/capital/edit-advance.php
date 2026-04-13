<?php
$base    = rtrim(BASE_URL, '/');
$advance = $advance ?? [];
$owners  = $owners  ?? [];
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="container py-4" style="max-width:680px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Owner Advance</h2>
            <p class="text-muted mb-0">Update the advance details or repayment amount.</p>
        </div>
        <a href="<?= $base ?>/capital/advances" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/capital/advances/update">
                <input type="hidden" name="id" value="<?= (int)$advance['id'] ?>">
                <div class="row g-3">

                    <!-- OWNER -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Owner <span class="text-danger">*</span></label>
                        <select name="owner_id" class="form-select" required>
                            <?php foreach ($owners as $i => $o): ?>
                                <option value="<?= (int)$o['id'] ?>" <?= (int)$advance['owner_id'] === (int)$o['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- DATE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Advance Date <span class="text-danger">*</span></label>
                        <input type="date" name="advance_date" class="form-control"
                               value="<?= htmlspecialchars($advance['advance_date'] ?? '') ?>" required>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control"
                               value="<?= htmlspecialchars($advance['description'] ?? '') ?>">
                    </div>

                    <!-- AMOUNT -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount Advanced (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control"
                               value="<?= number_format((float)($advance['amount'] ?? 0), 2, '.', '') ?>" required id="advAmount">
                    </div>

                    <!-- REPAID -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount Repaid (GHS)</label>
                        <input type="number" name="repaid_amount" step="0.01" min="0" class="form-control"
                               value="<?= number_format((float)($advance['repaid_amount'] ?? 0), 2, '.', '') ?>" id="advRepaid">
                        <div class="form-text">Outstanding: <strong id="advOutstanding">GHS <?= number_format((float)($advance['amount'] ?? 0) - (float)($advance['repaid_amount'] ?? 0), 2) ?></strong></div>
                    </div>

                    <!-- NOTES -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($advance['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark px-4">Save Changes</button>
                        <a href="<?= $base ?>/capital/advances" class="btn btn-outline-secondary">Cancel</a>
                        <a href="<?= $base ?>/capital/advances/delete?id=<?= (int)$advance['id'] ?>"
                           class="btn btn-outline-danger ms-auto"
                           onclick="return confirm('Delete this advance? This will remove the business liability to this owner.')">
                            Delete Advance
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const amt    = document.getElementById('advAmount');
const repaid = document.getElementById('advRepaid');
const out    = document.getElementById('advOutstanding');
function updateOut() {
    const o = Math.max(0, (parseFloat(amt.value)||0) - (parseFloat(repaid.value)||0));
    out.textContent = 'GHS ' + o.toFixed(2);
}
amt.addEventListener('input', updateOut);
repaid.addEventListener('input', updateOut);
</script>

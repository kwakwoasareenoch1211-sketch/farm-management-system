<?php
$base   = rtrim(BASE_URL, '/');
$farms  = $farms  ?? [];
$owners = $owners ?? [];
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<div class="container py-4" style="max-width:720px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add Capital Entry</h2>
            <p class="text-muted mb-0">Record a capital contribution or reinvestment into the business.</p>
        </div>
        <a href="<?= $base ?>/capital" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="alert alert-info small mb-4" style="border-radius:14px;">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Note:</strong> The business is one entity. This form records <em>who contributed</em> the capital — for transparency and record-keeping only. All business operations remain shared.
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/capital/store">
                <div class="row g-3">

                    <!-- CONTRIBUTOR (who put in this money) -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Contributor <span class="text-muted small">(who is putting in this capital?)</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($owners as $i => $owner):
                                $color = $ownerColors[$i % count($ownerColors)];
                            ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="owner_id" value="<?= (int)$owner['id'] ?>" class="d-none owner-radio">
                                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill owner-btn"
                                     style="border:2px solid #e2e8f0;background:#fff;transition:all .15s;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:28px;height:28px;background:<?= $color ?>;font-size:12px;">
                                        <?= strtoupper(substr($owner['full_name'], 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold small"><?= htmlspecialchars($owner['full_name']) ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                            <!-- Business/Joint option -->
                            <label style="cursor:pointer;">
                                <input type="radio" name="owner_id" value="" class="d-none owner-radio">
                                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill owner-btn"
                                     style="border:2px solid #e2e8f0;background:#fff;transition:all .15s;">
                                    <i class="bi bi-building" style="color:#64748b;"></i>
                                    <span class="fw-semibold small text-muted">Business (General)</span>
                                </div>
                            </label>
                        </div>
                        <div class="form-text">Select who is contributing this capital. Choose "Business" for retained earnings or grants.</div>
                    </div>

                    <!-- FARM -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm</label>
                        <select name="farm_id" class="form-select" required>
                            <option value="">Select Farm</option>
                            <?php foreach ($farms as $f): ?>
                                <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['farm_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ENTRY DATE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- TITLE -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Initial Investment, Q2 Reinvestment, Bank Loan" required>
                    </div>

                    <!-- CAPITAL TYPE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Capital Type <span class="text-danger">*</span></label>
                        <select name="capital_type" class="form-select" required>
                            <option value="owner_equity">Owner Equity — Initial capital to start the business</option>
                            <option value="reinvestment">Reinvestment — Profits put back into the business</option>
                            <option value="retained_earnings">Retained Earnings — Accumulated profits kept in business</option>
                            <option value="loan_capital">Loan Capital — Borrowed funds</option>
                            <option value="grant">Grant — External funding/grant</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- AMOUNT -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                    </div>

                    <!-- SOURCE NAME -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source Name</label>
                        <input type="text" name="source_name" class="form-control" placeholder="e.g. Personal savings, Bank of Ghana">
                    </div>

                    <!-- REFERENCE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="Receipt / transaction number">
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this capital entry"></textarea>
                    </div>

                    <!-- NOTES -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes"></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-dark px-5">Save Capital Entry</button>
                        <a href="<?= $base ?>/capital" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const radios = document.querySelectorAll('.owner-radio');
const colors = <?= json_encode($ownerColors) ?>;
radios.forEach((radio, idx) => {
    radio.addEventListener('change', () => {
        radios.forEach((r, i) => {
            const btn = r.nextElementSibling;
            const c = i < colors.length ? colors[i] : '#64748b';
            if (r.checked) {
                btn.style.borderColor = c;
                btn.style.background  = c + '15';
            } else {
                btn.style.borderColor = '#e2e8f0';
                btn.style.background  = '#fff';
            }
        });
    });
});
</script>

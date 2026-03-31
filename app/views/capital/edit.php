<?php $base = rtrim(BASE_URL, '/'); $r = $record ?? []; ?>
<div class="container py-4" style="max-width:680px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Edit Capital Entry</h2>
        <a href="<?= $base ?>/capital" class="btn btn-outline-secondary">Back</a>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/capital/update">
                <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm</label>
                        <select name="farm_id" class="form-select" required>
                            <?php foreach ($farms ?? [] as $f): ?>
                                <option value="<?= (int)$f['id'] ?>" <?= (int)($r['farm_id'] ?? 0) === (int)$f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['farm_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Entry Date</label>
                        <input type="date" name="entry_date" class="form-control" value="<?= htmlspecialchars($r['entry_date'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($r['title'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Capital Type</label>
                        <select name="capital_type" class="form-select" required>
                            <?php foreach (['owner_equity'=>'Owner Equity','retained_earnings'=>'Retained Earnings','loan_capital'=>'Loan Capital','grant'=>'Grant','other'=>'Other'] as $val=>$lbl): ?>
                                <option value="<?= $val ?>" <?= ($r['capital_type'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount (GHS)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" value="<?= htmlspecialchars($r['amount'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source Name</label>
                        <input type="text" name="source_name" class="form-control" value="<?= htmlspecialchars($r['source_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference No.</label>
                        <input type="text" name="reference_no" class="form-control" value="<?= htmlspecialchars($r['reference_no'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($r['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($r['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100">Update Capital Entry</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

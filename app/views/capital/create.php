<?php $base = rtrim(BASE_URL, '/'); ?>
<div class="container py-4" style="max-width:680px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Add Capital Entry</h2>
        <a href="<?= $base ?>/capital" class="btn btn-outline-secondary">Back</a>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="<?= $base ?>/capital/store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Farm</label>
                        <select name="farm_id" class="form-select" required>
                            <option value="">Select Farm</option>
                            <?php foreach ($farms ?? [] as $f): ?>
                                <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['farm_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Entry Date</label>
                        <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Initial Owner Investment" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Capital Type</label>
                        <select name="capital_type" class="form-select" required>
                            <option value="owner_equity">Owner Equity</option>
                            <option value="retained_earnings">Retained Earnings</option>
                            <option value="loan_capital">Loan Capital</option>
                            <option value="grant">Grant</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount (GHS)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source Name</label>
                        <input type="text" name="source_name" class="form-control" placeholder="e.g. John Doe, Bank of Ghana">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100">Save Capital Entry</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

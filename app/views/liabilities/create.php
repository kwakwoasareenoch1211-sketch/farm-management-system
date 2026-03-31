<?php $base = rtrim(BASE_URL, '/'); ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Management</span>
        <h2 class="fw-bold mb-1">Add New Liability</h2>
        <p class="text-muted mb-0">Record a new loan, mortgage, credit, or other financial obligation.</p>
    </div>

    <div>
        <a href="<?= $base ?>/liabilities" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Liabilities
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= $base ?>/liabilities/store">
            <div class="row g-3">
                <!-- Liability Name -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Liability Name <span class="text-danger">*</span></label>
                    <input type="text" name="liability_name" class="form-control" required placeholder="e.g., Bank Loan for Equipment">
                </div>

                <!-- Liability Type -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Liability Type <span class="text-danger">*</span></label>
                    <select name="liability_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="loan">Loan</option>
                        <option value="mortgage">Mortgage</option>
                        <option value="credit">Credit</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Principal Amount -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Principal Amount (GHS) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="principal_amount" class="form-control" required placeholder="0.00">
                    <div class="form-text">Original amount borrowed</div>
                </div>

                <!-- Outstanding Balance -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Outstanding Balance (GHS)</label>
                    <input type="number" step="0.01" name="outstanding_balance" class="form-control" placeholder="Leave empty to use principal amount">
                    <div class="form-text">Current amount owed (defaults to principal)</div>
                </div>

                <!-- Interest Rate -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Interest Rate (%)</label>
                    <input type="number" step="0.01" name="interest_rate" class="form-control" placeholder="0.00">
                </div>

                <!-- Start Date -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Due Date -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Active</option>
                        <option value="paid">Paid</option>
                        <option value="defaulted">Defaulted</option>
                    </select>
                </div>

                <!-- Farm ID -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Farm</label>
                    <select name="farm_id" class="form-select">
                        <option value="0">Select Farm (Optional)</option>
                        <?php foreach ($farms as $farm): ?>
                            <option value="<?= (int)$farm['id'] ?>"><?= htmlspecialchars($farm['farm_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notes -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional details about this liability..."></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="col-12">
                    <hr class="my-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Create Liability
                        </button>
                        <a href="<?= $base ?>/liabilities" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

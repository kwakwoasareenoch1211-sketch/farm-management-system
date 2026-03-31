<?php
$base = rtrim(BASE_URL, '/');
$totalPaid = (float)($record['total_paid'] ?? 0);
$calculatedBalance = (float)($record['calculated_balance'] ?? 0);
$principalAmount = (float)($record['principal_amount'] ?? 0);
$paymentProgress = $principalAmount > 0 ? ($totalPaid / $principalAmount) * 100 : 0;
?>

<style>
    .detail-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
        padding: 24px;
        margin-bottom: 24px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #64748b;
        font-weight: 600;
        font-size: 14px;
    }

    .detail-value {
        color: #1e293b;
        font-weight: 600;
        font-size: 14px;
    }

    .progress-bar-custom {
        height: 30px;
        border-radius: 15px;
        background: #f1f5f9;
        overflow: hidden;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Management</span>
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($record['liability_name']) ?></h2>
        <p class="text-muted mb-0">Liability details and payment history.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= $base ?>/liabilities/edit?id=<?= (int)$record['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="<?= $base ?>/liabilities" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<!-- Payment Progress -->
<div class="detail-card">
    <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-success me-2"></i>Payment Progress</h5>
    
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="small text-muted">Principal Amount</div>
            <div class="fs-5 fw-bold">GHS <?= number_format($principalAmount, 2) ?></div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Total Paid</div>
            <div class="fs-5 fw-bold text-success">GHS <?= number_format($totalPaid, 2) ?></div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Outstanding Balance</div>
            <div class="fs-5 fw-bold text-danger">GHS <?= number_format($calculatedBalance, 2) ?></div>
        </div>
        <div class="col-md-3">
            <div class="small text-muted">Progress</div>
            <div class="fs-5 fw-bold text-primary"><?= number_format($paymentProgress, 1) ?>%</div>
        </div>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill" style="width: <?= min(100, $paymentProgress) ?>%">
            <?= number_format($paymentProgress, 1) ?>% Paid
        </div>
    </div>
</div>

<!-- Liability Details -->
<div class="detail-card">
    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Liability Details</h5>
    
    <div class="detail-row">
        <span class="detail-label">Liability Type</span>
        <span class="detail-value"><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($record['liability_type'])) ?></span></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Status</span>
        <span class="detail-value">
            <?php
            $statusClass = 'warning';
            if ($record['status'] === 'paid') $statusClass = 'success';
            elseif ($record['status'] === 'defaulted') $statusClass = 'danger';
            ?>
            <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($record['status'])) ?></span>
        </span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Interest Rate</span>
        <span class="detail-value"><?= !empty($record['interest_rate']) ? number_format((float)$record['interest_rate'], 2) . '%' : 'N/A' ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Start Date</span>
        <span class="detail-value"><?= htmlspecialchars($record['start_date']) ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Due Date</span>
        <span class="detail-value"><?= !empty($record['due_date']) ? htmlspecialchars($record['due_date']) : 'N/A' ?></span>
    </div>

    <?php if (!empty($record['notes'])): ?>
        <div class="detail-row">
            <span class="detail-label">Notes</span>
            <span class="detail-value"><?= htmlspecialchars($record['notes']) ?></span>
        </div>
    <?php endif; ?>
</div>

<!-- Add Payment Form -->
<?php if ($record['status'] === 'active' && $calculatedBalance > 0): ?>
    <div class="detail-card">
        <h5 class="fw-bold mb-3"><i class="bi bi-cash-coin text-success me-2"></i>Record Payment</h5>
        
        <form method="POST" action="<?= $base ?>/liabilities/addPayment">
            <input type="hidden" name="liability_id" value="<?= (int)$record['id'] ?>">
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Amount Paid (GHS) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount_paid" class="form-control" required placeholder="0.00" max="<?= $calculatedBalance ?>">
                    <div class="form-text">Max: GHS <?= number_format($calculatedBalance, 2) ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Payment reference...">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Record Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Payment History -->
<div class="detail-card">
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-info me-2"></i>Payment History</h5>
    
    <?php if (!empty($payments)): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Amount Paid</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                            <td class="fw-bold text-success">GHS <?= number_format((float)$payment['amount_paid'], 2) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($payment['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td class="fw-bold">Total Paid</td>
                        <td class="fw-bold text-success">GHS <?= number_format($totalPaid, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No payments recorded yet.
        </div>
    <?php endif; ?>
</div>

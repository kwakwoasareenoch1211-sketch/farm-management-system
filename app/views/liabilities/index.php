<?php
$totalLiabilities = (int)($totals['total_liabilities'] ?? 0);
$activeLiabilities = (int)($totals['active_liabilities'] ?? 0);
$paidLiabilities = (int)($totals['paid_liabilities'] ?? 0);
$totalPrincipal = (float)($totals['total_principal'] ?? 0);
$totalOutstanding = (float)($totals['total_outstanding'] ?? 0);
$activeOutstanding = (float)($totals['active_outstanding'] ?? 0);
?>

<style>
    .liability-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .liability-stat {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #eef2f7;
        height: 100%;
    }

    .liability-stat .label {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .liability-stat .value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .liability-stat .meta {
        font-size: 12px;
        color: #94a3b8;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active { background: #fef3c7; color: #92400e; }
    .status-paid { background: #d1fae5; color: #065f46; }
    .status-defaulted { background: #fee2e2; color: #991b1b; }

    .alert-box {
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        border: 2px solid;
    }

    .alert-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .alert-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Management</span>
        <h2 class="fw-bold mb-1">Liabilities Management</h2>
        <p class="text-muted mb-0">Track loans, mortgages, credits, and other financial obligations.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= rtrim(BASE_URL, '/') ?>/liabilities/create" class="btn btn-dark">
            <i class="bi bi-plus-circle me-1"></i> Add Liability
        </a>
    </div>
</div>

<!-- Alerts for Overdue and Upcoming -->
<?php if (!empty($overdue)): ?>
    <div class="alert-box alert-danger">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            <div>
                <div class="fw-bold">⚠️ Overdue Liabilities</div>
                <div class="small">You have <?= count($overdue) ?> overdue liability(ies) that require immediate attention.</div>
            </div>
        </div>
        <div class="mt-2">
            <?php foreach ($overdue as $od): ?>
                <div class="small">
                    • <strong><?= htmlspecialchars($od['liability_name']) ?></strong> - Due: <?= htmlspecialchars($od['due_date']) ?> - Outstanding: GHS <?= number_format((float)$od['outstanding_balance'], 2) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($upcomingDue)): ?>
    <div class="alert-box alert-warning">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-fill fs-4"></i>
            <div>
                <div class="fw-bold">📅 Upcoming Due Dates</div>
                <div class="small"><?= count($upcomingDue) ?> liability(ies) due within the next 30 days.</div>
            </div>
        </div>
        <div class="mt-2">
            <?php foreach ($upcomingDue as $ud): ?>
                <div class="small">
                    • <strong><?= htmlspecialchars($ud['liability_name']) ?></strong> - Due: <?= htmlspecialchars($ud['due_date']) ?> - Outstanding: GHS <?= number_format((float)$ud['outstanding_balance'], 2) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Summary Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Total Liabilities</div>
            <div class="value"><?= number_format($totalLiabilities) ?></div>
            <div class="meta">All records</div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Active</div>
            <div class="value text-warning"><?= number_format($activeLiabilities) ?></div>
            <div class="meta">Ongoing obligations</div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Paid Off</div>
            <div class="value text-success"><?= number_format($paidLiabilities) ?></div>
            <div class="meta">Completed</div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Total Principal</div>
            <div class="value">GHS <?= number_format($totalPrincipal, 2) ?></div>
            <div class="meta">Original amount</div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Total Outstanding</div>
            <div class="value text-danger">GHS <?= number_format($totalOutstanding, 2) ?></div>
            <div class="meta">All liabilities</div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="liability-stat">
            <div class="label">Active Outstanding</div>
            <div class="value text-warning">GHS <?= number_format($activeOutstanding, 2) ?></div>
            <div class="meta">Current obligations</div>
        </div>
    </div>
</div>

<!-- Unpaid Expenses Section -->
<?php if (!empty($unpaidExpenses)): ?>
<div class="liability-card p-4 mb-4">
    <h5 class="fw-bold mb-3">
        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
        Unpaid Expenses (<?= count($unpaidExpenses) ?>)
    </h5>
    <p class="text-muted mb-3">These expenses are marked as unpaid or partially paid and have been automatically added to liabilities.</p>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Liability</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unpaidExpenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['expense_date']) ?></td>
                        <td><?= htmlspecialchars($expense['description'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($expense['category_name']) ?></td>
                        <td>GHS <?= number_format((float)$expense['amount'], 2) ?></td>
                        <td>GHS <?= number_format((float)$expense['amount_paid'], 2) ?></td>
                        <td class="text-danger fw-bold">GHS <?= number_format((float)$expense['outstanding_amount'], 2) ?></td>
                        <td>
                            <?php if ($expense['payment_status'] === 'unpaid'): ?>
                                <span class="status-badge status-defaulted">Unpaid</span>
                            <?php else: ?>
                                <span class="status-badge status-active">Partial</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($expense['liability_id']): ?>
                                <a href="<?= rtrim(BASE_URL, '/') ?>/liabilities/view?id=<?= (int)$expense['liability_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    View Liability
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Not linked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Liabilities Table -->
<div class="liability-card p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-list-ul text-primary me-2"></i>All Liabilities</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Created</th>
                    <th>Liability Name</th>
                    <th>Source</th>
                    <th>Type</th>
                    <th>Principal</th>
                    <th>Paid</th>
                    <th>Outstanding</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $r): ?>
                        <?php
                        $statusClass = 'status-active';
                        if ($r['status'] === 'paid') $statusClass = 'status-paid';
                        elseif ($r['status'] === 'defaulted') $statusClass = 'status-defaulted';
                        
                        $principalAmount = (float)($r['principal_amount'] ?? 0);
                        $totalPaid = (float)($r['total_paid'] ?? 0);
                        $outstandingBalance = (float)($r['calculated_balance'] ?? 0);
                        $sourceType = $r['source_type'] ?? 'manual';
                        $sourceDesc = $r['source_description'] ?? '';
                        $createdAt = $r['created_at'] ?? date('Y-m-d H:i:s');
                        ?>
                        <tr>
                            <td>
                                <small class="text-muted">
                                    <?= date('M d, Y', strtotime($createdAt)) ?><br>
                                    <span style="font-size: 0.75rem;"><?= date('h:i A', strtotime($createdAt)) ?></span>
                                </small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['liability_name']) ?></strong>
                                <?php if ($sourceDesc): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($sourceDesc, 0, 40)) ?>...</small>
                                <?php elseif (!empty($r['notes'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars(substr($r['notes'], 0, 40)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sourceType === 'expense'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-receipt"></i> Expense</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-pencil"></i> Manual</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $r['liability_type']))) ?></span></td>
                            <td class="fw-bold">GHS <?= number_format($principalAmount, 2) ?></td>
                            <td class="text-success">GHS <?= number_format($totalPaid, 2) ?></td>
                            <td class="fw-bold <?= $outstandingBalance > 0 ? 'text-danger' : 'text-success' ?>">
                                GHS <?= number_format($outstandingBalance, 2) ?>
                            </td>
                            <td><?= !empty($r['due_date']) ? date('M d, Y', strtotime($r['due_date'])) : '<span class="text-muted">N/A</span>' ?></td>
                            <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/liabilities/view?id=<?= (int)$r['id'] ?>">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= rtrim(BASE_URL, '/') ?>/liabilities/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/liabilities/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this liability record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            No liability records yet. Create an unpaid expense or click "Add Liability" to create one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

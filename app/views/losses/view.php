<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Loss Record Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= rtrim(BASE_URL, '/') ?>/losses">Losses</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Loss Information</h5>
                    <div>
                        <a href="<?= rtrim(BASE_URL, '/') ?>/losses/edit?id=<?= $loss['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/delete?id=<?= $loss['id'] ?>" style="display:inline;" onsubmit="return confirm('Delete this loss record?');">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Loss Type</label>
                            <p class="mb-0">
                                <span class="badge bg-<?= 
                                    $loss['loss_type'] === 'mortality' ? 'danger' : 
                                    ($loss['loss_type'] === 'inventory_writeoff' ? 'warning' : 
                                    ($loss['loss_type'] === 'bad_debt' ? 'info' : 'secondary')) 
                                ?>">
                                    <?= str_replace('_', ' ', ucfirst($loss['loss_type'])) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Loss Date</label>
                            <p class="mb-0"><?= date('F d, Y', strtotime($loss['loss_date'])) ?></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0"><?= htmlspecialchars($loss['description']) ?></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Quantity</label>
                            <p class="mb-0"><?= $loss['quantity'] ? number_format($loss['quantity'], 2) : 'N/A' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Unit Cost</label>
                            <p class="mb-0"><?= $loss['unit_cost'] ? '$' . number_format($loss['unit_cost'], 2) : 'N/A' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Total Loss Amount</label>
                            <h4 class="mb-0 text-danger">$<?= number_format($loss['total_loss_amount'], 2) ?></h4>
                        </div>
                    </div>

                    <?php if ($loss['reason']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Reason</label>
                        <p class="mb-0"><?= htmlspecialchars($loss['reason']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($loss['notes']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Notes</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($loss['notes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($loss['reference_id']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Reference ID</label>
                        <p class="mb-0"><?= $loss['reference_id'] ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="mb-0">
                        <label class="text-muted small">Created At</label>
                        <p class="mb-0"><?= date('F d, Y g:i A', strtotime($loss['created_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5>Accounting Impact</h5>
                    
                    <?php if ($loss['loss_type'] === 'mortality'): ?>
                        <p class="small"><strong>Type:</strong> Operating Loss</p>
                        <p class="small"><strong>Impact:</strong> Reduces livestock asset value</p>
                        <p class="small mb-0"><strong>Statement:</strong> Income Statement (COGS)</p>
                    <?php elseif ($loss['loss_type'] === 'inventory_writeoff'): ?>
                        <p class="small"><strong>Type:</strong> Inventory Adjustment</p>
                        <p class="small"><strong>Impact:</strong> Reduces inventory value</p>
                        <p class="small mb-0"><strong>Statement:</strong> Balance Sheet & Income Statement</p>
                    <?php elseif ($loss['loss_type'] === 'bad_debt'): ?>
                        <p class="small"><strong>Type:</strong> Bad Debt Expense</p>
                        <p class="small"><strong>Impact:</strong> Reduces accounts receivable</p>
                        <p class="small mb-0"><strong>Statement:</strong> Income Statement</p>
                    <?php else: ?>
                        <p class="small"><strong>Type:</strong> Impairment Loss</p>
                        <p class="small"><strong>Impact:</strong> Reduces asset book value</p>
                        <p class="small mb-0"><strong>Statement:</strong> Balance Sheet & Income Statement</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5>Quick Actions</h5>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/losses" class="btn btn-sm btn-secondary w-100 mb-2">Back to Losses</a>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/losses/create" class="btn btn-sm btn-primary w-100">Record New Loss</a>
                </div>
            </div>
        </div>
    </div>
</div>

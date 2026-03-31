<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Losses & Write-offs</h2>
            <p class="text-muted">Track all business losses following proper accounting principles</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Losses (All)</h6>
                    <h3 class="mb-0 text-danger">$<?= number_format($totals['total_losses'], 2) ?></h3>
                    <small class="text-muted"><?= $totals['total_count'] ?> total records</small>
                    <hr class="my-2">
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Recorded:</span>
                            <strong>$<?= number_format($totals['recorded_losses'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between text-warning">
                            <span>Unrecorded:</span>
                            <strong>$<?= number_format($totals['unrecorded_losses'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">This Month</h6>
                    <h3 class="mb-0">$<?= number_format($totals['current_month'], 2) ?></h3>
                    <small class="text-muted">Recorded</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Today</h6>
                    <h3 class="mb-0">$<?= number_format($totals['today'], 2) ?></h3>
                    <small class="text-muted">Recorded</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending Action</h6>
                    <h3 class="mb-0 text-warning">$<?= number_format($totals['unrecorded_losses'], 2) ?></h3>
                    <small class="text-muted"><?= $totals['unrecorded_count'] ?> unrecorded</small>
                    <?php if ($totals['unrecorded_count'] > 0): ?>
                    <div class="mt-2">
                        <small class="text-danger">⚠ Requires recording</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <a href="<?= rtrim(BASE_URL, '/') ?>/mortality/create" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Add Mortality
                    </a>
                    <small class="text-muted d-block"><?= $totals['recorded_count'] ?> recorded</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Loss by Type -->
    <?php if (!empty($byType)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Losses by Type</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($byType as $type): ?>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-capitalize"><?= str_replace('_', ' ', $type['loss_type']) ?></h6>
                                <h4>$<?= number_format($type['total_amount'], 2) ?></h4>
                                <small class="text-muted"><?= $type['count'] ?> records</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Unrecorded Mortality Losses -->
    <?php if (!empty($unrecordedMortality)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Unrecorded Mortality Losses (<?= count($unrecordedMortality) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">These mortality records have not been recorded as losses yet. The cost includes purchase price + accumulated feed + medication + vaccination costs per bird:</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch</th>
                                    <th>Qty</th>
                                    <th>Purchase</th>
                                    <th>Feed</th>
                                    <th>Med</th>
                                    <th>Vacc</th>
                                    <th>Total/Bird</th>
                                    <th>Total Loss</th>
                                    <th>Cause</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unrecordedMortality as $m): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($m['record_date'])) ?></td>
                                    <td><?= htmlspecialchars($m['batch_name']) ?></td>
                                    <td><?= $m['quantity'] ?></td>
                                    <td class="small">$<?= number_format($m['purchase_cost_per_bird'], 2) ?></td>
                                    <td class="small">$<?= number_format($m['feed_cost_per_bird'], 2) ?></td>
                                    <td class="small">$<?= number_format($m['medication_cost_per_bird'], 2) ?></td>
                                    <td class="small">$<?= number_format($m['vaccination_cost_per_bird'], 2) ?></td>
                                    <td><strong>$<?= number_format($m['total_cost_per_bird'], 2) ?></strong></td>
                                    <td><strong class="text-danger">$<?= number_format($m['estimated_loss'], 2) ?></strong></td>
                                    <td><?= htmlspecialchars($m['cause'] ?? 'Unknown') ?></td>
                                    <td>
                                        <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/recordMortality" style="display:inline;">
                                            <input type="hidden" name="mortality_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Record Loss</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Loss Impact Analysis by Batch -->
    <?php if (!empty($impactAnalysis)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Loss Impact Analysis by Batch</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Initial Qty</th>
                                    <th>Current Qty</th>
                                    <th>Birds Lost</th>
                                    <th>Mortality Rate</th>
                                    <th>Total Loss Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($impactAnalysis as $impact): ?>
                                <tr>
                                    <td><?= htmlspecialchars($impact['batch_name'] ?? $impact['batch_code']) ?></td>
                                    <td><?= number_format($impact['initial_quantity']) ?></td>
                                    <td><?= number_format($impact['current_quantity']) ?></td>
                                    <td><?= number_format($impact['total_birds_lost']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $impact['mortality_rate'] > 10 ? 'danger' : ($impact['mortality_rate'] > 5 ? 'warning' : 'success') ?>">
                                            <?= number_format($impact['mortality_rate'], 2) ?>%
                                        </span>
                                    </td>
                                    <td><strong class="text-danger">$<?= number_format($impact['total_loss_value'], 2) ?></strong></td>
                                    <td>
                                        <?php if ($impact['mortality_rate'] > 10): ?>
                                            <span class="badge bg-danger">High Risk</span>
                                        <?php elseif ($impact['mortality_rate'] > 5): ?>
                                            <span class="badge bg-warning">Monitor</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Losses -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Losses</h5>
                    <div>
                        <input type="text" id="searchLoss" class="form-control form-control-sm" placeholder="Search...">
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($losses)): ?>
                        <p class="text-center text-muted py-4">No losses recorded yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="lossesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Total Loss</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($losses as $loss): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($loss['loss_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $loss['loss_type'] === 'mortality' ? 'danger' : 
                                                ($loss['loss_type'] === 'inventory_writeoff' ? 'warning' : 
                                                ($loss['loss_type'] === 'bad_debt' ? 'info' : 'secondary')) 
                                            ?>">
                                                <?= str_replace('_', ' ', ucfirst($loss['loss_type'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($loss['description']) ?></td>
                                        <td><?= $loss['quantity'] ? number_format($loss['quantity'], 2) : '-' ?></td>
                                        <td><?= $loss['unit_cost'] ? '$' . number_format($loss['unit_cost'], 2) : '-' ?></td>
                                        <td><strong>$<?= number_format($loss['total_loss_amount'], 2) ?></strong></td>
                                        <td><?= htmlspecialchars($loss['reason'] ?? '-') ?></td>
                                        <td>
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/losses/view?id=<?= $loss['id'] ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="<?= rtrim(BASE_URL, '/') ?>/losses/edit?id=<?= $loss['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/delete?id=<?= $loss['id'] ?>" style="display:inline;" onsubmit="return confirm('Delete this loss record?');">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchLoss')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#lossesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

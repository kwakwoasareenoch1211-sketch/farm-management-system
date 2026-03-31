<?php
$totalRecords = (float)($totals['total_records'] ?? 0);
$totalAmount = (float)($totals['total_amount'] ?? 0);
$currentMonthAmount = (float)($totals['current_month_amount'] ?? 0);
$todayAmount = (float)($totals['today_amount'] ?? 0);
$bySource = $totals['by_source'] ?? [];

// Get filter from URL
$filterSource = $_GET['source'] ?? 'all';
?>

<style>
    .finance-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .finance-stat {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #eef2f7;
        height: 100%;
    }

    .finance-stat .label {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .finance-stat .value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .finance-stat .meta {
        font-size: 12px;
        color: #94a3b8;
    }

    .action-btns {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .source-breakdown {
        border-radius: 16px;
        padding: 16px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .source-breakdown:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    
    .source-breakdown.active {
        border-color: #3b82f6;
        background: #eff6ff;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Work Area</span>
        <h2 class="fw-bold mb-1">Comprehensive Expenses</h2>
        <p class="text-muted mb-0">Track all farm expenses from all sources with detailed breakdown and filtering.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= rtrim(BASE_URL, '/') ?>/expenses/create" class="btn btn-dark">
            <i class="bi bi-plus-circle me-1"></i> Add Manual Expense
        </a>
    </div>
</div>

<!-- Expense Breakdown by Source -->
<div class="finance-card p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Expense Breakdown by Source</h5>
    <div class="row g-3">
        <div class="col-md-12">
            <a href="?source=all" class="text-decoration-none">
                <div class="source-breakdown <?= $filterSource === 'all' ? 'active' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">All Sources</div>
                            <div class="small text-muted"><?= number_format($totalRecords) ?> records</div>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold text-dark">GHS <?= number_format($totalAmount, 2) ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <?php
        $sourceConfig = [
            'manual' => ['label' => 'Manual Expenses', 'color' => '#0d6efd', 'icon' => 'bi-pencil-square'],
            'livestock_purchase' => ['label' => 'Livestock Purchase Cost', 'color' => '#6f42c1', 'icon' => 'bi-egg'],
            'mortality_loss' => ['label' => 'Mortality Loss', 'color' => '#d63384', 'icon' => 'bi-heartbreak'],
            'feed' => ['label' => 'Feed Costs', 'color' => '#ffc107', 'icon' => 'bi-basket'],
            'medication' => ['label' => 'Medication Costs', 'color' => '#dc3545', 'icon' => 'bi-capsule'],
            'vaccination' => ['label' => 'Vaccination Costs', 'color' => '#198754', 'icon' => 'bi-shield-check'],
        ];
        
        foreach ($sourceConfig as $source => $config):
            $data = $bySource[$source] ?? ['count' => 0, 'total' => 0, 'current_month' => 0];
            if ($data['count'] == 0) continue;
        ?>
            <div class="col-md-6">
                <a href="?source=<?= $source ?>" class="text-decoration-none">
                    <div class="source-breakdown <?= $filterSource === $source ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi <?= $config['icon'] ?>" style="color:<?= $config['color'] ?>;font-size:1.2rem;"></i>
                                    <div class="fw-bold text-dark"><?= $config['label'] ?></div>
                                </div>
                                <div class="small text-muted"><?= number_format($data['count']) ?> records</div>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-dark">GHS <?= number_format($data['total'], 2) ?></div>
                                <div class="small text-muted">This month: GHS <?= number_format($data['current_month'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="finance-stat">
            <div class="label">Total Records</div>
            <div class="value"><?= number_format($totalRecords) ?></div>
            <div class="meta">All expense entries</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="finance-stat">
            <div class="label">Total Expenses</div>
            <div class="value">GHS <?= number_format($totalAmount, 2) ?></div>
            <div class="meta">Lifetime expense value</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="finance-stat">
            <div class="label">This Month</div>
            <div class="value">GHS <?= number_format($currentMonthAmount, 2) ?></div>
            <div class="meta">Current month total</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="finance-stat">
            <div class="label">Today</div>
            <div class="value">GHS <?= number_format($todayAmount, 2) ?></div>
            <div class="meta">Today's total</div>
        </div>
    </div>
</div>

<!-- Quick Expense Actions -->
<div class="finance-card p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Expense Actions</h5>
    <div class="row g-3">
        <?php
        $expActions = [
            ['icon'=>'bi-plus-circle','color'=>'#3b82f6','label'=>'Add Expense','desc'=>'Record new expense','url'=>rtrim(BASE_URL, '/').'/expenses/create'],
            ['icon'=>'bi-egg','color'=>'#6f42c1','label'=>'Livestock Purchase','desc'=>'View chick purchases','url'=>rtrim(BASE_URL, '/').'/batches'],
            ['icon'=>'bi-heartbreak','color'=>'#d63384','label'=>'Mortality Loss','desc'=>'View mortality records','url'=>rtrim(BASE_URL, '/').'/mortality'],
            ['icon'=>'bi-basket','color'=>'#f59e0b','label'=>'Feed Expenses','desc'=>'View feed costs','url'=>rtrim(BASE_URL, '/').'/feed'],
            ['icon'=>'bi-capsule','color'=>'#ef4444','label'=>'Medication','desc'=>'View medication costs','url'=>rtrim(BASE_URL, '/').'/medication'],
            ['icon'=>'bi-shield-check','color'=>'#22c55e','label'=>'Vaccination','desc'=>'View vaccination costs','url'=>rtrim(BASE_URL, '/').'/vaccination'],
            ['icon'=>'bi-file-earmark-bar-graph','color'=>'#8b5cf6','label'=>'Expense Report','desc'=>'Detailed expense analysis','url'=>rtrim(BASE_URL, '/').'/reports/expenses'],
            ['icon'=>'bi-graph-up','color'=>'#10b981','label'=>'Financial Dashboard','desc'=>'Overall financial view','url'=>rtrim(BASE_URL, '/').'/financial'],
        ];
        foreach ($expActions as $a):
        ?>
            <div class="col-6 col-md-3">
                <a href="<?= htmlspecialchars($a['url']) ?>" class="action-tile" style="border-radius:16px;padding:20px;border:2px solid #e2e8f0;background:#fff;text-align:center;text-decoration:none;color:#1e293b;display:block;transition:all .2s;">
                    <span style="color:<?= $a['color'] ?>;font-size:2.5rem;display:block;margin-bottom:10px;"><i class="bi <?= $a['icon'] ?>"></i></span>
                    <div class="fw-semibold small mt-2"><?= htmlspecialchars($a['label']) ?></div>
                    <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($a['desc']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="finance-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">
                <?php if ($filterSource === 'all'): ?>
                    All Expense Records
                <?php else: ?>
                    <?= $sourceConfig[$filterSource]['label'] ?? 'Filtered Expenses' ?>
                    <a href="?" class="btn btn-sm btn-outline-secondary ms-2">Clear Filter</a>
                <?php endif; ?>
            </h5>
            <p class="text-muted small mb-0">
                <?php if ($filterSource === 'all'): ?>
                    All expenses from all sources. Click a source above to filter.
                <?php else: ?>
                    Showing only <?= strtolower($sourceConfig[$filterSource]['label'] ?? 'filtered') ?>.
                <?php endif; ?>
            </p>
        </div>
        <span class="badge text-bg-primary px-3 py-2 rounded-pill">
            <?= $filterSource === 'all' ? 'All Sources' : ($sourceConfig[$filterSource]['label'] ?? 'Filtered') ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Source</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Filter records if needed
                $filteredRecords = $records;
                if ($filterSource !== 'all') {
                    $filteredRecords = array_filter($records, fn($r) => ($r['expense_source'] ?? 'manual') === $filterSource);
                }
                
                if (!empty($filteredRecords)): ?>
                    <?php foreach ($filteredRecords as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['date']) ?></td>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td>
                                <?php
                                $source = $r['expense_source'] ?? 'manual';
                                $badgeColor = 'secondary';
                                $badgeLabel = ucfirst($source);
                                if ($source === 'manual') { $badgeColor = 'primary'; $badgeLabel = 'Manual'; }
                                elseif ($source === 'livestock_purchase') { $badgeColor = 'dark'; $badgeLabel = 'Livestock'; }
                                elseif ($source === 'mortality_loss') { $badgeColor = 'danger'; $badgeLabel = 'Mortality'; }
                                elseif ($source === 'feed') { $badgeColor = 'warning'; $badgeLabel = 'Feed'; }
                                elseif ($source === 'medication') { $badgeColor = 'danger'; $badgeLabel = 'Medication'; }
                                elseif ($source === 'vaccination') { $badgeColor = 'success'; $badgeLabel = 'Vaccination'; }
                                ?>
                                <span class="badge text-bg-<?= $badgeColor ?>"><?= $badgeLabel ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="fw-bold">GHS <?= number_format((float)$r['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($r['payment_method'] ?? 'cash') ?></td>
                            <td class="small text-muted"><?= htmlspecialchars(substr($r['notes'] ?? '-', 0, 50)) ?></td>
                            <td>
                                <?php if (($r['expense_source'] ?? 'manual') === 'manual'): ?>
                                    <div class="action-btns">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/expenses/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                        <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/expenses/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this expense record?')">Delete</a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Auto-tracked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <?php if ($filterSource === 'all'): ?>
                                No expense records yet.
                            <?php else: ?>
                                No <?= strtolower($sourceConfig[$filterSource]['label'] ?? 'expenses') ?> found.
                                <a href="?" class="d-block mt-2">View all expenses</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

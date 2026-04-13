<?php
$totalRecords       = (float)($totals['total_records'] ?? 0);
$totalAmount        = (float)($totals['total_amount'] ?? 0);
$currentMonthAmount = (float)($totals['current_month_amount'] ?? 0);
$todayAmount        = (float)($totals['today_amount'] ?? 0);
$bySource           = $totals['by_source'] ?? [];
$filterSource       = $_GET['source'] ?? 'all';
$base               = rtrim(BASE_URL, '/');

$sourceConfig = [
    'manual'             => ['label'=>'Manual Expenses',    'color'=>'#0d6efd','icon'=>'bi-pencil-square'],
    'livestock_purchase' => ['label'=>'Livestock Purchase', 'color'=>'#6f42c1','icon'=>'bi-egg-fill'],
    'feed'               => ['label'=>'Feed Costs',         'color'=>'#ffc107','icon'=>'bi-basket'],
    'medication'         => ['label'=>'Medication Costs',   'color'=>'#dc3545','icon'=>'bi-capsule'],
    'vaccination'        => ['label'=>'Vaccination Costs',  'color'=>'#198754','icon'=>'bi-shield-check'],
    'mortality_loss'     => ['label'=>'Mortality Loss',     'color'=>'#d63384','icon'=>'bi-heartbreak'],
];
?>
<style>
.finance-card{border:0;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.08);background:#fff;}
.finance-stat{border-radius:18px;padding:18px;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%);border:1px solid #eef2f7;height:100%;}
.finance-stat .label{color:#64748b;font-size:13px;margin-bottom:6px;}
.finance-stat .value{font-size:1.5rem;font-weight:700;margin-bottom:4px;}
.finance-stat .meta{font-size:12px;color:#94a3b8;}
.source-breakdown{border-radius:16px;padding:16px;background:#f8fafc;border:2px solid #e2e8f0;margin-bottom:8px;cursor:pointer;transition:all .2s;}
.source-breakdown:hover{border-color:#cbd5e1;background:#f1f5f9;}
.source-breakdown.active{border-color:#3b82f6;background:#eff6ff;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Work Area</span>
        <h2 class="fw-bold mb-1">Business Expenses</h2>
        <p class="text-muted mb-0">All business expenses — one entity, tracked by source and category.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <a href="<?= $base ?>/expenses/export?format=csv" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="<?= $base ?>/expenses/create" class="btn btn-dark btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Expense
        </a>
    </div>
</div>

<style>
@media print {
    .btn, .sidebar, .topbar, .source-breakdown, .finance-card:not(#printable) { display: none !important; }
    #printable { display: block !important; }
    body { background: white !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
}
</style>

<!-- SUMMARY STATS -->
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
            <div class="value text-danger">GHS <?= number_format($totalAmount, 2) ?></div>
            <div class="meta">Lifetime total</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="finance-stat">
            <div class="label">This Month</div>
            <div class="value text-warning">GHS <?= number_format($currentMonthAmount, 2) ?></div>
            <div class="meta">Current month</div>
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

<!-- SOURCE BREAKDOWN -->
<div class="finance-card p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Expense Breakdown by Source</h5>
    <div class="row g-3">
        <div class="col-md-12">
            <a href="?" class="text-decoration-none">
                <div class="source-breakdown <?= $filterSource === 'all' ? 'active' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><div class="fw-bold text-dark">All Sources</div><div class="small text-muted"><?= number_format($totalRecords) ?> records</div></div>
                        <div class="text-end"><div class="fs-4 fw-bold text-dark">GHS <?= number_format($totalAmount, 2) ?></div><div class="small text-muted">Total</div></div>
                    </div>
                </div>
            </a>
        </div>
        <?php foreach ($sourceConfig as $source => $config):
            $data = $bySource[$source] ?? ['count'=>0,'total'=>0,'current_month'=>0];
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

<!-- EXPENSE TABLE -->
<div class="finance-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">
                <?= $filterSource === 'all' ? 'All Expense Records' : ($sourceConfig[$filterSource]['label'] ?? 'Filtered') ?>
            </h5>
            <p class="text-muted small mb-0">Business expenses — all operations are shared.</p>
        </div>
        <?php if ($filterSource !== 'all'): ?>
            <a href="?" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Source</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filteredRecords = $records;
                if ($filterSource !== 'all') {
                    $filteredRecords = array_filter($records, fn($r) => ($r['expense_source'] ?? 'manual') === $filterSource);
                }

                if (!empty($filteredRecords)):
                    foreach ($filteredRecords as $r):
                        $source = $r['expense_source'] ?? 'manual';
                        $amt    = (float)($r['amount'] ?? 0);
                        $paid   = (float)($r['amount_paid'] ?? ($source === 'manual' ? 0 : $amt));
                        $bal    = $amt - $paid;
                        $status = $r['payment_status'] ?? ($source !== 'manual' ? 'paid' : 'unpaid');

                        $badgeMap = [
                            'manual'             => ['primary','Manual'],
                            'livestock_purchase' => ['dark','Livestock'],
                            'mortality_loss'     => ['danger','Mortality'],
                            'feed'               => ['warning','Feed'],
                            'medication'         => ['danger','Medication'],
                            'vaccination'        => ['success','Vaccination'],
                        ];
                        [$bc, $bl] = $badgeMap[$source] ?? ['secondary', ucfirst($source)];
                        $statusBadge = match($status) { 'paid' => 'success', 'partial' => 'warning', default => 'danger' };
                ?>
                <tr>
                    <td class="small"><?= htmlspecialchars($r['date'] ?? '') ?></td>
                    <td><?= htmlspecialchars(substr($r['title'] ?? '', 0, 50)) ?></td>
                    <td><span class="badge text-bg-<?= $bc ?>"><?= $bl ?></span></td>
                    <td class="small"><?= htmlspecialchars($r['category_name'] ?? 'Uncategorized') ?></td>
                    <td class="fw-bold">GHS <?= number_format($amt, 2) ?></td>
                    <td class="text-success small">GHS <?= number_format($paid, 2) ?></td>
                    <td class="<?= $bal > 0 ? 'text-danger' : 'text-success' ?> small">GHS <?= number_format($bal, 2) ?></td>
                    <td><span class="badge text-bg-<?= $statusBadge ?>"><?= ucfirst($status) ?></span></td>
                    <td>
                        <?php if ($source === 'manual'): ?>
                            <div class="d-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/expenses/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="<?= $base ?>/expenses/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete?')">Del</a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">Auto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        No expense records found.
                        <?php if ($filterSource !== 'all'): ?>
                            <a href="?" class="d-block mt-2">View all expenses</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

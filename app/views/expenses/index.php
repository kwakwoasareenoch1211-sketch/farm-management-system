<?php
$totalRecords = (float)($totals['total_records'] ?? 0);
$totalAmount  = (float)($totals['total_amount'] ?? 0);
$currentMonthAmount = (float)($totals['current_month_amount'] ?? 0);
$todayAmount  = (float)($totals['today_amount'] ?? 0);
$bySource     = $totals['by_source'] ?? [];
$filterSource = $_GET['source'] ?? 'all';
$filterOwner  = isset($_GET['owner']) ? (int)$_GET['owner'] : 0;

$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<style>
.finance-card{border:0;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.08);background:#fff;}
.finance-stat{border-radius:18px;padding:18px;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%);border:1px solid #eef2f7;height:100%;}
.finance-stat .label{color:#64748b;font-size:13px;margin-bottom:6px;}
.finance-stat .value{font-size:1.5rem;font-weight:700;margin-bottom:4px;}
.finance-stat .meta{font-size:12px;color:#94a3b8;}
.owner-card{border-radius:20px;padding:24px;border:2px solid #e2e8f0;background:#fff;transition:all .2s;cursor:pointer;}
.owner-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.1);}
.owner-card.active{border-width:3px;}
.source-breakdown{border-radius:16px;padding:16px;background:#f8fafc;border:2px solid #e2e8f0;margin-bottom:8px;cursor:pointer;transition:all .2s;}
.source-breakdown:hover{border-color:#cbd5e1;background:#f1f5f9;}
.source-breakdown.active{border-color:#3b82f6;background:#eff6ff;}
.margin-positive{color:#16a34a;font-weight:700;}
.margin-negative{color:#dc2626;font-weight:700;}
.progress-thin{height:6px;border-radius:3px;}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Financial Work Area</span>
        <h2 class="fw-bold mb-1">Expenses Dashboard</h2>
        <p class="text-muted mb-0">Smart categorization · Owner breakdown · Margin analysis</p>
    </div>
    <a href="<?= rtrim(BASE_URL,'/') ?>/expenses/create" class="btn btn-dark">
        <i class="bi bi-plus-circle me-1"></i> Add Expense
    </a>
</div>

<!-- OWNER BREAKDOWN CARDS -->
<div class="row g-4 mb-4">
    <?php foreach ($ownerBreakdown as $i => $owner):
        $color = $ownerColors[$i % count($ownerColors)];
        $isActive = $filterOwner === $owner['id'];
        $marginClass = $owner['margin'] >= 0 ? 'margin-positive' : 'margin-negative';
        $marginIcon  = $owner['margin'] >= 0 ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill';
        $paidPct = $owner['total'] > 0 ? min(100, ($owner['paid'] / $owner['total']) * 100) : 0;
    ?>
    <div class="col-md-6">
        <a href="?owner=<?= $owner['id'] ?><?= $filterSource !== 'all' ? '&source='.$filterSource : '' ?>" class="text-decoration-none">
            <div class="owner-card <?= $isActive ? 'active' : '' ?>" style="<?= $isActive ? "border-color:$color;" : '' ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:40px;height:40px;background:<?= $color ?>;font-size:16px;">
                                <?= strtoupper(substr($owner['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($owner['name']) ?></div>
                                <div class="text-muted small">@<?= htmlspecialchars($owner['username']) ?></div>
                            </div>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $color ?>20;color:<?= $color ?>;">
                        <i class="bi <?= $marginIcon ?> me-1"></i>
                        <?= $owner['margin'] >= 0 ? '+' : '' ?>GHS <?= number_format(abs($owner['margin']), 2) ?>
                    </span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-4 text-center">
                        <div class="small text-muted mb-1">Total Expenses</div>
                        <div class="fw-bold text-dark">GHS <?= number_format($owner['total'], 2) ?></div>
                    </div>
                    <div class="col-4 text-center" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                        <div class="small text-muted mb-1">Paid</div>
                        <div class="fw-bold text-success">GHS <?= number_format($owner['paid'], 2) ?></div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="small text-muted mb-1">Balance</div>
                        <div class="fw-bold <?= $owner['balance'] > 0 ? 'text-danger' : 'text-success' ?>">
                            GHS <?= number_format($owner['balance'], 2) ?>
                        </div>
                    </div>
                </div>

                <!-- Payment progress -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Payment Progress</span>
                        <span><?= number_format($paidPct, 0) ?>% paid</span>
                    </div>
                    <div class="progress progress-thin">
                        <div class="progress-bar" style="width:<?= $paidPct ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>

                <!-- This month + margin -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <i class="bi bi-calendar3 me-1"></i>This month: <strong>GHS <?= number_format($owner['this_month'], 2) ?></strong>
                    </div>
                    <div class="small <?= $marginClass ?>">
                        <i class="bi <?= $marginIcon ?> me-1"></i>
                        Margin: <?= $owner['margin'] >= 0 ? '+' : '' ?>GHS <?= number_format($owner['margin'], 2) ?>
                    </div>
                </div>

                <?php if (!empty($owner['categories'])): ?>
                <div class="mt-3 pt-3" style="border-top:1px solid #e2e8f0;">
                    <div class="small text-muted mb-2">Top Categories</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (array_slice($owner['categories'], 0, 4) as $cat): ?>
                            <span class="badge rounded-pill" style="background:<?= $color ?>15;color:<?= $color ?>;font-size:11px;">
                                <?= htmlspecialchars($cat['cat']) ?>: GHS <?= number_format($cat['total'], 0) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($filterOwner > 0): ?>
<div class="alert alert-info d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-person-fill"></i>
    Showing expenses for <strong><?= htmlspecialchars(collect_owner_name($ownerBreakdown, $filterOwner)) ?></strong>
    <a href="?" class="btn btn-sm btn-outline-secondary ms-auto">Clear Filter</a>
</div>
<?php endif; ?>

<?php
function collect_owner_name($owners, $id) {
    foreach ($owners as $o) { if ($o['id'] == $id) return $o['name']; }
    return 'Unknown';
}
?>

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
            <a href="?<?= $filterOwner ? 'owner='.$filterOwner : '' ?>" class="text-decoration-none">
                <div class="source-breakdown <?= $filterSource === 'all' ? 'active' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><div class="fw-bold text-dark">All Sources</div><div class="small text-muted"><?= number_format($totalRecords) ?> records</div></div>
                        <div class="text-end"><div class="fs-4 fw-bold text-dark">GHS <?= number_format($totalAmount, 2) ?></div><div class="small text-muted">Total</div></div>
                    </div>
                </div>
            </a>
        </div>
        <?php
        $sourceConfig = [
            'manual'             => ['label'=>'Manual Expenses',      'color'=>'#0d6efd','icon'=>'bi-pencil-square'],
            'livestock_purchase' => ['label'=>'Livestock Purchase',   'color'=>'#6f42c1','icon'=>'bi-egg'],
            'mortality_loss'     => ['label'=>'Mortality Loss',       'color'=>'#d63384','icon'=>'bi-heartbreak'],
            'feed'               => ['label'=>'Feed Costs',           'color'=>'#ffc107','icon'=>'bi-basket'],
            'medication'         => ['label'=>'Medication Costs',     'color'=>'#dc3545','icon'=>'bi-capsule'],
            'vaccination'        => ['label'=>'Vaccination Costs',    'color'=>'#198754','icon'=>'bi-shield-check'],
        ];
        foreach ($sourceConfig as $source => $config):
            $data = $bySource[$source] ?? ['count'=>0,'total'=>0,'current_month'=>0];
            if ($data['count'] == 0) continue;
        ?>
        <div class="col-md-6">
            <a href="?source=<?= $source ?><?= $filterOwner ? '&owner='.$filterOwner : '' ?>" class="text-decoration-none">
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
                <?php if ($filterOwner): ?>
                    <span class="badge text-bg-info ms-2"><?= htmlspecialchars(collect_owner_name($ownerBreakdown, $filterOwner)) ?></span>
                <?php endif; ?>
            </h5>
            <p class="text-muted small mb-0">Click owner cards or source filters above to drill down.</p>
        </div>
        <?php if ($filterSource !== 'all' || $filterOwner): ?>
            <a href="?" class="btn btn-sm btn-outline-secondary">Clear All Filters</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Owner</th>
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
                    $filteredRecords = array_filter($filteredRecords, fn($r) => ($r['expense_source'] ?? 'manual') === $filterSource);
                }
                if ($filterOwner > 0) {
                    $filteredRecords = array_filter($filteredRecords, fn($r) => (int)($r['owner_id'] ?? 0) === $filterOwner);
                }

                if (!empty($filteredRecords)):
                    foreach ($filteredRecords as $r):
                        $source = $r['expense_source'] ?? 'manual';
                        $amt    = (float)($r['amount'] ?? 0);
                        $paid   = (float)($r['amount_paid'] ?? ($source === 'manual' ? 0 : $amt));
                        $bal    = $amt - $paid;
                        $status = $r['payment_status'] ?? ($source !== 'manual' ? 'paid' : 'unpaid');

                        // Owner name
                        $ownerName = '—';
                        $ownerColor = '#94a3b8';
                        if (!empty($r['owner_id'])) {
                            foreach ($ownerBreakdown as $idx => $o) {
                                if ($o['id'] == $r['owner_id']) {
                                    $ownerName  = $o['name'];
                                    $ownerColor = $ownerColors[$idx % count($ownerColors)];
                                    break;
                                }
                            }
                        }

                        $badgeMap = [
                            'manual'             => ['primary','Manual'],
                            'livestock_purchase' => ['dark','Livestock'],
                            'mortality_loss'     => ['danger','Mortality'],
                            'feed'               => ['warning','Feed'],
                            'medication'         => ['danger','Medication'],
                            'vaccination'        => ['success','Vaccination'],
                        ];
                        [$bc, $bl] = $badgeMap[$source] ?? ['secondary', ucfirst($source)];
                        $statusBadge = match($status) { 'paid'=>'success', 'partial'=>'warning', default=>'danger' };
                ?>
                <tr>
                    <td class="small"><?= htmlspecialchars($r['date'] ?? '') ?></td>
                    <td><?= htmlspecialchars(substr($r['title'] ?? '', 0, 45)) ?></td>
                    <td>
                        <?php if ($ownerName !== '—'): ?>
                            <span class="badge rounded-pill px-2" style="background:<?= $ownerColor ?>20;color:<?= $ownerColor ?>;">
                                <?= htmlspecialchars($ownerName) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge text-bg-<?= $bc ?>"><?= $bl ?></span></td>
                    <td class="small"><?= htmlspecialchars($r['category_name'] ?? 'Uncategorized') ?></td>
                    <td class="fw-bold">GHS <?= number_format($amt, 2) ?></td>
                    <td class="text-success small">GHS <?= number_format($paid, 2) ?></td>
                    <td class="<?= $bal > 0 ? 'text-danger' : 'text-success' ?> small">GHS <?= number_format($bal, 2) ?></td>
                    <td><span class="badge text-bg-<?= $statusBadge ?>"><?= ucfirst($status) ?></span></td>
                    <td>
                        <?php if ($source === 'manual'): ?>
                            <div class="d-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL,'/') ?>/expenses/edit?id=<?= (int)$r['id'] ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL,'/') ?>/expenses/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete?')">Del</a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">Auto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        No expense records found.
                        <?php if ($filterSource !== 'all' || $filterOwner): ?>
                            <a href="?" class="d-block mt-2">Clear filters</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

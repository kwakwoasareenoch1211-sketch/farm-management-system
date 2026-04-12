<?php
$records       = $records       ?? [];
$totals        = $totals        ?? [];
$byContributor = $byContributor ?? [];
$byType        = $byType        ?? [];
$base          = rtrim(BASE_URL, '/');

$typeLabels = [
    'owner_equity'      => ['label' => 'Owner Equity',       'color' => '#3b82f6', 'icon' => 'bi-person-fill'],
    'reinvestment'      => ['label' => 'Reinvestment',        'color' => '#10b981', 'icon' => 'bi-arrow-repeat'],
    'retained_earnings' => ['label' => 'Retained Earnings',   'color' => '#f59e0b', 'icon' => 'bi-piggy-bank'],
    'loan_capital'      => ['label' => 'Loan Capital',        'color' => '#ef4444', 'icon' => 'bi-bank'],
    'grant'             => ['label' => 'Grant',               'color' => '#8b5cf6', 'icon' => 'bi-gift'],
    'other'             => ['label' => 'Other',               'color' => '#64748b', 'icon' => 'bi-three-dots'],
];
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];

$totalCapital = (float)($totals['total_capital'] ?? 0);
?>

<style>
.cap-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.cap-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;}
.cap-kpi .lbl{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;}
.cap-kpi .val{font-size:1.4rem;font-weight:700;}
.contributor-card{border-radius:18px;padding:22px;border:2px solid #e2e8f0;background:#fff;transition:all .2s;}
.progress-sm{height:8px;border-radius:4px;}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-primary mb-2 px-3 py-2">Financial Management</span>
        <h2 class="fw-bold mb-1">Capital Management</h2>
        <p class="text-muted mb-0">
            The business is <strong>one entity</strong>. Capital records show who contributed what amount to start or reinvest in the business.
            All operations (expenses, sales, feed) belong to the business as a whole.
        </p>
    </div>
    <a href="<?= $base ?>/capital/create" class="btn btn-dark">
        <i class="bi bi-plus-circle me-1"></i> Add Capital Entry
    </a>
</div>

<!-- BUSINESS TOTALS -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="cap-kpi" style="border-left:4px solid #3b82f6;">
            <div class="lbl">Total Business Capital</div>
            <div class="val text-primary">GHS <?= number_format((float)($totals['total_capital'] ?? 0), 2) ?></div>
            <div class="small text-muted mt-1"><?= (int)($totals['total_records'] ?? 0) ?> entr<?= ($totals['total_records'] ?? 0) == 1 ? 'y' : 'ies' ?> combined</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="cap-kpi" style="border-left:4px solid #6366f1;">
            <div class="lbl">Owner Equity</div>
            <div class="val">GHS <?= number_format((float)($totals['owner_equity'] ?? 0), 2) ?></div>
            <div class="small text-muted mt-1">Initial capital invested</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="cap-kpi" style="border-left:4px solid #10b981;">
            <div class="lbl">Reinvestment</div>
            <div class="val text-success">GHS <?= number_format((float)($totals['reinvestment'] ?? 0), 2) ?></div>
            <div class="small text-muted mt-1">Profits put back in</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="cap-kpi" style="border-left:4px solid #ef4444;">
            <div class="lbl">Loan Capital</div>
            <div class="val text-danger">GHS <?= number_format((float)($totals['loan_capital'] ?? 0), 2) ?></div>
            <div class="small text-muted mt-1">Borrowed funds</div>
        </div>
    </div>
</div>

<!-- CONTRIBUTOR BREAKDOWN -->
<?php if (!empty($byContributor)): ?>
<div class="cap-card p-4 mb-4">
    <h5 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Contributor Breakdown</h5>
    <p class="text-muted small mb-4">Who contributed what to the business. This is for record-keeping only — the business operates as one.</p>

    <div class="row g-4">
        <?php foreach ($byContributor as $i => $c):
            $color = $ownerColors[$i % count($ownerColors)];
            $freshTotal = (float)($totals['total_capital'] ?? 0);
            $pct   = $freshTotal > 0 ? ((float)$c['total_contributed'] / $freshTotal) * 100 : 0;
        ?>
        <div class="col-md-6">
            <div class="contributor-card" style="border-color:<?= $color ?>30;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:44px;height:44px;background:<?= $color ?>;font-size:18px;flex-shrink:0;">
                        <?= strtoupper(substr($c['contributor_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($c['contributor_name']) ?></div>
                        <?php if (!empty($c['username'])): ?>
                            <div class="text-muted small">@<?= htmlspecialchars($c['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="ms-auto text-end">
                        <div class="fw-bold fs-5" style="color:<?= $color ?>">GHS <?= number_format((float)$c['total_contributed'], 2) ?></div>
                        <div class="small text-muted"><?= number_format($pct, 1) ?>% of total capital</div>
                    </div>
                </div>

                <!-- Progress bar -->
                <div class="progress progress-sm mb-3">
                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                </div>

                <!-- Breakdown by type -->
                <div class="row g-2">
                    <div class="col-4 text-center">
                        <div class="small text-muted">Equity</div>
                        <div class="fw-semibold small">GHS <?= number_format((float)$c['equity'], 0) ?></div>
                    </div>
                    <div class="col-4 text-center" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                        <div class="small text-muted">Reinvestment</div>
                        <div class="fw-semibold small text-success">GHS <?= number_format((float)$c['reinvestment'], 0) ?></div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="small text-muted">Retained</div>
                        <div class="fw-semibold small text-warning">GHS <?= number_format((float)$c['retained'], 0) ?></div>
                    </div>
                </div>

                <div class="mt-2 text-muted small"><?= (int)$c['entries'] ?> capital entr<?= $c['entries'] == 1 ? 'y' : 'ies' ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- CAPITAL ENTRIES TABLE -->
    <div class="col-lg-8">
        <div class="cap-card p-4">
            <h5 class="fw-bold mb-3">All Capital Entries</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Contributor</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r):
                                $ct = $r['capital_type'] ?? 'other';
                                $cfg = $typeLabels[$ct] ?? $typeLabels['other'];
                            ?>
                            <tr>
                                <td class="small"><?= htmlspecialchars($r['entry_date'] ?? '') ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['title'] ?? $r['description'] ?? 'Capital Entry') ?></div>
                                    <?php if (!empty($r['source_name'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($r['source_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['contributor_name'])): ?>
                                        <span class="badge rounded-pill px-2" style="background:<?= $cfg['color'] ?>20;color:<?= $cfg['color'] ?>;">
                                            <?= htmlspecialchars($r['contributor_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Business</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= $cfg['color'] ?>20;color:<?= $cfg['color'] ?>;">
                                        <i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">GHS <?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= $base ?>/capital/edit?id=<?= (int)$r['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        <a href="<?= $base ?>/capital/delete?id=<?= (int)$r['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this entry?')">Del</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No capital entries yet.
                                    <a href="<?= $base ?>/capital/create" class="d-block mt-2">Add your first capital entry</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CAPITAL BY TYPE -->
    <div class="col-lg-4">
        <div class="cap-card p-4">
            <h5 class="fw-bold mb-3">Capital by Type</h5>
            <?php if (!empty($byType)): ?>
                <?php foreach ($byType as $t):
                    $ct  = $t['capital_type'] ?? 'other';
                    $cfg = $typeLabels[$ct] ?? $typeLabels['other'];
                    $freshTotal2 = (float)($totals['total_capital'] ?? 0);
                    $pct = $freshTotal2 > 0 ? ((float)$t['total'] / $freshTotal2) * 100 : 100;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?= $cfg['icon'] ?>" style="color:<?= $cfg['color'] ?>"></i>
                            <span class="fw-semibold small"><?= $cfg['label'] ?></span>
                        </div>
                        <span class="fw-bold small">GHS <?= number_format((float)$t['total'], 0) ?></span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $cfg['color'] ?>"></div>
                    </div>
                    <div class="text-muted" style="font-size:11px;"><?= number_format($pct, 1) ?>% · <?= $t['records'] ?> entr<?= $t['records'] == 1 ? 'y' : 'ies' ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small">No capital data yet.</p>
            <?php endif; ?>

            <a href="<?= $base ?>/capital/create" class="btn btn-dark w-100 mt-3">
                <i class="bi bi-plus-circle me-1"></i> Add Capital Entry
            </a>
        </div>
    </div>
</div>

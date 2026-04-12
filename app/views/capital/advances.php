<?php
$base = rtrim(BASE_URL, '/');
$advances = $advances ?? [];
$ownerSummary = $ownerSummary ?? [];
$ownerColors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
?>
<style>
.adv-card{border-radius:18px;background:#fff;border:0;box-shadow:0 6px 24px rgba(15,23,42,.07);}
.adv-kpi{border-radius:16px;padding:18px;background:#fff;border:1px solid #eef2f7;height:100%;}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-warning mb-2 px-3 py-2">Financial Tracking</span>
        <h2 class="fw-bold mb-1">Owner Personal Advances</h2>
        <p class="text-muted mb-0">
            When business capital runs short, owners fund operations personally.
            This tracks what the business owes back to each owner.
        </p>
    </div>
    <a href="<?= $base ?>/capital" class="btn btn-outline-secondary btn-sm">Back to Capital</a>
</div>

<!-- OWNER SUMMARY CARDS -->
<?php if (!empty($ownerSummary)): ?>
<div class="row g-4 mb-4">
    <?php foreach ($ownerSummary as $i => $owner):
        $color = $ownerColors[$i % count($ownerColors)];
        $outstanding = (float)$owner['total_advanced'] - (float)$owner['total_repaid'];
        $pct = (float)$owner['total_advanced'] > 0 ? ((float)$owner['total_repaid'] / (float)$owner['total_advanced']) * 100 : 0;
    ?>
    <div class="col-md-6">
        <div class="adv-card p-4" style="border-left:4px solid <?= $color ?>;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:44px;height:44px;background:<?= $color ?>;font-size:18px;">
                    <?= strtoupper(substr($owner['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($owner['full_name']) ?></div>
                    <div class="text-muted small"><?= (int)$owner['advance_count'] ?> advance(s)</div>
                </div>
                <div class="ms-auto text-end">
                    <div class="fw-bold fs-5 text-danger">GHS <?= number_format($outstanding, 2) ?></div>
                    <div class="small text-muted">Outstanding</div>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4 text-center">
                    <div class="small text-muted">Total Advanced</div>
                    <div class="fw-semibold">GHS <?= number_format((float)$owner['total_advanced'], 2) ?></div>
                </div>
                <div class="col-4 text-center" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                    <div class="small text-muted">Repaid</div>
                    <div class="fw-semibold text-success">GHS <?= number_format((float)$owner['total_repaid'], 2) ?></div>
                </div>
                <div class="col-4 text-center">
                    <div class="small text-muted">Still Owed</div>
                    <div class="fw-semibold text-danger">GHS <?= number_format($outstanding, 2) ?></div>
                </div>
            </div>

            <div class="mb-1 d-flex justify-content-between small text-muted">
                <span>Repayment Progress</span>
                <span><?= number_format($pct, 0) ?>%</span>
            </div>
            <div class="progress" style="height:6px;border-radius:3px;">
                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ADVANCES TABLE -->
<div class="adv-card p-4">
    <h5 class="fw-bold mb-3">All Personal Advances</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Repaid</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($advances)): ?>
                    <?php foreach ($advances as $i => $a):
                        $outstanding = (float)$a['amount'] - (float)$a['repaid_amount'];
                        $statusBadge = match($a['status']) { 'repaid'=>'success', 'partial'=>'warning', default=>'danger' };
                        $color = $ownerColors[$i % count($ownerColors)];
                    ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars($a['advance_date']) ?></td>
                        <td>
                            <span class="badge rounded-pill px-2" style="background:<?= $color ?>20;color:<?= $color ?>;">
                                <?= htmlspecialchars($a['full_name'] ?? 'Unknown') ?>
                            </span>
                        </td>
                        <td><span class="badge text-bg-secondary"><?= ucfirst($a['source_type']) ?></span></td>
                        <td class="small"><?= htmlspecialchars(substr($a['description'] ?? '', 0, 50)) ?></td>
                        <td class="fw-bold">GHS <?= number_format((float)$a['amount'], 2) ?></td>
                        <td class="text-success small">GHS <?= number_format((float)$a['repaid_amount'], 2) ?></td>
                        <td class="text-danger fw-semibold">GHS <?= number_format($outstanding, 2) ?></td>
                        <td><span class="badge text-bg-<?= $statusBadge ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td>
                            <?php if ($a['status'] !== 'repaid'): ?>
                            <form method="POST" action="<?= $base ?>/capital/repay-advance" class="d-flex gap-1">
                                <input type="hidden" name="advance_id" value="<?= (int)$a['id'] ?>">
                                <input type="number" name="repay_amount" step="0.01" min="0.01"
                                       max="<?= $outstanding ?>" placeholder="Amount"
                                       class="form-control form-control-sm" style="width:100px;">
                                <button type="submit" class="btn btn-sm btn-success">Repay</button>
                            </form>
                            <?php else: ?>
                                <span class="text-success small"><i class="bi bi-check-circle-fill"></i> Repaid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            No personal advances recorded yet.<br>
                            <small>When an owner pays for feed, medication, or expenses personally, it will appear here.</small>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

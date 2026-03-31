<?php
$decisions    = $decisions    ?? [];
$mt           = $monitorTotals ?? [];
$currentMonth = $currentMonth  ?? [];
$base         = rtrim(BASE_URL, "/");
$totalRevenue  = (float)($mt["total_revenue"] ?? 0);
$totalExpenses = (float)($mt["total_expenses"] ?? 0);
$netProfit     = (float)($mt["net_profit"] ?? 0);
$profitMargin  = (float)($mt["profit_margin"] ?? 0);
$debtRatio     = (float)($mt["debt_ratio"] ?? 0);
$workingCapital= (float)($mt["working_capital"] ?? 0);
$priorityOrder = ["high" => 0, "medium" => 1, "low" => 2];
usort($decisions, fn($a,$b) => ($priorityOrder[$a["priority"]] ?? 9) <=> ($priorityOrder[$b["priority"]] ?? 9));
?>
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-4">
<div><h2 class="fw-bold mb-1">Decision Recommendations</h2><p class="text-muted mb-0">AI-driven recommendations based on live financial and operational data.</p></div>
<a href="<?= $base ?>/reports" class="btn btn-outline-secondary">Back</a>
</div>
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Net Profit</div><div class="fs-4 fw-bold <?= $netProfit >= 0 ? "text-success" : "text-danger" ?>">GHS <?= number_format($netProfit, 2) ?></div></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Profit Margin</div><div class="fs-4 fw-bold <?= $profitMargin >= 15 ? "text-success" : "text-warning" ?>"><?= number_format($profitMargin, 1) ?>%</div></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Working Capital</div><div class="fs-4 fw-bold <?= $workingCapital >= 0 ? "text-success" : "text-danger" ?>">GHS <?= number_format($workingCapital, 2) ?></div></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="text-muted small">Debt Ratio</div><div class="fs-4 fw-bold <?= $debtRatio <= 50 ? "text-success" : "text-danger" ?>"><?= number_format($debtRatio, 1) ?>%</div></div></div></div>
</div>
<?php if (!empty($smartSignals)): ?>
<div class="mb-4"><?php foreach ($smartSignals as $s): ?><div class="alert alert-<?= htmlspecialchars($s["type"]) ?> mb-2"><strong><?= htmlspecialchars($s["title"]) ?>:</strong> <?= htmlspecialchars($s["message"]) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<h5 class="fw-bold mb-3">Recommended Actions</h5>
<?php if (!empty($decisions)): ?>
<?php foreach ($decisions as $d): ?>
<div class="card border-0 shadow-sm rounded-4 mb-3">
<div class="card-body">
<div class="d-flex justify-content-between align-items-start mb-2">
<div class="d-flex align-items-center gap-2"><span class="badge bg-<?= $d["type"] ?>"><?= strtoupper($d["priority"]) ?> PRIORITY</span><h6 class="fw-bold mb-0"><?= htmlspecialchars($d["title"]) ?></h6></div>
<a href="<?= $base . htmlspecialchars($d["link"]) ?>" class="btn btn-<?= $d["type"] ?> btn-sm">Take Action</a>
</div>
<p class="text-muted mb-1"><strong>Why:</strong> <?= htmlspecialchars($d["reason"]) ?></p>
<p class="mb-0"><strong>What to do:</strong> <?= htmlspecialchars($d["action"]) ?></p>
</div></div>
<?php endforeach; ?>
<?php else: ?>
<div class="alert alert-success">No critical decisions required. Business is operating within healthy parameters.</div>
<?php endif; ?>
<div class="row g-4 mt-2">
<div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold mb-3">Top Profitable Batches</h5>
<?php if (!empty($topBatches)): ?><?php foreach ($topBatches as $b): ?>
<div class="border rounded-4 p-3 mb-2 bg-light d-flex justify-content-between"><div><div class="fw-semibold"><?= htmlspecialchars($b["batch_code"] ?? "") ?></div><div class="small text-muted"><?= htmlspecialchars(ucfirst($b["production_purpose"] ?? "")) ?></div></div><div class="text-success fw-bold">GHS <?= number_format((float)($b["gross_profit"] ?? 0), 2) ?></div></div>
<?php endforeach; ?><?php else: ?><p class="text-muted">No batch data yet.</p><?php endif; ?>
</div></div></div>
<div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold mb-3">Monthly Net Trend</h5>
<?php if (!empty($monthlyCombined)): ?><?php foreach ($monthlyCombined as $m): ?><?php $n=(float)($m["net_position"]??0); ?>
<div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted small"><?= htmlspecialchars($m["month_label"]??"") ?></span><span class="fw-bold <?= $n>=0?"text-success":"text-danger" ?>">GHS <?= number_format($n,2) ?></span></div>
<?php endforeach; ?><?php else: ?><p class="text-muted">No monthly data yet.</p><?php endif; ?>
</div></div></div>
</div>
</div>
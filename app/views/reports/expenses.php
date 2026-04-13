<?php
$printTitle    = 'Complete Business Expense Report';
$printSubtitle = 'All expenses: manual, livestock purchase, feed, medication, vaccination, mortality';
$exportUrl     = rtrim(BASE_URL, '/') . '/reports/download?type=expenses';
include BASE_PATH . 'app/views/layouts/print_toolbar.php';

// Load ALL business expenses directly in the view for completeness
require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

// Collect all expense sources
$allExpenses = [];

// 1. Manual expenses
$rows = $db->query("
    SELECT 'Manual Expense' AS source, e.expense_date AS date,
        COALESCE(e.description,'Manual Expense') AS description,
        COALESCE(ec.category_name,'Uncategorized') AS category,
        e.amount, e.payment_method, e.payment_status,
        COALESCE(e.amount_paid, 0) AS amount_paid
    FROM expenses e
    LEFT JOIN expense_categories ec ON ec.id = e.category_id
    ORDER BY e.expense_date DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// 2. Livestock / Chick purchases
$rows = $db->query("
    SELECT 'Livestock Purchase' AS source,
        COALESCE(ab.purchase_date, ab.start_date) AS date,
        CONCAT('Chicks/Birds: ', ab.batch_code, IFNULL(CONCAT(' - ', ab.batch_name),''),
               ' (', ab.initial_quantity, ' @ GHS ', ab.initial_unit_cost, ')') AS description,
        'Livestock Purchase' AS category,
        (ab.initial_quantity * ab.initial_unit_cost) AS amount,
        'cash' AS payment_method, 'paid' AS payment_status,
        (ab.initial_quantity * ab.initial_unit_cost) AS amount_paid
    FROM animal_batches ab
    WHERE ab.initial_unit_cost > 0 AND ab.initial_quantity > 0
    ORDER BY COALESCE(ab.purchase_date, ab.start_date) DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// 3. Feed costs
$rows = $db->query("
    SELECT 'Feed' AS source, fr.record_date AS date,
        CONCAT('Feed: ', COALESCE(fr.feed_name,'Unknown'), ' - ', COALESCE(ab.batch_code,'N/A')) AS description,
        'Feed' AS category,
        (fr.quantity_kg * fr.unit_cost) AS amount,
        'cash' AS payment_method, 'paid' AS payment_status,
        (fr.quantity_kg * fr.unit_cost) AS amount_paid
    FROM feed_records fr
    LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
    WHERE fr.unit_cost > 0
    ORDER BY fr.record_date DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// 4. Medication costs
$rows = $db->query("
    SELECT 'Medication' AS source, mr.record_date AS date,
        CONCAT('Medication: ', COALESCE(mr.medication_name,'Unknown'), ' - ', COALESCE(ab.batch_code,'N/A')) AS description,
        'Medication' AS category,
        (mr.quantity_used * mr.unit_cost) AS amount,
        'cash' AS payment_method, 'paid' AS payment_status,
        (mr.quantity_used * mr.unit_cost) AS amount_paid
    FROM medication_records mr
    LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
    WHERE mr.unit_cost > 0 AND mr.quantity_used > 0
    ORDER BY mr.record_date DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// 5. Vaccination costs
$rows = $db->query("
    SELECT 'Vaccination' AS source, vr.record_date AS date,
        CONCAT('Vaccination: ', COALESCE(vr.vaccine_name,'Unknown'), ' - ', COALESCE(ab.batch_code,'N/A')) AS description,
        'Vaccination' AS category,
        vr.cost_amount AS amount,
        'cash' AS payment_method, 'paid' AS payment_status,
        vr.cost_amount AS amount_paid
    FROM vaccination_records vr
    LEFT JOIN animal_batches ab ON ab.id = vr.batch_id
    WHERE vr.cost_amount > 0
    ORDER BY vr.record_date DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// 6. Mortality losses (financial impact)
$rows = $db->query("
    SELECT 'Mortality Loss' AS source, mr.record_date AS date,
        CONCAT('Mortality: ', mr.quantity, ' birds - ', COALESCE(ab.batch_code,'N/A'),
               IFNULL(CONCAT(' (', mr.cause, ')'),'')) AS description,
        'Mortality Loss' AS category,
        (mr.quantity * COALESCE(ab.initial_unit_cost, 0)) AS amount,
        'loss' AS payment_method, 'paid' AS payment_status,
        (mr.quantity * COALESCE(ab.initial_unit_cost, 0)) AS amount_paid
    FROM mortality_records mr
    LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
    WHERE ab.initial_unit_cost > 0
    ORDER BY mr.record_date DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$allExpenses = array_merge($allExpenses, $rows);

// Sort all by date descending
usort($allExpenses, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

// Compute totals
$grandTotal = array_sum(array_column($allExpenses, 'amount'));
$bySource = [];
foreach ($allExpenses as $e) {
    $src = $e['source'];
    if (!isset($bySource[$src])) $bySource[$src] = ['count'=>0,'total'=>0];
    $bySource[$src]['count']++;
    $bySource[$src]['total'] += (float)$e['amount'];
}

$sourceColors = [
    'Manual Expense'    => '#0d6efd',
    'Livestock Purchase'=> '#6f42c1',
    'Feed'              => '#f59e0b',
    'Medication'        => '#dc3545',
    'Vaccination'       => '#198754',
    'Mortality Loss'    => '#d63384',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
    <div>
        <span class="badge rounded-pill text-bg-dark mb-2 px-3 py-2">Reports</span>
        <h2 class="fw-bold mb-1">Complete Business Expense Report</h2>
        <p class="text-muted mb-0">All expenses: manual, livestock, feed, medication, vaccination, mortality losses.</p>
    </div>
    <a href="<?= rtrim(BASE_URL,'/') ?>/reports" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
</div>

<!-- SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="text-muted small">Total Business Expenses</div>
                <div class="fs-4 fw-bold text-danger">GHS <?= number_format($grandTotal, 2) ?></div>
                <div class="small text-muted"><?= count($allExpenses) ?> records</div>
            </div>
        </div>
    </div>
    <?php foreach ($bySource as $src => $data): $color = $sourceColors[$src] ?? '#64748b'; ?>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm rounded-4" style="border-left:3px solid <?= $color ?> !important;">
            <div class="card-body p-3">
                <div class="text-muted" style="font-size:11px;"><?= $src ?></div>
                <div class="fw-bold" style="color:<?= $color ?>">GHS <?= number_format($data['total'], 0) ?></div>
                <div class="text-muted" style="font-size:11px;"><?= $data['count'] ?> records</div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- FULL EXPENSE TABLE -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">All Business Expenses (<?= count($allExpenses) ?> records)</h5>
        <div class="table-responsive">
            <table class="table align-middle table-hover" id="expenseTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount (GHS)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allExpenses)):
                        $i = 1;
                        foreach ($allExpenses as $e):
                            $amt = (float)($e['amount'] ?? 0);
                            $color = $sourceColors[$e['source']] ?? '#64748b';
                            $status = $e['payment_status'] ?? 'paid';
                            $statusBadge = match($status) { 'paid'=>'success', 'partial'=>'warning', default=>'danger' };
                    ?>
                    <tr>
                        <td class="text-muted small"><?= $i++ ?></td>
                        <td class="small"><?= htmlspecialchars($e['date'] ?? '') ?></td>
                        <td>
                            <span class="badge rounded-pill" style="background:<?= $color ?>20;color:<?= $color ?>;font-size:11px;">
                                <?= htmlspecialchars($e['source']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($e['description'] ?? '') ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($e['category'] ?? '') ?></td>
                        <td class="fw-bold">GHS <?= number_format($amt, 2) ?></td>
                        <td><span class="badge text-bg-<?= $statusBadge ?>"><?= ucfirst($status) ?></span></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No expense records found.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="fw-bold text-end">GRAND TOTAL</td>
                        <td class="fw-bold text-danger">GHS <?= number_format($grandTotal, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
/**
 * Verify Expense Totals Fix
 * Checks that Expense::totals() now matches FinancialMonitor::buildExpenses()
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';
require_once 'app/models/Expense.php';
require_once 'app/models/FinancialMonitor.php';

$db = (new Database())->connect();
$expense = new Expense($db);
$financial = new FinancialMonitor($db);

echo "=== EXPENSE TOTALS VERIFICATION ===\n\n";

// Get totals from Expense model
$expenseTotals = $expense->totals();
echo "EXPENSE MODEL TOTALS:\n";
echo "--------------------\n";
echo "Total Amount: GHS " . number_format($expenseTotals['total_amount'], 2) . "\n";
echo "Total Records: " . $expenseTotals['total_records'] . "\n\n";

echo "BREAKDOWN BY SOURCE:\n";
foreach ($expenseTotals['by_source'] as $source => $data) {
    if ($data['count'] > 0) {
        echo "  " . ucfirst(str_replace('_', ' ', $source)) . ": ";
        echo $data['count'] . " records, GHS " . number_format($data['total'], 2) . "\n";
    }
}

// Get totals from FinancialMonitor
$financialData = $financial->totals();
echo "\n\nFINANCIAL MONITOR TOTALS:\n";
echo "-------------------------\n";
echo "Total Expenses: GHS " . number_format($financialData['total_expenses'], 2) . "\n";

// Compare
echo "\n\n=== COMPARISON ===\n";
$difference = abs($expenseTotals['total_amount'] - $financialData['total_expenses']);
if ($difference < 0.01) {
    echo "✓ MATCH! Both show GHS " . number_format($expenseTotals['total_amount'], 2) . "\n";
    echo "✓ Expense page and Financial dashboard are now consistent!\n";
} else {
    echo "✗ MISMATCH!\n";
    echo "  Expense Model: GHS " . number_format($expenseTotals['total_amount'], 2) . "\n";
    echo "  Financial Monitor: GHS " . number_format($financialData['total_expenses'], 2) . "\n";
    echo "  Difference: GHS " . number_format($difference, 2) . "\n";
}

echo "\n=== INDIVIDUAL SOURCE VERIFICATION ===\n";

// Manual expenses
$manual = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total FROM expenses")->fetch(PDO::FETCH_ASSOC);
echo "Manual Expenses: " . $manual['cnt'] . " records, GHS " . number_format($manual['total'], 2) . "\n";

// Feed
$feed = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(quantity_kg * unit_cost), 0) as total FROM feed_records WHERE unit_cost > 0")->fetch(PDO::FETCH_ASSOC);
echo "Feed Costs: " . $feed['cnt'] . " records, GHS " . number_format($feed['total'], 2) . "\n";

// Medication
$med = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(quantity_used * unit_cost), 0) as total FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetch(PDO::FETCH_ASSOC);
echo "Medication Costs: " . $med['cnt'] . " records, GHS " . number_format($med['total'], 2) . "\n";

// Vaccination
$vac = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(cost_amount), 0) as total FROM vaccination_records WHERE cost_amount > 0")->fetch(PDO::FETCH_ASSOC);
echo "Vaccination Costs: " . $vac['cnt'] . " records, GHS " . number_format($vac['total'], 2) . "\n";

// Livestock purchase
$livestock = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(initial_quantity * initial_unit_cost), 0) as total FROM animal_batches WHERE initial_unit_cost > 0")->fetch(PDO::FETCH_ASSOC);
echo "Livestock Purchase: " . $livestock['cnt'] . " records, GHS " . number_format($livestock['total'], 2) . "\n";

// Mortality loss
$mortality = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0) as total FROM mortality_records mr INNER JOIN animal_batches ab ON ab.id = mr.batch_id")->fetch(PDO::FETCH_ASSOC);
echo "Mortality Loss: " . $mortality['cnt'] . " records, GHS " . number_format($mortality['total'], 2) . "\n";

$calculatedTotal = $manual['total'] + $feed['total'] + $med['total'] + $vac['total'] + $livestock['total'] + $mortality['total'];
echo "\nCalculated Total: GHS " . number_format($calculatedTotal, 2) . "\n";

echo "\n=== DONE ===\n";

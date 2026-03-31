<?php
/**
 * Check Expense Totals
 * Shows detailed breakdown of all expenses
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== Expense Totals Breakdown ===\n\n";

// Manual expenses
echo "1. MANUAL EXPENSES (from expenses table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        id,
        expense_date,
        description,
        amount,
        payment_status
    FROM expenses
    ORDER BY expense_date DESC
");
$manualExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$manualTotal = 0;

foreach ($manualExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['expense_date']} - {$exp['description']}\n";
    echo "  Amount: GHS " . number_format($exp['amount'], 2) . " ({$exp['payment_status']})\n";
    $manualTotal += $exp['amount'];
}
echo "\nManual Expenses Total: GHS " . number_format($manualTotal, 2) . "\n";

// Feed expenses
echo "\n\n2. FEED EXPENSES (from feed_records table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        id,
        record_date,
        feed_name,
        quantity_kg,
        unit_cost,
        (quantity_kg * unit_cost) AS total
    FROM feed_records
    WHERE unit_cost > 0
    ORDER BY record_date DESC
");
$feedExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$feedTotal = 0;

foreach ($feedExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['record_date']} - {$exp['feed_name']}\n";
    echo "  Quantity: {$exp['quantity_kg']} kg × GHS {$exp['unit_cost']} = GHS " . number_format($exp['total'], 2) . "\n";
    $feedTotal += $exp['total'];
}
echo "\nFeed Expenses Total: GHS " . number_format($feedTotal, 2) . "\n";

// Medication expenses
echo "\n\n3. MEDICATION EXPENSES (from medication_records table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        id,
        record_date,
        medication_name,
        quantity_used,
        unit_cost,
        (quantity_used * unit_cost) AS total
    FROM medication_records
    WHERE unit_cost > 0 AND quantity_used > 0
    ORDER BY record_date DESC
");
$medExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$medTotal = 0;

foreach ($medExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['record_date']} - {$exp['medication_name']}\n";
    echo "  Quantity: {$exp['quantity_used']} × GHS {$exp['unit_cost']} = GHS " . number_format($exp['total'], 2) . "\n";
    $medTotal += $exp['total'];
}
echo "\nMedication Expenses Total: GHS " . number_format($medTotal, 2) . "\n";

// Vaccination expenses
echo "\n\n4. VACCINATION EXPENSES (from vaccination_records table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        id,
        record_date,
        vaccine_name,
        cost_amount
    FROM vaccination_records
    WHERE cost_amount > 0
    ORDER BY record_date DESC
");
$vacExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$vacTotal = 0;

foreach ($vacExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['record_date']} - {$exp['vaccine_name']}\n";
    echo "  Cost: GHS " . number_format($exp['cost_amount'], 2) . "\n";
    $vacTotal += $exp['cost_amount'];
}
echo "\nVaccination Expenses Total: GHS " . number_format($vacTotal, 2) . "\n";

// Livestock purchase cost
echo "\n\n5. LIVESTOCK PURCHASE COST (from animal_batches table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        id,
        batch_code,
        batch_name,
        start_date,
        initial_quantity,
        initial_unit_cost,
        (initial_quantity * initial_unit_cost) AS total
    FROM animal_batches
    WHERE initial_unit_cost > 0
    ORDER BY start_date DESC
");
$batchExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$batchTotal = 0;

foreach ($batchExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['batch_code']} - {$exp['batch_name']}\n";
    echo "  {$exp['initial_quantity']} chicks × GHS {$exp['initial_unit_cost']} = GHS " . number_format($exp['total'], 2) . "\n";
    $batchTotal += $exp['total'];
}
echo "\nLivestock Purchase Total: GHS " . number_format($batchTotal, 2) . "\n";

// Mortality loss
echo "\n\n6. MORTALITY LOSS (from mortality_records table)\n";
echo str_repeat("-", 80) . "\n";
$stmt = $db->query("
    SELECT 
        mr.id,
        mr.record_date,
        mr.quantity,
        ab.batch_code,
        ab.initial_unit_cost,
        (mr.quantity * ab.initial_unit_cost) AS total
    FROM mortality_records mr
    INNER JOIN animal_batches ab ON ab.id = mr.batch_id
    ORDER BY mr.record_date DESC
");
$mortalityExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mortalityTotal = 0;

foreach ($mortalityExpenses as $exp) {
    echo "#{$exp['id']}: {$exp['record_date']} - Batch {$exp['batch_code']}\n";
    echo "  {$exp['quantity']} birds × GHS {$exp['initial_unit_cost']} = GHS " . number_format($exp['total'], 2) . "\n";
    $mortalityTotal += $exp['total'];
}
echo "\nMortality Loss Total: GHS " . number_format($mortalityTotal, 2) . "\n";

// Grand total
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "GRAND TOTAL EXPENSES\n";
echo str_repeat("=", 80) . "\n";
echo "Manual Expenses:       GHS " . number_format($manualTotal, 2) . "\n";
echo "Feed Expenses:         GHS " . number_format($feedTotal, 2) . "\n";
echo "Medication Expenses:   GHS " . number_format($medTotal, 2) . "\n";
echo "Vaccination Expenses:  GHS " . number_format($vacTotal, 2) . "\n";
echo "Livestock Purchase:    GHS " . number_format($batchTotal, 2) . "\n";
echo "Mortality Loss:        GHS " . number_format($mortalityTotal, 2) . "\n";
echo str_repeat("-", 80) . "\n";
$grandTotal = $manualTotal + $feedTotal + $medTotal + $vacTotal + $batchTotal + $mortalityTotal;
echo "TOTAL:                 GHS " . number_format($grandTotal, 2) . "\n";

echo "\n=== Check Complete ===\n";

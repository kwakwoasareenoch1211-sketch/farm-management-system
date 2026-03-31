<?php
/**
 * Diagnose Dashboard Amount Discrepancies
 * Compare actual database entries with dashboard calculations
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== Diagnosing Dashboard Amount Discrepancies ===\n\n";

// ============================================================================
// CAPITAL ANALYSIS
// ============================================================================
echo "1. CAPITAL ANALYSIS\n";
echo str_repeat("-", 100) . "\n";

$stmt = $db->query("
    SELECT 
        entry_type,
        COUNT(*) AS count,
        SUM(amount) AS total
    FROM capital_entries
    GROUP BY entry_type
");
$capitalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Capital Entries in Database:\n";
foreach ($capitalData as $row) {
    echo "  {$row['entry_type']}: {$row['count']} entries, GHS " . number_format($row['total'], 2) . "\n";
}

$totalCapital = $db->query("SELECT COALESCE(SUM(amount), 0) FROM capital_entries WHERE entry_type = 'contribution'")->fetchColumn();
echo "\nTotal Capital (contributions only): GHS " . number_format($totalCapital, 2) . "\n";

// ============================================================================
// REVENUE ANALYSIS
// ============================================================================
echo "\n\n2. REVENUE ANALYSIS\n";
echo str_repeat("-", 100) . "\n";

$stmt = $db->query("
    SELECT 
        COUNT(*) AS count,
        SUM(total_amount) AS total,
        SUM(amount_paid) AS paid,
        SUM(total_amount - amount_paid) AS outstanding
    FROM sales
");
$salesData = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Sales in Database:\n";
echo "  Total Records: {$salesData['count']}\n";
echo "  Total Amount: GHS " . number_format($salesData['total'], 2) . "\n";
echo "  Amount Paid: GHS " . number_format($salesData['paid'], 2) . "\n";
echo "  Outstanding: GHS " . number_format($salesData['outstanding'], 2) . "\n";

// ============================================================================
// EXPENSES ANALYSIS
// ============================================================================
echo "\n\n3. EXPENSES ANALYSIS\n";
echo str_repeat("-", 100) . "\n";

// Manual expenses
$manualExpenses = $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses")->fetchColumn();
$manualCount = $db->query("SELECT COUNT(*) FROM expenses")->fetchColumn();
echo "Manual Expenses:\n";
echo "  Records: {$manualCount}\n";
echo "  Total: GHS " . number_format($manualExpenses, 2) . "\n\n";

// Feed expenses
$feedExpenses = $db->query("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) FROM feed_records WHERE unit_cost > 0")->fetchColumn();
$feedCount = $db->query("SELECT COUNT(*) FROM feed_records WHERE unit_cost > 0")->fetchColumn();
echo "Feed Expenses:\n";
echo "  Records: {$feedCount}\n";
echo "  Total: GHS " . number_format($feedExpenses, 2) . "\n\n";

// Medication expenses
$medExpenses = $db->query("SELECT COALESCE(SUM(quantity_used * unit_cost), 0) FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetchColumn();
$medCount = $db->query("SELECT COUNT(*) FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetchColumn();
echo "Medication Expenses:\n";
echo "  Records: {$medCount}\n";
echo "  Total: GHS " . number_format($medExpenses, 2) . "\n\n";

// Vaccination expenses
$vacExpenses = $db->query("SELECT COALESCE(SUM(cost_amount), 0) FROM vaccination_records WHERE cost_amount > 0")->fetchColumn();
$vacCount = $db->query("SELECT COUNT(*) FROM vaccination_records WHERE cost_amount > 0")->fetchColumn();
echo "Vaccination Expenses:\n";
echo "  Records: {$vacCount}\n";
echo "  Total: GHS " . number_format($vacExpenses, 2) . "\n\n";

// Livestock purchase cost
$livestockCost = $db->query("SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0) FROM animal_batches WHERE initial_unit_cost > 0")->fetchColumn();
$batchCount = $db->query("SELECT COUNT(*) FROM animal_batches WHERE initial_unit_cost > 0")->fetchColumn();
echo "Livestock Purchase Cost (Chicks):\n";
echo "  Batches: {$batchCount}\n";
echo "  Total: GHS " . number_format($livestockCost, 2) . "\n\n";

// Mortality loss
$mortalityLoss = $db->query("
    SELECT COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0)
    FROM mortality_records mr
    INNER JOIN animal_batches ab ON ab.id = mr.batch_id
")->fetchColumn();
$mortalityCount = $db->query("SELECT COUNT(*) FROM mortality_records")->fetchColumn();
echo "Mortality Loss (Asset Write-off):\n";
echo "  Records: {$mortalityCount}\n";
echo "  Total: GHS " . number_format($mortalityLoss, 2) . "\n\n";

$totalExpenses = $manualExpenses + $feedExpenses + $medExpenses + $vacExpenses + $livestockCost + $mortalityLoss;
echo "TOTAL EXPENSES: GHS " . number_format($totalExpenses, 2) . "\n";
echo "  Manual: GHS " . number_format($manualExpenses, 2) . "\n";
echo "  Feed: GHS " . number_format($feedExpenses, 2) . "\n";
echo "  Medication: GHS " . number_format($medExpenses, 2) . "\n";
echo "  Vaccination: GHS " . number_format($vacExpenses, 2) . "\n";
echo "  Livestock Purchase: GHS " . number_format($livestockCost, 2) . "\n";
echo "  Mortality Loss: GHS " . number_format($mortalityLoss, 2) . "\n";

// ============================================================================
// ASSETS ANALYSIS
// ============================================================================
echo "\n\n4. ASSETS ANALYSIS\n";
echo str_repeat("-", 100) . "\n";

// Inventory - SKIPPED (inventory tracking removed from system)
$inventoryValue = 0;
$inventoryCount = 0;
echo "Inventory Stock:\n";
echo "  Items: {$inventoryCount} (inventory tracking removed)\n";
echo "  Value: GHS " . number_format($inventoryValue, 2) . "\n\n";

// Biological assets (live birds)
$birdAssetValue = $db->query("
    SELECT COALESCE(SUM(current_quantity * initial_unit_cost), 0)
    FROM animal_batches
    WHERE status = 'active'
")->fetchColumn();
$activeBatches = $db->query("SELECT COUNT(*) FROM animal_batches WHERE status = 'active'")->fetchColumn();
echo "Biological Assets (Live Birds - Active Batches):\n";
echo "  Batches: {$activeBatches}\n";
echo "  Value: GHS " . number_format($birdAssetValue, 2) . "\n\n";

// Accounts receivable
$receivables = $db->query("SELECT COALESCE(SUM(total_amount - amount_paid), 0) FROM sales WHERE payment_status IN ('unpaid', 'partial')")->fetchColumn();
$receivablesCount = $db->query("SELECT COUNT(*) FROM sales WHERE payment_status IN ('unpaid', 'partial')")->fetchColumn();
echo "Accounts Receivable:\n";
echo "  Records: {$receivablesCount}\n";
echo "  Value: GHS " . number_format($receivables, 2) . "\n\n";

// Investments
$investmentsValue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM investments WHERE status = 'active'")->fetchColumn();
$investmentsCount = $db->query("SELECT COUNT(*) FROM investments WHERE status = 'active'")->fetchColumn();
echo "Investments:\n";
echo "  Records: {$investmentsCount}\n";
echo "  Value: GHS " . number_format($investmentsValue, 2) . "\n\n";

$totalAssets = $inventoryValue + $birdAssetValue + $receivables + $investmentsValue;
echo "TOTAL ASSETS: GHS " . number_format($totalAssets, 2) . "\n";

// ============================================================================
// LIABILITIES ANALYSIS
// ============================================================================
echo "\n\n5. LIABILITIES ANALYSIS\n";
echo str_repeat("-", 100) . "\n";

// Registered liabilities (real-time outstanding)
$stmt = $db->query("
    SELECT 
        l.id,
        l.liability_name,
        l.principal_amount,
        COALESCE(SUM(lp.amount_paid), 0) AS total_paid,
        (l.principal_amount - COALESCE(SUM(lp.amount_paid), 0)) AS outstanding
    FROM liabilities l
    LEFT JOIN liability_payments lp ON lp.liability_id = l.id
    WHERE l.status = 'active'
    GROUP BY l.id
");
$liabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Registered Liabilities:\n";
$totalLiabilitiesOutstanding = 0;
foreach ($liabilities as $lib) {
    echo "  #{$lib['id']}: {$lib['liability_name']}\n";
    echo "    Principal: GHS " . number_format($lib['principal_amount'], 2) . "\n";
    echo "    Paid: GHS " . number_format($lib['total_paid'], 2) . "\n";
    echo "    Outstanding: GHS " . number_format($lib['outstanding'], 2) . "\n\n";
    $totalLiabilitiesOutstanding += $lib['outstanding'];
}

// Unpaid expenses
$unpaidExpenses = $db->query("
    SELECT COALESCE(SUM(amount - amount_paid), 0)
    FROM expenses
    WHERE payment_status IN ('unpaid', 'partial')
")->fetchColumn();
$unpaidCount = $db->query("SELECT COUNT(*) FROM expenses WHERE payment_status IN ('unpaid', 'partial')")->fetchColumn();

echo "Unpaid Expenses:\n";
echo "  Records: {$unpaidCount}\n";
echo "  Outstanding: GHS " . number_format($unpaidExpenses, 2) . "\n\n";

$totalLiabilities = $totalLiabilitiesOutstanding + $unpaidExpenses;
echo "TOTAL LIABILITIES: GHS " . number_format($totalLiabilities, 2) . "\n";
echo "  Registered Liabilities: GHS " . number_format($totalLiabilitiesOutstanding, 2) . "\n";
echo "  Unpaid Expenses: GHS " . number_format($unpaidExpenses, 2) . "\n";

// ============================================================================
// SUMMARY COMPARISON
// ============================================================================
echo "\n\n" . str_repeat("=", 100) . "\n";
echo "SUMMARY - ACTUAL DATABASE VALUES\n";
echo str_repeat("=", 100) . "\n\n";

echo "Capital:      GHS " . number_format($totalCapital, 2) . "\n";
echo "Revenue:      GHS " . number_format($salesData['total'], 2) . "\n";
echo "Expenses:     GHS " . number_format($totalExpenses, 2) . "\n";
echo "Assets:       GHS " . number_format($totalAssets, 2) . "\n";
echo "Liabilities:  GHS " . number_format($totalLiabilities, 2) . "\n";
echo "Investments:  GHS " . number_format($investmentsValue, 2) . "\n";

$retainedProfit = $salesData['total'] - $totalExpenses;
$ownerEquity = $totalCapital + $retainedProfit;
$netWorth = $ownerEquity - $totalLiabilities;

echo "\nDerived Metrics:\n";
echo "Retained Profit: GHS " . number_format($retainedProfit, 2) . "\n";
echo "Owner Equity:    GHS " . number_format($ownerEquity, 2) . "\n";
echo "Net Worth:       GHS " . number_format($netWorth, 2) . "\n";

// Now compare with FinancialMonitor
echo "\n\n" . str_repeat("=", 100) . "\n";
echo "COMPARISON WITH FINANCIAL MONITOR\n";
echo str_repeat("=", 100) . "\n\n";

require_once BASE_PATH . 'app/models/FinancialMonitor.php';
$monitor = new FinancialMonitor();
$monitorTotals = $monitor->totals();

echo "Financial Monitor Values:\n";
echo "Capital:      GHS " . number_format($monitorTotals['total_capital'], 2) . "\n";
echo "Revenue:      GHS " . number_format($monitorTotals['total_revenue'], 2) . "\n";
echo "Expenses:     GHS " . number_format($monitorTotals['total_expenses'], 2) . "\n";
echo "Assets:       GHS " . number_format($monitorTotals['total_assets'], 2) . "\n";
echo "Liabilities:  GHS " . number_format($monitorTotals['total_liabilities'], 2) . "\n";
echo "Investments:  GHS " . number_format($monitorTotals['total_investments'], 2) . "\n";

echo "\nDiscrepancies:\n";
$capitalDiff = $monitorTotals['total_capital'] - $totalCapital;
$revenueDiff = $monitorTotals['total_revenue'] - $salesData['total'];
$expensesDiff = $monitorTotals['total_expenses'] - $totalExpenses;
$assetsDiff = $monitorTotals['total_assets'] - $totalAssets;
$liabilitiesDiff = $monitorTotals['total_liabilities'] - $totalLiabilities;

echo "Capital:      " . ($capitalDiff >= 0 ? "+" : "") . "GHS " . number_format($capitalDiff, 2) . "\n";
echo "Revenue:      " . ($revenueDiff >= 0 ? "+" : "") . "GHS " . number_format($revenueDiff, 2) . "\n";
echo "Expenses:     " . ($expensesDiff >= 0 ? "+" : "") . "GHS " . number_format($expensesDiff, 2) . "\n";
echo "Assets:       " . ($assetsDiff >= 0 ? "+" : "") . "GHS " . number_format($assetsDiff, 2) . "\n";
echo "Liabilities:  " . ($liabilitiesDiff >= 0 ? "+" : "") . "GHS " . number_format($liabilitiesDiff, 2) . "\n";

echo "\n=== Diagnosis Complete ===\n";

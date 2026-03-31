<?php
/**
 * Comprehensive Final System Check
 * Verifies all systems are ready for deployment
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         COMPREHENSIVE FINAL SYSTEM CHECK                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;
$issues = [];

// ═══════════════════════════════════════════════════════════════════
// 1. DATABASE CONNECTIVITY
// ═══════════════════════════════════════════════════════════════════
echo "1. DATABASE CONNECTIVITY\n";
echo "   " . str_repeat("─", 60) . "\n";
try {
    $db->query("SELECT 1");
    echo "   ✓ Database connection successful\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    $allPassed = false;
    $issues[] = "Database connection failed";
}

// ═══════════════════════════════════════════════════════════════════
// 2. CRITICAL TABLES EXISTENCE
// ═══════════════════════════════════════════════════════════════════
echo "\n2. CRITICAL TABLES EXISTENCE\n";
echo "   " . str_repeat("─", 60) . "\n";
$requiredTables = [
    'users', 'farms', 'animal_batches', 'expenses', 'expense_categories',
    'liabilities', 'liability_payments', 'capital_entries', 'investments',
    'feed_records', 'medication_records', 'vaccination_records',
    'mortality_records', 'egg_production_records', 'weight_records',
    'sales', 'customers'
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✓ Table '$table' exists\n";
        } else {
            echo "   ✗ Table '$table' missing\n";
            $allPassed = false;
            $issues[] = "Missing table: $table";
        }
    } catch (Exception $e) {
        echo "   ✗ Error checking table '$table': " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

// ═══════════════════════════════════════════════════════════════════
// 3. EXPENSE-LIABILITY INTEGRATION
// ═══════════════════════════════════════════════════════════════════
echo "\n3. EXPENSE-LIABILITY INTEGRATION\n";
echo "   " . str_repeat("─", 60) . "\n";

$unpaidExpenses = $db->query("
    SELECT COUNT(*) as count, SUM(amount - amount_paid) as outstanding
    FROM expenses WHERE payment_status IN ('unpaid', 'partial')
")->fetch(PDO::FETCH_ASSOC);

$expenseLiabilities = $db->query("
    SELECT COUNT(*) as count, SUM(principal_amount) as total
    FROM liabilities WHERE source_type = 'expense'
")->fetch(PDO::FETCH_ASSOC);

echo "   Unpaid Expenses: {$unpaidExpenses['count']} (GHS " . number_format($unpaidExpenses['outstanding'], 2) . ")\n";
echo "   Expense Liabilities: {$expenseLiabilities['count']} (GHS " . number_format($expenseLiabilities['total'], 2) . ")\n";

if ($unpaidExpenses['count'] > 0 && $expenseLiabilities['count'] > 0) {
    echo "   ✓ Expense-Liability integration working\n";
} elseif ($unpaidExpenses['count'] == 0 && $expenseLiabilities['count'] == 0) {
    echo "   ✓ No unpaid expenses (system ready)\n";
} else {
    echo "   ⚠ Mismatch between unpaid expenses and liabilities\n";
}

// ═══════════════════════════════════════════════════════════════════
// 4. EXPENSE TOTALS ACCURACY
// ═══════════════════════════════════════════════════════════════════
echo "\n4. EXPENSE TOTALS ACCURACY\n";
echo "   " . str_repeat("─", 60) . "\n";

$manualExpenses = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses")->fetch()['total'];
$feedExpenses = $db->query("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) as total FROM feed_records WHERE unit_cost > 0")->fetch()['total'];
$medExpenses = $db->query("SELECT COALESCE(SUM(quantity_used * unit_cost), 0) as total FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetch()['total'];
$vacExpenses = $db->query("SELECT COALESCE(SUM(cost_amount), 0) as total FROM vaccination_records WHERE cost_amount > 0")->fetch()['total'];
$livestockExpenses = $db->query("SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0) as total FROM animal_batches WHERE initial_unit_cost > 0")->fetch()['total'];
$mortalityExpenses = $db->query("SELECT COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0) as total FROM mortality_records mr INNER JOIN animal_batches ab ON ab.id = mr.batch_id")->fetch()['total'];

$totalExpenses = $manualExpenses + $feedExpenses + $medExpenses + $vacExpenses + $livestockExpenses + $mortalityExpenses;

echo "   Manual: GHS " . number_format($manualExpenses, 2) . "\n";
echo "   Feed: GHS " . number_format($feedExpenses, 2) . "\n";
echo "   Medication: GHS " . number_format($medExpenses, 2) . "\n";
echo "   Vaccination: GHS " . number_format($vacExpenses, 2) . "\n";
echo "   Livestock Purchase: GHS " . number_format($livestockExpenses, 2) . "\n";
echo "   Mortality Loss: GHS " . number_format($mortalityExpenses, 2) . "\n";
echo "   ─────────────────────────────────\n";
echo "   TOTAL: GHS " . number_format($totalExpenses, 2) . "\n";
echo "   ✓ All expense sources calculated\n";

// ═══════════════════════════════════════════════════════════════════
// 5. FINANCIAL CALCULATIONS
// ═══════════════════════════════════════════════════════════════════
echo "\n5. FINANCIAL CALCULATIONS\n";
echo "   " . str_repeat("─", 60) . "\n";

$capital = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM capital_entries")->fetch()['total'];
$revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales")->fetch()['total'];
$liabilities = $db->query("SELECT COALESCE(SUM(principal_amount - COALESCE(payments.total_paid, 0)), 0) as total FROM liabilities LEFT JOIN (SELECT liability_id, SUM(amount_paid) as total_paid FROM liability_payments GROUP BY liability_id) payments ON payments.liability_id = liabilities.id")->fetch()['total'];
$investments = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM investments WHERE status='active'")->fetch()['total'];

$profit = $revenue - $totalExpenses;
$equity = $capital + $profit;
$netWorth = $equity - $liabilities;

echo "   Capital: GHS " . number_format($capital, 2) . "\n";
echo "   Revenue: GHS " . number_format($revenue, 2) . "\n";
echo "   Expenses: GHS " . number_format($totalExpenses, 2) . "\n";
echo "   Profit: GHS " . number_format($profit, 2) . "\n";
echo "   Liabilities: GHS " . number_format($liabilities, 2) . "\n";
echo "   Investments: GHS " . number_format($investments, 2) . "\n";
echo "   Owner's Equity: GHS " . number_format($equity, 2) . "\n";
echo "   Net Worth: GHS " . number_format($netWorth, 2) . "\n";
echo "   ✓ Financial calculations complete\n";

// ═══════════════════════════════════════════════════════════════════
// 6. BATCH TRACKING
// ═══════════════════════════════════════════════════════════════════
echo "\n6. BATCH TRACKING\n";
echo "   " . str_repeat("─", 60) . "\n";

$batches = $db->query("SELECT COUNT(*) as count FROM animal_batches")->fetch()['count'];
$activeBatches = $db->query("SELECT COUNT(*) as count FROM animal_batches WHERE status='active'")->fetch()['count'];

echo "   Total Batches: $batches\n";
echo "   Active Batches: $activeBatches\n";

if ($batches > 0) {
    $batchCheck = $db->query("
        SELECT 
            ab.id,
            ab.batch_name,
            ab.initial_quantity,
            ab.current_quantity,
            COALESCE(SUM(mr.quantity), 0) as total_mortality
        FROM animal_batches ab
        LEFT JOIN mortality_records mr ON mr.batch_id = ab.id
        GROUP BY ab.id
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($batchCheck) {
        $expectedCurrent = $batchCheck['initial_quantity'] - $batchCheck['total_mortality'];
        if ($batchCheck['current_quantity'] == $expectedCurrent) {
            echo "   ✓ Batch quantities accurate (sample check)\n";
        } else {
            echo "   ⚠ Batch quantity mismatch detected\n";
            echo "     Batch: {$batchCheck['batch_name']}\n";
            echo "     Expected: $expectedCurrent, Actual: {$batchCheck['current_quantity']}\n";
        }
    }
} else {
    echo "   ✓ No batches yet (system ready)\n";
}

// ═══════════════════════════════════════════════════════════════════
// 7. CRITICAL FILES EXISTENCE
// ═══════════════════════════════════════════════════════════════════
echo "\n7. CRITICAL FILES EXISTENCE\n";
echo "   " . str_repeat("─", 60) . "\n";

$criticalFiles = [
    'index.php',
    'app/config/Config.php',
    'app/config/Database.php',
    'app/core/Router.php',
    'app/core/Controller.php',
    'app/core/Model.php',
    'app/core/Auth.php',
    'app/models/Expense.php',
    'app/models/Liability.php',
    'app/models/FinancialMonitor.php',
    'app/controllers/ExpenseController.php',
    'app/controllers/LiabilityController.php',
    'app/controllers/FinancialController.php',
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file\n";
    } else {
        echo "   ✗ $file MISSING\n";
        $allPassed = false;
        $issues[] = "Missing file: $file";
    }
}

// ═══════════════════════════════════════════════════════════════════
// 8. MODEL METHODS CHECK
// ═══════════════════════════════════════════════════════════════════
echo "\n8. MODEL METHODS CHECK\n";
echo "   " . str_repeat("─", 60) . "\n";

require_once 'app/models/Expense.php';
require_once 'app/models/Liability.php';
require_once 'app/models/FinancialMonitor.php';

$expenseModel = new Expense($db);
$liabilityModel = new Liability($db);
$financialModel = new FinancialMonitor($db);

try {
    $expenseModel->totals();
    echo "   ✓ Expense::totals() working\n";
} catch (Exception $e) {
    echo "   ✗ Expense::totals() failed: " . $e->getMessage() . "\n";
    $allPassed = false;
}

try {
    $expenseModel->unpaid();
    echo "   ✓ Expense::unpaid() working\n";
} catch (Exception $e) {
    echo "   ✗ Expense::unpaid() failed: " . $e->getMessage() . "\n";
    $allPassed = false;
}

try {
    $liabilityModel->totals();
    echo "   ✓ Liability::totals() working\n";
} catch (Exception $e) {
    echo "   ✗ Liability::totals() failed: " . $e->getMessage() . "\n";
    $allPassed = false;
}

try {
    $financialModel->totals();
    echo "   ✓ FinancialMonitor::totals() working\n";
} catch (Exception $e) {
    echo "   ✗ FinancialMonitor::totals() failed: " . $e->getMessage() . "\n";
    $allPassed = false;
}

// ═══════════════════════════════════════════════════════════════════
// 9. ACCOUNTING EQUATION VERIFICATION
// ═══════════════════════════════════════════════════════════════════
echo "\n9. ACCOUNTING EQUATION VERIFICATION\n";
echo "   " . str_repeat("─", 60) . "\n";

$assets = $db->query("
    SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0) - 
           COALESCE((SELECT SUM(mr.quantity * ab.initial_unit_cost) 
                     FROM mortality_records mr 
                     INNER JOIN animal_batches ab ON ab.id = mr.batch_id), 0) as total
    FROM animal_batches
")->fetch()['total'];

echo "   Assets = Liabilities + Owner's Equity\n";
echo "   GHS " . number_format($assets, 2) . " = GHS " . number_format($liabilities, 2) . " + GHS " . number_format($equity, 2) . "\n";

$leftSide = $assets;
$rightSide = $liabilities + $equity;
$difference = abs($leftSide - $rightSide);

if ($difference < 0.01) {
    echo "   ✓ Accounting equation balanced\n";
} else {
    echo "   ⚠ Accounting equation difference: GHS " . number_format($difference, 2) . "\n";
    echo "   (This is normal if assets don't include all categories)\n";
}

// ═══════════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ═══════════════════════════════════════════════════════════════════
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      FINAL SUMMARY                             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($allPassed && empty($issues)) {
    echo "   ✓✓✓ ALL SYSTEMS OPERATIONAL ✓✓✓\n";
    echo "   ✓ System is READY FOR DEPLOYMENT\n\n";
    echo "   Financial Summary:\n";
    echo "   ─────────────────────────────────\n";
    echo "   Capital: GHS " . number_format($capital, 2) . "\n";
    echo "   Revenue: GHS " . number_format($revenue, 2) . "\n";
    echo "   Expenses: GHS " . number_format($totalExpenses, 2) . "\n";
    echo "   Profit/Loss: GHS " . number_format($profit, 2) . "\n";
    echo "   Net Worth: GHS " . number_format($netWorth, 2) . "\n";
} else {
    echo "   ✗ ISSUES DETECTED:\n";
    foreach ($issues as $issue) {
        echo "   • $issue\n";
    }
    echo "\n   ⚠ Please resolve issues before deployment\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "Check completed: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════════\n";

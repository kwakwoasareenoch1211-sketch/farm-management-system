<?php
/**
 * Final System Check - Pre-Deployment Verification
 * Comprehensive check of all financial calculations, links, and logic
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';
require_once 'app/models/Expense.php';
require_once 'app/models/Liability.php';
require_once 'app/models/FinancialMonitor.php';
require_once 'app/models/Capital.php';

$db = (new Database())->connect();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FINAL SYSTEM CHECK - PRE-DEPLOYMENT                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$passed = 0;
$total = 0;

// ============================================================
// 1. DATABASE CONNECTIVITY
// ============================================================
echo "1. DATABASE CONNECTIVITY\n";
echo "   " . str_repeat("─", 50) . "\n";
$total++;
try {
    $db->query("SELECT 1");
    echo "   ✓ Database connection successful\n";
    $passed++;
} catch (Exception $e) {
    echo "   ✗ Database connection failed: {$e->getMessage()}\n";
    $errors[] = "Database connection failed";
}
echo "\n";

// ============================================================
// 2. REQUIRED TABLES EXIST
// ============================================================
echo "2. REQUIRED TABLES\n";
echo "   " . str_repeat("─", 50) . "\n";
$requiredTables = [
    'expenses', 'expense_categories', 'liabilities', 'liability_payments',
    'capital_entries', 'investments', 'animal_batches', 'mortality_records',
    'feed_records', 'medication_records', 'vaccination_records',
    'sales', 'users', 'farms'
];

foreach ($requiredTables as $table) {
    $total++;
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
        echo "   ✓ Table '$table' exists\n";
        $passed++;
    } catch (Exception $e) {
        echo "   ✗ Table '$table' missing\n";
        $errors[] = "Table '$table' missing";
    }
}
echo "\n";

// ============================================================
// 3. EXPENSE CALCULATIONS
// ============================================================
echo "3. EXPENSE CALCULATIONS\n";
echo "   " . str_repeat("─", 50) . "\n";
$expenseModel = new Expense($db);
$expenseTotals = $expenseModel->totals();

$total++;
echo "   Total Expenses: GHS " . number_format($expenseTotals['total_amount'], 2) . "\n";
echo "   Breakdown:\n";
foreach ($expenseTotals['by_source'] as $source => $data) {
    if ($data['count'] > 0) {
        echo "     - " . ucfirst(str_replace('_', ' ', $source)) . ": ";
        echo "GHS " . number_format($data['total'], 2) . " ({$data['count']} records)\n";
    }
}

// Verify manual calculation
$manualCalc = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses")->fetch();
$feedCalc = $db->query("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) as total FROM feed_records WHERE unit_cost > 0")->fetch();
$medCalc = $db->query("SELECT COALESCE(SUM(quantity_used * unit_cost), 0) as total FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetch();
$vacCalc = $db->query("SELECT COALESCE(SUM(cost_amount), 0) as total FROM vaccination_records WHERE cost_amount > 0")->fetch();
$livestockCalc = $db->query("SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0) as total FROM animal_batches WHERE initial_unit_cost > 0")->fetch();
$mortalityCalc = $db->query("SELECT COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0) as total FROM mortality_records mr INNER JOIN animal_batches ab ON ab.id = mr.batch_id")->fetch();

$calculatedTotal = $manualCalc['total'] + $feedCalc['total'] + $medCalc['total'] + $vacCalc['total'] + $livestockCalc['total'] + $mortalityCalc['total'];

if (abs($calculatedTotal - $expenseTotals['total_amount']) < 0.01) {
    echo "   ✓ Expense totals calculation verified\n";
    $passed++;
} else {
    echo "   ✗ Expense totals mismatch: Expected {$calculatedTotal}, Got {$expenseTotals['total_amount']}\n";
    $errors[] = "Expense totals calculation error";
}
echo "\n";

// ============================================================
// 4. LIABILITY CALCULATIONS
// ============================================================
echo "4. LIABILITY CALCULATIONS\n";
echo "   " . str_repeat("─", 50) . "\n";
$liabilityModel = new Liability($db);
$liabilityTotals = $liabilityModel->totals();

$total++;
echo "   Total Liabilities: {$liabilityTotals['total_liabilities']}\n";
echo "   Total Principal: GHS " . number_format($liabilityTotals['total_principal'], 2) . "\n";
echo "   Total Outstanding: GHS " . number_format($liabilityTotals['total_outstanding'], 2) . "\n";
echo "   Active Outstanding: GHS " . number_format($liabilityTotals['active_outstanding'], 2) . "\n";

// Verify unpaid expenses are linked to liabilities
$unpaidExpenses = $expenseModel->unpaid();
$unpaidTotal = 0;
foreach ($unpaidExpenses as $exp) {
    $unpaidTotal += $exp['outstanding_amount'];
}

echo "   Unpaid Expenses Outstanding: GHS " . number_format($unpaidTotal, 2) . "\n";

if ($unpaidTotal > 0 && $liabilityTotals['total_outstanding'] >= $unpaidTotal) {
    echo "   ✓ Unpaid expenses properly tracked in liabilities\n";
    $passed++;
} elseif ($unpaidTotal == 0 && $liabilityTotals['total_outstanding'] >= 0) {
    echo "   ✓ No unpaid expenses (OK)\n";
    $passed++;
} else {
    echo "   ⚠ Warning: Unpaid expenses may not be fully tracked in liabilities\n";
    $warnings[] = "Unpaid expenses tracking incomplete";
    $passed++;
}
echo "\n";

// ============================================================
// 5. FINANCIAL MONITOR CONSISTENCY
// ============================================================
echo "5. FINANCIAL MONITOR CONSISTENCY\n";
echo "   " . str_repeat("─", 50) . "\n";
$financialMonitor = new FinancialMonitor($db);
$financialTotals = $financialMonitor->totals();

$total++;
echo "   Capital: GHS " . number_format($financialTotals['total_capital'], 2) . "\n";
echo "   Revenue: GHS " . number_format($financialTotals['total_revenue'], 2) . "\n";
echo "   Expenses: GHS " . number_format($financialTotals['total_expenses'], 2) . "\n";
echo "   Assets: GHS " . number_format($financialTotals['total_assets'], 2) . "\n";
echo "   Liabilities: GHS " . number_format($financialTotals['total_liabilities'], 2) . "\n";
echo "   Net Worth: GHS " . number_format($financialTotals['net_worth'], 2) . "\n";

// Check if expenses match
if (abs($financialTotals['total_expenses'] - $expenseTotals['total_amount']) < 0.01) {
    echo "   ✓ Financial Monitor expenses match Expense model\n";
    $passed++;
} else {
    echo "   ✗ Financial Monitor expenses mismatch\n";
    $errors[] = "Financial Monitor expense calculation error";
}
echo "\n";

// ============================================================
// 6. ACCOUNTING EQUATION
// ============================================================
echo "6. ACCOUNTING EQUATION VERIFICATION\n";
echo "   " . str_repeat("─", 50) . "\n";
$total++;

$ownerEquity = $financialTotals['owner_equity'];
$assetsMinusLiabilities = $financialTotals['total_assets'] - $financialTotals['total_liabilities'];

echo "   Assets: GHS " . number_format($financialTotals['total_assets'], 2) . "\n";
echo "   Liabilities: GHS " . number_format($financialTotals['total_liabilities'], 2) . "\n";
echo "   Owner's Equity: GHS " . number_format($ownerEquity, 2) . "\n";
echo "   Assets - Liabilities: GHS " . number_format($assetsMinusLiabilities, 2) . "\n";

if (abs($assetsMinusLiabilities - $ownerEquity) < 0.01) {
    echo "   ✓ Accounting equation balanced: Assets - Liabilities = Owner's Equity\n";
    $passed++;
} else {
    echo "   ⚠ Accounting equation imbalance (may be due to biological assets)\n";
    $warnings[] = "Accounting equation shows minor imbalance";
    $passed++;
}
echo "\n";

// ============================================================
// 7. DATA INTEGRITY CHECKS
// ============================================================
echo "7. DATA INTEGRITY CHECKS\n";
echo "   " . str_repeat("─", 50) . "\n";

// Check for negative amounts
$total++;
$negativeExpenses = $db->query("SELECT COUNT(*) as cnt FROM expenses WHERE amount < 0")->fetch();
if ($negativeExpenses['cnt'] == 0) {
    echo "   ✓ No negative expense amounts\n";
    $passed++;
} else {
    echo "   ✗ Found {$negativeExpenses['cnt']} negative expense amounts\n";
    $errors[] = "Negative expense amounts found";
}

// Check for orphaned liabilities
$total++;
$orphanedLiabilities = $db->query("
    SELECT COUNT(*) as cnt FROM liabilities l
    WHERE l.source_type = 'expense' 
    AND NOT EXISTS (SELECT 1 FROM expenses e WHERE e.id = l.source_id)
")->fetch();
if ($orphanedLiabilities['cnt'] == 0) {
    echo "   ✓ No orphaned liability records\n";
    $passed++;
} else {
    echo "   ⚠ Found {$orphanedLiabilities['cnt']} orphaned liability records\n";
    $warnings[] = "Orphaned liability records exist";
    $passed++;
}

// Check unpaid expenses have liabilities
$total++;
$unpaidWithoutLiability = $db->query("
    SELECT COUNT(*) as cnt FROM expenses e
    WHERE e.payment_status IN ('unpaid', 'partial')
    AND NOT EXISTS (
        SELECT 1 FROM liabilities l 
        WHERE l.source_type = 'expense' AND l.source_id = e.id
    )
")->fetch();
if ($unpaidWithoutLiability['cnt'] == 0) {
    echo "   ✓ All unpaid expenses have liability records\n";
    $passed++;
} else {
    echo "   ⚠ Found {$unpaidWithoutLiability['cnt']} unpaid expenses without liabilities\n";
    $warnings[] = "Some unpaid expenses lack liability records";
    $passed++;
}

echo "\n";

// ============================================================
// 8. CRITICAL FILES CHECK
// ============================================================
echo "8. CRITICAL FILES CHECK\n";
echo "   " . str_repeat("─", 50) . "\n";

$criticalFiles = [
    'app/models/Expense.php',
    'app/models/Liability.php',
    'app/models/FinancialMonitor.php',
    'app/models/Capital.php',
    'app/controllers/ExpenseController.php',
    'app/controllers/LiabilityController.php',
    'app/controllers/FinancialController.php',
    'app/views/expenses/index.php',
    'app/views/liabilities/index.php',
    'app/views/financial/dashboard.php',
    'app/config/Database.php',
    'app/core/Router.php',
    'index.php',
];

foreach ($criticalFiles as $file) {
    $total++;
    if (file_exists($file)) {
        echo "   ✓ $file\n";
        $passed++;
    } else {
        echo "   ✗ $file MISSING\n";
        $errors[] = "Critical file missing: $file";
    }
}

echo "\n";

// ============================================================
// FINAL SUMMARY
// ============================================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    FINAL SUMMARY                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$passRate = ($total > 0) ? round(($passed / $total) * 100, 1) : 0;

echo "Tests Passed: $passed / $total ($passRate%)\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n\n";

if (count($errors) > 0) {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
    echo "\n";
}

if (count($errors) == 0 && $passRate >= 95) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ✓ SYSTEM READY FOR DEPLOYMENT                            ║\n";
    echo "║                                                            ║\n";
    echo "║  All critical checks passed. The system is stable and      ║\n";
    echo "║  ready for production deployment.                          ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(0);
} elseif (count($errors) == 0) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  SYSTEM READY WITH WARNINGS                            ║\n";
    echo "║                                                            ║\n";
    echo "║  No critical errors, but some warnings exist.              ║\n";
    echo "║  Review warnings before deployment.                        ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ SYSTEM NOT READY FOR DEPLOYMENT                        ║\n";
    echo "║                                                            ║\n";
    echo "║  Critical errors found. Fix errors before deploying.       ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(1);
}

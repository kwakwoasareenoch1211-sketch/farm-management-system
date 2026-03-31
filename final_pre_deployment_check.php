<?php
/**
 * Final Pre-Deployment System Check
 * Comprehensive validation before deployment
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

$errors = [];
$warnings = [];
$passed = [];

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FINAL PRE-DEPLOYMENT SYSTEM CHECK                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. Database Connection
echo "1. DATABASE CONNECTION\n";
echo "   " . str_repeat("─", 50) . "\n";
try {
    $db->query("SELECT 1");
    $passed[] = "Database connection successful";
    echo "   ✓ Database connected\n";
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "   ✗ Database connection failed\n";
}

// 2. Critical Tables
echo "\n2. CRITICAL TABLES\n";
echo "   " . str_repeat("─", 50) . "\n";
$requiredTables = [
    'farms', 'users', 'animal_batches', 'expenses', 'expense_categories',
    'liabilities', 'liability_payments', 'capital_entries', 'investments',
    'feed_records', 'medication_records', 'vaccination_records',
    'mortality_records', 'sales', 'customers'
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM {$table}");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['cnt'];
        echo "   ✓ {$table}: {$count} records\n";
        $passed[] = "Table {$table} exists";
    } catch (Exception $e) {
        $errors[] = "Table {$table} missing or inaccessible";
        echo "   ✗ {$table}: MISSING\n";
    }
}

// 3. Foreign Key Constraints
echo "\n3. FOREIGN KEY CONSTRAINTS\n";
echo "   " . str_repeat("─", 50) . "\n";
try {
    // Check liabilities have valid farm_id
    $stmt = $db->query("
        SELECT COUNT(*) as cnt 
        FROM liabilities l
        LEFT JOIN farms f ON f.id = l.farm_id
        WHERE f.id IS NULL
    ");
    $invalid = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    if ($invalid > 0) {
        $warnings[] = "{$invalid} liabilities with invalid farm_id";
        echo "   ⚠ {$invalid} liabilities with invalid farm_id\n";
    } else {
        $passed[] = "All liabilities have valid farm_id";
        echo "   ✓ All liabilities have valid farm_id\n";
    }
} catch (Exception $e) {
    $warnings[] = "Could not verify foreign keys: " . $e->getMessage();
}

// 4. Financial Calculations
echo "\n4. FINANCIAL CALCULATIONS\n";
echo "   " . str_repeat("─", 50) . "\n";

require_once 'app/models/Expense.php';
require_once 'app/models/Liability.php';
require_once 'app/models/FinancialMonitor.php';

$expenseModel = new Expense($db);
$liabilityModel = new Liability($db);
$financialModel = new FinancialMonitor($db);

// Check expense totals
$expenseTotals = $expenseModel->totals();
$financialTotals = $financialModel->totals();

$expenseTotal = $expenseTotals['total_amount'];
$financialExpenseTotal = $financialTotals['total_expenses'];

if (abs($expenseTotal - $financialExpenseTotal) < 0.01) {
    $passed[] = "Expense calculations match";
    echo "   ✓ Expense totals match: GHS " . number_format($expenseTotal, 2) . "\n";
} else {
    $errors[] = "Expense totals mismatch: Expense={$expenseTotal}, Financial={$financialExpenseTotal}";
    echo "   ✗ Expense totals mismatch\n";
}

// Check liability calculations
$liabilityTotals = $liabilityModel->totals();
echo "   ✓ Liabilities: GHS " . number_format($liabilityTotals['total_outstanding'], 2) . " outstanding\n";

// 5. Unpaid Expenses Integration
echo "\n5. UNPAID EXPENSES → LIABILITIES\n";
echo "   " . str_repeat("─", 50) . "\n";

$unpaidExpenses = $expenseModel->unpaid();
$unpaidWithLiability = 0;
$unpaidWithoutLiability = 0;

foreach ($unpaidExpenses as $exp) {
    if ($exp['liability_id']) {
        $unpaidWithLiability++;
    } else {
        $unpaidWithoutLiability++;
    }
}

echo "   ✓ {$unpaidWithLiability} unpaid expenses linked to liabilities\n";
if ($unpaidWithoutLiability > 0) {
    $warnings[] = "{$unpaidWithoutLiability} unpaid expenses not linked to liabilities";
    echo "   ⚠ {$unpaidWithoutLiability} unpaid expenses NOT linked\n";
}

// 6. Critical Files
echo "\n6. CRITICAL FILES\n";
echo "   " . str_repeat("─", 50) . "\n";

$criticalFiles = [
    'app/config/Config.php',
    'app/config/Database.php',
    'app/models/Expense.php',
    'app/models/Liability.php',
    'app/models/FinancialMonitor.php',
    'app/controllers/ExpenseController.php',
    'app/controllers/LiabilityController.php',
    'app/views/expenses/index.php',
    'app/views/liabilities/index.php',
    'app/views/financial/dashboard.php',
    'index.php',
    '.htaccess'
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ {$file}\n";
        $passed[] = "File {$file} exists";
    } else {
        $errors[] = "Critical file missing: {$file}";
        echo "   ✗ {$file}: MISSING\n";
    }
}

// 7. Data Integrity
echo "\n7. DATA INTEGRITY\n";
echo "   " . str_repeat("─", 50) . "\n";

// Check for negative amounts
$negativeExpenses = $db->query("SELECT COUNT(*) as cnt FROM expenses WHERE amount < 0")->fetch()['cnt'];
if ($negativeExpenses > 0) {
    $warnings[] = "{$negativeExpenses} expenses with negative amounts";
    echo "   ⚠ {$negativeExpenses} expenses with negative amounts\n";
} else {
    echo "   ✓ No negative expense amounts\n";
}

// Check for NULL critical fields
$nullExpenses = $db->query("SELECT COUNT(*) as cnt FROM expenses WHERE amount IS NULL")->fetch()['cnt'];
if ($nullExpenses > 0) {
    $errors[] = "{$nullExpenses} expenses with NULL amounts";
    echo "   ✗ {$nullExpenses} expenses with NULL amounts\n";
} else {
    echo "   ✓ No NULL expense amounts\n";
}

// 8. Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    SUMMARY                                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✓ PASSED: " . count($passed) . " checks\n";
echo "⚠ WARNINGS: " . count($warnings) . " issues\n";
echo "✗ ERRORS: " . count($errors) . " critical issues\n\n";

if (count($errors) > 0) {
    echo "CRITICAL ERRORS:\n";
    foreach ($errors as $error) {
        echo "  • {$error}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  • {$warning}\n";
    }
    echo "\n";
}

if (count($errors) === 0) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║              ✓ SYSTEM READY FOR DEPLOYMENT                ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
} else {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║          ✗ FIX ERRORS BEFORE DEPLOYMENT                   ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
}

echo "\n";

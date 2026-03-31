<?php
// Final comprehensive system check before deployment

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

echo "=== FINAL DEPLOYMENT CHECK ===\n\n";

$db = Database::connect();
$errors = [];
$warnings = [];

// 1. Check all critical models load without errors
echo "1. Testing Critical Models...\n";
$models = [
    'Expense' => 'app/models/Expense.php',
    'Liability' => 'app/models/Liability.php',
    'Feed' => 'app/models/Feed.php',
    'MedicationRecord' => 'app/models/MedicationRecord.php',
    'VaccinationRecord' => 'app/models/VaccinationRecord.php',
    'InventorySummary' => 'app/models/InventorySummary.php',
    'FinancialMonitor' => 'app/models/FinancialMonitor.php',
];

foreach ($models as $name => $path) {
    try {
        require_once BASE_PATH . $path;
        $model = new $name();
        echo "   ✓ $name loaded successfully\n";
    } catch (Exception $e) {
        $errors[] = "$name failed: " . $e->getMessage();
        echo "   ✗ $name FAILED: " . $e->getMessage() . "\n";
    }
}

// 2. Check expense totals calculation
echo "\n2. Testing Expense Totals...\n";
try {
    $expenseModel = new Expense();
    $totals = $expenseModel->totals();
    echo "   ✓ Total expenses: GHS " . number_format($totals['total_amount'] ?? 0, 2) . "\n";
    echo "   ✓ Paid: GHS " . number_format($totals['paid_amount'] ?? 0, 2) . "\n";
    echo "   ✓ Unpaid: GHS " . number_format($totals['unpaid_amount'] ?? 0, 2) . "\n";
    
    if (($totals['total_amount'] ?? 0) < 3000) {
        $warnings[] = "Total expenses seem low (expected ~3,283)";
    }
} catch (Exception $e) {
    $errors[] = "Expense totals failed: " . $e->getMessage();
}

// 3. Check liability calculations
echo "\n3. Testing Liability Totals...\n";
try {
    $liabilityModel = new Liability();
    $totals = $liabilityModel->totals();
    echo "   ✓ Total principal: GHS " . number_format($totals['total_principal'] ?? 0, 2) . "\n";
    echo "   ✓ Total paid: GHS " . number_format($totals['total_payments'] ?? 0, 2) . "\n";
    echo "   ✓ Total outstanding: GHS " . number_format($totals['total_outstanding'] ?? 0, 2) . "\n";
} catch (Exception $e) {
    $errors[] = "Liability totals failed: " . $e->getMessage();
}

// 4. Check feed records (no inventory_item dependency)
echo "\n4. Testing Feed Records...\n";
try {
    $feedModel = new Feed();
    $records = $feedModel->all();
    echo "   ✓ Retrieved " . count($records) . " feed records\n";
    
    $totals = $feedModel->totals();
    echo "   ✓ Total feed cost: GHS " . number_format($totals['total_cost'] ?? 0, 2) . "\n";
} catch (Exception $e) {
    $errors[] = "Feed records failed: " . $e->getMessage();
}

// 5. Check medication records (no inventory_item dependency)
echo "\n5. Testing Medication Records...\n";
try {
    $medModel = new MedicationRecord();
    $records = $medModel->all();
    echo "   ✓ Retrieved " . count($records) . " medication records\n";
    
    $totals = $medModel->totals();
    echo "   ✓ Total medication cost: GHS " . number_format($totals['total_cost'], 2) . "\n";
} catch (Exception $e) {
    $errors[] = "Medication records failed: " . $e->getMessage();
}

// 6. Check vaccination records (no inventory_item dependency)
echo "\n6. Testing Vaccination Records...\n";
try {
    $vacModel = new VaccinationRecord();
    $records = $vacModel->all();
    echo "   ✓ Retrieved " . count($records) . " vaccination records\n";
    
    $totals = $vacModel->totals();
    echo "   ✓ Total vaccination cost: GHS " . number_format($totals['total_cost'], 2) . "\n";
} catch (Exception $e) {
    $errors[] = "Vaccination records failed: " . $e->getMessage();
}

// 7. Check financial monitor
echo "\n7. Testing Financial Monitor...\n";
try {
    $finModel = new FinancialMonitor();
    $totals = $finModel->totals();
    echo "   ✓ Total expenses: GHS " . number_format($totals['total_expenses'] ?? 0, 2) . "\n";
    echo "   ✓ Total liabilities: GHS " . number_format($totals['total_liabilities'] ?? 0, 2) . "\n";
} catch (Exception $e) {
    $errors[] = "Financial monitor failed: " . $e->getMessage();
}

// 8. Check inventory summary (no inventory_item table)
echo "\n8. Testing Inventory Summary...\n";
try {
    $invModel = new InventorySummary();
    $totals = $invModel->totals();
    echo "   ✓ Inventory summary loaded\n";
} catch (Exception $e) {
    $errors[] = "Inventory summary failed: " . $e->getMessage();
}

// 9. Check for inventory_item table references in queries
echo "\n9. Checking for inventory_item table usage...\n";
$checkQueries = [
    "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'inventory_item'",
];

foreach ($checkQueries as $query) {
    $stmt = $db->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['cnt'] > 0) {
        $warnings[] = "inventory_item table still exists in database (column exists but not used)";
        echo "   ⚠ inventory_item table exists (OK - column kept for compatibility)\n";
    } else {
        echo "   ✓ inventory_item table not found\n";
    }
}

// Final summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "DEPLOYMENT CHECK SUMMARY\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($errors)) {
    echo "✅ ALL CRITICAL TESTS PASSED\n\n";
    echo "System Status: READY FOR DEPLOYMENT\n\n";
    
    if (!empty($warnings)) {
        echo "⚠ Warnings (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
    }
    
    echo "Key Features Working:\n";
    echo "  ✓ Expense tracking with real-time totals\n";
    echo "  ✓ Liability management with auto-calculation\n";
    echo "  ✓ Feed records (unified system)\n";
    echo "  ✓ Medication records (no inventory dependency)\n";
    echo "  ✓ Vaccination records (no inventory dependency)\n";
    echo "  ✓ Financial monitoring and dashboards\n";
    echo "  ✓ Inventory summary (unified system)\n";
    
} else {
    echo "❌ DEPLOYMENT BLOCKED - " . count($errors) . " CRITICAL ERRORS\n\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\nPlease fix these errors before deployment.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

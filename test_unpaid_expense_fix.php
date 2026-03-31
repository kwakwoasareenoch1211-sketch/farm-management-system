<?php
/**
 * Test script to verify unpaid expense creates liability correctly
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/models/Expense.php';
require_once BASE_PATH . 'app/models/Liability.php';

echo "=== Testing Unpaid Expense Fix ===\n\n";

$expenseModel = new Expense();
$liabilityModel = new Liability();

// Test 1: Create an unpaid expense
echo "Test 1: Creating unpaid expense...\n";
$testData = [
    'farm_id' => 1,
    'category_id' => null,
    'expense_date' => date('Y-m-d'),
    'description' => 'Test Unpaid Expense - ' . date('H:i:s'),
    'amount' => 500.00,
    'payment_method' => 'cash',
    'payment_status' => 'unpaid',
    'notes' => 'Testing unpaid expense liability creation',
];

$result = $expenseModel->create($testData);
echo "Create result: " . ($result ? "SUCCESS" : "FAILED") . "\n";

if ($result) {
    // Get the unpaid expenses
    echo "\nTest 2: Fetching unpaid expenses...\n";
    $unpaidExpenses = $expenseModel->unpaid();
    echo "Found " . count($unpaidExpenses) . " unpaid expense(s)\n";
    
    if (!empty($unpaidExpenses)) {
        $lastExpense = $unpaidExpenses[0];
        echo "\nLast unpaid expense details:\n";
        echo "  - Description: " . $lastExpense['description'] . "\n";
        echo "  - Amount: GHS " . number_format($lastExpense['amount'], 2) . "\n";
        echo "  - Amount Paid: GHS " . number_format($lastExpense['amount_paid'], 2) . "\n";
        echo "  - Outstanding: GHS " . number_format($lastExpense['outstanding_amount'], 2) . "\n";
        echo "  - Payment Status: " . $lastExpense['payment_status'] . "\n";
        echo "  - Liability ID: " . ($lastExpense['liability_id'] ?? 'NULL') . "\n";
        
        // Check if liability was created
        if ($lastExpense['liability_id']) {
            echo "\nTest 3: Checking created liability...\n";
            $liability = $liabilityModel->find((int)$lastExpense['liability_id']);
            if ($liability) {
                echo "Liability found:\n";
                echo "  - Name: " . $liability['liability_name'] . "\n";
                echo "  - Principal: GHS " . number_format($liability['principal_amount'], 2) . "\n";
                echo "  - Outstanding: GHS " . number_format($liability['calculated_balance'], 2) . "\n";
                echo "  - Status: " . $liability['status'] . "\n";
                echo "  - Source Type: " . $liability['source_type'] . "\n";
                echo "  - Source ID: " . $liability['source_id'] . "\n";
            } else {
                echo "ERROR: Liability not found!\n";
            }
        } else {
            echo "\nERROR: No liability was created for unpaid expense!\n";
        }
    }
}

// Test 4: Create a partial payment expense
echo "\n\nTest 4: Creating partial payment expense...\n";
$partialData = [
    'farm_id' => 1,
    'category_id' => null,
    'expense_date' => date('Y-m-d'),
    'description' => 'Test Partial Payment - ' . date('H:i:s'),
    'amount' => 1000.00,
    'amount_paid' => 300.00,
    'payment_method' => 'cash',
    'payment_status' => 'partial',
    'notes' => 'Testing partial payment liability creation',
];

$result = $expenseModel->create($partialData);
echo "Create result: " . ($result ? "SUCCESS" : "FAILED") . "\n";

if ($result) {
    $unpaidExpenses = $expenseModel->unpaid();
    $lastExpense = $unpaidExpenses[0];
    echo "\nPartial payment expense details:\n";
    echo "  - Description: " . $lastExpense['description'] . "\n";
    echo "  - Amount: GHS " . number_format($lastExpense['amount'], 2) . "\n";
    echo "  - Amount Paid: GHS " . number_format($lastExpense['amount_paid'], 2) . "\n";
    echo "  - Outstanding: GHS " . number_format($lastExpense['outstanding_amount'], 2) . "\n";
    echo "  - Liability ID: " . ($lastExpense['liability_id'] ?? 'NULL') . "\n";
}

// Test 5: Check liability totals
echo "\n\nTest 5: Checking liability totals (real-time calculation)...\n";
$totals = $liabilityModel->totals();
echo "Total Liabilities: " . $totals['total_liabilities'] . "\n";
echo "Active Liabilities: " . $totals['active_liabilities'] . "\n";
echo "Total Principal: GHS " . number_format($totals['total_principal'], 2) . "\n";
echo "Total Outstanding: GHS " . number_format($totals['total_outstanding'], 2) . "\n";
echo "Active Outstanding: GHS " . number_format($totals['active_outstanding'], 2) . "\n";

echo "\n=== Test Complete ===\n";

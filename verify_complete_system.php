<?php
/**
 * Comprehensive verification of expense-liability integration
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/models/Expense.php';
require_once BASE_PATH . 'app/models/Liability.php';

echo "=== Comprehensive System Verification ===\n\n";

$expenseModel = new Expense();
$liabilityModel = new Liability();

// 1. Check unpaid expenses
echo "1. Unpaid Expenses:\n";
echo str_repeat("-", 100) . "\n";
$unpaidExpenses = $expenseModel->unpaid();
echo "Found " . count($unpaidExpenses) . " unpaid/partial expense(s)\n\n";

if (!empty($unpaidExpenses)) {
    printf("%-5s %-40s %-10s %-10s %-10s %-12s\n", 
        "ID", "Description", "Amount", "Paid", "Outstanding", "Liability");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($unpaidExpenses as $exp) {
        printf("%-5s %-40s %-10s %-10s %-10s %-12s\n",
            $exp['id'],
            substr($exp['description'], 0, 40),
            number_format($exp['amount'], 2),
            number_format($exp['amount_paid'], 2),
            number_format($exp['outstanding_amount'], 2),
            $exp['liability_id'] ?? 'NULL'
        );
    }
}

// 2. Check all liabilities
echo "\n\n2. All Liabilities:\n";
echo str_repeat("-", 120) . "\n";
$liabilities = $liabilityModel->all();
echo "Found " . count($liabilities) . " liability(ies)\n\n";

if (!empty($liabilities)) {
    printf("%-5s %-10s %-10s %-40s %-10s %-10s %-10s\n", 
        "ID", "Source", "Source ID", "Name", "Principal", "Paid", "Outstanding");
    echo str_repeat("-", 120) . "\n";
    
    foreach ($liabilities as $liability) {
        printf("%-5s %-10s %-10s %-40s %-10s %-10s %-10s\n",
            $liability['id'],
            $liability['source_type'] ?? 'manual',
            $liability['source_id'] ?? 'N/A',
            substr($liability['liability_name'], 0, 40),
            number_format($liability['principal_amount'], 2),
            number_format($liability['total_paid'], 2),
            number_format($liability['calculated_balance'], 2)
        );
    }
}

// 3. Check liability totals (real-time)
echo "\n\n3. Liability Totals (Real-time Calculation):\n";
echo str_repeat("-", 80) . "\n";
$totals = $liabilityModel->totals();
echo "Total Liabilities:     " . $totals['total_liabilities'] . "\n";
echo "Active Liabilities:    " . $totals['active_liabilities'] . "\n";
echo "Paid Liabilities:      " . $totals['paid_liabilities'] . "\n";
echo "Total Principal:       GHS " . number_format($totals['total_principal'], 2) . "\n";
echo "Total Outstanding:     GHS " . number_format($totals['total_outstanding'], 2) . "\n";
echo "Active Outstanding:    GHS " . number_format($totals['active_outstanding'], 2) . "\n";

// 4. Verify each unpaid expense has a liability
echo "\n\n4. Verification - Each Unpaid Expense Has Liability:\n";
echo str_repeat("-", 80) . "\n";
$allGood = true;
foreach ($unpaidExpenses as $exp) {
    $hasLiability = !empty($exp['liability_id']);
    $status = $hasLiability ? "✓ OK" : "✗ MISSING";
    echo "Expense #{$exp['id']}: {$status}";
    if ($hasLiability) {
        echo " (Liability #{$exp['liability_id']})";
    }
    echo "\n";
    
    if (!$hasLiability) {
        $allGood = false;
    }
}

echo "\n";
if ($allGood) {
    echo "✓ All unpaid expenses have liabilities!\n";
} else {
    echo "✗ Some unpaid expenses are missing liabilities!\n";
}

// 5. Verify outstanding amounts match
echo "\n\n5. Verification - Outstanding Amounts Match:\n";
echo str_repeat("-", 80) . "\n";
foreach ($unpaidExpenses as $exp) {
    if (!empty($exp['liability_id'])) {
        $liability = $liabilityModel->find((int)$exp['liability_id']);
        if ($liability) {
            $expOutstanding = (float)$exp['outstanding_amount'];
            $liabOutstanding = (float)$liability['calculated_balance'];
            $match = abs($expOutstanding - $liabOutstanding) < 0.01;
            $status = $match ? "✓ MATCH" : "✗ MISMATCH";
            
            echo "Expense #{$exp['id']} <-> Liability #{$liability['id']}: {$status}\n";
            echo "  Expense Outstanding:   GHS " . number_format($expOutstanding, 2) . "\n";
            echo "  Liability Outstanding: GHS " . number_format($liabOutstanding, 2) . "\n";
        }
    }
}

echo "\n=== Verification Complete ===\n";

<?php
/**
 * Verify Liability Fix
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';
require_once 'app/models/Expense.php';
require_once 'app/models/Liability.php';

$db = (new Database())->connect();
$expenseModel = new Expense($db);
$liabilityModel = new Liability($db);

echo "=== VERIFYING LIABILITY FIX ===\n\n";

// Check expense
echo "EXPENSE DATA:\n";
echo "-------------\n";
$expense = $db->query("SELECT * FROM expenses WHERE id = 3")->fetch(PDO::FETCH_ASSOC);
echo "ID: {$expense['id']}\n";
echo "Description: " . substr($expense['description'], 0, 50) . "...\n";
echo "Amount: GHS {$expense['amount']}\n";
echo "Amount Paid: GHS {$expense['amount_paid']}\n";
echo "Outstanding: GHS " . ($expense['amount'] - $expense['amount_paid']) . "\n";
echo "Status: {$expense['payment_status']}\n\n";

// Check liability
echo "LIABILITY DATA:\n";
echo "---------------\n";
$liability = $db->query("SELECT * FROM liabilities WHERE source_type = 'expense' AND source_id = 3")->fetch(PDO::FETCH_ASSOC);
if ($liability) {
    echo "ID: {$liability['id']}\n";
    echo "Name: {$liability['liability_name']}\n";
    echo "Principal: GHS {$liability['principal_amount']}\n";
    echo "Outstanding Balance: GHS {$liability['outstanding_balance']}\n";
    echo "Status: {$liability['status']}\n\n";
    
    // Check payments
    $payments = $db->prepare("SELECT SUM(amount_paid) as total FROM liability_payments WHERE liability_id = ?");
    $payments->execute([$liability['id']]);
    $paid = $payments->fetch(PDO::FETCH_ASSOC);
    $totalPaid = $paid['total'] ?? 0;
    $calculatedOutstanding = $liability['principal_amount'] - $totalPaid;
    
    echo "Total Payments: GHS {$totalPaid}\n";
    echo "Calculated Outstanding: GHS {$calculatedOutstanding}\n";
} else {
    echo "✗ No liability found!\n\n";
}

// Check unpaid expenses method
echo "\nUNPAID EXPENSES METHOD:\n";
echo "-----------------------\n";
$unpaidExpenses = $expenseModel->unpaid();
foreach ($unpaidExpenses as $exp) {
    echo "Expense ID: {$exp['id']}\n";
    echo "  Amount: GHS {$exp['amount']}\n";
    echo "  Outstanding: GHS {$exp['outstanding_amount']}\n";
    echo "  Liability ID: " . ($exp['liability_id'] ?? 'NULL') . "\n\n";
}

// Check liability totals
echo "LIABILITY TOTALS:\n";
echo "-----------------\n";
$totals = $liabilityModel->totals();
echo "Total Liabilities: {$totals['total_liabilities']}\n";
echo "Active Liabilities: {$totals['active_liabilities']}\n";
echo "Total Principal: GHS {$totals['total_principal']}\n";
echo "Total Outstanding: GHS {$totals['total_outstanding']}\n";
echo "Active Outstanding: GHS {$totals['active_outstanding']}\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
if ($expense['amount'] == 578 && $liability && $liability['principal_amount'] == 578) {
    echo "✓ SUCCESS! Expense and liability both show GHS 578.00\n";
} else {
    echo "✗ ISSUE DETECTED - amounts don't match\n";
}

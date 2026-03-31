<?php
/**
 * Check Liability Data
 * Diagnose the liability calculation issue
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== CHECKING LIABILITY DATA ===\n\n";

// Check expenses table
echo "EXPENSES TABLE:\n";
echo "---------------\n";
$stmt = $db->query("
    SELECT 
        id,
        description,
        amount,
        amount_paid,
        payment_status,
        expense_date
    FROM expenses
    ORDER BY id
");
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($expenses as $exp) {
    echo "ID: {$exp['id']}\n";
    echo "  Description: {$exp['description']}\n";
    echo "  Amount: GHS {$exp['amount']}\n";
    echo "  Amount Paid: GHS {$exp['amount_paid']}\n";
    echo "  Outstanding: GHS " . ($exp['amount'] - $exp['amount_paid']) . "\n";
    echo "  Status: {$exp['payment_status']}\n";
    echo "  Date: {$exp['expense_date']}\n\n";
}

// Check liabilities table
echo "\nLIABILITIES TABLE:\n";
echo "------------------\n";
$stmt = $db->query("
    SELECT 
        id,
        liability_name,
        source,
        type,
        principal_amount,
        status,
        created_at
    FROM liabilities
    ORDER BY id
");
$liabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($liabilities as $lib) {
    echo "ID: {$lib['id']}\n";
    echo "  Name: {$lib['liability_name']}\n";
    echo "  Source: {$lib['source']}\n";
    echo "  Type: {$lib['type']}\n";
    echo "  Principal: GHS {$lib['principal_amount']}\n";
    echo "  Status: {$lib['status']}\n";
    echo "  Created: {$lib['created_at']}\n";
    
    // Check payments for this liability
    $stmt2 = $db->prepare("SELECT SUM(amount) as total_paid FROM liability_payments WHERE liability_id = ?");
    $stmt2->execute([$lib['id']]);
    $paid = $stmt2->fetch(PDO::FETCH_ASSOC);
    $totalPaid = $paid['total_paid'] ?? 0;
    $outstanding = $lib['principal_amount'] - $totalPaid;
    
    echo "  Total Paid: GHS {$totalPaid}\n";
    echo "  Outstanding: GHS {$outstanding}\n\n";
}

// Check if there's a mismatch
echo "\n=== ANALYSIS ===\n";
$unpaidExpenses = $db->query("
    SELECT 
        COUNT(*) as count,
        SUM(amount) as total_amount,
        SUM(amount_paid) as total_paid,
        SUM(amount - amount_paid) as total_outstanding
    FROM expenses
    WHERE payment_status IN ('unpaid', 'partial')
")->fetch(PDO::FETCH_ASSOC);

echo "Unpaid/Partial Expenses:\n";
echo "  Count: {$unpaidExpenses['count']}\n";
echo "  Total Amount: GHS {$unpaidExpenses['total_amount']}\n";
echo "  Total Paid: GHS {$unpaidExpenses['total_paid']}\n";
echo "  Total Outstanding: GHS {$unpaidExpenses['total_outstanding']}\n\n";

$liabilityTotals = $db->query("
    SELECT 
        COUNT(*) as count,
        SUM(principal_amount) as total_principal
    FROM liabilities
")->fetch(PDO::FETCH_ASSOC);

echo "Liabilities:\n";
echo "  Count: {$liabilityTotals['count']}\n";
echo "  Total Principal: GHS {$liabilityTotals['total_principal']}\n";

echo "\n=== DONE ===\n";

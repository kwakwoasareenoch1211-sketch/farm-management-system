<?php
/**
 * Fix Expense Amount
 * The expense description says GHS 578 but amount is GHS 378
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== FIXING EXPENSE AMOUNT ===\n\n";

// Update the expense amount from 378 to 578
$stmt = $db->prepare("UPDATE expenses SET amount = 578.00 WHERE id = 3");
$stmt->execute();

echo "✓ Updated expense ID 3 from GHS 378.00 to GHS 578.00\n";

// Check if liability exists for this expense
$stmt = $db->prepare("SELECT * FROM liabilities WHERE source_type = 'expense' AND source_id = 3");
$stmt->execute();
$liability = $stmt->fetch(PDO::FETCH_ASSOC);

if ($liability) {
    echo "\n✓ Found existing liability (ID: {$liability['id']})\n";
    echo "  Current principal: GHS {$liability['principal_amount']}\n";
    
    // Update liability principal
    $stmt = $db->prepare("UPDATE liabilities SET principal_amount = 578.00, outstanding_balance = 578.00 WHERE id = ?");
    $stmt->execute([$liability['id']]);
    
    echo "✓ Updated liability principal to GHS 578.00\n";
} else {
    echo "\n✗ No liability found for this expense\n";
    echo "Creating liability...\n";
    
    $stmt = $db->prepare("
        INSERT INTO liabilities (
            source_type, source_id, liability_name, liability_type,
            principal_amount, outstanding_balance, start_date, status
        ) VALUES (
            'expense', 3, 'Unpaid Expense: Various Items', 'other',
            578.00, 578.00, '2026-01-22', 'active'
        )
    ");
    $stmt->execute();
    
    echo "✓ Created liability for unpaid expense\n";
}

echo "\n=== VERIFICATION ===\n";
$expense = $db->query("SELECT * FROM expenses WHERE id = 3")->fetch(PDO::FETCH_ASSOC);
echo "Expense Amount: GHS {$expense['amount']}\n";
echo "Amount Paid: GHS {$expense['amount_paid']}\n";
echo "Outstanding: GHS " . ($expense['amount'] - $expense['amount_paid']) . "\n";

echo "\n=== DONE ===\n";

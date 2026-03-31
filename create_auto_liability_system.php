<?php
/**
 * Create Automatic Liability System
 * Ensures all unpaid expenses automatically have liability records
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== CREATING AUTO LIABILITY SYSTEM ===\n\n";

// Find all unpaid expenses without liabilities
$stmt = $db->query("
    SELECT e.*
    FROM expenses e
    WHERE e.payment_status IN ('unpaid', 'partial')
    AND NOT EXISTS (
        SELECT 1 FROM liabilities l 
        WHERE l.source_type = 'expense' AND l.source_id = e.id
    )
");

$unpaidWithoutLiability = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($unpaidWithoutLiability) . " unpaid expenses without liabilities\n\n";

foreach ($unpaidWithoutLiability as $expense) {
    $expenseId = $expense['id'];
    $amount = $expense['amount'];
    $amountPaid = $expense['amount_paid'];
    $outstanding = $amount - $amountPaid;
    $description = substr($expense['description'], 0, 50);
    
    echo "Creating liability for Expense ID {$expenseId}:\n";
    echo "  Description: {$description}...\n";
    echo "  Outstanding: GHS {$outstanding}\n";
    
    $stmt = $db->prepare("
        INSERT INTO liabilities (
            source_type, source_id, liability_name, liability_type,
            principal_amount, outstanding_balance, start_date, status
        ) VALUES (
            'expense', :expense_id, :name, 'other',
            :amount, :outstanding, :start_date, 'active'
        )
    ");
    
    $stmt->execute([
        ':expense_id' => $expenseId,
        ':name' => 'Unpaid Expense: ' . $description,
        ':amount' => $amount,
        ':outstanding' => $outstanding,
        ':start_date' => $expense['expense_date']
    ]);
    
    echo "  ✓ Liability created\n\n";
}

echo "=== VERIFICATION ===\n";
$stmt = $db->query("
    SELECT COUNT(*) as cnt FROM expenses e
    WHERE e.payment_status IN ('unpaid', 'partial')
    AND NOT EXISTS (
        SELECT 1 FROM liabilities l 
        WHERE l.source_type = 'expense' AND l.source_id = e.id
    )
");
$remaining = $stmt->fetch(PDO::FETCH_ASSOC);

if ($remaining['cnt'] == 0) {
    echo "✓ All unpaid expenses now have liability records\n";
} else {
    echo "✗ Still {$remaining['cnt']} unpaid expenses without liabilities\n";
}

echo "\n=== DONE ===\n";

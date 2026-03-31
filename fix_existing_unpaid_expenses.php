<?php
/**
 * Fix existing unpaid expenses that have incorrect amount_paid values
 * and create missing liabilities
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== Fixing Existing Unpaid Expenses ===\n\n";

try {
    $db->beginTransaction();
    
    // Find unpaid expenses with amount_paid = amount (should be 0)
    $stmt = $db->query("
        SELECT id, description, amount, amount_paid, payment_status
        FROM expenses
        WHERE payment_status = 'unpaid' AND amount_paid > 0
    ");
    
    $badExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($badExpenses)) {
        echo "No unpaid expenses with incorrect amount_paid found.\n";
    } else {
        echo "Found " . count($badExpenses) . " unpaid expense(s) with incorrect amount_paid:\n\n";
        
        foreach ($badExpenses as $expense) {
            $expenseId = (int)$expense['id'];
            $amount = (float)$expense['amount'];
            $description = $expense['description'];
            
            echo "Fixing Expense ID {$expenseId}: {$description}\n";
            echo "  - Amount: GHS " . number_format($amount, 2) . "\n";
            echo "  - Old amount_paid: GHS " . number_format($expense['amount_paid'], 2) . "\n";
            echo "  - New amount_paid: GHS 0.00\n";
            
            // Update amount_paid to 0
            $updateStmt = $db->prepare("
                UPDATE expenses 
                SET amount_paid = 0 
                WHERE id = ?
            ");
            $updateStmt->execute([$expenseId]);
            
            // Check if liability exists
            $checkStmt = $db->prepare("
                SELECT id FROM liabilities 
                WHERE source_type = 'expense' AND source_id = ?
            ");
            $checkStmt->execute([$expenseId]);
            $existingLiability = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingLiability) {
                echo "  - Liability already exists (ID: {$existingLiability['id']})\n";
                
                // Update the expense to link to liability
                $linkStmt = $db->prepare("
                    UPDATE expenses 
                    SET liability_id = ? 
                    WHERE id = ?
                ");
                $linkStmt->execute([$existingLiability['id'], $expenseId]);
            } else {
                // Create liability
                echo "  - Creating new liability...\n";
                
                $liabilityStmt = $db->prepare("
                    INSERT INTO liabilities (
                        farm_id,
                        source_type,
                        source_id,
                        liability_name,
                        liability_type,
                        principal_amount,
                        outstanding_balance,
                        start_date,
                        status,
                        notes
                    ) VALUES (
                        (SELECT farm_id FROM expenses WHERE id = ?),
                        'expense',
                        ?,
                        ?,
                        'accounts_payable',
                        ?,
                        ?,
                        (SELECT expense_date FROM expenses WHERE id = ?),
                        'active',
                        ?
                    )
                ");
                
                $liabilityName = 'Unpaid Expense: ' . substr($description, 0, 50);
                $notes = 'Auto-generated from expense #' . $expenseId;
                
                $liabilityStmt->execute([
                    $expenseId,
                    $expenseId,
                    $liabilityName,
                    $amount,
                    $amount,
                    $expenseId,
                    $notes
                ]);
                
                $liabilityId = (int)$db->lastInsertId();
                echo "  - Created liability ID: {$liabilityId}\n";
                
                // Link expense to liability
                $linkStmt = $db->prepare("
                    UPDATE expenses 
                    SET liability_id = ? 
                    WHERE id = ?
                ");
                $linkStmt->execute([$liabilityId, $expenseId]);
            }
            
            echo "  ✓ Fixed\n\n";
        }
    }
    
    $db->commit();
    echo "\n=== Fix Complete ===\n";
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== DEBUGGING EXPENSE CREATION ===\n\n";

// Check recent expenses
echo "Recent expenses:\n";
$stmt = $db->query("
    SELECT id, description, amount, payment_status, amount_paid, liability_id, expense_date 
    FROM expenses 
    ORDER BY id DESC 
    LIMIT 5
");
while ($row = $stmt->fetch()) {
    echo "  ID: {$row['id']}\n";
    echo "  Description: {$row['description']}\n";
    echo "  Amount: {$row['amount']}\n";
    echo "  Payment Status: {$row['payment_status']}\n";
    echo "  Amount Paid: {$row['amount_paid']}\n";
    echo "  Liability ID: {$row['liability_id']}\n";
    echo "  Date: {$row['expense_date']}\n";
    echo "  ---\n";
}

// Check liabilities
echo "\nLiabilities:\n";
$stmt = $db->query("
    SELECT id, liability_name, principal_amount, source_type, source_id 
    FROM liabilities 
    ORDER BY id DESC 
    LIMIT 5
");
$count = 0;
while ($row = $stmt->fetch()) {
    $count++;
    echo "  ID: {$row['id']}\n";
    echo "  Name: {$row['liability_name']}\n";
    echo "  Amount: {$row['principal_amount']}\n";
    echo "  Source: {$row['source_type']} (ID: {$row['source_id']})\n";
    echo "  ---\n";
}

if ($count == 0) {
    echo "  No liabilities found\n";
}

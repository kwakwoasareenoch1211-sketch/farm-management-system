<?php
/**
 * Check expense data in database
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== Checking Expense Data ===\n\n";

// Get all expenses with payment status
$stmt = $db->query("
    SELECT 
        id,
        expense_date,
        description,
        amount,
        payment_status,
        amount_paid,
        liability_id,
        created_at
    FROM expenses
    ORDER BY id DESC
    LIMIT 10
");

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Last 10 expenses:\n";
echo str_repeat("-", 120) . "\n";
printf("%-5s %-12s %-30s %-10s %-12s %-12s %-12s %-20s\n", 
    "ID", "Date", "Description", "Amount", "Status", "Paid", "Liability", "Created");
echo str_repeat("-", 120) . "\n";

foreach ($expenses as $exp) {
    printf("%-5s %-12s %-30s %-10s %-12s %-12s %-12s %-20s\n",
        $exp['id'],
        $exp['expense_date'],
        substr($exp['description'], 0, 30),
        number_format($exp['amount'], 2),
        $exp['payment_status'] ?? 'NULL',
        number_format($exp['amount_paid'] ?? 0, 2),
        $exp['liability_id'] ?? 'NULL',
        $exp['created_at']
    );
}

echo "\n=== Checking for unpaid expenses with wrong amount_paid ===\n\n";

$stmt = $db->query("
    SELECT 
        id,
        description,
        amount,
        payment_status,
        amount_paid,
        (amount - amount_paid) AS outstanding
    FROM expenses
    WHERE payment_status IN ('unpaid', 'partial')
    ORDER BY id DESC
");

$unpaid = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($unpaid)) {
    echo "No unpaid/partial expenses found.\n";
} else {
    echo "Found " . count($unpaid) . " unpaid/partial expense(s):\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-40s %-10s %-12s %-12s %-12s\n", 
        "ID", "Description", "Amount", "Status", "Paid", "Outstanding");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($unpaid as $exp) {
        printf("%-5s %-40s %-10s %-12s %-12s %-12s\n",
            $exp['id'],
            substr($exp['description'], 0, 40),
            number_format($exp['amount'], 2),
            $exp['payment_status'],
            number_format($exp['amount_paid'], 2),
            number_format($exp['outstanding'], 2)
        );
    }
}

echo "\n=== Done ===\n";

<?php
require 'app/config/Config.php';
require 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== CHECKING LIABILITIES ===\n\n";

$liabilities = $db->query('SELECT * FROM liabilities')->fetchAll(PDO::FETCH_ASSOC);
echo "Total liabilities: " . count($liabilities) . "\n\n";

foreach ($liabilities as $l) {
    echo "ID: {$l['id']}\n";
    echo "  Name: {$l['liability_name']}\n";
    echo "  Principal: GHS {$l['principal_amount']}\n";
    echo "  Outstanding: GHS {$l['outstanding_balance']}\n";
    echo "  Source: {$l['source_type']}\n";
    echo "  Source ID: {$l['source_id']}\n";
    echo "  Status: {$l['status']}\n\n";
}

echo "=== CHECKING UNPAID EXPENSES ===\n\n";
$unpaid = $db->query("SELECT * FROM expenses WHERE payment_status IN ('unpaid', 'partial')")->fetchAll(PDO::FETCH_ASSOC);
echo "Total unpaid expenses: " . count($unpaid) . "\n\n";

foreach ($unpaid as $exp) {
    echo "ID: {$exp['id']}\n";
    echo "  Amount: GHS {$exp['amount']}\n";
    echo "  Paid: GHS {$exp['amount_paid']}\n";
    echo "  Outstanding: GHS " . ($exp['amount'] - $exp['amount_paid']) . "\n";
    echo "  Status: {$exp['payment_status']}\n";
    
    // Check if liability exists
    $lib = $db->prepare("SELECT id FROM liabilities WHERE source_type = 'expense' AND source_id = ?");
    $lib->execute([$exp['id']]);
    $libId = $lib->fetchColumn();
    
    if ($libId) {
        echo "  Liability: ID {$libId} ✓\n";
    } else {
        echo "  Liability: MISSING ✗\n";
    }
    echo "\n";
}

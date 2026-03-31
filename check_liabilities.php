<?php
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== CHECKING LIABILITIES ===\n\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM liabilities');
$result = $stmt->fetch();
echo "Total liabilities: " . $result['count'] . "\n\n";

if ($result['count'] > 0) {
    echo "Liabilities in database:\n";
    $stmt = $db->query('SELECT id, liability_name, principal_amount, status FROM liabilities LIMIT 5');
    while ($row = $stmt->fetch()) {
        echo "  ID: {$row['id']} - {$row['liability_name']} - GHS {$row['principal_amount']} - {$row['status']}\n";
    }
} else {
    echo "No liabilities found. You need to create one first.\n";
    echo "Go to: http://localhost/farmapp/liabilities/create\n";
}

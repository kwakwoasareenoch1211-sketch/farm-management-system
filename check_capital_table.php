<?php
require 'app/config/Config.php';
require 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== CHECKING CAPITAL TABLE ===\n\n";

// Show all tables
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "All tables in database:\n";
foreach($tables as $t) {
    echo "  - {$t}\n";
    if(stripos($t, 'capital') !== false) {
        echo "    ^ CAPITAL TABLE FOUND!\n";
    }
}

// Try to query capital table
echo "\nTrying to query 'capital' table:\n";
try {
    $stmt = $db->query("SELECT * FROM capital");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Capital table exists with " . count($rows) . " records\n";
    if (count($rows) > 0) {
        print_r($rows[0]);
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Try capital_contributions
echo "\nTrying 'capital_contributions' table:\n";
try {
    $stmt = $db->query("SELECT * FROM capital_contributions");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ capital_contributions table exists with " . count($rows) . " records\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

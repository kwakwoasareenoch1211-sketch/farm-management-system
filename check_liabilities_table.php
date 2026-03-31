<?php
require 'app/config/Config.php';
require 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== LIABILITIES TABLE STRUCTURE ===\n";
$stmt = $db->query('DESCRIBE liabilities');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n=== LIABILITIES DATA ===\n";
$stmt = $db->query('SELECT * FROM liabilities');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

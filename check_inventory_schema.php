<?php
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "Inventory Item Table Schema:\n";
$stmt = $db->query('DESCRIBE inventory_item');
while($row = $stmt->fetch()) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

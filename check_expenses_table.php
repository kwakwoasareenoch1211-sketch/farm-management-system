<?php
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== EXPENSES TABLE STRUCTURE ===\n\n";
$stmt = $db->query('SHOW COLUMNS FROM expenses');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

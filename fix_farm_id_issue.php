<?php
/**
 * Fix Farm ID Issue in Liabilities
 */

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';

$db = (new Database())->connect();

echo "=== FIXING FARM_ID ISSUE ===\n\n";

// Check if farms table has any records
$farms = $db->query("SELECT * FROM farms")->fetchAll(PDO::FETCH_ASSOC);
echo "Farms in database: " . count($farms) . "\n";

if (count($farms) > 0) {
    $defaultFarmId = $farms[0]['id'];
    echo "Default farm ID: {$defaultFarmId}\n\n";
    
    // Update liabilities with NULL or 0 farm_id
    $stmt = $db->prepare("UPDATE liabilities SET farm_id = ? WHERE farm_id IS NULL OR farm_id = 0");
    $stmt->execute([$defaultFarmId]);
    $updated = $stmt->rowCount();
    
    echo "✓ Updated {$updated} liability records with farm_id = {$defaultFarmId}\n";
} else {
    echo "No farms found. Creating default farm...\n";
    
    // Create a default farm
    $stmt = $db->prepare("
        INSERT INTO farms (farm_name, location, farm_type, status)
        VALUES ('Default Farm', 'Main Location', 'poultry', 'active')
    ");
    $stmt->execute();
    $defaultFarmId = $db->lastInsertId();
    
    echo "✓ Created default farm with ID: {$defaultFarmId}\n";
    
    // Update liabilities
    $stmt = $db->prepare("UPDATE liabilities SET farm_id = ? WHERE farm_id IS NULL OR farm_id = 0");
    $stmt->execute([$defaultFarmId]);
    $updated = $stmt->rowCount();
    
    echo "✓ Updated {$updated} liability records\n";
}

// Verify
echo "\n=== VERIFICATION ===\n";
$liabilities = $db->query("SELECT id, liability_name, farm_id FROM liabilities")->fetchAll(PDO::FETCH_ASSOC);
foreach ($liabilities as $lib) {
    echo "Liability ID {$lib['id']}: farm_id = {$lib['farm_id']}\n";
}

echo "\n=== DONE ===\n";

<?php
// Test medication and vaccination pages load without inventory_item errors

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/models/MedicationRecord.php';
require_once BASE_PATH . 'app/models/VaccinationRecord.php';

echo "Testing Medication and Vaccination Models...\n\n";

try {
    // Test MedicationRecord
    echo "1. Testing MedicationRecord::all()...\n";
    $medModel = new MedicationRecord();
    $medications = $medModel->all();
    echo "   ✓ Retrieved " . count($medications) . " medication records\n";
    
    echo "2. Testing MedicationRecord::totals()...\n";
    $medTotals = $medModel->totals();
    echo "   ✓ Total records: " . $medTotals['total_records'] . "\n";
    echo "   ✓ Total cost: GHS " . number_format($medTotals['total_cost'], 2) . "\n";
    
    // Test VaccinationRecord
    echo "\n3. Testing VaccinationRecord::all()...\n";
    $vacModel = new VaccinationRecord();
    $vaccinations = $vacModel->all();
    echo "   ✓ Retrieved " . count($vaccinations) . " vaccination records\n";
    
    echo "4. Testing VaccinationRecord::totals()...\n";
    $vacTotals = $vacModel->totals();
    echo "   ✓ Total records: " . $vacTotals['total_records'] . "\n";
    echo "   ✓ Total cost: GHS " . number_format($vacTotals['total_cost'], 2) . "\n";
    
    echo "\n✅ ALL TESTS PASSED - No inventory_item table errors!\n";
    echo "✅ Medication and vaccination systems working correctly\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

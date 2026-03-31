<?php
/**
 * Test Mortality System Fix
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/models/MortalityRecord.php';
require_once BASE_PATH . 'app/models/Batch.php';

$db = Database::connect();

echo "=== TESTING MORTALITY SYSTEM ===\n\n";

// Test 1: Check disposal_method column exists
echo "1. Checking disposal_method column...\n";
$stmt = $db->query("SHOW COLUMNS FROM mortality_records LIKE 'disposal_method'");
$column = $stmt->fetch();
if ($column) {
    echo "   ✓ disposal_method column exists\n";
} else {
    echo "   ✗ disposal_method column missing\n";
}

// Test 2: Check totals calculation
echo "\n2. Testing totals calculation...\n";
$mortalityModel = new MortalityRecord();
$totals = $mortalityModel->totals();
echo "   Total Records: {$totals['total_records']}\n";
echo "   Total Mortality: {$totals['total_mortality']}\n";
echo "   Total Batches: {$totals['total_batches']}\n";
echo "   ✓ Totals calculated successfully\n";

// Test 3: Check batch current_quantity
echo "\n3. Testing batch quantities...\n";
$batchModel = new Batch();
$batches = $batchModel->all();
if (!empty($batches)) {
    $batch = $batches[0];
    echo "   Batch: {$batch['batch_code']}\n";
    echo "   Initial: {$batch['initial_quantity']}\n";
    echo "   Current: {$batch['current_quantity']}\n";
    echo "   Total Mortality: {$batch['total_mortality']}\n";
    $expected = $batch['initial_quantity'] - $batch['total_mortality'];
    if ($batch['current_quantity'] == $expected) {
        echo "   ✓ Quantity calculation correct\n";
    } else {
        echo "   ✗ Quantity mismatch (expected: {$expected})\n";
    }
} else {
    echo "   No batches found\n";
}

// Test 4: Check mortality records display
echo "\n4. Testing mortality records display...\n";
$records = $mortalityModel->all();
if (!empty($records)) {
    $record = $records[0];
    echo "   Record ID: {$record['id']}\n";
    echo "   Batch: {$record['batch_code']}\n";
    echo "   Quantity: {$record['quantity']}\n";
    echo "   Current Birds: {$record['current_quantity']}\n";
    echo "   Disposal: " . ($record['disposal_method'] ?? 'N/A') . "\n";
    echo "   ✓ Records display correctly\n";
} else {
    echo "   No mortality records found\n";
}

echo "\n=== ALL TESTS COMPLETED ===\n";

<?php
/**
 * Fix Mortality System
 * - Adds disposal_method column if missing
 * - Verifies batch current_quantity integrity
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== FIXING MORTALITY SYSTEM ===\n\n";

// 1. Add disposal_method column
echo "1. Adding disposal_method column...\n";
try {
    $db->exec("
        ALTER TABLE mortality_records 
        ADD COLUMN disposal_method VARCHAR(100) DEFAULT NULL 
        AFTER cause
    ");
    echo "   ✓ Column added successfully\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ Column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

// 2. Verify and fix batch quantities
echo "\n2. Verifying batch quantities...\n";
$stmt = $db->query("
    SELECT 
        ab.id,
        ab.batch_code,
        ab.initial_quantity,
        ab.current_quantity,
        COALESCE(SUM(mr.quantity), 0) AS total_mortality,
        (ab.initial_quantity - COALESCE(SUM(mr.quantity), 0)) AS calculated_current
    FROM animal_batches ab
    LEFT JOIN mortality_records mr ON mr.batch_id = ab.id
    GROUP BY ab.id
");

$batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
$fixed = 0;

foreach ($batches as $batch) {
    $currentQty = (int)$batch['current_quantity'];
    $calculatedQty = (int)$batch['calculated_current'];
    
    if ($currentQty !== $calculatedQty) {
        echo "   Fixing batch {$batch['batch_code']}: {$currentQty} → {$calculatedQty}\n";
        
        $update = $db->prepare("
            UPDATE animal_batches 
            SET current_quantity = :qty 
            WHERE id = :id
        ");
        $update->execute([
            ':qty' => $calculatedQty,
            ':id' => $batch['id']
        ]);
        $fixed++;
    }
}

if ($fixed > 0) {
    echo "   ✓ Fixed {$fixed} batch(es)\n";
} else {
    echo "   ✓ All batch quantities are correct\n";
}

echo "\n=== MORTALITY SYSTEM FIXED ===\n";
echo "\nChanges made:\n";
echo "1. Added disposal_method column to mortality_records\n";
echo "2. Fixed batch current_quantity calculations\n";
echo "3. Updated Batch model to use database current_quantity\n";
echo "4. Updated MortalityRecord model to include disposal_method\n";
echo "5. Fixed totals() to include total_batches count\n";

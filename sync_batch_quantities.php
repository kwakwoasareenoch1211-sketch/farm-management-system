<?php

require_once 'app/config/Config.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=farmapp_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting batch quantity synchronization...\n\n";
    
    // Get all batches
    $stmt = $db->query("
        SELECT 
            id, 
            batch_code, 
            batch_name, 
            initial_quantity, 
            current_quantity
        FROM animal_batches
    ");
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($batches) . " batches\n\n";
    
    foreach ($batches as $batch) {
        $batchId = $batch['id'];
        $batchName = $batch['batch_name'] ?: $batch['batch_code'];
        $initialQty = (int)$batch['initial_quantity'];
        $currentQty = (int)$batch['current_quantity'];
        
        // Calculate total mortality for this batch
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(quantity), 0) AS total_mortality
            FROM mortality_records
            WHERE batch_id = ?
        ");
        $stmt->execute([$batchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalMortality = (int)$result['total_mortality'];
        
        // Calculate what current_quantity should be
        $correctCurrentQty = $initialQty - $totalMortality;
        
        echo "Batch: $batchName (ID: $batchId)\n";
        echo "  Initial Quantity: $initialQty\n";
        echo "  Total Mortality: $totalMortality\n";
        echo "  Current Quantity (DB): $currentQty\n";
        echo "  Correct Quantity: $correctCurrentQty\n";
        
        if ($currentQty != $correctCurrentQty) {
            // Update the batch
            $stmt = $db->prepare("
                UPDATE animal_batches
                SET current_quantity = ?
                WHERE id = ?
            ");
            $stmt->execute([$correctCurrentQty, $batchId]);
            
            echo "  ✓ UPDATED: $currentQty → $correctCurrentQty\n";
        } else {
            echo "  ✓ Already correct\n";
        }
        
        echo "\n";
    }
    
    echo "\n=================================\n";
    echo "Synchronization complete!\n";
    echo "=================================\n\n";
    
    // Show summary
    $stmt = $db->query("
        SELECT 
            COUNT(*) AS total_batches,
            SUM(initial_quantity) AS total_initial,
            SUM(current_quantity) AS total_current
        FROM animal_batches
    ");
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->query("
        SELECT COALESCE(SUM(quantity), 0) AS total_mortality
        FROM mortality_records
    ");
    $mortality = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Summary:\n";
    echo "  Total Batches: " . $summary['total_batches'] . "\n";
    echo "  Total Initial Birds: " . number_format($summary['total_initial']) . "\n";
    echo "  Total Mortality: " . number_format($mortality['total_mortality']) . "\n";
    echo "  Current Live Birds: " . number_format($summary['total_current']) . "\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

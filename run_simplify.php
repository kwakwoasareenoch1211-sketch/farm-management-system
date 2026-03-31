<?php
// Simplify the inventory-feed system

$host = 'localhost';
$db = 'farmapp_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Simplifying system...\n\n";
    
    $sql = file_get_contents('database/simplify_system.sql');
    $pdo->exec($sql);
    
    echo "✓ System simplified successfully!\n";
    echo "✓ Removed status and stock_receipt_id from feed_records\n";
    echo "✓ Removed stock tracking columns from inventory_item\n";
    echo "✓ Cleaned up orphaned records\n";
    echo "✓ Updated feed records with current inventory costs\n\n";
    
    echo "The simplified system is ready!\n";
    echo "- Add feed items in Inventory Items\n";
    echo "- Record feed usage in Feed Module\n";
    echo "- No stock tracking complexity\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

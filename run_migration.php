<?php
// Run unified stock-feed flow migration

$host = 'localhost';
$db = 'farmapp_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Running migration...\n\n";
    
    $sql = file_get_contents('database/unified_stock_feed_flow.sql');
    $pdo->exec($sql);
    
    echo "✓ Database migration completed successfully!\n";
    echo "✓ Added status column to feed_records\n";
    echo "✓ Added stock_receipt_id column to feed_records\n";
    echo "✓ Made batch_id nullable for available feed\n";
    echo "✓ Updated existing records to 'assigned' status\n\n";
    
    echo "The unified stock-feed flow is now ready to use!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=farmapp_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('database/losses_writeoffs.sql');
    $db->exec($sql);
    
    echo "✓ Losses & Write-offs table created successfully\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

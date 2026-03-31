<?php
// Debug script for feed and inventory integration
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/InventoryManager.php';
require_once BASE_PATH . 'app/models/Feed.php';

echo "<h1>Feed & Inventory Debug</h1>";
echo "<style>body{font-family:monospace;padding:20px;} table{border-collapse:collapse;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f0f0f0;}</style>";

try {
    $inventoryItem = new InventoryItem();
    $inventoryManager = new InventoryManager();
    
    echo "<h2>1. Inventory Items</h2>";
    $items = $inventoryItem->all();
    if (empty($items)) {
        echo "<p style='color:red;'><strong>NO INVENTORY ITEMS FOUND!</strong></p>";
        echo "<p>You need to create inventory items first. Go to: Inventory → Add Item</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Current Stock</th><th>Unit Cost</th><th>Reorder Level</th></tr>";
        foreach ($items as $item) {
            $stockColor = (float)$item['current_stock'] > 0 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$item['id']}</td>";
            echo "<td>{$item['item_name']}</td>";
            echo "<td>{$item['category']}</td>";
            echo "<td style='color:{$stockColor};font-weight:bold;'>{$item['current_stock']}</td>";
            echo "<td>{$item['unit_cost']}</td>";
            echo "<td>{$item['reorder_level']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>2. Stock Check Test</h2>";
    if (!empty($items)) {
        $testItem = $items[0];
        $testItemId = (int)$testItem['id'];
        $stockLevel = $inventoryManager->stockLevel($testItemId);
        $hasStock = $inventoryManager->hasEnoughStock($testItemId, 1.0);
        
        echo "<p>Testing item: <strong>{$testItem['item_name']}</strong> (ID: {$testItemId})</p>";
        echo "<p>Stock Level: <strong style='color:" . ($stockLevel > 0 ? 'green' : 'red') . ";'>{$stockLevel}</strong></p>";
        echo "<p>Has enough stock for 1.0 kg: <strong style='color:" . ($hasStock ? 'green' : 'red') . ";'>" . ($hasStock ? 'YES' : 'NO') . "</strong></p>";
    }
    
    echo "<h2>3. Feed Records</h2>";
    $feedModel = new Feed();
    $feedRecords = $feedModel->all();
    if (empty($feedRecords)) {
        echo "<p>No feed records yet.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Batch</th><th>Feed Name</th><th>Quantity (kg)</th><th>Unit Cost</th><th>Date</th></tr>";
        foreach ($feedRecords as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['batch_code']}</td>";
            echo "<td>{$record['feed_name']}</td>";
            echo "<td>{$record['quantity_kg']}</td>";
            echo "<td>{$record['unit_cost']}</td>";
            echo "<td>{$record['record_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>4. Stock Movements</h2>";
    $db = Database::connect();
    $stmt = $db->query("SELECT * FROM stock_movements ORDER BY created_at DESC LIMIT 10");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($movements)) {
        echo "<p>No stock movements yet.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Item ID</th><th>Type</th><th>Quantity</th><th>Date</th><th>Reference</th><th>Notes</th></tr>";
        foreach ($movements as $mov) {
            echo "<tr>";
            echo "<td>{$mov['id']}</td>";
            echo "<td>{$mov['item_id']}</td>";
            echo "<td>{$mov['movement_type']}</td>";
            echo "<td>{$mov['quantity']}</td>";
            echo "<td>{$mov['movement_date']}</td>";
            echo "<td>{$mov['reference_no']}</td>";
            echo "<td>{$mov['notes']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<ul>";
    echo "<li>Total Inventory Items: <strong>" . count($items) . "</strong></li>";
    echo "<li>Items with Stock > 0: <strong>" . count(array_filter($items, fn($i) => (float)$i['current_stock'] > 0)) . "</strong></li>";
    echo "<li>Total Feed Records: <strong>" . count($feedRecords) . "</strong></li>";
    echo "<li>Total Stock Movements: <strong>" . count($movements) . "</strong></li>";
    echo "</ul>";
    
    if (count($items) === 0) {
        echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:20px 0;'>";
        echo "<h3>⚠️ Action Required</h3>";
        echo "<p><strong>You need to create inventory items first!</strong></p>";
        echo "<ol>";
        echo "<li>Go to: <strong>Inventory → Add Item</strong></li>";
        echo "<li>Create a feed item (e.g., 'Broiler Starter Feed')</li>";
        echo "<li>Set initial stock to 0 (you'll receive stock later)</li>";
        echo "<li>Then go to: <strong>Inventory → Receive Stock</strong></li>";
        echo "<li>Add quantity to your feed item</li>";
        echo "<li>Finally, you can record feed usage</li>";
        echo "</ol>";
        echo "</div>";
    } elseif (count(array_filter($items, fn($i) => (float)$i['current_stock'] > 0)) === 0) {
        echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:20px 0;'>";
        echo "<h3>⚠️ Action Required</h3>";
        echo "<p><strong>All inventory items have zero stock!</strong></p>";
        echo "<ol>";
        echo "<li>Go to: <strong>Inventory → Receive Stock</strong></li>";
        echo "<li>Select an inventory item</li>";
        echo "<li>Enter quantity and unit cost</li>";
        echo "<li>Save to add stock</li>";
        echo "<li>Then you can record feed usage</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background:#d1ecf1;border:1px solid #0c5460;padding:15px;margin:20px 0;'>";
        echo "<h3>✅ System Ready</h3>";
        echo "<p>You have inventory items with stock. You can now record feed usage!</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;border:1px solid #f5c6cb;padding:15px;margin:20px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Application</a></p>";

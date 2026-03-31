<?php
define('BASE_PATH', __DIR__ . '/');
define('BASE_URL', 'http://localhost/farmapp');

require_once 'app/config/Config.php';
require_once 'app/config/Database.php';
require_once 'app/models/InventorySummary.php';

$db = (new Database())->connect();
$inventorySummary = new InventorySummary($db);

echo "=== TESTING INVENTORY SUMMARY ===\n\n";

try {
    echo "1. Testing totals()...\n";
    $totals = $inventorySummary->totals();
    echo "   Total Items: {$totals['total_items']}\n";
    echo "   Total Value: GHS " . number_format($totals['total_value'], 2) . "\n";
    echo "   ✓ totals() working\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

try {
    echo "2. Testing recentInventoryActivities()...\n";
    $activities = $inventorySummary->recentInventoryActivities(5);
    echo "   Found " . count($activities) . " activities\n";
    foreach ($activities as $activity) {
        echo "   - {$activity['activity_type']}: {$activity['item_name']} ({$activity['quantity']})\n";
    }
    echo "   ✓ recentInventoryActivities() working\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

try {
    echo "3. Testing categorySummary()...\n";
    $categories = $inventorySummary->categorySummary();
    echo "   Found " . count($categories) . " categories\n";
    foreach ($categories as $cat) {
        echo "   - {$cat['category']}: {$cat['total_items']} items\n";
    }
    echo "   ✓ categorySummary() working\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

try {
    echo "4. Testing topValuedItems()...\n";
    $items = $inventorySummary->topValuedItems(5);
    echo "   Found " . count($items) . " items\n";
    foreach ($items as $item) {
        echo "   - {$item['item_name']}: GHS " . number_format($item['total_value'], 2) . "\n";
    }
    echo "   ✓ topValuedItems() working\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

try {
    echo "5. Testing feedUsageSummary()...\n";
    $feedSummary = $inventorySummary->feedUsageSummary();
    echo "   Found " . count($feedSummary) . " feed types\n";
    foreach ($feedSummary as $feed) {
        echo "   - {$feed['item_name']}: {$feed['total_quantity']} kg\n";
    }
    echo "   ✓ feedUsageSummary() working\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== ALL TESTS COMPLETE ===\n";

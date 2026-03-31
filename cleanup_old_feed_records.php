<?php
// Cleanup script for old feed records without inventory links
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

echo "<h1>Feed Records Cleanup</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:1200px;margin:0 auto;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:12px;text-align:left;} th{background:#f0f0f0;font-weight:600;} .btn{display:inline-block;padding:8px 16px;margin:4px;text-decoration:none;border-radius:4px;border:none;cursor:pointer;font-size:14px;} .btn-danger{background:#dc3545;color:white;} .btn-primary{background:#0d6efd;color:white;} .btn-secondary{background:#6c757d;color:white;} .alert{padding:15px;margin:20px 0;border-radius:4px;} .alert-warning{background:#fff3cd;border:1px solid #ffc107;} .alert-info{background:#d1ecf1;border:1px solid#0c5460;} .alert-success{background:#d1e7dd;border:1px solid #0f5132;}</style>";

try {
    $db = Database::connect();
    
    // Handle delete action
    if (isset($_POST['delete_old_records'])) {
        $stmt = $db->prepare("DELETE FROM feed_records WHERE inventory_item_id IS NULL");
        $stmt->execute();
        $deleted = $stmt->rowCount();
        
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ Success!</strong> Deleted {$deleted} old feed record(s) without inventory links.";
        echo "</div>";
    }
    
    // Get old records
    $stmt = $db->query("
        SELECT 
            fr.*,
            ab.batch_code,
            ab.batch_name
        FROM feed_records fr
        LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
        WHERE fr.inventory_item_id IS NULL
        ORDER BY fr.record_date DESC
    ");
    $oldRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get new records
    $stmt = $db->query("
        SELECT 
            fr.*,
            ab.batch_code,
            ab.batch_name,
            ii.item_name
        FROM feed_records fr
        LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
        LEFT JOIN inventory_item ii ON ii.id = fr.inventory_item_id
        WHERE fr.inventory_item_id IS NOT NULL
        ORDER BY fr.record_date DESC
    ");
    $newRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Old Records (No Inventory Link):</strong> " . count($oldRecords) . "</li>";
    echo "<li><strong>New Records (With Inventory Link):</strong> " . count($newRecords) . "</li>";
    echo "<li><strong>Total Feed Records:</strong> " . (count($oldRecords) + count($newRecords)) . "</li>";
    echo "</ul>";
    
    if (!empty($oldRecords)) {
        echo "<div class='alert alert-warning'>";
        echo "<h3>⚠️ Old Records Found</h3>";
        echo "<p>These records were created before the inventory integration. They don't affect inventory stock.</p>";
        echo "<p><strong>Options:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Keep them:</strong> They'll remain as historical data (marked as 'Manual')</li>";
        echo "<li><strong>Delete them:</strong> Clean up and start fresh with inventory-linked records only</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h2>Old Records (No Inventory Link)</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Date</th><th>Batch</th><th>Feed Name</th><th>Qty (kg)</th><th>Unit Cost</th><th>Total Cost</th></tr>";
        
        $totalOldCost = 0;
        foreach ($oldRecords as $r) {
            $cost = (float)$r['quantity_kg'] * (float)$r['unit_cost'];
            $totalOldCost += $cost;
            echo "<tr>";
            echo "<td>{$r['id']}</td>";
            echo "<td>{$r['record_date']}</td>";
            echo "<td>{$r['batch_code']}</td>";
            echo "<td>{$r['feed_name']}</td>";
            echo "<td>" . number_format((float)$r['quantity_kg'], 2) . "</td>";
            echo "<td>GHS " . number_format((float)$r['unit_cost'], 2) . "</td>";
            echo "<td>GHS " . number_format($cost, 2) . "</td>";
            echo "</tr>";
        }
        echo "<tr style='background:#f8f9fa;font-weight:bold;'>";
        echo "<td colspan='6'>TOTAL OLD RECORDS COST</td>";
        echo "<td>GHS " . number_format($totalOldCost, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<form method='POST' onsubmit='return confirm(\"Are you sure you want to delete all " . count($oldRecords) . " old feed records? This cannot be undone!\");'>";
        echo "<button type='submit' name='delete_old_records' class='btn btn-danger'>";
        echo "🗑️ Delete All Old Records (" . count($oldRecords) . ")";
        echo "</button>";
        echo "<a href='index.php' class='btn btn-secondary'>← Back to Application</a>";
        echo "</form>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h3>✅ All Clean!</h3>";
        echo "<p>No old records found. All feed records are properly linked to inventory.</p>";
        echo "</div>";
    }
    
    if (!empty($newRecords)) {
        echo "<h2>New Records (With Inventory Link)</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Date</th><th>Batch</th><th>Inventory Item</th><th>Qty (kg)</th><th>Unit Cost</th><th>Total Cost</th></tr>";
        
        $totalNewCost = 0;
        foreach ($newRecords as $r) {
            $cost = (float)$r['quantity_kg'] * (float)$r['unit_cost'];
            $totalNewCost += $cost;
            echo "<tr>";
            echo "<td>{$r['id']}</td>";
            echo "<td>{$r['record_date']}</td>";
            echo "<td>{$r['batch_code']}</td>";
            echo "<td><span style='background:#0dcaf0;color:#000;padding:4px 8px;border-radius:4px;font-size:12px;'>{$r['item_name']}</span></td>";
            echo "<td>" . number_format((float)$r['quantity_kg'], 2) . "</td>";
            echo "<td>GHS " . number_format((float)$r['unit_cost'], 2) . "</td>";
            echo "<td>GHS " . number_format($cost, 2) . "</td>";
            echo "</tr>";
        }
        echo "<tr style='background:#f8f9fa;font-weight:bold;'>";
        echo "<td colspan='6'>TOTAL NEW RECORDS COST</td>";
        echo "<td>GHS " . number_format($totalNewCost, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>Next Steps</h2>";
    echo "<div class='alert alert-info'>";
    echo "<ol>";
    echo "<li><strong>Create Inventory Items:</strong> Go to Inventory → Add Item</li>";
    echo "<li><strong>Receive Stock:</strong> Go to Inventory → Receive Stock</li>";
    echo "<li><strong>Record Feed Usage:</strong> Go to Feed → Add Feed Record (will auto-link to inventory)</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='index.php' class='btn btn-primary'>← Back to Application</a></p>";
    echo "<p><a href='debug_feed.php' class='btn btn-secondary'>🔍 Run Debug Script</a></p>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

<?php
// Database schema verification script
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

echo "<h1>Database Schema Verification</h1>";
echo "<style>
body{font-family:sans-serif;padding:20px;max-width:1400px;margin:0 auto;background:#f5f5f5;}
table{border-collapse:collapse;width:100%;margin:20px 0;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
th,td{border:1px solid #ddd;padding:12px;text-align:left;font-size:13px;}
th{background:#2c3e50;color:#fff;font-weight:600;}
.alert{padding:15px;margin:20px 0;border-radius:4px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
.alert-success{background:#d1e7dd;border:1px solid #0f5132;color:#0f5132;}
.alert-warning{background:#fff3cd;border:1px solid #ffc107;color:#856404;}
.alert-danger{background:#f8d7da;border:1px solid #842029;color:#842029;}
.alert-info{background:#d1ecf1;border:1px solid#0c5460;color:#0c5460;}
.badge{display:inline-block;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;}
.badge-success{background:#198754;color:#fff;}
.badge-danger{background:#dc3545;color:#fff;}
.badge-warning{background:#ffc107;color:#000;}
h2{color:#2c3e50;border-bottom:2px solid #3498db;padding-bottom:10px;margin-top:30px;}
</style>";

try {
    $db = Database::connect();
    
    echo "<div class='alert alert-success'>";
    echo "<h3>✅ Database Connection Successful</h3>";
    echo "<p>Connected to database: <strong>" . DB_NAME . "</strong></p>";
    echo "</div>";
    
    // Tables to verify
    $tables = [
        'expenses' => ['expense_date', 'description', 'amount', 'payment_method', 'category_id'],
        'feed_records' => ['record_date', 'feed_name', 'quantity_kg', 'unit_cost', 'batch_id', 'inventory_item_id'],
        'medication_records' => ['record_date', 'medication_name', 'quantity_used', 'unit_cost', 'batch_id', 'inventory_item_id'],
        'vaccination_records' => ['record_date', 'vaccine_name', 'dose_qty', 'cost_amount', 'batch_id', 'inventory_item_id'],
        'stock_receipts' => ['receipt_date', 'item_id', 'supplier_id', 'quantity', 'unit_cost'],
        'stock_issues' => ['issue_date', 'item_id', 'batch_id', 'quantity', 'purpose'],
        'stock_movements' => ['movement_date', 'item_id', 'movement_type', 'quantity', 'reference_no'],
        'inventory_item' => ['item_name', 'category', 'current_stock', 'reorder_level', 'unit_cost'],
        'animal_batches' => ['batch_code', 'batch_name', 'current_quantity', 'status'],
        'expense_categories' => ['category_name'],
        'suppliers' => ['supplier_name'],
    ];
    
    echo "<h2>Table Structure Verification</h2>";
    
    $allTablesOk = true;
    foreach ($tables as $tableName => $expectedColumns) {
        echo "<h3>Table: {$tableName}</h3>";
        
        // Check if table exists
        $stmt = $db->query("SHOW TABLES LIKE '{$tableName}'");
        if ($stmt->rowCount() === 0) {
            echo "<div class='alert alert-danger'>";
            echo "<strong>❌ Table does not exist!</strong>";
            echo "</div>";
            $allTablesOk = false;
            continue;
        }
        
        // Get table columns
        $stmt = $db->query("DESCRIBE {$tableName}");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th><th>Status</th></tr>";
        
        $foundColumns = [];
        foreach ($columns as $col) {
            $foundColumns[] = $col['Field'];
            $isExpected = in_array($col['Field'], $expectedColumns);
            $badge = $isExpected ? "<span class='badge badge-success'>Expected</span>" : "";
            
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "<td>{$badge}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing expected columns
        $missingColumns = array_diff($expectedColumns, $foundColumns);
        if (!empty($missingColumns)) {
            echo "<div class='alert alert-danger'>";
            echo "<strong>❌ Missing expected columns:</strong> " . implode(', ', $missingColumns);
            echo "</div>";
            $allTablesOk = false;
        } else {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ All expected columns present</strong>";
            echo "</div>";
        }
    }
    
    // Test expense queries
    echo "<h2>Expense Query Tests</h2>";
    
    $queries = [
        'Manual Expenses' => "SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total FROM expenses",
        'Feed Expenses' => "SELECT COUNT(*) as cnt, COALESCE(SUM(quantity_kg * unit_cost), 0) as total FROM feed_records WHERE unit_cost IS NOT NULL AND unit_cost > 0",
        'Medication Expenses' => "SELECT COUNT(*) as cnt, COALESCE(SUM(quantity_used * unit_cost), 0) as total FROM medication_records WHERE unit_cost IS NOT NULL AND unit_cost > 0 AND quantity_used IS NOT NULL",
        'Vaccination Expenses' => "SELECT COUNT(*) as cnt, COALESCE(SUM(cost_amount), 0) as total FROM vaccination_records WHERE cost_amount IS NOT NULL AND cost_amount > 0",
        'Stock Receipt Expenses' => "SELECT COUNT(*) as cnt, COALESCE(SUM(quantity * unit_cost), 0) as total FROM stock_receipts WHERE unit_cost IS NOT NULL AND unit_cost > 0 AND quantity IS NOT NULL",
    ];
    
    echo "<table>";
    echo "<tr><th>Query Type</th><th>Records</th><th>Total Amount</th><th>Status</th></tr>";
    
    $grandTotal = 0;
    $grandCount = 0;
    foreach ($queries as $label => $query) {
        try {
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)($result['cnt'] ?? 0);
            $total = (float)($result['total'] ?? 0);
            
            $grandTotal += $total;
            $grandCount += $count;
            
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>" . number_format($count) . "</td>";
            echo "<td>GHS " . number_format($total, 2) . "</td>";
            echo "<td><span class='badge badge-success'>✅ OK</span></td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td colspan='2'><span class='badge badge-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</span></td>";
            echo "<td><span class='badge badge-danger'>❌ FAILED</span></td>";
            echo "</tr>";
            $allTablesOk = false;
        }
    }
    
    echo "<tr style='background:#f8f9fa;font-weight:bold;'>";
    echo "<td>GRAND TOTAL</td>";
    echo "<td>" . number_format($grandCount) . "</td>";
    echo "<td>GHS " . number_format($grandTotal, 2) . "</td>";
    echo "<td></td>";
    echo "</tr>";
    echo "</table>";
    
    // Test JOIN queries
    echo "<h2>JOIN Query Tests</h2>";
    
    $joinQueries = [
        'Feed with Batches' => "SELECT COUNT(*) as cnt FROM feed_records fr LEFT JOIN animal_batches ab ON ab.id = fr.batch_id",
        'Medication with Batches' => "SELECT COUNT(*) as cnt FROM medication_records mr LEFT JOIN animal_batches ab ON ab.id = mr.batch_id",
        'Vaccination with Batches' => "SELECT COUNT(*) as cnt FROM vaccination_records vr LEFT JOIN animal_batches ab ON ab.id = vr.batch_id",
        'Stock Receipts with Items' => "SELECT COUNT(*) as cnt FROM stock_receipts sr LEFT JOIN inventory_item ii ON ii.id = sr.item_id",
        'Stock Receipts with Suppliers' => "SELECT COUNT(*) as cnt FROM stock_receipts sr LEFT JOIN suppliers s ON s.id = sr.supplier_id",
        'Expenses with Categories' => "SELECT COUNT(*) as cnt FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id",
    ];
    
    echo "<table>";
    echo "<tr><th>JOIN Query</th><th>Records</th><th>Status</th></tr>";
    
    foreach ($joinQueries as $label => $query) {
        try {
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)($result['cnt'] ?? 0);
            
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>" . number_format($count) . "</td>";
            echo "<td><span class='badge badge-success'>✅ OK</span></td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td><span class='badge badge-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</span></td>";
            echo "<td><span class='badge badge-danger'>❌ FAILED</span></td>";
            echo "</tr>";
            $allTablesOk = false;
        }
    }
    echo "</table>";
    
    // Data integrity checks
    echo "<h2>Data Integrity Checks</h2>";
    
    $integrityChecks = [
        'Feed records with NULL unit_cost' => "SELECT COUNT(*) as cnt FROM feed_records WHERE unit_cost IS NULL",
        'Feed records with inventory link' => "SELECT COUNT(*) as cnt FROM feed_records WHERE inventory_item_id IS NOT NULL",
        'Medication records with NULL unit_cost' => "SELECT COUNT(*) as cnt FROM medication_records WHERE unit_cost IS NULL",
        'Vaccination records with NULL cost_amount' => "SELECT COUNT(*) as cnt FROM vaccination_records WHERE cost_amount IS NULL",
        'Stock receipts with NULL unit_cost' => "SELECT COUNT(*) as cnt FROM stock_receipts WHERE unit_cost IS NULL",
        'Orphaned feed records (no batch)' => "SELECT COUNT(*) as cnt FROM feed_records WHERE batch_id NOT IN (SELECT id FROM animal_batches)",
        'Orphaned stock receipts (no item)' => "SELECT COUNT(*) as cnt FROM stock_receipts WHERE item_id NOT IN (SELECT id FROM inventory_item)",
    ];
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Count</th><th>Status</th></tr>";
    
    foreach ($integrityChecks as $label => $query) {
        try {
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)($result['cnt'] ?? 0);
            
            $isWarning = $count > 0 && (strpos($label, 'NULL') !== false || strpos($label, 'Orphaned') !== false);
            $badge = $isWarning ? "<span class='badge badge-warning'>⚠️ {$count} found</span>" : "<span class='badge badge-success'>✅ {$count}</span>";
            
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>" . number_format($count) . "</td>";
            echo "<td>{$badge}</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td><span class='badge badge-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</span></td>";
            echo "<td><span class='badge badge-danger'>❌ FAILED</span></td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Final summary
    echo "<h2>Summary</h2>";
    if ($allTablesOk) {
        echo "<div class='alert alert-success'>";
        echo "<h3>✅ All Database Checks Passed!</h3>";
        echo "<p>Your database schema is correctly configured and all queries are working.</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<h3>❌ Some Issues Found</h3>";
        echo "<p>Please review the errors above and fix the database schema.</p>";
        echo "</div>";
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<div class='alert alert-info'>";
    echo "<ol>";
    echo "<li><a href='test_expense_system.php'>Test Expense System</a> - Verify expense aggregation</li>";
    echo "<li><a href='index.php?url=expenses'>View Expenses Dashboard</a> - Check the UI</li>";
    echo "<li><a href='index.php?url=inventory'>View Inventory Dashboard</a> - Check inventory integration</li>";
    echo "<li><a href='cleanup_old_feed_records.php'>Cleanup Old Records</a> - Optional cleanup</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

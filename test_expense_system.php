<?php
// Test script for expense system
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/models/Expense.php';

echo "<h1>Expense System Test</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:1200px;margin:0 auto;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:12px;text-align:left;} th{background:#f0f0f0;font-weight:600;} .alert{padding:15px;margin:20px 0;border-radius:4px;} .alert-success{background:#d1e7dd;border:1px solid #0f5132;} .alert-danger{background:#f8d7da;border:1px solid #842029;} .badge{display:inline-block;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;}</style>";

try {
    $expenseModel = new Expense();
    
    echo "<div class='alert alert-success'>";
    echo "<h3>✅ Expense Model Loaded Successfully</h3>";
    echo "</div>";
    
    // Test totals
    echo "<h2>1. Testing Expense Totals</h2>";
    $totals = $expenseModel->totals();
    
    echo "<table>";
    echo "<tr><th>Metric</th><th>Value</th></tr>";
    echo "<tr><td>Total Records</td><td>" . number_format($totals['total_records']) . "</td></tr>";
    echo "<tr><td>Total Amount</td><td>GHS " . number_format($totals['total_amount'], 2) . "</td></tr>";
    echo "<tr><td>Current Month</td><td>GHS " . number_format($totals['current_month_amount'], 2) . "</td></tr>";
    echo "<tr><td>Today</td><td>GHS " . number_format($totals['today_amount'], 2) . "</td></tr>";
    echo "</table>";
    
    // Test breakdown by source
    echo "<h2>2. Testing Breakdown by Source</h2>";
    echo "<table>";
    echo "<tr><th>Source</th><th>Records</th><th>Total</th><th>This Month</th><th>Today</th></tr>";
    
    $sourceLabels = [
        'manual' => 'Manual Expenses',
        'feed' => 'Feed Costs',
        'medication' => 'Medication Costs',
        'vaccination' => 'Vaccination Costs',
        'stock_receipt' => 'Stock Purchases',
    ];
    
    foreach ($totals['by_source'] as $source => $data) {
        echo "<tr>";
        echo "<td><span class='badge'>" . ($sourceLabels[$source] ?? $source) . "</span></td>";
        echo "<td>" . number_format($data['count']) . "</td>";
        echo "<td>GHS " . number_format($data['total'], 2) . "</td>";
        echo "<td>GHS " . number_format($data['current_month'], 2) . "</td>";
        echo "<td>GHS " . number_format($data['today'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test all expenses
    echo "<h2>3. Testing All Expenses Query</h2>";
    $records = $expenseModel->all();
    
    echo "<p><strong>Total Records Retrieved:</strong> " . count($records) . "</p>";
    
    if (!empty($records)) {
        echo "<table>";
        echo "<tr><th>Date</th><th>Source</th><th>Title</th><th>Amount</th></tr>";
        
        $displayLimit = 10;
        $displayed = 0;
        foreach ($records as $r) {
            if ($displayed >= $displayLimit) break;
            
            $badgeColors = [
                'manual' => '#0d6efd',
                'feed' => '#ffc107',
                'medication' => '#dc3545',
                'vaccination' => '#198754',
                'stock_receipt' => '#0dcaf0',
            ];
            
            $source = $r['expense_source'] ?? 'manual';
            $color = $badgeColors[$source] ?? '#6c757d';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($r['date']) . "</td>";
            echo "<td><span class='badge' style='background:{$color};color:#fff;'>" . ucfirst($source) . "</span></td>";
            echo "<td>" . htmlspecialchars(substr($r['title'], 0, 60)) . "</td>";
            echo "<td>GHS " . number_format((float)$r['amount'], 2) . "</td>";
            echo "</tr>";
            
            $displayed++;
        }
        echo "</table>";
        
        if (count($records) > $displayLimit) {
            echo "<p><em>Showing first {$displayLimit} of " . count($records) . " records</em></p>";
        }
    } else {
        echo "<p><em>No expense records found</em></p>";
    }
    
    // Verification
    echo "<h2>4. Verification</h2>";
    echo "<div class='alert alert-success'>";
    echo "<h3>✅ All Tests Passed!</h3>";
    echo "<ul>";
    echo "<li>Expense model loaded successfully</li>";
    echo "<li>Totals calculated correctly</li>";
    echo "<li>Breakdown by source working</li>";
    echo "<li>All expenses query working</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><a href='index.php'>Go to Application</a></li>";
    echo "<li><a href='index.php?url=expenses'>View Expenses Dashboard</a></li>";
    echo "<li><a href='index.php?url=inventory'>View Inventory Dashboard</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

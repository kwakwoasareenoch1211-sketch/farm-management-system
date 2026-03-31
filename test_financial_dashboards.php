<?php
/**
 * Test Financial & Economic Dashboards
 * Verifies all calculations are accurate and traceable
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/models/FinancialMonitor.php';

echo "=== Testing Financial & Economic Dashboards ===\n\n";

$monitor = new FinancialMonitor();

// Test 1: Get all totals
echo "Test 1: Financial Totals\n";
echo str_repeat("-", 80) . "\n";
$totals = $monitor->totals();

echo "Capital:           GHS " . number_format($totals['total_capital'], 2) . "\n";
echo "Revenue:           GHS " . number_format($totals['total_revenue'], 2) . "\n";
echo "Expenses:          GHS " . number_format($totals['total_expenses'], 2) . "\n";
echo "Assets:            GHS " . number_format($totals['total_assets'], 2) . "\n";
echo "Liabilities:       GHS " . number_format($totals['total_liabilities'], 2) . "\n";
echo "Investments:       GHS " . number_format($totals['total_investments'], 2) . "\n";
echo "\n";
echo "Retained Profit:   GHS " . number_format($totals['retained_profit'], 2) . "\n";
echo "Owner Equity:      GHS " . number_format($totals['owner_equity'], 2) . "\n";
echo "Net Worth:         GHS " . number_format($totals['net_worth'], 2) . "\n";
echo "Working Capital:   GHS " . number_format($totals['working_capital'], 2) . "\n";
echo "\n";
echo "Profit Margin:     " . number_format($totals['profit_margin'], 1) . "%\n";
echo "Debt Ratio:        " . number_format($totals['debt_ratio'], 1) . "%\n";
echo "ROI:               " . number_format($totals['roi'], 1) . "%\n";

// Test 2: Verify accounting equation
echo "\n\nTest 2: Accounting Equation Verification\n";
echo str_repeat("-", 80) . "\n";
$assets = $totals['total_assets'];
$liabilities = $totals['total_liabilities'];
$equity = $totals['owner_equity'];
$equation_balance = $assets - ($liabilities + $equity);

echo "Assets:                    GHS " . number_format($assets, 2) . "\n";
echo "Liabilities + Equity:      GHS " . number_format($liabilities + $equity, 2) . "\n";
echo "Difference:                GHS " . number_format($equation_balance, 2) . "\n";

if (abs($equation_balance) < 0.01) {
    echo "✓ Accounting equation BALANCED\n";
} else {
    echo "✗ Accounting equation UNBALANCED (difference: GHS " . number_format($equation_balance, 2) . ")\n";
}

// Test 3: Verify retained profit calculation
echo "\n\nTest 3: Retained Profit Verification\n";
echo str_repeat("-", 80) . "\n";
$revenue = $totals['total_revenue'];
$expenses = $totals['total_expenses'];
$calculated_profit = $revenue - $expenses;
$reported_profit = $totals['retained_profit'];

echo "Revenue:           GHS " . number_format($revenue, 2) . "\n";
echo "Expenses:          GHS " . number_format($expenses, 2) . "\n";
echo "Calculated Profit: GHS " . number_format($calculated_profit, 2) . "\n";
echo "Reported Profit:   GHS " . number_format($reported_profit, 2) . "\n";
echo "Difference:        GHS " . number_format(abs($calculated_profit - $reported_profit), 2) . "\n";

if (abs($calculated_profit - $reported_profit) < 0.01) {
    echo "✓ Retained profit calculation CORRECT\n";
} else {
    echo "✗ Retained profit calculation INCORRECT\n";
}

// Test 4: Verify owner equity calculation
echo "\n\nTest 4: Owner Equity Verification\n";
echo str_repeat("-", 80) . "\n";
$capital = $totals['total_capital'];
$profit = $totals['retained_profit'];
$calculated_equity = $capital + $profit;
$reported_equity = $totals['owner_equity'];

echo "Capital:           GHS " . number_format($capital, 2) . "\n";
echo "Retained Profit:   GHS " . number_format($profit, 2) . "\n";
echo "Calculated Equity: GHS " . number_format($calculated_equity, 2) . "\n";
echo "Reported Equity:   GHS " . number_format($reported_equity, 2) . "\n";
echo "Difference:        GHS " . number_format(abs($calculated_equity - $reported_equity), 2) . "\n";

if (abs($calculated_equity - $reported_equity) < 0.01) {
    echo "✓ Owner equity calculation CORRECT\n";
} else {
    echo "✗ Owner equity calculation INCORRECT\n";
}

// Test 5: Test traceability system
echo "\n\nTest 5: Calculation Traceability\n";
echo str_repeat("-", 80) . "\n";
$traceability = $monitor->getCalculationTraceability();
echo "Documented metrics: " . count($traceability) . "\n\n";

$metrics_to_check = ['capital', 'revenue', 'expenses', 'assets', 'liabilities'];
foreach ($metrics_to_check as $metric) {
    if (isset($traceability[$metric])) {
        $data = $traceability[$metric];
        echo "✓ {$metric}: ";
        echo "Formula documented, ";
        echo count($data['source_tables'] ?? []) . " source tables, ";
        echo "Principle: " . substr($data['accounting_principle'] ?? 'N/A', 0, 40) . "...\n";
    } else {
        echo "✗ {$metric}: NOT DOCUMENTED\n";
    }
}

// Test 6: Test accounting principles reference
echo "\n\nTest 6: Accounting Principles Reference\n";
echo str_repeat("-", 80) . "\n";
$principles = $monitor->getAccountingPrinciples();
echo "Documented principles: " . count($principles) . "\n\n";

foreach ($principles as $name => $data) {
    echo "✓ " . ucwords(str_replace('_', ' ', $name)) . "\n";
}

// Test 7: Test business analysis
echo "\n\nTest 7: Business Analysis\n";
echo str_repeat("-", 80) . "\n";
$analysis = $monitor->businessAnalysis();
echo "Business Stage:        " . ($analysis['stage'] ?? 'Unknown') . "\n";
echo "Capital Efficiency:    " . number_format($analysis['capital_efficiency'] ?? 0, 2) . "x\n";
echo "Asset Coverage:        " . number_format($analysis['asset_coverage'] ?? 0, 2) . "x\n";
echo "Investment Ratio:      " . number_format($analysis['investment_ratio'] ?? 0, 1) . "%\n";
echo "Expense Ratio:         " . number_format($analysis['expense_ratio'] ?? 0, 1) . "%\n";
echo "Capital Adequacy:      " . number_format($analysis['capital_adequacy'] ?? 0, 1) . "%\n";

// Test 8: Test current month totals
echo "\n\nTest 8: Current Month Totals\n";
echo str_repeat("-", 80) . "\n";
$monthTotals = $monitor->currentMonthTotals();
echo "Month Revenue:     GHS " . number_format($monthTotals['revenue'] ?? 0, 2) . "\n";
echo "Month Expenses:    GHS " . number_format($monthTotals['total_expense'] ?? 0, 2) . "\n";
echo "Month Net:         GHS " . number_format($monthTotals['net'] ?? 0, 2) . "\n";
echo "Capital Injected:  GHS " . number_format($monthTotals['capital_injected'] ?? 0, 2) . "\n";

// Summary
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n";

$tests_passed = 0;
$total_tests = 8;

// Check if accounting equation balances
if (abs($equation_balance) < 0.01) $tests_passed++;

// Check if profit calculation is correct
if (abs($calculated_profit - $reported_profit) < 0.01) $tests_passed++;

// Check if equity calculation is correct
if (abs($calculated_equity - $reported_equity) < 0.01) $tests_passed++;

// Check if traceability is complete
if (count($traceability) >= 10) $tests_passed++;

// Check if principles are documented
if (count($principles) >= 5) $tests_passed++;

// Check if business analysis works
if (!empty($analysis['stage'])) $tests_passed++;

// Check if month totals work
if (isset($monthTotals['revenue'])) $tests_passed++;

// Check if all core metrics are present
if (isset($totals['total_capital']) && isset($totals['total_revenue']) && 
    isset($totals['total_expenses']) && isset($totals['total_assets']) && 
    isset($totals['total_liabilities'])) $tests_passed++;

echo "\nTests Passed: {$tests_passed} / {$total_tests}\n";

if ($tests_passed === $total_tests) {
    echo "\n✓ ALL TESTS PASSED - Financial dashboards are working correctly!\n";
} else {
    echo "\n✗ SOME TESTS FAILED - Please review the output above\n";
}

echo "\n=== Test Complete ===\n";

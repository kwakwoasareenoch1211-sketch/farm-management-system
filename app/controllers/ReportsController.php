<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/core/ExportHelper.php';

require_once BASE_PATH . 'app/models/ReportsSummary.php';
require_once BASE_PATH . 'app/models/DecisionIntelligenceReport.php';
require_once BASE_PATH . 'app/models/EggProductionReport.php';
require_once BASE_PATH . 'app/models/MedicationReport.php';
require_once BASE_PATH . 'app/models/WeightReport.php';
require_once BASE_PATH . 'app/models/Sales.php';
require_once BASE_PATH . 'app/models/Expense.php';
require_once BASE_PATH . 'app/models/FinanceSummary.php';
require_once BASE_PATH . 'app/models/FinancialMonitor.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/InventorySummary.php';
require_once BASE_PATH . 'app/models/Batch.php';


class ReportsController extends Controller
{

    public function dashboard(): void
    {
        $summary = new ReportsSummary();
        $intel = new DecisionIntelligenceReport();

        $this->view('reports/dashboard', [
            'pageTitle' => 'Reports Dashboard',
            'sidebarType' => 'reports',

            'totals' => $summary->dashboardTotals(),
            'monthlyRevenueVsExpense' => $summary->monthlyRevenueVsExpense(6),
            'recentActivities' => $summary->recentActivities(10),

            'smartSignals' => $intel->smartSignals(),
            'topProfitableBatches' => $intel->topProfitableBatches(5),
            'highMortalityBatches' => $intel->highMortalityBatches(5),
            'lowStockPressure' => $intel->lowStockPressure(5)

        ], 'admin');
    }



    public function batchPerformance(): void
    {
        $batchModel = new Batch();

        $this->view('reports/batch-performance', [
            'pageTitle' => 'Batch Performance Report',
            'sidebarType' => 'reports',
            'reportRows' => $batchModel->all(),
        ], 'admin');
    }

    public function feedReport(): void
    {
        require_once BASE_PATH . 'app/models/Feed.php';
        $feedModel = new Feed();

        $this->view('reports/feed', [
            'pageTitle' => 'Feed Consumption Report',
            'sidebarType' => 'reports',
            'reportRows' => $feedModel->all(),
            'totals' => method_exists($feedModel, 'totals') ? $feedModel->totals() : ['total_records' => 0, 'total_feed_kg' => 0, 'total_feed_cost' => 0],
        ], 'admin');
    }

    public function mortalityReport(): void
    {
        require_once BASE_PATH . 'app/models/MortalityRecord.php';
        $mortalityModel = new MortalityRecord();

        $this->view('reports/mortality', [
            'pageTitle' => 'Mortality Report',
            'sidebarType' => 'reports',
            'reportRows' => $mortalityModel->all(),
            'totals' => $mortalityModel->totals(),
            'batchRows' => method_exists($mortalityModel, 'byBatch') ? $mortalityModel->byBatch() : [],
        ], 'admin');
    }

    public function vaccinationReport(): void
    {
        require_once BASE_PATH . 'app/models/VaccinationRecord.php';
        $vaccinationModel = new VaccinationRecord();

        $this->view('reports/vaccination', [
            'pageTitle' => 'Vaccination Report',
            'sidebarType' => 'reports',
            'reportRows' => $vaccinationModel->all(),
            'totals' => $vaccinationModel->totals(),
            'dueRows' => method_exists($vaccinationModel, 'upcoming') ? $vaccinationModel->upcoming(10) : [],
        ], 'admin');
    }



    public function medicationReport(): void
    {
        $report = new MedicationReport();

        $this->view('reports/medication', [
            'pageTitle' => 'Medication Report',
            'sidebarType' => 'reports',
            'reportRows' => $report->all(),
            'totals' => $report->totals(),
            'batchRows' => $report->byBatch()
        ], 'admin');
    }



    public function eggProductionReport(): void
    {
        $report = new EggProductionReport();

        $this->view('reports/egg-production', [
            'pageTitle' => 'Egg Production Report',
            'sidebarType' => 'reports',
            'reportRows' => $report->all(),
            'totals' => $report->totals(),
            'batchRows' => $report->byBatch()
        ], 'admin');
    }



    public function weightReport(): void
    {
        $report = new WeightReport();

        $this->view('reports/weight', [
            'pageTitle' => 'Weight Report',
            'sidebarType' => 'reports',
            'reportRows' => $report->all(),
            'totals' => $report->totals(),
            'batchRows' => $report->byBatch()
        ], 'admin');
    }



    public function stockPosition(): void
    {
        $inventoryItem = new InventoryItem();
        $inventorySummary = new InventorySummary();

        $this->view('reports/stock-position', [
            'pageTitle' => 'Stock Position Report',
            'sidebarType' => 'reports',
            'reportRows' => $inventoryItem->all(),
            'totals' => $inventorySummary->totals(),
            'categorySummary' => $inventorySummary->categorySummary(),
        ], 'admin');
    }

    public function lowStock(): void
    {
        $inventoryItem = new InventoryItem();

        $this->view('reports/low-stock', [
            'pageTitle' => 'Low Stock Report',
            'sidebarType' => 'reports',
            'reportRows' => $inventoryItem->lowStock(),
        ], 'admin');
    }

    public function stockMovement(): void
    {
        $inventorySummary = new InventorySummary();
        $db = \Database::connect();

        $rows = $db->query("
            SELECT sm.*, ii.item_name, ii.unit_of_measure
            FROM stock_movements sm
            LEFT JOIN inventory_item ii ON ii.id = sm.item_id
            ORDER BY sm.movement_date DESC, sm.id DESC
        ")->fetchAll() ?: [];

        $totals = $db->query("
            SELECT
                COALESCE(SUM(CASE WHEN movement_type='receipt' THEN quantity ELSE 0 END), 0) AS total_in_qty,
                COALESCE(SUM(CASE WHEN movement_type='issue' THEN quantity ELSE 0 END), 0) AS total_out_qty
            FROM stock_movements
        ")->fetch() ?: [];

        $this->view('reports/stock-movement', [
            'pageTitle' => 'Stock Movement Report',
            'sidebarType' => 'reports',
            'reportRows' => $rows,
            'totals' => $totals,
            'monthlyBreakdown' => $inventorySummary->monthlyMovementBreakdown(6),
        ], 'admin');
    }

    public function inventoryValuation(): void
    {
        $inventoryItem = new InventoryItem();
        $inventorySummary = new InventorySummary();

        $this->view('reports/inventory-valuation', [
            'pageTitle' => 'Inventory Valuation Report',
            'sidebarType' => 'reports',
            'reportRows' => $inventoryItem->all(),
            'totals' => $inventorySummary->totals(),
            'categorySummary' => $inventorySummary->categorySummary(),
        ], 'admin');
    }

    public function salesReport(): void
    {
        $salesModel = new Sales();

        $this->view('reports/sales', [
            'pageTitle' => 'Sales Report',
            'sidebarType' => 'reports',
            'reportRows' => $salesModel->all(),
            'totals' => $salesModel->totals(),
            'byType' => $salesModel->byType(),
        ], 'admin');
    }

    public function expenseReport(): void
    {
        $expenseModel = new Expense();

        $this->view('reports/expenses', [
            'pageTitle' => 'Expense Report',
            'sidebarType' => 'reports',
            'reportRows' => $expenseModel->all(),
            'totals' => $expenseModel->totals(),
            'byCategory' => $expenseModel->byCategory(),
        ], 'admin');
    }

    public function profitLoss(): void
    {
        $financeSummary = new FinanceSummary();

        $this->view('reports/profit-loss', [
            'pageTitle' => 'Profit & Loss Report',
            'sidebarType' => 'reports',
            'financeTotals' => $financeSummary->totals(),
            'currentMonthTotals' => $financeSummary->currentMonthTotals(),
            'monthlyCombined' => $financeSummary->monthlyCombinedBreakdown(6),
        ], 'admin');
    }



    public function forecastReport(): void
    {
        $financeSummary = new FinanceSummary();
        $batchModel     = new Batch();
        $salesModel     = new Sales();

        $monthlyCombined = $financeSummary->monthlyCombinedBreakdown(6);
        $batches         = $batchModel->all();
        $salesTotals     = $salesModel->totals();

        // Build simple linear forecast from last 3 months
        $revenueHistory  = array_column($monthlyCombined, 'sales_revenue');
        $expenseHistory  = array_column($monthlyCombined, 'total_expense');
        $avgRevenue      = count($revenueHistory) > 0 ? array_sum($revenueHistory) / count($revenueHistory) : 0;
        $avgExpense      = count($expenseHistory) > 0 ? array_sum($expenseHistory) / count($expenseHistory) : 0;

        // Project next 3 months
        $forecastMonths = [];
        for ($i = 1; $i <= 3; $i++) {
            $ts = strtotime("+{$i} month");
            $forecastMonths[] = [
                'month_label'       => date('M Y', $ts),
                'projected_revenue' => round($avgRevenue * (1 + ($i * 0.02)), 2),
                'projected_expense' => round($avgExpense * (1 + ($i * 0.01)), 2),
                'projected_net'     => round(($avgRevenue * (1 + ($i * 0.02))) - ($avgExpense * (1 + ($i * 0.01))), 2),
            ];
        }

        $this->view('reports/forecast', [
            'pageTitle'       => 'Forecast Report',
            'sidebarType'     => 'reports',
            'monthlyCombined' => $monthlyCombined,
            'forecastMonths'  => $forecastMonths,
            'avgRevenue'      => $avgRevenue,
            'avgExpense'      => $avgExpense,
            'batches'         => $batches,
            'salesTotals'     => $salesTotals,
        ], 'admin');
    }

    public function businessHealth(): void
    {
        require_once BASE_PATH . 'app/models/FinancialMonitor.php';
        $monitor        = new FinancialMonitor();
        $intel          = new DecisionIntelligenceReport();
        $financeSummary = new FinanceSummary();
        $batchModel     = new Batch();

        $monitorTotals   = $monitor->totals();
        $monthlyCombined = $financeSummary->monthlyCombinedBreakdown(6);
        $batches         = $batchModel->all();

        $lossMaking = [];
        $strong     = [];
        $topBatch   = null;
        $worstBatch = null;

        foreach ($batches as $b) {
            $gp = (float)($b['gross_profit'] ?? 0);
            if ($gp < 0) $lossMaking[] = $b;
            else $strong[] = $b;
            if ($topBatch === null || $gp > (float)($topBatch['gross_profit'] ?? 0)) $topBatch = $b;
            if ($worstBatch === null || $gp < (float)($worstBatch['gross_profit'] ?? 0)) $worstBatch = $b;
        }

        $this->view('reports/business-health', [
            'pageTitle'       => 'Business Health Report',
            'sidebarType'     => 'reports',
            'monitorTotals'   => $monitorTotals,
            'monthlyCombined' => $monthlyCombined,
            'batches'         => $batches,
            'lossMaking'      => $lossMaking,
            'strong'          => $strong,
            'topBatch'        => $topBatch,
            'worstBatch'      => $worstBatch,
            'smartSignals'    => $intel->smartSignals(),
            'highMortality'   => $intel->highMortalityBatches(5),
            'lowStockPressure'=> $intel->lowStockPressure(5),
        ], 'admin');
    }

    public function decisionRecommendations(): void
    {
        require_once BASE_PATH . 'app/models/FinancialMonitor.php';
        $monitor        = new FinancialMonitor();
        $intel          = new DecisionIntelligenceReport();
        $financeSummary = new FinanceSummary();
        $batchModel     = new Batch();

        $monitorTotals   = $monitor->totals();
        $currentMonth    = $monitor->currentMonthTotals();
        $monthlyCombined = $financeSummary->monthlyCombinedBreakdown(6);
        $batches         = $batchModel->all();

        $lossMaking = array_filter($batches, fn($b) => (float)($b['gross_profit'] ?? 0) < 0);
        $strong     = array_filter($batches, fn($b) => (float)($b['gross_profit'] ?? 0) > 0);

        $totalRevenue     = (float)($monitorTotals['total_revenue'] ?? 0);
        $totalExpenses    = (float)($monitorTotals['total_expenses'] ?? 0);
        $totalAssets      = (float)($monitorTotals['total_assets'] ?? 0);
        $totalLiabilities = (float)($monitorTotals['total_liabilities'] ?? 0);
        $netProfit        = (float)($monitorTotals['net_profit'] ?? 0);
        $profitMargin     = (float)($monitorTotals['profit_margin'] ?? 0);
        $debtRatio        = (float)($monitorTotals['debt_ratio'] ?? 0);

        // Build decision recommendations
        $decisions = [];

        if ($netProfit > 0 && $totalAssets > $totalLiabilities && $profitMargin >= 15) {
            $decisions[] = ['priority' => 'high', 'type' => 'success', 'title' => 'Expansion Opportunity',
                'reason' => 'Business is profitable with strong assets. Consider adding new batches or expanding flock size.',
                'action' => 'Review batch capacity and plan controlled expansion.', 'link' => '/batches/create'];
        }

        if (count($lossMaking) > 0) {
            $decisions[] = ['priority' => 'high', 'type' => 'danger', 'title' => 'Review Loss-Making Batches',
                'reason' => count($lossMaking) . ' batch(es) are generating losses. Feed cost or low sales may be the cause.',
                'action' => 'Audit feed usage, mortality, and pricing for affected batches.', 'link' => '/batches'];
        }

        if ($debtRatio > 60) {
            $decisions[] = ['priority' => 'high', 'type' => 'warning', 'title' => 'Reduce Liability Pressure',
                'reason' => 'Liabilities represent ' . number_format($debtRatio, 1) . '% of assets. This is above safe threshold.',
                'action' => 'Prioritise clearing unpaid expenses before new commitments.', 'link' => '/expenses'];
        }

        if ((float)($currentMonth['total_expense'] ?? 0) > (float)($currentMonth['revenue'] ?? 0)) {
            $decisions[] = ['priority' => 'medium', 'type' => 'warning', 'title' => 'Month Expenses Exceed Revenue',
                'reason' => 'This month costs are higher than income. Cash flow is under pressure.',
                'action' => 'Review and reduce non-essential expenses this month.', 'link' => '/expenses'];
        }

        if ($profitMargin > 0 && $profitMargin < 10) {
            $decisions[] = ['priority' => 'medium', 'type' => 'warning', 'title' => 'Low Profit Margin',
                'reason' => 'Profit margin is only ' . number_format($profitMargin, 1) . '%. Margins below 10% indicate thin profitability.',
                'action' => 'Increase sale prices or reduce operational costs.', 'link' => '/sales'];
        }

        if (count($strong) > count($lossMaking) && $netProfit > 0) {
            $decisions[] = ['priority' => 'low', 'type' => 'success', 'title' => 'Maintain Current Operations',
                'reason' => 'Most batches are profitable and overall net position is positive.',
                'action' => 'Continue monitoring and maintain current cost discipline.', 'link' => '/admin'];
        }

        $this->view('reports/decisions', [
            'pageTitle'       => 'Decision Recommendations',
            'sidebarType'     => 'reports',
            'decisions'       => $decisions,
            'monitorTotals'   => $monitorTotals,
            'currentMonth'    => $currentMonth,
            'monthlyCombined' => $monthlyCombined,
            'smartSignals'    => $intel->smartSignals(),
            'topBatches'      => $intel->topProfitableBatches(5),
            'highMortality'   => $intel->highMortalityBatches(5),
            'lowStockPressure'=> $intel->lowStockPressure(5),
        ], 'admin');
    }

    public function customReports(): void
    {
        $financeSummary  = new FinanceSummary();
        $salesModel      = new Sales();
        $expenseModel    = new Expense();
        $batchModel      = new Batch();
        $inventoryItem   = new InventoryItem();

        $this->view('reports/custom', [
            'pageTitle'       => 'Custom Reports',
            'sidebarType'     => 'reports',
            'financeTotals'   => $financeSummary->totals(),
            'monthlyCombined' => $financeSummary->monthlyCombinedBreakdown(12),
            'salesTotals'     => $salesModel->totals(),
            'salesByType'     => $salesModel->byType(),
            'expenseTotals'   => $expenseModel->totals(),
            'expenseByCategory' => $expenseModel->byCategory(),
            'batches'         => $batchModel->all(),
            'lowStockItems'   => $inventoryItem->lowStock(),
        ], 'admin');
    }

    public function exportCenter(): void
    {
        $financeSummary = new FinanceSummary();
        $salesModel     = new Sales();
        $expenseModel   = new Expense();
        $batchModel     = new Batch();

        $this->view('reports/export', [
            'pageTitle'       => 'Export Center',
            'sidebarType'     => 'reports',
            'financeTotals'   => $financeSummary->totals(),
            'salesTotals'     => $salesModel->totals(),
            'expenseTotals'   => $expenseModel->totals(),
            'totalBatches'    => count($batchModel->all()),
        ], 'admin');
    }

    /**
     * Generic download handler for all reports
     * Usage: /reports/download?type=feed&format=excel
     */
    public function download(): void
    {
        $type   = $_GET['type']   ?? 'expenses';
        $format = $_GET['format'] ?? 'excel';

        switch ($type) {
            case 'feed':
                require_once BASE_PATH . 'app/models/Feed.php';
                $rows = (new Feed())->all();
                $headers = ['Date', 'Batch', 'Feed Name', 'Quantity (kg)', 'Unit Cost (GHS)', 'Total Cost (GHS)', 'Notes'];
                $data = array_map(fn($r) => [
                    $r['record_date'] ?? '', $r['batch_code'] ?? '', $r['feed_name'] ?? '',
                    $r['quantity_kg'] ?? 0, $r['unit_cost'] ?? 0,
                    number_format((float)($r['quantity_kg'] ?? 0) * (float)($r['unit_cost'] ?? 0), 2),
                    $r['notes'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'feed-records-' . date('Y-m-d'), $format);
                break;

            case 'mortality':
                require_once BASE_PATH . 'app/models/MortalityRecord.php';
                $rows = (new MortalityRecord())->all();
                $headers = ['Date', 'Batch', 'Quantity', 'Cause', 'Disposal', 'Notes'];
                $data = array_map(fn($r) => [
                    $r['record_date'] ?? '', ($r['batch_code'] ?? '') . ' ' . ($r['batch_name'] ?? ''),
                    $r['quantity'] ?? 0, $r['cause'] ?? '', $r['disposal_method'] ?? '', $r['notes'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'mortality-records-' . date('Y-m-d'), $format);
                break;

            case 'vaccination':
                require_once BASE_PATH . 'app/models/VaccinationRecord.php';
                $rows = (new VaccinationRecord())->all();
                $headers = ['Date', 'Batch', 'Vaccine', 'Dose Qty', 'Disease Target', 'Cost (GHS)', 'Next Due', 'Notes'];
                $data = array_map(fn($r) => [
                    $r['record_date'] ?? '', ($r['batch_code'] ?? '') . ' ' . ($r['batch_name'] ?? ''),
                    $r['vaccine_name'] ?? '', $r['dose_qty'] ?? 0, $r['disease_target'] ?? '',
                    $r['cost_amount'] ?? 0, $r['next_due_date'] ?? '', $r['notes'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'vaccination-records-' . date('Y-m-d'), $format);
                break;

            case 'medication':
                require_once BASE_PATH . 'app/models/MedicationRecord.php';
                $rows = (new MedicationRecord())->all();
                $headers = ['Date', 'Batch', 'Medication', 'Qty Used', 'Unit Cost (GHS)', 'Total (GHS)', 'Notes'];
                $data = array_map(fn($r) => [
                    $r['record_date'] ?? '', ($r['batch_code'] ?? '') . ' ' . ($r['batch_name'] ?? ''),
                    $r['medication_name'] ?? '', $r['quantity_used'] ?? 0, $r['unit_cost'] ?? 0,
                    number_format((float)($r['quantity_used'] ?? 0) * (float)($r['unit_cost'] ?? 0), 2),
                    $r['notes'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'medication-records-' . date('Y-m-d'), $format);
                break;

            case 'batches':
                $rows = (new Batch())->all();
                $headers = ['Batch Code', 'Name', 'Purpose', 'Initial Qty', 'Current Qty', 'Start Date', 'Status'];
                $data = array_map(fn($r) => [
                    $r['batch_code'] ?? '', $r['batch_name'] ?? '', $r['production_purpose'] ?? '',
                    $r['initial_quantity'] ?? 0, $r['current_quantity'] ?? 0,
                    $r['start_date'] ?? '', $r['status'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'batches-' . date('Y-m-d'), $format);
                break;

            case 'sales':
                $rows = (new Sales())->all();
                $headers = ['Date', 'Batch', 'Customer', 'Product', 'Qty', 'Unit Price', 'Total (GHS)', 'Status'];
                $data = array_map(fn($r) => [
                    $r['sale_date'] ?? '', ($r['batch_code'] ?? '') . ' ' . ($r['batch_name'] ?? ''),
                    $r['customer_name'] ?? '', $r['product_type'] ?? '',
                    $r['quantity'] ?? 0, $r['unit_price'] ?? 0,
                    number_format((float)($r['total_amount'] ?? 0), 2),
                    $r['payment_status'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'sales-' . date('Y-m-d'), $format);
                break;

            case 'egg-production':
                require_once BASE_PATH . 'app/models/EggProductionRecord.php';
                $rows = (new EggProductionRecord())->all();
                $headers = ['Date', 'Batch', 'Quantity', 'Trays', 'Notes'];
                $data = array_map(fn($r) => [
                    $r['record_date'] ?? '', ($r['batch_code'] ?? '') . ' ' . ($r['batch_name'] ?? ''),
                    $r['quantity'] ?? 0, $r['trays_equivalent'] ?? 0, $r['notes'] ?? '',
                ], $rows);
                ExportHelper::export($data, $headers, 'egg-production-' . date('Y-m-d'), $format);
                break;

            case 'expenses':
            default:
                $rows = (new Expense())->all();
                $headers = ['Date', 'Description', 'Source', 'Category', 'Amount (GHS)', 'Paid (GHS)', 'Status'];
                $data = array_map(fn($r) => [
                    $r['date'] ?? '', $r['title'] ?? '',
                    ucfirst(str_replace('_', ' ', $r['expense_source'] ?? '')),
                    $r['category_name'] ?? '', number_format((float)($r['amount'] ?? 0), 2),
                    number_format((float)($r['amount_paid'] ?? $r['amount'] ?? 0), 2),
                    $r['payment_status'] ?? 'paid',
                ], $rows);
                $total = array_sum(array_column($rows, 'amount'));
                $data[] = ['', '', '', 'TOTAL', number_format($total, 2), '', ''];
                ExportHelper::export($data, $headers, 'expenses-' . date('Y-m-d'), $format);
                break;
        }
    }

}
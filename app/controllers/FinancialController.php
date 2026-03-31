<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Expense.php';
require_once BASE_PATH . 'app/models/Sales.php';
require_once BASE_PATH . 'app/models/Dashboard.php';
require_once BASE_PATH . 'app/models/FinanceSummary.php';
require_once BASE_PATH . 'app/models/FinancialMonitor.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/Capital.php';
require_once BASE_PATH . 'app/models/Investment.php';

class FinancialController extends Controller
{
    public function dashboard(): void
    {
        $salesModel     = new Sales();
        $dashboardModel = new Dashboard();
        $financeSummary = new FinanceSummary();
        $monitor        = new FinancialMonitor();
        $batchModel     = new Batch();
        $capitalModel   = new Capital();
        $investmentModel= new Investment();

        // Auto-classified financial picture
        $monitorTotals       = $monitor->totals();
        $monitorMonthTotals  = $monitor->currentMonthTotals();
        $businessAnalysis    = $monitor->businessAnalysis();

        $salesTotals             = $salesModel->totals();
        $summary                 = $dashboardModel->getAdminSummary();
        $financeTotals           = $financeSummary->totals();
        $currentMonthTotals      = $financeSummary->currentMonthTotals();
        $monthlyCombined         = $financeSummary->monthlyCombinedBreakdown(6);
        $recentFinancialActivities = $financeSummary->recentFinancialActivities(12);
        $recentSales             = $salesModel->recent(8);
        $salesByType             = $salesModel->byType();
        $topCustomers            = $salesModel->topCustomers(5);
        $batches                 = $batchModel->all();
        $capitalTotals           = $capitalModel->totals();
        $capitalByType           = $capitalModel->byType();
        $capitalRecords          = $capitalModel->all();
        $investmentTotals        = $investmentModel->totals();
        $investmentByType        = $investmentModel->byType();
        $investmentRecords       = $investmentModel->all();

        $lossMakingBatches = [];
        $bestEggMargin     = null;
        $bestBroilerMargin = null;

        foreach ($batches as $batch) {
            if (($batch['gross_profit'] ?? 0) < 0) {
                $lossMakingBatches[] = $batch;
            }
            if (($batch['egg_margin_per_egg'] ?? 0) > 0) {
                if ($bestEggMargin === null || $batch['egg_margin_per_egg'] > $bestEggMargin['egg_margin_per_egg']) {
                    $bestEggMargin = $batch;
                }
            }
            if (($batch['broiler_margin_per_kg'] ?? 0) > 0) {
                if ($bestBroilerMargin === null || $batch['broiler_margin_per_kg'] > $bestBroilerMargin['broiler_margin_per_kg']) {
                    $bestBroilerMargin = $batch;
                }
            }
        }

        $alerts = [];
        if ($monitorMonthTotals['total_expense'] > $monitorMonthTotals['revenue']) {
            $alerts[] = ['type' => 'danger', 'title' => 'Expenses Exceed Revenue',
                'message' => 'Current month expenses are higher than revenue.'];
        }
        if (!empty($lossMakingBatches)) {
            $alerts[] = ['type' => 'warning', 'title' => 'Loss-Making Batches Detected',
                'message' => count($lossMakingBatches) . ' batch(es) show negative gross profit.'];
        }
        if ((float)($salesTotals['total_outstanding'] ?? 0) > 0) {
            $alerts[] = ['type' => 'info', 'title' => 'Outstanding Receivables',
                'message' => 'GHS ' . number_format((float)$salesTotals['total_outstanding'], 2) . ' in sales is unpaid or partially paid.'];
        }
        if ($monitorTotals['debt_ratio'] > 70) {
            $alerts[] = ['type' => 'warning', 'title' => 'High Debt Ratio',
                'message' => 'Liabilities represent ' . number_format($monitorTotals['debt_ratio'], 1) . '% of total assets.'];
        }
        if ((float)($monitorTotals['total_capital'] ?? 0) === 0.0) {
            $alerts[] = ['type' => 'info', 'title' => 'No Capital Recorded',
                'message' => 'Add owner equity or capital entries to enable full business analysis.'];
        }

        $this->view('financial/dashboard', [
            'pageTitle'                  => 'Financial Dashboard',
            'sidebarType'                => 'financial',
            'monitorTotals'              => $monitorTotals,
            'monitorMonthTotals'         => $monitorMonthTotals,
            'salesTotals'                => $salesTotals,
            'summary'                    => $summary,
            'financeTotals'              => $financeTotals,
            'currentMonthTotals'         => $currentMonthTotals,
            'monthlyCombined'            => $monthlyCombined,
            'recentFinancialActivities'  => $recentFinancialActivities,
            'recentSales'                => $recentSales,
            'salesByType'                => $salesByType,
            'topCustomers'               => $topCustomers,
            'alerts'                     => $alerts,
            'lossMakingBatches'          => $lossMakingBatches,
            'bestEggMargin'              => $bestEggMargin,
            'bestBroilerMargin'          => $bestBroilerMargin,
            'capitalTotals'              => $capitalTotals,
            'capitalByType'              => $capitalByType,
            'capitalRecords'             => $capitalRecords,
            'investmentTotals'           => $investmentTotals,
            'investmentByType'           => $investmentByType,
            'investmentRecords'          => $investmentRecords,
            'businessAnalysis'           => $businessAnalysis,
        ], 'admin');
    }

    public function traceability(): void
    {
        $monitor = new FinancialMonitor();
        
        $traceability = $monitor->getCalculationTraceability();
        $principles = $monitor->getAccountingPrinciples();
        $totals = $monitor->totals();

        $this->view('financial/traceability', [
            'pageTitle' => 'Financial Traceability',
            'sidebarType' => 'financial',
            'traceability' => $traceability,
            'principles' => $principles,
            'totals' => $totals,
        ], 'admin');
    }
}

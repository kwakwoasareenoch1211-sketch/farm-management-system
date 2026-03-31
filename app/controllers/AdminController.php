<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Dashboard.php';
require_once BASE_PATH . 'app/models/FinanceSummary.php';
require_once BASE_PATH . 'app/models/FinancialMonitor.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/Batch.php';

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $dashboardModel = new Dashboard();
        $financeSummary = new FinanceSummary();
        $inventoryItemModel = new InventoryItem();
        $batchModel = new Batch();
        $monitor = new FinancialMonitor();

        $summary = $dashboardModel->getAdminSummary();
        $financeTotals = $financeSummary->totals();
        $currentMonthTotals = $financeSummary->currentMonthTotals();
        $monthlyCombined = $financeSummary->monthlyCombinedBreakdown(6);
        $recentActivities = $dashboardModel->recentActivities(10);
        $lowStockItems = $inventoryItemModel->lowStock();
        $batches = $batchModel->all();
        $monitorTotals = $monitor->totals();

        $assets = (float)($summary['assets_value'] ?? 0);
        $liabilities = (float)($summary['liabilities_value'] ?? 0);
        $currentRevenue = (float)($currentMonthTotals['sales_revenue'] ?? 0);
        $currentExpense = (float)($currentMonthTotals['total_expense'] ?? 0);
        $currentNet = (float)($currentMonthTotals['net_position'] ?? 0);

        $workingCapital = $assets - $liabilities;
        $liquidityRatio = $liabilities > 0 ? ($assets / $liabilities) : ($assets > 0 ? $assets : 0);
        $profitMargin = $currentRevenue > 0 ? (($currentNet / $currentRevenue) * 100) : 0;
        $roi = $currentExpense > 0 ? (($currentNet / $currentExpense) * 100) : 0;

        $lossMakingBatches = [];
        $strongBatches = [];
        $topBatch = null;
        $worstBatch = null;

        foreach ($batches as $batch) {
            $grossProfit = (float)($batch['gross_profit'] ?? 0);

            if ($grossProfit < 0) {
                $lossMakingBatches[] = $batch;
            } else {
                $strongBatches[] = $batch;
            }

            if ($topBatch === null || $grossProfit > (float)($topBatch['gross_profit'] ?? 0)) {
                $topBatch = $batch;
            }

            if ($worstBatch === null || $grossProfit < (float)($worstBatch['gross_profit'] ?? 0)) {
                $worstBatch = $batch;
            }
        }

        $healthScore = 0;
        $healthScore += $profitMargin >= 25 ? 30 : ($profitMargin >= 15 ? 24 : ($profitMargin >= 5 ? 16 : ($profitMargin > 0 ? 10 : 3)));
        $healthScore += $liquidityRatio >= 2 ? 20 : ($liquidityRatio >= 1.2 ? 15 : ($liquidityRatio >= 1 ? 10 : 4));
$healthScore += $assets > $liabilities ? 12 : ($assets == $liabilities ? 8 : 3);
        $healthScore += count($lossMakingBatches) === 0 ? 15 : (count($strongBatches) > count($lossMakingBatches) ? 10 : 4);
        $healthScore += $currentNet > 0 ? 18 : 5;

        $healthLabel = 'Risk';
        $healthClass = 'danger';

        if ($healthScore >= 80) {
            $healthLabel = 'Strong';
            $healthClass = 'success';
        } elseif ($healthScore >= 60) {
            $healthLabel = 'Stable';
            $healthClass = 'warning';
        }

        $goingConcernStatus = 'Caution';
        $goingConcernClass = 'warning';

        if ($assets > $liabilities && $currentRevenue >= $currentExpense && $healthScore >= 60) {
            $goingConcernStatus = 'Healthy';
            $goingConcernClass = 'success';
        } elseif ($assets < $liabilities && $currentRevenue < $currentExpense) {
            $goingConcernStatus = 'At Risk';
            $goingConcernClass = 'danger';
        }

        $decisionRecommendation = 'Stabilize First';
        $decisionClass = 'warning';

        if ($currentNet > 0 && $assets > $liabilities && $healthScore >= 70) {
            $decisionRecommendation = 'Expansion Possible';
            $decisionClass = 'success';
        } elseif ($currentNet < 0 || count($lossMakingBatches) > 0) {
            $decisionRecommendation = 'Review Operations';
            $decisionClass = 'danger';
        }

        $topRisk = 'No major risk detected.';
        if ($currentExpense > $currentRevenue) {
            $topRisk = 'Expenses are currently higher than revenue.';
        } elseif ($liabilities > $assets) {
            $topRisk = 'Liabilities are higher than assets.';
        } elseif (count($lossMakingBatches) > 0) {
            $topRisk = count($lossMakingBatches) . ' batch(es) are loss-making.';
        }

        $topAction = 'Continue monitoring performance and maintain control.';
        if ($decisionRecommendation === 'Expansion Possible') {
            $topAction = 'Expand gradually while protecting liquidity and margins.';
        } elseif ($decisionRecommendation === 'Review Operations') {
            $topAction = 'Review weak batches, high costs, and revenue efficiency immediately.';
        } elseif ($decisionRecommendation === 'Stabilize First') {
            $topAction = 'Improve cash flow and reduce pressure before new growth decisions.';
        }

        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'sidebarType' => 'admin',
            'summary' => $summary,
            'financeTotals' => $financeTotals,
            'currentMonthTotals' => $currentMonthTotals,
            'monthlyCombined' => $monthlyCombined,
            'recentActivities' => $recentActivities,
            'lowStockItems' => $lowStockItems,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'workingCapital' => $workingCapital,
            'liquidityRatio' => $liquidityRatio,
            'profitMargin' => $profitMargin,
            'roi' => $roi,
            'healthScore' => $healthScore,
            'healthLabel' => $healthLabel,
            'healthClass' => $healthClass,
            'goingConcernStatus' => $goingConcernStatus,
            'goingConcernClass' => $goingConcernClass,
            'decisionRecommendation' => $decisionRecommendation,
            'decisionClass' => $decisionClass,
            'topRisk' => $topRisk,
            'topAction' => $topAction,
            'topBatch' => $topBatch,
            'worstBatch' => $worstBatch,
            'lossMakingBatches' => $lossMakingBatches,
            'strongBatches' => $strongBatches,
            'monitorTotals' => $monitorTotals,
        ], 'admin');
    }
}
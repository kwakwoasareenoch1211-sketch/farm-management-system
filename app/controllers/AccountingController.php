<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/FinanceSummary.php';
require_once BASE_PATH . 'app/models/Dashboard.php';
require_once BASE_PATH . 'app/models/Sales.php';
require_once BASE_PATH . 'app/models/Expense.php';

class AccountingController extends Controller
{
    public function index(): void
    {
        $this->profitLoss();
    }

    public function profitLoss(): void
    {
        $financeSummary = new FinanceSummary();
        $dashboardModel = new Dashboard();
        $salesModel = new Sales();
        $expenseModel = new Expense();

        $summary = $dashboardModel->getAdminSummary();
        $financeTotals = $financeSummary->totals();
        $currentMonthTotals = $financeSummary->currentMonthTotals();
        $salesByType = $salesModel->byType();

        $totalRevenue = (float)($financeTotals['sales_revenue'] ?? 0);
        $totalExpenses = (float)($financeTotals['total_expense'] ?? 0);
        $netProfit = (float)($financeTotals['net_position'] ?? 0);

        $monthRevenue = (float)($currentMonthTotals['sales_revenue'] ?? 0);
        $monthExpenses = (float)($currentMonthTotals['total_expense'] ?? 0);
        $monthNet = (float)($currentMonthTotals['net_position'] ?? 0);

        $profitMargin = $totalRevenue > 0 ? (($netProfit / $totalRevenue) * 100) : 0;
        $monthMargin = $monthRevenue > 0 ? (($monthNet / $monthRevenue) * 100) : 0;

        $this->view('accounting/profit-loss', [
            'pageTitle' => 'Profit & Loss',
            'sidebarType' => 'financial',
            'summary' => $summary,
            'financeTotals' => $financeTotals,
            'currentMonthTotals' => $currentMonthTotals,
            'salesByType' => $salesByType,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'monthRevenue' => $monthRevenue,
            'monthExpenses' => $monthExpenses,
            'monthNet' => $monthNet,
            'profitMargin' => $profitMargin,
            'monthMargin' => $monthMargin,
        ], 'admin');
    }
}
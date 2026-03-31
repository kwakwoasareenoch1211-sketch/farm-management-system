<?php

require_once BASE_PATH . 'app/core/Model.php';

class ProfitLossReport extends Model
{
    public function summary(): array
    {
        $sales = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS total_sales
            FROM sales
        ")->fetch(PDO::FETCH_ASSOC);

        $expenses = $this->db->query("
            SELECT COALESCE(SUM(amount), 0) AS total_expenses
            FROM expenses
        ")->fetch(PDO::FETCH_ASSOC);

        $totalSales = (float)($sales['total_sales'] ?? 0);
        $totalExpenses = (float)($expenses['total_expenses'] ?? 0);

        return [
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalSales - $totalExpenses,
        ];
    }
}
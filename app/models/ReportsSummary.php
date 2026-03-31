<?php

require_once BASE_PATH . 'app/core/Model.php';

class ReportsSummary extends Model
{
    public function dashboardTotals(): array
    {
        $sales = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS total_sales
            FROM sales
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $expenses = $this->db->query("
            SELECT COALESCE(SUM(amount), 0) AS total_expenses
            FROM expenses
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        // Inventory system removed - return zeros for compatibility
        $inventory = [
            'total_items' => 0,
            'inventory_value' => 0,
            'low_stock_count' => 0,
        ];

        $batches = $this->db->query("
            SELECT COUNT(*) AS total_batches
            FROM animal_batches
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $mortality = $this->db->query("
            SELECT COALESCE(SUM(quantity), 0) AS total_mortality
            FROM mortality_records
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $eggs = $this->db->query("
            SELECT COALESCE(SUM(quantity), 0) AS total_eggs
            FROM egg_production_records
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $feed = $this->db->query("
            SELECT
                COALESCE(SUM(quantity_kg), 0) AS total_feed_kg,
                COALESCE(SUM(quantity_kg * unit_cost), 0) AS total_feed_cost
            FROM feed_records
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalSales = (float)($sales['total_sales'] ?? 0);
        $totalExpenses = (float)($expenses['total_expenses'] ?? 0);

        return [
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalSales - $totalExpenses,
            'inventory_value' => (float)($inventory['inventory_value'] ?? 0),
            'low_stock_count' => (int)($inventory['low_stock_count'] ?? 0),
            'total_items' => (int)($inventory['total_items'] ?? 0),
            'total_batches' => (int)($batches['total_batches'] ?? 0),
            'total_mortality' => (float)($mortality['total_mortality'] ?? 0),
            'total_eggs' => (float)($eggs['total_eggs'] ?? 0),
            'total_feed_kg' => (float)($feed['total_feed_kg'] ?? 0),
            'total_feed_cost' => (float)($feed['total_feed_cost'] ?? 0),
        ];
    }

    public function monthlyRevenueVsExpense(int $limit = 6): array
    {
        $salesRows = $this->db->query("
            SELECT
                DATE_FORMAT(sale_date, '%Y-%m') AS month_key,
                DATE_FORMAT(sale_date, '%b %Y') AS month_label,
                COALESCE(SUM(total_amount), 0) AS revenue
            FROM sales
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m'), DATE_FORMAT(sale_date, '%b %Y')
            ORDER BY month_key ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $expenseRows = $this->db->query("
            SELECT
                DATE_FORMAT(expense_date, '%Y-%m') AS month_key,
                DATE_FORMAT(expense_date, '%b %Y') AS month_label,
                COALESCE(SUM(amount), 0) AS expenses
            FROM expenses
            GROUP BY DATE_FORMAT(expense_date, '%Y-%m'), DATE_FORMAT(expense_date, '%b %Y')
            ORDER BY month_key ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $map = [];

        foreach ($salesRows as $row) {
            $map[$row['month_key']] = [
                'month_key' => $row['month_key'],
                'month_label' => $row['month_label'],
                'revenue' => (float)$row['revenue'],
                'expenses' => 0,
            ];
        }

        foreach ($expenseRows as $row) {
            $key = $row['month_key'];
            if (!isset($map[$key])) {
                $map[$key] = [
                    'month_key' => $key,
                    'month_label' => $row['month_label'],
                    'revenue' => 0,
                    'expenses' => (float)$row['expenses'],
                ];
            } else {
                $map[$key]['expenses'] = (float)$row['expenses'];
            }
        }

        ksort($map);
        $rows = array_values($map);
        $rows = array_slice($rows, -$limit);

        foreach ($rows as &$row) {
            $row['net'] = $row['revenue'] - $row['expenses'];
        }

        return $rows;
    }

    public function recentActivities(int $limit = 10): array
    {
        $sales = $this->db->query("
            SELECT
                sale_date AS activity_date,
                'Sale' AS activity_type,
                CONCAT(product_type, ' - ', quantity, ' units') AS title,
                total_amount AS amount
            FROM sales
            ORDER BY sale_date DESC, id DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $expenses = $this->db->query("
            SELECT
                expense_date AS activity_date,
                'Expense' AS activity_type,
                COALESCE(description, CONCAT('Expense #', id)) AS title,
                amount
            FROM expenses
            ORDER BY expense_date DESC, id DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $activities = array_merge($sales, $expenses);

        usort($activities, function ($a, $b) {
            return strcmp($b['activity_date'], $a['activity_date']);
        });

        return array_slice($activities, 0, $limit);
    }
}
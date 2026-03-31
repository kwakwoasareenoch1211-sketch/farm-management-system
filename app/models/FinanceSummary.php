<?php

require_once BASE_PATH . 'app/core/Model.php';

class FinanceSummary extends Model
{
    public function totals(): array
    {
        $feedExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(quantity_kg * unit_cost), 0)
            FROM feed_records
        ");

        $medicationExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(quantity_used * unit_cost), 0)
            FROM medication_records
        ");

        $vaccinationExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(cost_amount), 0)
            FROM vaccination_records
        ", true);

        $directExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
        ");

        $salesRevenue = (float)$this->singleValue("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM sales
        ");

        $totalExpense = $feedExpense + $medicationExpense + $vaccinationExpense + $directExpense;
        $net = $salesRevenue - $totalExpense;

        return [
            'feed_expense' => $feedExpense,
            'medication_expense' => $medicationExpense,
            'vaccination_expense' => $vaccinationExpense,
            'direct_expense' => $directExpense,
            'total_expense' => $totalExpense,
            'sales_revenue' => $salesRevenue,
            'net_position' => $net,
        ];
    }

    public function currentMonthTotals(): array
    {
        $feedExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(quantity_kg * unit_cost), 0)
            FROM feed_records
            WHERE YEAR(record_date) = YEAR(CURDATE())
              AND MONTH(record_date) = MONTH(CURDATE())
        ");

        $medicationExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(quantity_used * unit_cost), 0)
            FROM medication_records
            WHERE YEAR(record_date) = YEAR(CURDATE())
              AND MONTH(record_date) = MONTH(CURDATE())
        ");

        $vaccinationExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(cost_amount), 0)
            FROM vaccination_records
            WHERE YEAR(record_date) = YEAR(CURDATE())
              AND MONTH(record_date) = MONTH(CURDATE())
        ", true);

        $directExpense = (float)$this->singleValue("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE YEAR(expense_date) = YEAR(CURDATE())
              AND MONTH(expense_date) = MONTH(CURDATE())
        ");

        $salesRevenue = (float)$this->singleValue("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM sales
            WHERE YEAR(sale_date) = YEAR(CURDATE())
              AND MONTH(sale_date) = MONTH(CURDATE())
        ");

        $totalExpense = $feedExpense + $medicationExpense + $vaccinationExpense + $directExpense;
        $net = $salesRevenue - $totalExpense;

        return [
            'feed_expense' => $feedExpense,
            'medication_expense' => $medicationExpense,
            'vaccination_expense' => $vaccinationExpense,
            'direct_expense' => $directExpense,
            'total_expense' => $totalExpense,
            'sales_revenue' => $salesRevenue,
            'net_position' => $net,
        ];
    }

    public function monthlyCombinedBreakdown(int $limit = 6): array
    {
        $months = [];

        $feedRows = $this->fetchAllSafe("
            SELECT DATE_FORMAT(record_date, '%Y-%m') AS month_key,
                   DATE_FORMAT(record_date, '%b %Y') AS month_label,
                   COALESCE(SUM(quantity_kg * unit_cost), 0) AS total
            FROM feed_records
            GROUP BY DATE_FORMAT(record_date, '%Y-%m'), DATE_FORMAT(record_date, '%b %Y')
        ");

        $medRows = $this->fetchAllSafe("
            SELECT DATE_FORMAT(record_date, '%Y-%m') AS month_key,
                   DATE_FORMAT(record_date, '%b %Y') AS month_label,
                   COALESCE(SUM(quantity_used * unit_cost), 0) AS total
            FROM medication_records
            GROUP BY DATE_FORMAT(record_date, '%Y-%m'), DATE_FORMAT(record_date, '%b %Y')
        ");

        $vacRows = $this->fetchAllSafe("
            SELECT DATE_FORMAT(record_date, '%Y-%m') AS month_key,
                   DATE_FORMAT(record_date, '%b %Y') AS month_label,
                   COALESCE(SUM(cost_amount), 0) AS total
            FROM vaccination_records
            GROUP BY DATE_FORMAT(record_date, '%Y-%m'), DATE_FORMAT(record_date, '%b %Y')
        ");

        $expenseRows = $this->fetchAllSafe("
            SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month_key,
                   DATE_FORMAT(expense_date, '%b %Y') AS month_label,
                   COALESCE(SUM(amount), 0) AS total
            FROM expenses
            GROUP BY DATE_FORMAT(expense_date, '%Y-%m'), DATE_FORMAT(expense_date, '%b %Y')
        ");

        $salesRows = $this->fetchAllSafe("
            SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month_key,
                   DATE_FORMAT(sale_date, '%b %Y') AS month_label,
                   COALESCE(SUM(total_amount), 0) AS total
            FROM sales
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m'), DATE_FORMAT(sale_date, '%b %Y')
        ");

        foreach ($feedRows as $row) {
            $this->initMonth($months, $row['month_key'], $row['month_label']);
            $months[$row['month_key']]['feed_expense'] += (float)$row['total'];
        }

        foreach ($medRows as $row) {
            $this->initMonth($months, $row['month_key'], $row['month_label']);
            $months[$row['month_key']]['medication_expense'] += (float)$row['total'];
        }

        foreach ($vacRows as $row) {
            $this->initMonth($months, $row['month_key'], $row['month_label']);
            $months[$row['month_key']]['vaccination_expense'] += (float)$row['total'];
        }

        foreach ($expenseRows as $row) {
            $this->initMonth($months, $row['month_key'], $row['month_label']);
            $months[$row['month_key']]['direct_expense'] += (float)$row['total'];
        }

        foreach ($salesRows as $row) {
            $this->initMonth($months, $row['month_key'], $row['month_label']);
            $months[$row['month_key']]['sales_revenue'] += (float)$row['total'];
        }

        ksort($months);
        $months = array_values($months);
        $months = array_slice($months, -$limit);

        foreach ($months as &$month) {
            $month['total_expense'] =
                $month['feed_expense'] +
                $month['medication_expense'] +
                $month['vaccination_expense'] +
                $month['direct_expense'];

            $month['net_position'] = $month['sales_revenue'] - $month['total_expense'];
        }

        return $months;
    }

    public function recentFinancialActivities(int $limit = 12): array
    {
        $activities = [];

        $activities = array_merge(
            $activities,
            $this->mapRows($this->fetchAllSafe("
                SELECT id, record_date AS activity_date, 'Feed Expense' AS activity_type,
                       COALESCE(feed_name, 'Feed') AS title,
                       (quantity_kg * unit_cost) AS amount
                FROM feed_records
            "), 'expense'),

            $this->mapRows($this->fetchAllSafe("
                SELECT id, record_date AS activity_date, 'Medication Expense' AS activity_type,
                       medication_name AS title,
                       (quantity_used * unit_cost) AS amount
                FROM medication_records
            "), 'expense'),

            $this->mapRows($this->fetchAllSafe("
                SELECT id, record_date AS activity_date, 'Vaccination Expense' AS activity_type,
                       vaccine_name AS title,
                       COALESCE(cost_amount, 0) AS amount
                FROM vaccination_records
            "), 'expense'),

            $this->mapRows($this->fetchAllSafe("
                SELECT id, expense_date AS activity_date, 'Direct Expense' AS activity_type,
                       expense_title AS title,
                       amount
                FROM expenses
            "), 'expense'),

            $this->mapRows($this->fetchAllSafe("
                SELECT id, sale_date AS activity_date, 'Sales Revenue' AS activity_type,
                       COALESCE(reference_no, sale_type, 'Sale') AS title,
                       total_amount AS amount
                FROM sales
            "), 'revenue')
        );

        usort($activities, function ($a, $b) {
            return strcmp($b['activity_date'], $a['activity_date']);
        });

        return array_slice($activities, 0, $limit);
    }

    private function initMonth(array &$months, string $key, string $label): void
    {
        if (!isset($months[$key])) {
            $months[$key] = [
                'month_key' => $key,
                'month_label' => $label,
                'feed_expense' => 0,
                'medication_expense' => 0,
                'vaccination_expense' => 0,
                'direct_expense' => 0,
                'sales_revenue' => 0,
                'total_expense' => 0,
                'net_position' => 0,
            ];
        }
    }

    private function mapRows(array $rows, string $direction): array
    {
        return array_map(function ($row) use ($direction) {
            return [
                'id' => $row['id'],
                'activity_date' => $row['activity_date'],
                'activity_type' => $row['activity_type'],
                'title' => $row['title'],
                'amount' => (float)$row['amount'],
                'direction' => $direction,
            ];
        }, $rows);
    }

    private function singleValue(string $sql, bool $allowMissingColumn = false)
    {
        try {
            return $this->db->query($sql)->fetchColumn();
        } catch (Throwable $e) {
            if ($allowMissingColumn) {
                return 0;
            }
            throw $e;
        }
    }

    private function fetchAllSafe(string $sql): array
    {
        try {
            return $this->db->query($sql)->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
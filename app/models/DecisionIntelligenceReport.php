<?php

require_once BASE_PATH . 'app/core/Model.php';

class DecisionIntelligenceReport extends Model
{
    public function smartSignals(): array
    {
        $signals = [];

        $sales = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS total_sales
            FROM sales
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $expenses = $this->db->query("
            SELECT COALESCE(SUM(amount), 0) AS total_expenses
            FROM expenses
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalSales = (float)($sales['total_sales'] ?? 0);
        $totalExpenses = (float)($expenses['total_expenses'] ?? 0);

        if ($totalExpenses > $totalSales) {
            $signals[] = [
                'type' => 'danger',
                'title' => 'Loss Position',
                'message' => 'Overall expenses are higher than sales.'
            ];
        }

        // Inventory system removed - return zero for low stock count
        $lowStock = ['total_low' => 0];

        if ((int)($lowStock['total_low'] ?? 0) > 0) {
            $signals[] = [
                'type' => 'warning',
                'title' => 'Low Stock Pressure',
                'message' => 'Some inventory items are at or below reorder level.'
            ];
        }

        $mortality = $this->db->query("
            SELECT COALESCE(SUM(quantity), 0) AS total_mortality
            FROM mortality_records
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        if ((float)($mortality['total_mortality'] ?? 0) > 0) {
            $signals[] = [
                'type' => 'info',
                'title' => 'Health Monitoring Required',
                'message' => 'Mortality has been recorded. Review mortality and vaccination reports.'
            ];
        }

        return $signals;
    }

    public function topProfitableBatches(int $limit = 5): array
    {
        $rows = $this->db->query("
            SELECT
                ab.id,
                ab.batch_code,
                ab.batch_name,
                ab.production_purpose,
                COALESCE((
                    SELECT SUM(s.total_amount)
                    FROM sales s
                    WHERE s.batch_id = ab.id
                ), 0) AS total_sales,
                COALESCE((
                    SELECT SUM(f.quantity_kg * f.unit_cost)
                    FROM feed_records f
                    WHERE f.batch_id = ab.id
                ), 0) + COALESCE((
                    SELECT SUM(m.quantity_used * m.unit_cost)
                    FROM medication_records m
                    WHERE m.batch_id = ab.id
                ), 0) + COALESCE((
                    SELECT SUM(v.cost_amount)
                    FROM vaccination_records v
                    WHERE v.batch_id = ab.id
                ), 0) AS total_expenses
            FROM animal_batches ab
            ORDER BY ab.start_date DESC, ab.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['gross_profit'] = (float)$row['total_sales'] - (float)$row['total_expenses'];
        }

        usort($rows, fn($a, $b) => $b['gross_profit'] <=> $a['gross_profit']);

        return array_slice($rows, 0, $limit);
    }

    public function highMortalityBatches(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT
                ab.batch_code,
                ab.batch_name,
                ab.initial_quantity,
                COALESCE(SUM(mr.quantity), 0) AS total_mortality
            FROM animal_batches ab
            LEFT JOIN mortality_records mr ON mr.batch_id = ab.id
            GROUP BY ab.id, ab.batch_code, ab.batch_name, ab.initial_quantity
            ORDER BY total_mortality DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $initial = (float)($row['initial_quantity'] ?? 0);
            $mortality = (float)($row['total_mortality'] ?? 0);
            $row['mortality_rate'] = $initial > 0 ? ($mortality / $initial) * 100 : 0;
        }

        return $rows;
    }

    public function lowStockPressure(int $limit = 5): array
    {
        // Inventory system removed - return empty array
        return [];
    }
}
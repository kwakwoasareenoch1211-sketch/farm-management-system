<?php

require_once BASE_PATH . 'app/core/Model.php';

class BatchPerformanceReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.id,
                ab.batch_code,
                ab.batch_name,
                ab.production_purpose,
                ab.bird_subtype,
                ab.breed,
                ab.start_date,
                ab.initial_quantity,
                ab.current_quantity,
                ab.initial_unit_cost,
                ab.status,
                COALESCE((
                    SELECT SUM(fr.quantity_kg)
                    FROM feed_records fr
                    WHERE fr.batch_id = ab.id
                ), 0) AS total_feed_kg,
                COALESCE((
                    SELECT SUM(mr.quantity)
                    FROM mortality_records mr
                    WHERE mr.batch_id = ab.id
                ), 0) AS total_mortality,
                COALESCE((
                    SELECT SUM(e.amount)
                    FROM expenses e
                    WHERE e.batch_id = ab.id
                ), 0) AS total_expenses,
                COALESCE((
                    SELECT SUM(s.total_amount)
                    FROM sales s
                    WHERE s.batch_id = ab.id
                ), 0) AS total_sales
            FROM animal_batches ab
            ORDER BY ab.start_date DESC, ab.id DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['gross_profit'] = (float)($row['total_sales'] ?? 0) - (float)($row['total_expenses'] ?? 0);
        }

        return $rows;
    }
}
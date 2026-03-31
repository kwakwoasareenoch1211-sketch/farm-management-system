<?php

require_once BASE_PATH . 'app/core/Model.php';

class WeightReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                w.*,
                ab.batch_code,
                ab.batch_name
            FROM weight_records w
            LEFT JOIN animal_batches ab ON ab.id = w.batch_id
            ORDER BY w.record_date DESC, w.id DESC
        ");

        return $stmt->fetchAll() ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(AVG(average_weight_kg), 0) AS avg_weight_kg,
                COALESCE(MAX(average_weight_kg), 0) AS max_weight_kg,
                COALESCE(MIN(average_weight_kg), 0) AS min_weight_kg
            FROM weight_records
        ");

        return $stmt->fetch() ?: [
            'total_records' => 0,
            'avg_weight_kg' => 0,
            'max_weight_kg' => 0,
            'min_weight_kg' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COUNT(w.id) AS total_records,
                COALESCE(AVG(w.average_weight_kg), 0) AS avg_weight_kg,
                COALESCE(MAX(w.average_weight_kg), 0) AS max_weight_kg
            FROM weight_records w
            LEFT JOIN animal_batches ab ON ab.id = w.batch_id
            GROUP BY w.batch_id, ab.batch_code, ab.batch_name
            ORDER BY avg_weight_kg DESC
        ");

        return $stmt->fetchAll() ?: [];
    }
}

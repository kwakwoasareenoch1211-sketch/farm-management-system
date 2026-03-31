<?php

require_once BASE_PATH . 'app/core/Model.php';

class WeightReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                wr.record_date,
                ab.batch_code,
                ab.batch_name,
                wr.average_weight,
                wr.notes
            FROM weight_records wr
            LEFT JOIN animal_batches ab ON ab.id = wr.batch_id
            ORDER BY wr.record_date DESC, wr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(AVG(average_weight), 0) AS average_weight
            FROM weight_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records' => 0,
            'average_weight' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COALESCE(AVG(wr.average_weight), 0) AS average_weight
            FROM animal_batches ab
            LEFT JOIN weight_records wr ON wr.batch_id = ab.id
            GROUP BY ab.id, ab.batch_code, ab.batch_name
            ORDER BY average_weight DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
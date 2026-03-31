<?php

require_once BASE_PATH . 'app/core/Model.php';

class MortalityReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                mr.record_date,
                ab.batch_code,
                ab.batch_name,
                mr.quantity,
                mr.cause,
                mr.notes
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            ORDER BY mr.record_date DESC, mr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(quantity), 0) AS total_mortality,
                COUNT(*) AS total_records
            FROM mortality_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_mortality' => 0,
            'total_records' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COALESCE(SUM(mr.quantity), 0) AS total_mortality
            FROM animal_batches ab
            LEFT JOIN mortality_records mr ON mr.batch_id = ab.id
            GROUP BY ab.id, ab.batch_code, ab.batch_name
            ORDER BY total_mortality DESC, ab.batch_name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
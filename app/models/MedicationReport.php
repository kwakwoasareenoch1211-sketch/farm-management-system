<?php

require_once BASE_PATH . 'app/core/Model.php';

class MedicationReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                m.*,
                ab.batch_code,
                ab.batch_name
            FROM medication_records m
            LEFT JOIN animal_batches ab ON ab.id = m.batch_id
            ORDER BY m.record_date DESC, m.id DESC
        ");

        return $stmt->fetchAll() ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity_used), 0) AS total_quantity_used,
                COALESCE(SUM(quantity_used * unit_cost), 0) AS total_cost
            FROM medication_records
        ");

        return $stmt->fetch() ?: [
            'total_records' => 0,
            'total_quantity_used' => 0,
            'total_cost' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COUNT(m.id) AS total_records,
                COALESCE(SUM(m.quantity_used), 0) AS total_quantity_used,
                COALESCE(SUM(m.quantity_used * m.unit_cost), 0) AS total_cost
            FROM medication_records m
            LEFT JOIN animal_batches ab ON ab.id = m.batch_id
            GROUP BY m.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_cost DESC
        ");

        return $stmt->fetchAll() ?: [];
    }
}

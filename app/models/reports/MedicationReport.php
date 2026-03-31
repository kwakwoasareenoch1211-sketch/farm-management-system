<?php

require_once BASE_PATH . 'app/core/Model.php';

class MedicationReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                mr.record_date,
                ab.batch_code,
                ab.batch_name,
                mr.medication_name,
                mr.condition_treated,
                mr.dosage,
                mr.quantity_used,
                mr.unit,
                mr.unit_cost,
                mr.total_cost,
                mr.administered_by,
                mr.withdrawal_period_days,
                mr.notes
            FROM medication_records mr
            LEFT JOIN animal_batches ab
                ON ab.id = mr.batch_id
            ORDER BY mr.record_date DESC, mr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity_used), 0) AS total_quantity_used,
                COALESCE(SUM(total_cost), 0) AS total_cost
            FROM medication_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
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
                COUNT(mr.id) AS treatments,
                COALESCE(SUM(mr.quantity_used), 0) AS total_quantity_used,
                COALESCE(SUM(mr.total_cost), 0) AS total_cost
            FROM animal_batches ab
            LEFT JOIN medication_records mr
                ON mr.batch_id = ab.id
            GROUP BY ab.id, ab.batch_code, ab.batch_name
            ORDER BY total_cost DESC, treatments DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
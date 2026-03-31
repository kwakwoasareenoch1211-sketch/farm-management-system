<?php

require_once BASE_PATH . 'app/core/Model.php';

class EggProductionReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                e.id,
                e.farm_id,
                e.batch_id,
                e.record_date,
                e.quantity,
                e.trays_equivalent,
                e.notes,
                e.created_at,
                ab.batch_code,
                ab.batch_name
            FROM egg_production_records e
            LEFT JOIN animal_batches ab ON ab.id = e.batch_id
            ORDER BY e.record_date DESC, e.id DESC
        ");

        return $stmt->fetchAll() ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity), 0) AS total_eggs,
                COALESCE(SUM(trays_equivalent), 0) AS total_trays
            FROM egg_production_records
        ");

        return $stmt->fetch() ?: [
            'total_records' => 0,
            'total_eggs' => 0,
            'total_trays' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COUNT(e.id) AS total_records,
                COALESCE(SUM(e.quantity), 0) AS total_eggs,
                COALESCE(SUM(e.trays_equivalent), 0) AS total_trays
            FROM egg_production_records e
            LEFT JOIN animal_batches ab ON ab.id = e.batch_id
            GROUP BY e.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_eggs DESC
        ");

        return $stmt->fetchAll() ?: [];
    }
}
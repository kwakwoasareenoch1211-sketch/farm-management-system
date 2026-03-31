<?php

require_once BASE_PATH . 'app/core/Model.php';

class FeedConsumptionReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                fr.record_date,
                ab.batch_code,
                ab.batch_name,
                fr.feed_type,
                fr.quantity_kg,
                fr.unit_cost,
                fr.total_cost,
                fr.notes
            FROM feed_records fr
            LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
            ORDER BY fr.record_date DESC, fr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(quantity_kg), 0) AS total_feed_kg,
                COALESCE(SUM(total_cost), 0) AS total_feed_cost,
                COUNT(*) AS total_records
            FROM feed_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_feed_kg' => 0,
            'total_feed_cost' => 0,
            'total_records' => 0,
        ];
    }
}
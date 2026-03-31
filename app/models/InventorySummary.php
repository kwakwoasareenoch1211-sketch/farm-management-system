<?php

require_once BASE_PATH . 'app/core/Model.php';

class InventorySummary extends Model
{
    public function totals(): array
    {
        // Since inventory was unified with feed/medication systems,
        // calculate totals from feed and medication records
        $feedTotal = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(quantity_kg * unit_cost), 0) AS total
            FROM feed_records
            WHERE unit_cost > 0
        ")->fetch(PDO::FETCH_ASSOC);

        $medTotal = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(quantity_used * unit_cost), 0) AS total
            FROM medication_records
            WHERE unit_cost > 0 AND quantity_used > 0
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            'total_items' => (int)($feedTotal['cnt'] ?? 0) + (int)($medTotal['cnt'] ?? 0),
            'total_value' => (float)($feedTotal['total'] ?? 0) + (float)($medTotal['total'] ?? 0),
        ];
    }

    public function recentInventoryActivities(int $limit = 12): array
    {
        // Show recent feed and medication usage
        $activities = [];

        // 1. Feed usage records
        $stmt = $this->db->prepare("
            SELECT
                'feed_usage' AS activity_type,
                fr.record_date AS activity_date,
                fr.created_at,
                fr.feed_name AS item_name,
                'feed' AS category,
                fr.quantity_kg AS quantity,
                CONCAT('Batch: ', COALESCE(ab.batch_code, 'N/A')) AS reference_no,
                fr.notes
            FROM feed_records fr
            LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
            ORDER BY fr.record_date DESC, fr.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // 2. Medication usage records
        $stmt = $this->db->prepare("
            SELECT
                'medication_usage' AS activity_type,
                mr.record_date AS activity_date,
                mr.created_at,
                mr.medication_name AS item_name,
                'medication' AS category,
                mr.quantity_used AS quantity,
                CONCAT('Batch: ', COALESCE(ab.batch_code, 'N/A')) AS reference_no,
                mr.notes
            FROM medication_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            ORDER BY mr.record_date DESC, mr.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // Sort all activities by date descending
        usort($activities, function($a, $b) {
            return strtotime($b['activity_date']) - strtotime($a['activity_date']);
        });

        // Return only the requested limit
        return array_slice($activities, 0, $limit);
    }

    public function categorySummary(): array
    {
        // Summarize by feed and medication categories
        $summary = [];

        // Feed summary
        $feedCount = $this->db->query("SELECT COUNT(*) as cnt FROM feed_records")->fetch()['cnt'] ?? 0;
        $feedCost = $this->db->query("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) as total FROM feed_records WHERE unit_cost > 0")->fetch()['total'] ?? 0;
        
        if ($feedCount > 0) {
            $summary[] = [
                'category' => 'feed',
                'total_items' => (int)$feedCount,
                'avg_cost' => $feedCount > 0 ? ($feedCost / $feedCount) : 0,
            ];
        }

        // Medication summary
        $medCount = $this->db->query("SELECT COUNT(*) as cnt FROM medication_records WHERE quantity_used > 0")->fetch()['cnt'] ?? 0;
        $medCost = $this->db->query("SELECT COALESCE(SUM(quantity_used * unit_cost), 0) as total FROM medication_records WHERE unit_cost > 0 AND quantity_used > 0")->fetch()['total'] ?? 0;
        
        if ($medCount > 0) {
            $summary[] = [
                'category' => 'medication',
                'total_items' => (int)$medCount,
                'avg_cost' => $medCount > 0 ? ($medCost / $medCount) : 0,
            ];
        }

        return $summary;
    }

    public function topValuedItems(int $limit = 5): array
    {
        // Get top valued feed and medication records
        $items = [];

        // Top feed records by cost
        $stmt = $this->db->prepare("
            SELECT
                'feed' AS category,
                feed_name AS item_name,
                quantity_kg AS quantity,
                unit_cost,
                (quantity_kg * unit_cost) AS total_value,
                record_date
            FROM feed_records
            WHERE unit_cost > 0
            ORDER BY (quantity_kg * unit_cost) DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_merge($items, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // Top medication records by cost
        $stmt = $this->db->prepare("
            SELECT
                'medication' AS category,
                medication_name AS item_name,
                quantity_used AS quantity,
                unit_cost,
                (quantity_used * unit_cost) AS total_value,
                record_date
            FROM medication_records
            WHERE unit_cost > 0 AND quantity_used > 0
            ORDER BY (quantity_used * unit_cost) DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_merge($items, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // Sort by total value and return top items
        usort($items, function($a, $b) {
            return $b['total_value'] <=> $a['total_value'];
        });

        return array_slice($items, 0, $limit);
    }

    public function feedUsageSummary(): array
    {
        $stmt = $this->db->query("
            SELECT
                feed_name AS item_name,
                'feed' AS category,
                COUNT(id) AS usage_count,
                COALESCE(SUM(quantity_kg), 0) AS total_quantity,
                COALESCE(SUM(quantity_kg * unit_cost), 0) AS total_cost
            FROM feed_records
            WHERE unit_cost > 0
            GROUP BY feed_name
            ORDER BY total_quantity DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

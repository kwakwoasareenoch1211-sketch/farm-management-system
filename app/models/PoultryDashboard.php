<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * Aggregates all poultry-specific dashboard data.
 * Provides $summary and $extraMetrics expected by app/views/poultry/dashboard.php
 */
class PoultryDashboard extends Model
{
    public function getSummary(): array
    {
        return [
            'total_birds'       => (float)$this->scalar("SELECT COALESCE(SUM(current_quantity),0) FROM animal_batches"),
            'active_batches'    => (int)$this->scalar("SELECT COUNT(*) FROM animal_batches WHERE status='active'"),
            'total_batches'     => (int)$this->scalar("SELECT COUNT(*) FROM animal_batches"),
            'total_eggs'        => (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM egg_production_records"),
            'total_mortality'   => (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM mortality_records"),
            'total_feed_used_kg'=> (float)$this->scalar("SELECT COALESCE(SUM(quantity_kg),0) FROM feed_records"),
            'low_stock_count'   => 0, // Inventory system removed
        ];
    }

    public function getExtraMetrics(): array
    {
        // Average FCR across active batches (feed_kg / live_weight_kg)
        $avgFcr = (float)$this->scalar("
            SELECT COALESCE(AVG(
                CASE WHEN b.current_quantity > 0 AND w.avg_wt > 0
                     THEN f.feed_kg / (b.current_quantity * w.avg_wt)
                     ELSE 0 END
            ), 0)
            FROM animal_batches b
            LEFT JOIN (
                SELECT batch_id, COALESCE(SUM(quantity_kg),0) AS feed_kg
                FROM feed_records GROUP BY batch_id
            ) f ON f.batch_id = b.id
            LEFT JOIN (
                SELECT batch_id, COALESCE(AVG(average_weight_kg),0) AS avg_wt
                FROM weight_records GROUP BY batch_id
            ) w ON w.batch_id = b.id
            WHERE b.status = 'active'
        ");

        $avgWeight = (float)$this->scalar("
            SELECT COALESCE(AVG(average_weight_kg), 0)
            FROM weight_records
            WHERE (batch_id, record_date) IN (
                SELECT batch_id, MAX(record_date) FROM weight_records GROUP BY batch_id
            )
        ");

        $vacOverdue = (int)$this->scalar("
            SELECT COUNT(*) FROM vaccination_records
            WHERE next_due_date IS NOT NULL AND next_due_date < CURDATE()
        ");

        $vacDueSoon = (int)$this->scalar("
            SELECT COUNT(*) FROM vaccination_records
            WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ");

        $medRecords = (int)$this->scalar("SELECT COUNT(*) FROM medication_records");

        $medCost = (float)$this->scalar("
            SELECT COALESCE(SUM(quantity_used * unit_cost), 0) FROM medication_records
        ");

        return [
            'average_fcr'          => $avgFcr,
            'average_weight_kg'    => $avgWeight,
            'vaccination_overdue'  => $vacOverdue,
            'vaccination_due_soon' => $vacDueSoon,
            'medication_records'   => $medRecords,
            'medication_cost'      => $medCost,
        ];
    }

    public function getLowStockItems(): array
    {
        // Inventory system removed - return empty array
        return [];
    }

    public function getOwnerStats(): array
    {
        $this->db = Database::connect();
        $owners = $this->db->query("SELECT id, full_name, username FROM users WHERE role IN ('owner','admin') AND is_active=1 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $colors = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6'];
        $numOwners = max(count($owners), 1);

        $result = [];
        foreach ($owners as $i => $owner) {
            $oid = (int)$owner['id'];
            // Shared records split equally between owners
            $sharedBirds   = (float)$this->scalar("SELECT COALESCE(SUM(current_quantity),0) FROM animal_batches WHERE is_shared=1") / $numOwners;
            $sharedEggs    = (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM egg_production_records WHERE is_shared=1") / $numOwners;
            $sharedMort    = (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM mortality_records WHERE is_shared=1") / $numOwners;
            $sharedFeedKg  = (float)$this->scalar("SELECT COALESCE(SUM(quantity_kg),0) FROM feed_records WHERE is_shared=1") / $numOwners;
            $sharedFeedCost= (float)$this->scalar("SELECT COALESCE(SUM(quantity_kg*unit_cost),0) FROM feed_records WHERE is_shared=1") / $numOwners;
            $sharedMedCost = (float)$this->scalar("SELECT COALESCE(SUM(quantity_used*unit_cost),0) FROM medication_records WHERE is_shared=1") / $numOwners;
            $sharedVacCost = (float)$this->scalar("SELECT COALESCE(SUM(cost_amount),0) FROM vaccination_records WHERE is_shared=1") / $numOwners;

            $result[] = [
                'id'        => $oid,
                'name'      => $owner['full_name'],
                'username'  => $owner['username'],
                'color'     => $colors[$i % count($colors)],
                'batches'   => (int)$this->scalar("SELECT COUNT(*) FROM animal_batches WHERE owner_id=$oid") + (int)($this->scalar("SELECT COUNT(*) FROM animal_batches WHERE is_shared=1") / $numOwners),
                'birds'     => (float)$this->scalar("SELECT COALESCE(SUM(current_quantity),0) FROM animal_batches WHERE owner_id=$oid") + $sharedBirds,
                'eggs'      => (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM egg_production_records WHERE owner_id=$oid") + $sharedEggs,
                'mortality' => (float)$this->scalar("SELECT COALESCE(SUM(quantity),0) FROM mortality_records WHERE owner_id=$oid") + $sharedMort,
                'feed_kg'   => (float)$this->scalar("SELECT COALESCE(SUM(quantity_kg),0) FROM feed_records WHERE owner_id=$oid") + $sharedFeedKg,
                'feed_cost' => (float)$this->scalar("SELECT COALESCE(SUM(quantity_kg*unit_cost),0) FROM feed_records WHERE owner_id=$oid") + $sharedFeedCost,
                'med_cost'  => (float)$this->scalar("SELECT COALESCE(SUM(quantity_used*unit_cost),0) FROM medication_records WHERE owner_id=$oid") + $sharedMedCost,
                'vac_cost'  => (float)$this->scalar("SELECT COALESCE(SUM(cost_amount),0) FROM vaccination_records WHERE owner_id=$oid") + $sharedVacCost,
            ];
        }
        return $result;
    }

    private function scalar(string $sql): mixed
    {
        try {
            $val = $this->db->query($sql)->fetchColumn();
            return $val !== false ? $val : 0;
        } catch (Throwable) {
            return 0;
        }
    }
}

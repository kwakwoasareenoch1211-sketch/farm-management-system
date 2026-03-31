<?php

require_once BASE_PATH . 'app/core/Model.php';

class StockMovementReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                sm.movement_date,
                sm.movement_type,
                sm.quantity,
                sm.unit_cost,
                sm.total_cost,
                sm.reference_type,
                sm.notes,
                ii.item_name,
                f.farm_name
            FROM stock_movements sm
            LEFT JOIN inventory_item ii ON ii.id = sm.inventory_item_id
            LEFT JOIN farms f ON f.id = sm.farm_id
            ORDER BY sm.movement_date DESC, sm.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
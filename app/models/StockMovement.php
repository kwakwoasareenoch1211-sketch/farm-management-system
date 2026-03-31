<?php

require_once BASE_PATH . 'app/core/Model.php';

class StockMovement extends Model
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (
                item_id,
                movement_type,
                quantity,
                movement_date,
                reference_no,
                notes
            ) VALUES (
                :item_id,
                :movement_type,
                :quantity,
                :movement_date,
                :reference_no,
                :notes
            )
        ");

        return $stmt->execute([
            ':item_id'        => (int)$data['item_id'],
            ':movement_type'  => $data['movement_type'],
            ':quantity'       => (float)($data['quantity'] ?? 0),
            ':movement_date'  => $data['movement_date'],
            ':reference_no'   => $data['reference_no'] ?? null,
            ':notes'          => !empty($data['notes']) ? trim((string)$data['notes']) : null,
        ]);
    }
}
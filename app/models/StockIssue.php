<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/StockMovement.php';

class StockIssue extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                si.*,
                ii.item_name,
                ab.batch_name
            FROM stock_issues si
            LEFT JOIN inventory_item ii ON ii.id = si.item_id
            LEFT JOIN animal_batches ab ON ab.id = si.batch_id
            ORDER BY si.issue_date DESC, si.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $itemModel = new InventoryItem();
            $item = $itemModel->find((int)$data['inventory_item_id']);

            if (!$item) {
                $this->db->rollBack();
                return false;
            }

            if ((float)$item['current_stock'] < (float)$data['quantity_issued']) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO stock_issues (
                    item_id,
                    batch_id,
                    quantity,
                    issue_date,
                    purpose,
                    notes
                ) VALUES (
                    :item_id,
                    :batch_id,
                    :quantity,
                    :issue_date,
                    :purpose,
                    :notes
                )
            ");

            $ok = $stmt->execute([
                ':item_id' => (int)$data['inventory_item_id'],
                ':batch_id' => !empty($data['batch_id']) ? (int)$data['batch_id'] : null,
                ':quantity' => (float)$data['quantity_issued'],
                ':issue_date' => $data['issue_date'],
                ':purpose' => $data['issue_reason'] ?? 'farm_use',
                ':notes' => $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $issueId = (int)$this->db->lastInsertId();

            $stockUpdated = $itemModel->decreaseStock(
                (int)$data['inventory_item_id'],
                (float)$data['quantity_issued']
            );

            if (!$stockUpdated) {
                $this->db->rollBack();
                return false;
            }

            $movementModel = new StockMovement();
            $movementSaved = $movementModel->create([
                'item_id' => (int)$data['inventory_item_id'],
                'movement_type' => 'issue',
                'quantity' => (float)$data['quantity_issued'],
                'movement_date' => $data['issue_date'],
                'reference_no' => 'SI-' . $issueId,
                'notes' => $data['notes'] ?? null,
            ]);

            if (!$movementSaved) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/StockMovement.php';

class StockReceipt extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                sr.*,
                ii.item_name,
                s.supplier_name
            FROM stock_receipts sr
            LEFT JOIN inventory_item ii ON ii.id = sr.item_id
            LEFT JOIN suppliers s ON s.id = sr.supplier_id
            ORDER BY sr.receipt_date DESC, sr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO stock_receipts (
                    item_id,
                    supplier_id,
                    quantity,
                    unit_cost,
                    receipt_date,
                    reference_no,
                    notes
                ) VALUES (
                    :item_id,
                    :supplier_id,
                    :quantity,
                    :unit_cost,
                    :receipt_date,
                    :reference_no,
                    :notes
                )
            ");

            $ok = $stmt->execute([
                ':item_id' => (int)$data['inventory_item_id'],
                ':supplier_id' => !empty($data['supplier_name']) ? (int)$data['supplier_name'] : null,
                ':quantity' => (float)$data['quantity_received'],
                ':unit_cost' => (float)($data['unit_cost'] ?? 0),
                ':receipt_date' => $data['receipt_date'],
                ':reference_no' => $data['reference_no'] ?? null,
                ':notes' => $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $receiptId = (int)$this->db->lastInsertId();

            $itemModel = new InventoryItem();
            $stockUpdated = $itemModel->increaseStock(
                (int)$data['inventory_item_id'],
                (float)$data['quantity_received']
            );

            if (!$stockUpdated) {
                $this->db->rollBack();
                return false;
            }

            $movementModel = new StockMovement();
            $movementSaved = $movementModel->create([
                'item_id' => (int)$data['inventory_item_id'],
                'movement_type' => 'receipt',
                'quantity' => (float)$data['quantity_received'],
                'movement_date' => $data['receipt_date'],
                'reference_no' => 'SR-' . $receiptId,
                'notes' => $data['notes'] ?? null,
            ]);

            if (!$movementSaved) {
                $this->db->rollBack();
                return false;
            }

            // Auto-create available feed record if item is feed category
            $item = $itemModel->find((int)$data['inventory_item_id']);
            if ($item && strtolower($item['category'] ?? '') === 'feed') {
                $feedCreated = $this->createAvailableFeedRecord([
                    'stock_receipt_id' => $receiptId,
                    'inventory_item_id' => (int)$data['inventory_item_id'],
                    'feed_name' => $item['item_name'],
                    'quantity_kg' => (float)$data['quantity_received'],
                    'unit_cost' => (float)($data['unit_cost'] ?? 0),
                    'receipt_date' => $data['receipt_date'],
                    'notes' => 'Auto-created from stock receipt SR-' . $receiptId,
                ]);

                if (!$feedCreated) {
                    $this->db->rollBack();
                    return false;
                }
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

    /**
     * Create available feed record from stock receipt (unified flow)
     */
    private function createAvailableFeedRecord(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO feed_records (
                stock_receipt_id,
                inventory_item_id,
                feed_name,
                quantity_kg,
                unit_cost,
                record_date,
                notes,
                status,
                batch_id
            ) VALUES (
                :stock_receipt_id,
                :inventory_item_id,
                :feed_name,
                :quantity_kg,
                :unit_cost,
                :receipt_date,
                :notes,
                'available',
                NULL
            )
        ");

        return $stmt->execute([
            ':stock_receipt_id' => (int)$data['stock_receipt_id'],
            ':inventory_item_id' => (int)$data['inventory_item_id'],
            ':feed_name' => $data['feed_name'],
            ':quantity_kg' => (float)$data['quantity_kg'],
            ':unit_cost' => (float)$data['unit_cost'],
            ':receipt_date' => $data['receipt_date'],
            ':notes' => $data['notes'] ?? null,
        ]);
    }
}
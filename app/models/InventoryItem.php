<?php

require_once BASE_PATH . 'app/core/Model.php';

class InventoryItem extends Model
{
    public function all(): array
    {
        try {
            return $this->db->query("
                SELECT * FROM inventory_item ORDER BY item_name ASC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    public function active(): array
    {
        try {
            return $this->db->query("
                SELECT * FROM inventory_item WHERE status='active' ORDER BY item_name ASC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM inventory_item WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) { return null; }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO inventory_item
                    (farm_id, item_name, category, unit_of_measure, current_stock, reorder_level, unit_cost, status, notes)
                VALUES
                    (:farm_id, :item_name, :category, :unit_of_measure, :current_stock, :reorder_level, :unit_cost, :status, :notes)
            ");
            return $stmt->execute([
                ':farm_id'        => (int)($data['farm_id'] ?? 1),
                ':item_name'      => trim($data['item_name'] ?? ''),
                ':category'       => $data['category'] ?? 'general',
                ':unit_of_measure'=> $data['unit_of_measure'] ?? 'unit',
                ':current_stock'  => (float)($data['current_stock'] ?? 0),
                ':reorder_level'  => (float)($data['reorder_level'] ?? 0),
                ':unit_cost'      => (float)($data['unit_cost'] ?? 0),
                ':status'         => $data['status'] ?? 'active',
                ':notes'          => $data['notes'] ?? null,
            ]);
        } catch (\Throwable $e) { return false; }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE inventory_item SET
                    item_name=:item_name, category=:category, unit_of_measure=:unit_of_measure,
                    current_stock=:current_stock, reorder_level=:reorder_level,
                    unit_cost=:unit_cost, status=:status, notes=:notes
                WHERE id=:id
            ");
            return $stmt->execute([
                ':id'             => $id,
                ':item_name'      => trim($data['item_name'] ?? ''),
                ':category'       => $data['category'] ?? 'general',
                ':unit_of_measure'=> $data['unit_of_measure'] ?? 'unit',
                ':current_stock'  => (float)($data['current_stock'] ?? 0),
                ':reorder_level'  => (float)($data['reorder_level'] ?? 0),
                ':unit_cost'      => (float)($data['unit_cost'] ?? 0),
                ':status'         => $data['status'] ?? 'active',
                ':notes'          => $data['notes'] ?? null,
            ]);
        } catch (\Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM inventory_item WHERE id=?");
            return $stmt->execute([$id]);
        } catch (\Throwable $e) { return false; }
    }

    public function increaseStock(int $itemId, float $quantity): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE inventory_item SET current_stock = current_stock + ? WHERE id=?");
            return $stmt->execute([$quantity, $itemId]);
        } catch (\Throwable $e) { return false; }
    }

    public function decreaseStock(int $itemId, float $quantity): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE inventory_item SET current_stock = GREATEST(0, current_stock - ?) WHERE id=?");
            return $stmt->execute([$quantity, $itemId]);
        } catch (\Throwable $e) { return false; }
    }

    public function lowStock(): array
    {
        try {
            return $this->db->query("
                SELECT * FROM inventory_item
                WHERE reorder_level > 0 AND current_stock <= reorder_level
                ORDER BY (current_stock / reorder_level) ASC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }
}

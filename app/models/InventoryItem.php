<?php

require_once BASE_PATH . 'app/core/Model.php';

class InventoryItem extends Model
{
    // Inventory system has been unified with feed/medication tracking
    // This model now returns empty arrays for compatibility
    
    public function all(): array
    {
        // Return empty array - inventory is now tracked via feed_records and medication_records
        return [];
    }

    public function active(): array
    {
        return [];
    }

    public function find(int $id): ?array
    {
        return null;
    }

    public function create(array $data): bool
    {
        // Inventory items are now created as feed or medication records
        return false;
    }

    public function update(int $id, array $data): bool
    {
        return false;
    }

    public function delete(int $id): bool
    {
        return false;
    }

    // Legacy methods for compatibility
    public function increaseStock(int $itemId, float $quantity): bool
    {
        return true;
    }

    public function decreaseStock(int $itemId, float $quantity): bool
    {
        return true;
    }

    public function lowStock(): array
    {
        return [];
    }
}

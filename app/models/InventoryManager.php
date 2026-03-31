<?php

require_once BASE_PATH . 'app/core/Model.php';

class InventoryManager extends Model
{
    // Legacy methods for compatibility - do nothing in simplified system
    
    public function stockLevel(int $itemId): float
    {
        return 999999; // No stock tracking - always available
    }

    public function hasEnoughStock(int $itemId, float $quantity): bool
    {
        return true; // No stock tracking - always enough
    }

    public function increaseStock(int $itemId, float $quantity, array $movementData = []): bool
    {
        return true; // No stock tracking
    }

    public function decreaseStock(int $itemId, float $quantity, array $movementData = []): bool
    {
        return true; // No stock tracking
    }
}

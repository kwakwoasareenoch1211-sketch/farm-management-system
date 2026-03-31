<?php

require_once BASE_PATH . 'app/core/Model.php';

class LowStockReport extends Model
{
    public function all(): array
    {
        // Inventory system removed - return empty array
        return [];
    }
}
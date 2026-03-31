<?php

require_once BASE_PATH . 'app/core/Model.php';

class Farm extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, farm_name
            FROM farms
            ORDER BY farm_name ASC
        ");

        return $stmt->fetchAll();
    }
}
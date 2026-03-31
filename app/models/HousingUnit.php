<?php

require_once BASE_PATH . 'app/core/Model.php';

class HousingUnit extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, unit_name
            FROM housing_units
            ORDER BY unit_name ASC
        ");

        return $stmt->fetchAll();
    }
}
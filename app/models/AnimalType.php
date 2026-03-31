<?php

require_once BASE_PATH . 'app/core/Model.php';

class AnimalType extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, type_name
            FROM animal_types
            ORDER BY type_name ASC
        ");

        return $stmt->fetchAll();
    }
}
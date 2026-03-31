<?php

require_once BASE_PATH . 'app/core/Model.php';

class Supplier extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, supplier_name
            FROM suppliers
            ORDER BY supplier_name ASC
        ");

        return $stmt->fetchAll();
    }
}
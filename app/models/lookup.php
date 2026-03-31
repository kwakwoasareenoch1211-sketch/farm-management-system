<?php

require_once BASE_PATH . 'app/core/Model.php';

class Lookup extends Model
{
    public function farms(): array
    {
        $stmt = $this->db->query("
            SELECT id, farm_name
            FROM farms
            ORDER BY farm_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function animalTypes(): array
    {
        $stmt = $this->db->query("
            SELECT id, type_name
            FROM animal_types
            ORDER BY type_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function housingUnits(): array
    {
        $stmt = $this->db->query("
            SELECT id, unit_name
            FROM housing_units
            ORDER BY unit_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function expenseCategories(): array
    {
        $stmt = $this->db->query("
            SELECT id, category_name
            FROM expense_categories
            ORDER BY category_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function suppliers(): array
    {
        $stmt = $this->db->query("
            SELECT id, supplier_name
            FROM suppliers
            ORDER BY supplier_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function users(): array
    {
        $stmt = $this->db->query("
            SELECT id, full_name
            FROM users
            ORDER BY full_name ASC
        ");
        return $stmt->fetchAll();
    }
}
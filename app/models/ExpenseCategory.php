<?php

require_once BASE_PATH . 'app/core/Model.php';

class ExpenseCategory extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, category_name
            FROM expense_categories
            ORDER BY category_name ASC
        ");

        return $stmt->fetchAll();
    }
}
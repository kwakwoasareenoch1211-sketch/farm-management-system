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

    public function findOrCreate(string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM expense_categories WHERE category_name = ? LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['id'];

        $this->db->prepare("INSERT INTO expense_categories (category_name) VALUES (?)")->execute([$name]);
        return (int)$this->db->lastInsertId();
    }
}
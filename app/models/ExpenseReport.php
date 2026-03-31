<?php

require_once BASE_PATH . 'app/core/Model.php';

class ExpenseReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                e.id,
                e.expense_date,
                e.amount,
                e.description
            FROM expenses e
            ORDER BY e.expense_date DESC, e.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
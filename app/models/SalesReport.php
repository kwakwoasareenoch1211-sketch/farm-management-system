<?php

require_once BASE_PATH . 'app/core/Model.php';

class SalesReport extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                s.id,
                s.sale_date,
                s.total_amount,
                c.customer_name
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            ORDER BY s.sale_date DESC, s.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
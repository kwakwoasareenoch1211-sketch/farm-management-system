<?php

require_once BASE_PATH . 'app/core/Model.php';

class Sales extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT 
                s.*,
                ab.batch_code,
                ab.batch_name,
                c.customer_name
            FROM sales s
            LEFT JOIN animal_batches ab ON ab.id = s.batch_id
            LEFT JOIN customers c ON c.id = s.customer_id
            ORDER BY s.sale_date DESC, s.id DESC
        ");

        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $farmId = (int)($data['farm_id'] ?? 0);
        $batchId = (!empty($data['batch_id']) && (int)$data['batch_id'] > 0) ? (int)$data['batch_id'] : null;
        $customerId = (!empty($data['customer_id']) && (int)$data['customer_id'] > 0) ? (int)$data['customer_id'] : null;

        $subtotal = max(0, (float)($data['subtotal'] ?? 0));
        $discountAmount = max(0, (float)($data['discount_amount'] ?? 0));
        $totalAmount = max(0, $subtotal - $discountAmount);
        $amountPaid = max(0, (float)($data['amount_paid'] ?? 0));

        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $totalAmount, $data['payment_status'] ?? null);

        $stmt = $this->db->prepare("
            INSERT INTO sales (
                farm_id,
                customer_id,
                batch_id,
                sale_date,
                product_type,
                quantity,
                unit_price,
                subtotal,
                discount_amount,
                total_amount,
                amount_paid,
                payment_status,
                payment_method,
                notes
            ) VALUES (
                :farm_id,
                :customer_id,
                :batch_id,
                :sale_date,
                :product_type,
                :quantity,
                :unit_price,
                :subtotal,
                :discount_amount,
                :total_amount,
                :amount_paid,
                :payment_status,
                :payment_method,
                :notes
            )
        ");

        return $stmt->execute([
            ':farm_id' => $farmId,
            ':customer_id' => $customerId,
            ':batch_id' => $batchId,
            ':sale_date' => $data['sale_date'],
            ':product_type' => !empty($data['product_type']) ? trim($data['product_type']) : 'other',
            ':quantity' => (float)($data['quantity'] ?? 0),
            ':unit_price' => (float)($data['unit_price'] ?? 0),
            ':subtotal' => $subtotal,
            ':discount_amount' => $discountAmount,
            ':total_amount' => $totalAmount,
            ':amount_paid' => $amountPaid,
            ':payment_status' => $paymentStatus,
            ':payment_method' => !empty($data['payment_method']) ? trim($data['payment_method']) : 'cash',
            ':notes' => !empty($data['notes']) ? trim($data['notes']) : null,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM sales
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $farmId = (int)($data['farm_id'] ?? 0);
        $batchId = (!empty($data['batch_id']) && (int)$data['batch_id'] > 0) ? (int)$data['batch_id'] : null;
        $customerId = (!empty($data['customer_id']) && (int)$data['customer_id'] > 0) ? (int)$data['customer_id'] : null;

        $subtotal = max(0, (float)($data['subtotal'] ?? 0));
        $discountAmount = max(0, (float)($data['discount_amount'] ?? 0));
        $totalAmount = max(0, $subtotal - $discountAmount);
        $amountPaid = max(0, (float)($data['amount_paid'] ?? 0));

        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $totalAmount, $data['payment_status'] ?? null);

        $stmt = $this->db->prepare("
            UPDATE sales SET
                farm_id = :farm_id,
                customer_id = :customer_id,
                batch_id = :batch_id,
                sale_date = :sale_date,
                product_type = :product_type,
                quantity = :quantity,
                unit_price = :unit_price,
                subtotal = :subtotal,
                discount_amount = :discount_amount,
                total_amount = :total_amount,
                amount_paid = :amount_paid,
                payment_status = :payment_status,
                payment_method = :payment_method,
                notes = :notes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':farm_id' => $farmId,
            ':customer_id' => $customerId,
            ':batch_id' => $batchId,
            ':sale_date' => $data['sale_date'],
            ':product_type' => !empty($data['product_type']) ? trim($data['product_type']) : 'other',
            ':quantity' => (float)($data['quantity'] ?? 0),
            ':unit_price' => (float)($data['unit_price'] ?? 0),
            ':subtotal' => $subtotal,
            ':discount_amount' => $discountAmount,
            ':total_amount' => $totalAmount,
            ':amount_paid' => $amountPaid,
            ':payment_status' => $paymentStatus,
            ':payment_method' => !empty($data['payment_method']) ? trim($data['payment_method']) : 'cash',
            ':notes' => !empty($data['notes']) ? trim($data['notes']) : null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sales WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COALESCE(SUM(amount_paid), 0) AS total_paid,
                COALESCE(SUM(total_amount - amount_paid), 0) AS total_outstanding,
                COALESCE(SUM(CASE WHEN YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE()) THEN total_amount ELSE 0 END), 0) AS current_month_sales,
                COALESCE(SUM(CASE WHEN sale_date = CURDATE() THEN total_amount ELSE 0 END), 0) AS today_sales,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) AS paid_sales,
                COALESCE(SUM(CASE WHEN payment_status = 'partial' THEN total_amount ELSE 0 END), 0) AS partial_sales,
                COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN total_amount ELSE 0 END), 0) AS unpaid_sales
            FROM sales
        ");

        return $stmt->fetch() ?: [
            'total_records' => 0,
            'total_sales' => 0,
            'total_paid' => 0,
            'total_outstanding' => 0,
            'current_month_sales' => 0,
            'today_sales' => 0,
            'paid_sales' => 0,
            'partial_sales' => 0,
            'unpaid_sales' => 0,
        ];
    }

    public function byType(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COALESCE(product_type, 'other') AS product_type,
                COUNT(*) AS total_records,
                SUM(total_amount) AS total_amount
            FROM sales
            GROUP BY COALESCE(product_type, 'other')
            ORDER BY total_amount DESC
        ");

        return $stmt->fetchAll();
    }

    public function topCustomers(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.customer_name,
                COUNT(s.id) AS total_sales_count,
                COALESCE(SUM(s.total_amount), 0) AS total_sales_amount
            FROM sales s
            INNER JOIN customers c ON c.id = s.customer_id
            GROUP BY c.id, c.customer_name
            ORDER BY total_sales_amount DESC
            LIMIT :limit_value
        ");
        $stmt->bindValue(':limit_value', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                ab.batch_code,
                ab.batch_name,
                c.customer_name
            FROM sales s
            LEFT JOIN animal_batches ab ON ab.id = s.batch_id
            LEFT JOIN customers c ON c.id = s.customer_id
            ORDER BY s.sale_date DESC, s.id DESC
            LIMIT :limit_value
        ");
        $stmt->bindValue(':limit_value', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function resolvePaymentStatus(float $amountPaid, float $totalAmount, ?string $postedStatus): string
    {
        if ($totalAmount <= 0) {
            return 'unpaid';
        }

        if ($amountPaid >= $totalAmount) {
            return 'paid';
        }

        if ($amountPaid > 0) {
            return 'partial';
        }

        if (!empty($postedStatus) && in_array($postedStatus, ['paid', 'partial', 'unpaid'], true)) {
            return $postedStatus;
        }

        return 'unpaid';
    }

    private function generateInvoiceNo(): string
    {
        $stmt = $this->db->query("SELECT MAX(id) AS max_id FROM sales");
        $maxId = (int)($stmt->fetch()['max_id'] ?? 0);
        $next = $maxId + 1;

        return 'INV-' . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    private function farmExists(int $farmId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM farms WHERE id = ?");
        $stmt->execute([$farmId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    private function batchExists(?int $batchId): bool
    {
        if ($batchId === null || $batchId <= 0) {
            return true;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM animal_batches WHERE id = ?");
        $stmt->execute([$batchId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    private function customerExists(?int $customerId): bool
    {
        if ($customerId === null || $customerId <= 0) {
            return true;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }
}
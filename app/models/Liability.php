<?php

require_once BASE_PATH . 'app/core/Model.php';

class Liability extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT 
                l.*,
                COALESCE(SUM(lp.amount_paid), 0) AS total_paid,
                (l.principal_amount - COALESCE(SUM(lp.amount_paid), 0)) AS calculated_balance,
                CASE 
                    WHEN l.source_type = 'expense' THEN e.description
                    ELSE NULL
                END AS source_description,
                CASE 
                    WHEN l.source_type = 'expense' THEN e.expense_date
                    ELSE l.start_date
                END AS source_date
            FROM liabilities l
            LEFT JOIN liability_payments lp ON lp.liability_id = l.id
            LEFT JOIN expenses e ON e.id = l.source_id AND l.source_type = 'expense'
            GROUP BY l.id
            ORDER BY l.created_at DESC, l.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                l.*,
                COALESCE(SUM(lp.amount_paid), 0) AS total_paid,
                (l.principal_amount - COALESCE(SUM(lp.amount_paid), 0)) AS calculated_balance
            FROM liabilities l
            LEFT JOIN liability_payments lp ON lp.liability_id = l.id
            WHERE l.id = ?
            GROUP BY l.id
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $data): bool
    {
        // Get default farm_id if not provided
        $farmId = (int)($data['farm_id'] ?? 0);
        if ($farmId === 0) {
            $stmt = $this->db->query("SELECT id FROM farms LIMIT 1");
            $farm = $stmt->fetch(PDO::FETCH_ASSOC);
            $farmId = $farm ? (int)$farm['id'] : 1;
        }

        $stmt = $this->db->prepare("
            INSERT INTO liabilities (
                farm_id,
                liability_name,
                liability_type,
                principal_amount,
                outstanding_balance,
                interest_rate,
                start_date,
                due_date,
                status,
                notes
            ) VALUES (
                :farm_id,
                :liability_name,
                :liability_type,
                :principal_amount,
                :outstanding_balance,
                :interest_rate,
                :start_date,
                :due_date,
                :status,
                :notes
            )
        ");

        $principalAmount = (float)($data['principal_amount'] ?? 0);
        $outstandingBalance = isset($data['outstanding_balance']) && $data['outstanding_balance'] !== '' 
            ? (float)$data['outstanding_balance'] 
            : $principalAmount;

        return $stmt->execute([
            ':farm_id' => $farmId,
            ':liability_name' => $data['liability_name'],
            ':liability_type' => $data['liability_type'] ?? 'other',
            ':principal_amount' => $principalAmount,
            ':outstanding_balance' => $outstandingBalance,
            ':interest_rate' => !empty($data['interest_rate']) ? (float)$data['interest_rate'] : null,
            ':start_date' => $data['start_date'],
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':status' => $data['status'] ?? 'active',
            ':notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        // Get default farm_id if not provided
        $farmId = (int)($data['farm_id'] ?? 0);
        if ($farmId === 0) {
            $stmt = $this->db->query("SELECT id FROM farms LIMIT 1");
            $farm = $stmt->fetch(PDO::FETCH_ASSOC);
            $farmId = $farm ? (int)$farm['id'] : 1;
        }

        $stmt = $this->db->prepare("
            UPDATE liabilities SET
                farm_id = :farm_id,
                liability_name = :liability_name,
                liability_type = :liability_type,
                principal_amount = :principal_amount,
                outstanding_balance = :outstanding_balance,
                interest_rate = :interest_rate,
                start_date = :start_date,
                due_date = :due_date,
                status = :status,
                notes = :notes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':farm_id' => $farmId,
            ':liability_name' => $data['liability_name'],
            ':liability_type' => $data['liability_type'] ?? 'other',
            ':principal_amount' => (float)($data['principal_amount'] ?? 0),
            ':outstanding_balance' => (float)($data['outstanding_balance'] ?? 0),
            ':interest_rate' => !empty($data['interest_rate']) ? (float)$data['interest_rate'] : null,
            ':start_date' => $data['start_date'],
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':status' => $data['status'] ?? 'active',
            ':notes' => $data['notes'] ?? null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM liabilities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totals(): array
    {
        // Calculate real-time outstanding balances from principal minus payments
        $stmt = $this->db->query("
            SELECT
                COUNT(DISTINCT l.id) AS total_liabilities,
                COALESCE(SUM(CASE WHEN l.status = 'active' THEN 1 ELSE 0 END), 0) AS active_liabilities,
                COALESCE(SUM(CASE WHEN l.status = 'paid' THEN 1 ELSE 0 END), 0) AS paid_liabilities,
                COALESCE(SUM(l.principal_amount), 0) AS total_principal,
                COALESCE(SUM(l.principal_amount - COALESCE(payments.total_paid, 0)), 0) AS total_outstanding,
                COALESCE(SUM(CASE WHEN l.status = 'active' THEN l.principal_amount - COALESCE(payments.total_paid, 0) ELSE 0 END), 0) AS active_outstanding
            FROM liabilities l
            LEFT JOIN (
                SELECT liability_id, SUM(amount_paid) AS total_paid
                FROM liability_payments
                GROUP BY liability_id
            ) payments ON payments.liability_id = l.id
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_liabilities' => 0,
            'active_liabilities' => 0,
            'paid_liabilities' => 0,
            'total_principal' => 0,
            'total_outstanding' => 0,
            'active_outstanding' => 0,
        ];
    }

    public function getPayments(int $liabilityId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM liability_payments
            WHERE liability_id = ?
            ORDER BY payment_date DESC, id DESC
        ");
        $stmt->execute([$liabilityId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addPayment(int $liabilityId, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Insert payment record
            $stmt = $this->db->prepare("
                INSERT INTO liability_payments (
                    liability_id,
                    payment_date,
                    amount_paid,
                    notes
                ) VALUES (
                    :liability_id,
                    :payment_date,
                    :amount_paid,
                    :notes
                )
            ");

            $ok = $stmt->execute([
                ':liability_id' => $liabilityId,
                ':payment_date' => $data['payment_date'],
                ':amount_paid' => (float)($data['amount_paid'] ?? 0),
                ':notes' => $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Update outstanding balance
            $stmt = $this->db->prepare("
                UPDATE liabilities
                SET outstanding_balance = outstanding_balance - :amount_paid
                WHERE id = :id
            ");

            $ok = $stmt->execute([
                ':amount_paid' => (float)($data['amount_paid'] ?? 0),
                ':id' => $liabilityId,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Check if fully paid and update status
            $stmt = $this->db->prepare("
                UPDATE liabilities
                SET status = 'paid'
                WHERE id = :id AND outstanding_balance <= 0
            ");

            $stmt->execute([':id' => $liabilityId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function upcomingDue(int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM liabilities
            WHERE status = 'active'
              AND due_date IS NOT NULL
              AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY due_date ASC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function overdue(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM liabilities
            WHERE status = 'active'
              AND due_date IS NOT NULL
              AND due_date < CURDATE()
            ORDER BY due_date ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

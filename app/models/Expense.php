<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/core/OwnerHelper.php';

class Expense extends Model
{
    use OwnerHelper;
    public function all(): array
    {
        $this->db = Database::connect();
        $expenses = [];

        // 1. Manual expenses
        $stmt = $this->db->query("
            SELECT 'manual' AS expense_source,
                e.id, e.expense_date AS date,
                COALESCE(e.description, 'Manual Expense') AS title,
                e.amount,
                COALESCE(ec.category_name, 'Uncategorized') AS category_name,
                e.payment_method, e.payment_status, e.amount_paid,
                e.liability_id, e.expense_reference, e.notes, e.created_at
            FROM expenses e
            LEFT JOIN expense_categories ec ON ec.id = e.category_id
            ORDER BY e.expense_date DESC, e.id DESC
        ");
        $expenses = array_merge($expenses, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // 2. Livestock/Chick purchases from batches (initial_quantity * initial_unit_cost)
        $stmt = $this->db->query("
            SELECT 'livestock_purchase' AS expense_source,
                ab.id,
                COALESCE(ab.purchase_date, ab.start_date) AS date,
                CONCAT('Livestock Purchase: ', ab.batch_code,
                    CASE WHEN ab.batch_name IS NOT NULL THEN CONCAT(' - ', ab.batch_name) ELSE '' END,
                    ' (', ab.initial_quantity, ' birds @ GHS ', ab.initial_unit_cost, ')') AS title,
                COALESCE(ab.initial_quantity * ab.initial_unit_cost, 0) AS amount,
                'Livestock Purchase' AS category_name,
                'cash' AS payment_method,
                'paid' AS payment_status,
                COALESCE(ab.initial_quantity * ab.initial_unit_cost, 0) AS amount_paid,
                NULL AS liability_id, NULL AS expense_reference,
                ab.notes, ab.created_at
            FROM animal_batches ab
            WHERE ab.initial_unit_cost > 0 AND ab.initial_quantity > 0
            ORDER BY COALESCE(ab.purchase_date, ab.start_date) DESC, ab.id DESC
        ");
        $expenses = array_merge($expenses, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // 3. Feed expenses
        $stmt = $this->db->query("
            SELECT 'feed' AS expense_source,
                fr.id, fr.record_date AS date,
                CONCAT('Feed: ', COALESCE(fr.feed_name, 'Unknown'), ' - Batch: ', COALESCE(ab.batch_name, ab.batch_code, 'N/A')) AS title,
                COALESCE(fr.quantity_kg * fr.unit_cost, 0) AS amount,
                'Feed' AS category_name, 'cash' AS payment_method,
                'paid' AS payment_status,
                COALESCE(fr.quantity_kg * fr.unit_cost, 0) AS amount_paid,
                NULL AS liability_id, NULL AS expense_reference,
                fr.notes, fr.created_at
            FROM feed_records fr
            LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
            WHERE fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
            ORDER BY fr.record_date DESC, fr.id DESC
        ");
        $expenses = array_merge($expenses, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // 4. Medication expenses
        $stmt = $this->db->query("
            SELECT 'medication' AS expense_source,
                mr.id, mr.record_date AS date,
                CONCAT('Medication: ', COALESCE(mr.medication_name, 'Unknown'), ' - Batch: ', COALESCE(ab.batch_name, ab.batch_code, 'N/A')) AS title,
                COALESCE(mr.quantity_used * mr.unit_cost, 0) AS amount,
                'Medication' AS category_name, 'cash' AS payment_method,
                'paid' AS payment_status,
                COALESCE(mr.quantity_used * mr.unit_cost, 0) AS amount_paid,
                NULL AS liability_id, NULL AS expense_reference,
                mr.notes, mr.created_at
            FROM medication_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            WHERE mr.unit_cost IS NOT NULL AND mr.unit_cost > 0 AND mr.quantity_used > 0
            ORDER BY mr.record_date DESC, mr.id DESC
        ");
        $expenses = array_merge($expenses, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // 5. Vaccination expenses
        $stmt = $this->db->query("
            SELECT 'vaccination' AS expense_source,
                vr.id, vr.record_date AS date,
                CONCAT('Vaccination: ', COALESCE(vr.vaccine_name, 'Unknown'), ' - Batch: ', COALESCE(ab.batch_name, ab.batch_code, 'N/A')) AS title,
                COALESCE(vr.cost_amount, 0) AS amount,
                'Vaccination' AS category_name, 'cash' AS payment_method,
                'paid' AS payment_status,
                COALESCE(vr.cost_amount, 0) AS amount_paid,
                NULL AS liability_id, NULL AS expense_reference,
                vr.notes, vr.created_at
            FROM vaccination_records vr
            LEFT JOIN animal_batches ab ON ab.id = vr.batch_id
            WHERE vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
            ORDER BY vr.record_date DESC, vr.id DESC
        ");
        $expenses = array_merge($expenses, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // Sort all by date descending
        usort($expenses, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        return $expenses;
    }

    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $amount = (float)($data['amount'] ?? 0);
            $paymentStatus = $data['payment_status'] ?? 'paid';
            
            // Set amount_paid based on payment_status
            if ($paymentStatus === 'paid') {
                $amountPaid = $amount;
            } elseif ($paymentStatus === 'unpaid') {
                $amountPaid = 0;
            } else { // partial
                $amountPaid = isset($data['amount_paid']) ? (float)$data['amount_paid'] : 0;
            }

            $stmt = $this->db->prepare("
                INSERT INTO expenses (
                    farm_id, owner_id, is_shared,
                    category_id, expense_date, description, amount,
                    payment_method, payment_status, amount_paid,
                    expense_reference, notes
                ) VALUES (
                    :farm_id, :owner_id, :is_shared,
                    :category_id, :expense_date, :description, :amount,
                    :payment_method, :payment_status, :amount_paid,
                    :expense_reference, :notes
                )
            ");

            $owner = $this->resolveOwner($data);
            $ok = $stmt->execute([
                ':farm_id' => (int)($data['farm_id'] ?? 0),
                ':owner_id' => $owner['owner_id'],
                ':is_shared' => $owner['is_shared'],
                ':category_id' => (!empty($data['category_id']) && (int)$data['category_id'] > 0) ? (int)$data['category_id'] : null,
                ':expense_date' => $data['expense_date'],
                ':description' => $data['description'] ?? null,
                ':amount' => $amount,
                ':payment_method' => $data['payment_method'] ?? 'cash',
                ':payment_status' => $paymentStatus,
                ':amount_paid' => $amountPaid,
                ':expense_reference' => $data['expense_reference'] ?? null,
                ':notes' => $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $expenseId = (int)$this->db->lastInsertId();

            // If unpaid or partial, create a liability
            if ($paymentStatus === 'unpaid' || $paymentStatus === 'partial') {
                $outstandingAmount = $amount - $amountPaid;
                
                if ($outstandingAmount > 0) {
                    require_once BASE_PATH . 'app/models/Liability.php';
                    $liabilityModel = new Liability();

                    $liabilityData = [
                        'farm_id' => (int)($data['farm_id'] ?? 0),
                        'source_type' => 'expense',
                        'source_id' => $expenseId,
                        'liability_name' => 'Unpaid Expense: ' . ($data['description'] ?? 'Expense #' . $expenseId),
                        'liability_type' => 'accounts_payable',
                        'principal_amount' => $outstandingAmount,
                        'outstanding_balance' => $outstandingAmount,
                        'start_date' => $data['expense_date'],
                        'due_date' => $data['due_date'] ?? null,
                        'status' => 'active',
                        'notes' => 'Auto-generated from expense #' . $expenseId,
                    ];

                    $liabilityStmt = $this->db->prepare("
                        INSERT INTO liabilities (
                            farm_id,
                            source_type,
                            source_id,
                            liability_name,
                            liability_type,
                            principal_amount,
                            outstanding_balance,
                            start_date,
                            due_date,
                            status,
                            notes
                        ) VALUES (
                            :farm_id,
                            :source_type,
                            :source_id,
                            :liability_name,
                            :liability_type,
                            :principal_amount,
                            :outstanding_balance,
                            :start_date,
                            :due_date,
                            :status,
                            :notes
                        )
                    ");

                    $ok = $liabilityStmt->execute($liabilityData);

                    if (!$ok) {
                        $this->db->rollBack();
                        return false;
                    }

                    $liabilityId = (int)$this->db->lastInsertId();

                    // Link expense to liability
                    $updateStmt = $this->db->prepare("
                        UPDATE expenses 
                        SET liability_id = :liability_id 
                        WHERE id = :id
                    ");
                    $updateStmt->execute([
                        ':liability_id' => $liabilityId,
                        ':id' => $expenseId,
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Expense create error: " . $e->getMessage());
            return false;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM expenses
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Get old expense data
            $oldExpense = $this->find($id);
            if (!$oldExpense) {
                $this->db->rollBack();
                return false;
            }

            $amount = (float)($data['amount'] ?? 0);
            $paymentStatus = $data['payment_status'] ?? 'paid';
            
            // Set amount_paid based on payment_status
            if ($paymentStatus === 'paid') {
                $amountPaid = $amount;
            } elseif ($paymentStatus === 'unpaid') {
                $amountPaid = 0;
            } else { // partial
                $amountPaid = isset($data['amount_paid']) ? (float)$data['amount_paid'] : 0;
            }

            $stmt = $this->db->prepare("
                UPDATE expenses SET
                    farm_id = :farm_id,
                    category_id = :category_id,
                    expense_date = :expense_date,
                    description = :description,
                    amount = :amount,
                    payment_method = :payment_method,
                    payment_status = :payment_status,
                    amount_paid = :amount_paid,
                    expense_reference = :expense_reference,
                    notes = :notes
                WHERE id = :id
            ");

            $ok = $stmt->execute([
                ':farm_id' => (int)($data['farm_id'] ?? 0),
                ':category_id' => (!empty($data['category_id']) && (int)$data['category_id'] > 0) ? (int)$data['category_id'] : null,
                ':expense_date' => $data['expense_date'],
                ':description' => $data['description'] ?? null,
                ':amount' => $amount,
                ':payment_method' => $data['payment_method'] ?? 'cash',
                ':payment_status' => $paymentStatus,
                ':amount_paid' => $amountPaid,
                ':expense_reference' => $data['expense_reference'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':id' => $id,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Handle liability changes
            $oldStatus = $oldExpense['payment_status'] ?? 'paid';
            $oldLiabilityId = $oldExpense['liability_id'] ?? null;

            // If changing from paid to unpaid/partial, create liability
            if ($oldStatus === 'paid' && ($paymentStatus === 'unpaid' || $paymentStatus === 'partial')) {
                $outstandingAmount = $amount - $amountPaid;
                
                if ($outstandingAmount > 0) {
                    require_once BASE_PATH . 'app/models/Liability.php';
                    
                    $liabilityStmt = $this->db->prepare("
                        INSERT INTO liabilities (
                            farm_id,
                            source_type,
                            source_id,
                            liability_name,
                            liability_type,
                            principal_amount,
                            outstanding_balance,
                            start_date,
                            due_date,
                            status,
                            notes
                        ) VALUES (
                            :farm_id,
                            'expense',
                            :source_id,
                            :liability_name,
                            'accounts_payable',
                            :principal_amount,
                            :outstanding_balance,
                            :start_date,
                            :due_date,
                            'active',
                            :notes
                        )
                    ");

                    $ok = $liabilityStmt->execute([
                        ':farm_id' => (int)($data['farm_id'] ?? 0),
                        ':source_id' => $id,
                        ':liability_name' => 'Unpaid Expense: ' . ($data['description'] ?? 'Expense #' . $id),
                        ':principal_amount' => $outstandingAmount,
                        ':outstanding_balance' => $outstandingAmount,
                        ':start_date' => $data['expense_date'],
                        ':due_date' => $data['due_date'] ?? null,
                        ':notes' => 'Auto-generated from expense #' . $id,
                    ]);

                    if ($ok) {
                        $liabilityId = (int)$this->db->lastInsertId();
                        $this->db->prepare("UPDATE expenses SET liability_id = ? WHERE id = ?")->execute([$liabilityId, $id]);
                    }
                }
            }
            // If changing from unpaid/partial to paid, delete liability
            elseif (($oldStatus === 'unpaid' || $oldStatus === 'partial') && $paymentStatus === 'paid') {
                if ($oldLiabilityId) {
                    $this->db->prepare("DELETE FROM liabilities WHERE id = ?")->execute([$oldLiabilityId]);
                    $this->db->prepare("UPDATE expenses SET liability_id = NULL WHERE id = ?")->execute([$id]);
                }
            }
            // If still unpaid/partial, update liability amount
            elseif (($paymentStatus === 'unpaid' || $paymentStatus === 'partial') && $oldLiabilityId) {
                $outstandingAmount = $amount - $amountPaid;
                $this->db->prepare("
                    UPDATE liabilities 
                    SET principal_amount = ?, outstanding_balance = ?
                    WHERE id = ?
                ")->execute([$outstandingAmount, $outstandingAmount, $oldLiabilityId]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Expense update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM expenses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totals(): array
    {
        // Get totals from each source separately to avoid confusion
        $totals = [
            'manual' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
            'feed' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
            'medication' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
            'vaccination' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
            'livestock_purchase' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
            'mortality_loss' => ['count' => 0, 'total' => 0, 'current_month' => 0, 'today' => 0],
        ];

        // Manual expenses
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(amount), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(expense_date) = YEAR(CURDATE()) AND MONTH(expense_date) = MONTH(CURDATE()) THEN amount ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN expense_date = CURDATE() THEN amount ELSE 0 END), 0) AS today
            FROM expenses
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['manual'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Feed expenses (only count records with unit_cost)
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(quantity_kg * unit_cost), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(record_date) = YEAR(CURDATE()) AND MONTH(record_date) = MONTH(CURDATE()) THEN quantity_kg * unit_cost ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN record_date = CURDATE() THEN quantity_kg * unit_cost ELSE 0 END), 0) AS today
            FROM feed_records
            WHERE unit_cost IS NOT NULL AND unit_cost > 0
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['feed'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Medication expenses (only count records with unit_cost and quantity_used)
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(quantity_used * unit_cost), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(record_date) = YEAR(CURDATE()) AND MONTH(record_date) = MONTH(CURDATE()) THEN quantity_used * unit_cost ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN record_date = CURDATE() THEN quantity_used * unit_cost ELSE 0 END), 0) AS today
            FROM medication_records
            WHERE unit_cost IS NOT NULL AND unit_cost > 0 AND quantity_used IS NOT NULL AND quantity_used > 0
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['medication'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Vaccination expenses (only count records with cost_amount)
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(cost_amount), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(record_date) = YEAR(CURDATE()) AND MONTH(record_date) = MONTH(CURDATE()) THEN cost_amount ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN record_date = CURDATE() THEN cost_amount ELSE 0 END), 0) AS today
            FROM vaccination_records
            WHERE cost_amount IS NOT NULL AND cost_amount > 0
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['vaccination'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Livestock purchase cost (cash paid for chicks)
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(initial_quantity * initial_unit_cost), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(start_date) = YEAR(CURDATE()) AND MONTH(start_date) = MONTH(CURDATE()) THEN initial_quantity * initial_unit_cost ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN start_date = CURDATE() THEN initial_quantity * initial_unit_cost ELSE 0 END), 0) AS today
            FROM animal_batches
            WHERE initial_unit_cost IS NOT NULL AND initial_unit_cost > 0
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['livestock_purchase'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Mortality loss (asset write-off)
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS cnt,
                COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0) AS total,
                COALESCE(SUM(CASE WHEN YEAR(mr.record_date) = YEAR(CURDATE()) AND MONTH(mr.record_date) = MONTH(CURDATE()) THEN mr.quantity * ab.initial_unit_cost ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN mr.record_date = CURDATE() THEN mr.quantity * ab.initial_unit_cost ELSE 0 END), 0) AS today
            FROM mortality_records mr
            INNER JOIN animal_batches ab ON ab.id = mr.batch_id
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totals['mortality_loss'] = [
            'count' => (int)($row['cnt'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'current_month' => (float)($row['current_month'] ?? 0),
            'today' => (float)($row['today'] ?? 0),
        ];

        // Note: Stock receipts removed - inventory tracking unified with feed/medication systems

        // Calculate grand totals
        $grandTotal = 0;
        $grandCurrentMonth = 0;
        $grandToday = 0;
        $grandCount = 0;

        foreach ($totals as $source => $data) {
            $grandTotal += $data['total'];
            $grandCurrentMonth += $data['current_month'];
            $grandToday += $data['today'];
            $grandCount += $data['count'];
        }

        return [
            'by_source' => $totals,
            'total_records' => $grandCount,
            'total_amount' => $grandTotal,
            'current_month_amount' => $grandCurrentMonth,
            'today_amount' => $grandToday,
        ];
    }

    public function monthlyBreakdown(int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(expense_date, '%Y-%m') AS month_key,
                DATE_FORMAT(expense_date, '%b %Y') AS month_label,
                SUM(amount) AS total_amount
            FROM expenses
            GROUP BY DATE_FORMAT(expense_date, '%Y-%m'), DATE_FORMAT(expense_date, '%b %Y')
            ORDER BY month_key DESC
            LIMIT :limit_value
        ");
        $stmt->bindValue(':limit_value', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_reverse($stmt->fetchAll());
    }

    public function byCategory(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COALESCE(ec.category_name, 'Uncategorized') AS category_name,
                SUM(e.amount) AS total_amount
            FROM expenses e
            LEFT JOIN expense_categories ec ON ec.id = e.category_id
            GROUP BY COALESCE(ec.category_name, 'Uncategorized')
            ORDER BY total_amount DESC
        ");

        return $stmt->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                COALESCE(ec.category_name, 'Uncategorized') AS category_name
            FROM expenses e
            LEFT JOIN expense_categories ec ON ec.id = e.category_id
            ORDER BY e.expense_date DESC, e.id DESC
            LIMIT :limit_value
        ");
        $stmt->bindValue(':limit_value', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function unpaid(): array
    {
        $stmt = $this->db->query("
            SELECT 
                e.*,
                COALESCE(ec.category_name, 'Uncategorized') AS category_name,
                (e.amount - e.amount_paid) AS outstanding_amount,
                l.id AS liability_id
            FROM expenses e
            LEFT JOIN expense_categories ec ON ec.id = e.category_id
            LEFT JOIN liabilities l ON l.source_type = 'expense' AND l.source_id = e.id
            WHERE e.payment_status IN ('unpaid', 'partial')
            ORDER BY e.expense_date ASC, e.id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function byOwner(): array
    {
        $this->db = Database::connect();
        $stmt = $this->db->prepare("SELECT id, full_name, username FROM users WHERE role IN ('owner','admin') ORDER BY id");
        $stmt->execute();
        $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $numOwners = max(count($owners), 1);

        // Shared totals (split equally)
        $sharedTotal = (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE is_shared=1")->fetchColumn() / $numOwners;
        $sharedPaid  = (float)$this->db->query("SELECT COALESCE(SUM(amount_paid),0) FROM expenses WHERE is_shared=1")->fetchColumn() / $numOwners;
        $sharedMonth = (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE is_shared=1 AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())")->fetchColumn() / $numOwners;

        $result = [];
        foreach ($owners as $owner) {
            $oid = (int)$owner['id'];

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE owner_id=? AND is_shared=0");
            $s->execute([$oid]); $ownTotal = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE owner_id=? AND is_shared=0 AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())");
            $s->execute([$oid]); $ownMonth = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount_paid),0) FROM expenses WHERE owner_id=? AND is_shared=0");
            $s->execute([$oid]); $ownPaid = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE owner_id=?");
            $s->execute([$oid]); $revenue = (float)$s->fetchColumn();

            $s = $this->db->prepare("
                SELECT COALESCE(ec.category_name,'Uncategorized') AS cat, SUM(e.amount) AS total
                FROM expenses e LEFT JOIN expense_categories ec ON ec.id=e.category_id
                WHERE e.owner_id=? OR e.is_shared=1
                GROUP BY cat ORDER BY total DESC
            ");
            $s->execute([$oid]); $cats = $s->fetchAll(PDO::FETCH_ASSOC);

            $total = $ownTotal + $sharedTotal;
            $paid  = $ownPaid  + $sharedPaid;

            $result[] = [
                'id'           => $oid,
                'name'         => $owner['full_name'],
                'username'     => $owner['username'],
                'total'        => $total,
                'own_total'    => $ownTotal,
                'shared_total' => $sharedTotal,
                'this_month'   => $ownMonth + $sharedMonth,
                'paid'         => $paid,
                'balance'      => $total - $paid,
                'revenue'      => $revenue,
                'margin'       => $revenue - $total,
                'categories'   => $cats,
            ];
        }
        return $result;
    }

    public function autoCategory(string $description): string
    {
        $desc = strtolower($description);
        if (str_contains($desc, 'feed') || str_contains($desc, 'grain') || str_contains($desc, 'pellet')) return 'Feed';
        if (str_contains($desc, 'medic') || str_contains($desc, 'drug') || str_contains($desc, 'vaccine') || str_contains($desc, 'vet')) return 'Veterinary';
        if (str_contains($desc, 'labour') || str_contains($desc, 'labor') || str_contains($desc, 'salary') || str_contains($desc, 'wage')) return 'Labour';
        if (str_contains($desc, 'electric') || str_contains($desc, 'water') || str_contains($desc, 'utility') || str_contains($desc, 'bill')) return 'Utilities';
        if (str_contains($desc, 'transport') || str_contains($desc, 'fuel') || str_contains($desc, 'delivery')) return 'Transport';
        if (str_contains($desc, 'repair') || str_contains($desc, 'maintenance') || str_contains($desc, 'fix')) return 'Maintenance';
        if (str_contains($desc, 'equipment') || str_contains($desc, 'tool') || str_contains($desc, 'machine')) return 'Equipment';
        if (str_contains($desc, 'rent') || str_contains($desc, 'lease')) return 'Rent';
        return 'General';
    }
}
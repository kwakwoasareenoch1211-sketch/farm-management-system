<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/core/OwnerHelper.php';

class MortalityRecord extends Model
{
   use OwnerHelper;
   public function all(): array
    {
        // Calculate birds remaining after each mortality event using a running total
        $stmt = $this->db->query("
            SELECT
                mr.*,
                ab.batch_code,
                ab.batch_name,
                ab.initial_quantity,
                ab.current_quantity AS live_now,
                (ab.current_quantity + COALESCE(
                    (SELECT SUM(m2.quantity) FROM mortality_records m2
                     WHERE m2.batch_id = mr.batch_id AND m2.id > mr.id), 0
                )) AS birds_after_this_record,
                f.farm_name
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            LEFT JOIN farms f ON f.id = mr.farm_id
            ORDER BY mr.record_date DESC, mr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM mortality_records
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $quantity = (int)($data['quantity'] ?? 0);
        $batchId  = (int)($data['batch_id'] ?? 0);

        if ($quantity <= 0 || $batchId <= 0) {
            return false;
        }

        try {
            $this->db = Database::connect();
            $this->db->beginTransaction();

            // Get batch current quantity
            $stmt = $this->db->prepare("SELECT current_quantity, farm_id FROM animal_batches WHERE id=? LIMIT 1");
            $stmt->execute([$batchId]);
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$batch) {
                $this->db->rollBack();
                return false;
            }

            // Allow recording even if quantity exceeds (clamp to current)
            $actualQty = min($quantity, (int)$batch['current_quantity']);
            if ($actualQty <= 0) $actualQty = $quantity; // allow if batch qty is 0 (already depleted)

            $farmId = (int)($data['farm_id'] ?? $batch['farm_id'] ?? 1);

            // Insert mortality record
            $stmt = $this->db->prepare("
                INSERT INTO mortality_records
                    (farm_id, batch_id, record_date, quantity, cause, disposal_method, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $ok = $stmt->execute([
                $farmId,
                $batchId,
                $data['record_date'] ?? date('Y-m-d'),
                $actualQty,
                $data['cause'] ?? null,
                $data['disposal_method'] ?? null,
                $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Deduct from batch
            $this->db->prepare("
                UPDATE animal_batches SET current_quantity = GREATEST(0, current_quantity - ?) WHERE id=?
            ")->execute([$actualQty, $batchId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('MortalityRecord::create error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Get old mortality record
            $stmt = $this->db->prepare("SELECT batch_id, quantity FROM mortality_records WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $oldRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$oldRecord) {
                $this->db->rollBack();
                return false;
            }

            $oldQuantity = (int)$oldRecord['quantity'];
            $oldBatchId = (int)$oldRecord['batch_id'];
            $newQuantity = (int)($data['quantity'] ?? 0);
            $newBatchId = (int)($data['batch_id'] ?? 0);

            // Restore old quantity to old batch
            $stmt = $this->db->prepare("
                UPDATE animal_batches
                SET current_quantity = current_quantity + :quantity
                WHERE id = :batch_id
            ");
            $stmt->execute([
                ':quantity' => $oldQuantity,
                ':batch_id' => $oldBatchId,
            ]);

            // Check if new quantity is valid for new batch
            $stmt = $this->db->prepare("SELECT current_quantity FROM animal_batches WHERE id = :id");
            $stmt->execute([':id' => $newBatchId]);
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$batch || $newQuantity > (int)$batch['current_quantity']) {
                $this->db->rollBack();
                return false;
            }

            // Update mortality record
            $stmt = $this->db->prepare("
                UPDATE mortality_records
                SET
                    farm_id = :farm_id,
                    batch_id = :batch_id,
                    record_date = :record_date,
                    quantity = :quantity,
                    cause = :cause,
                    disposal_method = :disposal_method,
                    notes = :notes
                WHERE id = :id
            ");

            $ok = $stmt->execute([
                ':id'              => $id,
                ':farm_id'         => (int)($data['farm_id'] ?? 0),
                ':batch_id'        => $newBatchId,
                ':record_date'     => $data['record_date'] ?? date('Y-m-d'),
                ':quantity'        => $newQuantity,
                ':cause'           => $data['cause'] ?? null,
                ':disposal_method' => $data['disposal_method'] ?? null,
                ':notes'           => $data['notes'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Deduct new quantity from new batch
            $stmt = $this->db->prepare("
                UPDATE animal_batches
                SET current_quantity = current_quantity - :quantity
                WHERE id = :batch_id
            ");

            $ok = $stmt->execute([
                ':quantity' => $newQuantity,
                ':batch_id' => $newBatchId,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Get mortality record to restore quantity
            $stmt = $this->db->prepare("SELECT batch_id, quantity FROM mortality_records WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->db->rollBack();
                return false;
            }

            // Delete mortality record
            $stmt = $this->db->prepare("DELETE FROM mortality_records WHERE id = :id");
            $ok = $stmt->execute([':id' => $id]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            // Restore quantity to batch
            $stmt = $this->db->prepare("
                UPDATE animal_batches
                SET current_quantity = current_quantity + :quantity
                WHERE id = :batch_id
            ");

            $ok = $stmt->execute([
                ':quantity' => (int)$record['quantity'],
                ':batch_id' => (int)$record['batch_id'],
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity), 0) AS total_mortality,
                COUNT(DISTINCT batch_id) AS total_batches
            FROM mortality_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records' => 0,
            'total_mortality' => 0,
            'total_batches' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT
                ab.batch_code,
                ab.batch_name,
                COALESCE(SUM(mr.quantity), 0) AS total_mortality
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            GROUP BY mr.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_mortality DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
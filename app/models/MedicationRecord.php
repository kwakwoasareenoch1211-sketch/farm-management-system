<?php

require_once BASE_PATH . 'app/core/Model.php';

class MedicationRecord extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT mr.*, ab.batch_code, ab.batch_name, f.farm_name
            FROM medication_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            LEFT JOIN farms f ON f.id = mr.farm_id
            ORDER BY mr.record_date DESC, mr.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM medication_records WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO medication_records (
                    farm_id, batch_id, inventory_item_id, record_date,
                    medication_name, condition_treated, dosage,
                    quantity_used, unit, unit_cost,
                    administered_by, withdrawal_period_days, notes, created_by
                ) VALUES (
                    :farm_id, :batch_id, NULL, :record_date,
                    :medication_name, :condition_treated, :dosage,
                    :quantity_used, :unit, :unit_cost,
                    :administered_by, :withdrawal_period_days, :notes, :created_by
                )
            ");

            return $stmt->execute([
                ':farm_id'               => (int)($data['farm_id'] ?? 0),
                ':batch_id'              => (int)($data['batch_id'] ?? 0),
                ':record_date'           => $data['record_date'],
                ':medication_name'       => trim($data['medication_name'] ?? ''),
                ':condition_treated'     => $data['condition_treated'] ?? null,
                ':dosage'                => $data['dosage'] ?? null,
                ':quantity_used'         => (float)($data['quantity_used'] ?? 0),
                ':unit'                  => $data['unit'] ?? null,
                ':unit_cost'             => (float)($data['unit_cost'] ?? 0),
                ':administered_by'       => $data['administered_by'] ?? null,
                ':withdrawal_period_days'=> !empty($data['withdrawal_period_days']) ? (int)$data['withdrawal_period_days'] : null,
                ':notes'                 => $data['notes'] ?? null,
                ':created_by'            => !empty($data['created_by']) ? (int)$data['created_by'] : null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE medication_records SET
                    farm_id=:farm_id, batch_id=:batch_id, inventory_item_id=NULL,
                    record_date=:record_date, medication_name=:medication_name,
                    condition_treated=:condition_treated, dosage=:dosage,
                    quantity_used=:quantity_used, unit=:unit, unit_cost=:unit_cost,
                    administered_by=:administered_by, withdrawal_period_days=:withdrawal_period_days,
                    notes=:notes
                WHERE id=:id
            ");

            return $stmt->execute([
                ':id'                    => $id,
                ':farm_id'               => (int)($data['farm_id'] ?? 0),
                ':batch_id'              => (int)($data['batch_id'] ?? 0),
                ':record_date'           => $data['record_date'],
                ':medication_name'       => trim($data['medication_name'] ?? ''),
                ':condition_treated'     => $data['condition_treated'] ?? null,
                ':dosage'                => $data['dosage'] ?? null,
                ':quantity_used'         => (float)($data['quantity_used'] ?? 0),
                ':unit'                  => $data['unit'] ?? null,
                ':unit_cost'             => (float)($data['unit_cost'] ?? 0),
                ':administered_by'       => $data['administered_by'] ?? null,
                ':withdrawal_period_days'=> !empty($data['withdrawal_period_days']) ? (int)$data['withdrawal_period_days'] : null,
                ':notes'                 => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM medication_records WHERE id=?");
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity_used), 0) AS total_quantity,
                COALESCE(SUM(quantity_used * unit_cost), 0) AS total_cost,
                COALESCE(SUM(CASE
                    WHEN YEAR(record_date) = YEAR(CURDATE())
                     AND MONTH(record_date) = MONTH(CURDATE())
                    THEN quantity_used * unit_cost ELSE 0
                END), 0) AS current_month_cost
            FROM medication_records
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records' => 0, 'total_quantity' => 0,
            'total_cost' => 0, 'current_month_cost' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT ab.batch_code, ab.batch_name,
                   COUNT(mr.id) AS treatments,
                   COALESCE(SUM(mr.quantity_used), 0) AS total_quantity_used,
                   COALESCE(SUM(mr.quantity_used * mr.unit_cost), 0) AS total_cost
            FROM medication_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            GROUP BY mr.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_cost DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

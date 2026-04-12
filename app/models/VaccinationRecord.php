<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/core/OwnerHelper.php';

class VaccinationRecord extends Model
{
    use OwnerHelper;
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT vr.*, ab.batch_code, ab.batch_name, f.farm_name
            FROM vaccination_records vr
            LEFT JOIN animal_batches ab ON ab.id = vr.batch_id
            LEFT JOIN farms f ON f.id = vr.farm_id
            ORDER BY vr.record_date DESC, vr.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vaccination_records WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vaccination_records (
                    farm_id, owner_id, is_shared, batch_id, inventory_item_id, record_date,
                    vaccine_name, dose_qty, disease_target, dosage, route,
                    cost_amount, next_due_date, administered_by, notes, created_by
                ) VALUES (
                    :farm_id, :owner_id, :is_shared, :batch_id, NULL, :record_date,
                    :vaccine_name, :dose_qty, :disease_target, :dosage, :route,
                    :cost_amount, :next_due_date, :administered_by, :notes, :created_by
                )
            ");

            $owner = $this->resolveOwner($data);
            return $stmt->execute([
                ':farm_id'           => (int)($data['farm_id'] ?? 0),
                ':owner_id'          => $owner['owner_id'],
                ':is_shared'         => $owner['is_shared'],
                ':batch_id'          => (int)($data['batch_id'] ?? 0),
                ':record_date'       => $data['record_date'],
                ':vaccine_name'      => trim($data['vaccine_name'] ?? ''),
                ':dose_qty'          => (float)($data['dose_qty'] ?? 1),
                ':disease_target'    => $data['disease_target'] ?? null,
                ':dosage'            => $data['dosage'] ?? null,
                ':route'             => $data['route'] ?? null,
                ':cost_amount'       => (float)($data['cost_amount'] ?? 0),
                ':next_due_date'     => !empty($data['next_due_date']) ? $data['next_due_date'] : null,
                ':administered_by'   => $data['administered_by'] ?? null,
                ':notes'             => $data['notes'] ?? null,
                ':created_by'        => !empty($data['created_by']) ? (int)$data['created_by'] : null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE vaccination_records SET
                    farm_id=:farm_id, batch_id=:batch_id, inventory_item_id=NULL,
                    record_date=:record_date, vaccine_name=:vaccine_name, dose_qty=:dose_qty,
                    disease_target=:disease_target, dosage=:dosage, route=:route,
                    cost_amount=:cost_amount, next_due_date=:next_due_date,
                    administered_by=:administered_by, notes=:notes
                WHERE id=:id
            ");

            return $stmt->execute([
                ':id'                => $id,
                ':farm_id'           => (int)($data['farm_id'] ?? 0),
                ':batch_id'          => (int)($data['batch_id'] ?? 0),
                ':record_date'       => $data['record_date'],
                ':vaccine_name'      => trim($data['vaccine_name'] ?? ''),
                ':dose_qty'          => (float)($data['dose_qty'] ?? 1),
                ':disease_target'    => $data['disease_target'] ?? null,
                ':dosage'            => $data['dosage'] ?? null,
                ':route'             => $data['route'] ?? null,
                ':cost_amount'       => (float)($data['cost_amount'] ?? 0),
                ':next_due_date'     => !empty($data['next_due_date']) ? $data['next_due_date'] : null,
                ':administered_by'   => $data['administered_by'] ?? null,
                ':notes'             => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM vaccination_records WHERE id=?");
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
                COALESCE(SUM(dose_qty),0) AS total_doses,
                COALESCE(SUM(cost_amount),0) AS total_cost,
                COALESCE(SUM(CASE WHEN next_due_date < CURDATE() THEN 1 ELSE 0 END),0) AS overdue_count,
                COALESCE(SUM(CASE WHEN next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END),0) AS due_soon_count
            FROM vaccination_records
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records'=>0,'total_doses'=>0,'total_cost'=>0,'overdue_count'=>0,'due_soon_count'=>0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT ab.batch_code, ab.batch_name,
                   COUNT(vr.id) AS vaccinations,
                   COALESCE(SUM(vr.dose_qty),0) AS total_doses,
                   COALESCE(SUM(vr.cost_amount),0) AS total_cost
            FROM animal_batches ab
            LEFT JOIN vaccination_records vr ON vr.batch_id = ab.id
            GROUP BY ab.id, ab.batch_code, ab.batch_name
            ORDER BY vaccinations DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function upcoming(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT vr.id, vr.vaccine_name, vr.next_due_date, vr.disease_target,
                   ab.batch_code, ab.batch_name
            FROM vaccination_records vr
            LEFT JOIN animal_batches ab ON ab.id = vr.batch_id
            WHERE vr.next_due_date IS NOT NULL AND vr.next_due_date >= CURDATE()
            ORDER BY vr.next_due_date ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

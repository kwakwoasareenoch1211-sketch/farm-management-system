<?php

require_once BASE_PATH . 'app/core/Model.php';
require_once BASE_PATH . 'app/core/OwnerHelper.php';

class EggProductionRecord extends Model
{
    use OwnerHelper;
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT er.*, ab.batch_code, ab.batch_name, f.farm_name
            FROM egg_production_records er
            LEFT JOIN animal_batches ab ON ab.id = er.batch_id
            LEFT JOIN farms f ON f.id = er.farm_id
            ORDER BY er.record_date DESC, er.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM egg_production_records WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        $owner = $this->resolveOwner($data);
        $stmt = $this->db->prepare("
            INSERT INTO egg_production_records (farm_id, owner_id, is_shared, batch_id, record_date, quantity, notes, created_by)
            VALUES (:farm_id, :owner_id, :is_shared, :batch_id, :record_date, :quantity, :notes, :created_by)
        ");
        return $stmt->execute([
            ':farm_id'    => (int)($data['farm_id']    ?? 0),
            ':owner_id'   => $owner['owner_id'],
            ':is_shared'  => $owner['is_shared'],
            ':batch_id'   => (int)($data['batch_id']   ?? 0),
            ':record_date'=> $data['record_date'],
            ':quantity'   => (float)($data['quantity'] ?? 0),
            ':notes'      => $data['notes'] ?? null,
            ':created_by' => !empty($data['created_by']) ? (int)$data['created_by'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE egg_production_records
            SET farm_id=:farm_id, batch_id=:batch_id, record_date=:record_date,
                quantity=:quantity, notes=:notes
            WHERE id=:id
        ");
        return $stmt->execute([
            ':id'         => $id,
            ':farm_id'    => (int)($data['farm_id']    ?? 0),
            ':batch_id'   => (int)($data['batch_id']   ?? 0),
            ':record_date'=> $data['record_date'],
            ':quantity'   => (float)($data['quantity'] ?? 0),
            ':notes'      => $data['notes'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM egg_production_records WHERE id=?")->execute([$id]);
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total_records,
                   COALESCE(SUM(quantity),0) AS total_eggs,
                   COALESCE(SUM(trays_equivalent),0) AS total_trays,
                   COALESCE(SUM(CASE WHEN YEAR(record_date)=YEAR(CURDATE()) AND MONTH(record_date)=MONTH(CURDATE()) THEN quantity ELSE 0 END),0) AS current_month_eggs
            FROM egg_production_records
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records'=>0,'total_eggs'=>0,'total_trays'=>0,'current_month_eggs'=>0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT ab.batch_code, ab.batch_name,
                   COUNT(er.id) AS total_records,
                   COALESCE(SUM(er.quantity),0) AS total_eggs,
                   COALESCE(SUM(er.trays_equivalent),0) AS total_trays
            FROM egg_production_records er
            LEFT JOIN animal_batches ab ON ab.id = er.batch_id
            GROUP BY er.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_eggs DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

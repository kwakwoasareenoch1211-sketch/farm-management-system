<?php

require_once BASE_PATH . 'app/core/Model.php';

class WeightRecord extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT wr.*, ab.batch_code, ab.batch_name, f.farm_name
            FROM weight_records wr
            LEFT JOIN animal_batches ab ON ab.id = wr.batch_id
            LEFT JOIN farms f ON f.id = wr.farm_id
            ORDER BY wr.record_date DESC, wr.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM weight_records WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        $sampleSize     = (int)($data['sample_size']     ?? 0);
        $totalWeightKg  = (float)($data['total_weight_kg'] ?? 0);
        $avgWeightKg    = $sampleSize > 0 ? $totalWeightKg / $sampleSize : (float)($data['average_weight_kg'] ?? 0);

        $stmt = $this->db->prepare("
            INSERT INTO weight_records (farm_id, batch_id, record_date, sample_size, total_weight_kg, average_weight_kg, notes, created_by)
            VALUES (:farm_id, :batch_id, :record_date, :sample_size, :total_weight_kg, :average_weight_kg, :notes, :created_by)
        ");
        return $stmt->execute([
            ':farm_id'          => (int)($data['farm_id']  ?? 0),
            ':batch_id'         => (int)($data['batch_id'] ?? 0),
            ':record_date'      => $data['record_date'],
            ':sample_size'      => $sampleSize,
            ':total_weight_kg'  => $totalWeightKg,
            ':average_weight_kg'=> $avgWeightKg,
            ':notes'            => $data['notes'] ?? null,
            ':created_by'       => !empty($data['created_by']) ? (int)$data['created_by'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sampleSize    = (int)($data['sample_size']     ?? 0);
        $totalWeightKg = (float)($data['total_weight_kg'] ?? 0);
        $avgWeightKg   = $sampleSize > 0 ? $totalWeightKg / $sampleSize : (float)($data['average_weight_kg'] ?? 0);

        $stmt = $this->db->prepare("
            UPDATE weight_records SET
                farm_id=:farm_id, batch_id=:batch_id, record_date=:record_date,
                sample_size=:sample_size, total_weight_kg=:total_weight_kg,
                average_weight_kg=:average_weight_kg, notes=:notes
            WHERE id=:id
        ");
        return $stmt->execute([
            ':id'               => $id,
            ':farm_id'          => (int)($data['farm_id']  ?? 0),
            ':batch_id'         => (int)($data['batch_id'] ?? 0),
            ':record_date'      => $data['record_date'],
            ':sample_size'      => $sampleSize,
            ':total_weight_kg'  => $totalWeightKg,
            ':average_weight_kg'=> $avgWeightKg,
            ':notes'            => $data['notes'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM weight_records WHERE id=?")->execute([$id]);
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total_records,
                   COALESCE(SUM(sample_size),0) AS total_sampled,
                   COALESCE(SUM(total_weight_kg),0) AS total_weight_kg,
                   COALESCE(AVG(average_weight_kg),0) AS avg_weight_kg,
                   COALESCE(MAX(average_weight_kg),0) AS max_weight_kg
            FROM weight_records
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records'=>0,'total_sampled'=>0,'total_weight_kg'=>0,'avg_weight_kg'=>0,'max_weight_kg'=>0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT ab.batch_code, ab.batch_name,
                   COUNT(wr.id) AS total_records,
                   COALESCE(SUM(wr.sample_size),0) AS total_sample_size,
                   COALESCE(SUM(wr.total_weight_kg),0) AS total_weight_kg,
                   COALESCE(AVG(wr.average_weight_kg),0) AS avg_weight_kg,
                   COALESCE(MAX(wr.average_weight_kg),0) AS max_weight_kg
            FROM weight_records wr
            LEFT JOIN animal_batches ab ON ab.id = wr.batch_id
            GROUP BY wr.batch_id, ab.batch_code, ab.batch_name
            ORDER BY avg_weight_kg DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

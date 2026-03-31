<?php

require_once BASE_PATH . 'app/core/Model.php';

class Feed extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT 
                fr.*,
                ab.batch_code,
                ab.batch_name
            FROM feed_records fr
            LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
            ORDER BY fr.record_date DESC, fr.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(array $data): bool
    {
        $quantityKg = (float)($data['quantity_kg'] ?? 0);
        if ($quantityKg <= 0) {
            return false;
        }

        // Feed name and unit cost are now provided directly
        $feedName = trim($data['feed_name'] ?? '');
        if (empty($feedName)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO feed_records (
                    farm_id, batch_id, inventory_item_id,
                    record_date, feed_name, quantity_kg, unit_cost, notes
                ) VALUES (
                    :farm_id, :batch_id, :inventory_item_id,
                    :record_date, :feed_name, :quantity_kg, :unit_cost, :notes
                )
            ");

            return $stmt->execute([
                ':farm_id'            => (int)($data['farm_id'] ?? 1),
                ':batch_id'           => (int)($data['batch_id'] ?? 0),
                ':inventory_item_id'  => null, // No longer used
                ':record_date'        => $data['record_date'],
                ':feed_name'          => $feedName,
                ':quantity_kg'        => $quantityKg,
                ':unit_cost'          => (float)($data['unit_cost'] ?? 0),
                ':notes'              => !empty($data['notes']) ? trim((string)$data['notes']) : null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT fr.*
            FROM feed_records fr
            WHERE fr.id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $quantityKg = (float)($data['quantity_kg'] ?? 0);
        if ($quantityKg <= 0) {
            return false;
        }

        // Feed name and unit cost are now provided directly
        $feedName = trim($data['feed_name'] ?? '');
        if (empty($feedName)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE feed_records SET
                    farm_id = :farm_id,
                    batch_id = :batch_id,
                    inventory_item_id = :inventory_item_id,
                    record_date = :record_date,
                    feed_name = :feed_name,
                    quantity_kg = :quantity_kg,
                    unit_cost = :unit_cost,
                    notes = :notes
                WHERE id = :id
            ");

            return $stmt->execute([
                ':farm_id' => (int)($data['farm_id'] ?? 1),
                ':batch_id' => (int)($data['batch_id'] ?? 0),
                ':inventory_item_id' => null, // No longer used
                ':record_date' => $data['record_date'],
                ':feed_name' => $feedName,
                ':quantity_kg' => $quantityKg,
                ':unit_cost' => (float)($data['unit_cost'] ?? 0),
                ':notes' => !empty($data['notes']) ? trim((string)$data['notes']) : null,
                ':id' => $id,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM feed_records WHERE id = ?");
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
                COALESCE(SUM(quantity_kg), 0) AS total_feed_kg,
                COALESCE(SUM(quantity_kg * unit_cost), 0) AS total_feed_cost,
                COALESCE(SUM(CASE
                    WHEN YEAR(record_date) = YEAR(CURDATE())
                     AND MONTH(record_date) = MONTH(CURDATE())
                    THEN quantity_kg * unit_cost ELSE 0
                END), 0) AS current_month_cost
            FROM feed_records
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records' => 0, 'total_feed_kg' => 0,
            'total_feed_cost' => 0, 'current_month_cost' => 0,
        ];
    }

    public function byBatch(): array
    {
        $stmt = $this->db->query("
            SELECT ab.batch_code, ab.batch_name,
                   COUNT(fr.id) AS feed_entries,
                   COALESCE(SUM(fr.quantity_kg), 0) AS total_feed_kg,
                   COALESCE(SUM(fr.quantity_kg * fr.unit_cost), 0) AS total_feed_cost
            FROM feed_records fr
            LEFT JOIN animal_batches ab ON ab.id = fr.batch_id
            GROUP BY fr.batch_id, ab.batch_code, ab.batch_name
            ORDER BY total_feed_kg DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

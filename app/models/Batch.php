<?php

require_once BASE_PATH . 'app/core/Model.php';

class Batch extends Model
{
    public function all(): array
    {
        $sql = "
            SELECT 
                b.*,
                COALESCE((
                    SELECT SUM(m.quantity)
                    FROM mortality_records m
                    WHERE m.batch_id = b.id
                ), 0) AS total_mortality,
                COALESCE((
                    SELECT SUM(e.quantity)
                    FROM egg_production_records e
                    WHERE e.batch_id = b.id
                ), 0) AS total_eggs,
                COALESCE((
                    SELECT SUM(e.trays_equivalent)
                    FROM egg_production_records e
                    WHERE e.batch_id = b.id
                ), 0) AS total_trays,
                COALESCE((
                    SELECT SUM(f.quantity_kg)
                    FROM feed_records f
                    WHERE f.batch_id = b.id
                ), 0) AS total_feed_kg,
                COALESCE((
                    SELECT SUM(f.quantity_kg * f.unit_cost)
                    FROM feed_records f
                    WHERE f.batch_id = b.id
                ), 0) AS total_feed_cost,
                COALESCE((
                    SELECT SUM(md.quantity_used * md.unit_cost)
                    FROM medication_records md
                    WHERE md.batch_id = b.id
                ), 0) AS total_medication_cost,
                COALESCE((
                    SELECT SUM(v.cost_amount)
                    FROM vaccination_records v
                    WHERE v.batch_id = b.id
                ), 0) AS total_vaccination_cost,
                COALESCE((
                    SELECT SUM(s.total_amount)
                    FROM sales s
                    WHERE s.batch_id = b.id
                ), 0) AS total_batch_sales,
                (
                    SELECT wr.average_weight_kg
                    FROM weight_records wr
                    WHERE wr.batch_id = b.id
                    ORDER BY wr.record_date DESC, wr.id DESC
                    LIMIT 1
                ) AS latest_average_weight_kg
            FROM animal_batches b
            ORDER BY b.id DESC
        ";

        $rows = $this->db->query($sql)->fetchAll();

        foreach ($rows as &$row) {
            $row = $this->computeBatchMetrics($row);
        }

        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                b.*,
                COALESCE((
                    SELECT SUM(m.quantity)
                    FROM mortality_records m
                    WHERE m.batch_id = b.id
                ), 0) AS total_mortality,
                COALESCE((
                    SELECT SUM(e.quantity)
                    FROM egg_production_records e
                    WHERE e.batch_id = b.id
                ), 0) AS total_eggs,
                COALESCE((
                    SELECT SUM(e.trays_equivalent)
                    FROM egg_production_records e
                    WHERE e.batch_id = b.id
                ), 0) AS total_trays,
                COALESCE((
                    SELECT SUM(f.quantity_kg)
                    FROM feed_records f
                    WHERE f.batch_id = b.id
                ), 0) AS total_feed_kg,
                COALESCE((
                    SELECT SUM(f.quantity_kg * f.unit_cost)
                    FROM feed_records f
                    WHERE f.batch_id = b.id
                ), 0) AS total_feed_cost,
                COALESCE((
                    SELECT SUM(md.quantity_used * md.unit_cost)
                    FROM medication_records md
                    WHERE md.batch_id = b.id
                ), 0) AS total_medication_cost,
                COALESCE((
                    SELECT SUM(v.cost_amount)
                    FROM vaccination_records v
                    WHERE v.batch_id = b.id
                ), 0) AS total_vaccination_cost,
                COALESCE((
                    SELECT SUM(s.total_amount)
                    FROM sales s
                    WHERE s.batch_id = b.id
                ), 0) AS total_batch_sales,
                (
                    SELECT wr.average_weight_kg
                    FROM weight_records wr
                    WHERE wr.batch_id = b.id
                    ORDER BY wr.record_date DESC, wr.id DESC
                    LIMIT 1
                ) AS latest_average_weight_kg
            FROM animal_batches b
            WHERE b.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? $this->computeBatchMetrics($row) : null;
    }

    public function create(array $data): bool
    {
        $farmId = (int)($data['farm_id'] ?? 0);
        $animalTypeId = (int)($data['animal_type_id'] ?? 0);
        $housingUnitId = (!empty($data['housing_unit_id']) && (int)$data['housing_unit_id'] > 0)
            ? (int)$data['housing_unit_id']
            : null;

        if (!$this->farmExists($farmId) || !$this->animalTypeExists($animalTypeId) || !$this->housingUnitExists($housingUnitId)) {
            return false;
        }

        $batchCode = $this->generateBatchCode();

        $stmt = $this->db->prepare("
            INSERT INTO animal_batches (
                farm_id,
                animal_type_id,
                housing_unit_id,
                batch_code,
                batch_name,
                production_purpose,
                bird_subtype,
                breed,
                source_name,
                purchase_date,
                start_date,
                expected_end_date,
                initial_quantity,
                current_quantity,
                initial_unit_cost,
                status,
                notes
            ) VALUES (
                :farm_id,
                :animal_type_id,
                :housing_unit_id,
                :batch_code,
                :batch_name,
                :production_purpose,
                :bird_subtype,
                :breed,
                :source_name,
                :purchase_date,
                :start_date,
                :expected_end_date,
                :initial_quantity,
                :current_quantity,
                :initial_unit_cost,
                :status,
                :notes
            )
        ");

        return $stmt->execute([
            ':farm_id' => $farmId,
            ':animal_type_id' => $animalTypeId,
            ':housing_unit_id' => $housingUnitId,
            ':batch_code' => $batchCode,
            ':batch_name' => !empty($data['batch_name']) ? trim($data['batch_name']) : null,
            ':production_purpose' => $data['production_purpose'],
            ':bird_subtype' => !empty($data['bird_subtype']) ? trim($data['bird_subtype']) : null,
            ':breed' => !empty($data['breed']) ? trim($data['breed']) : null,
            ':source_name' => !empty($data['source_name']) ? trim($data['source_name']) : null,
            ':purchase_date' => !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            ':start_date' => $data['start_date'],
            ':expected_end_date' => !empty($data['expected_end_date']) ? $data['expected_end_date'] : null,
            ':initial_quantity' => (int)$data['initial_quantity'],
            ':current_quantity' => (int)$data['initial_quantity'],
            ':initial_unit_cost' => (float)$data['initial_unit_cost'],
            ':status' => $data['status'],
            ':notes' => !empty($data['notes']) ? trim($data['notes']) : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $farmId = (int)($data['farm_id'] ?? 0);
        $animalTypeId = (int)($data['animal_type_id'] ?? 0);
        $housingUnitId = (!empty($data['housing_unit_id']) && (int)$data['housing_unit_id'] > 0)
            ? (int)$data['housing_unit_id']
            : null;

        if (!$this->farmExists($farmId) || !$this->animalTypeExists($animalTypeId) || !$this->housingUnitExists($housingUnitId)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE animal_batches SET
                farm_id = :farm_id,
                animal_type_id = :animal_type_id,
                housing_unit_id = :housing_unit_id,
                batch_name = :batch_name,
                production_purpose = :production_purpose,
                bird_subtype = :bird_subtype,
                breed = :breed,
                source_name = :source_name,
                purchase_date = :purchase_date,
                start_date = :start_date,
                expected_end_date = :expected_end_date,
                initial_quantity = :initial_quantity,
                initial_unit_cost = :initial_unit_cost,
                status = :status,
                notes = :notes
            WHERE id = :id
        ");

        $ok = $stmt->execute([
            ':farm_id' => $farmId,
            ':animal_type_id' => $animalTypeId,
            ':housing_unit_id' => $housingUnitId,
            ':batch_name' => !empty($data['batch_name']) ? trim($data['batch_name']) : null,
            ':production_purpose' => $data['production_purpose'],
            ':bird_subtype' => !empty($data['bird_subtype']) ? trim($data['bird_subtype']) : null,
            ':breed' => !empty($data['breed']) ? trim($data['breed']) : null,
            ':source_name' => !empty($data['source_name']) ? trim($data['source_name']) : null,
            ':purchase_date' => !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            ':start_date' => $data['start_date'],
            ':expected_end_date' => !empty($data['expected_end_date']) ? $data['expected_end_date'] : null,
            ':initial_quantity' => (int)$data['initial_quantity'],
            ':initial_unit_cost' => (float)$data['initial_unit_cost'],
            ':status' => $data['status'],
            ':notes' => !empty($data['notes']) ? trim($data['notes']) : null,
            ':id' => $id,
        ]);

        if ($ok) {
            $this->refreshCurrentQuantity($id);
        }

        return $ok;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM animal_batches WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function refreshCurrentQuantity(int $batchId): void
    {
        $stmt = $this->db->prepare("
            SELECT 
                initial_quantity,
                COALESCE((
                    SELECT SUM(quantity)
                    FROM mortality_records
                    WHERE batch_id = ?
                ), 0) AS total_mortality
            FROM animal_batches
            WHERE id = ?
        ");
        $stmt->execute([$batchId, $batchId]);
        $row = $stmt->fetch();

        if (!$row) {
            return;
        }

        $currentQty = max(0, (int)$row['initial_quantity'] - (int)$row['total_mortality']);

        $update = $this->db->prepare("
            UPDATE animal_batches
            SET current_quantity = ?
            WHERE id = ?
        ");
        $update->execute([$currentQty, $batchId]);
    }

    private function computeBatchMetrics(array $row): array
    {
        $initialQty = (float)($row['initial_quantity'] ?? 0);
        $totalMortality = (float)($row['total_mortality'] ?? 0);
        $totalEggs = (float)($row['total_eggs'] ?? 0);
        $totalTrays = (float)($row['total_trays'] ?? 0);
        $totalFeedKg = (float)($row['total_feed_kg'] ?? 0);
        $unitCost = (float)($row['initial_unit_cost'] ?? 0);
        $latestAverageWeightKg = (float)($row['latest_average_weight_kg'] ?? 0);

        $feedCost = (float)($row['total_feed_cost'] ?? 0);
        $medicationCost = (float)($row['total_medication_cost'] ?? 0);
        $vaccinationCost = (float)($row['total_vaccination_cost'] ?? 0);
        $batchSales = (float)($row['total_batch_sales'] ?? 0);

        // Use the current_quantity from database (already updated by mortality records)
        $currentQty = (float)($row['current_quantity'] ?? 0);

        $row['mortality_rate'] = $initialQty > 0 ? ($totalMortality / $initialQty) * 100 : 0;
        $row['egg_production_rate'] = $currentQty > 0 ? ($totalEggs / $currentQty) * 100 : 0;
        $row['feed_per_bird'] = $currentQty > 0 ? ($totalFeedKg / $currentQty) : 0;

        $purchaseCost = $initialQty * $unitCost;
        $operationalCost = $feedCost + $medicationCost + $vaccinationCost;
        $totalBatchCost = $purchaseCost + $operationalCost;

        $row['purchase_cost'] = $purchaseCost;
        $row['total_feed_cost'] = $feedCost;
        $row['total_medication_cost'] = $medicationCost;
        $row['total_vaccination_cost'] = $vaccinationCost;
        $row['total_operational_cost'] = $operationalCost;
        $row['total_batch_cost'] = $totalBatchCost;
        $row['total_batch_sales'] = $batchSales;

        $row['cost_per_bird'] = $currentQty > 0 ? ($totalBatchCost / $currentQty) : 0;
        $row['cost_per_egg'] = $totalEggs > 0 ? ($totalBatchCost / $totalEggs) : 0;
        $row['cost_per_tray'] = $totalTrays > 0 ? ($totalBatchCost / $totalTrays) : 0;

        $row['latest_average_weight_kg'] = $latestAverageWeightKg;
        $row['estimated_total_live_weight_kg'] = $currentQty > 0 ? ($latestAverageWeightKg * $currentQty) : 0;

        $row['fcr'] = $row['estimated_total_live_weight_kg'] > 0
            ? ($totalFeedKg / $row['estimated_total_live_weight_kg'])
            : 0;

        $row['gross_profit'] = $batchSales - $totalBatchCost;
        $row['profit_margin'] = $batchSales > 0 ? (($row['gross_profit'] / $batchSales) * 100) : 0;

        $row['egg_margin_per_egg'] = $totalEggs > 0 && $batchSales > 0
            ? (($batchSales / $totalEggs) - $row['cost_per_egg'])
            : 0;

        $row['broiler_margin_per_kg'] = $row['estimated_total_live_weight_kg'] > 0 && $batchSales > 0
            ? (($batchSales / $row['estimated_total_live_weight_kg']) - ($totalBatchCost / $row['estimated_total_live_weight_kg']))
            : 0;

        return $row;
    }

    public function generateBatchCode(): string
    {
        $stmt = $this->db->query("
            SELECT MAX(id) AS max_id
            FROM animal_batches
        ");
        $maxId = (int)($stmt->fetch()['max_id'] ?? 0);
        $next = $maxId + 1;

        return 'BATCH-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function farmExists(int $farmId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM farms WHERE id = ?");
        $stmt->execute([$farmId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    private function animalTypeExists(int $animalTypeId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM animal_types WHERE id = ?");
        $stmt->execute([$animalTypeId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    private function housingUnitExists(?int $housingUnitId): bool
    {
        if ($housingUnitId === null || $housingUnitId <= 0) {
            return true;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM housing_units WHERE id = ?");
        $stmt->execute([$housingUnitId]);
        $row = $stmt->fetch();

        return ((int)($row['total'] ?? 0)) > 0;
    }
}
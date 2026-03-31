<?php

require_once BASE_PATH . 'app/core/Model.php';

class LossWriteoff extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM losses_writeoffs
            ORDER BY loss_date DESC, id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM losses_writeoffs
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO losses_writeoffs (
                farm_id,
                loss_type,
                reference_id,
                loss_date,
                description,
                quantity,
                unit_cost,
                total_loss_amount,
                reason,
                notes
            ) VALUES (
                :farm_id,
                :loss_type,
                :reference_id,
                :loss_date,
                :description,
                :quantity,
                :unit_cost,
                :total_loss_amount,
                :reason,
                :notes
            )
        ");

        return $stmt->execute([
            ':farm_id' => (int)($data['farm_id'] ?? 0),
            ':loss_type' => $data['loss_type'],
            ':reference_id' => !empty($data['reference_id']) ? (int)$data['reference_id'] : null,
            ':loss_date' => $data['loss_date'],
            ':description' => $data['description'],
            ':quantity' => !empty($data['quantity']) ? (float)$data['quantity'] : null,
            ':unit_cost' => !empty($data['unit_cost']) ? (float)$data['unit_cost'] : null,
            ':total_loss_amount' => (float)($data['total_loss_amount'] ?? 0),
            ':reason' => $data['reason'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE losses_writeoffs SET
                farm_id = :farm_id,
                loss_type = :loss_type,
                reference_id = :reference_id,
                loss_date = :loss_date,
                description = :description,
                quantity = :quantity,
                unit_cost = :unit_cost,
                total_loss_amount = :total_loss_amount,
                reason = :reason,
                notes = :notes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':farm_id' => (int)($data['farm_id'] ?? 0),
            ':loss_type' => $data['loss_type'],
            ':reference_id' => !empty($data['reference_id']) ? (int)$data['reference_id'] : null,
            ':loss_date' => $data['loss_date'],
            ':description' => $data['description'],
            ':quantity' => !empty($data['quantity']) ? (float)$data['quantity'] : null,
            ':unit_cost' => !empty($data['unit_cost']) ? (float)$data['unit_cost'] : null,
            ':total_loss_amount' => (float)($data['total_loss_amount'] ?? 0),
            ':reason' => $data['reason'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM losses_writeoffs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totals(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(total_loss_amount), 0) AS total_losses,
                COALESCE(SUM(CASE WHEN YEAR(loss_date) = YEAR(CURDATE()) AND MONTH(loss_date) = MONTH(CURDATE()) THEN total_loss_amount ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN loss_date = CURDATE() THEN total_loss_amount ELSE 0 END), 0) AS today
            FROM losses_writeoffs
        ");

        $recorded = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_records' => 0,
            'total_losses' => 0,
            'current_month' => 0,
            'today' => 0,
        ];

        // Calculate unrecorded mortality losses
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) AS unrecorded_count,
                COALESCE(SUM(
                    mr.quantity * (
                        COALESCE(ab.initial_unit_cost, 0) +
                        COALESCE(
                            (SELECT SUM(fr.quantity_kg * COALESCE(fr.unit_cost, 0))
                             FROM feed_records fr
                             WHERE fr.batch_id = mr.batch_id 
                             AND fr.record_date <= mr.record_date
                             AND fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
                            ) / NULLIF(ab.initial_quantity, 0), 
                            0
                        ) +
                        COALESCE(
                            (SELECT SUM(medr.quantity_used * COALESCE(medr.unit_cost, 0))
                             FROM medication_records medr
                             WHERE medr.batch_id = mr.batch_id 
                             AND medr.record_date <= mr.record_date
                             AND medr.unit_cost IS NOT NULL AND medr.unit_cost > 0
                            ) / NULLIF(ab.initial_quantity, 0), 
                            0
                        ) +
                        COALESCE(
                            (SELECT SUM(COALESCE(vr.cost_amount, 0))
                             FROM vaccination_records vr
                             WHERE vr.batch_id = mr.batch_id 
                             AND vr.record_date <= mr.record_date
                             AND vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
                            ) / NULLIF(ab.initial_quantity, 0), 
                            0
                        )
                    )
                ), 0) AS unrecorded_losses
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            WHERE mr.id NOT IN (
                SELECT reference_id 
                FROM losses_writeoffs 
                WHERE loss_type = 'mortality' 
                AND reference_id IS NOT NULL
            )
        ");

        $unrecorded = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'unrecorded_count' => 0,
            'unrecorded_losses' => 0,
        ];

        return [
            'recorded_count' => (int)$recorded['total_records'],
            'recorded_losses' => (float)$recorded['total_losses'],
            'current_month' => (float)$recorded['current_month'],
            'today' => (float)$recorded['today'],
            'unrecorded_count' => (int)$unrecorded['unrecorded_count'],
            'unrecorded_losses' => (float)$unrecorded['unrecorded_losses'],
            'total_count' => (int)$recorded['total_records'] + (int)$unrecorded['unrecorded_count'],
            'total_losses' => (float)$recorded['total_losses'] + (float)$unrecorded['unrecorded_losses'],
        ];
    }

    public function byType(): array
    {
        $stmt = $this->db->query("
            SELECT 
                loss_type,
                COUNT(*) AS count,
                COALESCE(SUM(total_loss_amount), 0) AS total_amount
            FROM losses_writeoffs
            GROUP BY loss_type
            ORDER BY total_amount DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function monthlyTrend(int $months = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(loss_date, '%Y-%m') AS month_key,
                DATE_FORMAT(loss_date, '%b %Y') AS month_label,
                COALESCE(SUM(total_loss_amount), 0) AS total_amount
            FROM losses_writeoffs
            WHERE loss_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(loss_date, '%Y-%m'), DATE_FORMAT(loss_date, '%b %Y')
            ORDER BY month_key ASC
        ");
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMortalityLosses(): array
    {
        $stmt = $this->db->query("
            SELECT 
                mr.id,
                mr.record_date,
                mr.quantity,
                mr.batch_id,
                COALESCE(ab.batch_name, ab.batch_code) AS batch_name,
                mr.cause,
                ab.initial_quantity,
                ab.current_quantity,
                COALESCE(ab.initial_unit_cost, 0) AS initial_unit_cost,
                
                -- Calculate accumulated costs per bird up to mortality date
                COALESCE(ab.initial_unit_cost, 0) AS purchase_cost_per_bird,
                
                -- Feed cost per bird (total feed cost / initial quantity)
                COALESCE(
                    (SELECT SUM(fr.quantity_kg * COALESCE(fr.unit_cost, 0))
                     FROM feed_records fr
                     WHERE fr.batch_id = mr.batch_id 
                     AND fr.record_date <= mr.record_date
                     AND fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) AS feed_cost_per_bird,
                
                -- Medication cost per bird
                COALESCE(
                    (SELECT SUM(medr.quantity_used * COALESCE(medr.unit_cost, 0))
                     FROM medication_records medr
                     WHERE medr.batch_id = mr.batch_id 
                     AND medr.record_date <= mr.record_date
                     AND medr.unit_cost IS NOT NULL AND medr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) AS medication_cost_per_bird,
                
                -- Vaccination cost per bird
                COALESCE(
                    (SELECT SUM(COALESCE(vr.cost_amount, 0))
                     FROM vaccination_records vr
                     WHERE vr.batch_id = mr.batch_id 
                     AND vr.record_date <= mr.record_date
                     AND vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) AS vaccination_cost_per_bird,
                
                -- Total accumulated cost per bird
                COALESCE(ab.initial_unit_cost, 0) +
                COALESCE(
                    (SELECT SUM(fr.quantity_kg * COALESCE(fr.unit_cost, 0))
                     FROM feed_records fr
                     WHERE fr.batch_id = mr.batch_id 
                     AND fr.record_date <= mr.record_date
                     AND fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) +
                COALESCE(
                    (SELECT SUM(medr.quantity_used * COALESCE(medr.unit_cost, 0))
                     FROM medication_records medr
                     WHERE medr.batch_id = mr.batch_id 
                     AND medr.record_date <= mr.record_date
                     AND medr.unit_cost IS NOT NULL AND medr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) +
                COALESCE(
                    (SELECT SUM(COALESCE(vr.cost_amount, 0))
                     FROM vaccination_records vr
                     WHERE vr.batch_id = mr.batch_id 
                     AND vr.record_date <= mr.record_date
                     AND vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) AS total_cost_per_bird,
                
                -- Total loss amount (quantity × total cost per bird)
                mr.quantity * (
                    COALESCE(ab.initial_unit_cost, 0) +
                    COALESCE(
                        (SELECT SUM(fr.quantity_kg * COALESCE(fr.unit_cost, 0))
                         FROM feed_records fr
                         WHERE fr.batch_id = mr.batch_id 
                         AND fr.record_date <= mr.record_date
                         AND fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
                        ) / NULLIF(ab.initial_quantity, 0), 
                        0
                    ) +
                    COALESCE(
                        (SELECT SUM(medr.quantity_used * COALESCE(medr.unit_cost, 0))
                         FROM medication_records medr
                         WHERE medr.batch_id = mr.batch_id 
                         AND medr.record_date <= mr.record_date
                         AND medr.unit_cost IS NOT NULL AND medr.unit_cost > 0
                        ) / NULLIF(ab.initial_quantity, 0), 
                        0
                    ) +
                    COALESCE(
                        (SELECT SUM(COALESCE(vr.cost_amount, 0))
                         FROM vaccination_records vr
                         WHERE vr.batch_id = mr.batch_id 
                         AND vr.record_date <= mr.record_date
                         AND vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
                        ) / NULLIF(ab.initial_quantity, 0), 
                        0
                    )
                ) AS estimated_loss
                
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            WHERE mr.id NOT IN (
                SELECT reference_id 
                FROM losses_writeoffs 
                WHERE loss_type = 'mortality' 
                AND reference_id IS NOT NULL
            )
            ORDER BY mr.record_date DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recordMortalityLoss(int $mortalityRecordId): bool
    {
        // Get mortality record with calculated costs
        $stmt = $this->db->prepare("
            SELECT 
                mr.*,
                COALESCE(ab.batch_name, ab.batch_code) AS batch_name,
                ab.initial_quantity,
                
                -- Calculate total accumulated cost per bird
                COALESCE(ab.initial_unit_cost, 0) +
                COALESCE(
                    (SELECT SUM(fr.quantity_kg * COALESCE(fr.unit_cost, 0))
                     FROM feed_records fr
                     WHERE fr.batch_id = mr.batch_id 
                     AND fr.record_date <= mr.record_date
                     AND fr.unit_cost IS NOT NULL AND fr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) +
                COALESCE(
                    (SELECT SUM(medr.quantity_used * COALESCE(medr.unit_cost, 0))
                     FROM medication_records medr
                     WHERE medr.batch_id = mr.batch_id 
                     AND medr.record_date <= mr.record_date
                     AND medr.unit_cost IS NOT NULL AND medr.unit_cost > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) +
                COALESCE(
                    (SELECT SUM(COALESCE(vr.cost_amount, 0))
                     FROM vaccination_records vr
                     WHERE vr.batch_id = mr.batch_id 
                     AND vr.record_date <= mr.record_date
                     AND vr.cost_amount IS NOT NULL AND vr.cost_amount > 0
                    ) / NULLIF(ab.initial_quantity, 0), 
                    0
                ) AS total_cost_per_bird
                
            FROM mortality_records mr
            LEFT JOIN animal_batches ab ON ab.id = mr.batch_id
            WHERE mr.id = ?
        ");
        $stmt->execute([$mortalityRecordId]);
        $mortality = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mortality) {
            return false;
        }

        $unitCost = (float)$mortality['total_cost_per_bird'];
        $totalLoss = $mortality['quantity'] * $unitCost;

        return $this->create([
            'farm_id' => $mortality['farm_id'],
            'loss_type' => 'mortality',
            'reference_id' => $mortalityRecordId,
            'loss_date' => $mortality['record_date'],
            'description' => "Mortality Loss - Batch: {$mortality['batch_name']} (includes purchase + feed + medication + vaccination costs)",
            'quantity' => $mortality['quantity'],
            'unit_cost' => $unitCost,
            'total_loss_amount' => $totalLoss,
            'reason' => $mortality['cause'] ?? 'Unknown',
            'notes' => $mortality['notes'],
        ]);
    }

    public function getLossImpactAnalysis(): array
    {
        // Get loss impact by batch
        $stmt = $this->db->query("
            SELECT 
                ab.batch_name,
                ab.batch_code,
                ab.initial_quantity,
                ab.current_quantity,
                COUNT(lw.id) AS loss_count,
                SUM(lw.quantity) AS total_birds_lost,
                SUM(lw.total_loss_amount) AS total_loss_value,
                (SUM(lw.quantity) / ab.initial_quantity * 100) AS mortality_rate
            FROM animal_batches ab
            LEFT JOIN losses_writeoffs lw ON lw.reference_id IN (
                SELECT id FROM mortality_records WHERE batch_id = ab.id
            ) AND lw.loss_type = 'mortality'
            WHERE ab.status = 'active'
            GROUP BY ab.id, ab.batch_name, ab.batch_code, ab.initial_quantity, ab.current_quantity
            HAVING loss_count > 0
            ORDER BY mortality_rate DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLossTrends(int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(loss_date) AS date,
                loss_type,
                COUNT(*) AS count,
                SUM(total_loss_amount) AS total_amount
            FROM losses_writeoffs
            WHERE loss_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY DATE(loss_date), loss_type
            ORDER BY date DESC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

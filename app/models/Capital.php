<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * Capital Model
 *
 * The business is ONE entity. Capital tracking is purely for record-keeping:
 * - Who contributed what amount to start/reinvest in the business
 * - All operations (expenses, sales, feed, etc.) belong to the business as a whole
 * - owner_id here means "who put in this capital" - not who owns the operation
 */
class Capital extends Model
{
    public function all(): array
    {
        try {
            return $this->db->query("
                SELECT c.*, f.farm_name,
                       u.full_name AS contributor_name
                FROM capital_entries c
                LEFT JOIN farms f ON f.id = c.farm_id
                LEFT JOIN users u ON u.id = c.owner_id
                ORDER BY c.entry_date DESC, c.id DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM capital_entries WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) { return null; }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO capital_entries
                    (farm_id, owner_id, entry_date, title, capital_type, source_name, reference_no, amount, description, notes)
                VALUES
                    (:farm_id, :owner_id, :entry_date, :title, :capital_type, :source_name, :reference_no, :amount, :description, :notes)
            ");
            return $stmt->execute([
                ':farm_id'      => (int)($data['farm_id'] ?? 1),
                ':owner_id'     => !empty($data['owner_id']) ? (int)$data['owner_id'] : null,
                ':entry_date'   => $data['entry_date'],
                ':title'        => $data['title'] ?? null,
                ':capital_type' => $data['capital_type'] ?? 'owner_equity',
                ':source_name'  => $data['source_name'] ?? null,
                ':reference_no' => $data['reference_no'] ?? null,
                ':amount'       => (float)($data['amount'] ?? 0),
                ':description'  => $data['description'] ?? null,
                ':notes'        => $data['notes'] ?? null,
            ]);
        } catch (\Throwable $e) { return false; }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE capital_entries SET
                    farm_id=:farm_id, owner_id=:owner_id, entry_date=:entry_date,
                    title=:title, capital_type=:capital_type, source_name=:source_name,
                    reference_no=:reference_no, amount=:amount, description=:description, notes=:notes
                WHERE id=:id
            ");
            return $stmt->execute([
                ':id'           => $id,
                ':farm_id'      => (int)($data['farm_id'] ?? 1),
                ':owner_id'     => !empty($data['owner_id']) ? (int)$data['owner_id'] : null,
                ':entry_date'   => $data['entry_date'],
                ':title'        => $data['title'] ?? null,
                ':capital_type' => $data['capital_type'] ?? 'owner_equity',
                ':source_name'  => $data['source_name'] ?? null,
                ':reference_no' => $data['reference_no'] ?? null,
                ':amount'       => (float)($data['amount'] ?? 0),
                ':description'  => $data['description'] ?? null,
                ':notes'        => $data['notes'] ?? null,
            ]);
        } catch (\Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            return $this->db->prepare("DELETE FROM capital_entries WHERE id=?")->execute([$id]);
        } catch (\Throwable $e) { return false; }
    }

    /**
     * Business-wide totals (all capital combined)
     */
    public function totals(): array
    {
        try {
            $this->db = \Database::connect();
            $row = $this->db->query("
                SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(amount), 0) AS total_capital,
                    COALESCE(SUM(CASE WHEN capital_type='owner_equity' THEN amount ELSE 0 END), 0) AS owner_equity,
                    COALESCE(SUM(CASE WHEN capital_type='retained_earnings' THEN amount ELSE 0 END), 0) AS retained_earnings,
                    COALESCE(SUM(CASE WHEN capital_type='loan_capital' THEN amount ELSE 0 END), 0) AS loan_capital,
                    COALESCE(SUM(CASE WHEN capital_type='reinvestment' THEN amount ELSE 0 END), 0) AS reinvestment,
                    COALESCE(SUM(CASE WHEN capital_type='grant' THEN amount ELSE 0 END), 0) AS grant
                FROM capital_entries
            ")->fetch(PDO::FETCH_ASSOC);
            return $row ?: ['total_records'=>0,'total_capital'=>0,'owner_equity'=>0,'retained_earnings'=>0,'loan_capital'=>0,'reinvestment'=>0,'grant'=>0];
        } catch (\Throwable $e) {
            return ['total_records'=>0,'total_capital'=>0,'owner_equity'=>0,'retained_earnings'=>0,'loan_capital'=>0,'reinvestment'=>0,'grant'=>0];
        }
    }

    /**
     * Per-contributor breakdown - who put in how much
     */
    public function byContributor(): array
    {
        try {
            return $this->db->query("
                SELECT
                    u.id AS owner_id,
                    COALESCE(u.full_name, 'Unassigned') AS contributor_name,
                    u.username,
                    COUNT(c.id) AS entries,
                    COALESCE(SUM(c.amount), 0) AS total_contributed,
                    COALESCE(SUM(CASE WHEN c.capital_type='owner_equity' THEN c.amount ELSE 0 END), 0) AS equity,
                    COALESCE(SUM(CASE WHEN c.capital_type='reinvestment' THEN c.amount ELSE 0 END), 0) AS reinvestment,
                    COALESCE(SUM(CASE WHEN c.capital_type='retained_earnings' THEN c.amount ELSE 0 END), 0) AS retained
                FROM capital_entries c
                LEFT JOIN users u ON u.id = c.owner_id
                GROUP BY u.id, u.full_name, u.username
                ORDER BY total_contributed DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    public function byType(): array
    {
        try {
            return $this->db->query("
                SELECT capital_type, COUNT(*) AS records, COALESCE(SUM(amount),0) AS total
                FROM capital_entries GROUP BY capital_type ORDER BY total DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }
}

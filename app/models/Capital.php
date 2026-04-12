<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * Capital Model
 * The business is ONE entity. Capital tracks who contributed what.
 * NULL owner_id = Business/General (retained earnings, grants, etc.)
 */
class Capital extends Model
{
    public function all(): array
    {
        try {
            $this->db = Database::connect();
            return $this->db->query("
                SELECT c.*, f.farm_name,
                       COALESCE(u.full_name, 'Business (General)') AS contributor_name
                FROM capital_entries c
                LEFT JOIN farms f ON f.id = c.farm_id
                LEFT JOIN users u ON u.id = c.owner_id
                ORDER BY c.entry_date DESC, c.id DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) { return []; }
    }

    public function find(int $id): ?array
    {
        try {
            $this->db = Database::connect();
            $stmt = $this->db->prepare("SELECT * FROM capital_entries WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) { return null; }
    }

    public function create(array $data): bool
    {
        try {
            $this->db = Database::connect();
            $ownerId = !empty($data['owner_id']) && $data['owner_id'] !== 'business' ? (int)$data['owner_id'] : null;
            $stmt = $this->db->prepare("
                INSERT INTO capital_entries
                    (farm_id, owner_id, entry_date, title, capital_type, source_name, reference_no, amount, description, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                (int)($data['farm_id'] ?? 1),
                $ownerId,
                $data['entry_date'],
                $data['title'] ?? null,
                $data['capital_type'] ?? 'owner_equity',
                $data['source_name'] ?? null,
                $data['reference_no'] ?? null,
                (float)($data['amount'] ?? 0),
                $data['description'] ?? null,
                $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->db = Database::connect();
            $ownerId = !empty($data['owner_id']) && $data['owner_id'] !== 'business' ? (int)$data['owner_id'] : null;
            $stmt = $this->db->prepare("
                UPDATE capital_entries SET
                    farm_id=?, owner_id=?, entry_date=?, title=?, capital_type=?,
                    source_name=?, reference_no=?, amount=?, description=?, notes=?
                WHERE id=?
            ");
            return $stmt->execute([
                (int)($data['farm_id'] ?? 1),
                $ownerId,
                $data['entry_date'],
                $data['title'] ?? null,
                $data['capital_type'] ?? 'owner_equity',
                $data['source_name'] ?? null,
                $data['reference_no'] ?? null,
                (float)($data['amount'] ?? 0),
                $data['description'] ?? null,
                $data['notes'] ?? null,
                $id,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            $this->db = Database::connect();
            return $this->db->prepare("DELETE FROM capital_entries WHERE id=?")->execute([$id]);
        } catch (Throwable $e) { return false; }
    }

    public function totals(): array
    {
        $d = ['total_records'=>0,'total_capital'=>0,'owner_equity'=>0,'retained_earnings'=>0,'loan_capital'=>0,'reinvestment'=>0,'grant_amount'=>0];
        try {
            $this->db = Database::connect();
            $row = $this->db->query("
                SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(amount), 0) AS total_capital,
                    COALESCE(SUM(CASE WHEN capital_type='owner_equity'      THEN amount ELSE 0 END), 0) AS owner_equity,
                    COALESCE(SUM(CASE WHEN capital_type='retained_earnings' THEN amount ELSE 0 END), 0) AS retained_earnings,
                    COALESCE(SUM(CASE WHEN capital_type='loan_capital'      THEN amount ELSE 0 END), 0) AS loan_capital,
                    COALESCE(SUM(CASE WHEN capital_type='reinvestment'      THEN amount ELSE 0 END), 0) AS reinvestment,
                    COALESCE(SUM(CASE WHEN capital_type='grant'             THEN amount ELSE 0 END), 0) AS grant_amount
                FROM capital_entries
            ")->fetch(PDO::FETCH_ASSOC);
            if (!$row) return $d;
            return [
                'total_records'     => (int)$row['total_records'],
                'total_capital'     => (float)$row['total_capital'],
                'owner_equity'      => (float)$row['owner_equity'],
                'retained_earnings' => (float)$row['retained_earnings'],
                'loan_capital'      => (float)$row['loan_capital'],
                'reinvestment'      => (float)$row['reinvestment'],
                'grant_amount'      => (float)$row['grant_amount'],
            ];
        } catch (Throwable $e) {
            error_log('Capital::totals error: ' . $e->getMessage());
            return $d;
        }
    }

    public function byContributor(): array
    {
        try {
            $this->db = Database::connect();

            $named = $this->db->query("
                SELECT
                    u.id AS owner_id,
                    u.full_name AS contributor_name,
                    u.username,
                    COUNT(c.id) AS entries,
                    COALESCE(SUM(c.amount), 0) AS total_contributed,
                    COALESCE(SUM(CASE WHEN c.capital_type='owner_equity'      THEN c.amount ELSE 0 END), 0) AS equity,
                    COALESCE(SUM(CASE WHEN c.capital_type='reinvestment'      THEN c.amount ELSE 0 END), 0) AS reinvestment,
                    COALESCE(SUM(CASE WHEN c.capital_type='retained_earnings' THEN c.amount ELSE 0 END), 0) AS retained
                FROM capital_entries c
                INNER JOIN users u ON u.id = c.owner_id
                GROUP BY u.id, u.full_name, u.username
                ORDER BY total_contributed DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $gen = $this->db->query("
                SELECT COUNT(*) AS entries,
                    COALESCE(SUM(amount), 0) AS total_contributed,
                    COALESCE(SUM(CASE WHEN capital_type='owner_equity'      THEN amount ELSE 0 END), 0) AS equity,
                    COALESCE(SUM(CASE WHEN capital_type='reinvestment'      THEN amount ELSE 0 END), 0) AS reinvestment,
                    COALESCE(SUM(CASE WHEN capital_type='retained_earnings' THEN amount ELSE 0 END), 0) AS retained
                FROM capital_entries WHERE owner_id IS NULL
            ")->fetch(PDO::FETCH_ASSOC);

            if ($gen && (float)$gen['total_contributed'] > 0) {
                $named[] = [
                    'owner_id'          => null,
                    'contributor_name'  => 'Business (General)',
                    'username'          => null,
                    'entries'           => (int)$gen['entries'],
                    'total_contributed' => (float)$gen['total_contributed'],
                    'equity'            => (float)$gen['equity'],
                    'reinvestment'      => (float)$gen['reinvestment'],
                    'retained'          => (float)$gen['retained'],
                ];
            }

            return $named;
        } catch (Throwable $e) { return []; }
    }

    public function byType(): array
    {
        try {
            $this->db = Database::connect();
            return $this->db->query("
                SELECT capital_type, COUNT(*) AS records, COALESCE(SUM(amount),0) AS total
                FROM capital_entries GROUP BY capital_type ORDER BY total DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) { return []; }
    }
}

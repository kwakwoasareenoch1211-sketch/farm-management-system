<?php

require_once BASE_PATH . 'app/core/Model.php';

class Capital extends Model
{
    private bool $tableExists;

    public function __construct()
    {
        parent::__construct();
        $this->tableExists = $this->checkTable();
    }

    private function checkTable(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='capital_entries'");
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function all(): array
    {
        if (!$this->tableExists) return [];
        try {
            return $this->db->query("
                SELECT c.*, f.farm_name
                FROM capital_entries c
                LEFT JOIN farms f ON f.id = c.farm_id
                ORDER BY c.entry_date DESC, c.id DESC
            ")->fetchAll() ?: [];
        } catch (Throwable $e) { return []; }
    }

    public function find(int $id): ?array
    {
        if (!$this->tableExists) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM capital_entries WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (Throwable $e) { return null; }
    }

    public function create(array $data): bool
    {
        if (!$this->tableExists) return false;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO capital_entries
                    (farm_id, entry_date, description, amount, entry_type, notes)
                VALUES
                    (:farm_id, :entry_date, :description, :amount, :entry_type, :notes)
            ");
            return $stmt->execute([
                ':farm_id'      => (int)($data['farm_id'] ?? 0),
                ':entry_date'   => $data['entry_date'],
                ':description'  => $data['description'] ?? null,
                ':amount'       => (float)($data['amount'] ?? 0),
                ':entry_type'   => $data['entry_type'] ?? 'contribution',
                ':notes'        => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->tableExists) return false;
        try {
            $stmt = $this->db->prepare("
                UPDATE capital_entries SET
                    farm_id=:farm_id, entry_date=:entry_date, description=:description,
                    amount=:amount, entry_type=:entry_type, notes=:notes
                WHERE id=:id
            ");
            return $stmt->execute([
                ':id'           => $id,
                ':farm_id'      => (int)($data['farm_id'] ?? 0),
                ':entry_date'   => $data['entry_date'],
                ':description'  => $data['description'] ?? null,
                ':amount'       => (float)($data['amount'] ?? 0),
                ':entry_type'   => $data['entry_type'] ?? 'contribution',
                ':notes'        => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        if (!$this->tableExists) return false;
        try {
            return $this->db->prepare("DELETE FROM capital_entries WHERE id=?")->execute([$id]);
        } catch (Throwable $e) { return false; }
    }

    public function totals(): array
    {
        $defaults = [
            'total_records' => 0, 'total_capital' => 0,
            'contributions' => 0, 'withdrawals' => 0,
        ];
        if (!$this->tableExists) return $defaults;
        try {
            $row = $this->db->query("
                SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(amount),0) AS total_capital,
                    COALESCE(SUM(CASE WHEN entry_type='contribution' THEN amount ELSE 0 END),0) AS contributions,
                    COALESCE(SUM(CASE WHEN entry_type='withdrawal' THEN amount ELSE 0 END),0) AS withdrawals
                FROM capital_entries
            ")->fetch() ?: [];
            return array_merge($defaults, $row);
        } catch (Throwable $e) { return $defaults; }
    }

    public function byType(): array
    {
        if (!$this->tableExists) return [];
        try {
            return $this->db->query("
                SELECT entry_type, COUNT(*) AS records, COALESCE(SUM(amount),0) AS total
                FROM capital_entries GROUP BY entry_type ORDER BY total DESC
            ")->fetchAll() ?: [];
        } catch (Throwable $e) { return []; }
    }

    public function isReady(): bool
    {
        return $this->tableExists;
    }
}

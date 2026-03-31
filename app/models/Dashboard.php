<?php

require_once BASE_PATH . 'app/core/Model.php';

class Dashboard extends Model
{
    public function getAdminSummary(): array
    {
        $summary = [];

        $summary['users'] = (int)$this->safeScalar("SELECT COUNT(*) FROM users");
        $summary['customers'] = (int)$this->safeScalar("SELECT COUNT(*) FROM customers");
        $summary['total_batches'] = (int)$this->safeScalar("SELECT COUNT(*) FROM animal_batches");
        $summary['active_batches'] = (int)$this->safeScalar("SELECT COUNT(*) FROM animal_batches WHERE status = 'active'");

        $summary['total_birds'] = (float)$this->safeScalar("
            SELECT COALESCE(SUM(current_quantity), 0)
            FROM animal_batches
        ");

        $summary['total_eggs'] = (float)$this->safeScalarFromExistingTable('egg_production_records', function () {
            if ($this->columnExists('egg_production_records', 'quantity')) {
                return "SELECT COALESCE(SUM(quantity), 0) FROM egg_production_records";
            }
            return null;
        });

        $summary['total_mortality'] = (float)$this->safeScalarFromExistingTable('mortality_records', function () {
            if ($this->columnExists('mortality_records', 'quantity')) {
                return "SELECT COALESCE(SUM(quantity), 0) FROM mortality_records";
            }
            return null;
        });

        $summary['total_feed_used_kg'] = (float)$this->safeScalarFromExistingTable('feed_records', function () {
            if ($this->columnExists('feed_records', 'quantity_kg')) {
                return "SELECT COALESCE(SUM(quantity_kg), 0) FROM feed_records";
            }
            return null;
        });

        $summary['total_feed_cost'] = (float)$this->getFeedCost();
        $summary['total_medication_cost'] = (float)$this->getMedicationCost();
        $summary['total_vaccination_cost'] = (float)$this->getVaccinationCost();

        $summary['sales_revenue'] = (float)$this->safeScalar("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM sales
        ");

        $summary['expenses_value'] = (float)$this->getExpensesTotal();
        $summary['inventory_value'] = (float)$this->getInventoryValue();
        $summary['assets_base_value'] = (float)$this->getAssetsTotal();
        $summary['liabilities_base_value'] = (float)$this->getLiabilitiesTotal();

        $summary['assets_value'] = $summary['assets_base_value'] + $summary['inventory_value'];
        $summary['liabilities_value'] = $summary['liabilities_base_value'] + $summary['expenses_value'];

        $summary['low_stock_count'] = (int)$this->safeScalarFromExistingTable('inventory_item', function () {
            if ($this->columnExists('inventory_item', 'current_stock') && $this->columnExists('inventory_item', 'reorder_level')) {
                return 0; // Inventory system removed
            }
            return null;
        });

        $summary['total_stock_value'] = $summary['inventory_value'];

        $summary['working_capital'] = $summary['assets_value'] - $summary['liabilities_value'];
        $summary['net_position'] = $summary['sales_revenue'] - (
            $summary['expenses_value'] +
            $summary['total_feed_cost'] +
            $summary['total_medication_cost'] +
            $summary['total_vaccination_cost']
        );

        return $summary;
    }

    public function recentActivities(int $limit = 10): array
    {
        $activities = [];

        if ($this->tableExists('feed_records')) {
            $titleCol = $this->firstExistingColumn('feed_records', ['feed_name', 'item_name', 'title']);
            $amountCol = $this->firstExistingColumn('feed_records', ['quantity_kg', 'quantity', 'amount']);
            $dateCol = $this->firstExistingColumn('feed_records', ['record_date', 'created_at', 'date']);

            if ($titleCol && $amountCol && $dateCol) {
                $activities = array_merge($activities, $this->mapRows($this->fetchAllSafe("
                    SELECT id, {$dateCol} AS activity_date, 'Feed Usage' AS activity_type,
                           {$titleCol} AS title,
                           {$amountCol} AS amount
                    FROM feed_records
                "), 'feed'));
            }
        }

        if ($this->tableExists('medication_records')) {
            $titleCol = $this->firstExistingColumn('medication_records', ['medication_name', 'item_name', 'title']);
            $amountCol = $this->firstExistingColumn('medication_records', ['quantity_used', 'quantity', 'amount']);
            $dateCol = $this->firstExistingColumn('medication_records', ['record_date', 'created_at', 'date']);

            if ($titleCol && $amountCol && $dateCol) {
                $activities = array_merge($activities, $this->mapRows($this->fetchAllSafe("
                    SELECT id, {$dateCol} AS activity_date, 'Medication' AS activity_type,
                           {$titleCol} AS title,
                           {$amountCol} AS amount
                    FROM medication_records
                "), 'medication'));
            }
        }

        if ($this->tableExists('vaccination_records')) {
            $titleCol = $this->firstExistingColumn('vaccination_records', ['vaccine_name', 'item_name', 'title']);
            $amountCol = $this->firstExistingColumn('vaccination_records', ['cost_amount', 'dose_qty', 'amount']);
            $dateCol = $this->firstExistingColumn('vaccination_records', ['record_date', 'created_at', 'date']);

            if ($titleCol && $amountCol && $dateCol) {
                $activities = array_merge($activities, $this->mapRows($this->fetchAllSafe("
                    SELECT id, {$dateCol} AS activity_date, 'Vaccination' AS activity_type,
                           {$titleCol} AS title,
                           {$amountCol} AS amount
                    FROM vaccination_records
                "), 'vaccination'));
            }
        }

        if ($this->tableExists('sales')) {
            $titleCol = $this->firstExistingColumn('sales', ['item_name', 'title', 'sale_type']);
            $amountCol = $this->firstExistingColumn('sales', ['total_amount', 'subtotal', 'amount_paid']);
            $dateCol = $this->firstExistingColumn('sales', ['sale_date', 'created_at', 'date']);

            if ($titleCol && $amountCol && $dateCol) {
                $activities = array_merge($activities, $this->mapRows($this->fetchAllSafe("
                    SELECT id, {$dateCol} AS activity_date, 'Sale' AS activity_type,
                           {$titleCol} AS title,
                           {$amountCol} AS amount
                    FROM sales
                "), 'sales'));
            }
        }

        if ($this->tableExists('expenses')) {
            $titleCol = $this->firstExistingColumn('expenses', ['expense_title', 'title', 'expense_name', 'description', 'category']);
            $amountCol = $this->firstExistingColumn('expenses', ['amount']);
            $dateCol = $this->firstExistingColumn('expenses', ['expense_date', 'date', 'created_at']);

            if ($titleCol && $amountCol && $dateCol) {
                $activities = array_merge($activities, $this->mapRows($this->fetchAllSafe("
                    SELECT id, {$dateCol} AS activity_date, 'Expense' AS activity_type,
                           {$titleCol} AS title,
                           {$amountCol} AS amount
                    FROM expenses
                "), 'expense'));
            }
        }

        usort($activities, function ($a, $b) {
            return strcmp((string)$b['activity_date'], (string)$a['activity_date']);
        });

        return array_slice($activities, 0, $limit);
    }

    private function getFeedCost(): float
    {
        if (!$this->tableExists('feed_records')) {
            return 0.0;
        }

        if ($this->columnExists('feed_records', 'total_cost')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(total_cost), 0) FROM feed_records");
        }

        if ($this->columnExists('feed_records', 'quantity_kg') && $this->columnExists('feed_records', 'unit_cost')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) FROM feed_records");
        }

        if ($this->columnExists('feed_records', 'quantity_kg') && $this->columnExists('feed_records', 'cost_per_kg')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(quantity_kg * cost_per_kg), 0) FROM feed_records");
        }

        if ($this->columnExists('feed_records', 'cost_amount')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(cost_amount), 0) FROM feed_records");
        }

        return 0.0;
    }

    private function getMedicationCost(): float
    {
        if (!$this->tableExists('medication_records')) {
            return 0.0;
        }

        if ($this->columnExists('medication_records', 'total_cost')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(total_cost), 0) FROM medication_records");
        }

        if ($this->columnExists('medication_records', 'quantity_used') && $this->columnExists('medication_records', 'unit_cost')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(quantity_used * unit_cost), 0) FROM medication_records");
        }

        if ($this->columnExists('medication_records', 'cost_amount')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(cost_amount), 0) FROM medication_records");
        }

        return 0.0;
    }

    private function getVaccinationCost(): float
    {
        if (!$this->tableExists('vaccination_records')) {
            return 0.0;
        }

        if ($this->columnExists('vaccination_records', 'cost_amount')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(cost_amount), 0) FROM vaccination_records");
        }

        if ($this->columnExists('vaccination_records', 'total_cost')) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM(total_cost), 0) FROM vaccination_records");
        }

        return 0.0;
    }

    private function getExpensesTotal(): float
    {
        if (!$this->tableExists('expenses') || !$this->columnExists('expenses', 'amount')) {
            return 0.0;
        }

        return (float)$this->safeScalar("SELECT COALESCE(SUM(amount), 0) FROM expenses");
    }

    private function getAssetsTotal(): float
    {
        if (!$this->tableExists('assets') || !$this->columnExists('assets', 'amount')) {
            return 0.0;
        }

        return (float)$this->safeScalar("SELECT COALESCE(SUM(amount), 0) FROM assets");
    }

    private function getLiabilitiesTotal(): float
    {
        if (!$this->tableExists('liabilities') || !$this->columnExists('liabilities', 'amount')) {
            return 0.0;
        }

        return (float)$this->safeScalar("SELECT COALESCE(SUM(amount), 0) FROM liabilities");
    }

    private function getInventoryValue(): float
    {
        if (!$this->tableExists('inventory_item')) {
            return 0.0;
        }

        $stockCol = $this->firstExistingColumn('inventory_item', ['current_stock', 'quantity_on_hand', 'stock_quantity']);
        $costCol = $this->firstExistingColumn('inventory_item', ['unit_cost', 'cost_per_unit', 'average_cost']);

        if ($stockCol && $costCol) {
            return (float)$this->safeScalar("SELECT COALESCE(SUM({$stockCol} * {$costCol}), 0) FROM inventory_item");
        }

        return 0.0;
    }

    private function safeScalar(string $sql)
    {
        try {
            $value = $this->db->query($sql)->fetchColumn();
            return $value !== false ? $value : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function safeScalarFromExistingTable(string $table, callable $sqlBuilder)
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        $sql = $sqlBuilder();
        if (!$sql) {
            return 0;
        }

        return $this->safeScalar($sql);
    }

    private function fetchAllSafe(string $sql): array
    {
        try {
            return $this->db->query($sql)->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function mapRows(array $rows, string $source): array
    {
        return array_map(function ($row) use ($source) {
            return [
                'id' => $row['id'] ?? null,
                'activity_date' => $row['activity_date'] ?? '',
                'activity_type' => $row['activity_type'] ?? '',
                'title' => $row['title'] ?? '',
                'amount' => (float)($row['amount'] ?? 0),
                'source' => $source,
            ];
        }, $rows);
    }

    private function tableExists(string $table): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
            ");
            $stmt->execute([$table]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
            ");
            $stmt->execute([$table, $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if ($this->columnExists($table, $column)) {
                return $column;
            }
        }
        return null;
    }
}
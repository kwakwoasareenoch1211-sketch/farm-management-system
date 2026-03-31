<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * Investment Model
 *
 * Investment types and how returns are auto-calculated:
 *
 * - eggs       : actual egg sales revenue / elapsed weeks/months/years
 * - livestock  : actual live bird sales revenue / elapsed time
 * - equipment  : straight-line depreciation + expected_return spread over useful_life_years
 * - infrastructure: same as equipment
 * - land       : expected_return / useful_life_years (appreciation)
 * - technology : expected_return / useful_life_years
 * - other      : expected_return / useful_life_years
 *
 * For egg/livestock types, the system reads ACTUAL revenue from the database
 * to compute real ROI rather than relying on manually entered expected_return.
 */
class Investment extends Model
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
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investments'");
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) { return false; }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CORE METRICS CALCULATOR
    // Computes all time-based metrics for a single investment row.
    // For egg/livestock types, pulls actual revenue from the DB.
    // ─────────────────────────────────────────────────────────────────────────
    public function computeMetrics(array $row): array
    {
        $amount      = (float)($row['amount']            ?? 0);
        $expected    = (float)($row['expected_return']   ?? 0);
        $lifeYears   = (int)($row['useful_life_years']   ?? 0);
        $type        = $row['investment_type']            ?? 'other';
        $date        = $row['investment_date']            ?? date('Y-m-d');
        $batchId     = !empty($row['linked_batch_id'])   ? (int)$row['linked_batch_id'] : null;

        // ── Elapsed time ──────────────────────────────────────────────────────
        $investTs     = strtotime($date);
        $nowTs        = time();
        $elapsedDays  = max(1, (int)(($nowTs - $investTs) / 86400));
        $elapsedWeeks = max(1, $elapsedDays / 7);
        $elapsedMonths= max(1, $elapsedDays / 30.44);
        $elapsedYears = max(1/365, $elapsedDays / 365.25);

        // ── Actual revenue from business data (for egg/livestock types) ───────
        $actualRevenue = $this->getActualRevenue($type, $date, $batchId);
        $isLiveType    = ($type === 'eggs' || $type === 'livestock');

        // ── Determine effective expected return ───────────────────────────────
        // For egg/livestock:
        //   - If actual revenue exists → use it (real performance)
        //   - If no revenue yet but expected_return set → use expected (forward projection)
        //   - If neither → investment is pending; don't show a loss
        $effectiveReturn = $isLiveType
            ? ($actualRevenue > 0 ? $actualRevenue : $expected)
            : $expected;

        // ── Net gain — only meaningful when there IS a return to compare ──────
        // Pending investments (no revenue, no expected) should not show -100% ROI
        $hasMeaningfulReturn = $effectiveReturn > 0;
        $netGain = $hasMeaningfulReturn ? ($effectiveReturn - $amount) : 0;
        $roiPct  = ($hasMeaningfulReturn && $amount > 0) ? ($netGain / $amount) * 100 : 0;

        // ── Depreciation (straight-line, only for physical assets) ───────────
        $depreciable = in_array($type, ['equipment','infrastructure','technology']);
        $annDeprec   = ($depreciable && $lifeYears > 0) ? $amount / $lifeYears : 0;
        $monDeprec   = $annDeprec / 12;
        $wkDeprec    = $annDeprec / 52;

        // ── Return per period ─────────────────────────────────────────────────
        if ($isLiveType) {
            if ($actualRevenue > 0) {
                // Real rate: actual revenue earned ÷ elapsed time
                $annReturn = $elapsedYears  > 0 ? $actualRevenue / $elapsedYears  : 0;
                $monReturn = $elapsedMonths > 0 ? $actualRevenue / $elapsedMonths : 0;
                $wkReturn  = $elapsedWeeks  > 0 ? $actualRevenue / $elapsedWeeks  : 0;
            } elseif ($expected > 0 && $lifeYears > 0) {
                // No sales yet — project from expected_return over useful life
                $annReturn = $expected / $lifeYears;
                $monReturn = $annReturn / 12;
                $wkReturn  = $annReturn / 52;
            } else {
                // Truly pending — no data to project from
                $annReturn = 0;
                $monReturn = 0;
                $wkReturn  = 0;
            }
        } else {
            $annReturn = ($lifeYears > 0 && $netGain > 0) ? $netGain / $lifeYears : 0;
            $monReturn = $annReturn / 12;
            $wkReturn  = $annReturn / 52;
        }

        // ── Payback period ────────────────────────────────────────────────────
        $paybackYears  = $annReturn > 0 ? $amount / $annReturn : 0;
        $paybackMonths = $monReturn > 0 ? $amount / $monReturn : 0;
        $paybackWeeks  = $wkReturn  > 0 ? $amount / $wkReturn  : 0;

        // ── Book value ────────────────────────────────────────────────────────
        $deprecToDate   = $depreciable ? min($amount, $annDeprec * $elapsedYears) : 0;
        $currentBookVal = max(0, $amount - $deprecToDate);
        $remainingLife  = $lifeYears > 0 ? max(0, $lifeYears - $elapsedYears) : 0;

        // ── Return earned to date ─────────────────────────────────────────────
        $returnToDate = $isLiveType
            ? $actualRevenue
            : ($annReturn > 0 ? min($netGain > 0 ? $netGain : 0, $annReturn * $elapsedYears) : 0);

        // ── Investor return breakdown ─────────────────────────────────────────
        // investor_share_pct: percentage of returns promised to investor
        // Stored in notes as "investor_share:30" or as a dedicated column if it exists
        $investorSharePct = 0.0;
        if (!empty($row['investor_share_pct'])) {
            $investorSharePct = (float)$row['investor_share_pct'];
        } elseif (!empty($row['notes']) && preg_match('/investor_share:(\d+(?:\.\d+)?)/', $row['notes'], $m)) {
            $investorSharePct = (float)$m[1];
        }

        $investorReturnTotal   = $investorSharePct > 0 ? ($effectiveReturn * $investorSharePct / 100) : 0;
        $investorReturnAnnual  = $investorSharePct > 0 ? ($annReturn * $investorSharePct / 100) : 0;
        $investorReturnMonthly = $investorSharePct > 0 ? ($monReturn * $investorSharePct / 100) : 0;
        $investorReturnWeekly  = $investorSharePct > 0 ? ($wkReturn  * $investorSharePct / 100) : 0;
        $investorReturnToDate  = $investorSharePct > 0 ? ($returnToDate * $investorSharePct / 100) : 0;
        $businessRetainedPct   = 100 - $investorSharePct;

        // ── Investment strategy classification ────────────────────────────────
        $strategy = $this->classifyStrategy($type, $roiPct, $paybackYears, $lifeYears, $amount, $netGain);

        return array_merge($row, [
            // Time elapsed
            'elapsed_days'           => (int)$elapsedDays,
            'elapsed_weeks'          => round($elapsedWeeks, 1),
            'elapsed_months'         => round($elapsedMonths, 1),
            'elapsed_years'          => round($elapsedYears, 2),
            // Revenue
            'actual_revenue'         => round($actualRevenue, 2),
            'effective_return'       => round($effectiveReturn, 2),
            'net_gain'               => round($netGain, 2),
            'roi_pct'                => round($roiPct, 2),
            // Periodic returns
            'weekly_return'          => round($wkReturn, 2),
            'monthly_return'         => round($monReturn, 2),
            'annual_return'          => round($annReturn, 2),
            // Depreciation
            'weekly_depreciation'    => round($wkDeprec, 4),
            'monthly_depreciation'   => round($monDeprec, 2),
            'annual_depreciation'    => round($annDeprec, 2),
            // Payback
            'payback_years'          => round($paybackYears, 2),
            'payback_months'         => round($paybackMonths, 1),
            'payback_weeks'          => round($paybackWeeks, 1),
            // Book value
            'depreciation_to_date'   => round($deprecToDate, 2),
            'current_book_value'     => round($currentBookVal, 2),
            'remaining_life_years'   => round($remainingLife, 2),
            'return_to_date'         => round($returnToDate, 2),
            // Investor breakdown
            'investor_share_pct'     => round($investorSharePct, 2),
            'investor_return_total'  => round($investorReturnTotal, 2),
            'investor_return_annual' => round($investorReturnAnnual, 2),
            'investor_return_monthly'=> round($investorReturnMonthly, 2),
            'investor_return_weekly' => round($investorReturnWeekly, 2),
            'investor_return_to_date'=> round($investorReturnToDate, 2),
            'business_retained_pct'  => round($businessRetainedPct, 2),
            // Strategy
            'strategy'               => $strategy,
            // Source label
            'return_source'          => $isLiveType && $actualRevenue > 0
                                        ? 'Actual business revenue'
                                        : ($isLiveType && $expected > 0
                                            ? 'Projected from expected return (no sales yet)'
                                            : ($isLiveType
                                                ? 'Pending — no sales or expected return set'
                                                : 'Projected from expected return')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTO-FETCH ACTUAL REVENUE FROM BUSINESS DATA
    // ─────────────────────────────────────────────────────────────────────────
    private function getActualRevenue(string $type, string $sinceDate, ?int $batchId): float
    {
        try {
            if ($type === 'eggs') {
                // Revenue from egg sales since investment date
                if ($batchId) {
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(total_amount),0)
                        FROM sales
                        WHERE sale_type='eggs' AND batch_id=? AND sale_date >= ?
                    ");
                    $stmt->execute([$batchId, $sinceDate]);
                } else {
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(total_amount),0)
                        FROM sales
                        WHERE sale_type='eggs' AND sale_date >= ?
                    ");
                    $stmt->execute([$sinceDate]);
                }
                return (float)$stmt->fetchColumn();
            }

            if ($type === 'livestock') {
                // Revenue from live bird / broiler sales since investment date
                if ($batchId) {
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(total_amount),0)
                        FROM sales
                        WHERE sale_type IN ('broiler','live_bird','livestock','meat','other')
                          AND batch_id=? AND sale_date >= ?
                    ");
                    $stmt->execute([$batchId, $sinceDate]);
                } else {
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(total_amount),0)
                        FROM sales
                        WHERE sale_type IN ('broiler','live_bird','livestock','meat','other')
                          AND sale_date >= ?
                    ");
                    $stmt->execute([$sinceDate]);
                }
                return (float)$stmt->fetchColumn();
            }
        } catch (Throwable $e) {}

        return 0.0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function all(): array
    {
        if (!$this->tableExists) return [];
        try {
            $rows = $this->db->query("
                SELECT i.*, f.farm_name
                FROM investments i
                LEFT JOIN farms f ON f.id = i.farm_id
                ORDER BY i.investment_date DESC, i.id DESC
            ")->fetchAll() ?: [];
            return array_map([$this, 'computeMetrics'], $rows);
        } catch (Throwable $e) { return []; }
    }

    public function find(int $id): ?array
    {
        if (!$this->tableExists) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM investments WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? $this->computeMetrics($row) : null;
        } catch (Throwable $e) { return null; }
    }

    public function create(array $data): bool
    {
        if (!$this->tableExists) return false;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO investments
                    (farm_id, investment_date, investment_type, title, description,
                     amount, expected_return, useful_life_years, status, reference_no, notes)
                VALUES
                    (:farm_id, :investment_date, :investment_type, :title, :description,
                     :amount, :expected_return, :useful_life_years, :status, :reference_no, :notes)
            ");
            return $stmt->execute([
                ':farm_id'           => (int)$data['farm_id'],
                ':investment_date'   => $data['investment_date'],
                ':investment_type'   => $data['investment_type'],
                ':title'             => trim($data['title']),
                ':description'       => $data['description'] ?? null,
                ':amount'            => (float)$data['amount'],
                ':expected_return'   => !empty($data['expected_return']) ? (float)$data['expected_return'] : null,
                ':useful_life_years' => !empty($data['useful_life_years']) ? (int)$data['useful_life_years'] : null,
                ':status'            => $data['status'] ?? 'active',
                ':reference_no'      => $data['reference_no'] ?? null,
                ':notes'             => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->tableExists) return false;
        try {
            $stmt = $this->db->prepare("
                UPDATE investments SET
                    farm_id=:farm_id, investment_date=:investment_date,
                    investment_type=:investment_type, title=:title,
                    description=:description, amount=:amount,
                    expected_return=:expected_return, useful_life_years=:useful_life_years,
                    status=:status, reference_no=:reference_no, notes=:notes
                WHERE id=:id
            ");
            return $stmt->execute([
                ':id'                => $id,
                ':farm_id'           => (int)$data['farm_id'],
                ':investment_date'   => $data['investment_date'],
                ':investment_type'   => $data['investment_type'],
                ':title'             => trim($data['title']),
                ':description'       => $data['description'] ?? null,
                ':amount'            => (float)$data['amount'],
                ':expected_return'   => !empty($data['expected_return']) ? (float)$data['expected_return'] : null,
                ':useful_life_years' => !empty($data['useful_life_years']) ? (int)$data['useful_life_years'] : null,
                ':status'            => $data['status'] ?? 'active',
                ':reference_no'      => $data['reference_no'] ?? null,
                ':notes'             => $data['notes'] ?? null,
            ]);
        } catch (Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        if (!$this->tableExists) return false;
        try {
            return $this->db->prepare("DELETE FROM investments WHERE id=?")->execute([$id]);
        } catch (Throwable $e) { return false; }
    }

    public function totals(): array
    {
        $defaults = [
            'total_records'=>0,'total_invested'=>0,'active_investment'=>0,
            'total_expected_return'=>0,'total_actual_revenue'=>0,'total_net_gain'=>0,
            'annual_depreciation'=>0,'monthly_depreciation'=>0,'weekly_depreciation'=>0,
            'annual_return'=>0,'monthly_return'=>0,'weekly_return'=>0,
            'roi_pct'=>0,'total_book_value'=>0,'pending_count'=>0,
        ];
        if (!$this->tableExists) return $defaults;
        try {
            $rows = $this->all();
            if (empty($rows)) return $defaults;

            $totalInvested  = array_sum(array_column($rows, 'amount'));
            $activeInvested = array_sum(array_map(fn($r) => $r['status']==='active' ? $r['amount'] : 0, $rows));
            $totalExpected  = array_sum(array_column($rows, 'expected_return'));
            $totalActual    = array_sum(array_column($rows, 'actual_revenue'));
            $totalNetGain   = array_sum(array_column($rows, 'net_gain'));
            $annDeprec      = array_sum(array_column($rows, 'annual_depreciation'));
            $annReturn      = array_sum(array_column($rows, 'annual_return'));
            $bookValue      = array_sum(array_column($rows, 'current_book_value'));

            // Only count investments that have meaningful returns for ROI
            $roiBase = array_sum(array_map(
                fn($r) => ($r['net_gain'] != 0 || $r['actual_revenue'] > 0 || ($r['expected_return'] ?? 0) > 0) ? $r['amount'] : 0,
                $rows
            ));
            $pendingCount = count(array_filter($rows, fn($r) =>
                $r['actual_revenue'] <= 0 && ($r['expected_return'] ?? 0) <= 0
                && in_array($r['investment_type'], ['eggs','livestock'])
            ));

            return [
                'total_records'         => count($rows),
                'total_invested'        => $totalInvested,
                'active_investment'     => $activeInvested,
                'total_expected_return' => $totalExpected,
                'total_actual_revenue'  => $totalActual,
                'total_net_gain'        => $totalNetGain,
                'annual_depreciation'   => round($annDeprec, 2),
                'monthly_depreciation'  => round($annDeprec / 12, 2),
                'weekly_depreciation'   => round($annDeprec / 52, 4),
                'annual_return'         => round($annReturn, 2),
                'monthly_return'        => round($annReturn / 12, 2),
                'weekly_return'         => round($annReturn / 52, 4),
                'roi_pct'               => $roiBase > 0 ? round(($totalNetGain / $roiBase) * 100, 2) : 0,
                'total_book_value'      => round($bookValue, 2),
                'pending_count'         => $pendingCount,
            ];
        } catch (Throwable $e) { return $defaults; }
    }

    public function byType(): array
    {
        if (!$this->tableExists) return [];
        try {
            $rows = $this->all();
            $grouped = [];
            foreach ($rows as $r) {
                $type = $r['investment_type'] ?? 'other';
                if (!isset($grouped[$type])) {
                    $grouped[$type] = [
                        'investment_type' => $type,
                        'records'         => 0,
                        'total'           => 0,
                        'expected_return' => 0,
                        'actual_revenue'  => 0,
                        'net_gain'        => 0,
                        'annual_return'   => 0,
                        'monthly_return'  => 0,
                        'weekly_return'   => 0,
                        'roi_pct'         => 0,
                        'pending_count'   => 0,
                    ];
                }
                $grouped[$type]['records']++;
                $grouped[$type]['total']           += $r['amount'];
                $grouped[$type]['expected_return'] += (float)($r['expected_return'] ?? 0);
                $grouped[$type]['actual_revenue']  += (float)($r['actual_revenue'] ?? 0);
                $grouped[$type]['net_gain']        += (float)($r['net_gain'] ?? 0);
                $grouped[$type]['annual_return']   += (float)($r['annual_return'] ?? 0);
                $grouped[$type]['monthly_return']  += (float)($r['monthly_return'] ?? 0);
                $grouped[$type]['weekly_return']   += (float)($r['weekly_return'] ?? 0);

                // Count pending (live types with no revenue and no expected return)
                $isLive = in_array($r['investment_type'], ['eggs','livestock']);
                if ($isLive && (float)($r['actual_revenue']??0) <= 0 && (float)($r['expected_return']??0) <= 0) {
                    $grouped[$type]['pending_count']++;
                }
            }

            foreach ($grouped as &$g) {
                // Only compute ROI when there's a meaningful return to compare
                $hasMeaningful = $g['actual_revenue'] > 0 || $g['expected_return'] > 0;
                if ($hasMeaningful && $g['total'] > 0) {
                    $effectiveReturn = $g['actual_revenue'] > 0 ? $g['actual_revenue'] : $g['expected_return'];
                    $g['roi_pct'] = round((($effectiveReturn - $g['total']) / $g['total']) * 100, 2);
                } else {
                    $g['roi_pct'] = null; // null = pending, not a loss
                }
            }

            usort($grouped, fn($a,$b) => $b['total'] <=> $a['total']);
            return array_values($grouped);
        } catch (Throwable $e) { return []; }
    }

    public function isReady(): bool { return $this->tableExists; }

    // ─────────────────────────────────────────────────────────────────────────
    // BUSINESS PERFORMANCE INTELLIGENCE
    // Analyses actual business data to auto-suggest expected return,
    // investor share %, and payback period for a given investment type + amount.
    // Called via AJAX from the create/edit form.
    // ─────────────────────────────────────────────────────────────────────────
    public function businessPerformance(string $type, float $amount, int $lifeYears = 3): array
    {
        // ── Revenue performance by type ───────────────────────────────────────
        $avgMonthlyRevenue = 0.0;
        $avgMonthlyProfit  = 0.0;
        $dataMonths        = 0;
        $revenueSource     = '';

        if ($type === 'eggs') {
            // Average monthly egg sales over last 6 months
            $row = $this->safeRow("
                SELECT COUNT(DISTINCT DATE_FORMAT(sale_date,'%Y-%m')) AS months,
                       COALESCE(SUM(total_amount),0) AS total_rev
                FROM sales
                WHERE sale_type='eggs'
                  AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ");
            $dataMonths        = max(1, (int)($row['months'] ?? 1));
            $avgMonthlyRevenue = (float)($row['total_rev'] ?? 0) / $dataMonths;
            $revenueSource     = 'Egg sales (last 6 months)';

        } elseif ($type === 'livestock') {
            $row = $this->safeRow("
                SELECT COUNT(DISTINCT DATE_FORMAT(sale_date,'%Y-%m')) AS months,
                       COALESCE(SUM(total_amount),0) AS total_rev
                FROM sales
                WHERE sale_type IN ('broiler','live_bird','livestock','meat','other')
                  AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ");
            $dataMonths        = max(1, (int)($row['months'] ?? 1));
            $avgMonthlyRevenue = (float)($row['total_rev'] ?? 0) / $dataMonths;
            $revenueSource     = 'Livestock sales (last 6 months)';

        } else {
            // For equipment/infrastructure/technology: use overall business net margin
            $revRow = $this->safeRow("
                SELECT COALESCE(SUM(total_amount),0) AS rev,
                       COUNT(DISTINCT DATE_FORMAT(sale_date,'%Y-%m')) AS months
                FROM sales
                WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ");
            $expRow = $this->safeRow("
                SELECT COALESCE(SUM(amount),0) AS exp
                FROM expenses
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ");
            $dataMonths        = max(1, (int)($revRow['months'] ?? 1));
            $avgMonthlyRevenue = (float)($revRow['rev'] ?? 0) / $dataMonths;
            $avgMonthlyExpenses= (float)($expRow['exp'] ?? 0) / $dataMonths;
            $avgMonthlyProfit  = $avgMonthlyRevenue - $avgMonthlyExpenses;
            $revenueSource     = 'Overall business performance (last 6 months)';
        }

        // ── Compute suggested expected return ─────────────────────────────────
        // Logic per type:
        // eggs/livestock: project monthly revenue × life months
        // equipment/infra/tech: project efficiency gain = 15-25% of amount per year
        // land: 8-12% appreciation per year
        // other: conservative 10% per year

        $annualReturnRate  = 0.0; // % of amount per year
        $suggestedReturn   = 0.0;
        $returnBasis       = '';

        if (in_array($type, ['eggs','livestock'])) {
            $annualRevenue    = $avgMonthlyRevenue * 12;
            $suggestedReturn  = $annualRevenue * $lifeYears;
            $annualReturnRate = $amount > 0 ? ($annualRevenue / $amount) * 100 : 0;
            $returnBasis      = 'Based on avg monthly ' . $type . ' revenue × ' . $lifeYears . ' years';
        } elseif ($type === 'equipment') {
            // Equipment reduces costs — assume 20% annual return on investment
            $annualReturnRate = 20.0;
            $suggestedReturn  = $amount * (1 + ($annualReturnRate / 100) * $lifeYears);
            $returnBasis      = '20% annual efficiency gain (industry standard for poultry equipment)';
        } elseif ($type === 'infrastructure') {
            $annualReturnRate = 15.0;
            $suggestedReturn  = $amount * (1 + ($annualReturnRate / 100) * $lifeYears);
            $returnBasis      = '15% annual return (infrastructure reduces per-unit operating cost)';
        } elseif ($type === 'technology') {
            $annualReturnRate = 18.0;
            $suggestedReturn  = $amount * (1 + ($annualReturnRate / 100) * $lifeYears);
            $returnBasis      = '18% annual return (technology improves productivity)';
        } elseif ($type === 'land') {
            $annualReturnRate = 10.0;
            $suggestedReturn  = $amount * (1 + ($annualReturnRate / 100) * $lifeYears);
            $returnBasis      = '10% annual appreciation (conservative land value growth)';
        } else {
            $annualReturnRate = 12.0;
            $suggestedReturn  = $amount * (1 + ($annualReturnRate / 100) * $lifeYears);
            $returnBasis      = '12% annual return (conservative general investment)';
        }

        // ── Suggested investor share % ────────────────────────────────────────
        // Principle: investor share = risk-adjusted fair split
        // Higher risk type → investor gets more upside to compensate
        // Business keeps majority (min 51%) to maintain control
        $riskShareMap = [
            'eggs'           => 30, // steady income, lower risk → 30% to investor
            'livestock'      => 35, // higher volatility → 35%
            'equipment'      => 25, // low risk, long payback → 25%
            'infrastructure' => 20, // very low risk → 20%
            'land'           => 20, // very low risk → 20%
            'technology'     => 28, // medium risk → 28%
            'other'          => 30, // default
        ];
        $suggestedInvestorShare = $riskShareMap[$type] ?? 30;

        // Adjust based on business health: if business is profitable, offer less
        // If struggling, offer more to attract capital
        $overallMargin = 0.0;
        $marginRow = $this->safeRow("
            SELECT COALESCE(SUM(total_amount),0) AS rev FROM sales
            WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        ");
        $expMarginRow = $this->safeRow("
            SELECT COALESCE(SUM(amount),0) AS exp FROM expenses
            WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        ");
        $rev3m = (float)($marginRow['rev'] ?? 0);
        $exp3m = (float)($expMarginRow['exp'] ?? 0);
        if ($rev3m > 0) {
            $overallMargin = (($rev3m - $exp3m) / $rev3m) * 100;
            if ($overallMargin >= 30) {
                $suggestedInvestorShare = max(15, $suggestedInvestorShare - 5); // profitable → offer less
            } elseif ($overallMargin < 10) {
                $suggestedInvestorShare = min(49, $suggestedInvestorShare + 5); // struggling → offer more
            }
        }

        // ── Payback period ────────────────────────────────────────────────────
        $annualReturn   = $amount > 0 && $suggestedReturn > 0
            ? ($suggestedReturn - $amount) / max(1, $lifeYears)
            : 0;
        $paybackYears   = $annualReturn > 0 ? $amount / $annualReturn : 0;
        $paybackMonths  = $paybackYears * 12;

        // ── Investor return amounts ───────────────────────────────────────────
        $investorTotalReturn   = $suggestedReturn * $suggestedInvestorShare / 100;
        $investorAnnualReturn  = $investorTotalReturn / max(1, $lifeYears);
        $investorMonthlyReturn = $investorAnnualReturn / 12;
        $investorWeeklyReturn  = $investorAnnualReturn / 52;
        $businessRetainedPct   = 100 - $suggestedInvestorShare;
        $businessTotalReturn   = $suggestedReturn * $businessRetainedPct / 100;

        return [
            'type'                    => $type,
            'amount'                  => $amount,
            'life_years'              => $lifeYears,
            // Business performance context
            'avg_monthly_revenue'     => round($avgMonthlyRevenue, 2),
            'avg_monthly_profit'      => round($avgMonthlyProfit, 2),
            'overall_margin_pct'      => round($overallMargin, 1),
            'revenue_source'          => $revenueSource,
            'data_months'             => $dataMonths,
            // Suggested return
            'suggested_return'        => round($suggestedReturn, 2),
            'annual_return_rate_pct'  => round($annualReturnRate, 1),
            'return_basis'            => $returnBasis,
            'annual_return'           => round($annualReturn, 2),
            'monthly_return'          => round($annualReturn / 12, 2),
            'weekly_return'           => round($annualReturn / 52, 2),
            // Payback
            'payback_years'           => round($paybackYears, 1),
            'payback_months'          => round($paybackMonths, 0),
            // Investor split
            'suggested_investor_share'=> $suggestedInvestorShare,
            'business_retained_pct'   => $businessRetainedPct,
            'investor_total_return'   => round($investorTotalReturn, 2),
            'investor_annual_return'  => round($investorAnnualReturn, 2),
            'investor_monthly_return' => round($investorMonthlyReturn, 2),
            'investor_weekly_return'  => round($investorWeeklyReturn, 2),
            'business_total_return'   => round($businessTotalReturn, 2),
            // Net gain
            'net_gain'                => round($suggestedReturn - $amount, 2),
            'roi_pct'                 => $amount > 0 ? round((($suggestedReturn - $amount) / $amount) * 100, 1) : 0,
        ];
    }

    private function safeRow(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) { return []; }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STRATEGY CLASSIFIER
    // ─────────────────────────────────────────────────────────────────────────
    private function classifyStrategy(string $type, float $roi, float $paybackYears, int $lifeYears, float $amount, float $netGain): array
    {
        // Principle: classify by risk, return type, and time horizon
        $principles = [
            'eggs'           => ['name' => 'Income Investment',    'icon' => '🥚', 'color' => 'warning',  'horizon' => 'Short-term recurring', 'risk' => 'Medium'],
            'livestock'      => ['name' => 'Growth Investment',    'icon' => '🐔', 'color' => 'success',  'horizon' => 'Short-to-medium term', 'risk' => 'Medium-High'],
            'equipment'      => ['name' => 'Capital Investment',   'icon' => '🔧', 'color' => 'primary',  'horizon' => 'Long-term',            'risk' => 'Low'],
            'infrastructure' => ['name' => 'Capital Investment',   'icon' => '🏗️', 'color' => 'info',     'horizon' => 'Long-term',            'risk' => 'Low'],
            'land'           => ['name' => 'Appreciation Asset',   'icon' => '🌍', 'color' => 'success',  'horizon' => 'Very long-term',       'risk' => 'Very Low'],
            'technology'     => ['name' => 'Efficiency Investment', 'icon' => '💻', 'color' => 'secondary','horizon' => 'Medium-term',          'risk' => 'Medium'],
            'other'          => ['name' => 'General Investment',   'icon' => '📦', 'color' => 'dark',     'horizon' => 'Variable',             'risk' => 'Variable'],
        ];

        $base = $principles[$type] ?? $principles['other'];

        // ROI rating
        $roiRating = $roi >= 50 ? 'Excellent' : ($roi >= 25 ? 'Good' : ($roi >= 10 ? 'Fair' : ($roi >= 0 ? 'Break-even' : 'Loss')));
        $roiClass  = $roi >= 25 ? 'success' : ($roi >= 10 ? 'warning' : ($roi >= 0 ? 'secondary' : 'danger'));

        // Payback rating
        $paybackRating = $paybackYears <= 1 ? 'Very Fast' : ($paybackYears <= 2 ? 'Fast' : ($paybackYears <= 5 ? 'Moderate' : 'Slow'));

        // Recommendation
        $recommendation = '';
        if ($type === 'eggs') {
            $recommendation = 'Maximize flock health and egg collection frequency. Each additional egg directly increases ROI.';
        } elseif ($type === 'livestock') {
            $recommendation = 'Sell at optimal weight (2kg+). Holding beyond peak weight increases feed cost without proportional revenue gain.';
        } elseif ($type === 'equipment') {
            $recommendation = 'Maintain equipment to extend useful life beyond depreciation schedule. Reduces replacement cost.';
        } elseif ($type === 'infrastructure') {
            $recommendation = 'Infrastructure investments reduce per-unit operating costs over time. Maximize utilization rate.';
        } elseif ($type === 'land') {
            $recommendation = 'Land appreciates over time. Consider productive use (housing, expansion) to generate income while holding.';
        } elseif ($type === 'technology') {
            $recommendation = 'Technology investments should reduce labor or feed costs. Track efficiency gains to measure true ROI.';
        } else {
            $recommendation = 'Monitor returns regularly and compare against expected return to assess performance.';
        }

        return [
            'principle'      => $base['name'],
            'icon'           => $base['icon'],
            'color'          => $base['color'],
            'horizon'        => $base['horizon'],
            'risk'           => $base['risk'],
            'roi_rating'     => $roiRating,
            'roi_class'      => $roiClass,
            'payback_rating' => $paybackRating,
            'recommendation' => $recommendation,
        ];
    }
}

<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * FinancialMonitor — Proper Accounting Engine
 *
 * ACCOUNTING EQUATION:  Assets = Liabilities + Capital (Owner's Equity)
 *
 * CATEGORIES:
 *  CAPITAL   — Source of funds: owner equity, retained earnings, grants (NOT a liability)
 *              Loan capital is separately tracked as a liability
 *  REVENUE   — Income from sales (increases equity)
 *  EXPENSES  — Costs that reduce profit (feed, medication, vaccination, direct)
 *  ASSETS    — What the business OWNS: inventory, live birds, receivables, investments
 *  LIABILITIES — What the business OWES: unpaid expenses, loan capital
 *  INVESTMENTS — Long-term assets deployed for future return (subset of assets)
 *
 * NET WORTH (Owner's Equity) = Capital + Retained Profit - Loan Capital
 * RETAINED PROFIT = Total Revenue - Total Expenses
 * WORKING CAPITAL = Current Assets - Current Liabilities
 */
class FinancialMonitor extends Model
{
    // ─────────────────────────────────────────────
    // FULL CLASSIFIED PICTURE
    // ─────────────────────────────────────────────
    public function classify(): array
    {
        return [
            'capital'     => $this->buildCapital(),
            'revenue'     => $this->buildRevenue(),
            'expenses'    => $this->buildExpenses(),
            'assets'      => $this->buildAssets(),
            'liabilities' => $this->buildLiabilities(),
            'investments' => $this->buildInvestments(),
        ];
    }

    // ─────────────────────────────────────────────
    // MASTER TOTALS — used by dashboard KPIs
    // ─────────────────────────────────────────────
    public function totals(): array
    {
        $cap  = $this->buildCapital();
        $rev  = $this->buildRevenue();
        $exp  = $this->buildExpenses();
        $ast  = $this->buildAssets();
        $lib  = $this->buildLiabilities();
        $inv  = $this->buildInvestments();

        $totalCapital     = array_sum(array_column($cap, 'amount'));
        $totalRevenue     = array_sum(array_column($rev, 'amount'));
        $totalExpenses    = array_sum(array_column($exp, 'amount'));
        $totalAssets      = array_sum(array_column($ast, 'amount'));
        $totalLiabilities = array_sum(array_column($lib, 'amount'));
        $totalInvestments = array_sum(array_column($inv, 'amount'));

        // Accounting metrics
        $retainedProfit   = $totalRevenue - $totalExpenses;
        $ownerEquity      = $totalCapital + $retainedProfit;          // Capital + Profit
        $netWorth         = $ownerEquity - $totalLiabilities;         // Equity minus debt
        $workingCapital   = $totalAssets - $totalLiabilities;
        $profitMargin     = $totalRevenue > 0 ? ($retainedProfit / $totalRevenue) * 100 : 0;
        $debtRatio        = $totalAssets > 0 ? ($totalLiabilities / $totalAssets) * 100 : 0;
        $capitalUtilisation = $totalCapital > 0 ? ($totalAssets / $totalCapital) * 100 : 0;
        $roi              = $totalCapital > 0 ? ($retainedProfit / $totalCapital) * 100 : 0;
        $investmentCoverage = $totalCapital > 0 ? ($totalInvestments / $totalCapital) * 100 : 0;

        // Accounting equation check: Assets should ≈ Liabilities + Capital + Retained Profit
        $equationBalance  = $totalAssets - ($totalLiabilities + $ownerEquity);

        return [
            // Raw totals
            'total_capital'          => $totalCapital,
            'total_revenue'          => $totalRevenue,
            'total_expenses'         => $totalExpenses,
            'total_assets'           => $totalAssets,
            'total_liabilities'      => $totalLiabilities,
            'total_investments'      => $totalInvestments,

            // Derived accounting metrics
            'retained_profit'        => $retainedProfit,
            'owner_equity'           => $ownerEquity,
            'net_worth'              => $netWorth,
            'working_capital'        => $workingCapital,
            'profit_margin'          => $profitMargin,
            'debt_ratio'             => $debtRatio,
            'capital_utilisation'    => $capitalUtilisation,
            'roi'                    => $roi,
            'investment_coverage'    => $investmentCoverage,
            'equation_balance'       => $equationBalance,

            // Groups for display
            'capital_groups'         => $cap,
            'revenue_groups'         => $rev,
            'expense_groups'         => $exp,
            'asset_groups'           => $ast,
            'liability_groups'       => $lib,
            'investment_groups'      => $inv,
        ];
    }

    // ─────────────────────────────────────────────
    // CURRENT MONTH TOTALS
    // ─────────────────────────────────────────────
    public function currentMonthTotals(): array
    {
        $y = date('Y');
        $m = date('n');

        $revenue = $this->safe("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE YEAR(sale_date)=? AND MONTH(sale_date)=?", [$y,$m]);
        $feedExp = $this->safe("SELECT COALESCE(SUM(quantity_kg*unit_cost),0) FROM feed_records WHERE YEAR(record_date)=? AND MONTH(record_date)=?", [$y,$m]);
        $medExp  = $this->safe("SELECT COALESCE(SUM(quantity_used*unit_cost),0) FROM medication_records WHERE YEAR(record_date)=? AND MONTH(record_date)=?", [$y,$m]);
        $vacExp  = $this->safe("SELECT COALESCE(SUM(cost_amount),0) FROM vaccination_records WHERE YEAR(record_date)=? AND MONTH(record_date)=?", [$y,$m]);
        $dirExp  = $this->safe("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE YEAR(expense_date)=? AND MONTH(expense_date)=?", [$y,$m]);
        $capIn   = $this->safe("SELECT COALESCE(SUM(amount),0) FROM capital_entries WHERE YEAR(entry_date)=? AND MONTH(entry_date)=?", [$y,$m]);

        $totalExp = (float)$feedExp + (float)$medExp + (float)$vacExp + (float)$dirExp;

        return [
            'revenue'             => (float)$revenue,
            'feed_expense'        => (float)$feedExp,
            'medication_expense'  => (float)$medExp,
            'vaccination_expense' => (float)$vacExp,
            'direct_expense'      => (float)$dirExp,
            'total_expense'       => $totalExp,
            'net'                 => (float)$revenue - $totalExp,
            'capital_injected'    => (float)$capIn,
        ];
    }

    // ─────────────────────────────────────────────
    // CAPITAL BUILDER
    // Capital = source of funds (equity, grants, retained)
    // Loan capital is tracked separately as liability
    // ─────────────────────────────────────────────
    private function buildCapital(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        // Capital from capital_entries - grouped by capital_type
        $rows = $this->fetchSafe("
            SELECT COALESCE(capital_type, entry_type, 'owner_equity') AS cap_type,
                   COUNT(*) AS records,
                   COALESCE(SUM(amount),0) AS amount
            FROM capital_entries
            GROUP BY COALESCE(capital_type, entry_type, 'owner_equity')
            ORDER BY amount DESC
        ");

        $labels = [
            'owner_equity'      => 'Owner Equity',
            'reinvestment'      => 'Reinvestment',
            'retained_earnings' => 'Retained Earnings',
            'loan_capital'      => 'Loan Capital',
            'grant'             => 'Grant',
            'other'             => 'Other Capital',
            'contribution'      => 'Capital Contributions',
            'withdrawal'        => 'Capital Withdrawals',
        ];

        foreach ($rows as $r) {
            $groups[] = [
                'category'    => 'Capital',
                'source'      => 'capital_entries',
                'label'       => $labels[$r['cap_type']] ?? ucfirst($r['cap_type']),
                'records'     => (int)$r['records'],
                'amount'      => (float)$r['amount'],
                'note'        => 'Equity — not a liability',
                'link'        => $base.'/capital',
                'create_link' => $base.'/capital/create',
            ];
        }

        if (empty($groups)) {
            $groups[] = [
                'category' => 'Capital', 'source' => 'capital_entries',
                'label' => 'No Capital Recorded', 'records' => 0, 'amount' => 0,
                'note' => 'Add owner equity or grants to track capital',
                'link' => $base.'/capital', 'create_link' => $base.'/capital/create',
            ];
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // REVENUE BUILDER
    // ─────────────────────────────────────────────
    private function buildRevenue(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        $rows = $this->fetchSafe("
            SELECT COALESCE(sale_type,'other') AS label,
                   COUNT(*) AS records,
                   COALESCE(SUM(total_amount),0) AS amount,
                   COALESCE(SUM(amount_paid),0) AS collected,
                   COALESCE(SUM(total_amount-amount_paid),0) AS outstanding
            FROM sales
            GROUP BY COALESCE(sale_type,'other')
            ORDER BY amount DESC
        ");

        foreach ($rows as $r) {
            $groups[] = [
                'category'    => 'Revenue',
                'source'      => 'sales',
                'label'       => ucfirst($r['label']) . ' Sales',
                'records'     => (int)$r['records'],
                'amount'      => (float)$r['amount'],
                'collected'   => (float)$r['collected'],
                'outstanding' => (float)$r['outstanding'],
                'note'        => 'Increases owner equity',
                'link'        => $base.'/sales',
                'create_link' => $base.'/sales/create',
            ];
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // EXPENSES BUILDER
    // Includes: feed, medication, vaccination, direct expenses,
    // AND livestock purchase cost (cash paid for chicks = expense)
    // ─────────────────────────────────────────────
    private function buildExpenses(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        // ── Livestock purchase cost (cash paid for chicks) ────────────────────
        // The MONEY SPENT to buy chicks is an expense (cost of production).
        // The BIRDS THEMSELVES are a separate biological asset (see buildAssets).
        // This dual treatment is correct accounting: debit Expense, debit Asset.
        $birdPurchaseCost = (float)$this->safe("
            SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0)
            FROM animal_batches
            WHERE initial_unit_cost > 0
        ");
        $birdBatchCount = (int)$this->safe("SELECT COUNT(*) FROM animal_batches WHERE initial_unit_cost > 0");
        if ($birdPurchaseCost > 0) {
            $groups[] = $this->expGroup(
                'Livestock Purchase Cost (Chicks)',
                'animal_batches',
                $birdPurchaseCost,
                $birdBatchCount,
                'Cash paid to buy chicks — production expense. Birds are separately tracked as biological assets.',
                $base.'/batches',
                $base.'/batches/create'
            );
        }

        // ── Mortality loss (asset write-off as expense) ───────────────────────
        // When birds die, the asset value is lost — this is an expense (loss).
        $mortalityLoss = (float)$this->safe("
            SELECT COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0)
            FROM mortality_records mr
            INNER JOIN animal_batches ab ON ab.id = mr.batch_id
        ");
        $mortalityCount = (int)$this->safe("SELECT COUNT(*) FROM mortality_records");
        if ($mortalityLoss > 0) {
            $groups[] = $this->expGroup(
                'Mortality Loss (Asset Write-off)',
                'mortality_records',
                $mortalityLoss,
                $mortalityCount,
                'Value of birds lost to mortality — reduces biological asset and hits expenses.',
                $base.'/mortality',
                $base.'/mortality/create'
            );
        }

        $feedCost = (float)$this->safe("SELECT COALESCE(SUM(quantity_kg*unit_cost),0) FROM feed_records");
        $feedCount= (int)$this->safe("SELECT COUNT(*) FROM feed_records");
        $groups[] = $this->expGroup('Feed Costs', 'feed_records', $feedCost, $feedCount, 'Operational cost — reduces profit', $base.'/feed', $base.'/feed/create');

        $medCost  = (float)$this->safe("SELECT COALESCE(SUM(quantity_used*unit_cost),0) FROM medication_records");
        $medCount = (int)$this->safe("SELECT COUNT(*) FROM medication_records");
        $groups[] = $this->expGroup('Medication Costs', 'medication_records', $medCost, $medCount, 'Health cost — reduces profit', $base.'/medication', $base.'/medication/create');

        $vacCost  = (float)$this->safe("SELECT COALESCE(SUM(cost_amount),0) FROM vaccination_records");
        $vacCount = (int)$this->safe("SELECT COUNT(*) FROM vaccination_records");
        $groups[] = $this->expGroup('Vaccination Costs', 'vaccination_records', $vacCost, $vacCount, 'Preventive cost — reduces profit', $base.'/vaccination', $base.'/vaccination/create');

        // Direct expenses by category
        $rows = $this->fetchSafe("
            SELECT COALESCE(ec.category_name,'Uncategorized') AS label,
                   COUNT(e.id) AS records,
                   COALESCE(SUM(e.amount),0) AS amount
            FROM expenses e
            LEFT JOIN expense_categories ec ON ec.id = e.category_id
            GROUP BY COALESCE(ec.category_name,'Uncategorized')
            ORDER BY amount DESC
        ");
        foreach ($rows as $r) {
            $groups[] = $this->expGroup($r['label'], 'expenses', (float)$r['amount'], (int)$r['records'], 'Direct expense — reduces profit', $base.'/expenses', $base.'/expenses/create');
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // ASSETS BUILDER
    // Includes: inventory, biological assets (live birds at CURRENT value),
    // receivables, fixed assets/investments.
    //
    // DUAL TREATMENT FOR BATCHES:
    //   EXPENSE side = cash paid to buy chicks (in buildExpenses above)
    //   ASSET side   = current value of live birds still on hand
    //   Net effect   = asset decreases as birds die or are sold
    // ─────────────────────────────────────────────
    private function buildAssets(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        // Inventory stock
        $invVal = 0; // Inventory tracking removed from system
        $invCnt = 0;
        $groups[] = [
            'category'=>'Asset','source'=>'inventory_item',
            'label'=>'Inventory Stock','records'=>$invCnt,'amount'=>$invVal,
            'note'=>'Inventory tracking has been unified with feed/medication systems',
            'link'=>$base.'/feed','create_link'=>$base.'/feed/create',
        ];

        // ── Biological Asset: CURRENT value of live birds ─────────────────────
        // = current_quantity (surviving birds) × initial_unit_cost
        // This is LESS than the purchase cost because mortality has reduced the flock.
        // The difference (dead birds × unit cost) is already in Mortality Loss expense.
        $birdAssetVal = (float)$this->safe("
            SELECT COALESCE(SUM(current_quantity * initial_unit_cost), 0)
            FROM animal_batches
            WHERE status = 'active'
        ");
        $birdAssetCnt = (int)$this->safe("SELECT COUNT(*) FROM animal_batches WHERE status='active'");

        // Also include total birds (all statuses) for full picture
        $allBirdVal = (float)$this->safe("
            SELECT COALESCE(SUM(current_quantity * initial_unit_cost), 0)
            FROM animal_batches
        ");

        $groups[] = [
            'category'    => 'Asset',
            'source'      => 'animal_batches',
            'label'       => 'Biological Asset — Live Birds',
            'records'     => $birdAssetCnt,
            'amount'      => $birdAssetVal,
            'note'        => 'Current value of surviving birds (purchase cost × live count). Cash paid is separately expensed.',
            'link'        => $base.'/batches',
            'create_link' => $base.'/batches/create',
            'dual_note'   => 'DUAL ENTRY: Purchase cost = Expense | Live birds = Asset',
        ];

        // Accounts receivable
        $recVal = (float)$this->safe("SELECT COALESCE(SUM(total_amount-amount_paid),0) FROM sales WHERE payment_status IN ('unpaid','partial')");
        $recCnt = (int)$this->safe("SELECT COUNT(*) FROM sales WHERE payment_status IN ('unpaid','partial')");
        $groups[] = [
            'category'=>'Asset','source'=>'sales',
            'label'=>'Accounts Receivable','records'=>$recCnt,'amount'=>$recVal,
            'note'=>'Money owed to the business from unpaid sales',
            'link'=>$base.'/sales','create_link'=>$base.'/sales/create',
        ];

        // Fixed assets from investments table
        $invAssetVal = (float)$this->safe("SELECT COALESCE(SUM(amount),0) FROM investments WHERE status='active'");
        $invAssetCnt = (int)$this->safe("SELECT COUNT(*) FROM investments WHERE status='active'");
        if ($invAssetVal > 0 || $invAssetCnt > 0) {
            $groups[] = [
                'category'=>'Asset','source'=>'investments',
                'label'=>'Fixed Assets (Investments)','records'=>$invAssetCnt,'amount'=>$invAssetVal,
                'note'=>'Long-term assets — equipment, land, infrastructure',
                'link'=>$base.'/investments','create_link'=>$base.'/investments/create',
            ];
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // LIABILITIES BUILDER
    // Liabilities = what the business OWES
    // Real-time calculation from database
    // ─────────────────────────────────────────────
    private function buildLiabilities(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        // Liabilities from liabilities table (real-time outstanding calculation)
        // Outstanding = principal_amount - SUM(payments)
        $libRows = $this->fetchSafe("
            SELECT 
                l.liability_type,
                COUNT(DISTINCT l.id) AS records,
                COALESCE(SUM(l.principal_amount), 0) AS principal,
                COALESCE(SUM(l.principal_amount - COALESCE(payments.total_paid, 0)), 0) AS outstanding
            FROM liabilities l
            LEFT JOIN (
                SELECT liability_id, SUM(amount_paid) AS total_paid
                FROM liability_payments
                GROUP BY liability_id
            ) payments ON payments.liability_id = l.id
            WHERE l.status = 'active'
            GROUP BY l.liability_type
            ORDER BY outstanding DESC
        ");

        $typeLabels = [
            'loan'             => 'Loans',
            'mortgage'         => 'Mortgages',
            'credit'           => 'Credit Lines',
            'accounts_payable' => 'Accounts Payable',
            'other'            => 'Other Liabilities',
        ];

        foreach ($libRows as $r) {
            $groups[] = [
                'category'    => 'Liability',
                'source'      => 'liabilities',
                'label'       => $typeLabels[$r['liability_type']] ?? ucfirst($r['liability_type']),
                'records'     => (int)$r['records'],
                'amount'      => (float)$r['outstanding'], // Real-time outstanding
                'principal'   => (float)$r['principal'],
                'note'        => 'Outstanding balance (principal - payments)',
                'link'        => $base.'/liabilities',
                'create_link' => $base.'/liabilities/create',
            ];
        }

        // Unpaid expenses (automatically linked to liabilities)
        $unpaidExpenses = (float)$this->safe("
            SELECT COALESCE(SUM(amount - amount_paid), 0)
            FROM expenses
            WHERE payment_status IN ('unpaid', 'partial')
        ");
        $unpaidCount = (int)$this->safe("
            SELECT COUNT(*)
            FROM expenses
            WHERE payment_status IN ('unpaid', 'partial')
        ");

        if ($unpaidExpenses > 0) {
            $groups[] = [
                'category'    => 'Liability',
                'source'      => 'expenses',
                'label'       => 'Unpaid Expenses',
                'records'     => $unpaidCount,
                'amount'      => $unpaidExpenses,
                'note'        => 'Outstanding expense obligations',
                'link'        => $base.'/expenses',
                'create_link' => $base.'/expenses/create',
            ];
        }

        if (empty($groups)) {
            $groups[] = [
                'category' => 'Liability', 'source' => 'liabilities',
                'label' => 'No Liabilities', 'records' => 0, 'amount' => 0,
                'note' => 'No outstanding obligations',
                'link' => $base.'/liabilities', 'create_link' => $base.'/liabilities/create',
            ];
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // INVESTMENTS BUILDER
    // Investments = capital deployed for future return
    // These are ASSETS but tracked separately for ROI analysis
    // ─────────────────────────────────────────────
    private function buildInvestments(): array
    {
        $groups = [];
        $base   = rtrim(BASE_URL, '/');

        $rows = $this->fetchSafe("
            SELECT 
                   COUNT(*) AS records,
                   COALESCE(SUM(amount),0) AS amount
            FROM investments
            WHERE status='active'
        ");

        foreach ($rows as $r) {
            if ((int)$r['records'] > 0) {
                $groups[] = [
                    'category'           => 'Investment',
                    'source'             => 'investments',
                    'label'              => 'Active Investments',
                    'records'            => (int)$r['records'],
                    'amount'             => (float)$r['amount'],
                    'note'               => 'Capital deployed — funded by owner equity or loans',
                    'link'               => $base.'/investments',
                    'create_link'        => $base.'/investments/create',
                ];
            }
        }

        return $groups;
    }

    // ─────────────────────────────────────────────
    // BUSINESS ANALYSIS using Capital as baseline
    // ─────────────────────────────────────────────
    public function businessAnalysis(): array
    {
        $t = $this->totals();

        $capital     = $t['total_capital'];
        $revenue     = $t['total_revenue'];
        $expenses    = $t['total_expenses'];
        $assets      = $t['total_assets'];
        $liabilities = $t['total_liabilities'];
        $investments = $t['total_investments'];
        $profit      = $t['retained_profit'];
        $equity      = $t['owner_equity'];
        $netWorth    = $t['net_worth'];

        // Capital efficiency: how much revenue generated per GHS of capital
        $capitalEfficiency = $capital > 0 ? $revenue / $capital : 0;

        // Asset coverage: assets vs liabilities (solvency)
        $assetCoverage = $liabilities > 0 ? $assets / $liabilities : ($assets > 0 ? 999 : 0);

        // Investment ratio: what % of capital is deployed in investments
        $investmentRatio = $capital > 0 ? ($investments / $capital) * 100 : 0;

        // Expense ratio: expenses as % of revenue
        $expenseRatio = $revenue > 0 ? ($expenses / $revenue) * 100 : 0;

        // Capital adequacy: equity vs liabilities
        $capitalAdequacy = $liabilities > 0 ? ($equity / $liabilities) * 100 : 100;

        // Business stage determination based on capital and profit
        $stage = 'Startup';
        if ($capital > 0 && $profit > 0 && $assetCoverage >= 1.5) {
            $stage = $profit / $capital >= 0.2 ? 'Growth' : 'Stable';
        } elseif ($capital > 0 && $profit < 0) {
            $stage = 'Recovery';
        } elseif ($capital <= 0) {
            $stage = 'Pre-Capital';
        }

        // Differentiation summary
        $differentiation = [
            'capital_vs_liability' => [
                'capital'   => $capital,
                'liability' => $liabilities,
                'ratio'     => $capital > 0 ? $liabilities / $capital : 0,
                'status'    => $liabilities <= $capital ? 'Healthy' : 'Over-leveraged',
            ],
            'capital_vs_assets' => [
                'capital' => $capital,
                'assets'  => $assets,
                'funded_by_capital_pct' => $assets > 0 ? ($equity / $assets) * 100 : 0,
                'funded_by_debt_pct'    => $assets > 0 ? ($liabilities / $assets) * 100 : 0,
            ],
            'capital_vs_investment' => [
                'capital'     => $capital,
                'investments' => $investments,
                'deployed_pct'=> $investmentRatio,
                'idle_capital'=> max(0, $capital - $investments),
            ],
            'capital_vs_expenses' => [
                'capital'       => $capital,
                'expenses'      => $expenses,
                'expense_ratio' => $expenseRatio,
                'status'        => $expenseRatio <= 70 ? 'Efficient' : ($expenseRatio <= 90 ? 'Moderate' : 'High Cost'),
            ],
            'capital_vs_revenue' => [
                'capital'    => $capital,
                'revenue'    => $revenue,
                'efficiency' => $capitalEfficiency,
                'status'     => $capitalEfficiency >= 1.5 ? 'High Return' : ($capitalEfficiency >= 1 ? 'Moderate Return' : 'Low Return'),
            ],
        ];

        return [
            'stage'               => $stage,
            'capital_efficiency'  => $capitalEfficiency,
            'asset_coverage'      => $assetCoverage,
            'investment_ratio'    => $investmentRatio,
            'expense_ratio'       => $expenseRatio,
            'capital_adequacy'    => $capitalAdequacy,
            'net_worth'           => $netWorth,
            'differentiation'     => $differentiation,
            'totals'              => $t,
        ];
    }

    // ─────────────────────────────────────────────
    // CALCULATION TRACEABILITY
    // Returns detailed breakdown of how each metric is calculated
    // with source tables and formulas for audit trail
    // ─────────────────────────────────────────────
    public function getCalculationTraceability(): array
    {
        return [
            'capital' => [
                'formula' => 'SUM(capital_entries.amount WHERE entry_type = "contribution")',
                'source_tables' => ['capital_entries'],
                'description' => 'Owner equity contributions from capital_entries table',
                'accounting_principle' => 'Capital represents owner investment - increases equity',
            ],
            'revenue' => [
                'formula' => 'SUM(sales.total_amount)',
                'source_tables' => ['sales'],
                'description' => 'Total sales revenue from all transactions',
                'accounting_principle' => 'Revenue increases retained earnings and owner equity',
            ],
            'expenses' => [
                'formula' => 'SUM(feed_records.quantity_kg * unit_cost) + SUM(medication_records.quantity_used * unit_cost) + SUM(vaccination_records.cost_amount) + SUM(expenses.amount) + SUM(mortality_records.quantity * animal_batches.initial_unit_cost) + SUM(animal_batches.initial_quantity * initial_unit_cost)',
                'source_tables' => ['feed_records', 'medication_records', 'vaccination_records', 'expenses', 'mortality_records', 'animal_batches'],
                'description' => 'All operational costs including feed, medication, vaccination, direct expenses, livestock purchase cost, and mortality losses',
                'accounting_principle' => 'Expenses reduce profit and retained earnings',
                'components' => [
                    'feed_costs' => 'Feed consumption × unit cost',
                    'medication_costs' => 'Medication used × unit cost',
                    'vaccination_costs' => 'Vaccination cost amounts',
                    'direct_expenses' => 'Manual expense entries',
                    'livestock_purchase' => 'Cash paid for chicks (initial_quantity × initial_unit_cost)',
                    'mortality_loss' => 'Dead birds × unit cost (asset write-off)',
                ],
            ],
            'assets' => [
                'formula' => 'SUM(inventory_item.current_stock * unit_cost) + SUM(animal_batches.current_quantity * initial_unit_cost WHERE status="active") + SUM(sales.total_amount - amount_paid WHERE payment_status IN ("unpaid","partial")) + SUM(investments.amount WHERE status="active")',
                'source_tables' => ['inventory_item', 'animal_batches', 'sales', 'investments'],
                'description' => 'Current value of all business assets',
                'accounting_principle' => 'Assets = what the business owns. Assets = Liabilities + Equity',
                'components' => [
                    'inventory' => 'Stock on hand × unit cost',
                    'biological_assets' => 'Live birds × purchase cost (current_quantity, not initial)',
                    'accounts_receivable' => 'Unpaid sales amounts',
                    'fixed_assets' => 'Active investments (equipment, infrastructure)',
                ],
            ],
            'liabilities' => [
                'formula' => 'SUM(liabilities.principal_amount - COALESCE(liability_payments.amount_paid, 0) WHERE status="active") + SUM(expenses.amount - amount_paid WHERE payment_status IN ("unpaid","partial"))',
                'source_tables' => ['liabilities', 'liability_payments', 'expenses'],
                'description' => 'Real-time outstanding obligations',
                'accounting_principle' => 'Liabilities = what the business owes. Must be repaid.',
                'components' => [
                    'registered_liabilities' => 'Principal - payments made (real-time)',
                    'unpaid_expenses' => 'Expense amount - amount paid',
                ],
            ],
            'retained_profit' => [
                'formula' => 'Total Revenue - Total Expenses',
                'source_tables' => ['sales', 'feed_records', 'medication_records', 'vaccination_records', 'expenses', 'mortality_records', 'animal_batches'],
                'description' => 'Cumulative profit/loss from operations',
                'accounting_principle' => 'Retained Profit = Revenue - Expenses. Increases owner equity.',
            ],
            'owner_equity' => [
                'formula' => 'Total Capital + Retained Profit',
                'source_tables' => ['capital_entries', 'sales', 'expenses', 'feed_records', 'medication_records', 'vaccination_records', 'mortality_records', 'animal_batches'],
                'description' => 'Total owner stake in the business',
                'accounting_principle' => 'Owner Equity = Capital + Retained Earnings. Represents owner ownership value.',
            ],
            'net_worth' => [
                'formula' => 'Owner Equity - Total Liabilities',
                'source_tables' => ['capital_entries', 'sales', 'expenses', 'liabilities', 'liability_payments'],
                'description' => 'True business value after debt',
                'accounting_principle' => 'Net Worth = Equity - Debt. Positive = solvent, Negative = insolvent.',
            ],
            'working_capital' => [
                'formula' => 'Total Assets - Total Liabilities',
                'source_tables' => ['inventory_item', 'animal_batches', 'sales', 'investments', 'liabilities', 'liability_payments', 'expenses'],
                'description' => 'Liquidity available for operations',
                'accounting_principle' => 'Working Capital = Current Assets - Current Liabilities. Measures short-term financial health.',
            ],
            'profit_margin' => [
                'formula' => '(Retained Profit / Total Revenue) × 100',
                'source_tables' => ['sales', 'expenses', 'feed_records', 'medication_records', 'vaccination_records', 'mortality_records', 'animal_batches'],
                'description' => 'Percentage of revenue retained as profit',
                'accounting_principle' => 'Profit Margin = (Net Income / Revenue) × 100. Higher is better.',
            ],
            'debt_ratio' => [
                'formula' => '(Total Liabilities / Total Assets) × 100',
                'source_tables' => ['liabilities', 'liability_payments', 'expenses', 'inventory_item', 'animal_batches', 'sales', 'investments'],
                'description' => 'Percentage of assets financed by debt',
                'accounting_principle' => 'Debt Ratio = (Liabilities / Assets) × 100. Lower is better. Above 60% is risky.',
            ],
            'roi' => [
                'formula' => '(Retained Profit / Total Capital) × 100',
                'source_tables' => ['capital_entries', 'sales', 'expenses', 'feed_records', 'medication_records', 'vaccination_records', 'mortality_records', 'animal_batches'],
                'description' => 'Return on capital investment',
                'accounting_principle' => 'ROI = (Net Income / Investment) × 100. Measures capital efficiency.',
            ],
            'current_ratio' => [
                'formula' => 'Total Assets / Total Liabilities',
                'source_tables' => ['inventory_item', 'animal_batches', 'sales', 'investments', 'liabilities', 'liability_payments', 'expenses'],
                'description' => 'Ability to pay short-term obligations',
                'accounting_principle' => 'Current Ratio = Assets / Liabilities. Above 1.0 = solvent, below 1.0 = liquidity risk.',
            ],
            'debt_to_equity' => [
                'formula' => 'Total Liabilities / Owner Equity',
                'source_tables' => ['liabilities', 'liability_payments', 'expenses', 'capital_entries', 'sales'],
                'description' => 'Leverage ratio',
                'accounting_principle' => 'Debt-to-Equity = Liabilities / Equity. Below 1.0 = healthy, above 2.0 = over-leveraged.',
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // ACCOUNTING PRINCIPLES REFERENCE
    // Educational reference for understanding financial statements
    // ─────────────────────────────────────────────
    public function getAccountingPrinciples(): array
    {
        return [
            'fundamental_equation' => [
                'formula' => 'Assets = Liabilities + Owner\'s Equity',
                'explanation' => 'The accounting equation must always balance. Everything the business owns (assets) is financed by either debt (liabilities) or owner investment (equity).',
            ],
            'double_entry' => [
                'principle' => 'Every transaction affects at least two accounts',
                'example' => 'Buying chicks: Debit Expense (cash paid), Debit Asset (birds owned)',
                'explanation' => 'This ensures the accounting equation stays balanced.',
            ],
            'revenue_recognition' => [
                'principle' => 'Revenue is recognized when earned, not when cash is received',
                'example' => 'Sale on credit: Revenue recorded immediately, cash collected later',
                'explanation' => 'Accrual accounting principle - matches revenue with the period it was earned.',
            ],
            'expense_matching' => [
                'principle' => 'Expenses are matched to the revenue they help generate',
                'example' => 'Feed cost for a batch is expensed when the batch is sold',
                'explanation' => 'Matching principle - expenses recorded in same period as related revenue.',
            ],
            'capital_vs_expense' => [
                'difference' => 'Capital = source of funds (equity). Expense = cost that reduces profit.',
                'explanation' => 'Capital is NOT reduced by expenses. Expenses reduce retained profit, which reduces equity.',
            ],
            'asset_vs_expense' => [
                'difference' => 'Asset = resource owned. Expense = cost consumed.',
                'example' => 'Buying chicks: Cash paid = Expense. Live birds = Asset.',
                'explanation' => 'Same transaction creates both expense (cash out) and asset (birds in).',
            ],
            'liability_vs_expense' => [
                'difference' => 'Liability = obligation to pay. Expense = cost incurred.',
                'example' => 'Unpaid feed bill: Expense recorded now, Liability until paid.',
                'explanation' => 'Expense reduces profit immediately. Liability tracks the debt.',
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function expGroup(string $label, string $source, float $amount, int $records, string $note, string $link, string $createLink): array
    {
        return [
            'category' => 'Expense', 'source' => $source,
            'label' => $label, 'records' => $records, 'amount' => $amount,
            'note' => $note, 'link' => $link, 'create_link' => $createLink,
            'collected' => 0, 'outstanding' => 0,
        ];
    }

    private function safe(string $sql, array $params = []): mixed
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() ?: 0;
        } catch (Throwable $e) { return 0; }
    }

    private function fetchSafe(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) { return []; }
    }
}

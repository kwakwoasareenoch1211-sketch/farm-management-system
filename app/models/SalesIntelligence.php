<?php

require_once BASE_PATH . 'app/core/Model.php';

/**
 * SalesIntelligence — Smart sales analytics engine.
 *
 * Computes:
 *  - Revenue projections (eggs + meat) from live batch data
 *  - Optimal pricing recommendations per unit/kg/tray
 *  - Break-even analysis per batch and overall
 *  - Debt payoff timeline based on current revenue trajectory
 *  - Monthly revenue trend and growth rate
 *  - Strategy recommendations (sell now vs hold, egg vs meat)
 */
class SalesIntelligence extends Model
{
    // ── Revenue projections from active batches ───────────────────────────────

    public function projections(): array
    {
        $batches = $this->fetchSafe("
            SELECT b.id, b.batch_code, b.batch_name, b.production_purpose,
                   b.current_quantity, b.initial_quantity, b.initial_unit_cost,
                   b.start_date, b.expected_end_date,
                   COALESCE(SUM(f.quantity_kg * f.unit_cost), 0) AS total_feed_cost,
                   COALESCE(SUM(m.quantity_used * m.unit_cost), 0) AS total_med_cost,
                   COALESCE(SUM(v.cost_amount), 0) AS total_vac_cost,
                   COALESCE(SUM(s.total_amount), 0) AS total_sales,
                   COALESCE((
                       SELECT AVG(wr.average_weight_kg)
                       FROM weight_records wr WHERE wr.batch_id = b.id
                   ), 0) AS avg_weight_kg,
                   COALESCE((
                       SELECT SUM(ep.quantity)
                       FROM egg_production_records ep WHERE ep.batch_id = b.id
                   ), 0) AS total_eggs
            FROM animal_batches b
            LEFT JOIN feed_records f ON f.batch_id = b.id
            LEFT JOIN medication_records m ON m.batch_id = b.id
            LEFT JOIN vaccination_records v ON v.batch_id = b.id
            LEFT JOIN sales s ON s.batch_id = b.id
            WHERE b.status = 'active'
            GROUP BY b.id
        ");

        // SMART PRICING: Learn from actual sales data
        $marketPrices = $this->getMarketPricesFromSales();
        $avgEggPrice  = $marketPrices['egg_price'];
        $avgMeatPricePerKg = $marketPrices['meat_price_per_kg'];

        $result = [];
        foreach ($batches as $b) {
            $birds       = (float)$b['current_quantity'];
            $unitCost    = (float)$b['initial_unit_cost'];
            
            // TRUE total cost: purchase + feed + medication + vaccination
            $totalCost   = ($b['initial_quantity'] * $unitCost)
                         + (float)$b['total_feed_cost']
                         + (float)$b['total_med_cost']
                         + (float)$b['total_vac_cost'];
            
            // Cost per LIVE bird (current quantity, not initial)
            $costPerBird = $birds > 0 ? $totalCost / $birds : 0;
            $avgWeight   = (float)$b['avg_weight_kg'];
            $totalEggs   = (float)$b['total_eggs'];
            $alreadySold = (float)$b['total_sales'];

            // Egg layer projection: 300 eggs/bird/year ≈ 25/month
            $isLayer = in_array($b['production_purpose'], ['layer', 'eggs', 'dual']);
            $projEggsPerMonth = $isLayer ? $birds * 25 : 0;
            $projEggRevMonth  = $projEggsPerMonth * $avgEggPrice;

            // Meat projection: current live weight × market price per kg
            $effectiveWeight = $avgWeight > 0 ? $avgWeight : 1.8;
            $liveWeightKg    = $birds * $effectiveWeight;
            $projMeatRevenue = $liveWeightKg * $avgMeatPricePerKg;

            // Break-even: how much revenue needed to cover REMAINING cost
            $remainingCost    = max(0, $totalCost - $alreadySold);
            $breakEvenEggs    = $avgEggPrice > 0 ? ceil($remainingCost / $avgEggPrice) : 0;
            $breakEvenKg      = $avgMeatPricePerKg > 0 ? $remainingCost / $avgMeatPricePerKg : 0;

            // Minimum price to break even per unit (based on REMAINING cost)
            $minPricePerEgg   = $projEggsPerMonth > 0 ? $remainingCost / $projEggsPerMonth : 0;
            $minPricePerKg    = $liveWeightKg > 0 ? $remainingCost / $liveWeightKg : 0;
            $minPricePerBird  = $birds > 0 ? $remainingCost / $birds : 0;

            // Recommended price (cost + 35% margin for profitability)
            $recPricePerEgg   = $minPricePerEgg * 1.35;
            $recPricePerKg    = $minPricePerKg  * 1.35;
            $recPricePerBird  = $minPricePerBird * 1.35;

            // Strategy recommendation with dynamic market thresholds
            $strategy = $this->batchStrategy($b, $totalCost, $alreadySold, $projEggRevMonth, $projMeatRevenue, $effectiveWeight, $birds, $marketPrices);

            $result[] = [
                'batch_id'           => (int)$b['id'],
                'batch_code'         => $b['batch_code'],
                'batch_name'         => $b['batch_name'],
                'purpose'            => $b['production_purpose'],
                'birds'              => $birds,
                'total_cost'         => $totalCost,
                'cost_per_bird'      => $costPerBird,
                'already_sold'       => $alreadySold,
                'remaining_cost'     => $remainingCost,
                'avg_weight_kg'      => $effectiveWeight,
                'live_weight_kg'     => $liveWeightKg,
                'total_eggs'         => $totalEggs,
                // Projections
                'proj_eggs_per_month'=> $projEggsPerMonth,
                'proj_egg_rev_month' => $projEggRevMonth,
                'proj_meat_revenue'  => $projMeatRevenue,
                // Break-even
                'breakeven_eggs'     => $breakEvenEggs,
                'breakeven_kg'       => round($breakEvenKg, 2),
                // Minimum prices (break-even)
                'min_price_per_egg'  => round($minPricePerEgg, 4),
                'min_price_per_kg'   => round($minPricePerKg, 2),
                'min_price_per_bird' => round($minPricePerBird, 2),
                // Recommended prices (35% margin)
                'rec_price_per_egg'  => round($recPricePerEgg, 4),
                'rec_price_per_kg'   => round($recPricePerKg, 2),
                'rec_price_per_bird' => round($recPricePerBird, 2),
                // Market reference (from actual sales)
                'market_egg_price'   => $avgEggPrice,
                'market_meat_price_kg' => $avgMeatPricePerKg,
                'market_meat_price_bird' => $effectiveWeight * $avgMeatPricePerKg,
                'strategy'           => $strategy,
            ];
        }

        return $result;
    }

    // ── Monthly revenue trend (last 12 months) ────────────────────────────────

    public function monthlyTrend(): array
    {
        $rows = $this->fetchSafe("
            SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month,
                   COALESCE(SUM(total_amount), 0) AS revenue,
                   COALESCE(SUM(amount_paid), 0) AS collected,
                   COUNT(*) AS transactions
            FROM sales
            WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
            ORDER BY month ASC
        ");

        // Compute month-over-month growth
        $prev = 0;
        foreach ($rows as &$r) {
            $r['growth_pct'] = $prev > 0 ? (((float)$r['revenue'] - $prev) / $prev) * 100 : 0;
            $prev = (float)$r['revenue'];
        }

        return $rows;
    }

    // ── Debt payoff timeline ──────────────────────────────────────────────────

    public function debtPayoff(): array
    {
        $totalDebt = (float)$this->scalar("
            SELECT COALESCE(SUM(amount), 0) FROM capital_entries WHERE entry_type = 'contribution'
        ");
        $unpaidExpenses = (float)$this->scalar("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
        ");
        $totalLiabilities = $totalDebt + $unpaidExpenses;

        // Average monthly net revenue (last 6 months)
        $avgMonthlyRevenue = (float)$this->scalar("
            SELECT COALESCE(AVG(monthly_rev), 0) FROM (
                SELECT SUM(total_amount) AS monthly_rev
                FROM sales
                WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
            ) t
        ");

        // Average monthly expenses (last 6 months)
        $avgMonthlyExpenses = (float)$this->scalar("
            SELECT COALESCE(AVG(monthly_exp), 0) FROM (
                SELECT SUM(amount) AS monthly_exp
                FROM expenses
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
            ) t
        ");

        $avgMonthlyNet = $avgMonthlyRevenue - $avgMonthlyExpenses;

        // Months to pay off debt at current trajectory
        $monthsToPayoff = ($avgMonthlyNet > 0 && $totalLiabilities > 0)
            ? ceil($totalLiabilities / $avgMonthlyNet)
            : null;

        // Revenue needed per month to pay off in 12 months
        $revenueNeeded12m = $totalLiabilities > 0
            ? ($totalLiabilities / 12) + $avgMonthlyExpenses
            : 0;

        // Revenue needed per month to pay off in 6 months
        $revenueNeeded6m = $totalLiabilities > 0
            ? ($totalLiabilities / 6) + $avgMonthlyExpenses
            : 0;

        return [
            'total_debt'           => $totalDebt,
            'unpaid_expenses'      => $unpaidExpenses,
            'total_liabilities'    => $totalLiabilities,
            'avg_monthly_revenue'  => $avgMonthlyRevenue,
            'avg_monthly_expenses' => $avgMonthlyExpenses,
            'avg_monthly_net'      => $avgMonthlyNet,
            'months_to_payoff'     => $monthsToPayoff,
            'revenue_needed_12m'   => $revenueNeeded12m,
            'revenue_needed_6m'    => $revenueNeeded6m,
            'status'               => $this->debtStatus($totalLiabilities, $avgMonthlyNet),
        ];
    }

    // ── Overall pricing intelligence ──────────────────────────────────────────

    public function pricingIntelligence(): array
    {
        // Get smart market prices from actual sales
        $marketPrices = $this->getMarketPricesFromSales();
        
        // Actual sales breakdown by type
        $rows = $this->fetchSafe("
            SELECT product_type,
                   COUNT(*) AS transactions,
                   COALESCE(SUM(total_amount), 0) AS total_revenue,
                   COALESCE(AVG(total_amount), 0) AS avg_sale_value,
                   COALESCE(SUM(quantity), 0) AS total_quantity
            FROM sales
            GROUP BY product_type
            ORDER BY total_revenue DESC
        ");

        // Total cost of production (all batches, all inputs)
        $totalCost = (float)$this->scalar("
            SELECT COALESCE(SUM(initial_quantity * initial_unit_cost), 0) FROM animal_batches
        ") + (float)$this->scalar("
            SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) FROM feed_records
        ") + (float)$this->scalar("
            SELECT COALESCE(SUM(quantity_used * unit_cost), 0) FROM medication_records
        ") + (float)$this->scalar("
            SELECT COALESCE(SUM(cost_amount), 0) FROM vaccination_records
        ");

        $totalRevenue = (float)$this->scalar("SELECT COALESCE(SUM(total_amount), 0) FROM sales");
        $totalBirds   = (float)$this->scalar("SELECT COALESCE(SUM(current_quantity), 0) FROM animal_batches WHERE status='active'");
        $totalEggs    = (float)$this->scalar("SELECT COALESCE(SUM(quantity), 0) FROM egg_production_records");
        
        // Total live weight: current birds × average weight (default 1.8kg for broilers)
        $totalWeight  = (float)$this->scalar("
            SELECT COALESCE(SUM(b.current_quantity * COALESCE(w.avg_wt, 1.8)), 0)
            FROM animal_batches b
            LEFT JOIN (
                SELECT batch_id, AVG(average_weight_kg) AS avg_wt FROM weight_records GROUP BY batch_id
            ) w ON w.batch_id = b.id
            WHERE b.status = 'active'
        ");

        // Cost per unit (based on CURRENT live inventory)
        $costPerEgg   = $totalEggs   > 0 ? $totalCost / $totalEggs   : 0;
        $costPerKg    = $totalWeight > 0 ? $totalCost / $totalWeight  : 0;
        $costPerBird  = $totalBirds  > 0 ? $totalCost / $totalBirds   : 0;

        // Recommended prices: MAX(cost + 35%, market price from sales)
        $recPricePerEgg  = max($costPerEgg * 1.35, $marketPrices['egg_price']);
        $recPricePerKg   = max($costPerKg * 1.35, $marketPrices['meat_price_per_kg']);
        $recPricePerBird = max($costPerBird * 1.35, $marketPrices['meat_price_per_bird']);

        return [
            'by_type'            => $rows,
            'total_cost'         => $totalCost,
            'total_revenue'      => $totalRevenue,
            'gross_profit'       => $totalRevenue - $totalCost,
            'overall_margin'     => $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0,
            'cost_per_egg'       => round($costPerEgg, 4),
            'cost_per_kg'        => round($costPerKg, 2),
            'cost_per_bird'      => round($costPerBird, 2),
            'rec_price_per_egg'  => round($recPricePerEgg, 4),
            'rec_price_per_kg'   => round($recPricePerKg, 2),
            'rec_price_per_bird' => round($recPricePerBird, 2),
            // Market prices from actual sales (SMART)
            'market_egg_price'   => round($marketPrices['egg_price'], 4),
            'market_meat_price_kg' => round($marketPrices['meat_price_per_kg'], 2),
            'market_meat_price_bird' => round($marketPrices['meat_price_per_bird'], 2),
            'has_egg_sales'      => $marketPrices['has_egg_sales'],
            'has_meat_sales'     => $marketPrices['has_meat_sales'],
            'price_source'       => $marketPrices['has_egg_sales'] || $marketPrices['has_meat_sales'] ? 'actual_sales' : 'defaults',
        ];
    }

    // ── Revenue growth strategy ───────────────────────────────────────────────

    public function growthStrategy(): array
    {
        $trend    = $this->monthlyTrend();
        $debt     = $this->debtPayoff();
        $pricing  = $this->pricingIntelligence();
        $projs    = $this->projections();

        $totalProjectedMonthly = array_sum(array_column($projs, 'proj_egg_rev_month'));
        $totalProjectedMeat    = array_sum(array_column($projs, 'proj_meat_revenue'));

        $strategies = [];

        // Strategy 1: Egg revenue
        if ($totalProjectedMonthly > 0) {
            $strategies[] = [
                'type'   => 'egg_revenue',
                'title'  => 'Maximize Egg Revenue',
                'icon'   => '🥚',
                'color'  => 'warning',
                'value'  => $totalProjectedMonthly,
                'label'  => 'Projected monthly egg revenue',
                'action' => 'Ensure all layers are healthy and producing. Record daily egg collections.',
            ];
        }

        // Strategy 2: Meat sale
        if ($totalProjectedMeat > 0) {
            $strategies[] = [
                'type'   => 'meat_sale',
                'title'  => 'Sell Broilers at Peak Weight',
                'icon'   => '🍗',
                'color'  => 'danger',
                'value'  => $totalProjectedMeat,
                'label'  => 'Projected meat revenue if sold now',
                'action' => 'Broilers at 2kg+ are at optimal sell weight. Delay increases feed cost with diminishing returns.',
            ];
        }

        // Strategy 3: Debt clearance
        if ($debt['total_liabilities'] > 0 && $debt['avg_monthly_net'] > 0) {
            $strategies[] = [
                'type'   => 'debt_clearance',
                'title'  => 'Accelerate Debt Payoff',
                'icon'   => '💳',
                'color'  => 'primary',
                'value'  => $debt['revenue_needed_12m'],
                'label'  => 'Monthly revenue needed to clear debt in 12 months',
                'action' => 'Increase sales volume or reduce expenses by ' . number_format(max(0, $debt['revenue_needed_12m'] - $debt['avg_monthly_revenue']), 2) . ' GHS/month.',
            ];
        }

        // Strategy 4: Pricing improvement
        if ($pricing['overall_margin'] < 20) {
            $strategies[] = [
                'type'   => 'pricing',
                'title'  => 'Improve Pricing Margin',
                'icon'   => '📈',
                'color'  => 'success',
                'value'  => $pricing['rec_price_per_egg'],
                'label'  => 'Recommended minimum egg price (GHS)',
                'action' => 'Current margin is ' . number_format($pricing['overall_margin'], 1) . '%. Target 30%+ by adjusting prices.',
            ];
        }

        return [
            'strategies'              => $strategies,
            'total_projected_monthly' => $totalProjectedMonthly,
            'total_projected_meat'    => $totalProjectedMeat,
            'debt'                    => $debt,
            'pricing'                 => $pricing,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Get market prices from actual sales data (SMART LEARNING)
     * Falls back to sensible defaults if no sales exist
     */
    private function getMarketPricesFromSales(): array
    {
        // Egg pricing from actual sales
        $eggData = $this->fetchSafe("
            SELECT 
                AVG(unit_price) AS avg_unit_price,
                AVG(total_amount / NULLIF(quantity, 0)) AS avg_price_per_unit,
                COUNT(*) AS sales_count
            FROM sales 
            WHERE product_type IN ('eggs', 'egg') 
            AND quantity > 0 
            AND total_amount > 0
        ");
        
        $eggPrice = 2.50; // Default: 2.50 GHS per egg
        if (!empty($eggData[0]) && (float)$eggData[0]['sales_count'] > 0) {
            $calculated = (float)($eggData[0]['avg_price_per_unit'] ?? 0);
            if ($calculated > 0) {
                $eggPrice = $calculated;
            } elseif ((float)($eggData[0]['avg_unit_price'] ?? 0) > 0) {
                $eggPrice = (float)$eggData[0]['avg_unit_price'];
            }
        }

        // Meat pricing from actual sales (per kg and per bird by weight)
        $meatData = $this->fetchSafe("
            SELECT 
                s.unit_price,
                s.total_amount,
                s.quantity,
                s.product_type,
                COALESCE(w.avg_weight, 2.0) AS avg_weight
            FROM sales s
            LEFT JOIN (
                SELECT batch_id, AVG(average_weight_kg) AS avg_weight 
                FROM weight_records 
                GROUP BY batch_id
            ) w ON w.batch_id = s.batch_id
            WHERE s.product_type IN ('broiler', 'meat', 'live_bird', 'chicken')
            AND s.quantity > 0 
            AND s.total_amount > 0
        ");

        $meatPricePerKg = 0;
        $meatPricePerBird = 0;
        $totalWeight = 0;
        $totalRevenue = 0;
        $birdCount = 0;

        foreach ($meatData as $sale) {
            $qty = (float)$sale['quantity'];
            $amount = (float)$sale['total_amount'];
            $weight = (float)$sale['avg_weight'];
            
            if ($qty > 0 && $amount > 0) {
                $pricePerBird = $amount / $qty;
                $totalRevenue += $amount;
                $birdCount += $qty;
                
                if ($weight > 0) {
                    $totalWeight += ($qty * $weight);
                }
            }
        }

        // Calculate average price per kg from sales
        if ($totalWeight > 0 && $totalRevenue > 0) {
            $meatPricePerKg = $totalRevenue / $totalWeight;
        } else {
            // Default: 150-250 GHS per bird at 2kg = 75-125 per kg, use 100
            $meatPricePerKg = 100.00;
        }

        // Calculate average price per bird from sales
        if ($birdCount > 0 && $totalRevenue > 0) {
            $meatPricePerBird = $totalRevenue / $birdCount;
        } else {
            // Default: 200 GHS per bird
            $meatPricePerBird = 200.00;
        }

        return [
            'egg_price'          => $eggPrice,
            'meat_price_per_kg'  => $meatPricePerKg,
            'meat_price_per_bird'=> $meatPricePerBird,
            'has_egg_sales'      => !empty($eggData[0]) && (float)$eggData[0]['sales_count'] > 0,
            'has_meat_sales'     => count($meatData) > 0,
        ];
    }

    private function batchStrategy(array $b, float $totalCost, float $alreadySold, float $projEggRev, float $projMeatRev, float $avgWeight, float $birds, array $marketPrices): array
    {
        $purpose = $b['production_purpose'] ?? 'broiler';
        $remaining = max(0, $totalCost - $alreadySold);
        
        $eggPrice = $marketPrices['egg_price'];
        $meatPricePerKg = $marketPrices['meat_price_per_kg'];

        // LAYER STRATEGY: Egg analysis with dynamic pricing
        if (in_array($purpose, ['layer', 'eggs'])) {
            $monthsToBreakEven = $projEggRev > 0 ? ceil($remaining / $projEggRev) : null;
            $projEggsPerMonth = $birds * 25; // 25 eggs/bird/month
            $projRevAtCurrentPrice = $projEggsPerMonth * $eggPrice;
            
            if ($projRevAtCurrentPrice >= $remaining && $monthsToBreakEven !== null && $monthsToBreakEven <= 3) {
                return [
                    'recommendation' => 'Excellent egg production',
                    'reason'         => 'Projected ' . number_format($projEggsPerMonth) . ' eggs/month at ' . number_format($eggPrice, 2) . ' GHS/egg. Break-even in ' . $monthsToBreakEven . ' month(s).',
                    'urgency'        => 'good',
                ];
            }
            
            if ($monthsToBreakEven !== null && $monthsToBreakEven <= 6) {
                return [
                    'recommendation' => 'Keep laying',
                    'reason'         => 'Layer flock producing ' . number_format($projEggsPerMonth) . ' eggs/month at ' . number_format($eggPrice, 2) . ' GHS. Break-even in ' . $monthsToBreakEven . ' months.',
                    'urgency'        => 'neutral',
                ];
            }
            
            return [
                'recommendation' => 'Review egg production',
                'reason'         => 'Low egg output or high costs. Current price: ' . number_format($eggPrice, 2) . ' GHS/egg. Break-even: ' . ($monthsToBreakEven ?? 'unknown') . ' months.',
                'urgency'        => 'medium',
            ];
        }

        // BROILER STRATEGY: Dynamic pricing based on weight
        // Price ranges by weight (from actual sales or defaults):
        // 1.5kg = 150 GHS, 2.0kg = 200 GHS, 2.5kg = 250 GHS, 3.0kg = 300 GHS
        $projRevenuePerBird = $avgWeight * $meatPricePerKg;
        $costPerBird = $birds > 0 ? $totalCost / $birds : 0;
        $profitPerBird = $projRevenuePerBird - $costPerBird;
        
        // Optimal sell weight: 2.0-2.5kg
        if ($avgWeight >= 2.0 && $avgWeight <= 2.5) {
            $totalProjRevenue = $birds * $projRevenuePerBird;
            $priceRange = number_format($avgWeight * $meatPricePerKg, 2);
            return [
                'recommendation' => 'SELL NOW - Optimal weight',
                'reason'         => 'Birds at ' . number_format($avgWeight, 2) . 'kg = ' . $priceRange . ' GHS/bird at ' . number_format($meatPricePerKg, 2) . ' GHS/kg. Total: ' . number_format($totalProjRevenue, 2) . ' GHS.',
                'urgency'        => 'high',
            ];
        }

        // Above optimal: diminishing returns
        if ($avgWeight > 2.5) {
            return [
                'recommendation' => 'SELL IMMEDIATELY',
                'reason'         => 'Birds at ' . number_format($avgWeight, 2) . 'kg = ' . number_format($projRevenuePerBird, 2) . ' GHS/bird. Above optimal weight - feed cost exceeding value gain.',
                'urgency'        => 'high',
            ];
        }

        // Approaching optimal: 1.8-2.0kg
        if ($avgWeight >= 1.8) {
            $daysToOptimal = ceil((2.0 - $avgWeight) / 0.05); // ~50g gain per day
            $targetPrice = 2.0 * $meatPricePerKg;
            return [
                'recommendation' => 'Prepare to sell',
                'reason'         => 'Birds at ' . number_format($avgWeight, 2) . 'kg — ' . $daysToOptimal . ' days to 2kg optimal. Current: ' . number_format($projRevenuePerBird, 2) . ' GHS, Target: ' . number_format($targetPrice, 2) . ' GHS/bird.',
                'urgency'        => 'medium',
            ];
        }

        // Still growing: 1.5-1.8kg
        if ($avgWeight >= 1.5) {
            $targetPrice = 2.0 * $meatPricePerKg;
            return [
                'recommendation' => 'Continue growing',
                'reason'         => 'Birds at ' . number_format($avgWeight, 2) . 'kg = ' . number_format($projRevenuePerBird, 2) . ' GHS/bird. Target 2kg for ' . number_format($targetPrice, 2) . ' GHS/bird at ' . number_format($meatPricePerKg, 2) . ' GHS/kg.',
                'urgency'        => 'low',
            ];
        }

        // Early stage: below 1.5kg
        $targetPrice = 2.0 * $meatPricePerKg;
        return [
            'recommendation' => 'Early growth stage',
            'reason'         => 'Birds at ' . number_format($avgWeight, 2) . 'kg — maintain feeding schedule. Target 2kg for ' . number_format($targetPrice, 2) . ' GHS/bird at market rate of ' . number_format($meatPricePerKg, 2) . ' GHS/kg.',
            'urgency'        => 'low',
        ];
    }

    private function debtStatus(float $debt, float $monthlyNet): string
    {
        if ($debt <= 0) return 'debt_free';
        if ($monthlyNet <= 0) return 'critical';
        $months = $debt / $monthlyNet;
        if ($months <= 6)  return 'manageable';
        if ($months <= 18) return 'moderate';
        return 'high_risk';
    }

    private function scalar(string $sql, array $params = []): mixed
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $v = $stmt->fetchColumn();
            return $v !== false ? $v : 0;
        } catch (Throwable) { return 0; }
    }

    private function fetchSafe(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) { return []; }
    }
}

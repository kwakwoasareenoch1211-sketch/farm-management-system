<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/FinanceSummary.php';
require_once BASE_PATH . 'app/models/FinancialMonitor.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/Sales.php';
require_once BASE_PATH . 'app/models/Capital.php';
require_once BASE_PATH . 'app/models/Investment.php';

class EconomicController extends Controller
{
    public function dashboard(): void
    {
        $data = $this->buildEconomicData();
        $this->view('economic/dashboard', ['pageTitle'=>'Economic Dashboard','sidebarType'=>'economic',...$data], 'admin');
    }

    public function businessHealth(): void
    {
        $data = $this->buildEconomicData();
        $this->view('economic/business-health', ['pageTitle'=>'Business Health','sidebarType'=>'economic',...$data], 'admin');
    }

    public function goingConcern(): void
    {
        $data = $this->buildEconomicData();
        $this->view('economic/going-concern', ['pageTitle'=>'Going Concern','sidebarType'=>'economic',...$data], 'admin');
    }

    public function decisionSupport(): void
    {
        $data = $this->buildEconomicData();
        $this->view('economic/decision-support', ['pageTitle'=>'Decision Support','sidebarType'=>'economic',...$data], 'admin');
    }

    private function buildEconomicData(): array
    {
        $financeSummary  = new FinanceSummary();
        $monitor         = new FinancialMonitor();
        $batchModel      = new Batch();
        $salesModel      = new Sales();
        $capitalModel    = new Capital();
        $investmentModel = new Investment();

        // ── Use FinanceSummary as single source of truth (same as financial dashboard) ──
        $financeTotals      = $financeSummary->totals();
        $currentMonthTotals = $financeSummary->currentMonthTotals();
        $monthlyCombined    = $financeSummary->monthlyCombinedBreakdown(6);

        $totalRevenue  = (float)($financeTotals['sales_revenue'] ?? 0);
        $totalExpenses = (float)($financeTotals['total_expense']  ?? 0);
        $monthRevenue  = (float)($currentMonthTotals['sales_revenue'] ?? 0);
        $monthExpense  = (float)($currentMonthTotals['total_expense']  ?? 0);
        $monthNet      = (float)($currentMonthTotals['net_position']   ?? 0);

        // ── Assets/Liabilities/Capital/Investments from FinancialMonitor ──────
        $monitorTotals    = $monitor->totals();
        $totalAssets      = (float)($monitorTotals['total_assets']      ?? 0);
        $totalLiabilities = (float)($monitorTotals['total_liabilities'] ?? 0);
        $totalCapital     = (float)($monitorTotals['total_capital']     ?? 0);
        $totalInvestments = (float)($monitorTotals['total_investments'] ?? 0);

        // ── Derived accounting metrics ────────────────────────────────────────
        $retainedProfit    = $totalRevenue - $totalExpenses;
        $ownerEquity       = $totalCapital + $retainedProfit;
        $netWorth          = $ownerEquity - $totalLiabilities;
        $workingCapital    = $totalAssets - $totalLiabilities;
        $profitMarginPct   = $totalRevenue > 0 ? ($retainedProfit / $totalRevenue) * 100 : 0;
        $debtRatio         = $totalAssets > 0 ? ($totalLiabilities / $totalAssets) * 100 : 0;
        $capitalROI        = $totalCapital > 0 ? ($retainedProfit / $totalCapital) * 100 : 0;
        $capitalEfficiency = $totalCapital > 0 ? $totalRevenue / $totalCapital : 0;
        $expenseRatio      = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;
        $capitalAdequacy   = $totalLiabilities > 0 ? ($ownerEquity / $totalLiabilities) * 100 : 100;
        $liquidityRatio    = $totalLiabilities > 0 ? $totalAssets / $totalLiabilities : ($totalAssets > 0 ? $totalAssets : 0);
        $debtToEquity      = $workingCapital != 0 ? $totalLiabilities / max(1, $workingCapital) : 0;
        $roi               = $monthExpense > 0 ? ($monthNet / $monthExpense) * 100 : 0;
        $assetCoverage     = $totalLiabilities > 0 ? $totalAssets / $totalLiabilities : ($totalAssets > 0 ? 999 : 0);
        $investmentRatio   = $totalCapital > 0 ? ($totalInvestments / $totalCapital) * 100 : 0;

        // ── Business stage ────────────────────────────────────────────────────
        $businessStage = 'Startup';
        if ($totalCapital > 0 && $retainedProfit > 0 && $assetCoverage >= 1.5) {
            $businessStage = $retainedProfit / $totalCapital >= 0.2 ? 'Growth' : 'Stable';
        } elseif ($totalCapital > 0 && $retainedProfit < 0) {
            $businessStage = 'Recovery';
        } elseif ($totalCapital <= 0) {
            $businessStage = 'Pre-Capital';
        }

        // ── Differentiation analysis ──────────────────────────────────────────
        $diff = [
            'capital_vs_liability'  => ['capital'=>$totalCapital,'liability'=>$totalLiabilities,'ratio'=>$totalCapital>0?$totalLiabilities/$totalCapital:0,'status'=>$totalLiabilities<=$totalCapital?'Healthy':'Over-leveraged'],
            'capital_vs_assets'     => ['capital'=>$totalCapital,'assets'=>$totalAssets,'funded_by_capital_pct'=>$totalAssets>0?($ownerEquity/$totalAssets)*100:0,'funded_by_debt_pct'=>$totalAssets>0?($totalLiabilities/$totalAssets)*100:0],
            'capital_vs_investment' => ['capital'=>$totalCapital,'investments'=>$totalInvestments,'deployed_pct'=>$investmentRatio,'idle_capital'=>max(0,$totalCapital-$totalInvestments)],
            'capital_vs_expenses'   => ['capital'=>$totalCapital,'expenses'=>$totalExpenses,'expense_ratio'=>$expenseRatio,'status'=>$expenseRatio<=70?'Efficient':($expenseRatio<=90?'Moderate':'High Cost')],
            'capital_vs_revenue'    => ['capital'=>$totalCapital,'revenue'=>$totalRevenue,'efficiency'=>$capitalEfficiency,'status'=>$capitalEfficiency>=1.5?'High Return':($capitalEfficiency>=1?'Moderate Return':'Low Return')],
        ];

        $businessAnalysis = ['stage'=>$businessStage,'capital_efficiency'=>$capitalEfficiency,'asset_coverage'=>$assetCoverage,'investment_ratio'=>$investmentRatio,'expense_ratio'=>$expenseRatio,'capital_adequacy'=>$capitalAdequacy,'net_worth'=>$netWorth,'differentiation'=>$diff,'totals'=>$monitorTotals];

        // ── Batch analysis ────────────────────────────────────────────────────
        $batches = $batchModel->all();
        $lossMakingBatches = []; $strongBatches = []; $topBatch = null; $worstBatch = null;
        foreach ($batches as $batch) {
            $gp = (float)($batch['gross_profit'] ?? 0);
            if ($gp < 0) $lossMakingBatches[] = $batch;
            if ($gp > 0) $strongBatches[]     = $batch;
            if ($topBatch   === null || $gp > (float)($topBatch['gross_profit']   ?? 0)) $topBatch   = $batch;
            if ($worstBatch === null || $gp < (float)($worstBatch['gross_profit'] ?? 0)) $worstBatch = $batch;
        }

        // ── Health scoring ────────────────────────────────────────────────────
        $ps = match(true) { $profitMarginPct>=25=>30,$profitMarginPct>=15=>24,$profitMarginPct>=5=>16,$profitMarginPct>0=>10,default=>3 };
        $ls = match(true) { $liquidityRatio>=2=>20,$liquidityRatio>=1.2=>15,$liquidityRatio>=1=>10,default=>4 };
        $ss = match(true) { $totalLiabilities<=0&&$totalAssets>0=>15,$totalAssets>$totalLiabilities=>12,$totalAssets==$totalLiabilities=>8,default=>3 };
        $es = match(true) { count($lossMakingBatches)===0&&count($strongBatches)>0=>15,count($strongBatches)>count($lossMakingBatches)=>11,count($strongBatches)>0=>8,default=>3 };

        $trendSignal = 'Flat'; $gs = 4;
        if (!empty($monthlyCombined)) {
            $last = end($monthlyCombined); $ln = (float)($last['net_position']??0);
            if ($ln>0&&$monthRevenue>0){$gs=20;$trendSignal='Positive';}
            elseif($monthRevenue>0){$gs=14;$trendSignal='Moderate';}
            else{$gs=5;$trendSignal='Weak';}
        } elseif ($monthRevenue>0) { $gs=12; $trendSignal='Moderate'; }

        $healthScore = $ps+$ls+$ss+$es+$gs;
        $healthLabel = $healthScore>=80?'Strong':($healthScore>=60?'Stable':'Risk');
        $healthClass = $healthScore>=80?'success':($healthScore>=60?'warning':'danger');

        // ── Going concern ─────────────────────────────────────────────────────
        $gcStatus='Caution'; $gcClass='warning'; $gcMsg='Monitor costs, liabilities, and operational efficiency carefully.';
        if ($totalAssets>$totalLiabilities&&$monthRevenue>=$monthExpense&&$healthScore>=60) { $gcStatus='Healthy';$gcClass='success';$gcMsg='Business appears capable of continuing operations.'; }
        elseif ($totalAssets<$totalLiabilities&&$monthRevenue<$monthExpense) { $gcStatus='At Risk';$gcClass='danger';$gcMsg='Financial pressure detected. Restructuring may be needed.'; }

        // ── Decision recommendation ───────────────────────────────────────────
        $dr='Stabilize First'; $dc='warning'; $dreason='Improve cash position and margins before expansion.';
        if ($monthNet>0&&$totalAssets>$totalLiabilities&&$healthScore>=70) { $dr='Expansion Possible';$dc='success';$dreason='Positive net, strong assets, healthy equity support growth.'; }
        elseif ($monthNet<0||count($lossMakingBatches)>0) { $dr='Review Operations';$dc='danger';$dreason='Weak profitability or loss-making batches need correction.'; }

        // ── Structured decisions ──────────────────────────────────────────────
        $decisions = [];
        if ($monthNet>0&&$totalAssets>$totalLiabilities&&$healthScore>=70) $decisions[]=['priority'=>'high','type'=>'success','title'=>'Expansion Opportunity','reason'=>'Business profitable with strong assets.','action'=>'Consider adding new batches or expanding flock.','link'=>'/batches/create'];
        if (count($lossMakingBatches)>0) $decisions[]=['priority'=>'high','type'=>'danger','title'=>'Review Loss-Making Batches','reason'=>count($lossMakingBatches).' batch(es) generating losses.','action'=>'Audit feed cost, mortality, and pricing.','link'=>'/batches'];
        if ($debtRatio>60) $decisions[]=['priority'=>'high','type'=>'warning','title'=>'Reduce Debt Pressure','reason'=>'Liabilities at '.number_format($debtRatio,1).'% of assets.','action'=>'Clear unpaid expenses before new commitments.','link'=>'/expenses'];
        if ($monthExpense>$monthRevenue) $decisions[]=['priority'=>'medium','type'=>'warning','title'=>'Month Expenses Exceed Revenue','reason'=>'This month costs exceed income.','action'=>'Review and reduce non-essential expenses.','link'=>'/expenses'];
        if ($profitMarginPct>0&&$profitMarginPct<10) $decisions[]=['priority'=>'medium','type'=>'warning','title'=>'Low Profit Margin','reason'=>'Margin is '.number_format($profitMarginPct,1).'%.','action'=>'Increase sale prices or reduce costs.','link'=>'/sales'];
        if ($totalCapital>0&&$capitalEfficiency<1) $decisions[]=['priority'=>'medium','type'=>'warning','title'=>'Low Capital Efficiency','reason'=>'GHS '.number_format($capitalEfficiency,2).' revenue per GHS capital.','action'=>'Deploy idle capital into productive investments.','link'=>'/investments/create'];
        if ($totalCapital<=0) $decisions[]=['priority'=>'high','type'=>'info','title'=>'No Capital Recorded','reason'=>'Capital base not tracked.','action'=>'Add owner equity to enable full analysis.','link'=>'/capital/create'];
        if (empty($decisions)) $decisions[]=['priority'=>'low','type'=>'success','title'=>'Business Operating Normally','reason'=>'No critical issues detected.','action'=>'Continue monitoring and maintain cost discipline.','link'=>'/admin'];

        // ── Strengths / Risks / Recommendations ──────────────────────────────
        $risks=[]; $strengths=[]; $recommendations=[];
        if ($monthExpense>$monthRevenue){$risks[]='Month expenses exceed revenue.';$recommendations[]='Reduce cost pressure immediately.';}else{$strengths[]='Month revenue covers expenses.';}
        if ($totalLiabilities>$totalAssets){$risks[]='Liabilities exceed assets.';$recommendations[]='Delay expansion and reduce debt.';}else{$strengths[]='Assets stronger than liabilities.';}
        if (count($lossMakingBatches)>0){$risks[]=count($lossMakingBatches).' batch(es) are loss-making.';$recommendations[]='Investigate weak batches.';}else{$strengths[]='No loss-making batch detected.';}
        if ($profitMarginPct>15){$strengths[]='Profit margin is healthy.';}elseif($profitMarginPct<=0){$risks[]='Profit margin is negative.';$recommendations[]='Increase revenue efficiency.';}
        if ($liquidityRatio<1){$risks[]='Liquidity below safe level.';$recommendations[]='Improve cash flow.';}else{$strengths[]='Liquidity is manageable.';}
        if ($totalCapital>0&&$capitalEfficiency>=1.5){$strengths[]='Capital generating strong revenue returns.';}
        if ($totalInvestments>0){$strengths[]='GHS '.number_format($totalInvestments,2).' in active investments.';}
        if ($healthScore>=80){$recommendations[]='Consider controlled expansion.';}elseif($healthScore>=60){$recommendations[]='Stable — improve weak areas before expansion.';}else{$recommendations[]='Focus on recovery before growth.';}
        if ($topBatch){$strengths[]='Top batch: '.($topBatch['batch_code']??'N/A').' — GHS '.number_format((float)($topBatch['gross_profit']??0),2).' profit.';}
        if ($worstBatch&&(float)($worstBatch['gross_profit']??0)<0){$risks[]='Weakest batch: '.($worstBatch['batch_code']??'N/A').' — GHS '.number_format((float)($worstBatch['gross_profit']??0),2).' loss.';}

        return [
            'monitorTotals'          => $monitorTotals,
            'monitorMonth'           => ['revenue'=>$monthRevenue,'total_expense'=>$monthExpense,'net'=>$monthNet],
            'businessAnalysis'       => $businessAnalysis,
            'businessStage'          => $businessStage,
            'capitalTotals'          => $capitalModel->totals(),
            'investmentTotals'       => $investmentModel->totals(),
            'capitalEfficiency'      => $capitalEfficiency,
            'expenseRatio'           => $expenseRatio,
            'capitalAdequacy'        => $capitalAdequacy,
            'diff'                   => $diff,
            'totalCapital'           => $totalCapital,
            'totalRevenue'           => $totalRevenue,
            'totalExpenses'          => $totalExpenses,
            'totalAssets'            => $totalAssets,
            'totalLiabilities'       => $totalLiabilities,
            'totalInvestments'       => $totalInvestments,
            'retainedProfit'         => $retainedProfit,
            'ownerEquity'            => $ownerEquity,
            'netWorth'               => $netWorth,
            'monthRevenue'           => $monthRevenue,
            'monthExpense'           => $monthExpense,
            'monthNet'               => $monthNet,
            'capitalROI'             => $capitalROI,
            'debtRatio'              => $debtRatio,
            'summary'                => [],
            'financeTotals'          => $financeTotals,
            'currentMonthTotals'     => $currentMonthTotals,
            'monthlyCombined'        => $monthlyCombined,
            'batches'                => $batches,
            'salesByType'            => $salesModel->byType(),
            'expenseTotals'          => [],
            'assets'                 => $totalAssets,
            'liabilities'            => $totalLiabilities,
            'workingCapital'         => $workingCapital,
            'liquidityRatio'         => $liquidityRatio,
            'debtToEquity'           => $debtToEquity,
            'profitMargin'           => $profitMarginPct,
            'overallProfitMargin'    => $profitMarginPct,
            'roi'                    => $roi,
            'healthScore'            => $healthScore,
            'healthLabel'            => $healthLabel,
            'healthClass'            => $healthClass,
            'goingConcernStatus'     => $gcStatus,
            'goingConcernClass'      => $gcClass,
            'goingConcernMessage'    => $gcMsg,
            'decisionRecommendation' => $dr,
            'decisionClass'          => $dc,
            'decisionReason'         => $dreason,
            'decisions'              => $decisions,
            'lossMakingBatches'      => $lossMakingBatches,
            'strongBatches'          => $strongBatches,
            'topBatch'               => $topBatch,
            'worstBatch'             => $worstBatch,
            'trendSignal'            => $trendSignal,
            'risks'                  => $risks,
            'strengths'              => $strengths,
            'recommendations'        => $recommendations,
        ];
    }
}

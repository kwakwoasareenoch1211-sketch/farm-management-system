<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Dashboard.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/VaccinationRecord.php';
require_once BASE_PATH . 'app/models/MedicationRecord.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/Feed.php';

class PoultryController extends Controller
{
    public function dashboard(): void
    {
        $dashboard = new Dashboard();
        $batchModel = new Batch();
        $vaccinationModel = new VaccinationRecord();
        $medicationModel = new MedicationRecord();
        $inventoryItemModel = new InventoryItem();

        // Load inventory summary for unified dashboard
        require_once BASE_PATH . 'app/models/InventorySummary.php';
        $inventorySummary = new InventorySummary();

        $summary = $dashboard->getAdminSummary();
        $batches = $batchModel->all();
        $vaccinationTotals = $vaccinationModel->totals();
        $medicationTotals = $medicationModel->totals();

        // Simplified inventory data
        $inventoryTotals = $inventorySummary->totals();
        $recentInventoryActivities = $inventorySummary->recentInventoryActivities(8);
        $categorySummary = $inventorySummary->categorySummary();
        $topValuedItems = $inventorySummary->topValuedItems(5);
        
        // Separate items by category
        $allItems = $inventoryItemModel->all();
        $feedItems = array_filter($allItems, fn($item) => strtolower($item['category'] ?? '') === 'feed');
        $medicationItems = array_filter($allItems, fn($item) => strtolower($item['category'] ?? '') === 'medication');
        $otherItems = array_filter($allItems, fn($item) => !in_array(strtolower($item['category'] ?? ''), ['feed', 'medication']));
        
        // Get feed-specific data
        $feedModel = new Feed();
        $feedTotals = $feedModel->totals();

        $avgFcr = 0;
        $avgWeight = 0;
        $countFcr = 0;
        $countWeight = 0;

        foreach ($batches as $batch) {
            if (!empty($batch['fcr']) && (float)$batch['fcr'] > 0) {
                $avgFcr += (float)$batch['fcr'];
                $countFcr++;
            }

            if (!empty($batch['latest_average_weight_kg']) && (float)$batch['latest_average_weight_kg'] > 0) {
                $avgWeight += (float)$batch['latest_average_weight_kg'];
                $countWeight++;
            }
        }

        $extraMetrics = [
            'average_fcr' => $countFcr > 0 ? $avgFcr / $countFcr : 0,
            'average_weight_kg' => $countWeight > 0 ? $avgWeight / $countWeight : 0,
            'vaccination_overdue' => (float)($vaccinationTotals['overdue_count'] ?? 0),
            'vaccination_due_soon' => (float)($vaccinationTotals['due_soon_count'] ?? 0),
            'medication_records' => (float)($medicationTotals['total_records'] ?? 0),
            'medication_cost' => (float)($medicationTotals['total_cost'] ?? 0),
        ];

        require_once BASE_PATH . 'app/models/PoultryDashboard.php';
        $poultryDash = new PoultryDashboard();
        $ownerStats = $poultryDash->getOwnerStats();

        $this->view('poultry/dashboard', [
            'pageTitle'      => 'Poultry Operations',
            'sidebarType'    => 'poultry',
            'summary'        => $summary,
            'extraMetrics'   => $extraMetrics,
            'ownerStats'     => $ownerStats,
            // Simplified inventory data
            'inventoryTotals' => $inventoryTotals,
            'recentInventoryActivities' => $recentInventoryActivities,
            'categorySummary' => $categorySummary,
            'topValuedItems' => $topValuedItems,
            // Separated by category
            'feedItems' => $feedItems,
            'medicationItems' => $medicationItems,
            'otherItems' => $otherItems,
            'feedTotals' => $feedTotals,
        ], 'admin');
    }
}
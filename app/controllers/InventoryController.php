<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/InventorySummary.php';
require_once BASE_PATH . 'app/models/Farm.php';

class InventoryController extends Controller
{
    public function dashboard(): void
    {
        $inventoryItem   = new InventoryItem();
        $inventorySummary = new InventorySummary();
        $db = \Database::connect();

        // Stock items from inventory_item table
        $items = $inventoryItem->all();
        $totalItems = count($items);
        $totalStockValue = 0;
        $lowStockItems = [];
        $categoryBreakdown = [];

        foreach ($items as $item) {
            $val = (float)($item['current_stock'] ?? 0) * (float)($item['unit_cost'] ?? 0);
            $totalStockValue += $val;
            $cat = $item['category'] ?? 'Other';
            if (!isset($categoryBreakdown[$cat])) $categoryBreakdown[$cat] = ['count' => 0, 'value' => 0];
            $categoryBreakdown[$cat]['count']++;
            $categoryBreakdown[$cat]['value'] += $val;
            if ((float)($item['current_stock'] ?? 0) <= (float)($item['reorder_level'] ?? 0) && (float)($item['reorder_level'] ?? 0) > 0) {
                $lowStockItems[] = $item;
            }
        }

        // Recent stock movements
        $recentMovements = [];
        try {
            $recentMovements = $db->query("
                SELECT sm.*, ii.item_name, ii.unit_of_measure
                FROM stock_movements sm
                LEFT JOIN inventory_item ii ON ii.id = sm.item_id
                ORDER BY sm.movement_date DESC, sm.id DESC
                LIMIT 10
            ")->fetchAll() ?: [];
        } catch (\Throwable $e) {}

        // Monthly movement totals
        $monthlyIn = 0; $monthlyOut = 0;
        try {
            $row = $db->query("
                SELECT
                    COALESCE(SUM(CASE WHEN movement_type='receipt' THEN quantity ELSE 0 END),0) AS total_in,
                    COALESCE(SUM(CASE WHEN movement_type='issue' THEN quantity ELSE 0 END),0) AS total_out
                FROM stock_movements
                WHERE MONTH(movement_date)=MONTH(CURDATE()) AND YEAR(movement_date)=YEAR(CURDATE())
            ")->fetch();
            $monthlyIn  = (float)($row['total_in']  ?? 0);
            $monthlyOut = (float)($row['total_out'] ?? 0);
        } catch (\Throwable $e) {}

        $this->view('inventory/dashboard', [
            'pageTitle'         => 'Inventory Dashboard',
            'sidebarType'       => 'inventory',
            'items'             => $items,
            'totalItems'        => $totalItems,
            'totalStockValue'   => $totalStockValue,
            'lowStockItems'     => $lowStockItems,
            'categoryBreakdown' => $categoryBreakdown,
            'recentMovements'   => $recentMovements,
            'monthlyIn'         => $monthlyIn,
            'monthlyOut'        => $monthlyOut,
        ], 'admin');
    }

    public function items(): void
    {
        $inventoryItem = new InventoryItem();
        $inventorySummary = new InventorySummary();
        
        $inventoryItems = $inventoryItem->all();
        $totals = $inventorySummary->totals();
        $categorySummary = $inventorySummary->categorySummary();
        $recentActivities = $inventorySummary->recentInventoryActivities(10);

        $this->view('inventory/items/index', [
            'pageTitle' => 'Inventory Items',
            'sidebarType' => 'inventory',
            'inventoryItems' => $inventoryItems,
            'totals' => $totals,
            'categorySummary' => $categorySummary,
            'recentActivities' => $recentActivities,
        ], 'admin');
    }

    public function createItem(): void
    {
        $farmModel = new Farm();
        $farms = method_exists($farmModel, 'all') ? $farmModel->all() : [];

        $this->view('inventory/items/create', [
            'pageTitle' => 'Add Inventory Item',
            'sidebarType' => 'inventory',
            'farms' => $farms,
        ], 'admin');
    }

    public function storeItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
            exit;
        }

        $inventoryItem = new InventoryItem();
        $inventoryItem->create($_POST);

        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }

    public function editItem(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        $inventoryItem = new InventoryItem();
        $farmModel = new Farm();

        $item = $inventoryItem->find($id);
        $farms = method_exists($farmModel, 'all') ? $farmModel->all() : [];

        if (!$item) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
            exit;
        }

        $this->view('inventory/items/edit', [
            'pageTitle' => 'Edit Inventory Item',
            'sidebarType' => 'inventory',
            'item' => $item,
            'farms' => $farms,
        ], 'admin');
    }

    public function updateItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $inventoryItem = new InventoryItem();
            $inventoryItem->update($id, $_POST);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }

    public function deleteItem(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $inventoryItem = new InventoryItem();
            $inventoryItem->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }
}


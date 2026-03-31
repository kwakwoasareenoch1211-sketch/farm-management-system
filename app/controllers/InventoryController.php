<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/InventorySummary.php';
require_once BASE_PATH . 'app/models/Farm.php';

class InventoryController extends Controller
{
    public function dashboard(): void
    {
        // Redirect to poultry dashboard (inventory is integrated there)
        header('Location: ' . rtrim(BASE_URL, '/') . '/poultry');
        exit;
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
            'sidebarType' => 'poultry',
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
            'sidebarType' => 'poultry',
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
            'sidebarType' => 'poultry',
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

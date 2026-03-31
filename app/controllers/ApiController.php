<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';

class ApiController extends Controller
{
    /**
     * Real-time inventory search API
     * Returns JSON array of inventory items matching search query
     */
    public function inventorySearch(): void
    {
        header('Content-Type: application/json');
        
        $query = trim($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        $inventoryModel = new InventoryItem();
        $db = $inventoryModel->getDb();

        // Real-time search with LIKE query
        $stmt = $db->prepare("
            SELECT 
                id,
                item_name,
                category,
                unit_of_measure,
                current_stock,
                unit_cost,
                reorder_level
            FROM inventory_item
            WHERE item_name LIKE :query
               OR category LIKE :query
            ORDER BY 
                current_stock DESC,
                item_name ASC
            LIMIT 20
        ");

        $stmt->execute([':query' => '%' . $query . '%']);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($items);
        exit;
    }

    /**
     * Get single inventory item details
     */
    public function inventoryItem(): void
    {
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid ID']);
            exit;
        }

        $inventoryModel = new InventoryItem();
        $item = $inventoryModel->find($id);

        if (!$item) {
            echo json_encode(['error' => 'Item not found']);
            exit;
        }

        echo json_encode($item);
        exit;
    }

    /**
     * Get all inventory items with stock > 0
     */
    public function inventoryAvailable(): void
    {
        header('Content-Type: application/json');
        
        $inventoryModel = new InventoryItem();
        $db = $inventoryModel->getDb();

        $stmt = $db->query("
            SELECT 
                id,
                item_name,
                category,
                unit_of_measure,
                current_stock,
                unit_cost
            FROM inventory_item
            WHERE current_stock > 0
            ORDER BY item_name ASC
        ");

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        exit;
    }
}

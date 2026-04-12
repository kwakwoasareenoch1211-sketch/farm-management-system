<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/InventorySummary.php';
require_once BASE_PATH . 'app/models/Farm.php';

class InventoryController extends Controller
{
    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    public function dashboard(): void
    {
        $inventoryItem = new InventoryItem();
        $db = \Database::connect();

        $items           = $inventoryItem->all();
        $totalItems      = count($items);
        $totalStockValue = 0;
        $lowStockItems   = [];
        $categoryBreakdown = [];

        foreach ($items as $item) {
            $val = (float)($item['current_stock'] ?? 0) * (float)($item['unit_cost'] ?? 0);
            $totalStockValue += $val;
            $cat = $item['category'] ?? 'Other';
            if (!isset($categoryBreakdown[$cat])) $categoryBreakdown[$cat] = ['count' => 0, 'value' => 0];
            $categoryBreakdown[$cat]['count']++;
            $categoryBreakdown[$cat]['value'] += $val;
            if ((float)($item['reorder_level'] ?? 0) > 0 && (float)($item['current_stock'] ?? 0) <= (float)($item['reorder_level'] ?? 0)) {
                $lowStockItems[] = $item;
            }
        }

        $recentMovements = [];
        $monthlyIn = 0; $monthlyOut = 0;
        try {
            $recentMovements = $db->query("
                SELECT sm.*, ii.item_name, ii.unit_of_measure
                FROM stock_movements sm
                LEFT JOIN inventory_item ii ON ii.id = sm.item_id
                ORDER BY sm.movement_date DESC, sm.id DESC LIMIT 10
            ")->fetchAll() ?: [];

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

    // ─── ITEMS ────────────────────────────────────────────────────────────────

    public function items(): void
    {
        $inventoryItem = new InventoryItem();
        $this->view('inventory/items/index', [
            'pageTitle'      => 'Inventory Items',
            'sidebarType'    => 'inventory',
            'inventoryItems' => $inventoryItem->all(),
        ], 'admin');
    }

    public function createItem(): void
    {
        $farmModel = new Farm();
        $this->view('inventory/items/create', [
            'pageTitle'   => 'Add Inventory Item',
            'sidebarType' => 'inventory',
            'farms'       => $farmModel->all(),
        ], 'admin');
    }

    public function storeItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inventoryItem = new InventoryItem();
            $inventoryItem->create($_POST);
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }

    public function editItem(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $item = (new InventoryItem())->find($id);
        if (!$item) { header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items'); exit; }

        $this->view('inventory/items/edit', [
            'pageTitle'   => 'Edit Inventory Item',
            'sidebarType' => 'inventory',
            'item'        => $item,
            'farms'       => (new Farm())->all(),
        ], 'admin');
    }

    public function updateItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) (new InventoryItem())->update($id, $_POST);
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }

    public function deleteItem(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) (new InventoryItem())->delete($id);
        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/items');
        exit;
    }

    // ─── RECEIPTS ─────────────────────────────────────────────────────────────

    public function receipts(): void
    {
        $db = \Database::connect();
        $rows = [];
        try {
            $rows = $db->query("
                SELECT sr.*, ii.item_name, ii.category, ii.unit_of_measure
                FROM stock_receipts sr
                LEFT JOIN inventory_item ii ON ii.id = sr.inventory_item_id
                ORDER BY sr.receipt_date DESC, sr.id DESC
            ")->fetchAll() ?: [];
        } catch (\Throwable $e) {}

        $this->view('inventory/receipts/index', [
            'pageTitle'   => 'Stock Receipts',
            'sidebarType' => 'inventory',
            'rows'        => $rows,
        ], 'admin');
    }

    public function createReceipt(): void
    {
        require_once BASE_PATH . 'app/models/Supplier.php';
        require_once BASE_PATH . 'app/models/Batch.php';
        $this->view('inventory/receipts/create', [
            'pageTitle'      => 'Receive Stock',
            'sidebarType'    => 'inventory',
            'inventoryItems' => (new InventoryItem())->active(),
            'suppliers'      => (new Supplier())->all(),
            'batches'        => (new Batch())->all(),
        ], 'admin');
    }

    public function storeReceipt(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/receipts');
            exit;
        }

        $db     = \Database::connect();
        $itemId = (int)($_POST['inventory_item_id'] ?? 0);
        $qty    = (float)($_POST['quantity_received'] ?? 0);
        $cost   = (float)($_POST['unit_cost'] ?? 0);
        $date   = $_POST['receipt_date'] ?? date('Y-m-d');

        if ($itemId > 0 && $qty > 0) {
            // 1. Save to stock_receipts
            try {
                $stmt = $db->prepare("
                    INSERT INTO stock_receipts
                        (inventory_item_id, supplier_id, receipt_date, quantity_received, unit_cost, reference_no, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $itemId,
                    !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
                    $date, $qty, $cost,
                    $_POST['reference_no'] ?? null,
                    $_POST['notes'] ?? null,
                ]);
            } catch (\Throwable $e) {}

            // 2. Update inventory_item stock
            (new InventoryItem())->increaseStock($itemId, $qty);

            // 3. Log stock movement
            try {
                $stmt = $db->prepare("
                    INSERT INTO stock_movements (item_id, movement_type, quantity, movement_date, reference_no, notes)
                    VALUES (?, 'receipt', ?, ?, ?, ?)
                ");
                $stmt->execute([$itemId, $qty, $date, $_POST['reference_no'] ?? null, $_POST['notes'] ?? null]);
            } catch (\Throwable $e) {}

            // 4. AUTO-SYNC: If category is 'feed', also create a feed_record
            $item = (new InventoryItem())->find($itemId);
            if ($item && strtolower($item['category'] ?? '') === 'feed') {
                $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
                if ($batchId) {
                    require_once BASE_PATH . 'app/models/Feed.php';
                    $feedData = [
                        'farm_id'     => (int)($item['farm_id'] ?? 1),
                        'batch_id'    => $batchId,
                        'feed_name'   => $item['item_name'],
                        'quantity_kg' => $qty,
                        'unit_cost'   => $cost,
                        'record_date' => $date,
                        'notes'       => 'Auto-created from inventory receipt: ' . ($item['item_name'] ?? ''),
                        'owner_id'    => null,
                        'is_shared'   => 0,
                    ];
                    (new Feed())->create($feedData);
                }
            }

            // 5. AUTO-SYNC: If category is 'medication', also create a medication_record
            if ($item && strtolower($item['category'] ?? '') === 'medication') {
                $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
                if ($batchId) {
                    require_once BASE_PATH . 'app/models/MedicationRecord.php';
                    $medData = [
                        'farm_id'         => (int)($item['farm_id'] ?? 1),
                        'batch_id'        => $batchId,
                        'medication_name' => $item['item_name'],
                        'quantity_used'   => $qty,
                        'unit_cost'       => $cost,
                        'record_date'     => $date,
                        'notes'           => 'Auto-created from inventory receipt: ' . ($item['item_name'] ?? ''),
                        'owner_id'        => null,
                        'is_shared'       => 0,
                    ];
                    (new MedicationRecord())->create($medData);
                }
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/receipts');
        exit;
    }

    // ─── ISSUES ───────────────────────────────────────────────────────────────

    public function issues(): void
    {
        $db = \Database::connect();
        $rows = [];
        try {
            $rows = $db->query("
                SELECT si.*, ii.item_name, ii.category, ii.unit_of_measure
                FROM stock_issues si
                LEFT JOIN inventory_item ii ON ii.id = si.inventory_item_id
                ORDER BY si.issue_date DESC, si.id DESC
            ")->fetchAll() ?: [];
        } catch (\Throwable $e) {}

        $this->view('inventory/issues/index', [
            'pageTitle'   => 'Stock Issues',
            'sidebarType' => 'inventory',
            'rows'        => $rows,
        ], 'admin');
    }

    public function createIssue(): void
    {
        require_once BASE_PATH . 'app/models/Batch.php';
        $this->view('inventory/issues/create', [
            'pageTitle'      => 'Issue Stock',
            'sidebarType'    => 'inventory',
            'inventoryItems' => (new InventoryItem())->active(),
            'batches'        => (new Batch())->all(),
        ], 'admin');
    }

    public function storeIssue(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/issues');
            exit;
        }

        $db     = \Database::connect();
        $itemId = (int)($_POST['inventory_item_id'] ?? 0);
        $qty    = (float)($_POST['quantity_issued'] ?? 0);
        $date   = $_POST['issue_date'] ?? date('Y-m-d');
        $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;

        if ($itemId > 0 && $qty > 0) {
            // 1. Save to stock_issues
            try {
                $stmt = $db->prepare("
                    INSERT INTO stock_issues
                        (inventory_item_id, batch_id, issue_date, quantity_issued, issue_reason, notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $itemId, $batchId, $date, $qty,
                    $_POST['issue_reason'] ?? 'farm_use',
                    $_POST['notes'] ?? null,
                ]);
            } catch (\Throwable $e) {}

            // 2. Decrease inventory stock
            (new InventoryItem())->decreaseStock($itemId, $qty);

            // 3. Log stock movement
            try {
                $stmt = $db->prepare("
                    INSERT INTO stock_movements (item_id, movement_type, quantity, movement_date, notes)
                    VALUES (?, 'issue', ?, ?, ?)
                ");
                $stmt->execute([$itemId, $qty, $date, $_POST['notes'] ?? null]);
            } catch (\Throwable $e) {}

            // 4. AUTO-SYNC: If feed category + batch selected → create feed_record
            $item = (new InventoryItem())->find($itemId);
            if ($item && strtolower($item['category'] ?? '') === 'feed' && $batchId) {
                require_once BASE_PATH . 'app/models/Feed.php';
                (new Feed())->create([
                    'farm_id'     => (int)($item['farm_id'] ?? 1),
                    'batch_id'    => $batchId,
                    'feed_name'   => $item['item_name'],
                    'quantity_kg' => $qty,
                    'unit_cost'   => (float)($item['unit_cost'] ?? 0),
                    'record_date' => $date,
                    'notes'       => 'Auto-created from stock issue: ' . ($item['item_name'] ?? ''),
                    'owner_id'    => null,
                    'is_shared'   => 0,
                ]);
            }

            // 5. AUTO-SYNC: If medication category + batch selected → create medication_record
            if ($item && strtolower($item['category'] ?? '') === 'medication' && $batchId) {
                require_once BASE_PATH . 'app/models/MedicationRecord.php';
                (new MedicationRecord())->create([
                    'farm_id'         => (int)($item['farm_id'] ?? 1),
                    'batch_id'        => $batchId,
                    'medication_name' => $item['item_name'],
                    'quantity_used'   => $qty,
                    'unit_cost'       => (float)($item['unit_cost'] ?? 0),
                    'record_date'     => $date,
                    'notes'           => 'Auto-created from stock issue: ' . ($item['item_name'] ?? ''),
                    'owner_id'        => null,
                    'is_shared'       => 0,
                ]);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/inventory/issues');
        exit;
    }

    // ─── LOW STOCK ────────────────────────────────────────────────────────────

    public function lowStock(): void
    {
        $this->view('inventory/low-stock', [
            'pageTitle'   => 'Low Stock Alert',
            'sidebarType' => 'inventory',
            'items'       => (new InventoryItem())->lowStock(),
        ], 'admin');
    }
}

<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Feed.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/User.php';

class FeedController extends Controller
{
    private Feed $feedModel;
    private Farm $farmModel;
    private Batch $batchModel;
    private InventoryItem $inventoryItemModel;

    public function __construct()
    {
        $this->feedModel = new Feed();
        $this->farmModel = new Farm();
        $this->batchModel = new Batch();
        $this->inventoryItemModel = new InventoryItem();
    }

    public function index(): void
    {
        $records = $this->feedModel->all();
        $totals  = $this->feedModel->totals();

        $this->view('feed/index', [
            'pageTitle'   => 'Feed Management',
            'sidebarType' => 'poultry',
            'records'     => $records,
            'totals'      => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('feed/create_simple', [
            'pageTitle'   => 'Record Feed Usage',
            'sidebarType' => 'poultry',
            'farms'       => $this->farmModel->all(),
            'batches'     => $this->batchModel->all(),
            'owners'      => (new User())->allOwners(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->feedModel->create($_POST);

            // Auto-create owner advance if owner paid personally
            if (!empty($_POST['paid_by']) && is_numeric($_POST['paid_by'])) {
                $cost = (float)($_POST['quantity_kg'] ?? 0) * (float)($_POST['unit_cost'] ?? 0);
                if ($cost > 0) {
                    try {
                        $db = \Database::connect();
                        $db->prepare("INSERT INTO owner_advances (owner_id, source_type, advance_date, amount, description, status) VALUES (?,?,?,?,?,'outstanding')")
                           ->execute([(int)$_POST['paid_by'], 'feed', $_POST['record_date'] ?? date('Y-m-d'), $cost, 'Feed: ' . ($_POST['feed_name'] ?? '')]);
                    } catch (\Throwable $e) {}
                }
            }
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/feed');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->feedModel->find($id);

        if (!$record) {
            die('Feed record not found');
        }

        $this->view('feed/edit_simple', [
            'pageTitle'   => 'Edit Feed Usage',
            'sidebarType' => 'poultry',
            'record'      => $record,
            'farms'       => $this->farmModel->all(),
            'batches'     => $this->batchModel->all(),
            'owners'      => (new User())->allOwners(),
            'currentPaidBy' => $record['paid_by'] ?? null,
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $this->feedModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/feed');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->feedModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/feed');
        exit;
    }

    public function assignToBatch(): void
    {
        // Future feature: assign feed to specific batch
        header('Location: ' . rtrim(BASE_URL, '/') . '/feed');
        exit;
    }
}

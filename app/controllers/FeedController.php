<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Feed.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';

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
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->feedModel->create($_POST);
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

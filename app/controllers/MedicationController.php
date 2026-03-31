<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/MedicationRecord.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';

class MedicationController extends Controller
{
    private MedicationRecord $medicationModel;
    private Farm $farmModel;
    private Batch $batchModel;
    private InventoryItem $inventoryItemModel;

    public function __construct()
    {
        $this->medicationModel = new MedicationRecord();
        $this->farmModel = new Farm();
        $this->batchModel = new Batch();
        $this->inventoryItemModel = new InventoryItem();
    }

    public function index(): void
    {
        $records = $this->medicationModel->all();
        $totals = method_exists($this->medicationModel, 'totals')
            ? $this->medicationModel->totals()
            : [
                'total_records' => 0,
                'total_quantity_used' => 0,
                'total_cost' => 0,
            ];

        $this->view('medication/index', [
            'pageTitle' => 'Medication Records',
            'sidebarType' => 'poultry',
            'records' => $records,
            'totals' => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('medication/create', [
            'pageTitle' => 'Add Medication Record',
            'sidebarType' => 'poultry',
            'farms' => $this->farmModel->all(),
            'batches' => $this->batchModel->all(),
            'inventoryItems' => $this->inventoryItemModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->medicationModel->create($_POST);

            if (!$ok) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/medication/create?error=insufficient_stock');
                exit;
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/medication');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->medicationModel->find($id);

        if (!$record) {
            die('Medication record not found');
        }

        $this->view('medication/edit', [
            'pageTitle' => 'Edit Medication Record',
            'sidebarType' => 'poultry',
            'record' => $record,
            'farms' => $this->farmModel->all(),
            'batches' => $this->batchModel->all(),
            'inventoryItems' => $this->inventoryItemModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $ok = $this->medicationModel->update($id, $_POST);

                if (!$ok) {
                    header('Location: ' . rtrim(BASE_URL, '/') . '/medication/edit?id=' . $id . '&error=insufficient_stock');
                    exit;
                }
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/medication');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->medicationModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/medication');
        exit;
    }
}
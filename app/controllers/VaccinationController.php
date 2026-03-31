<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/VaccinationRecord.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';

class VaccinationController extends Controller
{
    private VaccinationRecord $vaccinationModel;
    private Farm $farmModel;
    private Batch $batchModel;
    private InventoryItem $inventoryItemModel;

    public function __construct()
    {
        $this->vaccinationModel = new VaccinationRecord();
        $this->farmModel = new Farm();
        $this->batchModel = new Batch();
        $this->inventoryItemModel = new InventoryItem();
    }

    public function index(): void
    {
        $records = $this->vaccinationModel->all();
        $totals = $this->vaccinationModel->totals();

        $this->view('vaccination/index', [
            'pageTitle' => 'Vaccination Records',
            'sidebarType' => 'poultry',
            'records' => $records,
            'totals' => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('vaccination/create', [
            'pageTitle' => 'Add Vaccination Record',
            'sidebarType' => 'poultry',
            'farms' => $this->farmModel->all(),
            'batches' => $this->batchModel->all(),
            'inventoryItems' => $this->inventoryItemModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->vaccinationModel->create($_POST);

            if (!$ok) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/vaccination/create?error=insufficient_stock');
                exit;
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/vaccination');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->vaccinationModel->find($id);

        if (!$record) {
            die('Vaccination record not found');
        }

        $this->view('vaccination/edit', [
            'pageTitle' => 'Edit Vaccination Record',
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
                $ok = $this->vaccinationModel->update($id, $_POST);

                if (!$ok) {
                    header('Location: ' . rtrim(BASE_URL, '/') . '/vaccination/edit?id=' . $id . '&error=insufficient_stock');
                    exit;
                }
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/vaccination');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->vaccinationModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/vaccination');
        exit;
    }
}
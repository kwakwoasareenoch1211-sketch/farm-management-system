<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/MedicationRecord.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';
require_once BASE_PATH . 'app/models/User.php';

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
            'owners' => (new User())->allOwners(),
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

            // Auto-create owner advance if owner paid personally
            if (!empty($_POST['paid_by']) && is_numeric($_POST['paid_by'])) {
                $cost = (float)($_POST['quantity_used'] ?? 0) * (float)($_POST['unit_cost'] ?? 0);
                if ($cost > 0) {
                    try {
                        $db = \Database::connect();
                        $db->prepare("INSERT INTO owner_advances (owner_id, source_type, advance_date, amount, description, status) VALUES (?,?,?,?,?,'outstanding')")
                           ->execute([(int)$_POST['paid_by'], 'medication', $_POST['record_date'] ?? date('Y-m-d'), $cost, 'Medication: ' . ($_POST['medication_name'] ?? '')]);
                    } catch (\Throwable $e) {}
                }
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
            'pageTitle'     => 'Edit Medication Record',
            'sidebarType'   => 'poultry',
            'record'        => $record,
            'farms'         => $this->farmModel->all(),
            'batches'       => $this->batchModel->all(),
            'inventoryItems'=> $this->inventoryItemModel->all(),
            'owners'        => (new User())->allOwners(),
            'currentPaidBy' => $record['paid_by'] ?? null,
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
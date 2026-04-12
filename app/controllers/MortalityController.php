<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/MortalityRecord.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/User.php';

class MortalityController extends Controller
{
    private MortalityRecord $mortalityModel;
    private Batch $batchModel;

    public function __construct()
    {
        $this->mortalityModel = new MortalityRecord();
        $this->batchModel = new Batch();
    }

    public function index(): void
    {
        $records = $this->mortalityModel->all();
        $totals = $this->mortalityModel->totals();

        $this->view('mortality/index', [
            'pageTitle' => 'Mortality Records',
            'sidebarType' => 'poultry',
            'records' => $records,
            'totals' => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $batches = $this->batchModel->all();

        $this->view('mortality/create', [
            'pageTitle' => 'Add Mortality Record',
            'sidebarType' => 'poultry',
            'batches' => $batches,
            'owners' => (new User())->allOwners(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->mortalityModel->create($_POST);
            if (!$ok) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/mortality/create?error=failed');
                exit;
            }
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/mortality');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $record = $this->mortalityModel->find($id);
        $batches = $this->batchModel->all();

        if (!$record) {
            die('Mortality record not found');
        }

        $this->view('mortality/edit', [
            'pageTitle' => 'Edit Mortality Record',
            'sidebarType' => 'poultry',
            'record' => $record,
            'batches' => $batches,
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $this->mortalityModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/mortality');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->mortalityModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/mortality');
        exit;
    }
}
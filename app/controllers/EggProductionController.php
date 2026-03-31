<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/EggProductionRecord.php';
require_once BASE_PATH . 'app/models/Batch.php';

class EggProductionController extends Controller
{
    private EggProductionRecord $eggModel;
    private Batch $batchModel;

    public function __construct()
    {
        $this->eggModel = new EggProductionRecord();
        $this->batchModel = new Batch();
    }

    public function index(): void
    {
        $records = $this->eggModel->all();
        $totals = $this->eggModel->totals();

        $this->view('egg-production/index', [
            'pageTitle' => 'Egg Production',
            'sidebarType' => 'poultry',
            'records' => $records,
            'totals' => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $batches = $this->batchModel->all();

        $this->view('egg-production/create', [
            'pageTitle' => 'Add Egg Production Record',
            'sidebarType' => 'poultry',
            'batches' => $batches,
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->eggModel->create($_POST);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/egg-production');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $record = $this->eggModel->find($id);
        $batches = $this->batchModel->all();

        if (!$record) {
            die('Egg production record not found');
        }

        $this->view('egg-production/edit', [
            'pageTitle' => 'Edit Egg Production Record',
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
                $this->eggModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/egg-production');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->eggModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/egg-production');
        exit;
    }
}
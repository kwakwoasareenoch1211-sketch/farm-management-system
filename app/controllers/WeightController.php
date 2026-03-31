<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/WeightRecord.php';
require_once BASE_PATH . 'app/models/Batch.php';

class WeightController extends Controller
{
    private WeightRecord $weightModel;
    private Batch $batchModel;

    public function __construct()
    {
        $this->weightModel = new WeightRecord();
        $this->batchModel = new Batch();
    }

    public function index(): void
    {
        $records = $this->weightModel->all();
        $totals = $this->weightModel->totals();

        $this->view('weights/index', [
            'pageTitle' => 'Weight Tracking',
            'sidebarType' => 'poultry',
            'records' => $records,
            'totals' => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $batches = $this->batchModel->all();

        $this->view('weights/create', [
            'pageTitle' => 'Add Weight Record',
            'sidebarType' => 'poultry',
            'batches' => $batches,
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->weightModel->create($_POST);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/weights');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $record = $this->weightModel->find($id);
        $batches = $this->batchModel->all();

        if (!$record) {
            die('Weight record not found');
        }

        $this->view('weights/edit', [
            'pageTitle' => 'Edit Weight Record',
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
                $this->weightModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/weights');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->weightModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/weights');
        exit;
    }
}
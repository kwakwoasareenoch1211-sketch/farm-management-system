<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Batch.php';

class BatchController extends Controller
{
    public function index(): void
    {
        $batchModel = new Batch();

        $this->view('batches/index', [
            'pageTitle' => 'Animal Batches',
            'sidebarType' => 'poultry',
            'batches' => $batchModel->all()
        ], 'admin');
    }

    public function create(): void
    {
        require_once BASE_PATH . 'app/models/Farm.php';
        require_once BASE_PATH . 'app/models/AnimalType.php';
        require_once BASE_PATH . 'app/models/User.php';

        $farmModel       = new Farm();
        $animalTypeModel = new AnimalType();
        $db = \Database::connect();

        // Get housing units
        $housingUnits = [];
        try {
            $housingUnits = $db->query("SELECT id, unit_name, capacity FROM housing_units WHERE status='active' ORDER BY unit_name")->fetchAll() ?: [];
        } catch (\Throwable $e) {}

        $this->view('batches/create', [
            'pageTitle'    => 'Create Batch',
            'sidebarType'  => 'poultry',
            'farms'        => $farmModel->all(),
            'animalTypes'  => $animalTypeModel->all(),
            'housingUnits' => $housingUnits,
            'owners'       => (new User())->allOwners(),
        ], 'admin');
    }

    public function store(): void
    {
        $batchModel = new Batch();

        $data = [
            'batch_name' => $_POST['batch_name'] ?? '',
            'animal_type_id' => $_POST['animal_type_id'] ?? null,
            'housing_unit_id' => $_POST['housing_unit_id'] ?? null,
            'farm_id' => $_POST['farm_id'] ?? null,
            'owner_id' => !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null,
            'production_purpose' => $_POST['production_purpose'] ?? 'mixed',
            'bird_subtype' => $_POST['bird_subtype'] ?? null,
            'breed' => $_POST['breed'] ?? null,
            'source_name' => $_POST['source_name'] ?? null,
            'purchase_date' => $_POST['purchase_date'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'expected_end_date' => $_POST['expected_end_date'] ?? null,
            'initial_quantity' => $_POST['initial_quantity'] ?? 0,
            'initial_unit_cost' => $_POST['initial_unit_cost'] ?? 0,
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? null,
        ];

        $batchModel->create($data);

        header('Location: ' . rtrim(BASE_URL, '/') . '/batches');
        exit;
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        $batchModel = new Batch();

        $this->view('batches/view', [
            'pageTitle' => 'Batch Details',
            'sidebarType' => 'poultry',
            'batch' => $batchModel->find($id)
        ], 'admin');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $batchModel = new Batch();
        $batch = $batchModel->find($id);

        if (!$batch) {
            die('Batch not found');
        }

        require_once BASE_PATH . 'app/models/Farm.php';
        require_once BASE_PATH . 'app/models/AnimalType.php';

        $farmModel       = new Farm();
        $animalTypeModel = new AnimalType();

        $this->view('batches/edit', [
            'pageTitle'   => 'Edit Batch',
            'sidebarType' => 'poultry',
            'batch'       => $batch,
            'farms'       => $farmModel->all(),
            'animalTypes' => $animalTypeModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $batchModel = new Batch();
                $batchModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/batches');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        $batchModel = new Batch();
        $batchModel->delete($id);

        header('Location: ' . rtrim(BASE_URL, '/') . '/batches');
        exit;
    }
}
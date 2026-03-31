<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/LossWriteoff.php';

class LossWriteoffController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new LossWriteoff();
    }

    public function index(): void
    {
        $losses = $this->model->all();
        $totals = $this->model->totals();
        $byType = $this->model->byType();
        $monthlyTrend = $this->model->monthlyTrend(6);
        $unrecordedMortality = $this->model->getMortalityLosses();
        $impactAnalysis = $this->model->getLossImpactAnalysis();
        $lossTrends = $this->model->getLossTrends(30);

        $this->view('losses/index', [
            'losses' => $losses,
            'totals' => $totals,
            'byType' => $byType,
            'monthlyTrend' => $monthlyTrend,
            'unrecordedMortality' => $unrecordedMortality,
            'impactAnalysis' => $impactAnalysis,
            'lossTrends' => $lossTrends,
        ]);
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $loss = $this->model->find($id);

        if (!$loss) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $this->view('losses/view', ['loss' => $loss]);
    }

    public function create(): void
    {
        $this->view('losses/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $ok = $this->model->create($_POST);

        if ($ok) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
        } else {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses/create');
        }
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $loss = $this->model->find($id);

        if (!$loss) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $this->view('losses/edit', ['loss' => $loss]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->update($id, $_POST);

        if ($ok) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
        } else {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses/edit?id=' . $id);
        }
        exit;
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);

        header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
        exit;
    }

    public function recordMortality(): void
    {
        error_log("recordMortality called - Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not a POST request, redirecting to losses");
            header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
            exit;
        }

        $mortalityId = (int)($_POST['mortality_id'] ?? 0);
        error_log("Mortality ID: {$mortalityId}");
        
        $ok = $this->model->recordMortalityLoss($mortalityId);
        error_log("Record result: " . ($ok ? 'success' : 'failed'));

        header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
        exit;
    }
}

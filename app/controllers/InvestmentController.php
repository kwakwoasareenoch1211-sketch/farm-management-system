<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Investment.php';
require_once BASE_PATH . 'app/models/Farm.php';

class InvestmentController extends Controller
{
    private Investment $investmentModel;
    private Farm $farmModel;

    public function __construct()
    {
        $this->investmentModel = new Investment();
        $this->farmModel       = new Farm();
    }

    public function index(): void
    {
        $this->view('investments/index', [
            'pageTitle'  => 'Investment Portfolio',
            'sidebarType'=> 'financial',
            'records'    => $this->investmentModel->all(),
            'totals'     => $this->investmentModel->totals(),
            'byType'     => $this->investmentModel->byType(),
        ], 'admin');
    }

    public function show(): void
    {
        $id     = (int)($_GET['id'] ?? 0);
        $record = $this->investmentModel->find($id);
        if (!$record) die('Investment not found');

        $this->view('investments/view', [
            'pageTitle'  => 'Investment Details — ' . htmlspecialchars($record['title'] ?? ''),
            'sidebarType'=> 'financial',
            'record'     => $record,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('investments/create', [
            'pageTitle'  => 'Add Investment',
            'sidebarType'=> 'financial',
            'farms'      => $this->farmModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Auto-compute expected_return from business performance if not manually set
            if (empty($_POST['expected_return']) || (float)$_POST['expected_return'] <= 0) {
                $type      = $_POST['investment_type'] ?? 'other';
                $amount    = (float)($_POST['amount'] ?? 0);
                $lifeYears = (int)($_POST['useful_life_years'] ?? 3);
                if ($amount > 0) {
                    $perf = $this->investmentModel->businessPerformance($type, $amount, max(1, $lifeYears));
                    $_POST['expected_return'] = $perf['suggested_return'];
                    // Inject investor share into notes if investor name provided
                    if (!empty($_POST['investor_name'])) {
                        $share = $perf['suggested_investor_share'];
                        $_POST['notes'] = trim(($_POST['notes'] ?? '') . "\ninvestor_share:{$share}\ninvestor_name:{$_POST['investor_name']}");
                    }
                }
            }
            $this->investmentModel->create($_POST);
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/investments');
        exit;
    }

    /**
     * AJAX: return business performance data for a given type + amount.
     * Called by the create/edit form live calculator.
     */
    public function performance(): void
    {
        // Must output JSON only — no layout
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache');

        try {
            $type      = trim($_GET['type']   ?? 'eggs');
            $amount    = (float)($_GET['amount'] ?? 0);
            $lifeYears = max(1, (int)($_GET['life'] ?? 3));

            if ($amount <= 0) {
                echo json_encode(['error' => 'Amount required']);
                exit;
            }

            $data = $this->investmentModel->businessPerformance($type, $amount, $lifeYears);
            echo json_encode($data);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function edit(): void
    {
        $id     = (int)($_GET['id'] ?? 0);
        $record = $this->investmentModel->find($id);
        if (!$record) die('Investment not found');

        $this->view('investments/edit', [
            'pageTitle'  => 'Edit Investment',
            'sidebarType'=> 'financial',
            'record'     => $record,
            'farms'      => $this->farmModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                if (empty($_POST['expected_return']) || (float)$_POST['expected_return'] <= 0) {
                    $type      = $_POST['investment_type'] ?? 'other';
                    $amount    = (float)($_POST['amount'] ?? 0);
                    $lifeYears = (int)($_POST['useful_life_years'] ?? 3);
                    if ($amount > 0) {
                        $perf = $this->investmentModel->businessPerformance($type, $amount, max(1, $lifeYears));
                        $_POST['expected_return'] = $perf['suggested_return'];
                    }
                }
                $this->investmentModel->update($id, $_POST);
            }
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/investments');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) $this->investmentModel->delete($id);
        header('Location: ' . rtrim(BASE_URL, '/') . '/investments');
        exit;
    }
}

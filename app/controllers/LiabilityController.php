<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Liability.php';
require_once BASE_PATH . 'app/models/Farm.php';

class LiabilityController extends Controller
{
    private Liability $liabilityModel;
    private Farm $farmModel;

    public function __construct()
    {
        $this->liabilityModel = new Liability();
        $this->farmModel = new Farm();
    }

    public function index(): void
    {
        require_once BASE_PATH . 'app/models/Expense.php';
        
        $records = $this->liabilityModel->all();
        $totals = $this->liabilityModel->totals();
        $upcomingDue = $this->liabilityModel->upcomingDue(30);
        $overdue = $this->liabilityModel->overdue();
        
        // Get unpaid expenses
        $expenseModel = new Expense();
        $unpaidExpenses = $expenseModel->unpaid();

        $this->view('liabilities/index', [
            'pageTitle' => 'Liabilities Management',
            'sidebarType' => 'financial',
            'records' => $records,
            'totals' => $totals,
            'upcomingDue' => $upcomingDue,
            'overdue' => $overdue,
            'unpaidExpenses' => $unpaidExpenses,
        ], 'admin');
    }

    public function create(): void
    {
        $farms = $this->farmModel->all();

        $this->view('liabilities/create', [
            'pageTitle' => 'Add Liability',
            'sidebarType' => 'financial',
            'farms' => $farms,
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->liabilityModel->create($_POST);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
            exit;
        }
        
        $record = $this->liabilityModel->find($id);

        if (!$record) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
            exit;
        }

        $payments = $this->liabilityModel->getPayments($id);

        $this->view('liabilities/view', [
            'pageTitle' => 'View Liability',
            'sidebarType' => 'financial',
            'record' => $record,
            'payments' => $payments,
        ], 'admin');
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $record = $this->liabilityModel->find($id);

        if (!$record) {
            die('Liability record not found');
        }

        $farms = $this->farmModel->all();

        $this->view('liabilities/edit', [
            'pageTitle' => 'Edit Liability',
            'sidebarType' => 'financial',
            'record' => $record,
            'farms' => $farms,
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $this->liabilityModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->liabilityModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
        exit;
    }

    public function addPayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $liabilityId = (int)($_POST['liability_id'] ?? 0);

            if ($liabilityId > 0) {
                $this->liabilityModel->addPayment($liabilityId, $_POST);
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities/view?id=' . $liabilityId);
            exit;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/liabilities');
        exit;
    }
}

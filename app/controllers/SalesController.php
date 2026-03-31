<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Sales.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/Customer.php';
require_once BASE_PATH . 'app/models/SalesIntelligence.php';

class SalesController extends Controller
{
    private Sales $salesModel;
    private Farm $farmModel;
    private Batch $batchModel;
    private Customer $customerModel;
    private SalesIntelligence $intel;

    public function __construct()
    {
        $this->salesModel   = new Sales();
        $this->farmModel    = new Farm();
        $this->batchModel   = new Batch();
        $this->customerModel= new Customer();
        $this->intel        = new SalesIntelligence();
    }

    public function dashboard(): void
    {
        $records      = $this->salesModel->all();
        $totals       = $this->salesModel->totals();
        $salesByType  = $this->salesModel->byType();
        $topCustomers = $this->salesModel->topCustomers(5);

        // Intelligence layer
        $projections  = $this->intel->projections();
        $monthlyTrend = $this->intel->monthlyTrend();
        $debtPayoff   = $this->intel->debtPayoff();
        $pricing      = $this->intel->pricingIntelligence();
        $growth       = $this->intel->growthStrategy();

        $this->view('sales/dashboard', [
            'pageTitle'    => 'Sales Intelligence Dashboard',
            'sidebarType'  => 'sales',
            'records'      => $records,
            'totals'       => $totals,
            'salesByType'  => $salesByType,
            'topCustomers' => $topCustomers,
            'projections'  => $projections,
            'monthlyTrend' => $monthlyTrend,
            'debtPayoff'   => $debtPayoff,
            'pricing'      => $pricing,
            'growth'       => $growth,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('sales/create', [
            'pageTitle' => 'Add Sale',
            'sidebarType' => 'sales',
            'farms' => $this->farmModel->all(),
            'batches' => $this->batchModel->all(),
            'customers' => $this->customerModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->salesModel->create($_POST);

            if (!$ok) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/sales/create?error=invalid_selection');
                exit;
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/sales');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->salesModel->find($id);

        if (!$record) {
            die('Sale record not found');
        }

        $this->view('sales/edit', [
            'pageTitle' => 'Edit Sale',
            'sidebarType' => 'sales',
            'record' => $record,
            'farms' => $this->farmModel->all(),
            'batches' => $this->batchModel->all(),
            'customers' => $this->customerModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $ok = $this->salesModel->update($id, $_POST);

                if (!$ok) {
                    header('Location: ' . rtrim(BASE_URL, '/') . '/sales/edit?id=' . $id . '&error=invalid_selection');
                    exit;
                }
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/sales');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->salesModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/sales');
        exit;
    }
}
<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Expense.php';
require_once BASE_PATH . 'app/models/Batch.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/ExpenseCategory.php';
require_once BASE_PATH . 'app/models/Supplier.php';
require_once BASE_PATH . 'app/models/User.php';

class ExpenseController extends Controller
{
    private Expense $expenseModel;
    private Batch $batchModel;
    private Farm $farmModel;
    private ExpenseCategory $expenseCategoryModel;
    private Supplier $supplierModel;
    private User $userModel;

    public function __construct()
    {
        $this->expenseModel = new Expense();
        $this->batchModel = new Batch();
        $this->farmModel = new Farm();
        $this->expenseCategoryModel = new ExpenseCategory();
        $this->supplierModel = new Supplier();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $records = $this->expenseModel->all();
        $totals  = $this->expenseModel->totals();

        $this->view('expenses/index', [
            'pageTitle'   => 'Business Expenses',
            'sidebarType' => 'financial',
            'records'     => $records,
            'totals'      => $totals,
        ], 'admin');
    }

    public function create(): void
    {
        $owners = $this->userModel->allOwners();
        $this->view('expenses/create', [
            'pageTitle'   => 'Add Expense',
            'sidebarType' => 'financial',
            'batches'     => $this->batchModel->all(),
            'farms'       => $this->farmModel->all(),
            'categories'  => $this->expenseCategoryModel->all(),
            'suppliers'   => $this->supplierModel->all(),
            'users'       => $owners,
            'owners'      => $owners,
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Auto-categorize if no category selected
            if (empty($_POST['category_id'])) {
                $desc = $_POST['description'] ?? '';
                $autoCategory = $this->expenseModel->autoCategory($desc);
                $catId = $this->expenseCategoryModel->findOrCreate($autoCategory);
                $_POST['category_id'] = $catId;
            }

            $ok = $this->expenseModel->create($_POST);

            // AUTO-CREATE LIABILITY: if an owner paid personally, business owes them back
            if ($ok && !empty($_POST['paid_by']) && is_numeric($_POST['paid_by'])) {
                $this->createOwnerAdvance(
                    (int)$_POST['paid_by'],
                    'expense',
                    (float)($_POST['amount'] ?? 0),
                    $_POST['description'] ?? 'Expense',
                    $_POST['expense_date'] ?? date('Y-m-d')
                );
            }
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/expenses');
        exit;
    }

    private function createOwnerAdvance(int $ownerId, string $sourceType, float $amount, string $desc, string $date): void
    {
        try {
            $db = \Database::connect();
            $db->prepare("
                INSERT INTO owner_advances (owner_id, source_type, advance_date, amount, description, status)
                VALUES (?, ?, ?, ?, ?, 'outstanding')
            ")->execute([$ownerId, $sourceType, $date, $amount, $desc]);
        } catch (\Throwable $e) {
            error_log('createOwnerAdvance error: ' . $e->getMessage());
        }
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $record = $this->expenseModel->find($id);

        if (!$record) {
            die('Expense record not found');
        }

        $batches = $this->batchModel->all();
        $farms = $this->farmModel->all();
        $categories = $this->expenseCategoryModel->all();
        $suppliers = $this->supplierModel->all();
        $users = $this->userModel->all();

        $this->view('expenses/edit', [
            'pageTitle' => 'Edit Expense',
            'sidebarType' => 'financial',
            'record' => $record,
            'batches' => $batches,
            'farms' => $farms,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'users' => $users,
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $this->expenseModel->update($id, $_POST);
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/expenses');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->expenseModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/expenses');
        exit;
    }
}
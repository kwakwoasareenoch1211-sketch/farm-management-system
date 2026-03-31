<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Customer.php';
require_once BASE_PATH . 'app/models/Farm.php';

class CustomerController extends Controller
{
    private Customer $customerModel;
    private Farm $farmModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
        $this->farmModel = new Farm();
    }

    public function index(): void
    {
        $customers = $this->customerModel->all();

        $this->view('customers/index', [
            'pageTitle' => 'Customers',
            'sidebarType' => 'sales',
            'customers' => $customers,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('customers/create', [
            'pageTitle' => 'Add Customer',
            'sidebarType' => 'sales',
            'farms' => $this->farmModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->customerModel->create($_POST);

            if (!$ok) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/customers/create?error=invalid_selection');
                exit;
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/customers');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            die('Customer not found');
        }

        $this->view('customers/edit', [
            'pageTitle' => 'Edit Customer',
            'sidebarType' => 'sales',
            'customer' => $customer,
            'farms' => $this->farmModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $ok = $this->customerModel->update($id, $_POST);

                if (!$ok) {
                    header('Location: ' . rtrim(BASE_URL, '/') . '/customers/edit?id=' . $id . '&error=invalid_selection');
                    exit;
                }
            }
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/customers');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->customerModel->delete($id);
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/customers');
        exit;
    }
}
<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Capital.php';
require_once BASE_PATH . 'app/models/Farm.php';

class CapitalController extends Controller
{
    private Capital $capitalModel;
    private Farm $farmModel;

    public function __construct()
    {
        $this->capitalModel = new Capital();
        $this->farmModel    = new Farm();
    }

    public function index(): void
    {
        $this->view('capital/index', [
            'pageTitle'  => 'Capital Management',
            'sidebarType'=> 'financial',
            'records'    => $this->capitalModel->all(),
            'totals'     => $this->capitalModel->totals(),
            'byType'     => $this->capitalModel->byType(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('capital/create', [
            'pageTitle'  => 'Add Capital Entry',
            'sidebarType'=> 'financial',
            'farms'      => $this->farmModel->all(),
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->capitalModel->create($_POST);
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/capital');
        exit;
    }

    public function edit(): void
    {
        $id     = (int)($_GET['id'] ?? 0);
        $record = $this->capitalModel->find($id);

        if (!$record) die('Capital entry not found');

        $this->view('capital/edit', [
            'pageTitle'  => 'Edit Capital Entry',
            'sidebarType'=> 'financial',
            'record'     => $record,
            'farms'      => $this->farmModel->all(),
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) $this->capitalModel->update($id, $_POST);
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/capital');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) $this->capitalModel->delete($id);
        header('Location: ' . rtrim(BASE_URL, '/') . '/capital');
        exit;
    }
}

<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/Capital.php';
require_once BASE_PATH . 'app/models/Farm.php';
require_once BASE_PATH . 'app/models/User.php';

class CapitalController extends Controller
{
    private Capital $capitalModel;
    private Farm    $farmModel;
    private User    $userModel;

    public function __construct()
    {
        $this->capitalModel = new Capital();
        $this->farmModel    = new Farm();
        $this->userModel    = new User();
    }

    public function index(): void
    {
        $this->view('capital/index', [
            'pageTitle'      => 'Capital Management',
            'sidebarType'    => 'financial',
            'records'        => $this->capitalModel->all(),
            'totals'         => $this->capitalModel->totals(),
            'byType'         => $this->capitalModel->byType(),
            'byContributor'  => $this->capitalModel->byContributor(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('capital/create', [
            'pageTitle'   => 'Add Capital Entry',
            'sidebarType' => 'financial',
            'farms'       => $this->farmModel->all(),
            'owners'      => $this->userModel->allOwners(),
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
            'pageTitle'   => 'Edit Capital Entry',
            'sidebarType' => 'financial',
            'record'      => $record,
            'farms'       => $this->farmModel->all(),
            'owners'      => $this->userModel->allOwners(),
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

    public function advances(): void
    {
        $db = \Database::connect();

        $advances = $db->query("
            SELECT oa.*, u.full_name
            FROM owner_advances oa
            LEFT JOIN users u ON u.id = oa.owner_id
            ORDER BY oa.advance_date DESC, oa.id DESC
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $ownerSummary = $db->query("
            SELECT u.id, u.full_name,
                COUNT(oa.id) AS advance_count,
                COALESCE(SUM(oa.amount), 0) AS total_advanced,
                COALESCE(SUM(oa.repaid_amount), 0) AS total_repaid
            FROM users u
            LEFT JOIN owner_advances oa ON oa.owner_id = u.id
            WHERE u.role IN ('owner','admin')
            GROUP BY u.id, u.full_name
            ORDER BY total_advanced DESC
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('capital/advances', [
            'pageTitle'    => 'Owner Advances',
            'sidebarType'  => 'financial',
            'advances'     => $advances,
            'ownerSummary' => $ownerSummary,
        ], 'admin');
    }

    public function repayAdvance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['advance_id'] ?? 0);
            $amount = (float)($_POST['repay_amount'] ?? 0);

            if ($id > 0 && $amount > 0) {
                $db = \Database::connect();
                $adv = $db->prepare("SELECT * FROM owner_advances WHERE id=? LIMIT 1");
                $adv->execute([$id]);
                $row = $adv->fetch(\PDO::FETCH_ASSOC);

                if ($row) {
                    $newRepaid = min((float)$row['amount'], (float)$row['repaid_amount'] + $amount);
                    $status = $newRepaid >= (float)$row['amount'] ? 'repaid' : 'partial';
                    $db->prepare("UPDATE owner_advances SET repaid_amount=?, status=?, repaid_date=? WHERE id=?")
                       ->execute([$newRepaid, $status, date('Y-m-d'), $id]);
                }
            }
        }
        header('Location: ' . rtrim(BASE_URL, '/') . '/capital/advances');
        exit;
    }
}

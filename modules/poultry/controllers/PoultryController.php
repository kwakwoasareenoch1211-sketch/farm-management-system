<?php

require_once BASE_PATH . 'app/models/PoultryDashboard.php';
require_once BASE_PATH . 'app/models/InventoryItem.php';

class PoultryController extends Controller
{
    public function dashboard(): void
    {
        $model = new PoultryDashboard();

        $this->view('poultry/dashboard', [
            'pageTitle'    => 'Poultry Dashboard',
            'sidebarType'  => 'poultry',
            'summary'      => $model->getSummary(),
            'extraMetrics' => $model->getExtraMetrics(),
            'lowStockItems'=> $model->getLowStockItems(),
        ], 'admin');
    }
}

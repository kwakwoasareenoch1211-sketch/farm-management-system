<?php

require_once BASE_PATH . 'app/core/Controller.php';

class ForecastController extends Controller
{
    public function index(): void
    {
        $this->view('forecasts/index', [
            'pageTitle' => 'Forecasts',
            'sidebarType' => 'economic',
        ]);
    }
}
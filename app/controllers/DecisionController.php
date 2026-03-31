<?php

require_once BASE_PATH . 'app/core/Controller.php';

class DecisionController extends Controller
{
    public function index(): void
    {
        $this->view('decisions/index', [
            'pageTitle' => 'Decision Support',
            'sidebarType' => 'economic',
        ]);
    }
}
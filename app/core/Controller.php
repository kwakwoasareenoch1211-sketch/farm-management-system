<?php

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'admin')
    {
        extract($data);

        $viewPath = BASE_PATH . 'app/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }

        // Load layout
        $layoutPath = BASE_PATH . 'app/views/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            die("Layout not found: " . $layout);
        }

        require $layoutPath;
    }
}

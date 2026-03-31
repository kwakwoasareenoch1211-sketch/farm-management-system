<?php
require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/core/Auth.php';

class Router
{
    private array $routes;
    private array $publicRoutes = ['login', '/'];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $uri): void
    {
        $path = trim($uri, '/');

        if ($path === '') {
            $path = '/';
        }

        // DEBUG - Remove after fixing
        error_log("Router Debug - Path: '$path'");
        error_log("Router Debug - Routes available: " . implode(', ', array_keys($this->routes)));
        
        // Redirect root to login if not authenticated, or to admin if authenticated
        if ($path === '/') {
            if (Auth::check()) {
                header('Location: ' . rtrim(BASE_URL, '/') . '/admin');
            } else {
                header('Location: ' . rtrim(BASE_URL, '/') . '/login');
            }
            exit;
        }

        if (!isset($this->routes[$path])) {
            http_response_code(404);
            echo "404 - Route not found<br>";
            echo "Looking for: '$path'<br>";
            echo "Available routes: " . implode(', ', array_keys($this->routes));
            die();
        }

        // Check authentication for protected routes
        if (!in_array($path, $this->publicRoutes, true)) {
            Auth::requireAuth();
        }

        [$controllerName, $method] = $this->routes[$path];

        $controllerFile = BASE_PATH . 'app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            die('Controller file not found: ' . $controllerFile);
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die('Controller class not found: ' . $controllerName);
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            die("Method '{$method}' not found in controller '{$controllerName}'");
        }

        $controller->$method();
    }
}

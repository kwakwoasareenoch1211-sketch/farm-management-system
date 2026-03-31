<?php
// Test losses route directly

define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASE_URL', 'http://localhost/farmapp/');

require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';
require_once BASE_PATH . 'app/core/Router.php';

$router = new Router();

echo "Testing losses route...\n\n";

// Load routes
$routes = require BASE_PATH . 'app/Router/web.php';

echo "Losses routes found:\n";
foreach ($routes as $path => $handler) {
    if (strpos($path, 'losses') !== false) {
        echo "  - $path => {$handler[0]}::{$handler[1]}\n";
    }
}

echo "\nTrying to dispatch 'losses'...\n";

try {
    $router->dispatch('losses');
    echo "✓ Route dispatched successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

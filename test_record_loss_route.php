<?php
/**
 * Test Record Loss Route
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';

echo "=== TESTING RECORD LOSS ROUTE ===\n\n";

// Load routes
$routes = require BASE_PATH . 'app/Router/web.php';

echo "1. Checking if route exists...\n";
$routeKey = 'losses/recordMortality';
if (isset($routes[$routeKey])) {
    echo "   ✓ Route '{$routeKey}' exists\n";
    echo "   Controller: {$routes[$routeKey][0]}\n";
    echo "   Method: {$routes[$routeKey][1]}\n";
} else {
    echo "   ✗ Route '{$routeKey}' NOT FOUND\n";
}

// Check controller file
echo "\n2. Checking controller file...\n";
$controllerFile = BASE_PATH . 'app/controllers/LossWriteoffController.php';
if (file_exists($controllerFile)) {
    echo "   ✓ Controller file exists\n";
    require_once $controllerFile;
    
    if (class_exists('LossWriteoffController')) {
        echo "   ✓ Controller class exists\n";
        
        $controller = new LossWriteoffController();
        if (method_exists($controller, 'recordMortality')) {
            echo "   ✓ recordMortality method exists\n";
        } else {
            echo "   ✗ recordMortality method NOT FOUND\n";
        }
    } else {
        echo "   ✗ Controller class NOT FOUND\n";
    }
} else {
    echo "   ✗ Controller file NOT FOUND\n";
}

// Test URL construction
echo "\n3. Testing URL construction...\n";
echo "   BASE_URL: " . BASE_URL . "\n";
$url = rtrim(BASE_URL, '/') . '/losses/recordMortality';
echo "   Full URL: {$url}\n";
echo "   Expected path after BASE_URL: losses/recordMortality\n";

// Check all losses routes
echo "\n4. All losses routes:\n";
foreach ($routes as $path => $handler) {
    if (strpos($path, 'losses') === 0) {
        echo "   - {$path} => {$handler[0]}::{$handler[1]}\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";

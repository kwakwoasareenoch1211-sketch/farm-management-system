<?php
/**
 * Test if routes are loading correctly
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';

echo "=== TESTING ROUTE LOADING ===\n\n";

$routes = require BASE_PATH . 'app/Router/web.php';

echo "Total routes loaded: " . count($routes) . "\n\n";

echo "Checking liabilities routes:\n";
foreach ($routes as $path => $handler) {
    if (strpos($path, 'liabilities') === 0) {
        echo "  {$path} => {$handler[0]}::{$handler[1]}\n";
    }
}

echo "\nSpecific check for 'liabilities/view':\n";
if (isset($routes['liabilities/view'])) {
    echo "  Controller: {$routes['liabilities/view'][0]}\n";
    echo "  Method: {$routes['liabilities/view'][1]}\n";
} else {
    echo "  Route NOT FOUND!\n";
}

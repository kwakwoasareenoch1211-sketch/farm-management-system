<?php
$routes = require __DIR__ . '/app/Router/web.php';

echo "=== Testing Sales Routes ===\n\n";

$salesRoutes = [
    'sales',
    'sales/create',
    'sales/store',
    'sales/edit',
    'sales/update',
    'sales/delete'
];

foreach ($salesRoutes as $route) {
    if (isset($routes[$route])) {
        echo "✓ $route => {$routes[$route][0]}::{$routes[$route][1]}\n";
    } else {
        echo "✗ $route => NOT FOUND\n";
    }
}

echo "\nTotal routes in file: " . count($routes) . "\n";

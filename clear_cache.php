<?php
// Clear PHP opcode cache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✓ APC cache cleared\n";
}

// Verify routes file
$routes = require __DIR__ . '/app/Router/web.php';

echo "\n=== Sales Routes Check ===\n";
$salesRoutes = array_filter(array_keys($routes), fn($k) => str_starts_with($k, 'sales'));
foreach ($salesRoutes as $route) {
    echo "✓ $route => {$routes[$route][0]}::{$routes[$route][1]}\n";
}

echo "\nTotal routes: " . count($routes) . "\n";
echo "\nCache cleared! Please refresh your browser.\n";

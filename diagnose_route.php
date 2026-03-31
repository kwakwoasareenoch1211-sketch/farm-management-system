<?php
/**
 * Diagnose Route Issue
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

echo "<h1>Route Diagnostics</h1>";

echo "<h2>1. Environment</h2>";
echo "<pre>";
echo "BASE_URL: " . BASE_URL . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "</pre>";

echo "<h2>2. Routes</h2>";
$routes = require BASE_PATH . 'app/Router/web.php';
echo "<pre>";
foreach ($routes as $path => $handler) {
    if (strpos($path, 'losses') === 0) {
        echo "{$path} => {$handler[0]}::{$handler[1]}\n";
    }
}
echo "</pre>";

echo "<h2>3. Test Form</h2>";
echo "<p>This form should submit to: <code>" . rtrim(BASE_URL, '/') . "/losses/recordMortality</code></p>";
?>

<form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/recordMortality" style="border: 2px solid blue; padding: 20px;">
    <input type="hidden" name="mortality_id" value="999">
    <button type="submit" style="padding: 10px 20px; font-size: 16px;">
        Click to Test Submit
    </button>
</form>

<h2>4. Check Apache Logs</h2>
<p>After clicking the button above, check:</p>
<ul>
    <li>C:\xampp\apache\logs\error.log</li>
    <li>C:\xampp\php\logs\php_error_log</li>
</ul>

<h2>5. Manual Test</h2>
<p>Try accessing directly:</p>
<ul>
    <li><a href="<?= rtrim(BASE_URL, '/') ?>/losses">GET /losses</a> (should work)</li>
    <li><a href="<?= rtrim(BASE_URL, '/') ?>/losses/create">GET /losses/create</a> (should work)</li>
</ul>

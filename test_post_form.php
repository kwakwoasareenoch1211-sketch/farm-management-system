<?php
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test POST Form</title>
</head>
<body>
    <h1>Test POST to losses/recordMortality</h1>
    
    <p>BASE_URL: <?= BASE_URL ?></p>
    <p>Form Action: <?= rtrim(BASE_URL, '/') ?>/losses/recordMortality</p>
    
    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/losses/recordMortality">
        <input type="hidden" name="mortality_id" value="1">
        <button type="submit">Test Submit</button>
    </form>
    
    <hr>
    
    <h2>Alternative: Using full URL</h2>
    <form method="POST" action="http://localhost/farmapp/losses/recordMortality">
        <input type="hidden" name="mortality_id" value="1">
        <button type="submit">Test Submit (Full URL)</button>
    </form>
</body>
</html>

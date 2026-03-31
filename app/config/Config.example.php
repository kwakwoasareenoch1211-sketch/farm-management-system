<?php
/**
 * Configuration Example File
 * 
 * Copy this file to Config.php and update with your actual values
 * 
 * IMPORTANT: Never commit Config.php with real credentials to version control
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
}

// Application URL (update with your domain)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/farmapp/');
}

// Database Configuration (update with your credentials)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'your_username');
if (!defined('DB_PASS')) define('DB_PASS', 'your_password');
if (!defined('DB_NAME')) define('DB_NAME', 'farmapp_db');

// Optional: Application Settings
// define('APP_ENV', 'production'); // development, staging, production
// define('DEBUG_MODE', false);
// define('SESSION_LIFETIME', 3600); // 1 hour

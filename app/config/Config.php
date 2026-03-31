<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
}

// Base URL - auto-detect Railway or use local
if (!defined('BASE_URL')) {
    if (getenv('RAILWAY_STATIC_URL')) {
        define('BASE_URL', 'https://' . getenv('RAILWAY_STATIC_URL') . '/');
    } elseif (getenv('RAILWAY_PUBLIC_DOMAIN')) {
        define('BASE_URL', 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') . '/');
    } else {
        define('BASE_URL', 'http://localhost/farmapp/');
    }
}

// Database Configuration - use environment variables if available (Railway)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'farmapp_db');

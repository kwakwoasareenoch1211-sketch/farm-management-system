<?php
/**
 * Clear PHP OpCache
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache cleared successfully!\n";
} else {
    echo "OpCache is not enabled.\n";
}

// Also clear any file stat cache
clearstatcache(true);
echo "File stat cache cleared!\n";

echo "\nPlease restart Apache from XAMPP Control Panel for changes to take full effect.\n";

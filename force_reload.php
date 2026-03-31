<?php
/**
 * Force reload by adding cache busting
 */

echo "Current timestamp: " . time() . "\n";
echo "Add this to your URL: ?v=" . time() . "\n";
echo "\nExample: http://localhost/farmapp/liabilities/view?id=1&v=" . time() . "\n";
echo "\nOr just restart Apache and clear browser cache (Ctrl+Shift+Delete)\n";

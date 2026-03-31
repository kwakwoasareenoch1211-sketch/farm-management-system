<?php
/**
 * Deployment Helper Script
 * Run this before deploying to production
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              FARMAPP DEPLOYMENT HELPER                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "This script will help you prepare for deployment.\n\n";

// Step 1: Run system check
echo "STEP 1: Running System Check...\n";
echo str_repeat("─", 60) . "\n";
$output = [];
$returnCode = 0;
exec('php final_system_check.php', $output, $returnCode);

if ($returnCode === 0) {
    echo "✓ System check PASSED\n\n";
} else {
    echo "✗ System check FAILED\n";
    echo "Please fix errors before deploying.\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

// Step 2: Check for test/debug files
echo "STEP 2: Checking for Test Files...\n";
echo str_repeat("─", 60) . "\n";
$testFiles = glob('*.php');
$testFiles = array_filter($testFiles, function($file) {
    return preg_match('/(test|debug|check|verify|diagnose|fix)_/i', $file);
});

if (count($testFiles) > 0) {
    echo "⚠️  Found " . count($testFiles) . " test/debug files:\n";
    foreach ($testFiles as $file) {
        echo "   - $file\n";
    }
    echo "\nConsider removing these before deployment.\n\n";
} else {
    echo "✓ No test files found\n\n";
}

// Step 3: Check configuration
echo "STEP 3: Configuration Check...\n";
echo str_repeat("─", 60) . "\n";

if (file_exists('app/config/Config.php')) {
    $config = file_get_contents('app/config/Config.php');
    
    // Check for development settings
    if (strpos($config, 'localhost') !== false) {
        echo "⚠️  BASE_URL contains 'localhost' - update for production\n";
    }
    
    if (strpos($config, 'error_reporting(E_ALL)') !== false) {
        echo "⚠️  Error reporting is enabled - consider disabling for production\n";
    }
    
    if (strpos($config, 'display_errors') !== false && strpos($config, 'ini_set(\'display_errors\', 1)') !== false) {
        echo "⚠️  Display errors is ON - should be OFF for production\n";
    }
    
    echo "✓ Configuration file exists\n\n";
} else {
    echo "✗ Configuration file missing!\n\n";
    exit(1);
}

// Step 4: Database backup reminder
echo "STEP 4: Database Backup\n";
echo str_repeat("─", 60) . "\n";
echo "⚠️  IMPORTANT: Backup your database before deployment!\n";
echo "\nCommand:\n";
echo "  mysqldump -u root farmapp_db > backup_" . date('Ymd_His') . ".sql\n\n";

// Step 5: File permissions
echo "STEP 5: File Permissions Check...\n";
echo str_repeat("─", 60) . "\n";
$writableDirs = ['uploads', 'logs', 'cache'];
$allWritable = true;

foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ $dir/ is writable\n";
        } else {
            echo "✗ $dir/ is NOT writable\n";
            $allWritable = false;
        }
    } else {
        echo "⚠️  $dir/ does not exist (may not be needed)\n";
    }
}

if ($allWritable) {
    echo "✓ All required directories are writable\n";
}
echo "\n";

// Final summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                   DEPLOYMENT SUMMARY                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Pre-Deployment Checklist:\n";
echo "  [✓] System check passed\n";
echo "  [ ] Database backed up\n";
echo "  [ ] Configuration updated for production\n";
echo "  [ ] Error display disabled\n";
echo "  [ ] BASE_URL updated\n";
echo "  [ ] Test files removed (optional)\n";
echo "  [ ] File permissions verified\n\n";

echo "Next Steps:\n";
echo "  1. Backup database: mysqldump -u root farmapp_db > backup.sql\n";
echo "  2. Update app/config/Config.php for production\n";
echo "  3. Test critical paths after deployment\n";
echo "  4. Monitor error logs for 24 hours\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Ready to deploy! Good luck! 🚀                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

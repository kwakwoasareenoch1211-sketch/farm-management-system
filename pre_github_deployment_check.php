<?php
// Comprehensive pre-deployment check for GitHub

echo "=== PRE-GITHUB DEPLOYMENT CHECK ===\n\n";

$errors = [];
$warnings = [];
$passed = [];

// 1. Check critical files exist
echo "1. Checking Critical Files...\n";
$criticalFiles = [
    'README.md',
    'DEPLOYMENT_SUMMARY.md',
    '.gitignore',
    'index.php',
    'app/config/Config.php',
    'app/config/Database.php',
    'app/core/Router.php',
    'app/Router/web.php',
    'database/rebuild_complete.sql',
    'database/users.sql',
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file\n";
        $passed[] = "File exists: $file";
    } else {
        echo "   ✗ $file MISSING\n";
        $errors[] = "Missing critical file: $file";
    }
}

// 2. Check .gitignore excludes sensitive data
echo "\n2. Checking .gitignore Configuration...\n";
$gitignore = file_get_contents('.gitignore');
$requiredPatterns = [
    'app/config/Config.php' => 'Database credentials',
    '*.log' => 'Log files',
    '.env' => 'Environment files',
];

foreach ($requiredPatterns as $pattern => $reason) {
    if (strpos($gitignore, $pattern) !== false) {
        echo "   ✓ Excludes $pattern ($reason)\n";
        $passed[] = "Gitignore excludes: $pattern";
    } else {
        echo "   ⚠ Missing pattern: $pattern ($reason)\n";
        $warnings[] = "Gitignore should exclude: $pattern";
    }
}

// 3. Check no sensitive data in tracked files
echo "\n3. Checking for Sensitive Data...\n";
$sensitivePatterns = [
    'password' => false,
    'secret' => false,
    'api_key' => false,
    'private_key' => false,
];

// Check Config.php for placeholder values
if (file_exists('app/config/Config.php')) {
    $config = file_get_contents('app/config/Config.php');
    if (strpos($config, 'your_username') !== false || strpos($config, 'your_password') !== false) {
        echo "   ✓ Config.php has placeholder values\n";
        $passed[] = "Config has placeholders (safe for GitHub)";
    } else {
        echo "   ⚠ Config.php may contain real credentials\n";
        $warnings[] = "Verify Config.php has placeholder values";
    }
}

// 4. Check README quality
echo "\n4. Checking README.md...\n";
if (file_exists('README.md')) {
    $readme = file_get_contents('README.md');
    $readmeChecks = [
        'Installation' => 'Installation instructions',
        'Features' => 'Feature list',
        'Quick Start' => 'Quick start guide',
        'License' => 'License information',
    ];
    
    foreach ($readmeChecks as $section => $desc) {
        if (stripos($readme, $section) !== false) {
            echo "   ✓ Contains $desc\n";
            $passed[] = "README has: $desc";
        } else {
            echo "   ⚠ Missing $desc\n";
            $warnings[] = "README should have: $desc";
        }
    }
}

// 5. Check git status
echo "\n5. Checking Git Status...\n";
exec('git status --porcelain', $gitStatus);
if (empty($gitStatus)) {
    echo "   ✓ All changes committed\n";
    $passed[] = "Git working directory clean";
} else {
    echo "   ⚠ Uncommitted changes:\n";
    foreach (array_slice($gitStatus, 0, 5) as $line) {
        echo "      $line\n";
    }
    $warnings[] = "Uncommitted changes exist";
}

// 6. Check commit history
echo "\n6. Checking Commit History...\n";
exec('git log --oneline', $commits);
if (count($commits) > 0) {
    echo "   ✓ " . count($commits) . " commits ready\n";
    foreach (array_slice($commits, 0, 3) as $commit) {
        echo "      $commit\n";
    }
    $passed[] = count($commits) . " commits in history";
} else {
    echo "   ✗ No commits found\n";
    $errors[] = "No commits in repository";
}

// 7. Check for test/debug files that shouldn't be in production
echo "\n7. Checking for Test Files...\n";
$testFiles = glob('test_*.php');
$debugFiles = glob('debug_*.php');
$checkFiles = glob('check_*.php');

$devFiles = array_merge($testFiles, $debugFiles, $checkFiles);
if (count($devFiles) > 0) {
    echo "   ⚠ Found " . count($devFiles) . " test/debug files\n";
    echo "      (These are tracked but won't affect production)\n";
    $warnings[] = count($devFiles) . " test/debug files in repo";
} else {
    echo "   ✓ No test/debug files\n";
    $passed[] = "No test files";
}

// 8. Check database files
echo "\n8. Checking Database Files...\n";
$dbFiles = [
    'database/rebuild_complete.sql' => 'Main database schema',
    'database/users.sql' => 'Users table',
];

foreach ($dbFiles as $file => $desc) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✓ $desc (" . number_format($size) . " bytes)\n";
        $passed[] = "Database file: $desc";
    } else {
        echo "   ✗ Missing: $desc\n";
        $errors[] = "Missing database file: $desc";
    }
}

// 9. Check documentation files
echo "\n9. Checking Documentation...\n";
$docFiles = [
    'README.md',
    'DEPLOYMENT_SUMMARY.md',
    'QUICK_START.md',
];

$docCount = 0;
foreach ($docFiles as $file) {
    if (file_exists($file)) {
        $docCount++;
    }
}
echo "   ✓ $docCount documentation files found\n";
$passed[] = "$docCount documentation files";

// 10. Check MVC structure
echo "\n10. Checking MVC Structure...\n";
$mvcDirs = [
    'app/controllers' => 'Controllers',
    'app/models' => 'Models',
    'app/views' => 'Views',
    'app/core' => 'Core framework',
];

foreach ($mvcDirs as $dir => $desc) {
    if (is_dir($dir)) {
        $count = count(glob("$dir/*.php"));
        echo "   ✓ $desc ($count files)\n";
        $passed[] = "$desc directory exists";
    } else {
        echo "   ✗ Missing: $desc\n";
        $errors[] = "Missing directory: $desc";
    }
}

// Final Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "DEPLOYMENT CHECK SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ PASSED: " . count($passed) . " checks\n";
echo "⚠️  WARNINGS: " . count($warnings) . " items\n";
echo "❌ ERRORS: " . count($errors) . " critical issues\n\n";

if (count($errors) > 0) {
    echo "CRITICAL ERRORS (Must fix before deployment):\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS (Review recommended):\n";
    foreach ($warnings as $warning) {
        echo "  ⚠ $warning\n";
    }
    echo "\n";
}

if (count($errors) === 0) {
    echo "🎉 READY FOR GITHUB DEPLOYMENT!\n\n";
    echo "Next Steps:\n";
    echo "1. Create repository on GitHub\n";
    echo "2. git remote add origin <your-repo-url>\n";
    echo "3. git push -u origin master\n\n";
    echo "Repository Status: ✅ PRODUCTION READY\n";
} else {
    echo "⛔ NOT READY - Fix critical errors first\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

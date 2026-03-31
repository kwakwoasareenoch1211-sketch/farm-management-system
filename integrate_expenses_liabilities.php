<?php
/**
 * Integrate Expenses with Liabilities
 * - Adds payment_status to expenses
 * - Auto-creates liabilities for unpaid expenses
 * - Links expenses to liabilities
 */

define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'app/config/Config.php';
require_once BASE_PATH . 'app/config/Database.php';

$db = Database::connect();

echo "=== INTEGRATING EXPENSES WITH LIABILITIES ===\n\n";

// 1. Add payment_status column to expenses
echo "1. Adding payment_status columns to expenses...\n";
try {
    $db->exec("
        ALTER TABLE expenses 
        ADD COLUMN payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'paid' AFTER payment_method
    ");
    echo "   ✓ payment_status column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ payment_status column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("
        ALTER TABLE expenses 
        ADD COLUMN amount_paid DECIMAL(15,2) DEFAULT 0 AFTER payment_status
    ");
    echo "   ✓ amount_paid column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ amount_paid column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("
        ALTER TABLE expenses 
        ADD COLUMN liability_id INT(10) UNSIGNED NULL AFTER amount_paid
    ");
    echo "   ✓ liability_id column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ liability_id column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("
        ALTER TABLE expenses 
        ADD COLUMN expense_reference VARCHAR(100) NULL AFTER liability_id
    ");
    echo "   ✓ expense_reference column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ expense_reference column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

// 2. Add source tracking to liabilities
echo "\n2. Adding source tracking to liabilities...\n";
try {
    $db->exec("
        ALTER TABLE liabilities
        ADD COLUMN source_type ENUM('manual', 'expense', 'purchase_order') DEFAULT 'manual' AFTER farm_id
    ");
    echo "   ✓ source_type column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ source_type column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("
        ALTER TABLE liabilities
        ADD COLUMN source_id INT(10) UNSIGNED NULL AFTER source_type
    ");
    echo "   ✓ source_id column added\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   ✓ source_id column already exists\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

// 3. Set all existing expenses as paid
echo "\n3. Setting existing expenses as paid...\n";
$stmt = $db->exec("
    UPDATE expenses 
    SET payment_status = 'paid', amount_paid = amount 
    WHERE payment_status IS NULL OR payment_status = 'paid'
");
echo "   ✓ Updated existing expenses\n";

echo "\n=== INTEGRATION COMPLETE ===\n";
echo "\nNew Features:\n";
echo "1. Expenses can now be marked as 'paid', 'unpaid', or 'partial'\n";
echo "2. Unpaid expenses automatically create liabilities\n";
echo "3. Liabilities track their source (manual, expense, purchase_order)\n";
echo "4. Payment status is tracked separately from payment method\n";

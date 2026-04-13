-- ============================================
-- COMPLETE COLUMN FIX SCRIPT
-- Adds all missing columns safely (IF NOT EXISTS)
-- Run this after rebuild_complete.sql
-- ============================================

-- EXPENSES TABLE - missing columns
ALTER TABLE expenses
    ADD COLUMN IF NOT EXISTS payment_status ENUM('paid','unpaid','partial') DEFAULT 'paid' AFTER payment_method,
    ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(15,2) DEFAULT 0 AFTER payment_status,
    ADD COLUMN IF NOT EXISTS liability_id INT UNSIGNED NULL AFTER amount_paid,
    ADD COLUMN IF NOT EXISTS expense_reference VARCHAR(100) NULL AFTER liability_id;

-- SALES TABLE - missing columns
ALTER TABLE sales
    ADD COLUMN IF NOT EXISTS subtotal DECIMAL(15,2) DEFAULT 0 AFTER total_amount,
    ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(15,2) DEFAULT 0 AFTER subtotal,
    ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(15,2) DEFAULT 0 AFTER discount_amount,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('paid','unpaid','partial') DEFAULT 'paid' AFTER amount_paid,
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cash','bank_transfer','cheque','mobile_money') DEFAULT 'cash' AFTER payment_status,
    ADD COLUMN IF NOT EXISTS product_type VARCHAR(50) DEFAULT 'birds' AFTER batch_id,
    ADD COLUMN IF NOT EXISTS unit_price DECIMAL(15,2) DEFAULT 0 AFTER product_type;

-- LIABILITIES TABLE - missing columns (already added but ensure)
ALTER TABLE liabilities
    ADD COLUMN IF NOT EXISTS source_type VARCHAR(50) NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS source_id INT UNSIGNED NULL AFTER source_type,
    ADD COLUMN IF NOT EXISTS lender_name VARCHAR(100) NULL AFTER source_id;

-- LIABILITY_PAYMENTS TABLE - ensure amount_paid column exists
ALTER TABLE liability_payments
    ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(15,2) DEFAULT 0 AFTER liability_id,
    ADD COLUMN IF NOT EXISTS payment_date DATE NULL AFTER amount_paid;

-- EGG_PRODUCTION_RECORDS - missing trays_equivalent
ALTER TABLE egg_production_records
    ADD COLUMN IF NOT EXISTS trays_equivalent DECIMAL(10,2) DEFAULT 0 AFTER quantity;

-- USERS TABLE - missing columns
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL AFTER is_active,
    ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) NULL AFTER id;

-- ANIMAL_BATCHES - missing columns
ALTER TABLE animal_batches
    ADD COLUMN IF NOT EXISTS bird_subtype VARCHAR(50) NULL AFTER production_purpose,
    ADD COLUMN IF NOT EXISTS source_name VARCHAR(100) NULL AFTER bird_subtype,
    ADD COLUMN IF NOT EXISTS purchase_date DATE NULL AFTER source_name,
    ADD COLUMN IF NOT EXISTS expected_end_date DATE NULL AFTER start_date,
    ADD COLUMN IF NOT EXISTS initial_unit_cost DECIMAL(15,2) DEFAULT 0 AFTER initial_quantity;

-- FEED_RECORDS - ensure feed_name exists
ALTER TABLE feed_records
    ADD COLUMN IF NOT EXISTS feed_name VARCHAR(100) NULL AFTER record_date;

-- MORTALITY_RECORDS - missing disposal_method
ALTER TABLE mortality_records
    ADD COLUMN IF NOT EXISTS disposal_method VARCHAR(100) NULL AFTER cause;

-- LOSSES/WRITEOFFS TABLE - create if not exists
CREATE TABLE IF NOT EXISTS losses_writeoffs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    loss_type VARCHAR(50) NOT NULL,
    reference_id INT UNSIGNED NULL,
    loss_date DATE NOT NULL,
    description VARCHAR(255) NULL,
    quantity DECIMAL(10,2) DEFAULT 0,
    unit_cost DECIMAL(15,2) DEFAULT 0,
    total_loss_amount DECIMAL(15,2) DEFAULT 0,
    reason TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing expenses to set amount_paid = amount where payment_status is paid
UPDATE expenses SET amount_paid = amount WHERE payment_status = 'paid' OR payment_status IS NULL;
UPDATE expenses SET payment_status = 'paid' WHERE payment_status IS NULL;

-- Update existing sales to set subtotal and amount_paid
UPDATE sales SET subtotal = total_amount WHERE subtotal = 0 OR subtotal IS NULL;
UPDATE sales SET amount_paid = total_amount WHERE payment_status = 'paid' OR payment_status IS NULL;
UPDATE sales SET payment_status = 'paid' WHERE payment_status IS NULL;

SELECT 'All columns fixed successfully!' AS status;

-- ============================================
-- EXTRA COLUMNS ADDED AFTER INITIAL BUILD
-- ============================================

-- animal_batches
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS bird_subtype VARCHAR(50) NULL AFTER production_purpose;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS breed VARCHAR(100) NULL AFTER bird_subtype;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS source_name VARCHAR(100) NULL AFTER breed;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS purchase_date DATE NULL AFTER source_name;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS expected_end_date DATE NULL AFTER start_date;
ALTER TABLE animal_batches ADD COLUMN IF NOT EXISTS initial_unit_cost DECIMAL(15,2) DEFAULT 0 AFTER initial_quantity;

-- feed_records
ALTER TABLE feed_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE feed_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE feed_records ADD COLUMN IF NOT EXISTS paid_by INT UNSIGNED NULL AFTER is_shared;
ALTER TABLE feed_records ADD COLUMN IF NOT EXISTS feed_name VARCHAR(100) NULL AFTER record_date;

-- medication_records
ALTER TABLE medication_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE medication_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE medication_records ADD COLUMN IF NOT EXISTS paid_by INT UNSIGNED NULL AFTER is_shared;

-- vaccination_records
ALTER TABLE vaccination_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE vaccination_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE vaccination_records ADD COLUMN IF NOT EXISTS paid_by INT UNSIGNED NULL AFTER is_shared;

-- mortality_records
ALTER TABLE mortality_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE mortality_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE mortality_records ADD COLUMN IF NOT EXISTS disposal_method VARCHAR(100) NULL AFTER cause;

-- egg_production_records
ALTER TABLE egg_production_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE egg_production_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE egg_production_records ADD COLUMN IF NOT EXISTS trays_equivalent DECIMAL(10,2) DEFAULT 0 AFTER quantity;

-- weight_records
ALTER TABLE weight_records ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE weight_records ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;

-- expenses
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) DEFAULT 0 AFTER owner_id;
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS paid_by INT UNSIGNED NULL AFTER is_shared;

-- sales
ALTER TABLE sales ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;

-- capital_entries
ALTER TABLE capital_entries ADD COLUMN IF NOT EXISTS owner_id INT UNSIGNED NULL AFTER farm_id;
ALTER TABLE capital_entries ADD COLUMN IF NOT EXISTS title VARCHAR(150) NULL AFTER entry_date;
ALTER TABLE capital_entries ADD COLUMN IF NOT EXISTS capital_type ENUM('owner_equity','retained_earnings','loan_capital','grant','reinvestment','other') DEFAULT 'owner_equity' AFTER title;
ALTER TABLE capital_entries ADD COLUMN IF NOT EXISTS source_name VARCHAR(100) NULL AFTER capital_type;
ALTER TABLE capital_entries ADD COLUMN IF NOT EXISTS reference_no VARCHAR(100) NULL AFTER source_name;

-- users
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin','owner','manager','staff') DEFAULT 'admin' AFTER full_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL AFTER is_active;

-- liabilities
ALTER TABLE liabilities ADD COLUMN IF NOT EXISTS source_type VARCHAR(50) NULL AFTER notes;
ALTER TABLE liabilities ADD COLUMN IF NOT EXISTS source_id INT UNSIGNED NULL AFTER source_type;
ALTER TABLE liabilities ADD COLUMN IF NOT EXISTS lender_name VARCHAR(100) NULL AFTER source_id;

-- inventory_item
ALTER TABLE inventory_item ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active' AFTER unit_cost;
ALTER TABLE inventory_item ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER status;

-- stock_issues
CREATE TABLE IF NOT EXISTS stock_issues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id INT UNSIGNED NOT NULL,
    batch_id INT UNSIGNED NULL,
    issue_date DATE NOT NULL,
    quantity_issued DECIMAL(10,2) NOT NULL,
    issue_reason VARCHAR(50) DEFAULT 'farm_use',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_item(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- owner_advances
CREATE TABLE IF NOT EXISTS owner_advances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    source_type ENUM('expense','feed','medication','vaccination','other') NOT NULL DEFAULT 'other',
    source_id INT UNSIGNED NULL,
    advance_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NULL,
    repaid_amount DECIMAL(15,2) DEFAULT 0,
    repaid_date DATE NULL,
    status ENUM('outstanding','partial','repaid') DEFAULT 'outstanding',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed required data
INSERT IGNORE INTO farms (id, farm_name, location) VALUES (1, 'Main Farm', 'Ghana');
INSERT IGNORE INTO animal_types (type_name) VALUES ('Poultry'),('Cattle'),('Goat'),('Pig'),('Sheep');
INSERT IGNORE INTO housing_units (farm_id, unit_name, capacity) VALUES (1,'House A',500),(1,'House B',500),(1,'House C',1000),(1,'Open Range',2000);
UPDATE users SET role='owner' WHERE role='admin' OR role IS NULL;


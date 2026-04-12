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

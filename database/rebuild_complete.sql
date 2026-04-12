-- ============================================
-- COMPLETE DATABASE REBUILD SCRIPT
-- Drops all tables and recreates them in correct order
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop all existing tables
DROP TABLE IF EXISTS journal_entry_lines;
DROP TABLE IF EXISTS journal_entries;
DROP TABLE IF EXISTS asset_maintenance;
DROP TABLE IF EXISTS asset_depreciation;
DROP TABLE IF EXISTS liability_payments;
DROP TABLE IF EXISTS liabilities;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS kpi_snapshots;
DROP TABLE IF EXISTS business_health_scores;
DROP TABLE IF EXISTS going_concern_assessments;
DROP TABLE IF EXISTS decision_recommendations;
DROP TABLE IF EXISTS forecasts;
DROP TABLE IF EXISTS budgets;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS stock_issues;
DROP TABLE IF EXISTS stock_receipts;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS inventory_item;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS weight_records;
DROP TABLE IF EXISTS vaccination_records;
DROP TABLE IF EXISTS medication_records;
DROP TABLE IF EXISTS feed_records;
DROP TABLE IF EXISTS egg_production_records;
DROP TABLE IF EXISTS mortality_records;
DROP TABLE IF EXISTS animal_batches;
DROP TABLE IF EXISTS housing_units;
DROP TABLE IF EXISTS animal_types;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS expense_categories;
DROP TABLE IF EXISTS investments;
DROP TABLE IF EXISTS capital_entries;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS account_types;
DROP TABLE IF EXISTS farms;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. USERS TABLE (Must be first - referenced by many tables)
-- ============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_username (username),
    UNIQUE KEY unique_email (email),
    KEY idx_username (username),
    KEY idx_email (email),
    KEY idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (full_name, username, email, password_hash, is_active) 
VALUES ('Farm Admin', 'admin', 'admin@farmapp.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- ============================================
-- 2. CORE REFERENCE TABLES
-- ============================================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE farms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    size_hectares DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE account_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('asset','liability','equity','revenue','expense') NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_code VARCHAR(20) NOT NULL UNIQUE,
    account_name VARCHAR(100) NOT NULL,
    account_type_id INT UNSIGNED,
    balance DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. ANIMAL/POULTRY TABLES
-- ============================================

CREATE TABLE animal_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE housing_units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    unit_name VARCHAR(100) NOT NULL,
    capacity INT,
    status ENUM('active','inactive','maintenance') DEFAULT 'active',
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE animal_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_code VARCHAR(50) UNIQUE,
    batch_name VARCHAR(100),
    animal_type_id INT UNSIGNED,
    housing_unit_id INT UNSIGNED,
    production_purpose ENUM('eggs','meat','breeding','mixed') DEFAULT 'mixed',
    bird_subtype VARCHAR(50),
    breed VARCHAR(100),
    source_name VARCHAR(100),
    purchase_date DATE,
    start_date DATE,
    expected_end_date DATE,
    initial_quantity INT NOT NULL,
    current_quantity INT,
    initial_unit_cost DECIMAL(10,2),
    status ENUM('active','closed','sold') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_type_id) REFERENCES animal_types(id),
    FOREIGN KEY (housing_unit_id) REFERENCES housing_units(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. PRODUCTION RECORDS
-- ============================================

CREATE TABLE mortality_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    record_date DATE NOT NULL,
    quantity INT NOT NULL,
    cause VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE egg_production_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    record_date DATE NOT NULL,
    quantity INT NOT NULL,
    trays_equivalent DECIMAL(10,2),
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feed_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    inventory_item_id INT UNSIGNED,
    record_date DATE NOT NULL,
    feed_name VARCHAR(100),
    quantity_kg DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medication_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    inventory_item_id INT UNSIGNED,
    record_date DATE NOT NULL,
    medication_name VARCHAR(100),
    condition_treated VARCHAR(255),
    dosage VARCHAR(100),
    quantity_used DECIMAL(10,2),
    unit VARCHAR(50),
    unit_cost DECIMAL(10,2),
    administered_by VARCHAR(100),
    withdrawal_period_days INT,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vaccination_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    inventory_item_id INT UNSIGNED,
    record_date DATE NOT NULL,
    vaccine_name VARCHAR(100),
    dose_qty DECIMAL(10,2),
    disease_target VARCHAR(255),
    dosage VARCHAR(100),
    route VARCHAR(100),
    cost_amount DECIMAL(10,2),
    next_due_date DATE,
    administered_by VARCHAR(100),
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE weight_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    record_date DATE NOT NULL,
    sample_size INT,
    total_weight_kg DECIMAL(10,2),
    average_weight_kg DECIMAL(10,2),
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. INVENTORY & SALES
-- ============================================

CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inventory_item (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category ENUM('feed','medication','vaccine','equipment','other') DEFAULT 'other',
    unit_of_measure VARCHAR(20),
    current_stock DECIMAL(10,2) DEFAULT 0,
    reorder_level DECIMAL(10,2),
    unit_cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_movements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED,
    movement_type ENUM('receipt','issue','adjustment') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    movement_date DATE NOT NULL,
    reference_no VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_item(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_receipts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED,
    supplier_id INT UNSIGNED,
    quantity DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2),
    receipt_date DATE NOT NULL,
    reference_no VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_item(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_issues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    quantity DECIMAL(10,2) NOT NULL,
    issue_date DATE NOT NULL,
    purpose VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_item(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    batch_id INT UNSIGNED,
    customer_id INT UNSIGNED,
    sale_date DATE NOT NULL,
    product_type ENUM('eggs','birds','meat','other') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(15,2),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(15,2),
    payment_method ENUM('cash','bank_transfer','cheque','mobile_money','credit') DEFAULT 'cash',
    payment_status ENUM('paid','partial','unpaid') DEFAULT 'unpaid',
    amount_paid DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES animal_batches(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. FINANCIAL TABLES
-- ============================================

CREATE TABLE expense_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    category_id INT UNSIGNED,
    expense_date DATE NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash','bank_transfer','cheque','mobile_money') DEFAULT 'cash',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE capital_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    entry_date DATE NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    entry_type ENUM('contribution','withdrawal') DEFAULT 'contribution',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE investments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    investment_name VARCHAR(100) NOT NULL,
    investment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    expected_return_rate DECIMAL(5,2),
    maturity_date DATE,
    status ENUM('active','matured','withdrawn') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    asset_name VARCHAR(100) NOT NULL,
    asset_type ENUM('land','building','equipment','vehicle','other') NOT NULL,
    purchase_date DATE,
    purchase_cost DECIMAL(15,2),
    current_value DECIMAL(15,2),
    depreciation_rate DECIMAL(5,2),
    status ENUM('active','disposed','under_maintenance') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE liabilities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    liability_name VARCHAR(100) NOT NULL,
    liability_type ENUM('loan','mortgage','credit','other') NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    outstanding_balance DECIMAL(15,2),
    interest_rate DECIMAL(5,2),
    start_date DATE,
    due_date DATE,
    status ENUM('active','paid','defaulted') DEFAULT 'active',
    notes TEXT,
    source_type VARCHAR(50) NULL,
    source_id INT UNSIGNED NULL,
    lender_name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE asset_depreciation (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED,
    depreciation_date DATE NOT NULL,
    depreciation_amount DECIMAL(15,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE asset_maintenance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED,
    maintenance_date DATE NOT NULL,
    description VARCHAR(255),
    cost DECIMAL(15,2),
    notes TEXT,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE liability_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    liability_id INT UNSIGNED,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (liability_id) REFERENCES liabilities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE journal_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_date DATE NOT NULL,
    description VARCHAR(255),
    reference_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE journal_entry_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    journal_entry_id INT UNSIGNED,
    account_id INT UNSIGNED,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. DECISION SUPPORT & ANALYTICS
-- ============================================

CREATE TABLE budgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    budget_period VARCHAR(50),
    category VARCHAR(100),
    budgeted_amount DECIMAL(15,2),
    actual_amount DECIMAL(15,2) DEFAULT 0,
    variance DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE forecasts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    forecast_date DATE NOT NULL,
    metric_name VARCHAR(100),
    forecasted_value DECIMAL(15,2),
    actual_value DECIMAL(15,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE decision_recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    recommendation_date DATE NOT NULL,
    category VARCHAR(100),
    recommendation_text TEXT,
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    status ENUM('pending','implemented','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE going_concern_assessments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    assessment_date DATE NOT NULL,
    liquidity_ratio DECIMAL(10,2),
    debt_ratio DECIMAL(10,2),
    profitability_ratio DECIMAL(10,2),
    overall_score DECIMAL(5,2),
    status ENUM('healthy','warning','critical') DEFAULT 'healthy',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE business_health_scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    score_date DATE NOT NULL,
    financial_score DECIMAL(5,2),
    operational_score DECIMAL(5,2),
    market_score DECIMAL(5,2),
    overall_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kpi_snapshots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    snapshot_date DATE NOT NULL,
    kpi_name VARCHAR(100),
    kpi_value DECIMAL(15,2),
    target_value DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- COMPLETE! Database rebuilt successfully
-- ============================================

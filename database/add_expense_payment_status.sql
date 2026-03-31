-- Add payment_status and expense_reference to expenses table
ALTER TABLE expenses 
ADD COLUMN payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'paid' AFTER payment_method,
ADD COLUMN amount_paid DECIMAL(15,2) DEFAULT 0 AFTER payment_status,
ADD COLUMN liability_id INT(10) UNSIGNED NULL AFTER amount_paid,
ADD COLUMN expense_reference VARCHAR(100) NULL AFTER liability_id;

-- Add foreign key for liability_id
ALTER TABLE expenses
ADD CONSTRAINT fk_expenses_liability
FOREIGN KEY (liability_id) REFERENCES liabilities(id) ON DELETE SET NULL;

-- Add source_type and source_id to liabilities to track where they came from
ALTER TABLE liabilities
ADD COLUMN source_type ENUM('manual', 'expense', 'purchase_order') DEFAULT 'manual' AFTER farm_id,
ADD COLUMN source_id INT(10) UNSIGNED NULL AFTER source_type;

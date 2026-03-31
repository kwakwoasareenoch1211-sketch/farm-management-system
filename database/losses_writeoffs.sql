-- Losses & Write-offs Table
-- Tracks all business losses following proper accounting principles

CREATE TABLE IF NOT EXISTS losses_writeoffs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farm_id INT UNSIGNED,
    loss_type ENUM('mortality', 'inventory_writeoff', 'bad_debt', 'asset_impairment') NOT NULL,
    reference_id INT UNSIGNED,
    loss_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2),
    unit_cost DECIMAL(10,2),
    total_loss_amount DECIMAL(10,2) NOT NULL,
    reason VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index for faster queries
CREATE INDEX idx_loss_type ON losses_writeoffs(loss_type);
CREATE INDEX idx_loss_date ON losses_writeoffs(loss_date);
CREATE INDEX idx_farm_id ON losses_writeoffs(farm_id);

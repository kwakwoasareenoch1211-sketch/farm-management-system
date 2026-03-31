-- Unified Stock Receipt → Feed Records Flow
-- This migration adds status tracking and links feed records to stock receipts

-- Add status and stock_receipt_id to feed_records
ALTER TABLE feed_records 
ADD COLUMN status ENUM('available', 'assigned', 'used') DEFAULT 'assigned' AFTER notes,
ADD COLUMN stock_receipt_id INT UNSIGNED AFTER status,
ADD INDEX idx_status (status),
ADD INDEX idx_stock_receipt (stock_receipt_id);

-- Make batch_id nullable for available feed (not yet assigned)
ALTER TABLE feed_records 
MODIFY COLUMN batch_id INT UNSIGNED NULL;

-- Update existing records to 'assigned' status (they already have batches)
UPDATE feed_records SET status = 'assigned' WHERE batch_id IS NOT NULL;

-- Note: Foreign key for stock_receipt_id not added to allow flexibility
-- Stock receipts can be deleted without affecting feed records

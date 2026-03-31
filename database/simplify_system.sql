-- Simplify Inventory-Feed System
-- Remove stock tracking complexity

-- Remove columns from feed_records that are no longer needed
ALTER TABLE feed_records 
DROP COLUMN IF EXISTS status,
DROP COLUMN IF EXISTS stock_receipt_id;

-- Make batch_id required again (was made nullable for available feed)
ALTER TABLE feed_records 
MODIFY COLUMN batch_id INT UNSIGNED NOT NULL;

-- Simplify inventory_item table (remove stock tracking)
ALTER TABLE inventory_item
DROP COLUMN IF EXISTS current_stock,
DROP COLUMN IF EXISTS reorder_level,
DROP COLUMN IF EXISTS last_restock_date;

-- Drop complex tables (optional - comment out if you want to keep data)
-- DROP TABLE IF EXISTS stock_receipts;
-- DROP TABLE IF EXISTS stock_movements;
-- DROP TABLE IF EXISTS stock_issues;

-- Clean up any orphaned feed records
DELETE FROM feed_records WHERE batch_id IS NULL;

-- Update feed records to use current inventory item costs
UPDATE feed_records fr
INNER JOIN inventory_item ii ON ii.id = fr.inventory_item_id
SET fr.unit_cost = ii.unit_cost,
    fr.feed_name = ii.item_name
WHERE fr.inventory_item_id IS NOT NULL;

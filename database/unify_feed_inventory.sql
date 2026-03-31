-- Unify Feed and Inventory as One System
-- This makes feed_records completely dependent on inventory_item
-- No more separate feed names - everything comes from inventory

-- Step 1: Ensure all existing feed records have inventory links
-- (Skip records that can't be matched - they'll be marked as legacy)

-- Step 2: Make inventory_item_id required and add foreign key
ALTER TABLE feed_records 
    MODIFY COLUMN inventory_item_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_feed_inventory 
        FOREIGN KEY (inventory_item_id) 
        REFERENCES inventory_item(id) 
        ON DELETE RESTRICT;

-- Step 3: Remove redundant feed_name column (data comes from inventory_item.item_name)
-- Keep it for now for backward compatibility, but it will be auto-populated from inventory

-- Step 4: Add index for performance
CREATE INDEX idx_feed_inventory ON feed_records(inventory_item_id);

-- Step 5: Update any NULL inventory_item_id records to point to a "Legacy Feed" item
-- First create the legacy item if it doesn't exist
INSERT IGNORE INTO inventory_item (item_name, category, unit_of_measure, current_stock, reorder_level, unit_cost)
VALUES ('Legacy Feed (Pre-Integration)', 'feed', 'kg', 0, 0, 0);

-- Get the ID of the legacy item
SET @legacy_id = (SELECT id FROM inventory_item WHERE item_name = 'Legacy Feed (Pre-Integration)' LIMIT 1);

-- Update NULL records to point to legacy item
UPDATE feed_records 
SET inventory_item_id = @legacy_id 
WHERE inventory_item_id IS NULL OR inventory_item_id = 0;

-- Verification queries
SELECT 'Feed records without inventory link:' as status, COUNT(*) as count 
FROM feed_records 
WHERE inventory_item_id IS NULL OR inventory_item_id = 0;

SELECT 'Total feed records:' as status, COUNT(*) as count FROM feed_records;

SELECT 'Total inventory items (feed category):' as status, COUNT(*) as count 
FROM inventory_item 
WHERE LOWER(category) = 'feed';

-- Add disposal_method column to mortality_records if it doesn't exist
ALTER TABLE mortality_records 
ADD COLUMN IF NOT EXISTS disposal_method VARCHAR(100) DEFAULT NULL 
AFTER cause;

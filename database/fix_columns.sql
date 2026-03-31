-- ============================================
-- FIX MISSING COLUMNS IN EXISTING TABLES
-- This script adds missing columns without dropping data
-- Run this in phpMyAdmin or MySQL client
-- ============================================

-- Fix vaccination_records table
-- Drop old column first
ALTER TABLE vaccination_records DROP COLUMN IF EXISTS quantity_used;

-- Add new columns
ALTER TABLE vaccination_records 
    ADD COLUMN dose_qty DECIMAL(10,2) DEFAULT 0 AFTER vaccine_name,
    ADD COLUMN disease_target VARCHAR(255) AFTER dose_qty,
    ADD COLUMN dosage VARCHAR(100) AFTER disease_target,
    ADD COLUMN route VARCHAR(100) AFTER dosage,
    ADD COLUMN next_due_date DATE AFTER cost_amount,
    ADD COLUMN administered_by VARCHAR(100) AFTER next_due_date,
    ADD COLUMN created_by INT UNSIGNED AFTER notes;

-- Fix medication_records table
-- Drop old column first
ALTER TABLE medication_records DROP COLUMN IF EXISTS purpose;

-- Add new columns
ALTER TABLE medication_records 
    ADD COLUMN condition_treated VARCHAR(255) AFTER medication_name,
    ADD COLUMN dosage VARCHAR(100) AFTER condition_treated,
    ADD COLUMN unit VARCHAR(50) AFTER quantity_used,
    ADD COLUMN administered_by VARCHAR(100) AFTER unit_cost,
    ADD COLUMN withdrawal_period_days INT AFTER administered_by,
    ADD COLUMN created_by INT UNSIGNED AFTER notes;

-- Fix egg_production_records table
ALTER TABLE egg_production_records 
    ADD COLUMN created_by INT UNSIGNED AFTER notes;

-- Fix weight_records table
ALTER TABLE weight_records 
    ADD COLUMN created_by INT UNSIGNED AFTER notes;

-- Add foreign key constraints for created_by columns
ALTER TABLE vaccination_records 
    ADD CONSTRAINT fk_vaccination_created_by 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE medication_records 
    ADD CONSTRAINT fk_medication_created_by 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE egg_production_records 
    ADD CONSTRAINT fk_egg_production_created_by 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE weight_records 
    ADD CONSTRAINT fk_weight_created_by 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- ============================================
-- COMPLETE! Columns fixed successfully
-- ============================================

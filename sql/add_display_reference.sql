-- Migration: Add Display Reference System
-- Separates internal employee numbers (from HR/payroll) from display references (shown on ID cards)
-- UK English spelling used throughout

-- Step 1: Add display_reference field to employees table
ALTER TABLE employees 
ADD COLUMN display_reference VARCHAR(100) NULL AFTER employee_reference,
ADD COLUMN employee_number VARCHAR(100) NULL AFTER display_reference;

-- Step 2: Copy existing employee_reference to both display_reference and employee_number
-- This preserves existing data during migration
UPDATE employees 
SET display_reference = employee_reference,
    employee_number = employee_reference;

-- Step 3: Add unique constraint for display_reference within organisation
ALTER TABLE employees
ADD UNIQUE KEY unique_org_display_ref (organisation_id, display_reference);

-- Step 4: Add reference format configuration to organisations table
ALTER TABLE organisations
ADD COLUMN reference_prefix VARCHAR(50) NULL COMMENT 'Prefix for display references (e.g., SAMH)',
ADD COLUMN reference_pattern ENUM('incremental', 'random_alphanumeric', 'custom') DEFAULT 'incremental' COMMENT 'Pattern for generating display references',
ADD COLUMN reference_start_number INT DEFAULT 1 COMMENT 'Starting number for incremental references',
ADD COLUMN reference_digits INT DEFAULT 6 COMMENT 'Number of digits for incremental references';

-- Step 5: Create index for faster lookups
ALTER TABLE employees
ADD INDEX idx_display_reference (display_reference),
ADD INDEX idx_employee_number (employee_number);


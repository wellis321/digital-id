-- Migration: Convert from complex schema to simple schema
-- This migrates existing organisational_units table to the simplified structure

-- Step 1: Drop old complex tables if they exist (only if empty)
SET FOREIGN_KEY_CHECKS = 0;

-- First, drop foreign key constraints that reference organisational_unit_types
-- Find the constraint name first (it might be organisational_units_ibfk_2 or similar)
-- Drop the foreign key constraint from organisational_units table
ALTER TABLE organisational_units DROP FOREIGN KEY IF EXISTS organisational_units_ibfk_2;

-- Drop old unit_admin_roles table if exists
DROP TABLE IF EXISTS user_unit_admins;
DROP TABLE IF EXISTS unit_admin_roles;
DROP TABLE IF EXISTS organisational_unit_types;

-- Step 2: Check if organisational_units has old structure
-- If it has unit_type_id, we need to migrate

-- Step 3: Add unit_type column if it doesn't exist
ALTER TABLE organisational_units 
ADD COLUMN IF NOT EXISTS unit_type VARCHAR(100) NULL AFTER name;

-- Step 4: Migrate data from unit_type_id to unit_type (if needed)
-- This assumes you want to keep the type names - adjust as needed
UPDATE organisational_units ou
LEFT JOIN organisational_unit_types ut ON ou.unit_type_id = ut.id
SET ou.unit_type = ut.name
WHERE ou.unit_type_id IS NOT NULL AND ou.unit_type IS NULL;

-- Step 5: Drop old unit_type_id column
ALTER TABLE organisational_units 
DROP COLUMN IF EXISTS unit_type_id;

-- Step 6: Drop code column if exists (not in simple schema)
ALTER TABLE organisational_units 
DROP COLUMN IF EXISTS code;

-- Step 7: Add display_order if missing
ALTER TABLE organisational_units 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER manager_user_id;

-- Step 8: Ensure organisational_unit_members table exists with correct structure
CREATE TABLE IF NOT EXISTS organisational_unit_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(100) DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_unit_member (unit_id, user_id),
    INDEX idx_unit (unit_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 9: Migrate from old user_organisational_units to new organisational_unit_members (if needed)
-- Only if the old table exists and new table is empty
INSERT INTO organisational_unit_members (unit_id, user_id, role, joined_at)
SELECT organisational_unit_id, user_id, role_in_unit, assigned_at
FROM user_organisational_units
WHERE NOT EXISTS (
    SELECT 1 FROM organisational_unit_members 
    WHERE organisational_unit_members.unit_id = user_organisational_units.organisational_unit_id
    AND organisational_unit_members.user_id = user_organisational_units.user_id
);

-- Step 10: Drop old user_organisational_units table
DROP TABLE IF EXISTS user_organisational_units;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify structure
DESCRIBE organisational_units;
DESCRIBE organisational_unit_members;


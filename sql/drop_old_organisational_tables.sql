-- Drop Old Organisational Units Tables
-- This script safely drops the old complex schema tables
-- Run this if you need to clean up leftover tables

SET FOREIGN_KEY_CHECKS = 0;

-- Step 1: Find and drop foreign key constraints on organisational_units
-- Check what constraints exist first:
-- SHOW CREATE TABLE organisational_units;

-- Drop the foreign key constraint (adjust name based on SHOW CREATE TABLE output)
-- Common constraint names:
ALTER TABLE organisational_units DROP FOREIGN KEY IF EXISTS organisational_units_ibfk_2;
ALTER TABLE organisational_units DROP FOREIGN KEY IF EXISTS fk_org_units_unit_type;

-- If the above doesn't work, you may need to check the exact constraint name:
-- Run: SHOW CREATE TABLE organisational_units;
-- Look for CONSTRAINT `name` FOREIGN KEY and use that exact name

-- Step 2: Now drop the old tables
DROP TABLE IF EXISTS user_unit_admins;
DROP TABLE IF EXISTS unit_admin_roles;
DROP TABLE IF EXISTS organisational_unit_types;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify tables are dropped
SHOW TABLES LIKE 'organisational_unit%';









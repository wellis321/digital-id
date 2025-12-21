-- Safe Drop of organisational_unit_types Table
-- Run this if you get foreign key constraint errors

-- Step 1: First, check what foreign key constraints exist
-- Run this to see constraint names:
-- SHOW CREATE TABLE organisational_units;

-- Step 2: Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Step 3: Drop the foreign key constraint
-- MySQL doesn't support IF EXISTS for DROP FOREIGN KEY, so we need to know the exact name
-- Common constraint names (try each one, or check SHOW CREATE TABLE first):
-- ALTER TABLE organisational_units DROP FOREIGN KEY organisational_units_ibfk_2;
-- OR if that doesn't work, try:
-- ALTER TABLE organisational_units DROP FOREIGN KEY fk_org_units_unit_type;

-- Step 4: Now drop the table
DROP TABLE IF EXISTS organisational_unit_types;
DROP TABLE IF EXISTS unit_admin_roles;
DROP TABLE IF EXISTS user_unit_admins;

-- Step 5: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify tables are gone
SHOW TABLES LIKE 'organisational_unit_types';
SHOW TABLES LIKE 'unit_admin_roles';
SHOW TABLES LIKE 'user_unit_admins';


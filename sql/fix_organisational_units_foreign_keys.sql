-- Fix Foreign Key Constraints for Organisational Units Migration
-- Run this if you get foreign key constraint errors

SET FOREIGN_KEY_CHECKS = 0;

-- Find and drop foreign key constraint on unit_type_id
-- First, let's see what constraints exist
-- (Run this to see constraint names: SHOW CREATE TABLE organisational_units;)

-- Drop the foreign key constraint (adjust name if different)
ALTER TABLE organisational_units DROP FOREIGN KEY IF EXISTS organisational_units_ibfk_2;

-- Alternative: Drop all foreign keys on unit_type_id (if constraint name is unknown)
-- This will show errors for non-existent constraints but that's okay
-- You may need to check SHOW CREATE TABLE organisational_units; first to get exact constraint names

-- Now you can drop the tables
DROP TABLE IF EXISTS user_unit_admins;
DROP TABLE IF EXISTS unit_admin_roles;
DROP TABLE IF EXISTS organisational_unit_types;

SET FOREIGN_KEY_CHECKS = 1;









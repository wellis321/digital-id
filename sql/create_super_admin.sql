-- Create Super Admin User
-- 
-- This SQL script creates the main super admin user for the Digital ID system.
-- Run this after setting up the database and ensuring roles exist.
--
-- IMPORTANT: Change the password_hash below to a secure password hash!
-- You can generate a password hash using PHP: password_hash('your_password', PASSWORD_DEFAULT)
-- Or use the create_super_admin.php script which will prompt for a password.

-- First, ensure roles exist (should be created by migrations)
INSERT IGNORE INTO roles (name, description) VALUES
('superadmin', 'Super administrator with full system access'),
('organisation_admin', 'Organisation administrator with full access to their organisation'),
('staff', 'Standard staff member');

-- Create or get default organisation (if needed)
INSERT IGNORE INTO organisations (id, name, domain, seats_allocated) 
VALUES (1, 'Digital ID System', 'outlook.com', 1000);

-- Create super admin user
-- NOTE: Replace the password_hash with a secure hash before running!
-- Default password hash below is for 'ChangeMe123!' - CHANGE THIS!
INSERT INTO users (
    organisation_id, 
    email, 
    password_hash, 
    first_name, 
    last_name, 
    email_verified, 
    is_active
) VALUES (
    1,
    'digital-ids@outlook.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- CHANGE THIS PASSWORD HASH!
    'Digital ID',
    'Administrator',
    TRUE,
    TRUE
)
ON DUPLICATE KEY UPDATE 
    email_verified = TRUE,
    is_active = TRUE;

-- Assign superadmin role
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id 
FROM users u, roles r 
WHERE u.email = 'digital-ids@outlook.com' AND r.name = 'superadmin'
ON DUPLICATE KEY UPDATE user_id = user_id;


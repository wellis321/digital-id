-- Test Data Seed Script for Digital ID Application
-- Run this after setting up the test database schema
-- Usage: mysql -u root -p digital_ids_test < tests/seed-test-data.sql

-- Disable foreign key checks for clean inserts
SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing test data (if any)
DELETE FROM verification_logs;
DELETE FROM digital_id_cards;
DELETE FROM employees;
DELETE FROM user_roles;
DELETE FROM users;
DELETE FROM organisations;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- ROLES (if not already present from schema)
-- =====================================================
INSERT IGNORE INTO roles (id, name, description, created_at) VALUES
(1, 'staff', 'Regular staff member', NOW()),
(2, 'admin', 'Organisation administrator', NOW()),
(3, 'superadmin', 'System administrator', NOW());

-- =====================================================
-- TEST ORGANISATIONS
-- =====================================================
INSERT INTO organisations (id, name, domain, seats_allocated, seats_used, created_at) VALUES
(1, 'Test Organisation A', 'orga.test', 100, 0, NOW()),
(2, 'Test Organisation B', 'orgb.test', 50, 0, NOW()),
(3, 'Acme Corporation', 'acme.test', 200, 0, NOW());

-- =====================================================
-- TEST USERS
-- Password for all test users: password123
-- Hash generated with password_hash('password123', PASSWORD_DEFAULT)
-- =====================================================

-- Organisation A Users
INSERT INTO users (id, organisation_id, email, password_hash, first_name, last_name, email_verified, is_active, created_at) VALUES
(1, 1, 'staff1@orga.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 1, 1, NOW()),
(2, 1, 'staff2@orga.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 1, 1, NOW()),
(3, 1, 'admin@orga.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 1, 1, NOW());

-- Organisation B Users
INSERT INTO users (id, organisation_id, email, password_hash, first_name, last_name, email_verified, is_active, created_at) VALUES
(4, 2, 'staff1@orgb.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Wilson', 1, 1, NOW()),
(5, 2, 'admin@orgb.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Brown', 1, 1, NOW());

-- Superadmin (no organisation)
INSERT INTO users (id, organisation_id, email, password_hash, first_name, last_name, email_verified, is_active, created_at) VALUES
(6, NULL, 'superadmin@system.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Admin', 1, 1, NOW());

-- =====================================================
-- USER ROLES
-- =====================================================
INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES
-- Org A staff
(1, 1, NOW()),  -- staff1@orga.test = staff
(2, 1, NOW()),  -- staff2@orga.test = staff
(3, 2, NOW()),  -- admin@orga.test = admin
-- Org B staff
(4, 1, NOW()),  -- staff1@orgb.test = staff
(5, 2, NOW()),  -- admin@orgb.test = admin
-- Superadmin
(6, 3, NOW());  -- superadmin = superadmin

-- =====================================================
-- TEST EMPLOYEES
-- =====================================================
INSERT INTO employees (id, organisation_id, user_id, employee_reference, employee_number, display_reference, is_active, created_at) VALUES
-- Org A employees
(1, 1, 1, 'EMP001', 'EMP001', 'EMP001', 1, NOW()),
(2, 1, 2, 'EMP002', 'EMP002', 'EMP002', 1, NOW()),
(3, 1, 3, 'ADM001', 'ADM001', 'ADM001', 1, NOW()),
-- Org B employees
(4, 2, 4, 'EMPB001', 'EMPB001', 'EMPB001', 1, NOW()),
(5, 2, 5, 'ADMB001', 'ADMB001', 'ADMB001', 1, NOW());

-- =====================================================
-- TEST ID CARDS (with valid tokens)
-- =====================================================
INSERT INTO digital_id_cards (id, employee_id, qr_token, nfc_token, qr_token_expires_at, nfc_token_expires_at, expires_at, is_revoked, issued_at) VALUES
-- Active cards
(1, 1, 'f9f73786a952015faa1a10caf299a68df7618d0c17d7cc846639b4e44fe6b8ec', 'nfc_token_emp001_valid', DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 365 DAY), 0, NOW()),
(2, 2, 'a1b2c3d4e5f6789012345678901234567890123456789012345678901234abcd', 'nfc_token_emp002_valid', DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 365 DAY), 0, NOW()),
(3, 3, 'admin_qr_token_12345678901234567890123456789012345678901234', 'nfc_token_adm001_valid', DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 365 DAY), 0, NOW()),
(4, 4, 'orgb_emp_qr_token_123456789012345678901234567890123456789012', 'nfc_token_orgb_valid', DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 365 DAY), 0, NOW()),
(5, 5, 'orgb_admin_qr_token_1234567890123456789012345678901234567890', 'nfc_token_orgb_adm_valid', DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 5 MINUTE), DATE_ADD(NOW(), INTERVAL 365 DAY), 0, NOW());

-- =====================================================
-- UPDATE SEATS USED
-- =====================================================
UPDATE organisations SET seats_used = 3 WHERE id = 1;
UPDATE organisations SET seats_used = 2 WHERE id = 2;

-- =====================================================
-- SUMMARY
-- =====================================================
-- Test Accounts:
--
-- Organisation A (orga.test):
--   staff1@orga.test / password123  - Staff, Employee EMP001
--   staff2@orga.test / password123  - Staff, Employee EMP002
--   admin@orga.test / password123   - Admin, Employee ADM001
--
-- Organisation B (orgb.test):
--   staff1@orgb.test / password123  - Staff, Employee EMPB001
--   admin@orgb.test / password123   - Admin, Employee ADMB001
--
-- System:
--   superadmin@system.test / password123 - Superadmin
--
-- Valid QR Tokens for Testing:
--   EMP001: f9f73786a952015faa1a10caf299a68df7618d0c17d7cc846639b4e44fe6b8ec
--
SELECT 'Test data seeded successfully!' AS Status;

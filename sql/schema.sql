-- Digital ID Application Database Schema
-- UK English spelling used throughout

-- IMPORTANT: This schema assumes core authentication tables already exist.
-- If you haven't run the shared-auth migrations yet, use complete_schema.sql instead.
-- 
-- To set up the database:
-- 1. Run: shared-auth/migrations/core_schema.sql (creates organisations, users, roles, user_roles)
-- 2. Then run: sql/schema.sql (creates digital ID tables)
-- 
-- OR use the combined file: sql/complete_schema.sql (creates everything in order)

-- Employees table - Employee-specific data linked to users
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisation_id INT NOT NULL,
    employee_reference VARCHAR(100) NOT NULL,
    photo_path VARCHAR(255) NULL,
    id_card_data JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_employee_ref (organisation_id, employee_reference),
    INDEX idx_user (user_id),
    INDEX idx_organisation (organisation_id),
    INDEX idx_employee_reference (employee_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Digital ID cards table - Active ID card instances with tokens
CREATE TABLE IF NOT EXISTS digital_id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    qr_token VARCHAR(255) NULL UNIQUE,
    nfc_token VARCHAR(255) NULL UNIQUE,
    qr_token_expires_at TIMESTAMP NULL,
    nfc_token_expires_at TIMESTAMP NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_revoked BOOLEAN DEFAULT FALSE,
    revoked_at TIMESTAMP NULL,
    revoked_by INT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_qr_token (qr_token),
    INDEX idx_nfc_token (nfc_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_revoked (is_revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification logs table - Audit trail for all verification attempts
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_card_id INT NULL,
    employee_id INT NOT NULL,
    verification_type ENUM('visual', 'qr', 'nfc') NOT NULL,
    verified_by INT NULL,
    verified_by_ip VARCHAR(45) NULL,
    verified_by_device VARCHAR(255) NULL,
    verification_result ENUM('success', 'failed', 'expired', 'revoked') NOT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    notes TEXT NULL,
    FOREIGN KEY (id_card_id) REFERENCES digital_id_cards(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_id_card (id_card_id),
    INDEX idx_employee (employee_id),
    INDEX idx_verification_type (verification_type),
    INDEX idx_verification_result (verification_result),
    INDEX idx_verified_at (verified_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check-ins table - Attendance/safety check-ins
CREATE TABLE IF NOT EXISTS check_ins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    check_in_type ENUM('meeting', 'fire_drill', 'safety', 'door_access', 'lone_working', 'late_work') NOT NULL,
    location_id INT NULL,
    location_name VARCHAR(255) NULL,
    checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checked_out_at TIMESTAMP NULL,
    metadata JSON NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_check_in_type (check_in_type),
    INDEX idx_checked_in_at (checked_in_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Entra sync table - Microsoft Entra/365 integration (optional)
CREATE TABLE IF NOT EXISTS entra_sync (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    organisation_id INT NOT NULL,
    entra_user_id VARCHAR(255) NOT NULL,
    last_synced_at TIMESTAMP NULL,
    sync_status ENUM('active', 'pending', 'failed', 'disabled') DEFAULT 'pending',
    sync_error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_entra_user (entra_user_id),
    INDEX idx_employee (employee_id),
    INDEX idx_organisation (organisation_id),
    INDEX idx_sync_status (sync_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Organisations table extension for Entra integration settings
-- Note: Run these ALTER statements only if columns don't exist
-- You may need to check manually or use a migration script

-- ALTER TABLE organisations 
-- ADD COLUMN entra_enabled BOOLEAN DEFAULT FALSE,
-- ADD COLUMN entra_tenant_id VARCHAR(255) NULL,
-- ADD COLUMN entra_client_id VARCHAR(255) NULL;


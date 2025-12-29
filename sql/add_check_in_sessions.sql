-- Migration: Add Check-In Sessions and Microsoft 365 Integration
-- Extends check_ins table and adds new tables for session management and Microsoft 365 sync
--
-- NOTE: This SQL file uses MySQL 8.0+ syntax with IF NOT EXISTS.
-- For older MySQL versions, use the PHP migration script instead:
-- php sql/migrate_check_in_sessions.php

-- Extend check_ins table with additional fields
-- For MySQL < 8.0, run these individually and ignore errors if columns exist

-- Add session_id column (run manually if needed)
-- ALTER TABLE check_ins ADD COLUMN session_id INT NULL;

-- Add check_in_method column
-- ALTER TABLE check_ins ADD COLUMN check_in_method ENUM('qr_scan', 'manual', 'api') DEFAULT 'manual';

-- Add location_lat column
-- ALTER TABLE check_ins ADD COLUMN location_lat DECIMAL(10, 8) NULL;

-- Add location_lng column
-- ALTER TABLE check_ins ADD COLUMN location_lng DECIMAL(11, 8) NULL;

-- Add device_info column
-- ALTER TABLE check_ins ADD COLUMN device_info VARCHAR(255) NULL;

-- Add index for session_id (ignore error if exists)
-- ALTER TABLE check_ins ADD INDEX idx_session_id (session_id);

-- Create check_in_sessions table
CREATE TABLE IF NOT EXISTS check_in_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    session_type ENUM('fire_drill', 'fire_alarm', 'safety_meeting', 'emergency') NOT NULL,
    started_by INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    location_id INT NULL,
    location_name VARCHAR(255) NULL,
    metadata JSON NULL,
    microsoft_365_synced BOOLEAN DEFAULT FALSE,
    sharepoint_list_id VARCHAR(255) NULL,
    teams_channel_id VARCHAR(255) NULL,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (started_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_organisation (organisation_id),
    INDEX idx_started_at (started_at),
    INDEX idx_ended_at (ended_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for session_id in check_ins (run after sessions table exists)
-- First, add the column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND COLUMN_NAME = 'session_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE check_ins ADD COLUMN session_id INT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add other columns to check_ins
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND COLUMN_NAME = 'check_in_method');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE check_ins ADD COLUMN check_in_method ENUM(\'qr_scan\', \'manual\', \'api\') DEFAULT \'manual\'',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND COLUMN_NAME = 'location_lat');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE check_ins ADD COLUMN location_lat DECIMAL(10, 8) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND COLUMN_NAME = 'location_lng');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE check_ins ADD COLUMN location_lng DECIMAL(11, 8) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND COLUMN_NAME = 'device_info');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE check_ins ADD COLUMN device_info VARCHAR(255) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for session_id
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND INDEX_NAME = 'idx_session_id');
SET @sql = IF(@idx_exists = 0, 
    'ALTER TABLE check_ins ADD INDEX idx_session_id (session_id)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for session_id (after column and index exist)
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'check_ins' 
    AND CONSTRAINT_NAME = 'fk_check_ins_session');
SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE check_ins ADD CONSTRAINT fk_check_ins_session FOREIGN KEY (session_id) REFERENCES check_in_sessions(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create microsoft_365_sync_log table
CREATE TABLE IF NOT EXISTS microsoft_365_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    sync_type ENUM('check_in', 'session', 'attendance_report') NOT NULL,
    entity_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    sync_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    sync_error TEXT NULL,
    synced_at TIMESTAMP NULL,
    microsoft_365_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    INDEX idx_organisation (organisation_id),
    INDEX idx_sync_status (sync_status),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add Microsoft 365 integration settings to organisations table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'm365_sharepoint_site_url');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE organisations ADD COLUMN m365_sharepoint_site_url VARCHAR(500) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'm365_sharepoint_list_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE organisations ADD COLUMN m365_sharepoint_list_id VARCHAR(255) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'm365_teams_channel_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE organisations ADD COLUMN m365_teams_channel_id VARCHAR(255) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'm365_power_automate_webhook_url');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE organisations ADD COLUMN m365_power_automate_webhook_url VARCHAR(500) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'm365_sync_enabled');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE organisations ADD COLUMN m365_sync_enabled BOOLEAN DEFAULT FALSE',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

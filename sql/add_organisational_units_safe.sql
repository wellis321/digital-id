-- Migration: Add Organisational Units Tables
-- Safe to run multiple times - checks for existing tables first
-- UK English spelling used throughout

-- Check if organisational_units table exists, create if not
SET @dbname = DATABASE();
SET @tablename = 'organisational_units';
SET @tableExists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename
);

SET @preparedStatement = (SELECT IF(
    @tableExists > 0,
    'SELECT 1', -- Table exists, do nothing
    CONCAT('CREATE TABLE ', @tablename, ' (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organisation_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NULL,
        unit_type VARCHAR(100) NULL,
        parent_unit_id INT NULL,
        metadata JSON NULL,
        manager_user_id INT NULL,
        display_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_unit_id) REFERENCES ', @tablename, '(id) ON DELETE SET NULL,
        FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_organisation (organisation_id),
        INDEX idx_parent_unit (parent_unit_id),
        INDEX idx_unit_type (unit_type),
        INDEX idx_manager (manager_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')
));
PREPARE createIfNotExists FROM @preparedStatement;
EXECUTE createIfNotExists;
DEALLOCATE PREPARE createIfNotExists;

-- Check if organisational_unit_members table exists, create if not
SET @tablename = 'organisational_unit_members';
SET @tableExists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename
);

SET @preparedStatement = (SELECT IF(
    @tableExists > 0,
    'SELECT 1', -- Table exists, do nothing
    CONCAT('CREATE TABLE ', @tablename, ' (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unit_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(100) DEFAULT \'member\',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_unit_member (unit_id, user_id),
        INDEX idx_unit (unit_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')
));
PREPARE createIfNotExists FROM @preparedStatement;
EXECUTE createIfNotExists;
DEALLOCATE PREPARE createIfNotExists;


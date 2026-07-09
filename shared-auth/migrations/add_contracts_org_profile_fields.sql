-- Extend organisations table with Contracts application profile fields
-- These fields are needed by the Social Care Contracts Management application
-- for tender applications, regulatory registration, and contact information.
-- Safe to run multiple times (idempotent).

SET @dbname = DATABASE();
SET @tbl = 'organisations';

-- contract_number_prefix — custom prefix for auto-generated contract numbers
SET @col = 'contract_number_prefix';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(20) NULL DEFAULT NULL',
           ' COMMENT \'Custom prefix for auto-generated contract numbers (e.g. SCC-)\'',
           ' AFTER `name`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- company_registration_number
SET @col = 'company_registration_number';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Companies House registration number\'',
           ' AFTER `domain`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- care_inspectorate_registration
SET @col = 'care_inspectorate_registration';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Care Inspectorate (Scotland) or CQC (England) registration number\'',
           ' AFTER `company_registration_number`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- charity_number
SET @col = 'charity_number';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Charity Commission or OSCR registered charity number\'',
           ' AFTER `care_inspectorate_registration`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- vat_number
SET @col = 'vat_number';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'VAT registration number\'',
           ' AFTER `charity_number`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- registered_address
SET @col = 'registered_address';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Registered office address\'',
           ' AFTER `vat_number`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- trading_address
SET @col = 'trading_address';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Trading / operational address if different from registered address\'',
           ' AFTER `registered_address`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- phone
SET @col = 'phone';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Main organisation telephone number\'',
           ' AFTER `trading_address`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- website
SET @col = 'website';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(255) NULL DEFAULT NULL',
           ' COMMENT \'Organisation website URL\'',
           ' AFTER `phone`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- care_inspectorate_rating
SET @col = 'care_inspectorate_rating';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Most recent Care Inspectorate / CQC rating (e.g. Excellent, Good)\'',
           ' AFTER `website`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- last_inspection_date
SET @col = 'last_inspection_date';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATE NULL DEFAULT NULL',
           ' COMMENT \'Date of most recent regulatory inspection\'',
           ' AFTER `care_inspectorate_rating`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- main_contact_name
SET @col = 'main_contact_name';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(255) NULL DEFAULT NULL',
           ' COMMENT \'Primary contact person full name\'',
           ' AFTER `last_inspection_date`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- main_contact_email
SET @col = 'main_contact_email';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(255) NULL DEFAULT NULL',
           ' COMMENT \'Primary contact person email address\'',
           ' AFTER `main_contact_name`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- main_contact_phone
SET @col = 'main_contact_phone';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL',
           ' COMMENT \'Primary contact person telephone number\'',
           ' AFTER `main_contact_email`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- geographic_coverage
SET @col = 'geographic_coverage';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Geographic areas the organisation operates in\'',
           ' AFTER `main_contact_phone`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- service_types
SET @col = 'service_types';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Types of care services provided\'',
           ' AFTER `geographic_coverage`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- languages_spoken
SET @col = 'languages_spoken';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Languages spoken by staff, relevant for tender submissions\'',
           ' AFTER `service_types`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- specialist_expertise
SET @col = 'specialist_expertise';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL',
           ' COMMENT \'Specialist areas of expertise (e.g. dementia, learning disabilities)\'',
           ' AFTER `languages_spoken`')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ── Indexes ──────────────────────────────────────────────────────────────────

SET @idx = 'idx_care_inspectorate_reg';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND INDEX_NAME = @idx) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD INDEX `', @idx, '` (care_inspectorate_registration)')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx = 'idx_company_reg';
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND INDEX_NAME = @idx) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD INDEX `', @idx, '` (company_registration_number)')
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

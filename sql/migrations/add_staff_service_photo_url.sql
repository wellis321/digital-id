-- Add staff_service_photo_url to cache the photo URL from Staff Service
-- This is separate from photo_path (which is for locally-uploaded photos)
-- Safe to run multiple times

SET @dbname = DATABASE();
SET @tablename = 'employees';
SET @columnname = 'staff_service_photo_url';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(500) NULL COMMENT ''Cached photo URL from Staff Service (absolute URL requiring API key to fetch)'' AFTER signature_url')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

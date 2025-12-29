<?php
/**
 * Migration Script: Add Check-In Sessions and Microsoft 365 Integration
 * 
 * Run this script to add the check-in sessions functionality:
 * php sql/migrate_check_in_sessions.php
 */

require_once dirname(__DIR__) . '/config/config.php';

$db = getDbConnection();

echo "Starting migration: Check-In Sessions and Microsoft 365 Integration\n\n";

try {
    $db->beginTransaction();
    
    // Check and add columns to check_ins table
    echo "Extending check_ins table...\n";
    
    $columns = [
        'session_id' => "INT NULL",
        'check_in_method' => "ENUM('qr_scan', 'manual', 'api') DEFAULT 'manual'",
        'location_lat' => "DECIMAL(10, 8) NULL",
        'location_lng' => "DECIMAL(11, 8) NULL",
        'device_info' => "VARCHAR(255) NULL"
    ];
    
    foreach ($columns as $columnName => $columnDef) {
        try {
            $stmt = $db->query("SHOW COLUMNS FROM check_ins LIKE '{$columnName}'");
            if ($stmt->rowCount() == 0) {
                $db->exec("ALTER TABLE check_ins ADD COLUMN {$columnName} {$columnDef}");
                echo "  Added column: {$columnName}\n";
            } else {
                echo "  Column {$columnName} already exists, skipping\n";
            }
        } catch (PDOException $e) {
            echo "  Error adding column {$columnName}: " . $e->getMessage() . "\n";
        }
    }
    
    // Add index for session_id
    try {
        $stmt = $db->query("SHOW INDEX FROM check_ins WHERE Key_name = 'idx_session_id'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE check_ins ADD INDEX idx_session_id (session_id)");
            echo "  Added index: idx_session_id\n";
        } else {
            echo "  Index idx_session_id already exists, skipping\n";
        }
    } catch (PDOException $e) {
        echo "  Error adding index: " . $e->getMessage() . "\n";
    }
    
    // Create check_in_sessions table
    echo "\nCreating check_in_sessions table...\n";
    $db->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created check_in_sessions table\n";
    
    // Add foreign key for session_id
    try {
        $stmt = $db->query("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'check_ins' 
              AND CONSTRAINT_NAME = 'fk_check_ins_session'
        ");
        if ($stmt->rowCount() == 0) {
            $db->exec("
                ALTER TABLE check_ins 
                ADD CONSTRAINT fk_check_ins_session 
                FOREIGN KEY (session_id) REFERENCES check_in_sessions(id) ON DELETE SET NULL
            ");
            echo "  Added foreign key: fk_check_ins_session\n";
        } else {
            echo "  Foreign key fk_check_ins_session already exists, skipping\n";
        }
    } catch (PDOException $e) {
        echo "  Error adding foreign key: " . $e->getMessage() . "\n";
    }
    
    // Create microsoft_365_sync_log table
    echo "\nCreating microsoft_365_sync_log table...\n";
    $db->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created microsoft_365_sync_log table\n";
    
    // Add Microsoft 365 columns to organisations table
    echo "\nExtending organisations table...\n";
    $orgColumns = [
        'm365_sharepoint_site_url' => "VARCHAR(500) NULL",
        'm365_sharepoint_list_id' => "VARCHAR(255) NULL",
        'm365_teams_channel_id' => "VARCHAR(255) NULL",
        'm365_power_automate_webhook_url' => "VARCHAR(500) NULL",
        'm365_sync_enabled' => "BOOLEAN DEFAULT FALSE"
    ];
    
    foreach ($orgColumns as $columnName => $columnDef) {
        try {
            $stmt = $db->query("SHOW COLUMNS FROM organisations LIKE '{$columnName}'");
            if ($stmt->rowCount() == 0) {
                $db->exec("ALTER TABLE organisations ADD COLUMN {$columnName} {$columnDef}");
                echo "  Added column: {$columnName}\n";
            } else {
                echo "  Column {$columnName} already exists, skipping\n";
            }
        } catch (PDOException $e) {
            echo "  Error adding column {$columnName}: " . $e->getMessage() . "\n";
        }
    }
    
    $db->commit();
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    // Only rollback if transaction is still active
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\nMigration error: " . $e->getMessage() . "\n";
    echo "Note: Some changes may have been applied. Please check the database state.\n";
    exit(1);
}


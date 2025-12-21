<?php
/**
 * Migration Script: Convert to Simple Schema
 * Converts existing complex organisational units schema to simplified schema
 */

require_once dirname(__DIR__) . '/config/config.php';

echo "=== Migrating to Simple Schema ===\n\n";

try {
    $db = getDbConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check current structure
    echo "Checking current table structure...\n";
    $stmt = $db->query("DESCRIBE organisational_units");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "Current columns: " . implode(', ', $columnNames) . "\n\n";
    
    $hasOldSchema = in_array('unit_type_id', $columnNames);
    $hasNewSchema = in_array('unit_type', $columnNames);
    
    if ($hasNewSchema && !$hasOldSchema) {
        echo "✓ Already using simple schema. No migration needed.\n";
        exit(0);
    }
    
    echo "Starting migration...\n\n";
    
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Step 1: Drop old complex tables if they exist
    echo "1. Dropping old complex tables...\n";
    try {
        // First, drop foreign key constraints
        $stmt = $db->query("SHOW CREATE TABLE organisational_units");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($createTable['Create Table'])) {
            $createSql = $createTable['Create Table'];
            // Find all foreign key constraints
            preg_match_all("/CONSTRAINT `([^`]+)` FOREIGN KEY.*unit_type_id/i", $createSql, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $fkName) {
                    try {
                        $db->exec("ALTER TABLE organisational_units DROP FOREIGN KEY `$fkName`");
                        echo "   ✓ Dropped foreign key: $fkName\n";
                    } catch (Exception $e) {
                        // Ignore if already dropped
                    }
                }
            }
        }
        
        $db->exec("DROP TABLE IF EXISTS user_unit_admins");
        $db->exec("DROP TABLE IF EXISTS unit_admin_roles");
        $db->exec("DROP TABLE IF EXISTS organisational_unit_types");
        echo "   ✓ Dropped old tables\n";
    } catch (Exception $e) {
        echo "   ⚠ " . $e->getMessage() . "\n";
    }
    
    // Step 2: Add unit_type column if it doesn't exist
    if (!in_array('unit_type', $columnNames)) {
        echo "2. Adding unit_type column...\n";
        
        // Find position after 'name'
        $nameIndex = array_search('name', $columnNames);
        $afterColumn = $nameIndex !== false ? 'name' : 'organisation_id';
        
        $db->exec("ALTER TABLE organisational_units ADD COLUMN unit_type VARCHAR(100) NULL AFTER $afterColumn");
        echo "   ✓ Added unit_type column\n";
    } else {
        echo "2. unit_type column already exists\n";
    }
    
    // Step 3: Migrate data from unit_type_id to unit_type if needed
    if ($hasOldSchema) {
        echo "3. Migrating data from unit_type_id to unit_type...\n";
        
        // Check if organisational_unit_types table exists
        $stmt = $db->query("SHOW TABLES LIKE 'organisational_unit_types'");
        if ($stmt->fetch()) {
            $db->exec("
                UPDATE organisational_units ou
                LEFT JOIN organisational_unit_types ut ON ou.unit_type_id = ut.id
                SET ou.unit_type = ut.name
                WHERE ou.unit_type_id IS NOT NULL AND (ou.unit_type IS NULL OR ou.unit_type = '')
            ");
            echo "   ✓ Migrated unit type data\n";
        } else {
            echo "   ⚠ organisational_unit_types table doesn't exist, skipping data migration\n";
        }
        
        // Step 4: Drop foreign key constraint first, then drop unit_type_id column
        echo "4. Dropping unit_type_id column...\n";
        
        // Find and drop the foreign key constraint
        $stmt = $db->query("SHOW CREATE TABLE organisational_units");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($createTable['Create Table'])) {
            $createSql = $createTable['Create Table'];
            // Look for foreign key constraint name
            if (preg_match("/CONSTRAINT `([^`]+)` FOREIGN KEY.*unit_type_id/i", $createSql, $matches)) {
                $fkName = $matches[1];
                $db->exec("ALTER TABLE organisational_units DROP FOREIGN KEY `$fkName`");
                echo "   ✓ Dropped foreign key constraint: $fkName\n";
            }
        }
        
        $db->exec("ALTER TABLE organisational_units DROP COLUMN unit_type_id");
        echo "   ✓ Dropped unit_type_id column\n";
    }
    
    // Step 5: Drop code column if it exists
    if (in_array('code', $columnNames)) {
        echo "5. Dropping code column...\n";
        $db->exec("ALTER TABLE organisational_units DROP COLUMN code");
        echo "   ✓ Dropped code column\n";
    }
    
    // Step 6: Add display_order if missing
    if (!in_array('display_order', $columnNames)) {
        echo "6. Adding display_order column...\n";
        $db->exec("ALTER TABLE organisational_units ADD COLUMN display_order INT DEFAULT 0 AFTER manager_user_id");
        echo "   ✓ Added display_order column\n";
    }
    
    // Step 7: Ensure organisational_unit_members table exists
    echo "7. Checking organisational_unit_members table...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'organisational_unit_members'");
    if (!$stmt->fetch()) {
        echo "   Creating organisational_unit_members table...\n";
        $db->exec("
            CREATE TABLE organisational_unit_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unit_id INT NOT NULL,
                user_id INT NOT NULL,
                role VARCHAR(100) DEFAULT 'member',
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unit_id) REFERENCES organisational_units(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_unit_member (unit_id, user_id),
                INDEX idx_unit (unit_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✓ Created organisational_unit_members table\n";
    } else {
        echo "   ✓ Table already exists\n";
    }
    
    // Step 8: Migrate from old user_organisational_units if it exists
    $stmt = $db->query("SHOW TABLES LIKE 'user_organisational_units'");
    if ($stmt->fetch()) {
        echo "8. Migrating data from user_organisational_units...\n";
        $db->exec("
            INSERT INTO organisational_unit_members (unit_id, user_id, role, joined_at)
            SELECT organisational_unit_id, user_id, role_in_unit, assigned_at
            FROM user_organisational_units
            WHERE NOT EXISTS (
                SELECT 1 FROM organisational_unit_members 
                WHERE organisational_unit_members.unit_id = user_organisational_units.organisational_unit_id
                AND organisational_unit_members.user_id = user_organisational_units.user_id
            )
        ");
        echo "   ✓ Migrated member data\n";
        
        // Drop old table
        $db->exec("DROP TABLE user_organisational_units");
        echo "   ✓ Dropped old user_organisational_units table\n";
    }
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✓ Migration completed successfully!\n\n";
    
    // Verify final structure
    echo "Final table structure:\n";
    $stmt = $db->query("DESCRIBE organisational_units");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "organisational_units columns: " . implode(', ', $finalColumns) . "\n";
    
    $stmt = $db->query("DESCRIBE organisational_unit_members");
    $memberColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "organisational_unit_members columns: " . implode(', ', $memberColumns) . "\n";
    
} catch (Exception $e) {
    echo "\n✗ Migration failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}


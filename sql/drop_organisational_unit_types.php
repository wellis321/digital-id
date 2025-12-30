<?php
/**
 * Drop Organisational Unit Types Table
 * Safely finds and drops the foreign key constraint, then drops the table
 */

require_once dirname(__DIR__) . '/config/config.php';

try {
    $db = getDbConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Dropping Old Organisational Unit Types Table ===\n\n";
    
    // Step 1: Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'organisational_unit_types'");
    if (!$stmt->fetch()) {
        echo "✓ Table 'organisational_unit_types' doesn't exist. Nothing to do.\n";
        exit(0);
    }
    
    echo "Table exists. Finding foreign key constraints...\n\n";
    
    // Step 2: Find foreign key constraints
    $stmt = $db->query("SHOW CREATE TABLE organisational_units");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $createSql = $result['Create Table'];
    
    // Extract all foreign key constraints
    preg_match_all("/CONSTRAINT `([^`]+)` FOREIGN KEY.*organisational_unit_types/i", $createSql, $matches);
    
    if (!empty($matches[1])) {
        echo "Found " . count($matches[1]) . " foreign key constraint(s) to drop:\n";
        foreach ($matches[1] as $fkName) {
            echo "  - $fkName\n";
        }
        echo "\n";
        
        // Drop each constraint
        foreach ($matches[1] as $fkName) {
            try {
                $db->exec("ALTER TABLE organisational_units DROP FOREIGN KEY `$fkName`");
                echo "✓ Dropped constraint: $fkName\n";
            } catch (Exception $e) {
                echo "⚠ Could not drop $fkName: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "✓ No foreign key constraints found referencing organisational_unit_types\n";
    }
    
    // Step 3: Drop the tables
    echo "\nDropping old tables...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tablesToDrop = [
        'organisational_unit_types',
        'unit_admin_roles',
        'user_unit_admins'
    ];
    
    foreach ($tablesToDrop as $table) {
        try {
            $db->exec("DROP TABLE IF EXISTS `$table`");
            echo "✓ Dropped: $table\n";
        } catch (Exception $e) {
            echo "⚠ Could not drop $table: " . $e->getMessage() . "\n";
        }
    }
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✓ Cleanup complete!\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}









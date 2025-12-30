<?php
/**
 * Check and Drop Old Organisational Tables
 * Safely removes old complex schema tables
 */

require_once dirname(__DIR__) . '/config/config.php';

try {
    $db = getDbConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Checking for Old Tables ===\n\n";
    
    // Check which old tables exist
    $oldTables = [
        'organisational_unit_types',
        'unit_admin_roles',
        'user_unit_admins',
        'user_organisational_units'
    ];
    
    $existingTables = [];
    foreach ($oldTables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            $existingTables[] = $table;
            echo "✓ Found: $table\n";
        }
    }
    
    if (empty($existingTables)) {
        echo "\n✓ No old tables found. Migration already complete!\n";
        exit(0);
    }
    
    echo "\n=== Checking for Foreign Key Constraints ===\n";
    
    // Check organisational_units for any constraints referencing old tables
    $stmt = $db->query("SHOW CREATE TABLE organisational_units");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $createSql = $result['Create Table'];
    
    // Check if there's a constraint referencing organisational_unit_types
    if (strpos($createSql, 'organisational_unit_types') !== false) {
        echo "⚠ Found constraint referencing organisational_unit_types!\n";
        preg_match_all("/CONSTRAINT `([^`]+)` FOREIGN KEY.*organisational_unit_types/i", $createSql, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $fkName) {
                echo "  Dropping constraint: $fkName\n";
                $db->exec("ALTER TABLE organisational_units DROP FOREIGN KEY `$fkName`");
                echo "  ✓ Dropped\n";
            }
        }
    } else {
        echo "✓ No constraints found referencing old tables\n";
    }
    
    echo "\n=== Dropping Old Tables ===\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($existingTables as $table) {
        try {
            $db->exec("DROP TABLE IF EXISTS `$table`");
            echo "✓ Dropped: $table\n";
        } catch (Exception $e) {
            echo "✗ Failed to drop $table: " . $e->getMessage() . "\n";
        }
    }
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✓ Cleanup complete!\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}








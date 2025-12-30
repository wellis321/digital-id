<?php
/**
 * Find Foreign Key Constraints
 * This script will show you all foreign key constraints so you can drop them safely
 */

require_once dirname(__DIR__) . '/config/config.php';

try {
    $db = getDbConnection();
    
    echo "=== Foreign Key Constraints on organisational_units ===\n\n";
    
    // Get the CREATE TABLE statement
    $stmt = $db->query("SHOW CREATE TABLE organisational_units");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (isset($result['Create Table'])) {
        $createSql = $result['Create Table'];
        
        // Extract foreign key constraints
        preg_match_all("/CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)`/i", $createSql, $matches, PREG_SET_ORDER);
        
        if (empty($matches)) {
            echo "No foreign key constraints found.\n";
        } else {
            echo "Found " . count($matches) . " foreign key constraint(s):\n\n";
            foreach ($matches as $match) {
                $constraintName = $match[1];
                $columnName = $match[2];
                $referencedTable = $match[3];
                
                echo "Constraint: `$constraintName`\n";
                echo "  Column: `$columnName`\n";
                echo "  References: `$referencedTable`\n";
                
                if ($referencedTable === 'organisational_unit_types') {
                    echo "  ⚠ This references the old organisational_unit_types table!\n";
                    echo "  Drop command: ALTER TABLE organisational_units DROP FOREIGN KEY `$constraintName`;\n";
                }
                echo "\n";
            }
        }
        
        echo "\n=== Full CREATE TABLE Statement ===\n";
        echo $createSql . "\n";
        
    } else {
        echo "Could not retrieve table structure.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}








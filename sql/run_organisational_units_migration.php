<?php
/**
 * Run Organisational Units Migration
 * This script will create the organisational_units and organisational_unit_members tables
 */

require_once dirname(__DIR__) . '/config/config.php';

echo "=== Organisational Units Migration ===\n\n";

try {
    $db = getDbConnection();
    
    // Check if tables already exist
    $stmt = $db->query("SHOW TABLES LIKE 'organisational_units'");
    $unitsTableExists = $stmt->fetch();
    
    $stmt = $db->query("SHOW TABLES LIKE 'organisational_unit_members'");
    $membersTableExists = $stmt->fetch();
    
    if ($unitsTableExists && $membersTableExists) {
        echo "✓ Tables already exist!\n\n";
        echo "Checking table structure...\n";
        
        // Check columns
        $stmt = $db->query("DESCRIBE organisational_units");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('unit_type', $columns)) {
            echo "✓ Table structure is correct. Migration already completed.\n";
            exit(0);
        } else {
            echo "⚠ Table exists but missing columns. You may need to drop and recreate.\n";
            echo "Columns found: " . implode(', ', $columns) . "\n";
            exit(1);
        }
    }
    
    echo "Creating tables...\n\n";
    
    // Read and execute migration
    $migrationFile = dirname(__DIR__) . '/shared-auth/migrations/organisational_units_schema_simple.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip comments and empty statements
        }
        
        try {
            $db->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
            echo "⚠ Table already exists, skipping...\n";
        }
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "\nYou can now use the organisational structure management interface.\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Please run the migration manually:\n";
    echo "  mysql -u " . DB_USER . " -p " . DB_NAME . " < shared-auth/migrations/organisational_units_schema_simple.sql\n";
    exit(1);
}








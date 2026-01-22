<?php
/**
 * Simple PHP Setup Script for Test Database
 * Uses a simpler approach - executes SQL file more directly
 * Usage: php tests/setup-test-db-simple.php
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Test database name
$testDbName = 'digital_ids_test';

// Get database connection settings
$dbHost = getenv('DB_HOST') ?: DB_HOST;
$dbUser = getenv('DB_USER') ?: DB_USER;
$dbPass = getenv('DB_PASS') ?: DB_PASS;

echo "Setting up test database: $testDbName\n";
echo "Using host: $dbHost, user: $dbUser\n\n";

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host=$dbHost;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    echo "1. Creating database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$testDbName`");
    $pdo->exec("CREATE DATABASE `$testDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$testDbName`");
    echo "   ✓ Database created\n\n";
    
    // Read schema file
    $schemaFile = __DIR__ . '/../sql/complete_schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    echo "2. Reading schema file...\n";
    $sql = file_get_contents($schemaFile);
    echo "   ✓ Schema file loaded (" . strlen($sql) . " bytes)\n\n";
    
    // Execute SQL using exec() with multiple statements
    // PDO::exec() can handle multiple statements if we enable it
    echo "3. Executing schema...\n";
    
    // Split by semicolon but handle multi-line statements
    // Remove comments first
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split into statements
    $statements = preg_split('/;\s*(?=[A-Z])/i', $sql);
    
    $executed = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        // Skip empty statements
        if (empty($statement) || strlen($statement) < 10) {
            $skipped++;
            continue;
        }
        
        // Skip DELIMITER commands
        if (preg_match('/^DELIMITER/i', $statement)) {
            $skipped++;
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            $message = $e->getMessage();
            
            // Ignore expected errors
            if (strpos($message, 'already exists') !== false ||
                strpos($message, 'Duplicate') !== false ||
                strpos($message, 'Unknown prepared statement') !== false) {
                $skipped++;
            } else {
                // Store first few real errors
                if (count($errors) < 5) {
                    $errors[] = "Statement " . ($index + 1) . ": " . substr($message, 0, 100);
                }
            }
        }
    }
    
    echo "   ✓ Executed $executed statements";
    if ($skipped > 0) {
        echo " (skipped $skipped)";
    }
    echo "\n";
    
    if (!empty($errors)) {
        echo "\n   Warnings:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
    }
    echo "\n";
    
    // Verify tables
    echo "4. Verifying tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   ✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "     - $table\n";
    }
    
    // Verify database
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "\n5. Database verification:\n";
    echo "   Current database: $currentDb\n";
    
    if ($currentDb === $testDbName) {
        echo "   ✓ Connected to correct database\n";
    } else {
        echo "   ✗ WARNING: Expected '$testDbName' but got '$currentDb'\n";
    }
    
    echo "\n✓ Test database setup complete!\n";
    echo "\nYou can now view the tables in phpMyAdmin at:\n";
    echo "http://localhost:8888/phpMyAdmin5/index.php?route=/database/structure&db=$testDbName\n";
    
} catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check your database credentials in .env or config/config.php\n";
    echo "2. Make sure MySQL/MariaDB is running\n";
    echo "3. Verify DB_HOST, DB_USER, and DB_PASS are correct\n";
    echo "4. Check that the user has CREATE DATABASE permissions\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}



<?php
/**
 * PHP Setup Script for Test Database
 * Alternative to shell script - uses PHP database connection
 * Usage: php tests/setup-test-db.php
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Test database name (force test database, don't use production DB_NAME)
$testDbName = getenv('TEST_DB_NAME') ?: 'digital_ids_test';

// Get database connection (without database name)
$dbHost = getenv('DB_HOST') ?: DB_HOST;
$dbUser = getenv('DB_USER') ?: DB_USER;
$dbPass = getenv('DB_PASS') ?: DB_PASS;

echo "Setting up test database: $testDbName\n";

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host=$dbHost;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    echo "Creating database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$testDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$testDbName`");
    
    // Read and execute complete_schema.sql
    $schemaFile = __DIR__ . '/../sql/complete_schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    echo "Running schema...\n";
    $sql = file_get_contents($schemaFile);
    
    // Use a better approach: execute the entire SQL file using MySQL's SOURCE command equivalent
    // But since we can't use SOURCE in PDO, we'll parse it more carefully
    
    // Remove comments but preserve structure
    $lines = explode("\n", $sql);
    $cleanedLines = [];
    $inMultiLineComment = false;
    
    foreach ($lines as $line) {
        // Handle multi-line comments
        if (preg_match('/\/\*/', $line)) {
            $inMultiLineComment = true;
        }
        if (preg_match('/\*\//', $line)) {
            $inMultiLineComment = false;
            continue;
        }
        if ($inMultiLineComment) {
            continue;
        }
        
        // Remove single-line comments
        $line = preg_replace('/--.*$/', '', $line);
        
        // Skip empty lines
        if (trim($line) === '') {
            continue;
        }
        
        // Skip DELIMITER commands
        if (preg_match('/^\s*DELIMITER/i', $line)) {
            continue;
        }
        
        $cleanedLines[] = $line;
    }
    
    // Join lines back together
    $cleanedSql = implode("\n", $cleanedLines);
    
    // Split by semicolon, but keep multi-line statements together
    // Look for semicolons that are at the end of a line (not in strings)
    $statements = [];
    $currentStatement = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($cleanedSql); $i++) {
        $char = $cleanedSql[$i];
        $currentStatement .= $char;
        
        // Track string boundaries
        if (($char === '"' || $char === "'" || $char === '`') && ($i === 0 || $cleanedSql[$i-1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar) {
                $inString = false;
                $stringChar = '';
            }
        }
        
        // If we hit a semicolon outside of a string, we have a complete statement
        if ($char === ';' && !$inString) {
            $stmt = trim($currentStatement);
            if (!empty($stmt) && strlen($stmt) > 10) { // Minimum meaningful statement
                $statements[] = $stmt;
            }
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    $executed = 0;
    $errors = 0;
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement) || strlen($statement) < 10) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignore "table already exists" and "duplicate key" errors
            $message = $e->getMessage();
            if (strpos($message, 'already exists') === false 
                && strpos($message, 'Duplicate') === false
                && strpos($message, 'Duplicate entry') === false
                && strpos($message, 'Unknown prepared statement') === false) {
                // Only show first few errors to avoid spam
                if ($errors < 3) {
                    echo "Warning (statement " . ($index + 1) . "): " . substr($message, 0, 150) . "\n";
                }
                $errors++;
            }
        }
    }
    
    echo "Executed $executed SQL statements";
    if ($errors > 0) {
        echo " ($errors warnings suppressed)";
    }
    echo "\n";
    
    // Run migrations if they exist
    $migrationsDir = __DIR__ . '/../sql/migrations';
    if (is_dir($migrationsDir)) {
        echo "Running migrations...\n";
        $migrations = glob($migrationsDir . '/*.sql');
        foreach ($migrations as $migration) {
            echo "  Running: " . basename($migration) . "\n";
            $migrationSql = file_get_contents($migration);
            $migrationStatements = array_filter(
                array_map('trim', explode(';', $migrationSql)),
                function($stmt) {
                    $stmt = trim($stmt);
                    return !empty($stmt) 
                        && !preg_match('/^--/', $stmt)
                        && !preg_match('/^\/\*/', $stmt);
                }
            );
            
            foreach ($migrationStatements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') === false 
                        && strpos($e->getMessage(), 'Duplicate column') === false) {
                        echo "    Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
    
    // Verify tables were created
    echo "\nVerifying tables in database '$testDbName'...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    // Verify we're in the right database
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "\nCurrent database: $currentDb\n";
    
    if ($currentDb !== $testDbName) {
        echo "WARNING: Expected database '$testDbName' but connected to '$currentDb'\n";
    }
    
    echo "\nTest database setup complete!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check your database credentials in .env or config/config.php\n";
    echo "2. Make sure MySQL is running\n";
    echo "3. Verify DB_HOST, DB_USER, and DB_PASS are correct\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


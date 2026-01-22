<?php
/**
 * Verify Test Database Setup
 * Shows all tables in the test database
 * Usage: php tests/verify-database.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$testDbName = 'digital_ids_test';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=$testDbName;charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Test Database Verification ===\n\n";
    echo "Database: $testDbName\n";
    echo "Host: " . DB_HOST . "\n";
    echo "User: " . DB_USER . "\n\n";
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($tables as $table) {
        // Get row count
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $countStmt->fetchColumn();
        
        // Get table structure
        $descStmt = $pdo->query("DESCRIBE `$table`");
        $columns = $descStmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo sprintf("%-30s %5d rows, %2d columns\n", $table, $count, count($columns));
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ Database verification complete!\n";
    echo "\nTo view in phpMyAdmin:\n";
    echo "http://localhost:8888/phpMyAdmin5/index.php?route=/database/structure&db=$testDbName\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure the database '$testDbName' exists\n";
    echo "2. Run: php tests/setup-test-db-simple.php\n";
    echo "3. Check your database credentials\n";
    exit(1);
}



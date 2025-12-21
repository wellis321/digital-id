<?php
/**
 * Database Connection Test Script
 * Run this to diagnose database connection issues
 */

echo "=== Database Connection Test ===\n\n";

// Load environment variables
require_once __DIR__ . '/config/env_loader.php';

// Get database settings
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'digital_id';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

echo "Configuration:\n";
echo "  Host: $host\n";
echo "  Database: $dbname\n";
echo "  User: $user\n";
echo "  Password: " . (empty($pass) ? '(empty)' : '***') . "\n";
echo "  Charset: $charset\n\n";

// Test connection
try {
    $dsn = "mysql:host=$host;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    echo "Attempting connection...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✓ Connected to MySQL server successfully!\n\n";
    
    // Check if database exists
    echo "Checking if database '$dbname' exists...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "✓ Database '$dbname' exists!\n\n";
        
        // Try to use the database
        echo "Attempting to use database '$dbname'...\n";
        $pdo->exec("USE $dbname");
        echo "✓ Successfully connected to database '$dbname'!\n\n";
        
        // Check for tables
        echo "Checking for tables...\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠ No tables found. You need to run the schema:\n";
            echo "  mysql -u $user -p $dbname < sql/complete_schema.sql\n\n";
        } else {
            echo "✓ Found " . count($tables) . " table(s):\n";
            foreach ($tables as $table) {
                echo "  - $table\n";
            }
        }
    } else {
        echo "✗ Database '$dbname' does NOT exist!\n\n";
        echo "Create it with:\n";
        echo "  mysql -u $user -p -e \"CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"\n\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Connection failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Common issues:\n";
    echo "1. MySQL/MariaDB is not running\n";
    echo "2. Wrong database credentials in .env file\n";
    echo "3. Database doesn't exist\n";
    echo "4. User doesn't have permissions\n\n";
    
    echo "Troubleshooting:\n";
    echo "- Check if MySQL is running: mysql.server status (macOS)\n";
    echo "- Start MySQL: mysql.server start (macOS)\n";
    echo "- Check your .env file has correct credentials\n";
    echo "- Verify database exists: mysql -u $user -p -e 'SHOW DATABASES;'\n";
    
    exit(1);
}

echo "\n=== Test Complete ===\n";


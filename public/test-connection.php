<?php
/**
 * Database Connection Test
 * Use this to verify your database connection works
 * DELETE THIS FILE after testing for security
 */

// Try to load config
try {
    require_once dirname(__DIR__) . '/config/config.php';
    
    echo "<h1>Database Connection Test</h1>";
    echo "<p>Testing connection...</p>";
    
    // Test database connection
    $db = getDbConnection();
    
    if ($db) {
        echo "<p style='color: green;'><strong>✓ Database connection successful!</strong></p>";
        
        // Test query
        $stmt = $db->query("SELECT DATABASE() as db_name, VERSION() as db_version");
        $result = $stmt->fetch();
        
        echo "<h2>Database Information:</h2>";
        echo "<ul>";
        echo "<li><strong>Database:</strong> " . htmlspecialchars($result['db_name']) . "</li>";
        echo "<li><strong>Version:</strong> " . htmlspecialchars($result['db_version']) . "</li>";
        echo "</ul>";
        
        // Check if tables exist
        echo "<h2>Checking Tables:</h2>";
        $tables = ['users', 'organisations', 'employees', 'digital_id_cards'];
        echo "<ul>";
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch() ? '✓' : '✗';
            echo "<li>$exists $table</li>";
        }
        echo "</ul>";
        
        if ($exists === '✗') {
            echo "<p style='color: orange;'><strong>⚠ Warning:</strong> Some tables are missing. You need to run the database schema migrations.</p>";
            echo "<p>Run: <code>sql/complete_schema.sql</code> in your database.</p>";
        }
        
    } else {
        echo "<p style='color: red;'><strong>✗ Database connection failed!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Remember to delete this file after testing!</small></p>";
?>


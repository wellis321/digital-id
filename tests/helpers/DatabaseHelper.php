<?php
/**
 * Database Helper for Tests
 * Utilities for database operations in tests
 */

class DatabaseHelper {
    
    /**
     * Get database connection
     */
    public static function getConnection() {
        return getDbConnection();
    }
    
    /**
     * Truncate all tables (for test cleanup)
     * WARNING: Only use in test environment!
     */
    public static function truncateAllTables() {
        if (APP_ENV !== 'testing') {
            throw new Exception('truncateAllTables() can only be used in test environment');
        }
        
        $db = getDbConnection();
        
        // Disable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Get all tables
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        
        // Truncate each table
        foreach ($tables as $table) {
            $db->exec("TRUNCATE TABLE `$table`");
        }
        
        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
    
    /**
     * Run migrations on test database
     */
    public static function runMigrations() {
        $schemaFile = ROOT_PATH . '/sql/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $db = getDbConnection();
        $sql = file_get_contents($schemaFile);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $db->exec($statement);
                } catch (PDOException $e) {
                    // Ignore "table already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
    }
    
    /**
     * Count records in table
     */
    public static function countRecords($table, $where = []) {
        $db = getDbConnection();
        
        $sql = "SELECT COUNT(*) FROM `$table`";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "`$column` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Get last inserted ID
     */
    public static function lastInsertId() {
        $db = getDbConnection();
        return $db->lastInsertId();
    }
}


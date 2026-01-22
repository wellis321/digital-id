<?php
/**
 * Test Helper Functions
 * Common utilities for testing
 */

class TestHelper {
    
    /**
     * Create a test organisation
     */
    public static function createTestOrganisation($name = 'Test Organisation', $domain = 'test.local') {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            INSERT INTO organisations (name, domain, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$name, $domain]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Create a test user
     * Note: Roles are stored in user_roles table, not users table
     */
    public static function createTestUser($organisationId, $email, $password, $role = 'staff', $firstName = 'Test', $lastName = 'User') {
        $db = getDbConnection();
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user (no role column - roles are in user_roles table)
        $stmt = $db->prepare("
            INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, email_verified, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, 1, 1, NOW())
        ");
        $stmt->execute([$organisationId, $email, $passwordHash, $firstName, $lastName]);
        
        $userId = $db->lastInsertId();
        
        // Assign role via user_roles table
        if ($role) {
            // Get role ID
            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$role]);
            $roleData = $roleStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($roleData) {
                $roleId = $roleData['id'];
                $userRoleStmt = $db->prepare("
                    INSERT INTO user_roles (user_id, role_id, assigned_at) 
                    VALUES (?, ?, NOW())
                ");
                $userRoleStmt->execute([$userId, $roleId]);
            }
        }
        
        return $userId;
    }
    
    /**
     * Create a test employee
     * Note: Employees table doesn't have first_name/last_name - those are in users table
     */
    public static function createTestEmployee($organisationId, $userId, $employeeReference) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            INSERT INTO employees (organisation_id, user_id, employee_reference, is_active, created_at) 
            VALUES (?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$organisationId, $userId, $employeeReference]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Clean up test data
     */
    public static function cleanupTestData($organisationId) {
        $db = getDbConnection();
        
        // Delete in correct order (respecting foreign keys)
        $db->exec("DELETE FROM verification_logs WHERE employee_id IN (SELECT id FROM employees WHERE organisation_id = $organisationId)");
        $db->exec("DELETE FROM digital_id_cards WHERE employee_id IN (SELECT id FROM employees WHERE organisation_id = $organisationId)");
        $db->exec("DELETE FROM employees WHERE organisation_id = $organisationId");
        $db->exec("DELETE FROM users WHERE organisation_id = $organisationId");
        $db->exec("DELETE FROM organisations WHERE id = $organisationId");
    }
    
    /**
     * Login as a test user (set session)
     */
    public static function loginAsUser($userId, $organisationId, $email) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['organisation_id'] = $organisationId;
        $_SESSION['email'] = $email;
    }
    
    /**
     * Logout (clear session)
     */
    public static function logout() {
        $_SESSION = [];
    }
    
    /**
     * Get CSRF token from session
     */
    public static function getCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION[CSRF_TOKEN_NAME] ?? null;
    }
}


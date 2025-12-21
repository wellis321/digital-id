<?php
/**
 * Admin Notifications
 * Provides notification data for administrators
 */

class AdminNotifications {
    
    /**
     * Get users who need employee numbers assigned
     * These are users who have verified their email but don't have employee records
     */
    public static function getUsersNeedingEmployeeNumbers($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.email_verified,
                u.created_at,
                DATEDIFF(NOW(), u.created_at) as days_since_registration
            FROM users u 
            WHERE u.organisation_id = ? 
            AND u.email_verified = TRUE 
            AND u.is_active = TRUE
            AND u.id NOT IN (
                SELECT user_id 
                FROM employees 
                WHERE organisation_id = ?
            )
            ORDER BY u.created_at ASC
        ");
        $stmt->execute([$organisationId, $organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get count of users needing employee numbers
     */
    public static function getCountUsersNeedingEmployeeNumbers($organisationId) {
        $users = self::getUsersNeedingEmployeeNumbers($organisationId);
        return count($users);
    }
}


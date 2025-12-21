<?php
/**
 * Contact Helper Class
 * Finds appropriate contact persons for users who need help
 */

class ContactHelper {
    
    /**
     * Find the best contact person for a user in their organisation
     * Priority: organisation_admin > any active user in org > superadmin
     * 
     * @param int $userId The user ID who needs help
     * @return array|null Contact person details or null if none found
     */
    public static function findContactPerson($userId) {
        $db = getDbConnection();
        $user = Auth::getUser();
        
        if (!$user || !$user['organisation_id']) {
            return null;
        }
        
        $organisationId = $user['organisation_id'];
        
        // First, try to find an organisation admin in the same organisation
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, 'organisation_admin' as role_type
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.organisation_id = ?
            AND r.name = 'organisation_admin'
            AND u.id != ?
            AND u.is_active = TRUE
            AND u.email_verified = TRUE
            ORDER BY u.created_at ASC
            LIMIT 1
        ");
        $stmt->execute([$organisationId, $userId]);
        $contact = $stmt->fetch();
        
        if ($contact) {
            return $contact;
        }
        
        // If no org admin, find any active verified user in the organisation
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, 'staff' as role_type
            FROM users u
            WHERE u.organisation_id = ?
            AND u.id != ?
            AND u.is_active = TRUE
            AND u.email_verified = TRUE
            ORDER BY u.created_at ASC
            LIMIT 1
        ");
        $stmt->execute([$organisationId, $userId]);
        $contact = $stmt->fetch();
        
        if ($contact) {
            return $contact;
        }
        
        // If no one in the organisation, find a superadmin
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, 'superadmin' as role_type
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.name = 'superadmin'
            AND u.is_active = TRUE
            AND u.email_verified = TRUE
            ORDER BY u.created_at ASC
            LIMIT 1
        ");
        $stmt->execute();
        $contact = $stmt->fetch();
        
        return $contact;
    }
    
    /**
     * Get organisation details
     * 
     * @param int $organisationId
     * @return array|null Organisation details or null
     */
    public static function getOrganisationDetails($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
        $stmt->execute([$organisationId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all organisation admins for an organisation
     * 
     * @param int $organisationId
     * @return array Array of admin users
     */
    public static function getOrganisationAdmins($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.organisation_id = ?
            AND r.name = 'organisation_admin'
            AND u.is_active = TRUE
            AND u.email_verified = TRUE
            ORDER BY u.first_name, u.last_name
        ");
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all superadmins
     * 
     * @return array Array of superadmin users
     */
    public static function getSuperAdmins() {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.name = 'superadmin'
            AND u.is_active = TRUE
            AND u.email_verified = TRUE
            ORDER BY u.first_name, u.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}


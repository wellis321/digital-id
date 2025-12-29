<?php
/**
 * Staff Sync Service
 * Handles syncing staff data from Staff Service to Digital ID
 */

class StaffSyncService {
    
    /**
     * Sync all staff for an organisation
     * @param int $organisationId
     * @return array Results with success count and errors
     */
    public static function syncAllStaff($organisationId) {
        
        if (!defined('USE_STAFF_SERVICE') || !USE_STAFF_SERVICE) {
            return ['success' => false, 'message' => 'Staff Service integration is not enabled'];
        }
        
        require_once SRC_PATH . '/classes/StaffServiceClient.php';
        
        if (!StaffServiceClient::isAvailable()) {
            return ['success' => false, 'message' => 'Staff Service is not available'];
        }
        
        // Get all staff from Staff Service
        $staffList = StaffServiceClient::searchStaff('');
        
        if (empty($staffList)) {
            return ['success' => true, 'synced' => 0, 'errors' => []];
        }
        
        $synced = 0;
        $errors = [];
        $skippedByOrgId = 0;
        $db = getDbConnection();
        
        foreach ($staffList as $staffMember) {
            
            // Note: We don't filter by Digital ID's organisation_id because:
            // 1. The API key is already scoped to a specific organisation in Staff Service
            // 2. The API only returns staff from the API key's organisation
            // 3. Digital ID's organisation_id may differ from Staff Service's organisation_id
            // 4. We match staff by employee_reference instead, which is organisation-agnostic
            
            try {
                // Check if employee exists in Digital ID by employee_reference
                $employeeReference = $staffMember['employee_reference'] ?? '';
                $employee = null;
                
                if (!empty($employeeReference)) {
                    $employee = Employee::findByReference($organisationId, $employeeReference);
                }
                
                // Also try to find by email if employee_reference search failed
                if (!$employee && !empty($staffMember['email'])) {
                    $db = getDbConnection();
                    $stmt = $db->prepare("
                        SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
                        FROM employees e
                        JOIN users u ON e.user_id = u.id
                        JOIN organisations o ON e.organisation_id = o.id
                        WHERE e.organisation_id = ? AND u.email = ?
                    ");
                    $stmt->execute([$organisationId, $staffMember['email']]);
                    $employee = $stmt->fetch();
                }
                
                if ($employee) {
                    // Employee exists, sync data
                    if (!empty($staffMember['id'])) {
                        // Link if not already linked
                        if (empty($employee['staff_service_person_id'])) {
                            Employee::linkToStaffService($employee['id'], $staffMember['id']);
                        }
                        // Sync data
                        Employee::syncFromStaffService($staffMember['id'], $employee['id']);
                        $synced++;
                    }
                } else {
                    // Employee doesn't exist - try to auto-create
                    // First, find or create user account
                    $db = getDbConnection();
                    $email = $staffMember['email'] ?? '';
                    $userId = null;
                    
                    if (!empty($email)) {
                        // Try to find existing user
                        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND organisation_id = ?");
                        $stmt->execute([$email, $organisationId]);
                        $user = $stmt->fetch();
                        
                        if ($user) {
                            $userId = $user['id'];
                        } else {
                            // Create new user account (without password - they can set it later)
                            $firstName = $staffMember['first_name'] ?? '';
                            $lastName = $staffMember['last_name'] ?? '';
                            $tempPassword = bin2hex(random_bytes(16)); // Temporary password
                            
                            // Get organisation domain for Auth::register
                            $stmt = $db->prepare("SELECT domain FROM organisations WHERE id = ?");
                            $stmt->execute([$organisationId]);
                            $org = $stmt->fetch();
                            $domain = $org['domain'] ?? null; // null will let Auth::register extract from email
                            
                            try {
                                $result = Auth::register($email, $tempPassword, $firstName, $lastName, $domain);
                                if (is_array($result) && isset($result['success']) && $result['success']) {
                                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND organisation_id = ?");
                                    $stmt->execute([$email, $organisationId]);
                                    $newUser = $stmt->fetch();
                                    if ($newUser) {
                                        $userId = $newUser['id'];
                                        // Mark email as unverified since we created it automatically
                                        $db->prepare("UPDATE users SET email_verified = 0 WHERE id = ?")->execute([$userId]);
                                    }
                                }
                            } catch (Exception $e) {
                                error_log('Error creating user during sync: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    if ($userId && !empty($employeeReference)) {
                        // Create employee record
                        $photoPath = !empty($staffMember['photo_path']) ? $staffMember['photo_path'] : null;
                        $result = Employee::createWithStaffService(
                            $userId,
                            $organisationId,
                            $employeeReference,
                            null, // display_reference - will be auto-generated
                            $photoPath,
                            $staffMember['id'] ?? null // staff_service_person_id
                        );
                        
                        if ($result['success']) {
                            $synced++;
                        } else {
                            $errors[] = 'Failed to create employee for "' . ($staffMember['first_name'] ?? '') . ' ' . ($staffMember['last_name'] ?? '') . '": ' . ($result['message'] ?? 'Unknown error');
                        }
                    } elseif (empty($employeeReference)) {
                        $errors[] = 'Cannot create employee for "' . ($staffMember['first_name'] ?? '') . ' ' . ($staffMember['last_name'] ?? '') . '" - missing employee reference';
                    } elseif (empty($email)) {
                        $errors[] = 'Cannot create employee for "' . ($staffMember['first_name'] ?? '') . ' ' . ($staffMember['last_name'] ?? '') . '" - missing email address';
                    } else {
                        $errors[] = 'Failed to create user account for "' . ($staffMember['email'] ?? 'N/A') . '"';
                    }
                }
            } catch (Exception $e) {
                $errors[] = 'Error syncing staff member: ' . $e->getMessage();
                error_log('Staff sync error: ' . $e->getMessage());
            }
        }
        
        return [
            'success' => true,
            'synced' => $synced,
            'total' => count($staffList),
            'skipped_by_org_id' => $skippedByOrgId,
            'errors' => $errors
        ];
    }
    
    /**
     * Sync single staff member
     * @param int $personId Staff Service person ID
     * @return bool Success
     */
    public static function syncStaffMember($personId) {
        if (!defined('USE_STAFF_SERVICE') || !USE_STAFF_SERVICE) {
            return false;
        }
        
        require_once SRC_PATH . '/classes/StaffServiceClient.php';
        
        $staffData = StaffServiceClient::getStaffMember($personId);
        if (!$staffData) {
            return false;
        }
        
        $db = getDbConnection();
        
        // Find employee by staff_service_person_id or employee_reference
        $stmt = $db->prepare("
            SELECT id FROM employees 
            WHERE staff_service_person_id = ? 
            OR (organisation_id = ? AND employee_reference = ?)
        ");
        $stmt->execute([
            $personId,
            $staffData['organisation_id'] ?? 0,
            $staffData['employee_reference'] ?? ''
        ]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            return false;
        }
        
        // Link if not already linked
        if (empty($employee['staff_service_person_id'])) {
            Employee::linkToStaffService($employee['id'], $personId);
        }
        
        // Sync data
        return Employee::syncFromStaffService($personId, $employee['id']);
    }
    
    /**
     * Sync staff by user ID
     * @param int $userId User ID
     * @return bool Success
     */
    public static function syncStaffByUserId($userId) {
        if (!defined('USE_STAFF_SERVICE') || !USE_STAFF_SERVICE) {
            return false;
        }
        
        require_once SRC_PATH . '/classes/StaffServiceClient.php';
        
        $staffData = StaffServiceClient::getStaffByUserId($userId);
        if (!$staffData || !isset($staffData['id'])) {
            return false;
        }
        
        $db = getDbConnection();
        
        // Find employee by user_id
        $stmt = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
        $stmt->execute([$userId]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            return false;
        }
        
        // Link if not already linked
        if (empty($employee['staff_service_person_id'])) {
            Employee::linkToStaffService($employee['id'], $staffData['id']);
        }
        
        // Sync data
        return Employee::syncFromStaffService($staffData['id'], $employee['id']);
    }
    
    /**
     * Update local employee record with Staff Service data
     * @param int $employeeId Digital ID employee ID
     * @param array $staffData Staff Service data
     * @return bool Success
     */
    public static function updateLocalEmployee($employeeId, $staffData) {
        $db = getDbConnection();
        
        try {
            // Get signature
            $signatureUrl = null;
            if (isset($staffData['id'])) {
                require_once SRC_PATH . '/classes/StaffServiceClient.php';
                $signatureData = StaffServiceClient::getStaffSignature($staffData['id']);
                if ($signatureData && isset($signatureData['signature_url'])) {
                    $signatureUrl = $signatureData['signature_url'];
                }
            }
            
            $updates = [];
            $values = [];
            
            // Map Staff Service fields
            if (isset($staffData['employee_reference'])) {
                $updates[] = "employee_reference = ?";
                $values[] = $staffData['employee_reference'];
            }
            
            if (isset($staffData['photo_path'])) {
                $updates[] = "photo_path = ?";
                $values[] = $staffData['photo_path'];
            }
            
            if (isset($staffData['is_active'])) {
                $updates[] = "is_active = ?";
                $values[] = $staffData['is_active'] ? 1 : 0;
            }
            
            if ($signatureUrl !== null) {
                $updates[] = "signature_url = ?";
                $values[] = $signatureUrl;
            }
            
            // Update sync timestamp
            $updates[] = "last_synced_from_staff_service = CURRENT_TIMESTAMP";
            
            if (!empty($updates)) {
                $values[] = $employeeId;
                $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($values);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error updating local employee: ' . $e->getMessage());
            return false;
        }
    }
}


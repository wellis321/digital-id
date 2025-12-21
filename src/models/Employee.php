<?php
/**
 * Employee Model
 * Handles employee data and operations
 */

class Employee {
    
    /**
     * Create a new employee
     * @param int $userId User ID
     * @param int $organisationId Organisation ID
     * @param string $employeeNumber Internal employee number from HR/payroll (required, non-editable)
     * @param string|null $displayReference Display reference for ID card (optional, auto-generated if not provided)
     * @param string|null $photoPath Path to employee photo
     * @return array Success/failure result
     */
    public static function create($userId, $organisationId, $employeeNumber, $displayReference = null, $photoPath = null) {
        $db = getDbConnection();
        
        // Trim and validate employee number (required - from HR/payroll)
        $employeeNumber = trim($employeeNumber);
        if (empty($employeeNumber)) {
            return ['success' => false, 'message' => 'Employee number is required. This should be the employee number from your HR or payroll system.'];
        }
        
        try {
            $db->beginTransaction();
            
            // Check if employee number already exists for this organisation
            $stmt = $db->prepare("
                SELECT id FROM employees 
                WHERE organisation_id = ? AND employee_number = ?
            ");
            $stmt->execute([$organisationId, $employeeNumber]);
            if ($stmt->fetch()) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Employee number "' . htmlspecialchars($employeeNumber) . '" already exists in this organisation.'];
            }
            
            // Generate display reference if not provided
            if (empty($displayReference)) {
                require_once SRC_PATH . '/classes/ReferenceGenerator.php';
                $displayReference = ReferenceGenerator::generateDisplayReference($organisationId, $employeeNumber);
                
                if (!$displayReference) {
                    $db->rollBack();
                    return ['success' => false, 'message' => 'Failed to generate display reference. Please configure reference settings for your organisation or provide a display reference manually.'];
                }
            } else {
                // Validate provided display reference
                $displayReference = trim($displayReference);
                require_once SRC_PATH . '/classes/ReferenceGenerator.php';
                $validation = ReferenceGenerator::validateDisplayReference($organisationId, $displayReference);
                if (!$validation['valid']) {
                    $db->rollBack();
                    return ['success' => false, 'message' => $validation['message']];
                }
            }
            
            // Get user details for ID card data
            $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            // Create employee
            $stmt = $db->prepare("
                INSERT INTO employees (user_id, organisation_id, employee_number, employee_reference, display_reference, photo_path, id_card_data)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Generate initial ID card data JSON (use display_reference for display)
            $idCardData = json_encode([
                'employee_number' => $employeeNumber, // Internal reference (not shown)
                'display_reference' => $displayReference, // Shown on ID card
                'full_name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                'organization' => [
                    'id' => $organisationId,
                    'name' => self::getOrganisationName($organisationId)
                ],
                'issued_at' => date('c'),
                'valid_until' => date('c', strtotime('+' . ID_CARD_EXPIRY_DAYS . ' days')),
                'verification_levels' => ['visual', 'qr', 'nfc']
            ]);
            
            // For backwards compatibility, also store in employee_reference
            // But this will be deprecated - new code should use display_reference
            $stmt->execute([
                $userId,
                $organisationId,
                $employeeNumber,
                $displayReference, // employee_reference for backwards compatibility
                $displayReference, // display_reference (new field)
                $photoPath,
                $idCardData
            ]);
            
            $employeeId = $db->lastInsertId();
            
            $db->commit();
            
            return ['success' => true, 'employee_id' => $employeeId, 'display_reference' => $displayReference];
        } catch (PDOException $e) {
            $db->rollBack();
            // Check for duplicate key error
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'unique_org_employee_ref') !== false || strpos($e->getMessage(), 'employee_number') !== false) {
                    return ['success' => false, 'message' => 'Employee number "' . htmlspecialchars($employeeNumber) . '" already exists in this organisation.'];
                } elseif (strpos($e->getMessage(), 'unique_org_display_ref') !== false || strpos($e->getMessage(), 'display_reference') !== false) {
                    return ['success' => false, 'message' => 'Display reference "' . htmlspecialchars($displayReference) . '" already exists in this organisation.'];
                }
            }
            return ['success' => false, 'message' => 'Failed to create employee: ' . $e->getMessage()];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Failed to create employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get employee by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get employee by user ID
     */
    public static function findByUserId($userId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE e.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Get employee by organisation and reference
     */
    public static function findByReference($organisationId, $employeeReference) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE e.organisation_id = ? AND e.employee_reference = ?
        ");
        $stmt->execute([$organisationId, $employeeReference]);
        return $stmt->fetch();
    }
    
    /**
     * Get all employees for an organisation
     */
    public static function getByOrganisation($organisationId, $activeOnly = true) {
        $db = getDbConnection();
        $sql = "
            SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE e.organisation_id = ?
        ";
        
        if ($activeOnly) {
            $sql .= " AND e.is_active = TRUE";
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update employee
     * Note: employee_number cannot be updated (it comes from HR/payroll systems)
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        
        // Get current employee to check organisation
        $currentEmployee = self::findById($id);
        if (!$currentEmployee) {
            return ['success' => false, 'message' => 'Employee not found.'];
        }
        
        $organisationId = $currentEmployee['organisation_id'];
        
        // Prevent updating employee_number (it's from HR/payroll and should never change)
        if (isset($data['employee_number'])) {
            return ['success' => false, 'message' => 'Employee number cannot be changed. This is set from your HR or payroll system and is integral to other systems.'];
        }
        
        // If updating display_reference, validate uniqueness (excluding current employee)
        if (isset($data['display_reference'])) {
            $displayReference = trim($data['display_reference']);
            if (empty($displayReference)) {
                return ['success' => false, 'message' => 'Display reference cannot be empty.'];
            }
            
            // Check if the new display reference already exists for another employee in this organisation
            $stmt = $db->prepare("
                SELECT id FROM employees 
                WHERE organisation_id = ? AND display_reference = ? AND id != ?
            ");
            $stmt->execute([$organisationId, $displayReference, $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Display reference "' . htmlspecialchars($displayReference) . '" already exists in this organisation. Each display reference must be unique within your organisation.'];
            }
        }
        
        // Allow updating: display_reference, photo_path, photo_approval_status, photo_pending_path, photo_rejection_reason, is_active
        // Also allow employee_reference for backwards compatibility during migration
        $allowedFields = ['display_reference', 'employee_reference', 'photo_path', 'photo_approval_status', 'photo_pending_path', 'photo_rejection_reason', 'is_active'];
        $updates = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Update ID card data JSON if display reference changed
        if (isset($data['display_reference'])) {
            $idCardData = json_decode($currentEmployee['id_card_data'], true);
            if ($idCardData) {
                $idCardData['display_reference'] = $data['display_reference'];
                $idCardData['updated_at'] = date('c');
                $updates[] = "id_card_data = ?";
                $values[] = json_encode($idCardData);
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No valid fields to update.'];
        }
        
        try {
            $values[] = $id;
            
            $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true];
        } catch (PDOException $e) {
            // Check for duplicate key error
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'unique_org_display_ref') !== false || strpos($e->getMessage(), 'display_reference') !== false) {
                    $ref = isset($data['display_reference']) ? htmlspecialchars($data['display_reference']) : '';
                    return ['success' => false, 'message' => 'Display reference "' . $ref . '" already exists in this organisation.'];
                }
            }
            return ['success' => false, 'message' => 'Failed to update employee: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update ID card data JSON
     */
    public static function updateIdCardData($id, $idCardData) {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE employees SET id_card_data = ? WHERE id = ?");
        $stmt->execute([json_encode($idCardData), $id]);
        return ['success' => true];
    }
    
    /**
     * Get ID card data as JSON
     */
    public static function getIdCardData($id) {
        $employee = self::findById($id);
        if (!$employee || !$employee['id_card_data']) {
            return null;
        }
        return json_decode($employee['id_card_data'], true);
    }
    
    /**
     * Get organisation name
     */
    private static function getOrganisationName($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT name FROM organisations WHERE id = ?");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        return $org ? $org['name'] : '';
    }
}


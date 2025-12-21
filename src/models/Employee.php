<?php
/**
 * Employee Model
 * Handles employee data and operations
 */

class Employee {
    
    /**
     * Create a new employee
     */
    public static function create($userId, $organisationId, $employeeReference, $photoPath = null) {
        $db = getDbConnection();
        
        try {
            $db->beginTransaction();
            
            // Check if employee reference is unique for this organisation
            $stmt = $db->prepare("
                SELECT id FROM employees 
                WHERE organisation_id = ? AND employee_reference = ?
            ");
            $stmt->execute([$organisationId, $employeeReference]);
            if ($stmt->fetch()) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Employee reference already exists for this organisation.'];
            }
            
            // Create employee
            $stmt = $db->prepare("
                INSERT INTO employees (user_id, organisation_id, employee_reference, photo_path, id_card_data)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            // Generate initial ID card data JSON
            $user = Auth::getUser();
            $idCardData = json_encode([
                'employee_reference' => $employeeReference,
                'full_name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                'organization' => [
                    'id' => $organisationId,
                    'name' => self::getOrganisationName($organisationId)
                ],
                'issued_at' => date('c'),
                'valid_until' => date('c', strtotime('+' . ID_CARD_EXPIRY_DAYS . ' days')),
                'verification_levels' => ['visual', 'qr', 'nfc']
            ]);
            
            $stmt->execute([
                $userId,
                $organisationId,
                $employeeReference,
                $photoPath,
                $idCardData
            ]);
            
            $employeeId = $db->lastInsertId();
            
            $db->commit();
            
            return ['success' => true, 'employee_id' => $employeeId];
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
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        
        $allowedFields = ['employee_reference', 'photo_path', 'is_active'];
        $updates = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Update ID card data JSON if employee reference changed
        if (isset($data['employee_reference'])) {
            $employee = self::findById($id);
            if ($employee) {
                $idCardData = json_decode($employee['id_card_data'], true);
                if ($idCardData) {
                    $idCardData['employee_reference'] = $data['employee_reference'];
                    $idCardData['updated_at'] = date('c');
                    $updates[] = "id_card_data = ?";
                    $values[] = json_encode($idCardData);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No valid fields to update.'];
        }
        
        $values[] = $id;
        
        $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        return ['success' => true];
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


<?php
/**
 * Verification Service
 * Handles all verification operations and logging
 */

class VerificationService {
    
    /**
     * Verify employee by token (QR or NFC)
     */
    public static function verifyByToken($token, $type = 'qr') {
        $validation = DigitalID::validateToken($token, $type);
        
        if (!$validation['valid']) {
            self::logVerification(null, $validation['id_card']['employee_id'] ?? null, $type, 'failed', $validation['reason']);
            return [
                'success' => false,
                'reason' => $validation['reason'],
                'message' => self::getErrorMessage($validation['reason'])
            ];
        }
        
        $idCard = $validation['id_card'];
        $employee = Employee::findById($idCard['employee_id']);
        
        if (!$employee || !$employee['is_active']) {
            self::logVerification($idCard['id'], $idCard['employee_id'], $type, 'failed', 'employee_inactive');
            return [
                'success' => false,
                'reason' => 'employee_inactive',
                'message' => 'Employee is not active.'
            ];
        }
        
        // Log successful verification
        self::logVerification($idCard['id'], $idCard['employee_id'], $type, 'success');
        
        return [
            'success' => true,
            'employee' => $employee,
            'id_card' => $idCard,
            'verification_type' => $type
        ];
    }
    
    /**
     * Verify employee by reference (visual/manual lookup)
     */
    public static function verifyByReference($organisationId, $employeeReference) {
        $employee = Employee::findByReference($organisationId, $employeeReference);
        
        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found.'
            ];
        }
        
        if (!$employee['is_active']) {
            return [
                'success' => false,
                'message' => 'Employee is not active.'
            ];
        }
        
        // Get active ID card
        $idCard = DigitalID::getOrCreateIdCard($employee['id']);
        
        if (!$idCard || $idCard['is_revoked']) {
            return [
                'success' => false,
                'message' => 'ID card is not valid.'
            ];
        }
        
        // Log visual verification
        self::logVerification($idCard['id'], $employee['id'], 'visual', 'success');
        
        return [
            'success' => true,
            'employee' => $employee,
            'id_card' => $idCard,
            'verification_type' => 'visual'
        ];
    }
    
    /**
     * Log verification attempt
     */
    public static function logVerification($idCardId, $employeeId, $type, $result, $reason = null) {
        $db = getDbConnection();
        
        $verifiedBy = Auth::isLoggedIn() ? Auth::getUserId() : null;
        $verifiedByIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $verifiedByDevice = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $db->prepare("
            INSERT INTO verification_logs 
            (id_card_id, employee_id, verification_type, verified_by, verified_by_ip, verified_by_device, verification_result, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $idCardId,
            $employeeId,
            $type,
            $verifiedBy,
            $verifiedByIp,
            $verifiedByDevice,
            $result,
            $reason
        ]);
    }
    
    /**
     * Get error message for verification failure reason
     */
    private static function getErrorMessage($reason) {
        $messages = [
            'token_not_found' => 'Invalid verification token.',
            'expired' => 'Verification token has expired. Please request a new one.',
            'revoked' => 'This ID card has been revoked.',
            'card_expired' => 'This ID card has expired.',
            'employee_inactive' => 'Employee is not active.'
        ];
        
        return $messages[$reason] ?? 'Verification failed.';
    }
    
    /**
     * Get verification history for an employee
     */
    public static function getVerificationHistory($employeeId, $limit = 50) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT vl.*, u.first_name as verifier_first_name, u.last_name as verifier_last_name
            FROM verification_logs vl
            LEFT JOIN users u ON vl.verified_by = u.id
            WHERE vl.employee_id = ?
            ORDER BY vl.verified_at DESC
            LIMIT ?
        ");
        $stmt->execute([$employeeId, $limit]);
        return $stmt->fetchAll();
    }
}


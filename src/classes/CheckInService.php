<?php
/**
 * Check-In Service
 * Handles check-in operations for fire drills and safety events
 */

require_once SRC_PATH . '/classes/CheckInSession.php';
require_once SRC_PATH . '/models/Employee.php';
require_once SRC_PATH . '/classes/DigitalID.php';
require_once SRC_PATH . '/classes/VerificationService.php';

class CheckInService {
    
    /**
     * Create a new check-in session
     */
    public static function createSession($organisationId, $sessionName, $sessionType, $startedBy, $locationName = null, $locationId = null, $metadata = null) {
        // Validate session type
        $validTypes = ['fire_drill', 'fire_alarm', 'safety_meeting', 'emergency'];
        if (!in_array($sessionType, $validTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid session type.'
            ];
        }
        
        // Validate user has permission
        if (!RBAC::isOrganisationAdmin() && Auth::getOrganisationId() != $organisationId) {
            return [
                'success' => false,
                'message' => 'Permission denied.'
            ];
        }
        
        $session = CheckInSession::create(
            $organisationId,
            $sessionName,
            $sessionType,
            $startedBy,
            $locationName,
            $locationId,
            $metadata
        );
        
        if ($session) {
            // Trigger Microsoft 365 sync if enabled
            self::triggerMicrosoft365Sync($organisationId, 'session.created', $session);
            
            return [
                'success' => true,
                'session' => $session,
                'message' => 'Check-in session created successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create check-in session.'
        ];
    }
    
    /**
     * Check in an employee
     * Supports both QR token and manual employee reference
     */
    public static function checkIn($sessionId, $token = null, $employeeReference = null, $organisationId = null, $method = 'manual', $locationLat = null, $locationLng = null, $deviceInfo = null) {
        $db = getDbConnection();
        
        // Get session
        $session = CheckInSession::findById($sessionId);
        if (!$session) {
            return [
                'success' => false,
                'message' => 'Check-in session not found.'
            ];
        }
        
        // Check if session is ended
        if ($session['ended_at']) {
            return [
                'success' => false,
                'message' => 'This check-in session has ended.'
            ];
        }
        
        $employee = null;
        
        // If token provided, verify via QR code
        if ($token) {
            $verification = VerificationService::verifyByToken($token, 'qr');
            if (!$verification['success']) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired QR code: ' . ($verification['message'] ?? 'Verification failed.')
                ];
            }
            $employee = $verification['employee'];
            $method = 'qr_scan';
        }
        // If employee reference provided, look up manually
        elseif ($employeeReference && $organisationId) {
            $employee = Employee::findByReference($organisationId, $employeeReference);
            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Employee not found.'
                ];
            }
            $method = 'manual';
        }
        else {
            return [
                'success' => false,
                'message' => 'Either a QR token or employee reference must be provided.'
            ];
        }
        
        // Check if employee belongs to session organisation
        if ($employee['organisation_id'] != $session['organisation_id']) {
            return [
                'success' => false,
                'message' => 'Employee does not belong to this organisation.'
            ];
        }
        
        // Check if already checked in (and not checked out)
        $existingCheckIn = self::getActiveCheckIn($sessionId, $employee['id']);
        if ($existingCheckIn) {
            return [
                'success' => false,
                'message' => 'Employee is already checked in to this session.',
                'check_in' => $existingCheckIn
            ];
        }
        
        // Create check-in record
        try {
            $stmt = $db->prepare("
                INSERT INTO check_ins 
                (employee_id, check_in_type, session_id, location_name, location_id, 
                 check_in_method, location_lat, location_lng, device_info, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Map session type to check_in_type
            $checkInType = self::mapSessionTypeToCheckInType($session['session_type']);
            
            $metadata = [
                'session_name' => $session['session_name'],
                'session_type' => $session['session_type'],
                'checked_in_by' => Auth::isLoggedIn() ? Auth::getUserId() : null
            ];
            
            $stmt->execute([
                $employee['id'],
                $checkInType,
                $sessionId,
                $session['location_name'],
                $session['location_id'],
                $method,
                $locationLat,
                $locationLng,
                $deviceInfo,
                json_encode($metadata)
            ]);
            
            $checkInId = $db->lastInsertId();
            
            // Get the full check-in record
            $checkIn = self::getCheckInById($checkInId);
            
            // Trigger Microsoft 365 sync if enabled
            self::triggerMicrosoft365Sync($session['organisation_id'], 'check_in', $checkIn);
            
            return [
                'success' => true,
                'check_in' => $checkIn,
                'message' => 'Checked in successfully.'
            ];
        } catch (Exception $e) {
            error_log("Check-in error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record check-in: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check out an employee
     */
    public static function checkOut($sessionId, $employeeId) {
        $db = getDbConnection();
        
        // Get active check-in
        $checkIn = self::getActiveCheckIn($sessionId, $employeeId);
        if (!$checkIn) {
            return [
                'success' => false,
                'message' => 'No active check-in found for this employee.'
            ];
        }
        
        // Update check-out time
        $stmt = $db->prepare("
            UPDATE check_ins 
            SET checked_out_at = CURRENT_TIMESTAMP
            WHERE id = ? AND checked_out_at IS NULL
        ");
        
        $stmt->execute([$checkIn['id']]);
        
        if ($stmt->rowCount() > 0) {
            $checkIn['checked_out_at'] = date('Y-m-d H:i:s');
            
            // Get full check-in record
            $fullCheckIn = self::getCheckInById($checkIn['id']);
            
            // Trigger Microsoft 365 sync if enabled
            $session = CheckInSession::findById($sessionId);
            if ($session) {
                self::triggerMicrosoft365Sync($session['organisation_id'], 'check_in', $fullCheckIn);
            }
            
            return [
                'success' => true,
                'check_in' => $fullCheckIn,
                'message' => 'Checked out successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to check out.'
        ];
    }
    
    /**
     * Get all check-ins for a session
     */
    public static function getSessionCheckIns($sessionId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT ci.*,
                   e.first_name, e.last_name, e.employee_reference, e.display_reference,
                   u.email as employee_email
            FROM check_ins ci
            INNER JOIN employees e ON ci.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE ci.session_id = ?
            ORDER BY ci.checked_in_at DESC
        ");
        
        $stmt->execute([$sessionId]);
        $checkIns = $stmt->fetchAll();
        
        foreach ($checkIns as &$checkIn) {
            if ($checkIn['metadata']) {
                $checkIn['metadata'] = json_decode($checkIn['metadata'], true);
            }
        }
        
        return $checkIns;
    }
    
    /**
     * Get active check-in for employee in session
     */
    public static function getActiveCheckIn($sessionId, $employeeId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT ci.*
            FROM check_ins ci
            WHERE ci.session_id = ? 
              AND ci.employee_id = ?
              AND ci.checked_out_at IS NULL
            ORDER BY ci.checked_in_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$sessionId, $employeeId]);
        $checkIn = $stmt->fetch();
        
        if ($checkIn && $checkIn['metadata']) {
            $checkIn['metadata'] = json_decode($checkIn['metadata'], true);
        }
        
        return $checkIn;
    }
    
    /**
     * Get check-in by ID
     */
    public static function getCheckInById($checkInId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT ci.*,
                   e.first_name, e.last_name, e.employee_reference, e.display_reference,
                   u.email as employee_email
            FROM check_ins ci
            INNER JOIN employees e ON ci.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE ci.id = ?
        ");
        
        $stmt->execute([$checkInId]);
        $checkIn = $stmt->fetch();
        
        if ($checkIn && $checkIn['metadata']) {
            $checkIn['metadata'] = json_decode($checkIn['metadata'], true);
        }
        
        return $checkIn;
    }
    
    /**
     * End a check-in session
     */
    public static function endSession($sessionId) {
        $session = CheckInSession::findById($sessionId);
        if (!$session) {
            return [
                'success' => false,
                'message' => 'Session not found.'
            ];
        }
        
        if ($session['ended_at']) {
            return [
                'success' => false,
                'message' => 'Session is already ended.'
            ];
        }
        
        $ended = CheckInSession::end($sessionId);
        
        if ($ended) {
            return [
                'success' => true,
                'message' => 'Session ended successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to end session.'
        ];
    }
    
    /**
     * Get active sessions for organisation
     */
    public static function getActiveSessions($organisationId) {
        return CheckInSession::findActive($organisationId);
    }
    
    /**
     * Map session type to check_in_type
     */
    private static function mapSessionTypeToCheckInType($sessionType) {
        $mapping = [
            'fire_drill' => 'fire_drill',
            'fire_alarm' => 'fire_drill',
            'safety_meeting' => 'safety',
            'emergency' => 'safety'
        ];
        
        return $mapping[$sessionType] ?? 'safety';
    }
    
    /**
     * Trigger Microsoft 365 sync for check-in events
     */
    private static function triggerMicrosoft365Sync($organisationId, $eventType, $data) {
        // Check if Microsoft 365 sync is enabled
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT m365_sync_enabled 
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_sync_enabled']) {
            return; // Sync not enabled
        }
        
        require_once SRC_PATH . '/classes/Microsoft365Integration.php';
        
        try {
            // Sync to SharePoint
            if ($eventType === 'check_in') {
                Microsoft365Integration::syncCheckInToSharePoint($organisationId, $data);
            }
            
            // Trigger Power Automate
            Microsoft365Integration::triggerPowerAutomate($organisationId, $eventType, $data);
            
            // Send Teams notification for session start
            if ($eventType === 'session.created') {
                $message = "Check-in session started: {$data['session_name']}\n";
                $message .= "Type: " . ucfirst(str_replace('_', ' ', $data['session_type'])) . "\n";
                $message .= "Location: " . ($data['location_name'] ?? 'N/A');
                Microsoft365Integration::sendTeamsNotification($organisationId, $message, "Check-In Session Started", $data['id'] ?? null);
            }
        } catch (Exception $e) {
            // Log error but don't break check-in flow
            error_log("Microsoft 365 sync error: " . $e->getMessage());
        }
    }
}


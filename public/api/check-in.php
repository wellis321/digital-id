<?php
/**
 * Check-In API Endpoint
 * Handles employee check-in and check-out operations
 * 
 * POST /api/check-in.php - Check in employee
 * GET /api/check-in.php?session_id=X&employee_id=Y - Get check-in status
 * DELETE /api/check-in.php?session_id=X&employee_id=Y - Check out employee
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json');

// Require authentication for all operations
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$result = ['success' => false, 'message' => ''];

require_once SRC_PATH . '/classes/CheckInService.php';

try {
    switch ($method) {
        case 'POST':
            // Check in employee
            // For API endpoints, we accept both form-encoded POST (with CSRF) and JSON POST (without CSRF for external integrations)
            $isJsonRequest = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
            
            if ($isJsonRequest) {
                // JSON request - parse body
                $input = json_decode(file_get_contents('php://input'), true);
                $_POST = array_merge($_POST, $input ?? []);
            } else {
                // Form-encoded request - validate CSRF
                if (!CSRF::validatePost()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                    exit;
                }
            }
            
            $sessionId = $_POST['session_id'] ?? null;
            $token = $_POST['token'] ?? null;
            $employeeReference = $_POST['employee_reference'] ?? null;
            $organisationId = $_POST['organisation_id'] ?? null;
            $checkInMethod = $_POST['method'] ?? 'manual';
            $locationLat = isset($_POST['location_lat']) ? (float)$_POST['location_lat'] : null;
            $locationLng = isset($_POST['location_lng']) ? (float)$_POST['location_lng'] : null;
            $deviceInfo = $_POST['device_info'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }
            
            $result = CheckInService::checkIn(
                $sessionId,
                $token,
                $employeeReference,
                $organisationId,
                $checkInMethod,
                $locationLat,
                $locationLng,
                $deviceInfo
            );
            break;
            
        case 'GET':
            // Get check-in status
            $sessionId = $_GET['session_id'] ?? null;
            $employeeId = $_GET['employee_id'] ?? null;
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }
            
            // If employee_id not provided, use current user's employee
            if (!$employeeId) {
                require_once SRC_PATH . '/models/Employee.php';
                $employee = Employee::findByUserId(Auth::getUserId());
                if (!$employee) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    exit;
                }
                $employeeId = $employee['id'];
            }
            
            $checkIn = CheckInService::getActiveCheckIn($sessionId, $employeeId);
            
            if ($checkIn) {
                $result = [
                    'success' => true,
                    'checked_in' => true,
                    'check_in' => $checkIn
                ];
            } else {
                $result = [
                    'success' => true,
                    'checked_in' => false
                ];
            }
            break;
            
        case 'DELETE':
            // Check out employee
            parse_str(file_get_contents('php://input'), $deleteParams);
            $sessionId = $deleteParams['session_id'] ?? $_GET['session_id'] ?? null;
            $employeeId = $deleteParams['employee_id'] ?? $_GET['employee_id'] ?? null;
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }
            
            // If employee_id not provided, use current user's employee
            if (!$employeeId) {
                require_once SRC_PATH . '/models/Employee.php';
                $employee = Employee::findByUserId(Auth::getUserId());
                if (!$employee) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    exit;
                }
                $employeeId = $employee['id'];
            }
            
            $result = CheckInService::checkOut($sessionId, $employeeId);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
    }
} catch (Exception $e) {
    error_log("Check-in API error: " . $e->getMessage());
    http_response_code(500);
    $result = [
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ];
}

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);


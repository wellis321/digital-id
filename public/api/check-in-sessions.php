<?php
/**
 * Check-In Sessions API Endpoint
 * Handles check-in session management
 * 
 * POST /api/check-in-sessions.php - Create new session
 * GET /api/check-in-sessions.php - List sessions
 * GET /api/check-in-sessions.php?id=X - Get session details
 * PATCH /api/check-in-sessions.php - Update session (end, update metadata)
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json');

// Require authentication
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$result = ['success' => false, 'message' => ''];

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

try {
    switch ($method) {
        case 'POST':
            // Create new session
            if (!CSRF::validatePost()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }
            
            // Require organisation admin
            RBAC::requireOrganisationAdmin();
            
            $organisationId = Auth::getOrganisationId();
            $sessionName = $_POST['session_name'] ?? '';
            $sessionType = $_POST['session_type'] ?? '';
            $locationName = $_POST['location_name'] ?? null;
            $locationId = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
            $metadata = isset($_POST['metadata']) ? json_decode($_POST['metadata'], true) : null;
            
            if (empty($sessionName) || empty($sessionType)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session name and type are required']);
                exit;
            }
            
            $result = CheckInService::createSession(
                $organisationId,
                $sessionName,
                $sessionType,
                Auth::getUserId(),
                $locationName,
                $locationId,
                $metadata
            );
            break;
            
        case 'GET':
            // Get session(s)
            $sessionId = $_GET['id'] ?? null;
            $organisationId = Auth::getOrganisationId();
            
            if ($sessionId) {
                // Get single session with check-ins
                $session = CheckInSession::findById($sessionId);
                
                if (!$session) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Session not found']);
                    exit;
                }
                
                // Check permission
                if ($session['organisation_id'] != $organisationId && !RBAC::isSuperAdmin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Permission denied']);
                    exit;
                }
                
                $checkIns = CheckInService::getSessionCheckIns($sessionId);
                $checkInCount = CheckInSession::getCheckInCount($sessionId);
                
                $result = [
                    'success' => true,
                    'session' => $session,
                    'check_ins' => $checkIns,
                    'check_in_count' => $checkInCount
                ];
            } else {
                // List sessions
                $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                
                if ($activeOnly) {
                    $sessions = CheckInService::getActiveSessions($organisationId);
                } else {
                    $sessions = CheckInSession::findAll($organisationId, $limit, $offset);
                }
                
                $result = [
                    'success' => true,
                    'sessions' => $sessions
                ];
            }
            break;
            
        case 'PATCH':
            // Update session
            if (!CSRF::validatePost()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }
            
            // Require organisation admin
            RBAC::requireOrganisationAdmin();
            
            $sessionId = $_POST['session_id'] ?? null;
            $action = $_POST['action'] ?? '';
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }
            
            $session = CheckInSession::findById($sessionId);
            if (!$session) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Session not found']);
                exit;
            }
            
            // Check permission
            if ($session['organisation_id'] != Auth::getOrganisationId() && !RBAC::isSuperAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            
            if ($action === 'end') {
                $result = CheckInService::endSession($sessionId);
            } elseif ($action === 'update_metadata') {
                $metadata = isset($_POST['metadata']) ? json_decode($_POST['metadata'], true) : null;
                if ($metadata) {
                    $updated = CheckInSession::updateMetadata($sessionId, $metadata);
                    $result = [
                        'success' => $updated,
                        'message' => $updated ? 'Metadata updated successfully' : 'Failed to update metadata'
                    ];
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Metadata is required']);
                    exit;
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
    }
} catch (Exception $e) {
    error_log("Check-in sessions API error: " . $e->getMessage());
    http_response_code(500);
    $result = [
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ];
}

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);


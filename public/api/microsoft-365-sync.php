<?php
/**
 * Microsoft 365 Sync API Endpoint
 * Handles manual sync triggers and sync status queries
 * 
 * POST /api/microsoft-365-sync.php - Trigger manual sync
 * GET /api/microsoft-365-sync.php - Get sync status
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json');

// Require authentication
Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$result = ['success' => false, 'message' => ''];

require_once SRC_PATH . '/classes/Microsoft365Integration.php';
require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

try {
    switch ($method) {
        case 'POST':
            // Trigger manual sync
            if (!CSRF::validatePost()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }
            
            $organisationId = Auth::getOrganisationId();
            $syncType = $_POST['sync_type'] ?? '';
            $entityId = isset($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;
            
            if (!$syncType || !$entityId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Sync type and entity ID are required']);
                exit;
            }
            
            if ($syncType === 'check_in') {
                $checkIn = CheckInService::getCheckInById($entityId);
                if (!$checkIn) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Check-in not found']);
                    exit;
                }
                
                $result = Microsoft365Integration::syncCheckInToSharePoint($organisationId, $checkIn);
            } elseif ($syncType === 'session') {
                $session = CheckInSession::findById($entityId);
                if (!$session || $session['organisation_id'] != $organisationId) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Session not found']);
                    exit;
                }
                
                $result = Microsoft365Integration::syncSessionToSharePoint($organisationId, $session);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid sync type']);
                exit;
            }
            break;
            
        case 'GET':
            // Get sync status
            $organisationId = Auth::getOrganisationId();
            $entityId = isset($_GET['entity_id']) ? (int)$_GET['entity_id'] : null;
            $entityType = $_GET['entity_type'] ?? '';
            
            $db = getDbConnection();
            
            if ($entityId && $entityType) {
                // Get sync status for specific entity
                $stmt = $db->prepare("
                    SELECT * FROM microsoft_365_sync_log 
                    WHERE organisation_id = ? 
                      AND entity_id = ? 
                      AND entity_type = ?
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([$organisationId, $entityId, $entityType]);
                $log = $stmt->fetch();
                
                if ($log) {
                    $result = [
                        'success' => true,
                        'sync_status' => $log['sync_status'],
                        'synced_at' => $log['synced_at'],
                        'error' => $log['sync_error']
                    ];
                } else {
                    $result = [
                        'success' => true,
                        'sync_status' => 'pending',
                        'synced_at' => null,
                        'error' => null
                    ];
                }
            } else {
                // Get recent sync logs
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $stmt = $db->prepare("
                    SELECT * FROM microsoft_365_sync_log 
                    WHERE organisation_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?
                ");
                $stmt->execute([$organisationId, $limit]);
                $logs = $stmt->fetchAll();
                
                $result = [
                    'success' => true,
                    'logs' => $logs
                ];
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
    }
} catch (Exception $e) {
    error_log("Microsoft 365 sync API error: " . $e->getMessage());
    http_response_code(500);
    $result = [
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ];
}

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);


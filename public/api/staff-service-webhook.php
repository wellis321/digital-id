<?php
/**
 * Staff Service Webhook Handler
 * Receives real-time updates from Staff Service
 * 
 * Endpoint: POST /api/staff-service-webhook.php
 * 
 * Events handled:
 * - person.created: New staff member created in Staff Service
 * - person.updated: Staff member updated in Staff Service
 * - person.deactivated: Staff member deactivated
 * - signature.uploaded: Signature uploaded/updated
 * - photo.updated: Photo updated
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get raw POST data
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

// Verify webhook signature (if configured)
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$webhookSecret = getenv('STAFF_SERVICE_WEBHOOK_SECRET') ?: '';

if (!empty($webhookSecret)) {
    $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
    if (!hash_equals($expectedSignature, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid webhook signature']);
        exit;
    }
}

// Validate required fields
if (!isset($data['event']) || !isset($data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: event and data']);
    exit;
}

$event = $data['event'];
$eventData = $data['data'];

require_once SRC_PATH . '/classes/StaffSyncService.php';
require_once SRC_PATH . '/models/Employee.php';

$result = ['success' => false, 'message' => ''];

try {
    switch ($event) {
        case 'person.created':
        case 'person.updated':
            if (!isset($eventData['id'])) {
                throw new Exception('Missing person ID in event data');
            }
            
            $personId = (int)$eventData['id'];
            $synced = StaffSyncService::syncStaffMember($personId);
            
            if ($synced) {
                $result = ['success' => true, 'message' => 'Staff member synced successfully'];
            } else {
                $result = ['success' => false, 'message' => 'Failed to sync staff member'];
            }
            break;
            
        case 'person.deactivated':
            if (!isset($eventData['id'])) {
                throw new Exception('Missing person ID in event data');
            }
            
            $personId = (int)$eventData['id'];
            $db = getDbConnection();
            
            // Find employee linked to this person
            $stmt = $db->prepare("SELECT id FROM employees WHERE staff_service_person_id = ?");
            $stmt->execute([$personId]);
            $employee = $stmt->fetch();
            
            if ($employee) {
                // Deactivate employee
                Employee::update($employee['id'], ['is_active' => false]);
                
                // Automatically revoke all ID cards for this employee
                require_once SRC_PATH . '/classes/DigitalID.php';
                $stmt = $db->prepare("SELECT id FROM digital_id_cards WHERE employee_id = ? AND is_revoked = FALSE");
                $stmt->execute([$employee['id']]);
                $idCards = $stmt->fetchAll();
                
                foreach ($idCards as $idCard) {
                    DigitalID::revoke($idCard['id'], null); // null = revoked by system/Staff Service
                }
                
                $result = ['success' => true, 'message' => 'Employee deactivated and ID cards revoked'];
            } else {
                $result = ['success' => false, 'message' => 'Employee not found'];
            }
            break;
            
        case 'signature.uploaded':
        case 'signature.updated':
            if (!isset($eventData['person_id'])) {
                throw new Exception('Missing person ID in event data');
            }
            
            $personId = (int)$eventData['person_id'];
            
            // Sync to update signature
            $synced = StaffSyncService::syncStaffMember($personId);
            
            if ($synced) {
                $result = ['success' => true, 'message' => 'Signature synced successfully'];
            } else {
                $result = ['success' => false, 'message' => 'Failed to sync signature'];
            }
            break;
            
        case 'photo.updated':
            if (!isset($eventData['person_id'])) {
                throw new Exception('Missing person ID in event data');
            }
            
            $personId = (int)$eventData['person_id'];
            
            // Sync to update photo
            $synced = StaffSyncService::syncStaffMember($personId);
            
            if ($synced) {
                $result = ['success' => true, 'message' => 'Photo synced successfully'];
            } else {
                $result = ['success' => false, 'message' => 'Failed to sync photo'];
            }
            break;
            
        default:
            $result = ['success' => false, 'message' => 'Unknown event type: ' . $event];
            break;
    }
} catch (Exception $e) {
    error_log('Webhook handler error: ' . $e->getMessage());
    $result = ['success' => false, 'message' => 'Error processing webhook: ' . $e->getMessage()];
}

http_response_code($result['success'] ? 200 : 500);
echo json_encode($result);


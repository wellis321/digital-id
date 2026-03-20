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

// Verify webhook signature — look up per-org secret from DB, fall back to env var
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$allowUnsignedWebhooks = getenv('ALLOW_UNSIGNED_WEBHOOKS') === '1';

$webhookSecret = '';
$localOrgIdFromWebhook = null;
$pmsOrgId = isset($data['organisation_id']) ? (int)$data['organisation_id'] : 0;
if ($pmsOrgId) {
    // Find the Digital ID org that is connected to this specific PMS org,
    // using the staff_service_organisation_id mapping stored at setup time.
    $secretDb = getDbConnection();
    $secretStmt = $secretDb->prepare("
        SELECT os_secret.organisation_id, os_secret.setting_value AS webhook_secret
        FROM organisation_settings os_secret
        JOIN organisation_settings os_org
          ON os_org.organisation_id = os_secret.organisation_id
         AND os_org.setting_key = 'staff_service_organisation_id'
         AND os_org.setting_value = ?
        WHERE os_secret.setting_key = 'staff_service_webhook_secret'
        LIMIT 1
    ");
    $secretStmt->execute([(string)$pmsOrgId]);
    $secretRow = $secretStmt->fetch();
    if ($secretRow) {
        $webhookSecret = $secretRow['webhook_secret'];
        $localOrgIdFromWebhook = (int)$secretRow['organisation_id'];
    }
}
if (empty($webhookSecret)) {
    $webhookSecret = getenv('STAFF_SERVICE_WEBHOOK_SECRET') ?: '';
}

if (empty($webhookSecret)) {
    if (!$allowUnsignedWebhooks) {
        error_log('Webhook rejected: STAFF_SERVICE_WEBHOOK_SECRET not configured. Set ALLOW_UNSIGNED_WEBHOOKS=1 for development.');
        http_response_code(500);
        echo json_encode(['error' => 'Webhook signature verification not configured']);
        exit;
    }
    // Unsigned webhooks explicitly allowed (development mode)
} else {
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
            $stmt = $db->prepare("SELECT id, organisation_id FROM employees WHERE staff_service_person_id = ?");
            $stmt->execute([$personId]);
            $employee = $stmt->fetch();

            if ($employee) {
                // Deactivate employee record
                Employee::update($employee['id'], ['is_active' => false]);

                // Deactivate the linked user account so existing sessions are rejected
                if (!empty($eventData['email'])) {
                    $db->prepare("UPDATE users SET is_active = 0 WHERE email = ? AND organisation_id = ?")
                       ->execute([$eventData['email'], $employee['organisation_id']]);
                }

                // Revoke all active ID cards
                require_once SRC_PATH . '/classes/DigitalID.php';
                $stmt = $db->prepare("SELECT id FROM digital_id_cards WHERE employee_id = ? AND is_revoked = FALSE");
                $stmt->execute([$employee['id']]);
                $idCards = $stmt->fetchAll();

                foreach ($idCards as $idCard) {
                    DigitalID::revoke($idCard['id'], null);
                }

                $result = ['success' => true, 'message' => 'Employee deactivated and ID cards revoked'];
            } else {
                $result = ['success' => false, 'message' => 'Employee not found'];
            }
            break;

        case 'organisation.deactivated':
            $db = getDbConnection();

            // Use the org mapping established at setup time (most reliable).
            // Fall back to domain matching if mapping not available.
            $localOrg = null;
            if ($localOrgIdFromWebhook) {
                $localOrg = ['id' => $localOrgIdFromWebhook];
            } elseif (!empty($eventData['organisation_domain'])) {
                $orgStmt = $db->prepare("SELECT id FROM organisations WHERE domain = ? LIMIT 1");
                $orgStmt->execute([$eventData['organisation_domain']]);
                $localOrg = $orgStmt->fetch();
            }

            if (!$localOrg) {
                throw new Exception('Cannot identify local organisation for deactivation event');
            }

            $localOrgId = (int)$localOrg['id'];

            // Deactivate all users in this org
            $db->prepare("UPDATE users SET is_active = 0 WHERE organisation_id = ?")
               ->execute([$localOrgId]);

            // Deactivate all employees in this org
            $db->prepare("UPDATE employees SET is_active = 0 WHERE organisation_id = ?")
               ->execute([$localOrgId]);

            // Revoke all active ID cards for this org
            require_once SRC_PATH . '/classes/DigitalID.php';
            $stmt = $db->prepare("
                SELECT dc.id FROM digital_id_cards dc
                JOIN employees e ON e.id = dc.employee_id
                WHERE e.organisation_id = ? AND dc.is_revoked = FALSE
            ");
            $stmt->execute([$localOrgId]);
            $idCards = $stmt->fetchAll();

            foreach ($idCards as $idCard) {
                DigitalID::revoke($idCard['id'], null);
            }

            $result = ['success' => true, 'message' => 'Organisation access revoked'];
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


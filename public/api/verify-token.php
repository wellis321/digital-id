<?php
/**
 * Token Verification API Endpoint
 * For turnstile systems, building access control, and automated verification
 * 
 * GET /api/verify-token.php?token=XXX&type=qr
 * POST /api/verify-token.php (JSON body: {"token": "XXX", "type": "qr"})
 * 
 * Optional authentication:
 * - API Key: X-API-Key header or api_key parameter
 * - For public access (turnstiles), no authentication required
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json');

// CORS headers for cross-origin requests (if needed for turnstile systems)
// Configure VERIFICATION_CORS_ORIGIN env var to restrict origins (default: no CORS headers)
$corsOrigin = getenv('VERIFICATION_CORS_ORIGIN');
if ($corsOrigin) {
    // Use configured origin(s) - can be specific domain or '*' for public access
    header('Access-Control-Allow-Origin: ' . $corsOrigin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
}

// Handle preflight OPTIONS request (only if CORS is enabled)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && $corsOrigin) {
    http_response_code(200);
    exit;
}

require_once SRC_PATH . '/classes/VerificationService.php';
require_once SRC_PATH . '/classes/DigitalID.php';
require_once SRC_PATH . '/models/Employee.php';

// Optional API key authentication (for enhanced security)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? $_POST['api_key'] ?? null;
$requireApiKey = getenv('REQUIRE_API_KEY_FOR_VERIFICATION') === '1';

if ($requireApiKey) {
    // API key is required - reject if not provided
    if (empty($apiKey)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'API key required',
            'error' => 'unauthorized'
        ]);
        exit;
    }

    // Validate API key against database
    $db = getDbConnection();
    $stmt = $db->prepare("
        SELECT organisation_id
        FROM organisation_settings
        WHERE setting_key = 'access_control_api_key'
        AND setting_value = ?
        LIMIT 1
    ");
    $stmt->execute([$apiKey]);
    $apiKeyRecord = $stmt->fetch();

    if (!$apiKeyRecord) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API key',
            'error' => 'unauthorized'
        ]);
        exit;
    }
}

// Get token and type from request
$method = $_SERVER['REQUEST_METHOD'];
$token = null;
$type = 'qr';
$location = null; // Optional: location identifier for access control
$deviceId = null; // Optional: device identifier (turnstile ID, etc.)

if ($method === 'POST') {
    // Check if JSON request
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? null;
        $type = $input['type'] ?? 'qr';
        $location = $input['location'] ?? null;
        $deviceId = $input['device_id'] ?? null;
    } else {
        // Form-encoded POST
        $token = $_POST['token'] ?? null;
        $type = $_POST['type'] ?? 'qr';
        $location = $_POST['location'] ?? null;
        $deviceId = $_POST['device_id'] ?? null;
    }
} else {
    // GET request
    $token = $_GET['token'] ?? null;
    $type = $_GET['type'] ?? 'qr';
    $location = $_GET['location'] ?? null;
    $deviceId = $_GET['device_id'] ?? null;
}

// Validate token parameter
if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token parameter is required',
        'error' => 'missing_token'
    ]);
    exit;
}

// Validate type
$allowedTypes = ['qr', 'nfc', 'ble'];
if (!in_array($type, $allowedTypes)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid verification type. Must be: ' . implode(', ', $allowedTypes),
        'error' => 'invalid_type'
    ]);
    exit;
}

try {
    // Verify the token
    $verificationResult = VerificationService::verifyByToken($token, $type);
    
    if ($verificationResult['success']) {
        $employee = $verificationResult['employee'];
        $idCard = $verificationResult['id_card'];
        
        // Log access attempt with location/device info if provided
        if ($location || $deviceId) {
            $db = getDbConnection();
            $notes = [];
            if ($location) $notes[] = "Location: {$location}";
            if ($deviceId) $notes[] = "Device: {$deviceId}";
            
            // Update the most recent verification log with location/device info
            $stmt = $db->prepare("
                UPDATE verification_logs 
                SET notes = CONCAT(COALESCE(notes, ''), IF(notes IS NULL OR notes = '', '', ' | '), ?)
                WHERE employee_id = ? 
                AND verification_type = ?
                AND verification_result = 'success'
                ORDER BY verified_at DESC 
                LIMIT 1
            ");
            $stmt->execute([implode(' | ', $notes), $employee['id'], $type]);
        }
        
        // Return success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'valid' => true,
            'employee' => [
                'id' => $employee['id'],
                'employee_reference' => $employee['employee_reference'],
                'display_reference' => $employee['display_reference'] ?? $employee['employee_reference'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'organisation_id' => $employee['organisation_id'],
                'organisation_name' => $employee['organisation_name'],
                'is_active' => (bool)$employee['is_active']
            ],
            'id_card' => [
                'id' => $idCard['id'],
                'expires_at' => $idCard['expires_at']
            ],
            'verification_type' => $type,
            'verified_at' => date('c'),
            'message' => 'Verification successful'
        ], JSON_PRETTY_PRINT);
    } else {
        // Verification failed
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => $verificationResult['message'] ?? 'Verification failed',
            'error' => $verificationResult['reason'] ?? 'verification_failed',
            'verified_at' => date('c')
        ], JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    error_log("Token verification API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during verification',
        'error' => 'server_error'
    ], JSON_PRETTY_PRINT);
}


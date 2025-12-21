<?php
/**
 * Import ID Card Data from JSON
 * Allows employees to import their ID card data when moving organisations
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!CSRF::validatePost()) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Check if JSON file was uploaded
if (!isset($_FILES['id_data']) || $_FILES['id_data']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

// Read and parse JSON file
$jsonContent = file_get_contents($_FILES['id_data']['tmp_name']);
$idCardData = json_decode($jsonContent, true);

if (!$idCardData || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON file']);
    exit;
}

// Validate required fields
$requiredFields = ['employee_reference', 'full_name', 'organization'];
foreach ($requiredFields as $field) {
    if (!isset($idCardData[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Get current employee
$employee = Employee::findByUserId(Auth::getUserId());

if (!$employee) {
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

// Update employee with imported data
// Note: Employee reference and organization cannot be changed, but other data can be updated
$updateData = [];
if (isset($idCardData['verification_levels'])) {
    // Store the imported data structure
    $result = Employee::updateIdCardData($employee['id'], $idCardData);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'ID card data imported successfully',
            'data' => $idCardData
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to import ID card data']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID card data structure']);
}


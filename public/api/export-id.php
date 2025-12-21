<?php
/**
 * Export ID Card Data as JSON
 * Allows employees to export their ID card data for portability
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();

$employee = Employee::findByUserId(Auth::getUserId());

if (!$employee) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$idCardData = Employee::getIdCardData($employee['id']);

if (!$idCardData) {
    // Generate default structure if not exists
    $idCardData = [
        'employee_number' => $employee['employee_number'] ?? $employee['employee_reference'] ?? null,
        'display_reference' => $employee['display_reference'] ?? $employee['employee_reference'] ?? null,
        'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'organization' => [
            'id' => $employee['organisation_id'],
            'name' => $employee['organisation_name']
        ],
        'issued_at' => $employee['created_at'],
        'valid_until' => date('c', strtotime('+' . ID_CARD_EXPIRY_DAYS . ' days')),
        'verification_levels' => ['visual', 'qr', 'nfc']
    ];
}

// Add photo hash if photo exists
if ($employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path'])) {
    $idCardData['photo_hash'] = hash_file('sha256', dirname(__DIR__, 2) . '/' . $employee['photo_path']);
}

$referenceForFilename = $employee['display_reference'] ?? $employee['employee_reference'] ?? 'unknown';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="digital-id-' . $referenceForFilename . '.json"');

echo json_encode($idCardData, JSON_PRETTY_PRINT);


<?php
/**
 * Staff Service Image Proxy
 * Fetches photos and signatures from Staff Service server-to-server,
 * then streams them to the browser. This allows the browser to display
 * images that are protected by Staff Service API key authentication.
 *
 * Endpoint: GET /api/proxy-image.php
 *
 * Query Parameters:
 * - employee_id (required): Digital ID employee ID
 * - type (required): 'photo' or 'signature'
 *
 * Authentication: Digital ID session required
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$type       = $_GET['type'] ?? '';

if (!$employeeId || !in_array($type, ['photo', 'signature'], true)) {
    http_response_code(400);
    exit;
}

$organisationId = Auth::getOrganisationId();
$db = getDbConnection();

// Fetch the employee — must belong to the logged-in user's organisation
$stmt = $db->prepare("
    SELECT e.id, e.staff_service_person_id, e.staff_service_photo_url, e.signature_url
    FROM employees e
    WHERE e.id = ? AND e.organisation_id = ?
    LIMIT 1
");
$stmt->execute([$employeeId, $organisationId]);
$employee = $stmt->fetch();

if (!$employee || empty($employee['staff_service_person_id'])) {
    http_response_code(404);
    exit;
}

// Pick the stored URL for the requested type
$remoteUrl = $type === 'photo'
    ? ($employee['staff_service_photo_url'] ?? '')
    : ($employee['signature_url'] ?? '');

if (empty($remoteUrl)) {
    http_response_code(404);
    exit;
}

// Load the org's Staff Service API key
$stmt = $db->prepare("
    SELECT setting_key, setting_value
    FROM organisation_settings
    WHERE organisation_id = ?
      AND setting_key IN ('staff_service_api_key', 'use_staff_service')
");
$stmt->execute([$organisationId]);
$settings = [];
foreach ($stmt->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if (($settings['use_staff_service'] ?? '0') !== '1' || empty($settings['staff_service_api_key'])) {
    http_response_code(403);
    exit;
}

$apiKey = $settings['staff_service_api_key'];

// Fetch the image from Staff Service with the API key
$ch = curl_init($remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
]);

$imageData = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$mimeType  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if ($httpCode !== 200 || empty($imageData)) {
    http_response_code(404);
    exit;
}

// Only pass through image MIME types
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$mimeType = strtok($mimeType, ';'); // strip charset if present
if (!in_array($mimeType, $allowedMimes, true)) {
    http_response_code(403);
    exit;
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . strlen($imageData));
header('Cache-Control: private, max-age=3600');
echo $imageData;
exit;

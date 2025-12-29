<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$sessionId = (int)($_GET['id'] ?? 0);

if (!$sessionId) {
    header('Location: ' . url('admin/check-in-sessions.php'));
    exit;
}

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

$session = CheckInSession::findById($sessionId);

if (!$session || $session['organisation_id'] != $organisationId) {
    header('Location: ' . url('admin/check-in-sessions.php'));
    exit;
}

$checkIns = CheckInService::getSessionCheckIns($sessionId);

// Set headers for CSV download
$filename = 'check-in-session-' . $sessionId . '-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, [
    'Employee Name',
    'Employee Reference',
    'Check-In Time',
    'Check-Out Time',
    'Method',
    'Location',
    'Status'
]);

// Write data rows
foreach ($checkIns as $checkIn) {
    $employeeName = ($checkIn['first_name'] ?? '') . ' ' . ($checkIn['last_name'] ?? '');
    $employeeRef = $checkIn['display_reference'] ?? $checkIn['employee_reference'] ?? '';
    $checkInTime = date('Y-m-d H:i:s', strtotime($checkIn['checked_in_at']));
    $checkOutTime = $checkIn['checked_out_at'] ? date('Y-m-d H:i:s', strtotime($checkIn['checked_out_at'])) : '';
    $method = ucfirst(str_replace('_', ' ', $checkIn['check_in_method'] ?? 'manual'));
    $location = $checkIn['location_name'] ?? '';
    $status = $checkIn['checked_out_at'] ? 'Checked Out' : 'Checked In';
    
    fputcsv($output, [
        $employeeName,
        $employeeRef,
        $checkInTime,
        $checkOutTime,
        $method,
        $location,
        $status
    ]);
}

fclose($output);
exit;


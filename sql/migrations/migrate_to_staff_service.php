<?php
/**
 * Migration Script: Link Existing Employees to Staff Service
 * 
 * This script links existing Digital ID employees to Staff Service people records
 * by matching on user_id or employee_reference.
 * 
 * Usage: php migrate_to_staff_service.php
 */

require_once dirname(__DIR__, 3) . '/config/config.php';

// Check if Staff Service is enabled
if (!defined('USE_STAFF_SERVICE') || !USE_STAFF_SERVICE) {
    echo "ERROR: Staff Service integration is not enabled.\n";
    echo "Set USE_STAFF_SERVICE=true in your .env file.\n";
    exit(1);
}

require_once SRC_PATH . '/classes/StaffServiceClient.php';
require_once SRC_PATH . '/models/Employee.php';

// Check if Staff Service is available
if (!StaffServiceClient::isAvailable()) {
    echo "ERROR: Staff Service is not available.\n";
    echo "Check your STAFF_SERVICE_URL and STAFF_SERVICE_API_KEY configuration.\n";
    exit(1);
}

$db = getDbConnection();

echo "Starting migration: Linking employees to Staff Service...\n\n";

// Get all employees that are not yet linked
$stmt = $db->query("
    SELECT e.id, e.user_id, e.organisation_id, e.employee_reference, e.employee_number,
           u.email, u.first_name, u.last_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    WHERE e.staff_service_person_id IS NULL
    ORDER BY e.organisation_id, e.id
");
$employees = $stmt->fetchAll();

$linked = 0;
$notFound = 0;
$errors = 0;

foreach ($employees as $employee) {
    echo "Processing employee ID {$employee['id']} ({$employee['first_name']} {$employee['last_name']})...\n";
    
    $personId = null;
    
    // Try to find by user_id first
    if ($employee['user_id']) {
        $staffData = StaffServiceClient::getStaffByUserId($employee['user_id']);
        if ($staffData && isset($staffData['id'])) {
            $personId = $staffData['id'];
            echo "  Found in Staff Service by user_id: Person ID {$personId}\n";
        }
    }
    
    // If not found by user_id, try by employee_reference
    if (!$personId && !empty($employee['employee_reference'])) {
        $searchResults = StaffServiceClient::searchStaff($employee['employee_reference']);
        foreach ($searchResults as $staffMember) {
            if (isset($staffMember['employee_reference']) && 
                $staffMember['employee_reference'] === $employee['employee_reference'] &&
                isset($staffMember['organisation_id']) &&
                $staffMember['organisation_id'] == $employee['organisation_id']) {
                $personId = $staffMember['id'];
                echo "  Found in Staff Service by employee_reference: Person ID {$personId}\n";
                break;
            }
        }
    }
    
    // If still not found, try by employee_number
    if (!$personId && !empty($employee['employee_number'])) {
        $searchResults = StaffServiceClient::searchStaff($employee['employee_number']);
        foreach ($searchResults as $staffMember) {
            if (isset($staffMember['employee_reference']) && 
                $staffMember['employee_reference'] === $employee['employee_number'] &&
                isset($staffMember['organisation_id']) &&
                $staffMember['organisation_id'] == $employee['organisation_id']) {
                $personId = $staffMember['id'];
                echo "  Found in Staff Service by employee_number: Person ID {$personId}\n";
                break;
            }
        }
    }
    
    if ($personId) {
        // Link employee to Staff Service person
        $linkedResult = Employee::linkToStaffService($employee['id'], $personId);
        if ($linkedResult) {
            // Sync data from Staff Service
            $synced = Employee::syncFromStaffService($personId, $employee['id']);
            if ($synced) {
                echo "  ✓ Linked and synced successfully\n";
                $linked++;
            } else {
                echo "  ⚠ Linked but sync failed\n";
                $linked++;
                $errors++;
            }
        } else {
            echo "  ✗ Failed to link\n";
            $errors++;
        }
    } else {
        echo "  ✗ Not found in Staff Service\n";
        $notFound++;
    }
    
    echo "\n";
}

echo "=== MIGRATION SUMMARY ===\n";
echo "Total employees processed: " . count($employees) . "\n";
echo "Successfully linked: {$linked}\n";
echo "Not found in Staff Service: {$notFound}\n";
if ($errors > 0) {
    echo "Errors: {$errors}\n";
}

echo "\nMigration completed.\n";

if ($notFound > 0) {
    echo "\nNOTE: {$notFound} employee(s) were not found in Staff Service.\n";
    echo "These employees will continue to work in standalone mode.\n";
    echo "You can manually link them later through the admin interface.\n";
}


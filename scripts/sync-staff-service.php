#!/usr/bin/env php
<?php
/**
 * Staff Service Sync Script
 * Syncs all staff from Staff Service to Digital ID
 * 
 * Usage: php sync-staff-service.php [organisation_id]
 * If organisation_id is not provided, syncs all organisations
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Check if Staff Service is enabled
if (!defined('USE_STAFF_SERVICE') || !USE_STAFF_SERVICE) {
    echo "ERROR: Staff Service integration is not enabled.\n";
    echo "Set USE_STAFF_SERVICE=true in your .env file.\n";
    exit(1);
}

require_once SRC_PATH . '/classes/StaffServiceClient.php';
require_once SRC_PATH . '/classes/StaffSyncService.php';

// Check if Staff Service is available
if (!StaffServiceClient::isAvailable()) {
    echo "ERROR: Staff Service is not available.\n";
    echo "Check your STAFF_SERVICE_URL and STAFF_SERVICE_API_KEY configuration.\n";
    exit(1);
}

// Get organisation ID from command line argument
$organisationId = isset($argv[1]) ? (int)$argv[1] : null;

$db = getDbConnection();

if ($organisationId) {
    // Sync specific organisation
    echo "Syncing staff for organisation ID: {$organisationId}\n";
    $result = StaffSyncService::syncAllStaff($organisationId);
    
    if ($result['success']) {
        echo "SUCCESS: Synced {$result['synced']} staff member(s) from {$result['total']} total.\n";
        if (!empty($result['errors'])) {
            echo "WARNINGS:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
    } else {
        echo "ERROR: {$result['message']}\n";
        exit(1);
    }
} else {
    // Sync all organisations
    echo "Syncing staff for all organisations...\n";
    
    $stmt = $db->query("SELECT id, name FROM organisations ORDER BY id");
    $organisations = $stmt->fetchAll();
    
    $totalSynced = 0;
    $totalErrors = 0;
    
    foreach ($organisations as $org) {
        echo "\n--- Organisation: {$org['name']} (ID: {$org['id']}) ---\n";
        
        $result = StaffSyncService::syncAllStaff($org['id']);
        
        if ($result['success']) {
            echo "Synced {$result['synced']} staff member(s) from {$result['total']} total.\n";
            $totalSynced += $result['synced'];
            
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    echo "  WARNING: {$error}\n";
                    $totalErrors++;
                }
            }
        } else {
            echo "ERROR: {$result['message']}\n";
            $totalErrors++;
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Total synced: {$totalSynced}\n";
    if ($totalErrors > 0) {
        echo "Total errors: {$totalErrors}\n";
    }
}

echo "\nSync completed.\n";


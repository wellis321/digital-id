<?php
/**
 * Migration Checker
 * 
 * This script checks which migrations have been run on the database
 * and identifies what still needs to be run.
 * 
 * Usage: php sql/check_migrations.php
 */

require_once dirname(__DIR__) . '/config/config.php';

$db = getDbConnection();

echo "=== Digital ID Migration Status Check ===\n\n";

$migrations = [];

// Check for check_in_sessions table (Check-In Sessions feature)
$migrations['check_in_sessions'] = [
    'name' => 'Check-In Sessions and Microsoft 365 Integration',
    'file' => 'sql/add_check_in_sessions.sql',
    'php_script' => 'sql/migrate_check_in_sessions.php',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW TABLES LIKE 'check_in_sessions'");
    $migrations['check_in_sessions']['checks']['check_in_sessions_table'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['check_in_sessions']['checks']['check_in_sessions_table'] = false;
}

try {
    $stmt = $db->query("SHOW TABLES LIKE 'microsoft_365_sync_log'");
    $migrations['check_in_sessions']['checks']['m365_sync_log_table'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['check_in_sessions']['checks']['m365_sync_log_table'] = false;
}

try {
    $stmt = $db->query("SHOW COLUMNS FROM check_ins LIKE 'session_id'");
    $migrations['check_in_sessions']['checks']['check_ins_session_id'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['check_in_sessions']['checks']['check_ins_session_id'] = false;
}

try {
    $stmt = $db->query("SHOW COLUMNS FROM organisations LIKE 'm365_sync_enabled'");
    $migrations['check_in_sessions']['checks']['org_m365_columns'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['check_in_sessions']['checks']['org_m365_columns'] = false;
}

// Check for photo approval columns
$migrations['photo_approval'] = [
    'name' => 'Photo Approval System',
    'file' => 'sql/add_photo_approval_simple.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW COLUMNS FROM employees LIKE 'photo_approval_status'");
    $migrations['photo_approval']['checks']['photo_approval_status'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['photo_approval']['checks']['photo_approval_status'] = false;
}

// Check for organisational units
$migrations['organisational_units'] = [
    'name' => 'Organisational Units',
    'file' => 'sql/add_organisational_units_simple.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW TABLES LIKE 'organisational_units'");
    $migrations['organisational_units']['checks']['organisational_units_table'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['organisational_units']['checks']['organisational_units_table'] = false;
}

// Check for reference format columns
$migrations['reference_format'] = [
    'name' => 'Reference Format Settings',
    'file' => 'sql/add_reference_format_columns.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW COLUMNS FROM organisations LIKE 'reference_prefix'");
    $migrations['reference_format']['checks']['reference_prefix'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['reference_format']['checks']['reference_prefix'] = false;
}

// Check for display reference column
$migrations['display_reference'] = [
    'name' => 'Display Reference',
    'file' => 'sql/add_display_reference.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW COLUMNS FROM employees LIKE 'display_reference'");
    $migrations['display_reference']['checks']['display_reference'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['display_reference']['checks']['display_reference'] = false;
}

// Check for organisation logo
$migrations['organisation_logo'] = [
    'name' => 'Organisation Logo',
    'file' => 'sql/add_organisation_logo.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW COLUMNS FROM organisations LIKE 'logo_path'");
    $migrations['organisation_logo']['checks']['logo_path'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['organisation_logo']['checks']['logo_path'] = false;
}

// Check for Entra integration columns
$migrations['entra_integration'] = [
    'name' => 'Microsoft Entra Integration',
    'file' => 'sql/add_entra_columns_simple.sql',
    'checks' => []
];

try {
    $stmt = $db->query("SHOW COLUMNS FROM organisations LIKE 'entra_tenant_id'");
    $migrations['entra_integration']['checks']['entra_columns'] = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $migrations['entra_integration']['checks']['entra_columns'] = false;
}

// Display results
$needsMigration = false;

foreach ($migrations as $key => $migration) {
    $allPassed = true;
    $anyPassed = false;
    
    foreach ($migration['checks'] as $checkName => $passed) {
        if ($passed) {
            $anyPassed = true;
        } else {
            $allPassed = false;
        }
    }
    
    if (!$allPassed) {
        $needsMigration = true;
        echo "❌ {$migration['name']}\n";
        echo "   Status: INCOMPLETE\n";
        
        foreach ($migration['checks'] as $checkName => $passed) {
            $status = $passed ? '✓' : '✗';
            echo "   {$status} {$checkName}\n";
        }
        
        if (isset($migration['file'])) {
            echo "   SQL File: {$migration['file']}\n";
        }
        if (isset($migration['php_script'])) {
            echo "   PHP Script: {$migration['php_script']}\n";
        }
        echo "\n";
    } else {
        echo "✓ {$migration['name']}\n";
        echo "   Status: COMPLETE\n\n";
    }
}

if (!$needsMigration) {
    echo "\n✅ All migrations appear to be complete!\n";
} else {
    echo "\n⚠️  Some migrations need to be run.\n";
    echo "\nTo run migrations:\n";
    echo "1. Use the PHP migration scripts (recommended): php sql/migrate_[name].php\n";
    echo "2. Or run the SQL files directly in phpMyAdmin\n";
    echo "3. The PHP scripts are safer as they check for existing columns/tables\n";
}


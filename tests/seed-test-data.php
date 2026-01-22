<?php
/**
 * Seed Test Data Script
 * Creates test users, organisations, and employees for testing
 * Usage: php tests/seed-test-data.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "Seeding test data...\n\n";

try {
    $db = getDbConnection();

    // Disable foreign key checks
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');

    // Clear existing test data
    echo "Clearing existing test data...\n";
    $tables = ['verification_logs', 'digital_id_cards', 'employees', 'user_roles', 'users', 'organisations'];
    foreach ($tables as $table) {
        try {
            $db->exec("DELETE FROM `$table`");
        } catch (PDOException $e) {
            // Ignore errors for tables that don't exist
        }
    }

    // Re-enable foreign key checks
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    // =====================================================
    // ROLES (insert if not exists)
    // =====================================================
    echo "Setting up roles...\n";
    $roles = [
        ['staff', 'Regular staff member'],
        ['admin', 'Organisation administrator'],
        ['superadmin', 'System administrator']
    ];

    foreach ($roles as $role) {
        try {
            $stmt = $db->prepare("INSERT INTO roles (name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->execute($role);
        } catch (PDOException $e) {
            // Role might already exist
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "  Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Get role IDs
    $roleIds = [];
    $stmt = $db->query("SELECT id, name FROM roles");
    while ($row = $stmt->fetch()) {
        $roleIds[$row['name']] = $row['id'];
    }

    // =====================================================
    // TEST ORGANISATIONS
    // =====================================================
    echo "Creating test organisations...\n";
    $organisations = [
        [1, 'Test Organisation A', 'orga.test', 100],
        [2, 'Test Organisation B', 'orgb.test', 50],
        [3, 'Acme Corporation', 'acme.test', 200]
    ];

    foreach ($organisations as $org) {
        $stmt = $db->prepare("INSERT INTO organisations (id, name, domain, seats_allocated, seats_used, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->execute($org);
        echo "  Created: {$org[1]} ({$org[2]})\n";
    }

    // =====================================================
    // TEST USERS (password: password123)
    // =====================================================
    echo "Creating test users...\n";

    // Pre-computed hash for 'password123'
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

    $users = [
        // [id, org_id, email, first_name, last_name, role]
        [1, 1, 'staff1@orga.test', 'John', 'Doe', 'staff'],
        [2, 1, 'staff2@orga.test', 'Jane', 'Smith', 'staff'],
        [3, 1, 'admin@orga.test', 'Admin', 'User', 'admin'],
        [4, 2, 'staff1@orgb.test', 'Bob', 'Wilson', 'staff'],
        [5, 2, 'admin@orgb.test', 'Alice', 'Brown', 'admin'],
        [6, null, 'superadmin@system.test', 'Super', 'Admin', 'superadmin']
    ];

    foreach ($users as $user) {
        $stmt = $db->prepare("
            INSERT INTO users (id, organisation_id, email, password_hash, first_name, last_name, email_verified, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, 1, NOW())
        ");
        $stmt->execute([$user[0], $user[1], $user[2], $passwordHash, $user[3], $user[4]]);

        // Assign role
        if (isset($roleIds[$user[5]])) {
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user[0], $roleIds[$user[5]]]);
        }

        echo "  Created: {$user[2]} ({$user[5]})\n";
    }

    // =====================================================
    // TEST EMPLOYEES
    // =====================================================
    echo "Creating test employees...\n";
    $employees = [
        // [id, org_id, user_id, reference]
        [1, 1, 1, 'EMP001'],
        [2, 1, 2, 'EMP002'],
        [3, 1, 3, 'ADM001'],
        [4, 2, 4, 'EMPB001'],
        [5, 2, 5, 'ADMB001']
    ];

    foreach ($employees as $emp) {
        $stmt = $db->prepare("
            INSERT INTO employees (id, organisation_id, user_id, employee_reference, employee_number, display_reference, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$emp[0], $emp[1], $emp[2], $emp[3], $emp[3], $emp[3]]);
        echo "  Created: Employee {$emp[3]}\n";
    }

    // =====================================================
    // TEST ID CARDS
    // =====================================================
    echo "Creating test ID cards...\n";

    $qrExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $cardExpiry = date('Y-m-d H:i:s', strtotime('+365 days'));

    $idCards = [
        // [id, employee_id, qr_token, nfc_token]
        [1, 1, 'f9f73786a952015faa1a10caf299a68df7618d0c17d7cc846639b4e44fe6b8ec', 'nfc_token_emp001'],
        [2, 2, 'a1b2c3d4e5f6789012345678901234567890123456789012345678901234abcd', 'nfc_token_emp002'],
        [3, 3, 'admin_qr_token_12345678901234567890123456789012345678901234', 'nfc_token_adm001'],
        [4, 4, 'orgb_emp_qr_token_123456789012345678901234567890123456789012', 'nfc_token_orgb'],
        [5, 5, 'orgb_admin_qr_token_1234567890123456789012345678901234567890', 'nfc_token_orgb_adm']
    ];

    foreach ($idCards as $card) {
        $stmt = $db->prepare("
            INSERT INTO digital_id_cards (id, employee_id, qr_token, nfc_token, qr_token_expires_at, nfc_token_expires_at, expires_at, is_revoked, issued_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$card[0], $card[1], $card[2], $card[3], $qrExpiry, $qrExpiry, $cardExpiry]);
    }
    echo "  Created 5 ID cards\n";

    // Update organisation seat counts
    $db->exec("UPDATE organisations SET seats_used = 3 WHERE id = 1");
    $db->exec("UPDATE organisations SET seats_used = 2 WHERE id = 2");

    echo "\n";
    echo "========================================\n";
    echo "Test Data Seeded Successfully!\n";
    echo "========================================\n";
    echo "\n";
    echo "Test Accounts (all passwords: password123):\n";
    echo "\n";
    echo "Organisation A (orga.test):\n";
    echo "  staff1@orga.test  - Staff,  Employee EMP001\n";
    echo "  staff2@orga.test  - Staff,  Employee EMP002\n";
    echo "  admin@orga.test   - Admin,  Employee ADM001\n";
    echo "\n";
    echo "Organisation B (orgb.test):\n";
    echo "  staff1@orgb.test  - Staff,  Employee EMPB001\n";
    echo "  admin@orgb.test   - Admin,  Employee ADMB001\n";
    echo "\n";
    echo "System:\n";
    echo "  superadmin@system.test - Superadmin\n";
    echo "\n";
    echo "Valid QR Token for EMP001:\n";
    echo "  f9f73786a952015faa1a10caf299a68df7618d0c17d7cc846639b4e44fe6b8ec\n";
    echo "\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

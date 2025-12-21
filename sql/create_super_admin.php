<?php
/**
 * Create Super Admin User
 * 
 * This script creates the main super admin user for the Digital ID system.
 * Run this once after setting up the database.
 * 
 * Usage: php sql/create_super_admin.php
 */

require_once dirname(__DIR__) . '/config/config.php';

// Get database connection
$db = getDbConnection();

echo "Creating super admin user...\n\n";

// Check if super admin already exists
$stmt = $db->prepare("
    SELECT u.id, u.email 
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.name = 'superadmin' AND u.email = ?
");
$stmt->execute([CONTACT_EMAIL]);
$existingAdmin = $stmt->fetch();

if ($existingAdmin) {
    echo "Super admin with email '" . CONTACT_EMAIL . "' already exists (ID: {$existingAdmin['id']}).\n";
    echo "Skipping creation.\n";
    exit(0);
}

// Check if roles exist
$stmt = $db->query("SELECT id, name FROM roles WHERE name = 'superadmin'");
$superadminRole = $stmt->fetch();

if (!$superadminRole) {
    echo "ERROR: 'superadmin' role does not exist. Please run the database migrations first.\n";
    exit(1);
}

// Check if an organisation exists, create one if not
$stmt = $db->query("SELECT id FROM organisations LIMIT 1");
$organisation = $stmt->fetch();

if (!$organisation) {
    echo "No organisation found. Creating default organisation...\n";
    $stmt = $db->prepare("
        INSERT INTO organisations (name, domain, seats_allocated) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        'Digital ID System',
        'outlook.com',
        1000
    ]);
    $organisationId = $db->lastInsertId();
    echo "Created organisation with ID: $organisationId\n\n";
} else {
    $organisationId = $organisation['id'];
    echo "Using existing organisation ID: $organisationId\n\n";
}

// Prompt for password
echo "Enter password for super admin user (" . CONTACT_EMAIL . "): ";
$password = readline();
if (empty($password)) {
    echo "ERROR: Password cannot be empty.\n";
    exit(1);
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db->beginTransaction();
    
    // Create super admin user
    $stmt = $db->prepare("
        INSERT INTO users (
            organisation_id, 
            email, 
            password_hash, 
            first_name, 
            last_name, 
            email_verified, 
            is_active
        ) VALUES (?, ?, ?, ?, ?, TRUE, TRUE)
    ");
    $stmt->execute([
        $organisationId,
        CONTACT_EMAIL,
        $passwordHash,
        'Digital ID',
        'Administrator'
    ]);
    
    $userId = $db->lastInsertId();
    echo "Created user with ID: $userId\n";
    
    // Assign superadmin role
    $stmt = $db->prepare("
        INSERT INTO user_roles (user_id, role_id) 
        VALUES (?, ?)
    ");
    $stmt->execute([$userId, $superadminRole['id']]);
    
    $db->commit();
    
    echo "\n✓ Super admin user created successfully!\n";
    echo "  Email: " . CONTACT_EMAIL . "\n";
    echo "  User ID: $userId\n";
    echo "  Role: superadmin\n";
    echo "\nYou can now log in with this account.\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\nERROR: Failed to create super admin user.\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


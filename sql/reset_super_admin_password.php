<?php
/**
 * Reset Super Admin Password
 * 
 * This script resets the password for the super admin user.
 * 
 * Usage: php sql/reset_super_admin_password.php
 */

require_once dirname(__DIR__) . '/config/config.php';

// Get database connection
$db = getDbConnection();

echo "Reset Super Admin Password\n";
echo "=========================\n\n";

// Find super admin user
$stmt = $db->prepare("
    SELECT u.id, u.email, u.first_name, u.last_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.name = 'superadmin' AND u.email = ?
");
$stmt->execute([CONTACT_EMAIL]);
$user = $stmt->fetch();

if (!$user) {
    echo "ERROR: Super admin user with email '" . CONTACT_EMAIL . "' not found.\n";
    exit(1);
}

echo "Found super admin user:\n";
echo "  ID: {$user['id']}\n";
echo "  Email: {$user['email']}\n";
echo "  Name: {$user['first_name']} {$user['last_name']}\n\n";

// Get password from command line argument or prompt
if (isset($argv[1])) {
    $password = $argv[1];
    echo "Using password from command line argument.\n\n";
} else {
    // Prompt for new password
    echo "Enter new password: ";
    $password = readline();
}

if (empty($password)) {
    echo "ERROR: Password cannot be empty.\n";
    echo "Usage: php sql/reset_super_admin_password.php [password]\n";
    exit(1);
}

// Validate password length
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    echo "ERROR: Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.\n";
    exit(1);
}

// Confirm password (only if not from command line)
if (!isset($argv[1])) {
    echo "Confirm password: ";
    $confirmPassword = readline();
    if ($password !== $confirmPassword) {
        echo "ERROR: Passwords do not match.\n";
        exit(1);
    }
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Update password
    $stmt = $db->prepare("
        UPDATE users 
        SET password_hash = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$passwordHash, $user['id']]);
    
    echo "\n✓ Password reset successfully!\n";
    echo "  Email: " . CONTACT_EMAIL . "\n";
    echo "  User ID: {$user['id']}\n";
    echo "\nYou can now log in with the new password.\n";
    
} catch (Exception $e) {
    echo "\nERROR: Failed to reset password.\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


<?php
/**
 * Admin Script: Verify User Manually
 * Use this to manually verify a user's email and activate their account
 */

require_once dirname(__DIR__) . '/config/config.php';

// Simple authentication check (you can enhance this)
$adminPassword = 'admin123'; // Change this or use proper authentication

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['admin_password'] ?? '';
    
    if ($password !== $adminPassword) {
        die('Invalid admin password');
    }
    
    $userId = $_POST['user_id'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($userId) && empty($email)) {
        die('Please provide either User ID or Email');
    }
    
    $db = getDbConnection();
    
    // Find user
    if ($userId) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }
    
    $user = $stmt->fetch();
    
    if (!$user) {
        die('User not found');
    }
    
    // Update user to verified and active
    $stmt = $db->prepare("
        UPDATE users 
        SET email_verified = TRUE, 
            is_active = TRUE,
            verification_token = NULL,
            verification_token_expires_at = NULL
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    
    echo "✓ User verified successfully!\n";
    echo "User ID: " . $user['id'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify User</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #2563eb; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <h1>Manually Verify User</h1>
    <form method="POST">
        <div class="form-group">
            <label>Admin Password:</label>
            <input type="password" name="admin_password" required>
        </div>
        
        <div class="form-group">
            <label>User ID (or leave blank):</label>
            <input type="number" name="user_id" placeholder="e.g., 1">
        </div>
        
        <div class="form-group">
            <label>OR Email (or leave blank):</label>
            <input type="email" name="email" placeholder="e.g., user@example.com">
        </div>
        
        <button type="submit">Verify User</button>
    </form>
    
    <hr style="margin: 30px 0;">
    
    <h2>Or use SQL directly:</h2>
    <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px;">
-- Verify user by ID:
UPDATE users 
SET email_verified = TRUE, 
    is_active = TRUE,
    verification_token = NULL,
    verification_token_expires_at = NULL
WHERE id = 1;

-- Verify user by email:
UPDATE users 
SET email_verified = TRUE, 
    is_active = TRUE,
    verification_token = NULL,
    verification_token_expires_at = NULL
WHERE email = 'williamjamesellis@example.com';
    </pre>
</body>
</html>


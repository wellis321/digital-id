<?php
/**
 * Reset Super Admin Password (Web Interface)
 * 
 * This page allows you to reset the password for the super admin user.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Only allow if logged in as super admin, or for initial setup
// For security, you might want to add additional checks here
$error = '';
$success = '';

// Find super admin user
$db = getDbConnection();
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
    $error = 'Super admin user with email "' . CONTACT_EMAIL . '" not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && !$error) {
    if (!CSRF::validatePost()) {
        // Regenerate token to invalidate the old form
        unset($_SESSION[CSRF_TOKEN_NAME]);
        $error = 'Invalid security token. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password)) {
            $error = 'Password is required.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $db->prepare("
                    UPDATE users 
                    SET password_hash = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$passwordHash, $user['id']]);
                
                $success = 'Password reset successfully! You can now log in with the new password.';
                
            } catch (Exception $e) {
                $error = 'Failed to reset password: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Reset Super Admin Password';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Reset Super Admin Password</h1>
    
    <?php if ($error && !$user): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        
        <?php if ($user): ?>
        <form method="POST" action="" autocomplete="off">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="reset_password" value="1">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required 
                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       autocomplete="new-password">
                <small>
                    Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters.
                    <?php if (PASSWORD_REQUIRE_UPPERCASE): ?>Must include uppercase.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_LOWERCASE): ?>Must include lowercase.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_NUMBER): ?>Must include number.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_SPECIAL): ?>Must include special character.<?php endif; ?>
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <?php endif; ?>
        
    <?php elseif ($success): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success); ?></p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo url('login.php'); ?>" class="btn btn-primary">Go to Login</a>
            </p>
        </div>
    <?php else: ?>
        <p>Reset the password for the super admin user:</p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        
        <form method="POST" action="" autocomplete="off">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="reset_password" value="1">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required 
                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       autocomplete="new-password">
                <small>
                    Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters.
                    <?php if (PASSWORD_REQUIRE_UPPERCASE): ?>Must include uppercase.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_LOWERCASE): ?>Must include lowercase.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_NUMBER): ?>Must include number.<?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_SPECIAL): ?>Must include special character.<?php endif; ?>
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


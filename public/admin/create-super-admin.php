<?php
/**
 * Create Super Admin User (Web Interface)
 * 
 * This page allows you to create the main super admin user via web interface.
 * Only accessible when no super admin exists, or for security, you may want to
 * restrict this further.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Only allow if no super admin exists (or remove this check if you want to allow recreation)
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.name = 'superadmin'
");
$stmt->execute();
$result = $stmt->fetch();
$hasSuperAdmin = $result['count'] > 0;

$error = '';
$success = '';
$created = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    if (!CSRF::validatePost()) {
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
            // Check if super admin already exists
            $stmt = $db->prepare("
                SELECT u.id 
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE r.name = 'superadmin' AND u.email = ?
            ");
            $stmt->execute([CONTACT_EMAIL]);
            $existingAdmin = $stmt->fetch();
            
            if ($existingAdmin) {
                $error = 'Super admin with email "' . CONTACT_EMAIL . '" already exists.';
            } else {
                // Check if roles exist
                $stmt = $db->query("SELECT id, name FROM roles WHERE name = 'superadmin'");
                $superadminRole = $stmt->fetch();
                
                if (!$superadminRole) {
                    $error = 'Superadmin role does not exist. Please run database migrations first.';
                } else {
                    // Check if an organisation exists, create one if not
                    $stmt = $db->query("SELECT id FROM organisations LIMIT 1");
                    $organisation = $stmt->fetch();
                    
                    if (!$organisation) {
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
                    } else {
                        $organisationId = $organisation['id'];
                    }
                    
                    try {
                        $db->beginTransaction();
                        
                        // Hash password
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        
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
                        
                        // Assign superadmin role
                        $stmt = $db->prepare("
                            INSERT INTO user_roles (user_id, role_id) 
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$userId, $superadminRole['id']]);
                        
                        $db->commit();
                        
                        $success = 'Super admin user created successfully!';
                        $created = true;
                        
                    } catch (Exception $e) {
                        $db->rollBack();
                        $error = 'Failed to create super admin user: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$pageTitle = 'Create Super Admin';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Create Super Admin User</h1>
    
    <?php if ($hasSuperAdmin && !$created): ?>
        <div class="alert alert-info">
            <p><strong>Super admin already exists.</strong></p>
            <p>A super admin user has already been created in the system.</p>
            <p>If you need to create a new one, please use the command line script:</p>
            <code>php sql/create_super_admin.php</code>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars(CONTACT_EMAIL); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($userId ?? 'N/A'); ?></p>
            <p><strong>Role:</strong> superadmin</p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo url('login.php'); ?>" class="btn btn-primary">Go to Login</a>
            </p>
        </div>
    <?php else: ?>
        <p>This will create the main super admin user for the Digital ID system.</p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars(CONTACT_EMAIL); ?></p>
        
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="create_admin" value="1">
            
            <div class="form-group">
                <label for="password">Password</label>
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
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary">Create Super Admin</button>
        </form>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


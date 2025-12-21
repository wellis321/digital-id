<?php
require_once dirname(__DIR__) . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        // Regenerate token to invalidate the old form
        unset($_SESSION[CSRF_TOKEN_NAME]);
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = Auth::login($email, $password);
        
        if ($result === true) {
            header('Location: ' . url('id-card.php'));
            exit;
        } elseif (is_array($result) && isset($result['error'])) {
            $error = $result['message'];
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Login</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" autocomplete="off">
        <?php echo CSRF::tokenField(); 
        ?>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <?php
    // Check if Entra login is available
    $organisationId = null;
    if (isset($_GET['org_id'])) {
        $organisationId = $_GET['org_id'];
    } else {
        // Try to detect organisation from email domain if provided
        $email = $_POST['email'] ?? '';
        if ($email) {
            $domain = substr(strrchr($email, '@'), 1);
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT id FROM organisations WHERE domain = ?");
            $stmt->execute([$domain]);
            $org = $stmt->fetch();
            if ($org) {
                $organisationId = $org['id'];
            }
        }
    }
    
    if ($organisationId && EntraIntegration::isEnabled($organisationId)):
    ?>
        <div style="margin: 1.5rem 0; text-align: center;">
            <div style="margin: 1rem 0; display: flex; align-items: center;">
                <div style="flex: 1; height: 1px; background: #ddd;"></div>
                <span style="padding: 0 1rem; color: #666;">or</span>
                <div style="flex: 1; height: 1px; background: #ddd;"></div>
            </div>
            <a href="<?php echo url('entra-login.php?org_id=' . $organisationId); ?>" class="btn btn-secondary">
                Login with Microsoft 365
            </a>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
        <p style="margin-bottom: 1rem; text-align: center; color: #374151; font-weight: 500;">Don't have an account?</p>
        <a href="<?php echo url('register.php'); ?>" class="btn btn-primary" style="width: 100%; display: block; text-align: center;">
            <i class="fas fa-user-plus"></i> Register Now
        </a>
    </div>
    <p style="margin-top: 1rem; text-align: center; color: #6b7280; font-size: 0.875rem;">
        <strong>New organisation?</strong> <a href="<?php echo url('request-access.php'); ?>">Request access</a> for your organisation first.
    </p>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


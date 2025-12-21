<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

$config = EntraIntegration::getConfig($organisationId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'enable') {
            $tenantId = $_POST['tenant_id'] ?? '';
            $clientId = $_POST['client_id'] ?? '';
            
            if (empty($tenantId) || empty($clientId)) {
                $error = 'Tenant ID and Client ID are required.';
            } else {
                $result = EntraIntegration::enable($organisationId, $tenantId, $clientId);
                if ($result['success']) {
                    $success = 'Entra integration enabled successfully.';
                    $config = EntraIntegration::getConfig($organisationId);
                } else {
                    $error = 'Failed to enable Entra integration.';
                }
            }
        } elseif ($action === 'disable') {
            $result = EntraIntegration::disable($organisationId);
            if ($result['success']) {
                $success = 'Entra integration disabled.';
                $config = EntraIntegration::getConfig($organisationId);
            } else {
                $error = 'Failed to disable Entra integration.';
            }
        }
    }
}

$pageTitle = 'Entra/365 Settings';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Microsoft Entra/365 Integration Settings</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <p>Configure optional Microsoft Entra ID (Azure AD) integration for single sign-on and employee synchronisation.</p>
    
    <?php if ($config && $config['entra_enabled']): ?>
        <div class="alert alert-success">
            <strong>Entra integration is enabled</strong>
            <p>Tenant ID: <?php echo htmlspecialchars($config['entra_tenant_id']); ?></p>
            <p>Client ID: <?php echo htmlspecialchars($config['entra_client_id']); ?></p>
        </div>
        
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="disable">
            <button type="submit" class="btn btn-danger">Disable Entra Integration</button>
        </form>
    <?php else: ?>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="enable">
            
            <div class="form-group">
                <label for="tenant_id">Tenant ID</label>
                <input type="text" id="tenant_id" name="tenant_id" required>
                <small>Your Azure AD Tenant ID</small>
            </div>
            
            <div class="form-group">
                <label for="client_id">Client ID (Application ID)</label>
                <input type="text" id="client_id" name="client_id" required>
                <small>Your Azure AD Application (Client) ID</small>
            </div>
            
            <div class="alert alert-info">
                <strong>Note:</strong> You also need to set the <code>ENTRA_CLIENT_SECRET</code> environment variable 
                with your Azure AD Application secret.
            </div>
            
            <button type="submit" class="btn btn-primary">Enable Entra Integration</button>
        </form>
    <?php endif; ?>
    
    <div style="margin-top: 2rem; padding: 1rem; background-color: #f0f0f0; border-radius: 4px;">
        <h3>Setup Instructions</h3>
        <ol style="margin-left: 1.5rem;">
            <li>Register your application in Azure AD</li>
            <li>Configure redirect URI: <code><?php echo APP_URL . url('entra-login.php'); ?></code></li>
            <li>Grant API permissions: <code>User.Read</code>, <code>openid</code>, <code>profile</code>, <code>email</code></li>
            <li>Copy your Tenant ID and Client ID</li>
            <li>Set the Client Secret as an environment variable</li>
            <li>Enter the details above and enable integration</li>
        </ol>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


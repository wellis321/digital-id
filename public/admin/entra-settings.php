<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

$config = EntraIntegration::getConfig($organisationId);

$syncResult = null;

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
        } elseif ($action === 'sync_users') {
            $createEmployees = isset($_POST['create_employees']) && $_POST['create_employees'] === '1';
            $syncResult = EntraIntegration::syncUsersFromEntra($organisationId, $createEmployees);
            
            if ($syncResult['success']) {
                $successMessage = "Sync complete! {$syncResult['users_created']} users created";
                if ($syncResult['users_updated'] > 0) {
                    $successMessage .= ", {$syncResult['users_updated']} updated";
                }
                if ($createEmployees && $syncResult['employees_created'] > 0) {
                    $successMessage .= ", {$syncResult['employees_created']} employee profiles created";
                }
                if ($syncResult['users_skipped'] > 0) {
                    $successMessage .= ", {$syncResult['users_skipped']} skipped";
                }
                $success = $successMessage;
            } else {
                $error = 'Sync failed: ' . ($syncResult['message'] ?? 'Unknown error');
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
        
        <!-- User Synchronisation Section -->
        <div class="card" style="margin-top: 2rem; padding: 1.5rem; background: #f9fafb; border: 1px solid #e5e7eb;">
            <h2 style="margin-top: 0;">User Synchronisation</h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                Synchronise users from Microsoft Entra ID (Azure AD) to Digital ID. This will fetch all active users from your Microsoft 365 organisation and import them using the same process as CSV/JSON import.
            </p>
            
            <?php if ($syncResult && !empty($syncResult['warnings'])): ?>
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; margin-bottom: 1rem; border-radius: 0;">
                    <h4 style="margin-top: 0; color: #92400e; font-size: 1rem;">Sync Warnings</h4>
                    <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #92400e; font-size: 0.875rem;">
                        <?php foreach ($syncResult['warnings'] as $warning): ?>
                            <li><?php echo htmlspecialchars($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="sync_users">
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="create_employees" value="1" style="margin: 0;">
                        <span>Also create employee profiles for users with employee IDs</span>
                    </label>
                    <small style="display: block; margin-top: 0.25rem; color: #6b7280;">
                        If checked, employee profiles will be created for users who have an employee ID in Microsoft Entra ID.
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Sync Users from Microsoft Entra ID
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; padding: 1rem; background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 0;">
                <h4 style="margin-top: 0; color: #1e40af; font-size: 0.875rem;">
                    <i class="fas fa-info-circle"></i> How It Works
                </h4>
                <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #1e40af; font-size: 0.875rem;">
                    <li>Fetches all active users from Microsoft Entra ID</li>
                    <li>Matches users by email address</li>
                    <li>Creates new users or updates existing ones</li>
                    <li>Optionally creates employee profiles if employee IDs are available</li>
                    <li>Uses the same import logic as CSV/JSON import</li>
                </ul>
            </div>
            
            <div style="margin-top: 1rem; padding: 1rem; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0;">
                <h4 style="margin-top: 0; color: #92400e; font-size: 0.875rem;">
                    <i class="fas fa-exclamation-triangle"></i> Required Permissions
                </h4>
                <p style="margin: 0.5rem 0 0; color: #92400e; font-size: 0.875rem;">
                    For user synchronisation to work, your Azure AD app registration needs <strong>User.Read.All</strong> application permission (not delegated). 
                    Admin consent is required for this permission.
                </p>
            </div>
        </div>
        
        <form method="POST" action="" style="margin-top: 2rem;">
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
    
    <div style="margin-top: 2rem; padding: 1rem; background-color: #f0f0f0; border-radius: 0;">
        <h3>Setup Instructions</h3>
        <ol style="margin-left: 1.5rem;">
            <li>Register your application in Azure AD</li>
            <li>Configure redirect URI: <code><?php echo APP_URL . url('entra-login.php'); ?></code></li>
            <li>Grant API permissions:
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li><strong>For SSO login:</strong> <code>User.Read</code>, <code>openid</code>, <code>profile</code>, <code>email</code> (delegated permissions)</li>
                    <li><strong>For user synchronisation:</strong> <code>User.Read.All</code> (application permission, requires admin consent)</li>
                </ul>
            </li>
            <li>Copy your Tenant ID and Client ID</li>
            <li>Set the Client Secret as an environment variable</li>
            <li>Enter the details above and enable integration</li>
        </ol>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


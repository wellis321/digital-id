<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Microsoft Entra Integration - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Microsoft Entra Integration</h1>
            <p>Integrate Digital ID with Microsoft Entra ID (Azure AD) for single sign-on and employee synchronisation.</p>
            
            <h2>Overview</h2>
            <p>Microsoft Entra integration provides:</p>
            <ul>
                <li><strong>Single Sign-On (SSO):</strong> Users can log in with their Microsoft 365 accounts</li>
                <li><strong>Employee Synchronisation:</strong> Automatically sync employees from Microsoft 365</li>
                <li><strong>Seamless Integration:</strong> Works with existing Office 365 infrastructure</li>
            </ul>
            
            <h2>Prerequisites</h2>
            <ul>
                <li>Microsoft 365 subscription with Azure AD</li>
                <li>Admin access to Azure AD</li>
                <li>Ability to register applications in Azure AD</li>
            </ul>
            
            <h2>Setting Up Entra Integration</h2>
            <h3>Step 1: Register Application in Azure AD</h3>
            <ol class="step-list">
                <li>Log in to the Azure Portal</li>
                <li>Go to "Azure Active Directory" → "App registrations"</li>
                <li>Click "New registration"</li>
                <li>Enter application name: "Digital ID"</li>
                <li>Set redirect URI: <code><?php echo APP_URL; ?>/entra-login.php</code></li>
                <li>Click "Register"</li>
            </ol>
            
            <h3>Step 2: Configure API Permissions</h3>
            <ol>
                <li>In your app registration, go to "API permissions"</li>
                <li>Click "Add a permission"</li>
                <li>Select "Microsoft Graph"</li>
                <li>Add the following permissions:
                    <ul>
                        <li><strong>For SSO Login (Delegated Permissions):</strong>
                            <ul>
                                <li><code>User.Read</code> - Read user profile</li>
                                <li><code>openid</code> - Sign in and read user profile</li>
                                <li><code>profile</code> - View user's basic profile</li>
                                <li><code>email</code> - View user's email address</li>
                            </ul>
                        </li>
                        <li><strong>For User Synchronisation (Application Permissions):</strong>
                            <ul>
                                <li><code>User.Read.All</code> - Read all users' profiles (requires admin consent)</li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>Click "Add permissions"</li>
                <li><strong>Important:</strong> For <code>User.Read.All</code>, click "Grant admin consent" to enable user synchronisation</li>
            </ol>
            
            <h3>Step 3: Create Client Secret</h3>
            <ol>
                <li>Go to "Certificates & secrets"</li>
                <li>Click "New client secret"</li>
                <li>Enter description and expiration</li>
                <li>Click "Add"</li>
                <li><strong>Copy the secret value immediately</strong> - you won't be able to see it again</li>
            </ol>
            
            <h3>Step 4: Configure in Digital ID</h3>
            <ol>
                <li>Log in as organisation administrator</li>
                <li>Go to "Entra Settings" in the admin menu</li>
                <li>Enter your Tenant ID (found in Azure AD overview)</li>
                <li>Enter your Client ID (Application ID from app registration)</li>
                <li>Enter your Client Secret (from Step 3)</li>
                <li>Enable the integration</li>
                <li>Save settings</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Security</h4>
                <p>Keep your Client Secret secure. Never share it or commit it to version control. Store it in your environment variables.</p>
            </div>
            
            <h2>Using Entra Login</h2>
            <p>Once configured, users can log in with Microsoft:</p>
            <ol>
                <li>Go to the login page</li>
                <li>Click "Sign in with Microsoft"</li>
                <li>Authenticate with Microsoft 365 credentials</li>
                <li>You'll be redirected back to Digital ID</li>
            </ol>
            
            <h2>User Synchronisation</h2>
            <p>When Microsoft Entra integration is enabled, organisation administrators can synchronise users from Microsoft 365:</p>
            <ul>
                <li><strong>Bulk Import:</strong> Fetch all active users from Microsoft Entra ID</li>
                <li><strong>Automatic Matching:</strong> Users are matched by email address</li>
                <li><strong>Create or Update:</strong> New users are created, existing users are updated</li>
                <li><strong>Employee Profiles:</strong> Optionally create employee profiles for users with employee IDs</li>
                <li><strong>Same Process:</strong> Uses the same import logic as CSV/JSON import for consistency</li>
            </ul>
            
            <h3>How to Sync Users</h3>
            <ol>
                <li>Go to Organisation → Microsoft 365 SSO Settings</li>
                <li>Ensure Entra integration is enabled</li>
                <li>Click "Sync Users from Microsoft Entra ID"</li>
                <li>Optionally check "Also create employee profiles" if users have employee IDs</li>
                <li>Review the sync results and any warnings</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Required Permissions</h4>
                <p>For user synchronisation to work, your Azure AD app registration needs <strong>User.Read.All</strong> application permission (not delegated). Admin consent is required for this permission.</p>
            </div>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <p>User synchronisation automates the import process by pulling data directly from Microsoft Entra ID, eliminating the need to export CSV files manually. It uses the same reliable import system as manual CSV/JSON imports.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            <h3>Common Issues</h3>
            <ul>
                <li><strong>Redirect URI mismatch:</strong> Ensure the redirect URI in Azure AD matches exactly</li>
                <li><strong>Permissions not granted:</strong> Admin consent may be required for API permissions</li>
                <li><strong>Invalid client secret:</strong> Check that the secret hasn't expired</li>
                <li><strong>Tenant ID incorrect:</strong> Verify the Tenant ID in Azure AD overview</li>
            </ul>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

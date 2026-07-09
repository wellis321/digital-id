<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Admin Guide - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Admin Guide</h1>
            <p>This guide covers administrative functions for organisation administrators.</p>
            
            <h2>Organisation Administration</h2>
            <p>As an organisation administrator, you have access to:</p>
            <ul>
                <li>Employee management</li>
                <li>Organisational structure management</li>
                <li>User management</li>
                <li>Import/export functionality</li>
                <li>Microsoft Entra integration settings</li>
            </ul>
            
            <h2>Managing Employees</h2>
            <h3>Creating Employee Profiles</h3>
            <ol class="step-list">
                <li>Go to "Employees" in the admin menu</li>
                <li>Click "Add New Employee"</li>
                <li>Enter the employee details:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Employee reference (unique identifier)</li>
                        <li>Full name</li>
                        <li>Link to existing user account (by email)</li>
                        <li>Photo upload</li>
                        <li>Expiration date</li>
                    </ul>
                </li>
                <li>Save the employee profile</li>
            </ol>
            
            <h3>Editing Employee Profiles</h3>
            <p>To update an employee's information and ID card:</p>
            <ol class="step-list">
                <li>Go to "Employees" in the admin menu</li>
                <li>Find the employee in the list</li>
                <li>Click "Edit" next to the employee</li>
                <li>Update any of the following:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Employee reference (must be unique within your organisation)</li>
                        <li>Photo (upload a new photo to replace the existing one)</li>
                        <li>Active status (activate or deactivate the employee)</li>
                        <li>ID card expiration date (set when the ID card expires)</li>
                    </ul>
                </li>
                <li>Click "Save Changes"</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Photo Updates</h4>
                <p>
                    When an employee uploads a new photo, it requires administrator approval before it appears on their ID card. 
                    <strong>The current approved photo remains visible on the ID card until the new photo is approved.</strong> 
                    This ensures the ID card remains usable throughout the approval process. Supported formats: JPEG, PNG. Maximum file size: 5MB.
                </p>
                <p style="margin-top: 0.75rem;">
                    To review and approve/reject pending photos, go to "Photos" in the Organisation menu. You'll see all employees 
                    who have uploaded new photos waiting for approval.
                </p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> ID Card Expiration</h4>
                <p>You can set a custom expiration date for the ID card. If left empty, the system will use the default expiration period. Changing the expiration date does not create a new card - it updates the existing one.</p>
            </div>
            
            <h3>Revoking ID Cards</h3>
            <p>If an employee leaves or a card is compromised:</p>
            <ol>
                <li>Go to "Employees"</li>
                <li>Find the employee</li>
                <li>Click "Revoke ID Card"</li>
                <li>Confirm the revocation</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Revoked ID cards cannot be verified, even with valid tokens. Revocation takes effect immediately.</p>
            </div>
            
            <div class="success-box" style="margin-top: 1.5rem;">
                <h4><i class="fas fa-info-circle"></i> Automatic Revocation with Staff Service</h4>
                <p>If your organisation uses <a href="<?php echo url('docs-staff-service.php'); ?>">Staff Service integration</a>, ID cards are automatically revoked when staff members are deactivated in Staff Service. No manual action is required in Digital ID - the revocation happens automatically via webhook when a person is deactivated in Staff Service.</p>
            </div>
            
            <h2>Managing Users</h2>
            <p>You can view all users in your organisation:</p>
            <ol>
                <li>Go to "Users" in the admin menu</li>
                <li>View the list of all registered users</li>
                <li>See which users have employee profiles linked</li>
            </ol>
            
            <h2>Organisational Structure</h2>
            <p>Create and manage your organisation's hierarchical structure:</p>
            <ul>
                <li>Create organisational units (teams, departments, areas, regions)</li>
                <li>Assign members to units</li>
                <li>Set up unit administrators</li>
                <li>Import structure from CSV or JSON</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs-organisational-structure.php'); ?>">Organisational Structure</a> section for detailed information.</p>
            
            <h2>Import and Export</h2>
            <p>Bulk import organisational structure and member assignments:</p>
            <ul>
                <li>Import organisational units from CSV or JSON</li>
                <li>Import member assignments</li>
                <li>Export ID card data</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs-import-export.php'); ?>">Import & Export</a> section for detailed information.</p>
            
            <h2>Microsoft Entra Integration</h2>
            <p>Configure Microsoft Entra ID (Azure AD) integration for:</p>
            <ul>
                <li>Single sign-on (SSO)</li>
                <li>Automatic employee synchronisation</li>
                <li>Office 365 integration</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs-entra-integration.php'); ?>">Microsoft Entra Integration</a> section for setup instructions.</p>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

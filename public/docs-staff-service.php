<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Staff Service Integration - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Staff Service Integration</h1>
            <p>Digital ID can integrate with the Staff Service to use it as the <strong>sole source of truth</strong> for staff data. When enabled, Digital ID automatically syncs staff information, photos, and signatures from Staff Service, and handles ID card revocation automatically.</p>
            
            <h2>Overview</h2>
            <p>When Staff Service integration is enabled:</p>
            <ul>
                <li>Staff data (name, employee reference, photo, status) syncs automatically from Staff Service</li>
                <li>Staff signatures from Staff Service are displayed on digital ID cards</li>
                <li>Real-time updates are received via webhooks when staff data changes</li>
                <li>Digital ID employees are automatically linked to Staff Service people records</li>
                <li><strong>ID cards are automatically revoked when staff are deactivated in Staff Service</strong></li>
            </ul>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Automatic Revocation</h4>
                <p>When a staff member is deactivated in Staff Service, their Digital ID card is automatically revoked via webhook. No manual action is required in Digital ID - the revocation happens immediately when the deactivation occurs in Staff Service.</p>
            </div>
            
            <h2>Configuration</h2>
            
            <h3>Step 1: Create API Key in Staff Service</h3>
            <p>Before configuring Digital ID, you need to create an API key in Staff Service:</p>
            <ol class="step-list">
                <li>Log in to Staff Service as an organisation administrator</li>
                <li>Navigate to <strong>Admin</strong> → <strong>API Keys</strong> (in the Admin dropdown menu)</li>
                <li>Click <strong>"Create API Key"</strong></li>
                <li>Enter a descriptive name (e.g., "Digital ID Integration")</li>
                <li>Click <strong>"Create API Key"</strong></li>
                <li><strong>Copy the API key immediately</strong> - it will only be shown once!</li>
            </ol>
            
            <h3>Step 2: Configure Digital ID Settings</h3>
            <p>Configure Staff Service integration in Digital ID's web interface:</p>
            <ol class="step-list">
                <li>Log in to Digital ID as an organisation administrator</li>
                <li>Navigate to <strong>Admin</strong> → <strong>Organisation</strong> → <strong>Staff Service</strong> (in the dropdown menu)</li>
                <li>Enable Staff Service Integration:
                    <ul>
                        <li>Check the "Enable Staff Service Integration" checkbox</li>
                        <li>Enter the <strong>Staff Service URL</strong> (e.g., <code>http://localhost:8000</code> or <code>https://staff.yourdomain.com</code>)</li>
                        <li>Paste the <strong>API Key</strong> you copied from Staff Service</li>
                        <li>Set the <strong>Sync Interval</strong> (default: 3600 seconds = 1 hour)</li>
                    </ul>
                </li>
                <li>Click <strong>"Test Connection"</strong> to verify the URL and API key are correct (optional)</li>
                <li>Click <strong>"Save Settings"</strong></li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Settings Storage</h4>
                <p>Settings are stored in the database and take effect immediately. No need to edit <code>.env</code> files or restart the server.</p>
            </div>
            
            <h2>Data Synchronisation</h2>
            
            <h3>Automatic Sync</h3>
            <p>When Staff Service integration is enabled, data syncs automatically in several ways:</p>
            <ul>
                <li><strong>On-Demand Sync:</strong> When an employee record is accessed, if it's linked to Staff Service and data is stale, it syncs automatically</li>
                <li><strong>Periodic Sync:</strong> Run the sync script via cron to sync all staff periodically</li>
                <li><strong>Webhook Sync:</strong> Real-time sync when Staff Service sends webhook events</li>
            </ul>
            
            <h3>Manual Sync</h3>
            <p>You can manually sync all staff from Staff Service:</p>
            <ol class="step-list">
                <li>Go to <strong>Admin</strong> → <strong>Manage Employees</strong></li>
                <li>Click <strong>"Sync from Staff Service"</strong> button</li>
                <li>The system will sync all staff members and create employee records for new staff</li>
            </ol>
            
            <h3>Sync Script</h3>
            <p>For automated periodic syncing, you can run the sync script manually or via cron:</p>
            <pre><code># Sync all organisations
php scripts/sync-staff-service.php

# Sync specific organisation
php scripts/sync-staff-service.php 1</code></pre>
            
            <p>Add to crontab for automatic syncing:</p>
            <pre><code># Sync every hour
0 * * * * cd /path/to/digital-id && php scripts/sync-staff-service.php >> /var/log/staff-sync.log 2>&1</code></pre>
            
            <h2>Webhook Configuration</h2>
            <p>To receive real-time updates from Staff Service (including automatic ID card revocation), configure webhooks in Staff Service:</p>
            <ol class="step-list">
                <li>In Staff Service, go to <strong>Admin</strong> → <strong>Webhooks</strong></li>
                <li>Create a new webhook pointing to:
                    <pre><code>https://your-digital-id-domain.com/api/staff-service-webhook.php</code></pre>
                </li>
                <li>Select the events you want to receive:
                    <ul>
                        <li><code>person.created</code> - New staff member created</li>
                        <li><code>person.updated</code> - Staff member updated</li>
                        <li><code>person.deactivated</code> - Staff member deactivated (triggers automatic ID card revocation)</li>
                        <li><code>signature.uploaded</code> - Signature uploaded/updated</li>
                        <li><code>photo.updated</code> - Photo updated</li>
                    </ul>
                </li>
                <li>Set the webhook secret in Digital ID's Staff Service settings (optional, for security)</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Webhooks are required for automatic ID card revocation. Without webhooks configured, you'll need to manually revoke ID cards when staff are deactivated in Staff Service.</p>
            </div>
            
            <h2>Automatic ID Card Revocation</h2>
            <p>When Staff Service integration is enabled with webhooks configured:</p>
            <ul>
                <li>When a staff member is <strong>deactivated in Staff Service</strong>, a webhook event is sent to Digital ID</li>
                <li>Digital ID automatically:
                    <ul>
                        <li>Sets the employee's <code>is_active</code> flag to <code>false</code></li>
                        <li>Revokes all active ID cards for that employee</li>
                        <li>Marks the revocation as system-initiated (from Staff Service)</li>
                    </ul>
                </li>
                <li>The revocation takes effect immediately - revoked cards cannot be verified</li>
                <li>No manual action is required in Digital ID</li>
            </ul>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>No need to manually revoke ID cards when staff leave</li>
                    <li>Immediate revocation ensures security</li>
                    <li>Single source of truth - manage staff in Staff Service, Digital ID follows automatically</li>
                    <li>Complete audit trail - all revocations are logged</li>
                </ul>
            </div>
            
            <h2>Employee Management</h2>
            <p>In the employee management pages (<code>/admin/employees.php</code> and <code>/admin/employees-edit.php</code>):</p>
            <ul>
                <li><strong>Sync Status:</strong> Shows if employee is linked to Staff Service</li>
                <li><strong>Last Sync Time:</strong> Displays when data was last synced</li>
                <li><strong>Sync Button:</strong> Manual sync button to refresh data from Staff Service</li>
                <li><strong>Bulk Sync:</strong> "Sync from Staff Service" button to sync all employees</li>
            </ul>
            
            <h2>Signature Display</h2>
            <p>When an employee is linked to Staff Service and has a signature:</p>
            <ul>
                <li>Signature is automatically fetched from Staff Service API</li>
                <li>Signature URL is cached in <code>employees.signature_url</code></li>
                <li>Signature is displayed on the digital ID card below the photo</li>
                <li>Signature updates automatically when synced</li>
            </ul>
            
            <h2>Standalone Mode</h2>
            <p>If Staff Service integration is disabled or unavailable:</p>
            <ul>
                <li>Digital ID operates independently</li>
                <li>Employees are managed locally</li>
                <li>No external API calls are made</li>
                <li>All functionality works as before</li>
                <li>ID cards must be revoked manually through the admin interface</li>
            </ul>
            
            <h2>Troubleshooting</h2>
            
            <h3>Staff Service Not Available</h3>
            <p>If the connection test fails:</p>
            <ul>
                <li>Check <code>STAFF_SERVICE_URL</code> is correct</li>
                <li>Verify <code>STAFF_SERVICE_API_KEY</code> is valid</li>
                <li>Ensure Staff Service is accessible from Digital ID server</li>
                <li>Check firewall/network settings</li>
            </ul>
            
            <h3>Sync Failures</h3>
            <p>If syncing fails:</p>
            <ul>
                <li>Check error logs: <code>error_log()</code> messages</li>
                <li>Verify API key has correct permissions</li>
                <li>Ensure employee reference matches between systems</li>
                <li>Check database connection and table structure</li>
            </ul>
            
            <h3>Webhook Not Working</h3>
            <p>If webhooks aren't being received:</p>
            <ul>
                <li>Verify webhook URL is accessible from Staff Service</li>
                <li>Check webhook secret matches in both systems (if configured)</li>
                <li>Review webhook logs in Staff Service</li>
                <li>Check firewall allows incoming webhook requests</li>
                <li>Ensure the webhook endpoint is publicly accessible (if Staff Service is on a different server)</li>
            </ul>
            
            <h2>Data Mapping</h2>
            <p>The following data is synced from Staff Service to Digital ID:</p>
            <table class="comparison-table" style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th>Staff Service</th>
                        <th>Digital ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>people.id</code></td>
                        <td><code>employees.staff_service_person_id</code></td>
                    </tr>
                    <tr>
                        <td><code>people.first_name</code></td>
                        <td><code>users.first_name</code> (via user_id)</td>
                    </tr>
                    <tr>
                        <td><code>people.last_name</code></td>
                        <td><code>users.last_name</code> (via user_id)</td>
                    </tr>
                    <tr>
                        <td><code>people.employee_reference</code></td>
                        <td><code>employees.employee_reference</code></td>
                    </tr>
                    <tr>
                        <td><code>people.photo_path</code></td>
                        <td><code>employees.photo_path</code></td>
                    </tr>
                    <tr>
                        <td><code>staff_profiles.signature_path</code></td>
                        <td><code>employees.signature_url</code></td>
                    </tr>
                    <tr>
                        <td><code>people.is_active</code></td>
                        <td><code>employees.is_active</code></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="info-box" style="margin-top: 2rem;">
                <h4><i class="fas fa-book"></i> Additional Resources</h4>
                <p>For detailed technical information about the Staff Service integration, see the <a href="<?php echo dirname(__DIR__); ?>/INTEGRATION.md">INTEGRATION.md</a> file in the project root.</p>
            </div>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

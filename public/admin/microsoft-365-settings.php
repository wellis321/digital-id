<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

$db = getDbConnection();

// Get current settings
$stmt = $db->prepare("
    SELECT m365_sync_enabled, m365_sharepoint_site_url, m365_sharepoint_list_id,
           m365_teams_channel_id, m365_power_automate_webhook_url
    FROM organisations 
    WHERE id = ?
");
$stmt->execute([$organisationId]);
$settings = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $m365SyncEnabled = isset($_POST['m365_sync_enabled']) ? 1 : 0;
        $sharepointSiteUrl = trim($_POST['m365_sharepoint_site_url'] ?? '');
        $sharepointListId = trim($_POST['m365_sharepoint_list_id'] ?? '');
        $teamsChannelId = trim($_POST['m365_teams_channel_id'] ?? '');
        $powerAutomateWebhook = trim($_POST['m365_power_automate_webhook_url'] ?? '');
        
        // Check if Entra integration is enabled
        require_once SRC_PATH . '/classes/EntraIntegration.php';
        if (!EntraIntegration::isEnabled($organisationId)) {
            $error = 'Microsoft Entra integration must be enabled first. Please configure it in Microsoft 365 SSO settings.';
        } else {
            $stmt = $db->prepare("
                UPDATE organisations 
                SET m365_sync_enabled = ?,
                    m365_sharepoint_site_url = ?,
                    m365_sharepoint_list_id = ?,
                    m365_teams_channel_id = ?,
                    m365_power_automate_webhook_url = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $m365SyncEnabled,
                $sharepointSiteUrl ?: null,
                $sharepointListId ?: null,
                $teamsChannelId ?: null,
                $powerAutomateWebhook ?: null,
                $organisationId
            ]);
            
            $success = 'Microsoft 365 settings updated successfully.';
            
            // Refresh settings
            $stmt = $db->prepare("
                SELECT m365_sync_enabled, m365_sharepoint_site_url, m365_sharepoint_list_id,
                       m365_teams_channel_id, m365_power_automate_webhook_url
                FROM organisations 
                WHERE id = ?
            ");
            $stmt->execute([$organisationId]);
            $settings = $stmt->fetch();
        }
    }
}

// Get sync logs
$stmt = $db->prepare("
    SELECT * FROM microsoft_365_sync_log 
    WHERE organisation_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$organisationId]);
$syncLogs = $stmt->fetchAll();

$pageTitle = 'Microsoft 365 Integration Settings';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Microsoft 365 Integration Settings</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <p>Configure Microsoft 365 integration for automatic synchronisation of check-in data to SharePoint Lists, Power Automate workflows, and Teams notifications.</p>
    
    <div class="info-box" style="margin-bottom: 2rem;">
        <p><strong>Prerequisites:</strong></p>
        <ul>
            <li>Microsoft Entra integration must be enabled (configure in <a href="<?php echo url('admin/entra-settings.php'); ?>">Microsoft 365 SSO settings</a>)</li>
            <li>SharePoint site and list must be created</li>
            <li>Power Automate webhook URL (if using Power Automate)</li>
            <li>Teams channel ID (if using Teams notifications)</li>
        </ul>
    </div>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="m365_sync_enabled" value="1" 
                       <?php echo ($settings['m365_sync_enabled'] ?? false) ? 'checked' : ''; ?>>
                Enable Microsoft 365 Synchronisation
            </label>
            <small>When enabled, check-ins will automatically sync to configured Microsoft 365 services</small>
        </div>
        
        <h2>SharePoint Integration</h2>
        
        <div class="form-group">
            <label for="m365_sharepoint_site_url">SharePoint Site URL</label>
            <input type="text" id="m365_sharepoint_site_url" name="m365_sharepoint_site_url" 
                   value="<?php echo htmlspecialchars($settings['m365_sharepoint_site_url'] ?? ''); ?>" 
                   placeholder="e.g., https://yourtenant.sharepoint.com/sites/YourSite">
            <small>Full URL to your SharePoint site</small>
        </div>
        
        <div class="form-group">
            <label for="m365_sharepoint_list_id">SharePoint List ID</label>
            <input type="text" id="m365_sharepoint_list_id" name="m365_sharepoint_list_id" 
                   value="<?php echo htmlspecialchars($settings['m365_sharepoint_list_id'] ?? ''); ?>" 
                   placeholder="GUID of the SharePoint list">
            <small>List ID (GUID) where check-ins will be stored</small>
        </div>
        
        <h2>Microsoft Teams Integration</h2>
        
        <div class="form-group">
            <label for="m365_teams_channel_id">Teams Channel ID</label>
            <input type="text" id="m365_teams_channel_id" name="m365_teams_channel_id" 
                   value="<?php echo htmlspecialchars($settings['m365_teams_channel_id'] ?? ''); ?>" 
                   placeholder="Channel ID for notifications">
            <small>Teams channel ID where notifications will be sent</small>
        </div>
        
        <h2>Power Automate Integration</h2>
        
        <div class="form-group">
            <label for="m365_power_automate_webhook_url">Power Automate Webhook URL</label>
            <input type="url" id="m365_power_automate_webhook_url" name="m365_power_automate_webhook_url" 
                   value="<?php echo htmlspecialchars($settings['m365_power_automate_webhook_url'] ?? ''); ?>" 
                   placeholder="https://prod-xx.webhook.office.com/webhookb2/...">
            <small>Webhook URL from your Power Automate flow</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2>Sync Logs</h2>
    
    <?php if (empty($syncLogs)): ?>
        <p>No sync logs yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Entity Type</th>
                        <th>Status</th>
                        <th>Synced At</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($syncLogs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $log['sync_type']))); ?></td>
                            <td><?php echo htmlspecialchars($log['entity_type']); ?></td>
                            <td>
                                <?php
                                $statusClass = $log['sync_status'] === 'success' ? 'badge-success' : 
                                             ($log['sync_status'] === 'failed' ? 'badge-error' : 'badge-warning');
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($log['sync_status'])); ?>
                                </span>
                            </td>
                            <td><?php echo $log['synced_at'] ? date('d/m/Y H:i:s', strtotime($log['synced_at'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($log['sync_error'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


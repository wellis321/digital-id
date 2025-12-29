<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

$db = getDbConnection();

// Create settings table if it doesn't exist
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS organisation_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            organisation_id INT NOT NULL,
            setting_key VARCHAR(255) NOT NULL,
            setting_value TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_org_setting (organisation_id, setting_key),
            FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
            INDEX idx_organisation_id (organisation_id),
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table might already exist, ignore
}

// Get current settings
function getSetting($db, $organisationId, $key, $default = '') {
    $stmt = $db->prepare("SELECT setting_value FROM organisation_settings WHERE organisation_id = ? AND setting_key = ?");
    $stmt->execute([$organisationId, $key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function setSetting($db, $organisationId, $key, $value) {
    $stmt = $db->prepare("
        INSERT INTO organisation_settings (organisation_id, setting_key, setting_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
    ");
    return $stmt->execute([$organisationId, $key, $value, $value]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save') {
            $useStaffService = isset($_POST['use_staff_service']) && $_POST['use_staff_service'] === '1';
            $staffServiceUrl = trim($_POST['staff_service_url'] ?? '');
            $staffServiceApiKey = trim($_POST['staff_service_api_key'] ?? '');
            $syncInterval = (int)($_POST['sync_interval'] ?? 3600);
            
            if ($useStaffService) {
                if (empty($staffServiceUrl)) {
                    $error = 'Staff Service URL is required when integration is enabled.';
                } elseif (empty($staffServiceApiKey)) {
                    $error = 'Staff Service API Key is required when integration is enabled.';
                } else {
                    // Validate URL format
                    if (!filter_var($staffServiceUrl, FILTER_VALIDATE_URL)) {
                        $error = 'Invalid Staff Service URL format.';
                    } else {
                        // Save settings
                        setSetting($db, $organisationId, 'use_staff_service', $useStaffService ? '1' : '0');
                        setSetting($db, $organisationId, 'staff_service_url', $staffServiceUrl);
                        setSetting($db, $organisationId, 'staff_service_api_key', $staffServiceApiKey);
                        setSetting($db, $organisationId, 'staff_sync_interval', (string)$syncInterval);
                        
                        $success = 'Staff Service integration settings saved successfully.';
                    }
                }
            } else {
                // Disable integration
                setSetting($db, $organisationId, 'use_staff_service', '0');
                $success = 'Staff Service integration disabled.';
            }
        } elseif ($action === 'test') {
            $staffServiceUrl = trim($_POST['staff_service_url'] ?? '');
            $staffServiceApiKey = trim($_POST['staff_service_api_key'] ?? '');
            
            if (empty($staffServiceUrl) || empty($staffServiceApiKey)) {
                $error = 'Staff Service URL and API Key are required for testing.';
            } else {
                // Test connection directly with curl (avoid redefining constants that are already defined in config.php)
                // Use GET request instead of HEAD since the API endpoint may not support HEAD
                $testUrl = rtrim($staffServiceUrl, '/') . '/api/staff-data.php';
                $ch = curl_init($testUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $staffServiceApiKey,
                    'Accept: application/json'
                ]);
                // Use GET request instead of HEAD
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                
                if ($curlError) {
                    $error = 'Connection test failed: ' . htmlspecialchars($curlError) . '. Please check the Staff Service URL.';
                } elseif ($httpCode === 200) {
                    // Success - API is reachable and responding
                    $success = 'Connection test successful! Staff Service is reachable and the API key is valid.';
                } elseif (in_array($httpCode, [400, 401, 403])) {
                    // 400/401/403 = service is up but auth/request issue (which is fine for testing - means service is reachable)
                    $success = 'Connection test successful! Staff Service is reachable. HTTP ' . $httpCode . ' indicates the service is responding (authentication or request format may need adjustment).';
                } elseif ($httpCode === 0) {
                    $error = 'Connection test failed: Could not connect to Staff Service. Please check the URL and ensure Staff Service is running.';
                } elseif ($httpCode === 404) {
                    $error = 'Connection test failed: API endpoint not found (404). Please check the Staff Service URL is correct.';
                } elseif ($httpCode === 405) {
                    $error = 'Connection test failed: Method not allowed (405). The API endpoint may not be configured correctly.';
                } else {
                    $error = 'Connection test failed: HTTP ' . $httpCode . '. Please check the URL and API key.';
                }
            }
        }
    }
}

// Get current settings (check database first, then fall back to .env)
$useStaffService = getSetting($db, $organisationId, 'use_staff_service', defined('USE_STAFF_SERVICE') && USE_STAFF_SERVICE ? '1' : '0');
$staffServiceUrl = getSetting($db, $organisationId, 'staff_service_url', defined('STAFF_SERVICE_URL') ? STAFF_SERVICE_URL : '');
$staffServiceApiKey = getSetting($db, $organisationId, 'staff_service_api_key', defined('STAFF_SERVICE_API_KEY') ? STAFF_SERVICE_API_KEY : '');
$syncInterval = (int)getSetting($db, $organisationId, 'staff_sync_interval', defined('STAFF_SYNC_INTERVAL') ? (string)STAFF_SYNC_INTERVAL : '3600');

// Mask API key for display (show first 8 and last 4 characters)
$maskedApiKey = '';
if (!empty($staffServiceApiKey)) {
    $length = strlen($staffServiceApiKey);
    if ($length > 12) {
        $maskedApiKey = substr($staffServiceApiKey, 0, 8) . str_repeat('*', $length - 12) . substr($staffServiceApiKey, -4);
    } else {
        $maskedApiKey = str_repeat('*', $length);
    }
}

$pageTitle = 'Staff Service Settings';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Staff Service Integration Settings</h1>
    
    <p>Configure integration with Staff Service to sync staff data automatically. Staff Service acts as the central source of truth for staff information.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="save">
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" 
                       name="use_staff_service" 
                       value="1" 
                       <?php echo $useStaffService === '1' ? 'checked' : ''; ?>
                       onchange="toggleStaffServiceFields(this.checked)">
                <span style="font-weight: 600;">Enable Staff Service Integration</span>
            </label>
            <small>When enabled, Digital ID will sync staff data from Staff Service instead of managing it locally.</small>
        </div>
        
        <div id="staff-service-fields" style="<?php echo $useStaffService === '1' ? '' : 'display: none;'; ?>">
            <div class="form-group">
                <label for="staff_service_url">Staff Service URL</label>
                <input type="url" 
                       id="staff_service_url" 
                       name="staff_service_url" 
                       value="<?php echo htmlspecialchars($staffServiceUrl); ?>"
                       placeholder="http://localhost:8000 or https://staff.yourdomain.com"
                       required>
                <small>The base URL of your Staff Service installation (without trailing slash).</small>
            </div>
            
            <div class="form-group">
                <label for="staff_service_api_key">Staff Service API Key</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="password" 
                           id="staff_service_api_key" 
                           name="staff_service_api_key" 
                           value="<?php echo htmlspecialchars($staffServiceApiKey); ?>"
                           placeholder="Paste your API key from Staff Service"
                           style="flex: 1;"
                           required>
                    <button type="button" 
                            onclick="toggleApiKeyVisibility()" 
                            class="btn btn-secondary"
                            style="white-space: nowrap;">
                        <i class="fas fa-eye" id="api-key-eye-icon"></i> Show
                    </button>
                </div>
                <small>
                    Get your API key from Staff Service: <strong>Admin</strong> → <strong>API Keys</strong> → <strong>Create API Key</strong>
                    <?php if (!empty($maskedApiKey)): ?>
                        <br>Current key: <code><?php echo htmlspecialchars($maskedApiKey); ?></code>
                    <?php endif; ?>
                </small>
            </div>
            
            <div class="form-group">
                <label for="sync_interval">Sync Interval (seconds)</label>
                <input type="number" 
                       id="sync_interval" 
                       name="sync_interval" 
                       value="<?php echo $syncInterval; ?>"
                       min="60"
                       step="60"
                       required>
                <small>How often to check for updates from Staff Service (in seconds). Default: 3600 (1 hour). Minimum: 60 seconds.</small>
            </div>
            
            <div style="margin-top: 1rem; padding: 1rem; background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 0;">
                <h4 style="margin-top: 0; color: #1e40af; font-size: 0.875rem;">
                    <i class="fas fa-info-circle"></i> How to Get Your API Key
                </h4>
                <ol style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #1e40af; font-size: 0.875rem;">
                    <li>Log in to <strong>Staff Service</strong> as an organisation administrator</li>
                    <li>Go to <strong>Admin</strong> → <strong>API Keys</strong></li>
                    <li>Click <strong>"Create API Key"</strong></li>
                    <li>Enter a name (e.g., "Digital ID Integration")</li>
                    <li>Copy the API key immediately (it's only shown once!)</li>
                    <li>Paste it in the field above</li>
                </ol>
            </div>
            
            <div style="margin-top: 1rem;">
                <button type="button" 
                        onclick="testConnection()" 
                        class="btn btn-secondary">
                    <i class="fas fa-plug"></i> Test Connection
                </button>
            </div>
        </div>
        
        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
    
    <div style="margin-top: 2rem; padding: 1rem; background-color: #f0f0f0; border-radius: 0;">
        <h3>About Staff Service Integration</h3>
        <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem;">
            <li>When enabled, Digital ID will use Staff Service as the source of truth for staff data</li>
            <li>Staff information (name, photo, employee reference, signature) syncs automatically</li>
            <li>Changes in Staff Service automatically appear in Digital ID</li>
            <li>You can still manage Digital ID-specific data (ID card details, verification logs) locally</li>
            <li>Settings are stored in the database and take effect immediately (no server restart needed)</li>
        </ul>
    </div>
</div>

<script>
function toggleStaffServiceFields(enabled) {
    const fields = document.getElementById('staff-service-fields');
    if (enabled) {
        fields.style.display = 'block';
        // Make fields required
        document.getElementById('staff_service_url').required = true;
        document.getElementById('staff_service_api_key').required = true;
    } else {
        fields.style.display = 'none';
        // Remove required attribute
        document.getElementById('staff_service_url').required = false;
        document.getElementById('staff_service_api_key').required = false;
    }
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('staff_service_api_key');
    const icon = document.getElementById('api-key-eye-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.parentElement.innerHTML = '<i class="fas fa-eye-slash" id="api-key-eye-icon"></i> Hide';
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.parentElement.innerHTML = '<i class="fas fa-eye" id="api-key-eye-icon"></i> Show';
    }
}

function testConnection() {
    const url = document.getElementById('staff_service_url').value;
    const apiKey = document.getElementById('staff_service_api_key').value;
    
    if (!url || !apiKey) {
        alert('Please enter both Staff Service URL and API Key before testing.');
        return;
    }
    
    // Create a form to submit test request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'test';
    form.appendChild(actionInput);
    
    const urlInput = document.createElement('input');
    urlInput.type = 'hidden';
    urlInput.name = 'staff_service_url';
    urlInput.value = url;
    form.appendChild(urlInput);
    
    const keyInput = document.createElement('input');
    keyInput.type = 'hidden';
    keyInput.name = 'staff_service_api_key';
    keyInput.value = apiKey;
    form.appendChild(keyInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


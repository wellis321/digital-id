<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Building Access Integration - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Building Access Integration</h1>
            <p>Digital ID provides a JSON API endpoint for turnstile systems, building access control panels, and automated access verification. This allows organisations to use Digital ID cards for secure building entry and automatic access logging.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Use Cases</h4>
                <p>Building Access Integration is ideal for:</p>
                <ul style="margin-top: 0.5rem;">
                    <li><strong>Turnstiles:</strong> Staff scan QR codes at turnstile scanners for entry</li>
                    <li><strong>Building Access Panels:</strong> Door access systems verify employee identity</li>
                    <li><strong>Secure Areas:</strong> Restricted locations requiring employee verification</li>
                    <li><strong>Housing Providers:</strong> Staff access to buildings and properties</li>
                    <li><strong>Social Care Organisations:</strong> Secure entry to care facilities</li>
                </ul>
            </div>
            
            <h2>How It Works</h2>
            <p>When an employee scans their Digital ID QR code at an access point:</p>
            <ol class="step-list">
                <li>The turnstile or access panel scans the QR code</li>
                <li>The system extracts the verification token from the QR code</li>
                <li>The access system calls the Digital ID API endpoint</li>
                <li>Digital ID verifies the token and employee status</li>
                <li>The API returns success or failure response</li>
                <li>Access is granted or denied based on the response</li>
                <li>All access attempts are logged for security monitoring</li>
            </ol>
            
            <h2>API Endpoint</h2>
            <p>The Building Access API endpoint is available at:</p>
            <pre><code><?php echo APP_URL; ?>/api/verify-token.php</code></pre>
            
            <h3>Request Methods</h3>
            <p>The API supports both GET and POST requests:</p>
            
            <h4>GET Request</h4>
            <pre><code>GET /api/verify-token.php?token=XXX&type=qr&location=Main_Entrance&device_id=TURNSTILE_01</code></pre>
            
            <h4>POST Request (JSON)</h4>
            <pre><code>POST /api/verify-token.php
Content-Type: application/json

{
  "token": "abc123...",
  "type": "qr",
  "location": "Building_A_Floor_2",
  "device_id": "ACCESS_PANEL_03"
}</code></pre>
            
            <h3>Request Parameters</h3>
            <table class="comparison-table" style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>token</code></td>
                        <td>Yes</td>
                        <td>The verification token extracted from the QR code</td>
                    </tr>
                    <tr>
                        <td><code>type</code></td>
                        <td>No</td>
                        <td>Verification type: <code>qr</code>, <code>nfc</code>, or <code>ble</code> (default: <code>qr</code>)</td>
                    </tr>
                    <tr>
                        <td><code>location</code></td>
                        <td>No</td>
                        <td>Location identifier (e.g., "Main_Entrance", "Building_A_Floor_2") for logging</td>
                    </tr>
                    <tr>
                        <td><code>device_id</code></td>
                        <td>No</td>
                        <td>Device identifier (e.g., "TURNSTILE_01", "ACCESS_PANEL_03") for logging</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Response Format</h3>
            
            <h4>Success Response (200 OK)</h4>
            <pre><code>{
  "success": true,
  "valid": true,
  "employee": {
    "id": 123,
    "employee_reference": "EMP001",
    "display_reference": "EMP001",
    "first_name": "John",
    "last_name": "Smith",
    "full_name": "John Smith",
    "organisation_id": 1,
    "organisation_name": "Example Organisation",
    "is_active": true
  },
  "id_card": {
    "id": 456,
    "expires_at": "2025-12-31 23:59:59"
  },
  "verification_type": "qr",
  "verified_at": "2025-01-15T10:30:00+00:00",
  "message": "Verification successful"
}</code></pre>
            
            <h4>Failure Response (403 Forbidden)</h4>
            <pre><code>{
  "success": false,
  "valid": false,
  "message": "Verification token has expired. Please request a new one.",
  "error": "expired",
  "verified_at": "2025-01-15T10:30:00+00:00"
}</code></pre>
            
            <h4>Error Codes</h4>
            <table class="comparison-table" style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th>Error Code</th>
                        <th>HTTP Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>missing_token</code></td>
                        <td>400</td>
                        <td>Token parameter is missing</td>
                    </tr>
                    <tr>
                        <td><code>invalid_type</code></td>
                        <td>400</td>
                        <td>Invalid verification type specified</td>
                    </tr>
                    <tr>
                        <td><code>token_not_found</code></td>
                        <td>403</td>
                        <td>Token not found in database</td>
                    </tr>
                    <tr>
                        <td><code>expired</code></td>
                        <td>403</td>
                        <td>Verification token has expired (5 minutes)</td>
                    </tr>
                    <tr>
                        <td><code>revoked</code></td>
                        <td>403</td>
                        <td>ID card has been revoked</td>
                    </tr>
                    <tr>
                        <td><code>card_expired</code></td>
                        <td>403</td>
                        <td>ID card has expired</td>
                    </tr>
                    <tr>
                        <td><code>employee_inactive</code></td>
                        <td>403</td>
                        <td>Employee is not active</td>
                    </tr>
                    <tr>
                        <td><code>unauthorized</code></td>
                        <td>401</td>
                        <td>Invalid API key (if API key authentication is enabled)</td>
                    </tr>
                    <tr>
                        <td><code>server_error</code></td>
                        <td>500</td>
                        <td>Internal server error</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Integration Guide</h2>
            <p>To integrate Digital ID with your turnstile or building access system:</p>
            
            <h3>Step 1: Extract Token from QR Code</h3>
            <p>When an employee scans their QR code, the access system should:</p>
            <ol class="step-list">
                <li>Scan the QR code image</li>
                <li>Extract the URL (format: <code><?php echo APP_URL; ?>/verify.php?token=XXX</code>)</li>
                <li>Parse the <code>token</code> parameter from the URL</li>
            </ol>
            
            <h3>Step 2: Call the API</h3>
            <p>Make an HTTP request to the verification endpoint:</p>
            <pre><code>GET <?php echo APP_URL; ?>/api/verify-token.php?token=XXX&type=qr&location=Main_Entrance&device_id=TURNSTILE_01</code></pre>
            
            <h3>Step 3: Process Response</h3>
            <p>Based on the API response:</p>
            <ul>
                <li>If <code>success: true</code> and <code>valid: true</code>: Grant access</li>
                <li>If <code>success: false</code> or <code>valid: false</code>: Deny access</li>
                <li>Log the access attempt (success or failure) for audit purposes</li>
            </ul>
            
            <h3>Step 4: Handle Errors</h3>
            <p>Common error scenarios:</p>
            <ul>
                <li><strong>Expired Token:</strong> QR codes expire after 5 minutes. The employee should refresh their ID card page to get a new QR code.</li>
                <li><strong>Revoked Card:</strong> The employee's ID card has been revoked by an administrator. Access should be denied.</li>
                <li><strong>Inactive Employee:</strong> The employee is no longer active. Access should be denied.</li>
                <li><strong>Network Error:</strong> If the API is unreachable, consider implementing a fallback mechanism or offline mode.</li>
            </ul>
            
            <h2>Security Features</h2>
            <ul>
                <li><strong>Time-Limited Tokens:</strong> QR code tokens expire after 5 minutes to prevent replay attacks</li>
                <li><strong>Automatic Logging:</strong> All access attempts are logged with location and device information</li>
                <li><strong>Real-Time Verification:</strong> Employee status is checked in real-time (active/inactive, revoked cards)</li>
                <li><strong>Organisation Isolation:</strong> Each organisation's data is completely isolated</li>
                <li><strong>Optional API Key Authentication:</strong> Can be enabled for additional security</li>
            </ul>
            
            <h2>Access Logging</h2>
            <p>All access attempts are automatically logged in the Digital ID system with:</p>
            <ul>
                <li>Employee information (name, reference)</li>
                <li>Verification type (QR, NFC, BLE)</li>
                <li>Verification result (success/failed)</li>
                <li>Timestamp</li>
                <li>Location (if provided)</li>
                <li>Device ID (if provided)</li>
                <li>IP address</li>
            </ul>
            
            <p>Organisation administrators can view and export these logs from the <a href="<?php echo url('admin/verification-logs.php'); ?>">Verification Logs</a> page, with filtering by location and device ID.</p>
            
            <h2>Optional API Key Authentication</h2>
            <p>For enhanced security, you can enable API key authentication:</p>
            <ol class="step-list">
                <li>Set <code>REQUIRE_API_KEY_FOR_VERIFICATION=1</code> in your environment configuration</li>
                <li>Create an API key in your organisation settings</li>
                <li>Include the API key in requests:
                    <ul>
                        <li>Header: <code>X-API-Key: your-api-key</code></li>
                        <li>Or parameter: <code>?api_key=your-api-key</code></li>
                    </ul>
                </li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important Notes</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>QR code tokens expire after 5 minutes. Employees should refresh their ID card page if the token expires.</li>
                    <li>The access system needs internet connectivity to verify tokens.</li>
                    <li>All access attempts are logged for security and compliance purposes.</li>
                    <li>For production use, always use HTTPS to protect API communications.</li>
                </ul>
            </div>
            
            <h2>Example Integration Code</h2>
            <p>Here's a simple example of how to integrate with the API:</p>
            
            <h3>Python Example</h3>
            <pre><code>import requests
import urllib.parse

def verify_access(qr_code_url, location, device_id):
    # Extract token from QR code URL
    parsed = urllib.parse.urlparse(qr_code_url)
    params = urllib.parse.parse_qs(parsed.query)
    token = params.get('token', [None])[0]
    
    if not token:
        return False, "No token found in QR code"
    
    # Call verification API
    api_url = "<?php echo APP_URL; ?>/api/verify-token.php"
    response = requests.get(api_url, params={
        'token': token,
        'type': 'qr',
        'location': location,
        'device_id': device_id
    })
    
    if response.status_code == 200:
        data = response.json()
        if data.get('success') and data.get('valid'):
            return True, f"Access granted for {data['employee']['full_name']}"
        else:
            return False, data.get('message', 'Verification failed')
    else:
        return False, f"API error: {response.status_code}"

# Usage
success, message = verify_access(
    qr_code_url="<?php echo APP_URL; ?>/verify.php?token=abc123",
    location="Main_Entrance",
    device_id="TURNSTILE_01"
)
if success:
    # Grant access
    open_turnstile()
else:
    # Deny access
    deny_access(message)</code></pre>
            
            <h2>Support</h2>
            <p>For integration support or questions about the Building Access API, contact your organisation administrator or refer to the <a href="<?php echo url('docs-troubleshooting.php'); ?>">Troubleshooting</a> guide.</p>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Verification - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Verification Methods</h1>
            <p>Digital ID supports three verification methods, each with different security levels and use cases.</p>
            
            <h2>Visual Verification</h2>
            <h3>How It Works</h3>
            <p>Display your digital ID card and compare the photo and employee details with the person presenting it.</p>
            
            <h3>Use Cases</h3>
            <ul>
                <li>Quick identity checks</li>
                <li>Face-to-face verification</li>
                <li>Service user verification</li>
                <li>Informal checks</li>
            </ul>
            
            <h3>Security Level</h3>
            <p><strong>Basic</strong> - Relies on visual comparison and photo matching. No automated verification or logging.</p>
            
            <h2>QR Code Verification</h2>
            <h3>How It Works</h3>
            <ol>
                <li>Display your digital ID card</li>
                <li>The QR code contains a time-limited verification token</li>
                <li>Scan the QR code using any QR scanner or the verification page</li>
                <li>The system validates the token and displays verification results</li>
            </ol>
            
            <h3>Use Cases</h3>
            <ul>
                <li>Online verification</li>
                <li>Remote checks</li>
                <li>Automated systems</li>
                <li>Service providers</li>
                <li>Bank transactions</li>
            </ul>
            
            <h3>Security Level</h3>
            <p><strong>High</strong> - Time-limited token (5 minutes), cryptographically secure, logged, and validated.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Token Expiry</h4>
                <p>QR code tokens expire after 5 minutes to prevent replay attacks. The token automatically refreshes when you view your ID card.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Internet Requirement</h4>
                <p><strong>The person scanning your QR code needs internet access</strong> to validate the token on the verification server. You (the ID card owner) can display your QR code offline, but if the token expires, you'll need internet to refresh your ID card page and get a new QR code.</p>
            </div>
            
            <h2>Supplementary Verification Methods</h2>
            <p>In addition to QR codes, the system supports NFC and BLE (Bluetooth Low Energy) as supplementary verification methods. These are optional features that work alongside QR codes.</p>
            
            <h3>NFC Verification</h3>
            <h4>How It Works</h4>
            <ol>
                <li>Activate NFC on your device</li>
                <li>View your digital ID card</li>
                <li>Tap your device to write the verification token to an NFC tag</li>
                <li>Or use NFC-enabled verification systems</li>
            </ol>
            
            <h4>Browser Support</h4>
            <ul>
                <li>Chrome on Android (version 89+)</li>
                <li>Edge on Android (version 89+)</li>
                <li><strong>Not supported:</strong> iOS Safari, Firefox, desktop browsers</li>
            </ul>
            
            <h4>Use Cases</h4>
            <ul>
                <li>Contactless verification</li>
                <li>Door access systems</li>
                <li>Automated checkpoints</li>
                <li>Meeting attendance</li>
            </ul>
            
            <h4>Security Level</h4>
            <p><strong>High</strong> - Time-limited token (5 minutes), contactless, logged, and validated.</p>
            
            <h3>BLE (Bluetooth Low Energy) Verification</h3>
            <h4>How It Works</h4>
            <ol>
                <li>Activate BLE on your device</li>
                <li>View your digital ID card</li>
                <li>Share the verification URL via Bluetooth</li>
                <li>Verifier device receives the verification token</li>
            </ol>
            
            <h4>Browser Support</h4>
            <ul>
                <li>Chrome on Android and Desktop</li>
                <li>Edge on Android and Desktop</li>
                <li><strong>Not supported:</strong> iOS Safari, Firefox</li>
            </ul>
            
            <h4>Use Cases</h4>
            <ul>
                <li>Proximity-based verification</li>
                <li>Bluetooth-enabled access systems</li>
                <li>Device-to-device verification</li>
            </ul>
            
            <h4>Security Level</h4>
            <p><strong>High</strong> - Time-limited token (5 minutes), proximity-based, logged, and validated.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Note</h4>
                <p><strong>QR codes remain the primary verification method</strong> as they work on all devices and browsers. NFC and BLE are supplementary features for specific use cases and device combinations.</p>
            </div>
            
            <h2>Public Verification Page</h2>
            <p>The public verification page allows anyone to verify employee identity:</p>
            <ol>
                <li>Visit the verification page</li>
                <li>Scan the QR code, or use manual lookup by entering the organisation name and employee reference</li>
                <li>View the verification results</li>
            </ol>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Manual Lookup Needs the Organisation Name</h4>
                <p>For manual lookup, enter the organisation name exactly as shown on the person's digital ID card, along with their employee reference. Both are required together - this keeps the lookup scoped to the right organisation and means the verification page never displays a list of other organisations using Digital ID.</p>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Rate Limited to Prevent Abuse</h4>
                <p>To protect against automated guessing, both QR/NFC verification and manual lookup are rate limited per visitor. If you see a "Too many verification attempts" message, wait a few minutes before trying again.</p>
            </div>

            <h3>What Information Is Shown</h3>
            <ul>
                <li>Employee name</li>
                <li>Employee reference</li>
                <li>Organisation name</li>
                <li>Verification status</li>
                <li>Card expiration status</li>
            </ul>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Privacy</h4>
                <p>The verification page only shows information necessary for identity verification. Personal contact details are not displayed.</p>
            </div>
            
            <h2>Verification Results</h2>
            <p>When a verification is performed, the system checks:</p>
            <ul>
                <li>Token validity</li>
                <li>Token expiration (must be less than 5 minutes old)</li>
                <li>Card revocation status</li>
                <li>Employee active status</li>
                <li>Organisation membership</li>
            </ul>
            
            <h2>Audit Trail</h2>
            <p>Every verification attempt is automatically logged with comprehensive details for compliance and security monitoring.</p>
            
            <h3>What Gets Logged</h3>
            <p>Each verification attempt records:</p>
            <ul>
                <li><strong>Timestamp:</strong> Exact date and time of verification</li>
                <li><strong>Verification Method:</strong> Visual, QR code, NFC, or BLE</li>
                <li><strong>Result:</strong> Success, failed, expired, or revoked</li>
                <li><strong>Employee Details:</strong> Name and reference number</li>
                <li><strong>Verifier Information:</strong> Who performed the verification (if logged in) or "Public Verification"</li>
                <li><strong>IP Address:</strong> Network location of the verification</li>
                <li><strong>Device Information:</strong> Browser and device details</li>
                <li><strong>Failure Reason:</strong> Detailed notes if verification failed</li>
            </ul>
            
            <h3>Admin Verification Logs Interface</h3>
            <p>Administrators can access the Verification Logs page from the Organisation menu to:</p>
            <ul>
                <li><strong>View All Logs:</strong> See every verification attempt in your organisation</li>
                <li><strong>Filter Results:</strong> Filter by date range, employee, verification type, or result</li>
                <li><strong>Export Data:</strong> Download logs as CSV files for compliance reporting</li>
                <li><strong>Search History:</strong> Quickly find specific verifications using filters</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Compliance Ready</h4>
                <p>The audit trail provides complete documentation for regulatory compliance, security audits, and quality assurance reviews. All verification attempts are permanently logged and cannot be modified.</p>
            </div>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Privacy</h4>
                <p>Verification logs are only accessible to organisation administrators. Personal contact details are not included in logs, maintaining privacy while ensuring security.</p>
            </div>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

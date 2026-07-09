<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Troubleshooting - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Troubleshooting</h1>
            <p>Common issues and solutions for using Digital ID.</p>
            
            <h2>Account Issues</h2>
            
            <h3>I Can't Log In</h3>
            <ul>
                <li>Check that your email address is correct</li>
                <li>Verify your password is correct (check caps lock)</li>
                <li>Ensure your email has been verified</li>
                <li>Try resetting your password</li>
                <li>Contact your organisation administrator</li>
            </ul>
            
            <h3>I Didn't Receive Verification Email</h3>
            <ul>
                <li>Check your spam/junk folder</li>
                <li>Verify the email address is correct</li>
                <li>Request a new verification email</li>
                <li>Check with your IT department if emails are being blocked</li>
            </ul>
            
            <h3>My Account Is Locked</h3>
            <p>Contact your organisation administrator to unlock your account.</p>
            
            <h2>ID Card Issues</h2>
            
            <h3>I Can't See My ID Card</h3>
            <ul>
                <li>Ensure your organisation administrator has created your employee profile</li>
                <li>Check that your user account is linked to an employee record</li>
                <li>Verify your account is active</li>
                <li>Contact your organisation administrator</li>
            </ul>
            
            <h3>My ID Card Shows as Expired</h3>
            <p>Contact your organisation administrator to update your card expiration date.</p>
            
            <h3>My ID Card Is Revoked</h3>
            <p>If your ID card has been revoked, contact your organisation administrator. Revoked cards cannot be verified.</p>
            
            <h2>Verification Issues</h2>
            
            <h3>QR Code Won't Scan</h3>
            <ul>
                <li>Ensure your device screen is clean and bright</li>
                <li>Try refreshing your ID card page to get a new QR code</li>
                <li>Check that the QR code hasn't expired (tokens expire after 5 minutes)</li>
                <li>Use a different QR scanner app</li>
            </ul>
            
            <h3>Verification Shows as Failed</h3>
            <ul>
                <li>Check that your ID card hasn't expired</li>
                <li>Verify your card hasn't been revoked</li>
                <li>Ensure you're using a fresh QR code (less than 5 minutes old)</li>
                <li>Check that you're still an active employee</li>
            </ul>

            <h3>Manual Lookup Says "Employee Not Found"</h3>
            <ul>
                <li>Double-check the organisation name is spelt exactly as shown on the digital ID card</li>
                <li>Double-check the employee reference is correct</li>
                <li>Note that this message is shown for both an incorrect organisation name and an incorrect employee reference, so it won't confirm which one was wrong</li>
                <li>Ask the ID card holder to confirm their organisation name and reference directly</li>
            </ul>

            <h3>"Too Many Verification Attempts" Message</h3>
            <ul>
                <li>This appears after repeated failed verification attempts from the same visitor, and is a security measure to prevent automated guessing</li>
                <li>Wait for the time shown in the message (up to 15 minutes), then try again</li>
                <li>If this happens regularly during legitimate use (e.g. a busy reception desk), contact your organisation administrator</li>
            </ul>

            <h3>NFC Not Working</h3>
            <ul>
                <li>Ensure NFC is enabled on your device</li>
                <li>Check that your device supports NFC</li>
                <li>Try refreshing your ID card to get a new token</li>
                <li>Ensure the NFC tag or reader is compatible</li>
            </ul>
            
            <h2>Import/Export Issues</h2>
            
            <h3>Import File Won't Upload</h3>
            <ul>
                <li>Check file size (maximum 2MB)</li>
                <li>Verify file format (CSV or JSON)</li>
                <li>Check file encoding (UTF-8 recommended)</li>
                <li>Review file format against examples</li>
            </ul>
            
            <h3>Import Errors</h3>
            <ul>
                <li>Check that required columns are present</li>
                <li>Verify data format matches examples</li>
                <li>Ensure users exist before assigning to units</li>
                <li>Check that unit names match exactly (case-sensitive)</li>
                <li>Review import warnings for specific issues</li>
            </ul>
            
            <h2>Organisational Structure Issues</h2>
            
            <h3>Can't Create Organisational Unit</h3>
            <ul>
                <li>Verify you have organisation administrator permissions</li>
                <li>Check that parent unit exists (if specified)</li>
                <li>Ensure unit name is unique within your organisation</li>
            </ul>
            
            <h3>Can't Assign Members</h3>
            <ul>
                <li>Verify users exist in your organisation</li>
                <li>Check email addresses match exactly</li>
                <li>Ensure the organisational unit exists</li>
                <li>Verify unit name matches exactly (case-sensitive)</li>
            </ul>
            
            <h2>Microsoft Entra Integration Issues</h2>
            
            <h3>Can't Log In with Microsoft</h3>
            <ul>
                <li>Verify Entra integration is enabled</li>
                <li>Check Tenant ID, Client ID, and Client Secret are correct</li>
                <li>Verify redirect URI matches in Azure AD</li>
                <li>Check API permissions are granted</li>
                <li>Ensure admin consent is given (if required)</li>
            </ul>
            
            <h2>Getting Help</h2>
            <p>If you're experiencing issues accessing your account or using the system:</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> In-App Admin Contact Details</h4>
                <p>When you encounter access issues or other problems, the system will automatically display your organisation administrator's contact details directly on the screen. This information is shown at the point where the trouble is experienced to help reduce stress and provide immediate assistance.</p>
            </div>
            
            <h3>Escalation Process</h3>
            <ol class="step-list">
                <li>
                    <strong>Check the in-app display:</strong> When you experience an issue, look for the automatically displayed contact details of your organisation administrator shown on the screen.
                </li>
                <li>
                    <strong>Contact your organisation administrator:</strong> Use the contact details displayed in the app to reach out to your organisation administrator for assistance.
                </li>
                <li>
                    <strong>Contact the super administrator:</strong> If your organisation administrator cannot resolve the issue or you cannot reach them, the system will also display super administrator contact details for escalation.
                </li>
            </ol>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Support Channels</h4>
                <p>You can get help through:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>In-app display of admin contact details (shown automatically when issues occur)</li>
                    <li>Direct contact with your organisation administrator</li>
                    <li>Super administrator escalation (contact details also displayed in-app if needed)</li>
                    <li>Documentation and troubleshooting guides</li>
                </ul>
            </div>
            
            <div class="info-box" style="margin-top: 1.5rem;">
                <h4><i class="fas fa-heart"></i> Designed to Reduce Stress</h4>
                <p>We understand that access issues can be stressful. That's why we display administrator contact details directly in the app at the point where problems occur, so you don't have to search for help - it's right there when you need it.</p>
            </div>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

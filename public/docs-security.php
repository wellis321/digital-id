<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Security - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Security</h1>
            <p>Digital ID implements multiple layers of security to protect your organisation's data and employee identities.</p>
            
            <h2>Security Features</h2>
            
            <h3>Cryptographically Secure Tokens</h3>
            <p>All QR and NFC tokens are generated using cryptographically secure random number generation (64-character hex strings). Tokens are unique and cannot be predicted or guessed.</p>
            
            <h3>Time-Limited Access</h3>
            <p>QR and NFC tokens expire after 5 minutes, preventing replay attacks. Even if a token is intercepted, it becomes useless after expiration. Cards also have expiration dates set by administrators.</p>
            
            <h3>Immediate Revocation</h3>
            <p>ID cards can be revoked instantly by administrators if compromised, lost, or when an employee leaves. Revoked cards cannot be verified, even with valid tokens.</p>
            
            <h3>Multi-Layer Validation</h3>
            <p>Every verification goes through multiple checks:</p>
            <ul>
                <li>Token validity</li>
                <li>Expiration status</li>
                <li>Card revocation status</li>
                <li>Employee active status</li>
                <li>Organisation membership</li>
            </ul>
            
            <h3>Complete Audit Trail</h3>
            <p>Every verification attempt is logged with full details including timestamp, verification method, result, and failure reason. Perfect for compliance and security audits.</p>
            
            <h3>Strong Password Requirements</h3>
            <p>User accounts require passwords with minimum 8 characters, including uppercase, lowercase, numbers, and special characters. Passwords are hashed using industry-standard algorithms.</p>
            
            <h3>Role-Based Access Control</h3>
            <p>Multi-level access control with Superadmin, Organisation Admin, and Staff roles. Each organisation's data is completely isolated from others.</p>
            
            <h3>SQL Injection Prevention</h3>
            <p>All database queries use prepared statements, preventing SQL injection attacks. User input is always validated and sanitised before processing.</p>
            
            <h3>XSS Protection</h3>
            <p>All user-generated content is escaped using <code>htmlspecialchars()</code> to prevent cross-site scripting (XSS) attacks.</p>
            
            <h3>CSRF Protection</h3>
            <p>All forms are protected against Cross-Site Request Forgery (CSRF) attacks using secure tokens that are validated on every submission.</p>
            
            <h3>Email Verification</h3>
            <p>Users must verify their email address before their account is activated, preventing unauthorised account creation and ensuring valid contact information.</p>
            
            <h3>Multi-Tenant Isolation</h3>
            <p>Each organisation's data is completely isolated. Users can only access data from their own organisation, enforced at the database and application level.</p>
            
            <h2>Security Best Practices</h2>
            
            <h3>For Administrators</h3>
            <ul>
                <li>Revoke ID cards immediately when employees leave</li>
                <li>Regularly review verification logs</li>
                <li>Monitor for suspicious activity</li>
                <li>Keep administrator accounts secure</li>
                <li>Use strong, unique passwords</li>
            </ul>
            
            <h3>For Users</h3>
            <ul>
                <li>Use strong, unique passwords</li>
                <li>Never share your login credentials</li>
                <li>Report suspicious activity immediately</li>
                <li>Keep your device secure</li>
                <li>Log out when finished</li>
            </ul>
            
            <h2>Data Privacy</h2>
            <p>Digital ID is designed with privacy in mind:</p>
            <ul>
                <li>Only necessary information is displayed during verification</li>
                <li>Personal contact details are not shown on public verification</li>
                <li>Organisation data is completely isolated</li>
                <li>Audit logs are accessible only to administrators</li>
            </ul>
            
            <h2>Compliance</h2>
            <p>Digital ID helps organisations meet compliance requirements:</p>
            <ul>
                <li>Complete audit trails for all verification attempts</li>
                <li>Secure data storage and transmission</li>
                <li>Access control and user management</li>
                <li>Data portability (export functionality)</li>
            </ul>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

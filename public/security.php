<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Security & How It Works';
include INCLUDES_PATH . '/header.php';
?>

<style>
.hero-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    padding: 4rem 0;
    margin-bottom: 4rem;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.hero-content p {
    font-size: 1.25rem;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.hero-image {
    background-color: #eff6ff;
    border-radius: 0;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.125rem;
    position: relative;
    overflow: hidden;
}

.hero-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%23eff6ff"/><path d="M20 20h60v60h-60z" fill="none" stroke="%23bfdbfe" stroke-width="2"/><circle cx="50" cy="50" r="15" fill="none" stroke="%23bfdbfe" stroke-width="2"/></svg>');
    background-repeat: repeat;
    opacity: 0.3;
}

.hero-image-placeholder {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
}

.hero-image-placeholder i {
    font-size: 4rem;
    margin-bottom: 1rem;
    display: block;
    color: #93c5fd;
}

@media (max-width: 968px) {
    .hero-section {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .hero-image {
        order: -1;
    }
}

.section {
    margin: 3rem 0;
}

.section h2 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    color: #1f2937;
    border-bottom: 3px solid #3b82f6;
    padding-bottom: 0.5rem;
}

.section h3 {
    font-size: 1.75rem;
    margin: 2rem 0 1rem;
    color: #3b82f6;
}

.security-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.security-feature {
    background: white;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-left: 4px solid #8b5cf6;
}

.security-feature:nth-child(3n+1) {
    border-left-color: #3b82f6;
}

.security-feature:nth-child(3n+2) {
    border-left-color: #10b981;
}

.security-feature:nth-child(3n+3) {
    border-left-color: #8b5cf6;
}

.security-feature-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    display: block;
}

.security-feature:nth-child(3n+1) .security-feature-icon {
    color: #3b82f6;
}

.security-feature:nth-child(3n+2) .security-feature-icon {
    color: #10b981;
}

.security-feature:nth-child(3n+3) .security-feature-icon {
    color: #8b5cf6;
}

.security-feature h4 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.security-feature p {
    color: #6b7280;
    line-height: 1.7;
    font-size: 1rem;
}

.how-it-works-steps {
    display: grid;
    gap: 2rem;
    margin: 2rem 0;
}

.step {
    display: flex;
    gap: 1.5rem;
    background: white;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.step-number {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    color: white;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.step:nth-child(1) .step-number {
    background-color: #3b82f6;
}

.step:nth-child(2) .step-number {
    background-color: #10b981;
}

.step:nth-child(3) .step-number {
    background-color: #8b5cf6;
}

.step:nth-child(4) .step-number {
    background-color: #f59e0b;
}

.step:nth-child(5) .step-number {
    background-color: #06b6d4;
}

.step-content h4 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.step-content p {
    color: #6b7280;
    line-height: 1.7;
    font-size: 1rem;
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.comparison-table th {
    background-color: #3b82f6;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.comparison-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.comparison-table tr:last-child td {
    border-bottom: none;
}

.comparison-table tr:nth-child(even) {
    background: #f9fafb;
}

.highlight-box {
    background-color: #e0f2fe;
    border-left: 4px solid #2563eb;
    padding: 2rem;
    margin: 2rem 0;
}

.highlight-box h3 {
    margin-top: 0;
    color: #1e40af;
    font-size: 1.5rem;
}

.highlight-box p {
    font-size: 1.125rem;
    line-height: 1.8;
}

.use-cases-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.use-case {
    background: white;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-top: 3px solid #10b981;
}

.use-case-icon {
    color: #10b981;
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
}

.use-case h4 {
    margin: 0.5rem 0;
    color: #1f2937;
}

.use-case p {
    margin: 0;
    color: #6b7280;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .step {
        flex-direction: column;
        text-align: center;
    }
    
    .comparison-table {
        font-size: 0.875rem;
    }
}
</style>

<div class="hero-section">
    <div class="hero-content">
        <h1>Security & How It Works</h1>
        <p>Understanding the security features and verification methods that protect your digital identity. Learn how our multi-layered approach ensures your organisation's data remains secure.</p>
        <a href="<?php echo url('features.php'); ?>" class="btn btn-primary">Explore Features</a>
    </div>
    <div class="hero-image">
        <div class="hero-image-placeholder">
            <i class="fas fa-shield-alt"></i>
            <div>Security Overview</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="section">
        <h2>How Digital ID Works</h2>
        
        <div class="how-it-works-steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Employee Profile Creation</h4>
                    <p>Organisation administrators create employee profiles with unique employee reference numbers. Each employee is linked to their user account and organisation.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Digital ID Card Generation</h4>
                    <p>When an employee views their ID card, the system automatically generates secure, time-limited tokens for QR code and NFC verification. Each card has an expiration date.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Verification Methods</h4>
                    <p>Identity can be verified through three methods: <strong>Visual</strong> (photo and details), <strong>QR Code</strong> (scan for online verification), or <strong>NFC</strong> (tap for contactless verification).</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Secure Token Validation</h4>
                    <p>When a QR code or NFC tag is scanned, the system validates the token, checks expiration, verifies the card is active, and confirms the employee status before displaying verification results.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Audit Trail Recording</h4>
                    <p>Every verification attempt is logged with timestamp, method, result, and reason (if failed). This creates a complete audit trail for compliance and security monitoring.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Security Features</h2>
        
        <div class="security-features-grid">
            <div class="security-feature">
                <i class="fas fa-key security-feature-icon"></i>
                <h4>Cryptographically Secure Tokens</h4>
                <p>All QR and NFC tokens are generated using cryptographically secure random number generation (64-character hex strings). Tokens are unique and cannot be predicted or guessed.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-clock security-feature-icon"></i>
                <h4>Time-Limited Access</h4>
                <p>QR and NFC tokens expire after 5 minutes, preventing replay attacks. Even if a token is intercepted, it becomes useless after expiration. Cards also have expiration dates.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-ban security-feature-icon"></i>
                <h4>Immediate Revocation</h4>
                <p>ID cards can be revoked instantly by administrators if compromised, lost, or when an employee leaves. Revoked cards cannot be verified, even with valid tokens.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-shield-alt security-feature-icon"></i>
                <h4>Multi-Layer Validation</h4>
                <p>Every verification goes through multiple checks: token validity, expiration status, card revocation status, employee active status, and organisation membership.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-file-alt security-feature-icon"></i>
                <h4>Complete Audit Trail</h4>
                <p>Every verification attempt is logged with full details including timestamp, verification method, result, and failure reason. Perfect for compliance and security audits.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-lock security-feature-icon"></i>
                <h4>Strong Password Requirements</h4>
                <p>User accounts require passwords with minimum 8 characters, including uppercase, lowercase, numbers, and special characters. Passwords are hashed using industry-standard algorithms.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-user-shield security-feature-icon"></i>
                <h4>Role-Based Access Control</h4>
                <p>Multi-level access control with Superadmin, Organisation Admin, and Staff roles. Each organisation's data is completely isolated from others.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-database security-feature-icon"></i>
                <h4>SQL Injection Prevention</h4>
                <p>All database queries use prepared statements, preventing SQL injection attacks. User input is always validated and sanitised before processing.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-code security-feature-icon"></i>
                <h4>XSS Protection</h4>
                <p>All user-generated content is escaped using <code>htmlspecialchars()</code> to prevent cross-site scripting (XSS) attacks.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-shield-virus security-feature-icon"></i>
                <h4>CSRF Protection</h4>
                <p>All forms are protected against Cross-Site Request Forgery (CSRF) attacks using secure tokens that are validated on every submission.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-envelope-check security-feature-icon"></i>
                <h4>Email Verification</h4>
                <p>Users must verify their email address before their account is activated, preventing unauthorised account creation and ensuring valid contact information.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-network-wired security-feature-icon"></i>
                <h4>Multi-Tenant Isolation</h4>
                <p>Each organisation's data is completely isolated. Users can only access data from their own organisation, enforced at the database and application level.</p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Verification Methods Explained</h2>
        
        <div class="security-features-grid">
            <div class="security-feature">
                <i class="fas fa-eye security-feature-icon"></i>
                <h4>Visual Verification</h4>
                <p><strong>How it works:</strong> Display the digital ID card and compare the photo and employee details with the person presenting it.</p>
                <p><strong>Use case:</strong> Quick identity checks, face-to-face verification, service user verification.</p>
                <p><strong>Security level:</strong> Basic - relies on visual comparison and photo matching.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-qrcode security-feature-icon"></i>
                <h4>QR Code Verification</h4>
                <p><strong>How it works:</strong> Scan the QR code on the ID card using any QR scanner or the verification page. The system validates the token and displays verification results.</p>
                <p><strong>Use case:</strong> Online verification, remote checks, automated systems, service providers.</p>
                <p><strong>Security level:</strong> High - time-limited token (5 minutes), cryptographically secure, logged.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-wifi security-feature-icon"></i>
                <h4>NFC Verification</h4>
                <p><strong>How it works:</strong> Activate NFC on the device, then tap to write the verification token to an NFC tag or use NFC-enabled verification systems.</p>
                <p><strong>Use case:</strong> Contactless verification, door access systems, automated checkpoints.</p>
                <p><strong>Security level:</strong> High - time-limited token (5 minutes), contactless, logged.</p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Use Cases</h2>
        
        <div class="use-cases-grid">
            <div class="use-case">
                <i class="fas fa-university use-case-icon"></i>
                <h4>Bank Transactions</h4>
                <p>Prove employee identity when acting on behalf of vulnerable clients at banks and financial institutions</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-exclamation-triangle use-case-icon"></i>
                <h4>Emergency Situations</h4>
                <p>Quick identity verification during emergencies, fire drills, and safety checks</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-users use-case-icon"></i>
                <h4>Service User Verification</h4>
                <p>Service users, families, and carers can verify staff identity through the public verification system</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-clipboard-check use-case-icon"></i>
                <h4>Meeting Attendance</h4>
                <p>Automatically track attendance at meetings, training sessions, and mandatory briefings</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-user-shield use-case-icon"></i>
                <h4>Lone Working Safety</h4>
                <p>Verify staff identity for lone working checks and safety protocols</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-door-open use-case-icon"></i>
                <h4>Access Control</h4>
                <p>Use digital ID for door access systems and secure area entry</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-file-contract use-case-icon"></i>
                <h4>Compliance Audits</h4>
                <p>Complete audit trails for regulatory compliance and quality assurance</p>
            </div>
            
            <div class="use-case">
                <i class="fas fa-handshake use-case-icon"></i>
                <h4>Service Provider Verification</h4>
                <p>External service providers can verify employee identity before providing services</p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>How We Compare</h2>
        
        <div class="highlight-box">
            <h3>Enterprise vs. Consumer Digital ID Systems</h3>
            <p>Our Digital ID system is designed specifically for <strong>organisational employee verification</strong>, which has different security requirements than consumer identity systems (like government-issued digital IDs).</p>
        </div>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Consumer Digital ID</th>
                    <th>Our Enterprise System</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Storage</strong></td>
                    <td>Local device (user's phone)</td>
                    <td>Organisation's secure database</td>
                </tr>
                <tr>
                    <td><strong>Access Control</strong></td>
                    <td>Biometric (Face ID, Touch ID)</td>
                    <td>Password + role-based access</td>
                </tr>
                <tr>
                    <td><strong>Audit Trail</strong></td>
                    <td>No (privacy-focused)</td>
                    <td>Yes (compliance requirement)</td>
                </tr>
                <tr>
                    <td><strong>Token Expiry</strong></td>
                    <td>Not applicable</td>
                    <td>5 minutes (prevents replay attacks)</td>
                </tr>
                <tr>
                    <td><strong>Verification</strong></td>
                    <td>User presents ID</td>
                    <td>Third party verifies employee</td>
                </tr>
                <tr>
                    <td><strong>Management</strong></td>
                    <td>User controls</td>
                    <td>Organisation administrators</td>
                </tr>
                <tr>
                    <td><strong>Use Case</strong></td>
                    <td>Personal identity (like driver's license)</td>
                    <td>Employee verification (like company ID badge)</td>
                </tr>
            </tbody>
        </table>
        
        <p style="margin-top: 2rem; color: #6b7280; line-height: 1.8; font-size: 1.125rem;">
            <strong>Why the difference?</strong> Consumer digital ID systems prioritise user privacy and control, while enterprise systems prioritise organisational control, compliance, and auditability. Our approach aligns with industry-standard employee verification systems used by organisations worldwide.
        </p>
    </div>
    
    <div class="section">
        <h2>Security Best Practices</h2>
        
        <div class="security-features-grid">
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Regular Token Refresh</h4>
                <p>QR and NFC tokens automatically refresh every 5 minutes, ensuring old tokens cannot be reused even if intercepted.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Immediate Revocation</h4>
                <p>Revoke ID cards immediately when employees leave or if cards are compromised. Revocation takes effect instantly.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Monitor Audit Logs</h4>
                <p>Regularly review verification logs to identify suspicious activity or unauthorised access attempts.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Strong Passwords</h4>
                <p>Ensure all users have strong, unique passwords. The system enforces password complexity requirements.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Regular Updates</h4>
                <p>Keep the system updated with the latest security patches and improvements.</p>
            </div>
            
            <div class="security-feature">
                <i class="fas fa-check-circle security-feature-icon" style="color: #10b981;"></i>
                <h4>Access Control</h4>
                <p>Limit administrative access to trusted personnel only. Use role-based access control effectively.</p>
            </div>
        </div>
    </div>
    
    <div class="highlight-box">
        <h3>Questions About Security?</h3>
        <p>If you have questions about our security features or need assistance with security configuration, please contact your organisation administrator or reach out to our support team.</p>
        <p style="margin-top: 1rem;">
            <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">Return to Home</a>
            <?php if (!Auth::isLoggedIn()): ?>
                <a href="<?php echo url('request-access.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">Request Access</a>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


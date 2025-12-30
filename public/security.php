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
    background-color: #f3f4f6;
    border-radius: 0;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.125rem;
    position: relative;
    overflow: hidden;
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
    color: #9ca3af;
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

.two-column-section {
    padding: 2rem 0;
    margin-top: 2rem;
}

.two-column-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: stretch;
}

.two-column-text {
    display: flex;
    flex-direction: column;
}

.two-column-text h3 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
}

.two-column-text p {
    font-size: 1.125rem;
    color: #6b7280;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.two-column-text ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
    color: #4b5563;
    line-height: 1.8;
}

.two-column-text ul li {
    margin-bottom: 0.5rem;
}

.two-column-image {
    background-color: #f3f4f6;
    border-radius: 0;
    display: block;
    color: #9ca3af;
    font-size: 1.125rem;
    overflow: hidden;
    position: relative;
    height: 100%;
}

.two-column-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top;
    border-radius: 0;
    display: block;
}

.two-column-image.placeholder {
    text-align: center;
    padding: 2rem;
}

.two-column-image.placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    color: #06b6d4;
}

@media (max-width: 968px) {
    .two-column-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Timeline Styles */
.timeline-container {
    position: relative;
    max-width: 1000px;
    margin: 3rem auto 0;
    padding: 0 2rem 4rem;
}

.timeline-line {
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(to bottom, #2563eb, #10b981, #8b5cf6, #f59e0b, #06b6d4);
    transform: translateX(-50%);
    z-index: 1;
}

.timeline-steps {
    position: relative;
    z-index: 2;
}

.timeline-step {
    display: flex;
    align-items: center;
    margin-bottom: -3rem;
    position: relative;
    z-index: 2;
}

.timeline-step:last-child {
    margin-bottom: 0;
}

.timeline-step:nth-child(2) {
    margin-top: 1rem;
}

.timeline-step:nth-child(3) {
    margin-top: 1rem;
}

.timeline-step:nth-child(4) {
    margin-top: 1rem;
}

.timeline-step:nth-child(5) {
    margin-top: 1rem;
}

.timeline-step:nth-child(odd) {
    flex-direction: row;
}

.timeline-step:nth-child(even) {
    flex-direction: row-reverse;
}

.timeline-content {
    flex: 1;
    background: white;
    padding: 2rem;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 45%;
    position: relative;
}

.timeline-step:nth-child(odd) .timeline-content {
    margin-right: auto;
    text-align: right;
}

.timeline-step:nth-child(even) .timeline-content {
    margin-left: auto;
    text-align: left;
}

.timeline-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 0;
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
    display: block;
}

.timeline-marker {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 4px solid white;
}

.timeline-step:nth-child(1) .timeline-marker {
    background-color: #2563eb;
}

.timeline-step:nth-child(2) .timeline-marker {
    background-color: #10b981;
}

.timeline-step:nth-child(3) .timeline-marker {
    background-color: #8b5cf6;
}

.timeline-step:nth-child(4) .timeline-marker {
    background-color: #f59e0b;
}

.timeline-step:nth-child(5) .timeline-marker {
    background-color: #06b6d4;
}

.timeline-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
    margin-top: 0;
}

.timeline-content p {
    color: #6b7280;
    line-height: 1.7;
    margin: 0;
}

@media (max-width: 768px) {
    .timeline-line {
        left: 30px;
    }
    
    .timeline-step {
        flex-direction: row !important;
        padding-left: 4rem;
    }
    
    .timeline-content {
        max-width: 100%;
        text-align: left !important;
        margin: 0 !important;
    }
    
    .timeline-marker {
        left: 30px;
        transform: translateX(-50%);
    }
    
    .timeline-image {
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100%;
    }
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 2rem;
}

@media (max-width: 1024px) {
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
}

.feature-item {
    background: white;
    padding: 2rem;
    border: 1px solid #e5e7eb;
    border-radius: 0;
}

.feature-icon-wrapper {
    width: 64px;
    height: 64px;
    background-color: #f0f9ff;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.feature-icon-wrapper i {
    font-size: 1.75rem;
    color: #06b6d4;
}

.feature-item h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.feature-item p {
    color: #6b7280;
    line-height: 1.7;
    margin: 0;
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.comparison-table th {
    background-color: #1f2937;
    color: white;
    padding: 1.25rem 1.5rem;
    text-align: left;
    font-weight: 600;
    font-size: 1.0625rem;
    border-right: 1px solid rgba(255,255,255,0.1);
}

.comparison-table th:first-child {
    background-color: #111827;
    width: 25%;
}

.comparison-table th:nth-child(2) {
    background-color: #374151;
    width: 37.5%;
}

.comparison-table th:nth-child(3) {
    background-color: #10b981;
    width: 37.5%;
    border-right: none;
}

.comparison-table td {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    vertical-align: top;
}

.comparison-table td:first-child {
    background-color: #f9fafb;
    font-weight: 600;
    color: #1f2937;
}

.comparison-table td:nth-child(2) {
    background-color: #ffffff;
    color: #4b5563;
}

.comparison-table td:nth-child(3) {
    background-color: #f0fdf4;
    color: #047857;
    border-right: none;
    font-weight: 500;
}

.comparison-table tr:last-child td {
    border-bottom: none;
}

.comparison-table tr:hover td {
    background-color: #f3f4f6;
}

.comparison-table tr:hover td:first-child {
    background-color: #f3f4f6;
}

.comparison-table tr:hover td:nth-child(3) {
    background-color: #dcfce7;
}

.highlight-box {
    background-color: #eff6ff;
    border-left: 4px solid #2563eb;
    padding: 2rem;
    margin: 2rem 0;
    border-radius: 0;
}

.highlight-box h3 {
    margin-top: 0;
    color: #1e40af;
    font-size: 1.5rem;
}

.highlight-box p {
    font-size: 1.125rem;
    line-height: 1.8;
    color: #1e40af;
}
</style>

<!-- Hero Section -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="hero-section">
            <div class="hero-content">
                <h1>Security & How It Works</h1>
                <p>Understanding the security features and verification methods that protect your digital identity. Learn how our multi-layered approach ensures your organisation's data remains secure.</p>
                <a href="<?php echo url('features.php'); ?>" class="btn btn-primary">Explore Features</a>
            </div>
            <div class="hero-image">
                <img src="<?php echo url('assets/images/security/Security & How It Works.png'); ?>" alt="Security & How It Works" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="full-width-section main-section-light-gray" style="padding-bottom: 5rem;">
    <div class="section-content">
        <div class="section-header">
            <h2>How Digital ID Works</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">A simple five-step process from employee profile creation to secure verification</h3>
        </div>
        
        <div class="timeline-container">
            <div class="timeline-line"></div>
            <div class="timeline-steps">
                <div class="timeline-step">
                    <div class="timeline-marker">1</div>
                    <div class="timeline-content">
                        <img src="<?php echo url('assets/images/security/timeline/step-1.png'); ?>" alt="Employee Profile Creation" class="timeline-image" onerror="this.style.display='none';">
                        <h4>Employee Profile Creation</h4>
                        <p>Organisation administrators create employee profiles with unique employee reference numbers. Each employee is linked to their user account and organisation.</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="timeline-marker">2</div>
                    <div class="timeline-content">
                        <img src="<?php echo url('assets/images/security/timeline/step-2.png'); ?>" alt="Digital ID Card Generation" class="timeline-image" onerror="this.style.display='none';">
                        <h4>Digital ID Card Generation</h4>
                        <p>When an employee views their ID card, the system automatically generates secure, time-limited tokens for QR code and NFC verification. Each card has an expiration date.</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="timeline-marker">3</div>
                    <div class="timeline-content">
                        <img src="<?php echo url('assets/images/security/timeline/step-3.png'); ?>" alt="Verification Methods" class="timeline-image" onerror="this.style.display='none';">
                        <h4>Verification Methods</h4>
                        <p>Identity can be verified through three methods: <strong>Visual</strong> (photo and details), <strong>QR Code</strong> (scan for online verification), or <strong>NFC</strong> (tap for contactless verification).</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="timeline-marker">4</div>
                    <div class="timeline-content">
                        <img src="<?php echo url('assets/images/security/timeline/step-4.png'); ?>" alt="Secure Token Validation" class="timeline-image" onerror="this.style.display='none';">
                        <h4>Secure Token Validation</h4>
                        <p>When a QR code or NFC tag is scanned, the system validates the token, checks expiration, verifies the card is active, and confirms the employee status before displaying verification results.</p>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="timeline-marker">5</div>
                    <div class="timeline-content">
                        <img src="<?php echo url('assets/images/security/timeline/step-5.png'); ?>" alt="Audit Trail Recording" class="timeline-image" onerror="this.style.display='none';">
                        <h4>Audit Trail Recording</h4>
                        <p>Every verification attempt is logged with timestamp, method, result, and reason (if failed). This creates a complete audit trail for compliance and security monitoring.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Methods Section -->
<div class="full-width-section main-section" style="padding-bottom: 5rem;">
    <div class="section-content">
        <div class="section-header">
            <h2>Verification Methods</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">Three ways to verify employee identity, each suited to different scenarios</h3>
        </div>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Visual Verification</h3>
                <p><strong>How it works:</strong> Display the digital ID card and compare the photo and employee details with the person presenting it.</p>
                <p style="margin-top: 0.75rem;"><strong>Use case:</strong> Quick identity checks, face-to-face verification, service user verification.</p>
                <p style="margin-top: 0.75rem;"><strong>Security level:</strong> Basic - relies on visual comparison and photo matching.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3>QR Code Verification</h3>
                <p><strong>How it works:</strong> Scan the QR code on the ID card using any QR scanner or the verification page. The system validates the token and displays verification results.</p>
                <p style="margin-top: 0.75rem;"><strong>Use case:</strong> Online verification, remote checks, automated systems, service providers.</p>
                <p style="margin-top: 0.75rem;"><strong>Security level:</strong> High - time-limited token (5 minutes), cryptographically secure, logged.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3>NFC Verification</h3>
                <p><strong>How it works:</strong> Activate NFC on the device, then tap to write the verification token to an NFC tag or use NFC-enabled verification systems.</p>
                <p style="margin-top: 0.75rem;"><strong>Use case:</strong> Contactless verification, door access systems, automated checkpoints.</p>
                <p style="margin-top: 0.75rem;"><strong>Security level:</strong> High - time-limited token (5 minutes), contactless, logged.</p>
            </div>
        </div>
    </div>
</div>

<!-- Core Security Features Section -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="section-header">
            <h2>Core Security Features</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">Multi-layered security measures that protect your organisation's data and employee identities</h3>
        </div>
        
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-text">
                    <h3>Cryptographic Security</h3>
                    <p>
                        All QR and NFC tokens are generated using cryptographically secure random number generation (64-character hex strings). 
                        Tokens are unique and cannot be predicted or guessed, ensuring that even if someone intercepts a token, they cannot generate new ones.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Time-Limited Access</h3>
                    <p>
                        QR and NFC tokens expire after 5 minutes, preventing replay attacks. Even if a token is intercepted, it becomes useless after expiration. 
                        Cards also have expiration dates that can be set by administrators.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Immediate Revocation</h3>
                    <p>
                        ID cards can be revoked instantly by administrators if compromised, lost, or when an employee leaves. 
                        Revoked cards cannot be verified, even with valid tokens, ensuring immediate security control.
                    </p>
                </div>
                <div class="two-column-image">
                    <img src="<?php echo url('assets/images/security/Core Security Features.png'); ?>" alt="Core Security Features" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
                </div>
            </div>
        </div>
        
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-image">
                    <img src="<?php echo url('assets/images/security/ Multi-Layer Protection  Multi-Layer Validation.png'); ?>" alt="Multi-Layer Protection & Multi-Layer Validation" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
                </div>
                <div class="two-column-text">
                    <h3>Multi-Layer Validation</h3>
                    <p>
                        Every verification goes through multiple checks before approval:
                    </p>
                    <ul>
                        <li>Token validity and format verification</li>
                        <li>Expiration status check</li>
                        <li>Card revocation status</li>
                        <li>Employee active status</li>
                        <li>Organisation membership verification</li>
                    </ul>
                    
                    <h3 style="margin-top: 2rem;">Complete Audit Trail</h3>
                    <p>
                        Every verification attempt is logged with full details including timestamp, verification method, result, IP address, 
                        and failure reason. Perfect for compliance and security audits.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Role-Based Access Control</h3>
                    <p>
                        Multi-level access control with Superadmin, Organisation Admin, and Staff roles. 
                        Each organisation's data is completely isolated from others, enforced at both database and application levels.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Security Section -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="section-header">
            <h2>Application Security</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">Industry-standard security practices protect against common threats</h3>
        </div>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-key"></i>
                </div>
                <h3>Strong Password Requirements</h3>
                <p>User accounts require passwords with minimum 8 characters, including uppercase, lowercase, numbers, and special characters. Passwords are hashed using industry-standard algorithms.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-database"></i>
                </div>
                <h3>SQL Injection Prevention</h3>
                <p>All database queries use prepared statements, preventing SQL injection attacks. User input is always validated and sanitised before processing.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-code"></i>
                </div>
                <h3>XSS Protection</h3>
                <p>All user-generated content is escaped using <code>htmlspecialchars()</code> to prevent cross-site scripting (XSS) attacks.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3>CSRF Protection</h3>
                <p>All forms are protected against Cross-Site Request Forgery (CSRF) attacks using secure tokens that are validated on every submission.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email Verification</h3>
                <p>Users must verify their email address before their account is activated, preventing unauthorised account creation and ensuring valid contact information.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h3>Multi-Tenant Isolation</h3>
                <p>Each organisation's data is completely isolated. Users can only access data from their own organisation, enforced at the database and application level.</p>
            </div>
        </div>
    </div>
</div>

<!-- Comparison Section -->
<div class="full-width-section main-section-light-gray" style="padding-top: 5rem;">
    <div class="section-content">
        <div class="section-header">
            <h2>How We Compare</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">Understanding the difference between consumer and enterprise digital ID systems</h3>
        </div>
        
        <div class="highlight-box" style="background-color: #f0fdf4; border: 2px solid #10b981; border-left: 4px solid #10b981; max-width: 900px; margin: 2rem auto 0;">
            <h3 style="margin-top: 0; color: #047857; font-size: 1.5rem; margin-bottom: 1rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                Enterprise vs. Consumer Digital ID Systems
            </h3>
            <p style="margin: 0; font-size: 1.125rem; line-height: 1.8; color: #047857;">
                Our Digital ID system is designed specifically for <strong>organisational employee verification</strong>, 
                which has different security requirements than consumer identity systems (like government-issued digital IDs).
            </p>
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
                    <td>Device-level security (user's phone may have biometrics)</td>
                    <td>Strong password + role-based access control</td>
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
        
        <div class="highlight-box" style="max-width: 900px; margin: 2rem auto 4rem; border: 2px solid #2563eb; border-left: 4px solid #2563eb;">
            <h3 style="margin-top: 0; color: #1e40af; font-size: 1.25rem; margin-bottom: 1rem;">
                <i class="fas fa-question-circle" style="margin-right: 0.5rem;"></i>
                Why the difference?
            </h3>
            <p style="margin: 0; font-size: 1.125rem; line-height: 1.8; color: #1e40af;">
                Consumer digital ID systems prioritise user privacy and control, 
                while enterprise systems prioritise organisational control, compliance, and auditability. 
                Our approach aligns with industry-standard employee verification systems used by organisations worldwide.
            </p>
        </div>
    </div>
</div>

<!-- Security Best Practices Section -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="section-header">
            <h2>Security Best Practices</h2>
            <h3 style="font-size: 1.5rem; font-weight: 500; color: #4b5563; margin-top: 0.5rem; margin-bottom: 0;">Recommendations for maintaining security in your organisation</h3>
        </div>
        
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-text">
                    <h3>Regular Token Refresh</h3>
                    <p>
                        QR and NFC tokens automatically refresh every 5 minutes, ensuring old tokens cannot be reused even if intercepted. 
                        This prevents replay attacks and maintains security.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Immediate Revocation</h3>
                    <p>
                        Revoke ID cards immediately when employees leave or if cards are compromised. 
                        Revocation takes effect instantly, preventing any further verification attempts.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Monitor Audit Logs</h3>
                    <p>
                        Regularly review verification logs to identify suspicious activity or unauthorised access attempts. 
                        The admin interface provides filtering and export capabilities for easy analysis.
                    </p>
                </div>
                <div class="two-column-image">
                    <img src="<?php echo url('assets/images/security/Security Best Practices.png'); ?>" alt="Security Best Practices" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
                </div>
            </div>
        </div>
        
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-image">
                    <img src="<?php echo url('assets/images/security/Strong Passwords.png'); ?>" alt="Strong Passwords">
                </div>
                <div class="two-column-text">
                    <h3>Strong Passwords</h3>
                    <p>
                        Ensure all users have strong, unique passwords. The system enforces password complexity requirements, 
                        but administrators should encourage good password practices.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Regular Updates</h3>
                    <p>
                        Keep the system updated with the latest security patches and improvements. 
                        Regular updates ensure you benefit from the latest security enhancements.
                    </p>
                    
                    <h3 style="margin-top: 2rem;">Access Control</h3>
                    <p>
                        Limit administrative access to trusted personnel only. Use role-based access control effectively 
                        to ensure users only have access to the features they need.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="highlight-box">
            <h3>Questions About Security?</h3>
            <p>
                If you have questions about our security features or need assistance with security configuration, 
                please contact your organisation administrator or reach out to our support team.
            </p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">Return to Home</a>
                <?php if (!Auth::isLoggedIn()): ?>
                    <a href="<?php echo url('request-access.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">Request Access</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Privacy Policy';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Privacy Policy</h1>
    <p style="color: #6b7280; margin-bottom: 2rem;"><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>
    
    <div style="line-height: 1.8;">
        <section style="margin-bottom: 3rem;">
            <h2>1. Introduction</h2>
            <p><?php echo htmlspecialchars(APP_NAME); ?> ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our digital identification service.</p>
            <p>Please read this Privacy Policy carefully. By using our service, you agree to the collection and use of information in accordance with this policy.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>2. Information We Collect</h2>
            
            <h3>2.1 Personal Information</h3>
            <p>We collect information that you provide directly to us, including:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Account Information:</strong> Name, email address, password (stored as a secure hash)</li>
                <li><strong>Employee Information:</strong> Employee reference numbers, photos, job titles, and organisational unit assignments</li>
                <li><strong>Organisation Information:</strong> Organisation name, email domain, and seat allocation</li>
                <li><strong>Contact Information:</strong> Information provided when requesting access or contacting support</li>
            </ul>
            
            <h3>2.2 Automatically Collected Information</h3>
            <p>When you use our service, we automatically collect certain information, including:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Usage Data:</strong> Pages visited, features used, and time spent on the service</li>
                <li><strong>Verification Logs:</strong> Timestamps, verification methods (QR code, NFC, visual), and results</li>
                <li><strong>Technical Data:</strong> IP address, browser type, device information, and operating system</li>
                <li><strong>Cookies and Session Data:</strong> Information stored in cookies and session storage for authentication and functionality</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>3. How We Use Your Information</h2>
            <p>We use the information we collect for the following purposes:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Service Provision:</strong> To provide, maintain, and improve our digital ID service</li>
                <li><strong>Authentication:</strong> To verify user identity and manage access to accounts</li>
                <li><strong>ID Card Generation:</strong> To create and manage digital ID cards with QR codes and NFC tokens</li>
                <li><strong>Verification Services:</strong> To enable verification of employee identity through various methods</li>
                <li><strong>Audit Trails:</strong> To maintain records of verification attempts for compliance and security purposes</li>
                <li><strong>Communication:</strong> To send account-related notifications, verification emails, and support responses</li>
                <li><strong>Security:</strong> To detect, prevent, and address security issues and unauthorised access</li>
                <li><strong>Compliance:</strong> To comply with legal obligations and respond to legal requests</li>
                <li><strong>Analytics:</strong> To understand how the service is used and improve user experience</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>4. Data Sharing and Disclosure</h2>
            
            <h3>4.1 Within Your Organisation</h3>
            <p>Your information may be accessible to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Organisation administrators who manage your organisation's account</li>
                <li>Other administrators within your organisational unit (if configured)</li>
                <li>Authorised personnel designated by your organisation</li>
            </ul>
            
            <h3>4.2 Verification</h3>
            <p>When your ID card is verified (via QR code, NFC, or visual check), verification results may be logged. Public verification allows anyone with your verification token to view limited information about your employee status.</p>
            
            <h3>4.3 Third-Party Services</h3>
            <p>We may share information with third-party service providers who perform services on our behalf, such as:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Email service providers for sending notifications</li>
                <li>Microsoft Entra ID (where configured) for authentication and user synchronisation</li>
                <li>Hosting and infrastructure providers</li>
            </ul>
            <p>These service providers are contractually obligated to protect your information and only use it for specified purposes.</p>
            
            <h3>4.4 Legal Requirements</h3>
            <p>We may disclose your information if required by law or in response to valid legal requests, including:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Compliance with court orders or legal processes</li>
                <li>Protection of our rights, property, or safety</li>
                <li>Prevention of fraud or illegal activity</li>
            </ul>
            
            <h3>4.5 No Sale of Data</h3>
            <p>We do not sell, rent, or trade your personal information to third parties for their marketing purposes.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>5. Data Security</h2>
            <p>We implement appropriate technical and organisational measures to protect your information, including:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Encryption:</strong> Passwords are hashed using industry-standard algorithms</li>
                <li><strong>Secure Tokens:</strong> QR and NFC tokens are cryptographically secure and time-limited</li>
                <li><strong>Access Controls:</strong> Role-based access control limits who can view and modify data</li>
                <li><strong>Database Security:</strong> Prepared statements prevent SQL injection attacks</li>
                <li><strong>HTTPS:</strong> Data transmitted over the internet is encrypted</li>
                <li><strong>Regular Updates:</strong> We keep our systems updated with security patches</li>
            </ul>
            <p>However, no method of transmission over the internet or electronic storage is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>6. Data Retention</h2>
            <p>We retain your information for as long as necessary to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Provide the service to you</li>
                <li>Comply with legal obligations</li>
                <li>Resolve disputes and enforce our agreements</li>
                <li>Maintain audit trails as required by law or organisational policy</li>
            </ul>
            <p>When you delete your account or your organisation removes you, we will delete or anonymise your personal information, subject to legal retention requirements and our backup systems.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>7. Your Rights</h2>
            <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Access:</strong> Request access to your personal information</li>
                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                <li><strong>Deletion:</strong> Request deletion of your personal information (subject to legal requirements)</li>
                <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
                <li><strong>Objection:</strong> Object to processing of your personal information</li>
                <li><strong>Restriction:</strong> Request restriction of processing in certain circumstances</li>
            </ul>
            <p>To exercise these rights, please contact your organisation administrator or email us at <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a>.</p>
        </section>
        
        <section id="cookies" style="margin-bottom: 3rem;">
            <h2>8. Cookies and Tracking Technologies</h2>
            <p>We use cookies and similar tracking technologies to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Maintain your login session</li>
                <li>Store CSRF tokens for security</li>
                <li>Remember your preferences</li>
                <li>Analyse service usage</li>
            </ul>
            <p>You can control cookies through your browser settings. However, disabling cookies may affect the functionality of the service.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>9. Children's Privacy</h2>
            <p>Our service is intended for use by organisations and their employees. We do not knowingly collect personal information from children under the age of 18. If you believe we have collected information from a child, please contact us immediately.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>10. International Data Transfers</h2>
            <p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have data protection laws that differ from those in your country. We take appropriate safeguards to ensure your information is protected in accordance with this Privacy Policy.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>11. Changes to This Privacy Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Posting the new Privacy Policy on this page</li>
                <li>Updating the "Last updated" date</li>
                <li>Sending an email notification (for significant changes)</li>
            </ul>
            <p>Your continued use of the service after such changes constitutes your acceptance of the updated Privacy Policy.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>12. Contact Us</h2>
            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>
            <p>
                <strong>Email:</strong> <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a><br>
            </p>
            <p>For data protection inquiries, you may also contact your organisation's data protection officer if one has been designated.</p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>13. GDPR Compliance</h2>
            <p>For users in the European Economic Area (EEA), we process your personal information in accordance with the General Data Protection Regulation (GDPR). Our legal bases for processing include:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li><strong>Contract:</strong> Processing necessary to provide the service you have requested</li>
                <li><strong>Legal Obligation:</strong> Processing required to comply with legal obligations</li>
                <li><strong>Legitimate Interests:</strong> Processing necessary for our legitimate business interests (such as security and fraud prevention)</li>
                <li><strong>Consent:</strong> Where we have obtained your explicit consent</li>
            </ul>
        </section>
    </div>
    
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">Return to Home</a>
        <a href="<?php echo url('terms-of-service.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">View Terms of Service</a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


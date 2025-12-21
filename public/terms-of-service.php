<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Terms of Service';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Terms of Service</h1>
    <p style="color: #6b7280; margin-bottom: 2rem;"><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>
    
    <div style="line-height: 1.8;">
        <section style="margin-bottom: 3rem;">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using <?php echo htmlspecialchars(APP_NAME); ?>, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these Terms of Service, please do not use our service.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>2. Description of Service</h2>
            <p><?php echo htmlspecialchars(APP_NAME); ?> provides a digital identification system for organisations, allowing them to create, manage, and verify employee identity cards. The service includes:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Digital ID card generation and management</li>
                <li>QR code and NFC verification systems</li>
                <li>Organisational structure management</li>
                <li>Audit trail and verification logging</li>
                <li>Microsoft Entra ID integration (where configured)</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>3. User Accounts</h2>
            <h3>3.1 Account Creation</h3>
            <p>To use our service, you must register for an account. You agree to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Provide accurate, current, and complete information</li>
                <li>Maintain and update your information to keep it accurate</li>
                <li>Maintain the security of your password</li>
                <li>Accept all responsibility for activities that occur under your account</li>
            </ul>
            
            <h3>3.2 Account Responsibilities</h3>
            <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You must immediately notify us of any unauthorised use of your account.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>4. Organisational Accounts</h2>
            <h3>4.1 Organisation Setup</h3>
            <p>Organisations must be set up by a system administrator before users can register. The organisation administrator is responsible for:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Managing user accounts within their organisation</li>
                <li>Configuring organisational structure and units</li>
                <li>Assigning administrative roles</li>
                <li>Ensuring compliance with these Terms of Service</li>
            </ul>
            
            <h3>4.2 Seat Allocation</h3>
            <p>Each organisation is allocated a maximum number of active user seats. Organisations must not exceed their allocated seat limit. Additional seats may be available subject to separate agreement.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>5. Acceptable Use</h2>
            <p>You agree not to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Use the service for any illegal or unauthorised purpose</li>
                <li>Violate any laws in your jurisdiction</li>
                <li>Infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                <li>Harass, abuse, or harm other users</li>
                <li>Submit false or misleading information</li>
                <li>Interfere with or disrupt the service or servers</li>
                <li>Attempt to gain unauthorised access to any portion of the service</li>
                <li>Use automated systems to access the service without permission</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>6. Data and Privacy</h2>
            <p>Your use of our service is also governed by our <a href="<?php echo url('privacy-policy.php'); ?>">Privacy Policy</a>. We are committed to protecting your data and comply with applicable data protection legislation, including GDPR where applicable.</p>
            <p>You retain ownership of all data you submit to the service. By using the service, you grant us a licence to use, store, and process your data as necessary to provide the service.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>7. Intellectual Property</h2>
            <p>The service and its original content, features, and functionality are owned by <?php echo htmlspecialchars(APP_NAME); ?> and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
            <p>You may not copy, modify, distribute, sell, or lease any part of our service without our prior written consent.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>8. Service Availability</h2>
            <p>We strive to provide a reliable service but do not guarantee that the service will be available at all times. The service may be temporarily unavailable due to:</p>
            <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                <li>Scheduled maintenance</li>
                <li>Technical issues</li>
                <li>Circumstances beyond our reasonable control</li>
            </ul>
            <p>We reserve the right to modify or discontinue the service at any time with reasonable notice.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>9. Termination</h2>
            <h3>9.1 Termination by You</h3>
            <p>You may stop using the service at any time. If you wish to delete your account, please contact your organisation administrator or our support team.</p>
            
            <h3>9.2 Termination by Us</h3>
            <p>We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties.</p>
            
            <h3>9.3 Effect of Termination</h3>
            <p>Upon termination, your right to use the service will immediately cease. We may delete your account and data, subject to our data retention policies and legal obligations.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>10. Disclaimers and Limitations of Liability</h2>
            <h3>10.1 Service Provided "As Is"</h3>
            <p>The service is provided on an "as is" and "as available" basis. We make no warranties, expressed or implied, and hereby disclaim all warranties including, without limitation, implied warranties of merchantability, fitness for a particular purpose, or non-infringement.</p>
            
            <h3>10.2 Limitation of Liability</h3>
            <p>To the maximum extent permitted by law, <?php echo htmlspecialchars(APP_NAME); ?> shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the service.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>11. Indemnification</h2>
            <p>You agree to indemnify and hold harmless <?php echo htmlspecialchars(APP_NAME); ?>, its officers, directors, employees, and agents from and against any claims, liabilities, damages, losses, and expenses, including reasonable legal fees, arising out of or in any way connected with your access to or use of the service or your violation of these Terms of Service.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>12. Changes to Terms</h2>
            <p>We reserve the right to modify these Terms of Service at any time. We will notify users of any material changes by posting the new Terms of Service on this page and updating the "Last updated" date. Your continued use of the service after such modifications constitutes your acceptance of the updated terms.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>13. Governing Law</h2>
            <p>These Terms of Service shall be governed by and construed in accordance with the laws of the jurisdiction in which <?php echo htmlspecialchars(APP_NAME); ?> operates, without regard to its conflict of law provisions.</p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h2>14. Contact Information</h2>
            <p>If you have any questions about these Terms of Service, please contact us at:</p>
            <p>
                <strong>Email:</strong> <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a><br>
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>15. Severability</h2>
            <p>If any provision of these Terms of Service is found to be unenforceable or invalid, that provision shall be limited or eliminated to the minimum extent necessary, and the remaining provisions shall remain in full force and effect.</p>
        </section>
    </div>
    
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">Return to Home</a>
        <a href="<?php echo url('privacy-policy.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">View Privacy Policy</a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


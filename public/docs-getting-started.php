<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Getting Started - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Getting Started</h1>
            <p>Welcome to Digital ID! This guide will help you get started with using the platform.</p>
            
            <h2>What is Digital ID?</h2>
            <p>Digital ID is a secure, verifiable digital identification system designed specifically for social care providers and other organisations. It replaces traditional paper-based ID cards with modern, secure digital alternatives that can be accessed from any device.</p>
            
            <h2>Key Features</h2>
            <ul>
                <li><strong>Multi-tenant Organisation System:</strong> Each organisation has isolated data and settings</li>
                <li><strong>Digital ID Cards:</strong> Secure, verifiable employee identification cards</li>
                <li><strong>Multiple Verification Methods:</strong> Visual, QR code, and NFC verification</li>
                <li><strong>Public Verification:</strong> Service users and third parties can verify employee identity</li>
                <li><strong>Complete Audit Trail:</strong> All verification attempts are logged</li>
                <li><strong>Data Portability:</strong> Export and import employee data in JSON format</li>
                <li><strong>Microsoft Entra Integration:</strong> Optional SSO login and automatic user synchronisation from Microsoft 365</li>
                <li><strong>Check-In Sessions:</strong> Track attendance for fire drills, safety meetings, and emergencies with QR code or manual check-in</li>
                <li><strong>Microsoft 365 Integration:</strong> Automatic synchronisation of check-in data to SharePoint Lists, Power Automate workflows, and Teams notifications</li>
            </ul>
            
            <h2>Creating Your Account</h2>
            <ol class="step-list">
                <li>
                    <strong>Register:</strong> Go to the registration page and create your account with your email address and a strong password.
                </li>
                <li>
                    <strong>Verify Email:</strong> Check your email inbox and click the verification link to activate your account.
                </li>
                <li>
                    <strong>Join or Create Organisation:</strong> During registration, you can either join an existing organisation (if you have an invitation code) or create a new organisation.
                </li>
                <li>
                    <strong>Wait for Employee Profile:</strong> An organisation administrator must create an employee profile for you before you can access your digital ID card.
                </li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Note</h4>
                <p>If you're the first person in your organisation, you'll automatically become the organisation administrator and can create employee profiles for others.</p>
            </div>
            
            <h2>First Steps After Registration</h2>
            <ul>
                <li>Complete your email verification</li>
                <li>Log in to your account</li>
                <li>Wait for your organisation admin to create your employee profile</li>
                <li>Once your profile is created, view your digital ID card</li>
                <li>Familiarise yourself with the verification methods</li>
            </ul>
            
            <h2>Next Steps</h2>
            <p>Once you're set up, explore the following sections:</p>
            <ul>
                <li><a href="<?php echo url('docs-user-guide.php'); ?>">User Guide</a> - Learn how to use your digital ID card</li>
                <li><a href="<?php echo url('docs-pwa.php'); ?>">Install as App</a> - Install Digital ID on your phone for quick access</li>
                <li><a href="<?php echo url('docs-verification.php'); ?>">Verification Methods</a> - Understand how verification works</li>
                <li><a href="<?php echo url('docs-security.php'); ?>">Security</a> - Learn about security features</li>
            </ul>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

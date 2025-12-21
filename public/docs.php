<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Documentation';
include INCLUDES_PATH . '/header.php';

// Get the section from URL parameter
$section = $_GET['section'] ?? 'getting-started';
?>

<style>
.docs-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 3rem;
    margin: 2rem 0;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

.docs-sidebar {
    position: sticky;
    top: 2rem;
    height: fit-content;
    background: #f9fafb;
    border-radius: 0;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.docs-sidebar h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 1rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #06b6d4;
}

.docs-sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.docs-sidebar li {
    margin-bottom: 0.5rem;
}

.docs-sidebar a {
    display: block;
    padding: 0.5rem 0.75rem;
    color: #6b7280;
    text-decoration: none;
    border-radius: 0;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.docs-sidebar a:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.docs-sidebar a.active {
    background: #06b6d4;
    color: white;
    font-weight: 500;
}

.docs-content {
    background: white;
    padding: 2.5rem;
    border-radius: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.docs-content h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #10b981;
}

.docs-content h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.docs-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.docs-content p {
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.docs-content ul, .docs-content ol {
    color: #4b5563;
    line-height: 1.8;
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.docs-content li {
    margin-bottom: 0.5rem;
}

.docs-content code {
    background: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 0;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #dc2626;
}

.docs-content pre {
    background: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0;
    overflow-x: auto;
    margin: 1rem 0;
}

.docs-content pre code {
    background: none;
    color: #f9fafb;
    padding: 0;
}

.docs-content .info-box {
    background: #f0f9ff;
    border-left: 4px solid #06b6d4;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-radius: 0;
}

.docs-content .info-box h4 {
    margin: 0 0 0.5rem 0;
    color: #0e7490;
    font-size: 1rem;
}

.docs-content .info-box p {
    margin: 0;
    color: #0e7490;
}

.docs-content .warning-box {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-radius: 0;
}

.docs-content .warning-box h4 {
    margin: 0 0 0.5rem 0;
    color: #92400e;
    font-size: 1rem;
}

.docs-content .warning-box p {
    margin: 0;
    color: #92400e;
}

.docs-content .success-box {
    background: #d1fae5;
    border-left: 4px solid #10b981;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-radius: 0;
}

.docs-content .success-box h4 {
    margin: 0 0 0.5rem 0;
    color: #065f46;
    font-size: 1rem;
}

.docs-content .success-box p {
    margin: 0;
    color: #065f46;
}

.docs-content .step-list {
    counter-reset: step-counter;
    list-style: none;
    padding-left: 0;
}

.docs-content .step-list li {
    counter-increment: step-counter;
    margin-bottom: 1.5rem;
    padding-left: 3rem;
    position: relative;
}

.docs-content .step-list li::before {
    content: counter(step-counter);
    position: absolute;
    left: 0;
    top: 0;
    background-color: #06b6d4;
    color: white;
    width: 2rem;
    height: 2rem;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.docs-content .step-list li ul {
    list-style-type: disc;
    counter-reset: none;
    margin-left: 1.5rem;
    margin-top: 0.5rem;
}

.docs-content .step-list li ul li {
    counter-increment: none;
    padding-left: 0;
    margin-bottom: 0.5rem;
    position: relative;
}

.docs-content .step-list li ul li::before {
    display: none;
}

.docs-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.docs-content table th {
    background: #f3f4f6;
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #1f2937;
    border-bottom: 2px solid #e5e7eb;
}

.docs-content table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    color: #4b5563;
}

.docs-content table tr:hover {
    background: #f9fafb;
}

@media (max-width: 968px) {
    .docs-container {
        grid-template-columns: 1fr;
    }
    
    .docs-sidebar {
        position: relative;
        top: 0;
    }
    
    .docs-sidebar ul {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.5rem;
    }
}
</style>

<div class="docs-container">
    <aside class="docs-sidebar">
        <h3>Documentation</h3>
        <ul>
            <li><a href="<?php echo url('docs.php?section=getting-started'); ?>" class="<?php echo $section === 'getting-started' ? 'active' : ''; ?>">
                <i class="fas fa-rocket"></i> Getting Started
            </a></li>
            <li><a href="<?php echo url('docs.php?section=user-guide'); ?>" class="<?php echo $section === 'user-guide' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> User Guide
            </a></li>
            <li><a href="<?php echo url('docs.php?section=pwa'); ?>" class="<?php echo $section === 'pwa' ? 'active' : ''; ?>">
                <i class="fas fa-mobile-alt"></i> Install as App
            </a></li>
            <li><a href="<?php echo url('docs.php?section=admin-guide'); ?>" class="<?php echo $section === 'admin-guide' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i> Admin Guide
            </a></li>
            <li><a href="<?php echo url('docs.php?section=verification'); ?>" class="<?php echo $section === 'verification' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Verification
            </a></li>
            <li><a href="<?php echo url('docs.php?section=organisational-structure'); ?>" class="<?php echo $section === 'organisational-structure' ? 'active' : ''; ?>">
                <i class="fas fa-sitemap"></i> Organisational Structure
            </a></li>
            <li><a href="<?php echo url('docs.php?section=import-export'); ?>" class="<?php echo $section === 'import-export' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt"></i> Import & Export
            </a></li>
            <li><a href="<?php echo url('docs.php?section=entra-integration'); ?>" class="<?php echo $section === 'entra-integration' ? 'active' : ''; ?>">
                <i class="fab fa-microsoft"></i> Microsoft Entra
            </a></li>
            <li><a href="<?php echo url('docs.php?section=security'); ?>" class="<?php echo $section === 'security' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> Security
            </a></li>
            <li><a href="<?php echo url('docs.php?section=troubleshooting'); ?>" class="<?php echo $section === 'troubleshooting' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i> Troubleshooting
            </a></li>
        </ul>
    </aside>
    
    <main class="docs-content">
        <?php if ($section === 'getting-started'): ?>
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
                <li><strong>Microsoft Entra Integration:</strong> Optional SSO and employee synchronisation</li>
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
                <li><a href="<?php echo url('docs.php?section=user-guide'); ?>">User Guide</a> - Learn how to use your digital ID card</li>
                <li><a href="<?php echo url('docs.php?section=pwa'); ?>">Install as App</a> - Install Digital ID on your phone for quick access</li>
                <li><a href="<?php echo url('docs.php?section=verification'); ?>">Verification Methods</a> - Understand how verification works</li>
                <li><a href="<?php echo url('docs.php?section=security'); ?>">Security</a> - Learn about security features</li>
            </ul>
            
        <?php elseif ($section === 'pwa'): ?>
            <h1>Install Digital ID as an App</h1>
            <p>Digital ID can be installed as a Progressive Web App (PWA) on your phone, giving you quick access to your ID card directly from your home screen.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> What is a PWA?</h4>
                <p>A Progressive Web App (PWA) is a website that works like a native app. You can install it on your phone without going through an app store, and it provides quick access and offline capabilities.</p>
            </div>
            
            <h2>Benefits of Installing as an App</h2>
            <ul>
                <li><strong>Quick Access:</strong> Tap the app icon on your home screen to instantly open your ID card</li>
                <li><strong>App-like Experience:</strong> Works like a native app with full-screen mode</li>
                <li><strong>Offline Support:</strong> Your ID card can be cached for offline viewing</li>
                <li><strong>No App Store:</strong> Install directly from your browser - no need for Apple App Store or Google Play Store</li>
                <li><strong>Always Up to Date:</strong> Automatically updates when you're online</li>
            </ul>
            
            <h2>How to Install on iPhone/iPad</h2>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Safari</strong> (other browsers don't support PWA installation on iOS)</li>
                <li>Tap the <strong>Share</strong> button <i class="fas fa-share"></i> at the bottom of your screen</li>
                <li>Scroll down in the share menu and tap <strong>"Add to Home Screen"</strong></li>
                <li>Customise the name if desired (default is "Digital ID")</li>
                <li>Tap <strong>"Add"</strong> in the top right corner</li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> iOS Note</h4>
                <p>On iPhone and iPad, PWA installation is only available through Safari. Chrome and other browsers on iOS don't support PWA installation.</p>
            </div>
            
            <h2>How to Install on Android</h2>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Chrome</strong> or <strong>Edge</strong> browser</li>
                <li>Look for an <strong>"Install"</strong> or <strong>"Add to Home screen"</strong> banner at the top of the page</li>
                <li>If you don't see the banner, tap the <strong>menu button</strong> <i class="fas fa-ellipsis-vertical"></i> (three dots) in the top right</li>
                <li>Select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                <li>Review the installation prompt and tap <strong>"Install"</strong> or <strong>"Add"</strong></li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            
            <h2>How to Install on Desktop</h2>
            <p>On desktop computers (Windows, Mac, Linux), you can install Digital ID as an app:</p>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Chrome</strong>, <strong>Edge</strong>, or <strong>Opera</strong></li>
                <li>Look for an <strong>"Install"</strong> icon in the browser's address bar (usually appears as a plus or download icon)</li>
                <li>Click the install icon and confirm the installation</li>
                <li>The app will open in its own window, separate from your browser</li>
            </ol>
            
            <h2>Using the Installed App</h2>
            <p>Once installed, using Digital ID is simple:</p>
            <ol class="step-list">
                <li>Tap the Digital ID icon on your home screen</li>
                <li>The app opens directly to the login page (or your ID card if already logged in)</li>
                <li>Log in with your credentials</li>
                <li>Access your ID card instantly - no need to navigate through menus</li>
            </ol>
            
            <h3>Quick Access Shortcut</h3>
            <p>On supported devices, you can use a shortcut to go directly to your ID card:</p>
            <ol class="step-list">
                <li>Long-press the Digital ID icon on your home screen</li>
                <li>Select <strong>"My ID Card"</strong> from the shortcut menu</li>
                <li>The app opens directly to your ID card page</li>
            </ol>
            
            <h2>Offline Access</h2>
            <p>The installed app can cache your ID card for offline viewing:</p>
            <ul>
                <li>Your ID card data is cached when you view it online</li>
                <li>You can view your cached ID card even without an internet connection</li>
                <li>Note: QR code verification requires an internet connection</li>
                <li>The app will automatically update when you're back online</li>
            </ul>
            
            <h2>Updating the App</h2>
            <p>The PWA automatically updates when you're online:</p>
            <ul>
                <li>No need to manually update - changes are downloaded automatically</li>
                <li>You'll always have the latest version when connected to the internet</li>
                <li>If you notice issues, try closing and reopening the app</li>
            </ul>
            
            <h2>Uninstalling the App</h2>
            <p>If you no longer want the app installed:</p>
            
            <h3>On iPhone/iPad:</h3>
            <ol class="step-list">
                <li>Long-press the app icon on your home screen</li>
                <li>Tap <strong>"Remove App"</strong></li>
                <li>Confirm removal</li>
            </ol>
            
            <h3>On Android:</h3>
            <ol class="step-list">
                <li>Long-press the app icon</li>
                <li>Drag it to <strong>"Uninstall"</strong> or tap the info icon and select <strong>"Uninstall"</strong></li>
                <li>Confirm removal</li>
            </ol>
            
            <h3>On Desktop:</h3>
            <ol class="step-list">
                <li>Open the installed app</li>
                <li>Use the browser menu to find <strong>"Uninstall"</strong> or <strong>"Remove"</strong></li>
                <li>Or uninstall through your system's installed apps list</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Still Works in Browser</h4>
                <p>Uninstalling the app doesn't affect your ability to use Digital ID in your web browser. You can always access it at the website URL.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            
            <h3>Install Prompt Not Appearing</h3>
            <ul>
                <li><strong>iOS:</strong> Make sure you're using Safari, not Chrome or other browsers</li>
                <li><strong>Android:</strong> Ensure you're using Chrome or Edge browser</li>
                <li>Try visiting the site again - the prompt may appear after a few seconds</li>
                <li>Check that you haven't previously dismissed the prompt</li>
            </ul>
            
            <h3>App Not Working Offline</h3>
            <ul>
                <li>Make sure you've viewed your ID card at least once while online</li>
                <li>The app needs to cache the data before offline access works</li>
                <li>Try refreshing the page while online, then test offline</li>
            </ul>
            
            <h3>App Looks Different</h3>
            <ul>
                <li>When installed as a PWA, the app runs in "standalone" mode</li>
                <li>Navigation menus may be hidden for a cleaner, app-like experience</li>
                <li>This is normal and designed for quick access to your ID card</li>
            </ul>
            
        <?php elseif ($section === 'user-guide'): ?>
            <h1>User Guide</h1>
            <p>This guide covers everything you need to know as a user of Digital ID.</p>
            
            <h2>Viewing Your ID Card</h2>
            <p>Once your organisation administrator has created your employee profile, you can view your digital ID card:</p>
            <ol class="step-list">
                <li>Log in to your account</li>
                <li>Click on "My ID Card" in the navigation menu</li>
                <li>Your digital ID card will be displayed with your photo, name, employee reference, and organisation details</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-mobile-alt"></i> Quick Access Tip</h4>
                <p>For faster access, install Digital ID as an app on your phone! See the <a href="<?php echo url('docs.php?section=pwa'); ?>">Install as App</a> guide for instructions. This lets you access your ID card directly from your home screen.</p>
            </div>
            
            <h2>Updating Your ID Card</h2>
            <p>As a user, you cannot directly edit your ID card information. This is managed by your organisation administrator to ensure security and data integrity.</p>
            
            <h3>What You Can Do</h3>
            <ul>
                <li><strong>View your ID card:</strong> Access your digital ID card at any time</li>
                <li><strong>Request updates:</strong> Contact your organisation administrator if you need to update your photo, employee reference, or other details</li>
                <li><strong>Report issues:</strong> If your ID card shows incorrect information, contact your administrator</li>
            </ul>
            
            <h3>What Your Administrator Can Update</h3>
            <p>Your organisation administrator can update:</p>
            <ul>
                <li>Your photo</li>
                <li>Employee reference (if needed)</li>
                <li>ID card expiration date</li>
                <li>Active status</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Why Can't I Edit My Own ID Card?</h4>
                <p>ID cards are managed by administrators to ensure consistency, security, and compliance. This prevents unauthorised changes and maintains the integrity of the verification system.</p>
            </div>
            
            <h2>ID Card Features</h2>
            <h3>Visual Information</h3>
            <ul>
                <li><strong>Photo:</strong> Your profile photo (uploaded by admin)</li>
                <li><strong>Full Name:</strong> Your name as recorded in your employee profile</li>
                <li><strong>Employee Reference:</strong> Your unique employee reference number</li>
                <li><strong>Organisation:</strong> Your organisation name</li>
                <li><strong>Expiration Date:</strong> When your ID card expires (set by admin)</li>
            </ul>
            
            <h3>Verification Methods</h3>
            <p>Your ID card includes three verification methods:</p>
            <ul>
                <li><strong>Visual:</strong> Compare the photo and details with the person</li>
                <li><strong>QR Code:</strong> Scan the QR code for online verification</li>
                <li><strong>NFC:</strong> Tap your device to write the verification token to an NFC tag</li>
            </ul>
            
            <h2>Using Your ID Card</h2>
            <h3>For Bank Transactions</h3>
            <p>When acting on behalf of vulnerable clients at banks:</p>
            <ol>
                <li>Display your digital ID card on your device</li>
                <li>Show the QR code to bank staff</li>
                <li>They can scan it using the verification page</li>
                <li>The system will confirm your identity and employment status</li>
            </ol>
            
            <h3>For Service User Verification</h3>
            <p>Service users, families, and carers can verify your identity:</p>
            <ol>
                <li>Share the verification link or QR code</li>
                <li>They can scan the QR code or visit the verification page</li>
                <li>The system will display your verified identity information</li>
            </ol>
            
            <h3>For Emergency Situations</h3>
            <p>During emergencies or safety checks:</p>
            <ol>
                <li>Quickly access your ID card</li>
                <li>Use visual verification for immediate checks</li>
                <li>Or scan QR code for logged verification</li>
            </ol>
            
            <h2>Exporting Your ID Data</h2>
            <p>If you're moving to a new organisation, you can export your ID card data:</p>
            <ol class="step-list">
                <li>Go to "Import/Export ID Data" in the admin menu</li>
                <li>Click "Export ID Data"</li>
                <li>A JSON file will be downloaded containing your ID card information</li>
                <li>Keep this file safe - you can import it at your new organisation</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Your employee reference and organisation cannot be changed when importing. Only the ID card data structure will be updated.</p>
            </div>
            
            <h2>Account Management</h2>
            <h3>Updating Your Password</h3>
            <p>To change your password:</p>
            <ol>
                <li>Go to your account settings (if available)</li>
                <li>Or contact your organisation administrator</li>
            </ol>
            
            <h3>Email Verification</h3>
            <p>Your email address must be verified before your account is fully activated. If you didn't receive the verification email:</p>
            <ul>
                <li>Check your spam folder</li>
                <li>Request a new verification email</li>
                <li>Contact support if issues persist</li>
            </ul>
            
        <?php elseif ($section === 'admin-guide'): ?>
            <h1>Admin Guide</h1>
            <p>This guide covers administrative functions for organisation administrators.</p>
            
            <h2>Organisation Administration</h2>
            <p>As an organisation administrator, you have access to:</p>
            <ul>
                <li>Employee management</li>
                <li>Organisational structure management</li>
                <li>User management</li>
                <li>Import/export functionality</li>
                <li>Microsoft Entra integration settings</li>
            </ul>
            
            <h2>Managing Employees</h2>
            <h3>Creating Employee Profiles</h3>
            <ol class="step-list">
                <li>Go to "Employees" in the admin menu</li>
                <li>Click "Add New Employee"</li>
                <li>Enter the employee details:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Employee reference (unique identifier)</li>
                        <li>Full name</li>
                        <li>Link to existing user account (by email)</li>
                        <li>Photo upload</li>
                        <li>Expiration date</li>
                    </ul>
                </li>
                <li>Save the employee profile</li>
            </ol>
            
            <h3>Editing Employee Profiles</h3>
            <p>To update an employee's information and ID card:</p>
            <ol class="step-list">
                <li>Go to "Employees" in the admin menu</li>
                <li>Find the employee in the list</li>
                <li>Click "Edit" next to the employee</li>
                <li>Update any of the following:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Employee reference (must be unique within your organisation)</li>
                        <li>Photo (upload a new photo to replace the existing one)</li>
                        <li>Active status (activate or deactivate the employee)</li>
                        <li>ID card expiration date (set when the ID card expires)</li>
                    </ul>
                </li>
                <li>Click "Save Changes"</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Photo Updates</h4>
                <p>When you upload a new photo, the old photo is automatically replaced. Supported formats: JPEG, PNG, GIF. Maximum file size: 5MB.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> ID Card Expiration</h4>
                <p>You can set a custom expiration date for the ID card. If left empty, the system will use the default expiration period. Changing the expiration date does not create a new card - it updates the existing one.</p>
            </div>
            
            <h3>Revoking ID Cards</h3>
            <p>If an employee leaves or a card is compromised:</p>
            <ol>
                <li>Go to "Employees"</li>
                <li>Find the employee</li>
                <li>Click "Revoke ID Card"</li>
                <li>Confirm the revocation</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Revoked ID cards cannot be verified, even with valid tokens. Revocation takes effect immediately.</p>
            </div>
            
            <h2>Managing Users</h2>
            <p>You can view all users in your organisation:</p>
            <ol>
                <li>Go to "Users" in the admin menu</li>
                <li>View the list of all registered users</li>
                <li>See which users have employee profiles linked</li>
            </ol>
            
            <h2>Organisational Structure</h2>
            <p>Create and manage your organisation's hierarchical structure:</p>
            <ul>
                <li>Create organisational units (teams, departments, areas, regions)</li>
                <li>Assign members to units</li>
                <li>Set up unit administrators</li>
                <li>Import structure from CSV or JSON</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs.php?section=organisational-structure'); ?>">Organisational Structure</a> section for detailed information.</p>
            
            <h2>Import and Export</h2>
            <p>Bulk import organisational structure and member assignments:</p>
            <ul>
                <li>Import organisational units from CSV or JSON</li>
                <li>Import member assignments</li>
                <li>Export ID card data</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs.php?section=import-export'); ?>">Import & Export</a> section for detailed information.</p>
            
            <h2>Microsoft Entra Integration</h2>
            <p>Configure Microsoft Entra ID (Azure AD) integration for:</p>
            <ul>
                <li>Single sign-on (SSO)</li>
                <li>Automatic employee synchronisation</li>
                <li>Office 365 integration</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs.php?section=entra-integration'); ?>">Microsoft Entra Integration</a> section for setup instructions.</p>
            
        <?php elseif ($section === 'verification'): ?>
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
            
            <h2>NFC Verification</h2>
            <h3>How It Works</h3>
            <ol>
                <li>Activate NFC on your device</li>
                <li>View your digital ID card</li>
                <li>Tap your device to write the verification token to an NFC tag</li>
                <li>Or use NFC-enabled verification systems</li>
            </ol>
            
            <h3>Use Cases</h3>
            <ul>
                <li>Contactless verification</li>
                <li>Door access systems</li>
                <li>Automated checkpoints</li>
                <li>Meeting attendance</li>
            </ul>
            
            <h3>Security Level</h3>
            <p><strong>High</strong> - Time-limited token (5 minutes), contactless, logged, and validated.</p>
            
            <h2>Public Verification Page</h2>
            <p>The public verification page allows anyone to verify employee identity:</p>
            <ol>
                <li>Visit the verification page</li>
                <li>Scan the QR code or enter the employee reference</li>
                <li>View the verification results</li>
            </ol>
            
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
            <p>Every verification attempt is logged with:</p>
            <ul>
                <li>Timestamp</li>
                <li>Verification method (visual, QR, NFC)</li>
                <li>Result (success or failure)</li>
                <li>Failure reason (if applicable)</li>
                <li>Employee reference</li>
            </ul>
            
            <p>Administrators can review verification logs for compliance and security monitoring.</p>
            
        <?php elseif ($section === 'organisational-structure'): ?>
            <h1>Organisational Structure</h1>
            <p>Manage your organisation's hierarchical structure with teams, departments, areas, and regions.</p>
            
            <h2>Understanding Organisational Units</h2>
            <p>Organisational units allow you to structure your organisation hierarchically:</p>
            <ul>
                <li><strong>Regions:</strong> Top-level geographical or organisational groupings</li>
                <li><strong>Areas:</strong> Sub-divisions within regions</li>
                <li><strong>Teams:</strong> Individual teams or departments</li>
                <li><strong>Custom Types:</strong> Create your own unit types as needed</li>
            </ul>
            
            <h2>Creating Organisational Units</h2>
            <h3>Manual Creation</h3>
            <ol class="step-list">
                <li>Go to "Organisational Structure" in the admin menu</li>
                <li>Click "Add New Unit"</li>
                <li>Enter unit details:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Name (required)</li>
                        <li>Unit type (optional)</li>
                        <li>Parent unit (for hierarchical structure)</li>
                        <li>Description (optional)</li>
                    </ul>
                </li>
                <li>Save the unit</li>
            </ol>
            
            <h3>Bulk Import</h3>
            <p>Import your organisational structure from CSV or JSON files. See the <a href="<?php echo url('docs.php?section=import-export'); ?>">Import & Export</a> section for details.</p>
            
            <h2>Assigning Members</h2>
            <h3>Adding Members to Units</h3>
            <ol>
                <li>Go to the organisational unit</li>
                <li>Click "Members"</li>
                <li>Click "Add Member"</li>
                <li>Select a user by email</li>
                <li>Choose a role (member, lead, etc.)</li>
                <li>Save the assignment</li>
            </ol>
            
            <h3>Member Roles</h3>
            <ul>
                <li><strong>Member:</strong> Standard member of the unit</li>
                <li><strong>Lead:</strong> Unit leader or manager</li>
                <li><strong>Custom Roles:</strong> Create custom roles as needed</li>
            </ul>
            
            <h2>Unit Administrators</h2>
            <p>You can assign unit administrators who have specific permissions for their unit:</p>
            <ul>
                <li>Manage members within their unit</li>
                <li>Manage child units (if permitted)</li>
                <li>View unit-specific information</li>
            </ul>
            
            <h2>Hierarchical Structure</h2>
            <p>Organisational units can be nested to create a hierarchical structure:</p>
            <pre><code>North Region
  └── Newcastle Area
      ├── Newcastle Team
      └── Newcastle Admin
  └── Leeds Area
      └── Leeds Team
South Region
  └── London Area
      └── London Team</code></pre>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Tip</h4>
                <p>When creating units, start with top-level units first, then add child units. This ensures parent relationships are properly established.</p>
            </div>
            
            <h2>Importing Structure</h2>
            <p>You can import your entire organisational structure from CSV or JSON:</p>
            <ul>
                <li>CSV format for simple unit creation</li>
                <li>JSON format for hierarchical structures with members</li>
                <li>Bulk member assignment via CSV</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs.php?section=import-export'); ?>">Import & Export</a> section for file formats and examples.</p>
            
        <?php elseif ($section === 'import-export'): ?>
            <h1>Import & Export</h1>
            <p>Import and export data to streamline your organisation's setup and data portability.</p>
            
            <h2>Importing Organisational Structure</h2>
            <p>You can import your organisational structure from CSV or JSON files.</p>
            
            <h3>CSV Format for Units</h3>
            <p>Required columns: <code>name</code> (required), <code>unit_type</code>, <code>parent</code>, <code>description</code></p>
            <pre><code>name,unit_type,parent,description
North Region,region,,Regional grouping
Newcastle Area,area,North Region,Newcastle area
Newcastle Team,team,Newcastle Area,Acute care team</code></pre>
            
            <h3>JSON Format for Units</h3>
            <p>Hierarchical structure with nested units and members:</p>
            <pre><code>{
  "units": [
    {
      "name": "North Region",
      "unit_type": "region",
      "description": "Regional grouping",
      "children": [
        {
          "name": "Newcastle Area",
          "unit_type": "area",
          "members": [
            {"email": "manager@example.com", "role": "lead"}
          ],
          "children": [
            {
              "name": "Newcastle Team",
              "unit_type": "team",
              "members": [
                {"email": "john@example.com", "role": "member"}
              ]
            }
          ]
        }
      ]
    }
  ]
}</code></pre>
            
            <h2>Importing Member Assignments</h2>
            <h3>CSV Format for Members</h3>
            <p>Required columns: <code>email</code> (required), <code>unit_name</code> (required), <code>role</code></p>
            <pre><code>email,unit_name,role
john@example.com,Newcastle Team,member
jane@example.com,Newcastle Team,lead</code></pre>
            
            <h3>JSON Format for Members</h3>
            <pre><code>{
  "assignments": [
    {
      "email": "john@example.com",
      "unit_name": "Newcastle Team",
      "role": "member"
    },
    {
      "email": "jane@example.com",
      "unit_name": "Newcastle Team",
      "role": "lead"
    }
  ]
}</code></pre>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Users must already exist in your organisation before you can assign them to units. Email addresses must match exactly. Unit names must match existing units exactly (case-sensitive).</p>
            </div>
            
            <h2>Downloading Example Files</h2>
            <p>You can download example CSV and JSON files from the import page:</p>
            <ol>
                <li>Go to "Organisational Structure" → "Import"</li>
                <li>Click the download buttons for example files</li>
                <li>Use these as templates for your own imports</li>
            </ol>
            
            <h2>Exporting ID Card Data</h2>
            <p>Employees can export their ID card data when moving organisations:</p>
            <ol>
                <li>Go to "Import/Export ID Data"</li>
                <li>Click "Export ID Data"</li>
                <li>A JSON file will be downloaded</li>
                <li>Keep this file safe for import at the new organisation</li>
            </ol>
            
            <h2>Importing ID Card Data</h2>
            <p>When joining a new organisation, employees can import their previous ID card data:</p>
            <ol>
                <li>Go to "Import/Export ID Data"</li>
                <li>Upload the JSON file exported from the previous organisation</li>
                <li>The ID card data structure will be updated</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Note</h4>
                <p>Employee reference and organisation cannot be changed when importing. Only the ID card data structure will be updated.</p>
            </div>
            
            <h2>File Size Limits</h2>
            <ul>
                <li>Maximum file size: 2MB</li>
                <li>Supported formats: CSV, JSON</li>
                <li>File encoding: UTF-8 recommended</li>
            </ul>
            
        <?php elseif ($section === 'entra-integration'): ?>
            <h1>Microsoft Entra Integration</h1>
            <p>Integrate Digital ID with Microsoft Entra ID (Azure AD) for single sign-on and employee synchronisation.</p>
            
            <h2>Overview</h2>
            <p>Microsoft Entra integration provides:</p>
            <ul>
                <li><strong>Single Sign-On (SSO):</strong> Users can log in with their Microsoft 365 accounts</li>
                <li><strong>Employee Synchronisation:</strong> Automatically sync employees from Microsoft 365</li>
                <li><strong>Seamless Integration:</strong> Works with existing Office 365 infrastructure</li>
            </ul>
            
            <h2>Prerequisites</h2>
            <ul>
                <li>Microsoft 365 subscription with Azure AD</li>
                <li>Admin access to Azure AD</li>
                <li>Ability to register applications in Azure AD</li>
            </ul>
            
            <h2>Setting Up Entra Integration</h2>
            <h3>Step 1: Register Application in Azure AD</h3>
            <ol class="step-list">
                <li>Log in to the Azure Portal</li>
                <li>Go to "Azure Active Directory" → "App registrations"</li>
                <li>Click "New registration"</li>
                <li>Enter application name: "Digital ID"</li>
                <li>Set redirect URI: <code><?php echo APP_URL; ?>/entra-login.php</code></li>
                <li>Click "Register"</li>
            </ol>
            
            <h3>Step 2: Configure API Permissions</h3>
            <ol>
                <li>In your app registration, go to "API permissions"</li>
                <li>Click "Add a permission"</li>
                <li>Select "Microsoft Graph"</li>
                <li>Add the following permissions:
                    <ul>
                        <li><code>User.Read</code> - Read user profile</li>
                        <li><code>openid</code> - Sign in and read user profile</li>
                        <li><code>profile</code> - View user's basic profile</li>
                        <li><code>email</code> - View user's email address</li>
                    </ul>
                </li>
                <li>Click "Add permissions"</li>
            </ol>
            
            <h3>Step 3: Create Client Secret</h3>
            <ol>
                <li>Go to "Certificates & secrets"</li>
                <li>Click "New client secret"</li>
                <li>Enter description and expiration</li>
                <li>Click "Add"</li>
                <li><strong>Copy the secret value immediately</strong> - you won't be able to see it again</li>
            </ol>
            
            <h3>Step 4: Configure in Digital ID</h3>
            <ol>
                <li>Log in as organisation administrator</li>
                <li>Go to "Entra Settings" in the admin menu</li>
                <li>Enter your Tenant ID (found in Azure AD overview)</li>
                <li>Enter your Client ID (Application ID from app registration)</li>
                <li>Enter your Client Secret (from Step 3)</li>
                <li>Enable the integration</li>
                <li>Save settings</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Security</h4>
                <p>Keep your Client Secret secure. Never share it or commit it to version control. Store it in your environment variables.</p>
            </div>
            
            <h2>Using Entra Login</h2>
            <p>Once configured, users can log in with Microsoft:</p>
            <ol>
                <li>Go to the login page</li>
                <li>Click "Sign in with Microsoft"</li>
                <li>Authenticate with Microsoft 365 credentials</li>
                <li>You'll be redirected back to Digital ID</li>
            </ol>
            
            <h2>Employee Synchronisation</h2>
            <p>If enabled, the system can automatically synchronise employees from Microsoft 365:</p>
            <ul>
                <li>Employees are matched by email address</li>
                <li>New employees can be automatically created</li>
                <li>Existing employees are updated</li>
            </ul>
            
            <h2>Troubleshooting</h2>
            <h3>Common Issues</h3>
            <ul>
                <li><strong>Redirect URI mismatch:</strong> Ensure the redirect URI in Azure AD matches exactly</li>
                <li><strong>Permissions not granted:</strong> Admin consent may be required for API permissions</li>
                <li><strong>Invalid client secret:</strong> Check that the secret hasn't expired</li>
                <li><strong>Tenant ID incorrect:</strong> Verify the Tenant ID in Azure AD overview</li>
            </ul>
            
        <?php elseif ($section === 'security'): ?>
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
            
        <?php elseif ($section === 'troubleshooting'): ?>
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
            
        <?php else: ?>
            <h1>Documentation</h1>
            <p>Select a section from the sidebar to view documentation.</p>
        <?php endif; ?>
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


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
            <li><a href="<?php echo url('docs.php?section=check-in-sessions'); ?>" class="<?php echo $section === 'check-in-sessions' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-check"></i> Check-In Sessions
            </a></li>
            <li><a href="<?php echo url('docs.php?section=mcp-integration'); ?>" class="<?php echo $section === 'mcp-integration' ? 'active' : ''; ?>">
                <i class="fas fa-robot"></i> AI Integration (MCP)
            </a></li>
            <li><a href="<?php echo url('docs.php?section=security'); ?>" class="<?php echo $section === 'security' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> Security
            </a></li>
            <li><a href="<?php echo url('docs.php?section=user-stories'); ?>" class="<?php echo $section === 'user-stories' ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i> User Stories
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
                <li><strong>Important:</strong> QR code verification requires the person scanning the QR code to have internet access. When someone scans your QR code, their device needs to connect to the verification server to validate the token.</li>
                <li>QR code tokens expire after 5 minutes. If your token expires while offline, you'll need internet access to refresh your ID card page and get a new QR code.</li>
                <li>The app will automatically update when you're back online</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Who Needs Internet for QR Verification?</h4>
                <p><strong>The person scanning/verifying your QR code needs internet access</strong> to send the verification request to the server. You (the ID card owner) don't need internet at the moment of verification - you just need to have your QR code displayed. However, if your QR code token expires (after 5 minutes), you'll need internet to refresh your ID card page and get a new QR code.</p>
            </div>
            
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
                <li><strong>Upload your photo:</strong> Upload a new photo for your ID card (requires administrator approval)</li>
                <li><strong>Request updates:</strong> Contact your organisation administrator if you need to update your employee reference or other details</li>
                <li><strong>Report issues:</strong> If your ID card shows incorrect information, contact your administrator</li>
            </ul>
            
            <h3>Uploading Your Photo</h3>
            <p>You can upload your own photo for your digital ID card:</p>
            <ol>
                <li>Go to your ID card page</li>
                <li>Click "Upload Photo" or "Change Photo"</li>
                <li>Select a photo that meets the <a href="<?php echo url('photo-guidelines.php'); ?>">photo guidelines</a></li>
                <li>Upload the photo for administrator review</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Photo Approval Workflow</h4>
                <p>
                    When you upload a new photo, it needs to be approved by an administrator before it appears on your ID card. 
                    <strong>Your current approved photo will remain visible on your ID card until the new photo is approved.</strong> 
                    This ensures your ID card remains usable throughout the approval process. You'll see a notification that a new photo 
                    is awaiting approval, but your existing photo will continue to be displayed.
                </p>
            </div>
            
            <h3>What Your Administrator Can Update</h3>
            <p>Your organisation administrator can update:</p>
            <ul>
                <li>Your photo (or approve/reject photos you upload)</li>
                <li>Employee reference (if needed)</li>
                <li>ID card expiration date</li>
                <li>Active status</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Why Can't I Edit My Own ID Card?</h4>
                <p>ID cards are managed by administrators to ensure consistency, security, and compliance. This prevents unauthorised changes and maintains the integrity of the verification system. However, you can upload your own photo, which will be reviewed by an administrator before being approved.</p>
            </div>
            
            <h2>ID Card Features</h2>
            <h3>Visual Information</h3>
            <ul>
                <li><strong>Photo:</strong> Your profile photo (you can upload your own, subject to administrator approval)</li>
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
                <p>
                    When an employee uploads a new photo, it requires administrator approval before it appears on their ID card. 
                    <strong>The current approved photo remains visible on the ID card until the new photo is approved.</strong> 
                    This ensures the ID card remains usable throughout the approval process. Supported formats: JPEG, PNG. Maximum file size: 5MB.
                </p>
                <p style="margin-top: 0.75rem;">
                    To review and approve/reject pending photos, go to "Photos" in the Organisation menu. You'll see all employees 
                    who have uploaded new photos waiting for approval.
                </p>
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
                        <li><strong>For SSO Login (Delegated Permissions):</strong>
                            <ul>
                                <li><code>User.Read</code> - Read user profile</li>
                                <li><code>openid</code> - Sign in and read user profile</li>
                                <li><code>profile</code> - View user's basic profile</li>
                                <li><code>email</code> - View user's email address</li>
                            </ul>
                        </li>
                        <li><strong>For User Synchronisation (Application Permissions):</strong>
                            <ul>
                                <li><code>User.Read.All</code> - Read all users' profiles (requires admin consent)</li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>Click "Add permissions"</li>
                <li><strong>Important:</strong> For <code>User.Read.All</code>, click "Grant admin consent" to enable user synchronisation</li>
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
            
            <h2>User Synchronisation</h2>
            <p>When Microsoft Entra integration is enabled, organisation administrators can synchronise users from Microsoft 365:</p>
            <ul>
                <li><strong>Bulk Import:</strong> Fetch all active users from Microsoft Entra ID</li>
                <li><strong>Automatic Matching:</strong> Users are matched by email address</li>
                <li><strong>Create or Update:</strong> New users are created, existing users are updated</li>
                <li><strong>Employee Profiles:</strong> Optionally create employee profiles for users with employee IDs</li>
                <li><strong>Same Process:</strong> Uses the same import logic as CSV/JSON import for consistency</li>
            </ul>
            
            <h3>How to Sync Users</h3>
            <ol>
                <li>Go to Organisation → Microsoft 365 SSO Settings</li>
                <li>Ensure Entra integration is enabled</li>
                <li>Click "Sync Users from Microsoft Entra ID"</li>
                <li>Optionally check "Also create employee profiles" if users have employee IDs</li>
                <li>Review the sync results and any warnings</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Required Permissions</h4>
                <p>For user synchronisation to work, your Azure AD app registration needs <strong>User.Read.All</strong> application permission (not delegated). Admin consent is required for this permission.</p>
            </div>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <p>User synchronisation automates the import process by pulling data directly from Microsoft Entra ID, eliminating the need to export CSV files manually. It uses the same reliable import system as manual CSV/JSON imports.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            <h3>Common Issues</h3>
            <ul>
                <li><strong>Redirect URI mismatch:</strong> Ensure the redirect URI in Azure AD matches exactly</li>
                <li><strong>Permissions not granted:</strong> Admin consent may be required for API permissions</li>
                <li><strong>Invalid client secret:</strong> Check that the secret hasn't expired</li>
                <li><strong>Tenant ID incorrect:</strong> Verify the Tenant ID in Azure AD overview</li>
            </ul>
            
        <?php elseif ($section === 'mcp-integration'): ?>
            <h1>AI Integration (MCP Server)</h1>
            <p>Digital ID includes a Model Context Protocol (MCP) server that allows AI assistants like Cursor or Claude Desktop to interact with your Digital ID system directly.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Wide Access Only</h4>
                <p><strong>The MCP server only supports organisation-wide access for security.</strong> You must configure <code>ORGANISATION_ID</code> in your environment, and the server will automatically restrict all queries to that organisation only.</p>
                <p style="margin-top: 0.75rem;">This ensures:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>Each MCP server instance can only access one organisation's data</li>
                    <li>No risk of cross-organisation data access</li>
                    <li>Safe for multi-tenant deployments</li>
                    <li>Organisation-level security by design</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Access Control:</strong> The MCP server requires database credentials and a configured <code>ORGANISATION_ID</code>. Once configured, it can only access data from the specified organisation. This makes it suitable for trusted administrators who need to query their organisation's data.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> What is MCP?</h4>
                <p>The Model Context Protocol (MCP) is a standard protocol that allows AI assistants to access external data and perform actions through secure, standardised interfaces. The Digital ID MCP server provides AI assistants with tools to query employee data, verify ID cards, view verification logs, and perform administrative tasks.</p>
            </div>
            
            <h2>Overview</h2>
            <p>The MCP server acts as a bridge between AI assistants and your Digital ID database, allowing natural language queries and automated tasks:</p>
            <ul>
                <li><strong>Employee Lookups:</strong> Find employee information by ID, email, or reference number</li>
                <li><strong>ID Card Verification:</strong> Verify ID cards using QR codes or NFC tokens</li>
                <li><strong>Log Analysis:</strong> Query verification logs with filtering options</li>
                <li><strong>Employee Management:</strong> List employees, view pending photo approvals, and manage ID cards</li>
                <li><strong>Organisation Data:</strong> Access organisation information and structure</li>
            </ul>
            
            <h2>How It Works</h2>
            <p>The MCP server is a TypeScript/Node.js application that:</p>
            <ol class="step-list">
                <li>
                    <strong>Connects to Your Database:</strong> Uses MySQL connection to access Digital ID data securely
                </li>
                <li>
                    <strong>Exposes Tools:</strong> Provides standardised tools that AI assistants can call (like functions)
                </li>
                <li>
                    <strong>Communicates via JSON-RPC:</strong> Uses the Model Context Protocol standard over stdio (standard input/output)
                </li>
                <li>
                    <strong>Returns Structured Data:</strong> Formats database results as JSON for AI assistants to process
                </li>
            </ol>
            
            <h2>Available Tools</h2>
            <p>The MCP server provides the following tools that AI assistants can use:</p>
            
            <h3>Employee Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>get_employee</code></td>
                        <td>Get employee information</td>
                        <td>employee_id, email, employee_reference, organisation_id</td>
                    </tr>
                    <tr>
                        <td><code>list_employees</code></td>
                        <td>List employees with filters</td>
                        <td>organisation_id, is_active, has_photo, limit</td>
                    </tr>
                    <tr>
                        <td><code>get_pending_photos</code></td>
                        <td>Get employees with pending photo approvals</td>
                        <td>organisation_id</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Verification</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>verify_id_card</code></td>
                        <td>Verify an ID card token</td>
                        <td>token, verification_type (qr/nfc/ble)</td>
                    </tr>
                    <tr>
                        <td><code>get_verification_logs</code></td>
                        <td>Get verification logs with filters</td>
                        <td>employee_id, organisation_id, verification_type, result, start_date, end_date, limit</td>
                    </tr>
                    <tr>
                        <td><code>revoke_id_card</code></td>
                        <td>Revoke an employee's ID card</td>
                        <td>employee_id, reason</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Organisation</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>get_organisation</code></td>
                        <td>Get organisation information</td>
                        <td>organisation_id, domain</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Available Resources</h2>
            <p>In addition to tools, the MCP server provides resources that AI assistants can read:</p>
            <ul>
                <li><strong><code>digital-id://employees</code>:</strong> List of all employees (limited to 1000 records)</li>
                <li><strong><code>digital-id://organisations</code>:</strong> List of all organisations</li>
            </ul>
            
            <h2>Setting Up the MCP Server</h2>
            <p>To use the MCP server with an AI assistant like Cursor or Claude Desktop, follow these steps:</p>
            
            <h3>Prerequisites</h3>
            <ul>
                <li>Node.js 18+ installed on your system</li>
                <li>Access to the Digital ID database</li>
                <li>Database credentials (host, database name, username, password)</li>
                <li>An AI assistant that supports MCP (Cursor, Claude Desktop, etc.)</li>
            </ul>
            
            <h3>Step 1: Install Dependencies</h3>
            <ol class="step-list">
                <li>Navigate to the MCP server directory:
                    <pre><code>cd mcp-server</code></pre>
                </li>
                <li>Install Node.js dependencies:
                    <pre><code>npm install</code></pre>
                </li>
            </ol>
            
            <h3>Step 2: Configure Environment</h3>
            <ol class="step-list">
                <li>Create a <code>.env</code> file in the <code>mcp-server</code> directory</li>
                <li>Add your database credentials and organisation ID:
                    <pre><code>DB_HOST=localhost
DB_NAME=digital_ids
DB_USER=your_db_user
DB_PASS=your_db_password
ORGANISATION_ID=1</code></pre>
                </li>
                <li>Replace the values with your actual database credentials</li>
                <li><strong>Required:</strong> Set <code>ORGANISATION_ID</code> to the ID of the organisation whose data you want to access. The MCP server only supports organisation-wide access for security - all queries will be automatically filtered to this organisation.</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Wide Access Required</h4>
                <p>The MCP server <strong>requires</strong> <code>ORGANISATION_ID</code> to be set in your environment configuration. This ensures the server can only access data from that specific organisation, providing essential security for multi-tenant deployments.</p>
                <p style="margin-top: 0.75rem;">When <code>ORGANISATION_ID</code> is configured:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>All employee queries are automatically filtered to the specified organisation</li>
                    <li>Verification logs only show data from that organisation</li>
                    <li>Only employees from that organisation can be revoked</li>
                    <li>Resources (employees, organisations) only show data from that organisation</li>
                    <li>The server will fail to start if <code>ORGANISATION_ID</code> is not set</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Example:</strong> If you set <code>ORGANISATION_ID=5</code>, the MCP server will only be able to access data from organisation ID 5, even if the database contains data from multiple organisations.</p>
                <p style="margin-top: 0.75rem;"><strong>Multi-Organisation Deployments:</strong> If you need to access data from multiple organisations, you must set up separate MCP server instances, each with its own <code>ORGANISATION_ID</code> configuration.</p>
            </div>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Security Warning</h4>
                <p>Never commit the <code>.env</code> file to version control. It contains sensitive database credentials. The <code>.gitignore</code> file is already configured to exclude it.</p>
            </div>
            
            <h3>Step 3: Build the Server</h3>
            <ol class="step-list">
                <li>Compile the TypeScript code:
                    <pre><code>npm run build</code></pre>
                </li>
                <li>Verify the build succeeded - you should see a <code>dist/index.js</code> file</li>
            </ol>
            
            <h3>Step 4: Test the Server</h3>
            <ol class="step-list">
                <li>Run the server to verify it works:
                    <pre><code>npm start</code></pre>
                </li>
                <li>You should see "Digital ID MCP Server running on stdio"</li>
                <li>Press Ctrl+C to stop the server</li>
            </ol>
            
            <h3>Step 5: Configure Your AI Assistant</h3>
            <p>The configuration depends on which AI assistant you're using:</p>
            
            <h4>For Cursor</h4>
            <ol class="step-list">
                <li>Open Cursor settings</li>
                <li>Navigate to MCP settings (usually in Settings → Features → MCP)</li>
                <li>Add the following configuration to your MCP settings file:
                    <pre><code>{
  "mcpServers": {
    "digital-id": {
      "command": "node",
      "args": ["/absolute/path/to/digital-id/mcp-server/dist/index.js"],
      "env": {
        "DB_HOST": "localhost",
        "DB_NAME": "digital_ids",
        "DB_USER": "your_db_user",
        "DB_PASS": "your_db_password",
        "ORGANISATION_ID": "1"
      }
    }
  }
}</code></pre>
                </li>
                <li><strong>Important:</strong> Replace <code>/absolute/path/to/digital-id</code> with the actual absolute path to your project directory</li>
                <li>Replace database credentials with your actual values</li>
                <li><strong>Required:</strong> Add <code>"ORGANISATION_ID": "1"</code> with your organisation ID (replace 1 with your actual organisation ID). The MCP server requires this to be set.</li>
                <li>Restart Cursor for changes to take effect</li>
            </ol>
            
            <h4>For Claude Desktop</h4>
            <ol class="step-list">
                <li>Open Claude Desktop settings</li>
                <li>Navigate to MCP settings (usually in Settings → Developer → MCP)</li>
                <li>Add the same configuration as shown for Cursor above</li>
                <li>Use the absolute path to <code>dist/index.js</code></li>
                <li>Restart Claude Desktop</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Finding Your Path</h4>
                <p>On macOS/Linux, you can find the absolute path by running <code>pwd</code> in the terminal while in your project directory. On Windows, use the full path including the drive letter (e.g., <code>C:\Users\YourName\digital-id\mcp-server\dist\index.js</code>).</p>
            </div>
            
            <h2>Using the MCP Server</h2>
            <p>Once configured, you can interact with your Digital ID system using natural language in your AI assistant:</p>
            
            <h3>Example Queries</h3>
            <ul>
                <li>"Get employee information for john.doe@example.com"</li>
                <li>"Show me verification logs for organisation ID 1 from last month"</li>
                <li>"List all employees with pending photo approvals"</li>
                <li>"Verify this ID card token: abc123..."</li>
                <li>"How many active employees are in organisation 2?"</li>
                <li>"Show me all failed verifications from yesterday"</li>
                <li>"Revoke the ID card for employee ID 5, reason: employee left"</li>
            </ul>
            
            <h2>Architecture</h2>
            <p>Understanding how the MCP server works helps with troubleshooting and customisation:</p>
            
            <h3>Communication Flow</h3>
            <ol>
                <li><strong>AI Assistant:</strong> Receives user query in natural language</li>
                <li><strong>AI Assistant:</strong> Decides which MCP tool to call based on the query</li>
                <li><strong>MCP Server:</strong> Receives tool call request via JSON-RPC over stdio</li>
                <li><strong>MCP Server:</strong> Executes database query using provided parameters</li>
                <li><strong>MCP Server:</strong> Formats results as JSON</li>
                <li><strong>AI Assistant:</strong> Receives structured data and presents it to the user</li>
            </ol>
            
            <h3>Database Connection</h3>
            <p>The server maintains a single MySQL connection that is reused for all requests. The connection is created on first use and persists for the lifetime of the server process.</p>
            
            <h3>Error Handling</h3>
            <p>All errors are caught and returned in a standardised format that AI assistants can understand. Database errors, validation errors, and missing data are all handled gracefully.</p>
            
            <h2>Development Mode</h2>
            <p>For development, you can use watch mode to automatically rebuild when code changes:</p>
            <pre><code>npm run dev</code></pre>
            <p>This runs TypeScript compiler in watch mode, automatically recompiling when you save changes to <code>src/index.ts</code>.</p>
            
            <h2>Adding New Tools</h2>
            <p>To add custom functionality to the MCP server:</p>
            <ol class="step-list">
                <li>
                    <strong>Add Tool Definition:</strong> Add a new tool object to the <code>ListToolsRequestSchema</code> handler in <code>src/index.ts</code>
                    <ul>
                        <li>Define the tool name, description, and input schema</li>
                        <li>Specify required and optional parameters</li>
                    </ul>
                </li>
                <li>
                    <strong>Implement Tool Handler:</strong> Add a case in the <code>CallToolRequestSchema</code> handler switch statement
                    <ul>
                        <li>Extract parameters from the request</li>
                        <li>Execute database queries</li>
                        <li>Return formatted JSON results</li>
                    </ul>
                </li>
                <li>
                    <strong>Rebuild:</strong> Run <code>npm run build</code> to compile changes
                </li>
                <li>
                    <strong>Restart:</strong> Restart your AI assistant to load the updated MCP server
                </li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Code Structure</h4>
                <p>The MCP server code is well-organised in <code>src/index.ts</code>. Tool definitions are at the top in the <code>ListToolsRequestSchema</code> handler, and implementations are in the <code>CallToolRequestSchema</code> handler. Follow the existing patterns for consistency.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            
            <h3>Server Won't Start</h3>
            <ul>
                <li>Check that Node.js 18+ is installed: <code>node --version</code></li>
                <li>Verify database credentials in <code>.env</code> file</li>
                <li>Ensure the database server is running and accessible</li>
                <li>Check network connectivity if using a remote database</li>
            </ul>
            
            <h3>"Cannot Find Module" Errors</h3>
            <ul>
                <li>Run <code>npm install</code> again to ensure dependencies are installed</li>
                <li>Verify you're in the <code>mcp-server</code> directory</li>
                <li>Check that <code>dist/index.js</code> exists after building</li>
                <li>Ensure <code>node_modules</code> directory exists</li>
            </ul>
            
            <h3>Database Connection Errors</h3>
            <ul>
                <li>Verify database credentials are correct</li>
                <li>Check that the database server is running</li>
                <li>Ensure network access to the database (if remote)</li>
                <li>Verify database name, username, and password</li>
                <li>Check firewall settings if connecting remotely</li>
            </ul>
            
            <h3>Tools Not Appearing in AI Assistant</h3>
            <ul>
                <li>Restart your AI assistant application completely</li>
                <li>Check the MCP configuration syntax is valid JSON</li>
                <li>Verify the path to <code>dist/index.js</code> is correct and absolute</li>
                <li>Check AI assistant logs for MCP connection errors</li>
                <li>Ensure the MCP server starts without errors when tested manually</li>
            </ul>
            
            <h3>Tools Return Errors</h3>
            <ul>
                <li>Check database schema matches what the code expects</li>
                <li>Verify table names and column names are correct</li>
                <li>Check database user has necessary permissions</li>
                <li>Review error messages in AI assistant output for details</li>
            </ul>
            
            <h2>Security Considerations</h2>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Level Security</h4>
                <p><strong>The MCP server enforces organisation-level access control.</strong> When configured with <code>ORGANISATION_ID</code>, the server automatically restricts all queries to that organisation only, ensuring:</p>
                <ul style="margin-top: 0.75rem;">
                    <li><strong>Single organisation access</strong> - Can only access the configured organisation's data</li>
                    <li><strong>No cross-organisation access</strong> - Cannot view other organisations' data</li>
                    <li><strong>Automatic filtering</strong> - All queries are filtered by organisation ID</li>
                    <li><strong>Safe for multi-tenant</strong> - Each organisation can have its own MCP server instance</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Security Model:</strong> The MCP server requires database credentials and a configured <code>ORGANISATION_ID</code>. Once configured, it can only access data from the specified organisation. This provides organisation-level isolation for multi-tenant deployments.</p>
            </div>
            
            <h3>Security Best Practices</h3>
            <p>To secure the MCP server, follow these recommendations:</p>
            <ol class="step-list">
                <li>
                    <strong>Configure Organisation ID (Required):</strong> Set <code>ORGANISATION_ID</code> in your environment - this is mandatory
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The MCP server requires <code>ORGANISATION_ID</code> to be set - it will not start without it</li>
                        <li>Automatically filters all queries to the specified organisation</li>
                        <li>Prevents access to other organisations' data</li>
                        <li>Essential for multi-tenant deployments</li>
                        <li>Example: Add <code>ORGANISATION_ID=1</code> to your <code>.env</code> file</li>
                    </ul>
                </li>
                <li>
                    <strong>Use Read-Only Database Users:</strong> For production, create a read-only database user that can only SELECT data, not modify it
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>This prevents users from modifying data via the MCP server</li>
                        <li>They can still query all data, but cannot revoke cards or make changes</li>
                        <li>Example MySQL: <code>GRANT SELECT ON digital_ids.* TO 'mcp_readonly'@'localhost';</code></li>
                    </ul>
                </li>
                <li>
                    <strong>Restrict Database Access:</strong> Only provide database credentials to trusted administrators
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The MCP server is designed for administrators who already have database access</li>
                        <li>Do not share database credentials with end users</li>
                        <li>Use separate credentials for MCP server if possible</li>
                    </ul>
                </li>
                <li>
                    <strong>Protect Credentials:</strong> Never commit <code>.env</code> files or database credentials to version control
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The <code>.gitignore</code> file excludes <code>.env</code> files</li>
                        <li>Use secure methods to share credentials (password managers, secure channels)</li>
                    </ul>
                </li>
                <li>
                    <strong>Network Security:</strong> The server runs locally via stdio (not HTTP), but still ensure network security
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>MCP servers communicate via stdio, so they're not directly accessible over the network</li>
                        <li>However, if database is remote, secure the database connection</li>
                        <li>Use SSH tunnels or VPNs for remote database access</li>
                    </ul>
                </li>
                <li>
                    <strong>Audit Access:</strong> Monitor who has access to database credentials
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Keep records of who has database credentials</li>
                        <li>Review database access logs regularly</li>
                        <li>Rotate credentials periodically</li>
                    </ul>
                </li>
                <li>
                    <strong>Multi-Tenant Deployments:</strong> The MCP server is designed for organisation-wide access
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li><code>ORGANISATION_ID</code> is required - the server will not start without it</li>
                        <li>Each organisation must have its own MCP server instance with its own <code>ORGANISATION_ID</code></li>
                        <li>This ensures complete isolation between organisations</li>
                        <li>No risk of cross-organisation data access</li>
                    </ul>
                </li>
            </ol>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Organisation-Wide Access Enforced</h4>
                <p>The MCP server enforces organisation-wide access by requiring <code>ORGANISATION_ID</code> to be set. This ensures all queries are automatically filtered to the specified organisation, providing essential security for multi-tenant deployments. This feature is described in detail in the setup instructions above.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Future Improvements</h4>
                <p>Potential security enhancements for future versions could include:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>User-based authentication (require login credentials)</li>
                    <li>Role-based access control (restrict certain tools based on user role)</li>
                    <li>API keys for authentication</li>
                    <li>Rate limiting to prevent abuse</li>
                </ul>
            </div>
            
            <h2>Benefits of MCP Integration</h2>
            <ul>
                <li><strong>Natural Language Queries:</strong> Ask questions about your Digital ID system in plain English</li>
                <li><strong>Quick Data Access:</strong> Instantly retrieve employee information without navigating the web interface</li>
                <li><strong>Automated Tasks:</strong> Perform routine administrative tasks through AI assistants</li>
                <li><strong>Data Analysis:</strong> Query verification logs and analyse patterns using natural language</li>
                <li><strong>Integration:</strong> Connect Digital ID data with other tools and workflows</li>
            </ul>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Use Cases</h4>
                <p>The MCP server is particularly useful for:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>Administrators who want to quickly look up employee information</li>
                    <li>Analysing verification patterns and security events</li>
                    <li>Integrating Digital ID data into automated workflows</li>
                    <li>Building custom reports and dashboards</li>
                    <li>Auditing and compliance reviews</li>
                </ul>
            </div>
            
        <?php elseif ($section === 'user-stories'): ?>
            <h1>User Stories</h1>
            <p>Digital ID helps organisations and individuals in various scenarios. Below are user stories that demonstrate how different people use the system to solve real-world challenges.</p>
            
            <h2>Social Care Worker Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-user"></i> As a social care worker...</h4>
            </div>
            
            <h3>Bank Transactions</h3>
            <p><strong>Story:</strong> As a social care worker, I need to prove my identity when acting on behalf of vulnerable clients at banks, so that I can complete financial transactions legally and securely.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>I open my Digital ID app on my phone before going to the bank</li>
                <li>At the bank, I show my digital ID card to the bank staff</li>
                <li>Bank staff scan the QR code using the verification page</li>
                <li>The system confirms my identity, employee status, and organisation</li>
                <li>The bank can proceed with the transaction, knowing I'm a verified employee</li>
                <li>The verification is logged for compliance and audit purposes</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>No need to carry physical ID cards that can be lost or stolen</li>
                    <li>Quick verification process - no waiting for manual checks</li>
                    <li>Secure digital verification replaces paper-based authorisation</li>
                    <li>Complete audit trail for compliance</li>
                </ul>
            </div>
            
            <h3>Service User Visits</h3>
            <p><strong>Story:</strong> As a social care worker visiting service users in their homes, I need service users and their families to be able to verify my identity, so they feel safe and confident that I'm a legitimate employee.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Before visiting, I share the verification link with the service user or their family</li>
                <li>When I arrive, I show my digital ID card</li>
                <li>They can scan the QR code or visit the verification page</li>
                <li>The system displays my verified name, photo, and employee reference</li>
                <li>They can confirm I'm the person they're expecting</li>
                <li>They feel confident and secure knowing I'm verified</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Builds trust and confidence with service users and families</li>
                    <li>Easy verification process - no technical knowledge required</li>
                    <li>Service users can verify staff independently</li>
                    <li>Reduces anxiety and security concerns</li>
                </ul>
            </div>
            
            <h3>Emergency Situations</h3>
            <p><strong>Story:</strong> As a social care worker, I need to quickly prove my identity during emergencies, fire drills, or safety checks, so that emergency services and site managers can verify I'm authorised to be on site.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>During an emergency or fire drill, I quickly access my digital ID card</li>
                <li>I can use visual verification - showing my photo and details immediately</li>
                <li>For logged verification, emergency staff can scan the QR code</li>
                <li>The system confirms my identity and employment status instantly</li>
                <li>Attendance and safety checks are automatically logged</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Instant access - no need to search for physical ID cards</li>
                    <li>Works even if phone is offline (visual verification)</li>
                    <li>Automatic logging for safety compliance</li>
                    <li>Fast verification during critical situations</li>
                </ul>
            </div>
            
            <h3>Lone Working</h3>
            <p><strong>Story:</strong> As a social care worker working alone or late hours, I need to be able to prove my identity if challenged, so that security staff or members of the public can verify I'm authorised to be on site.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>While working alone or during late hours, I keep my digital ID accessible</li>
                <li>If questioned by security or concerned members of the public, I show my ID card</li>
                <li>They can quickly verify my identity using the QR code</li>
                <li>The system confirms I'm a current, active employee</li>
                <li>Verification attempts are logged for security records</li>
            </ol>
            
            <h2>Organisation Administrator Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-user-shield"></i> As an organisation administrator...</h4>
            </div>
            
            <h3>Employee Management</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to easily manage employee ID cards, approve photos, and revoke access when staff leave, so that our organisation maintains secure, up-to-date identification records.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>I create employee profiles for new staff members</li>
                <li>I can approve or reject employee photo uploads</li>
                <li>I set employee references and expiration dates</li>
                <li>When staff leave, I can instantly revoke their ID cards</li>
                <li>Revoked cards cannot be verified, even with valid tokens</li>
                <li>All changes are tracked and auditable</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Centralised employee management</li>
                    <li>Instant revocation when staff leave</li>
                    <li>Photo approval workflow ensures quality</li>
                    <li>Complete audit trail for compliance</li>
                </ul>
            </div>
            
            <h3>Compliance and Auditing</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to maintain complete records of all ID verifications for regulatory compliance and quality assurance reviews.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Every verification attempt is automatically logged</li>
                <li>I can view verification logs with filters by date, employee, or type</li>
                <li>I can export logs as CSV for compliance reporting</li>
                <li>Logs include timestamps, verification methods, results, and verifier information</li>
                <li>Complete audit trail for inspections and reviews</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Automatic logging - no manual record keeping required</li>
                    <li>Exportable data for regulatory submissions</li>
                    <li>Comprehensive audit trail</li>
                    <li>Searchable and filterable logs</li>
                </ul>
            </div>
            
            <h3>Meeting Attendance</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to track attendance at meetings, training sessions, and mandatory briefings, so that we have digital records for compliance and quality assurance.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Staff scan their QR codes at the start of meetings</li>
                <li>Each scan is logged with timestamp and employee details</li>
                <li>I can view attendance records in the verification logs</li>
                <li>I can filter logs by date range to see meeting attendance</li>
                <li>Digital records replace paper sign-in sheets</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Digital records - no lost paperwork</li>
                    <li>Automatic timestamping</li>
                    <li>Easy to query and report on attendance</li>
                    <li>Integrated with existing verification system</li>
                </ul>
            </div>
            
            <h2>Service User and Family Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-users"></i> As a service user, family member, or carer...</h4>
            </div>
            
            <h3>Verifying Staff Identity</h3>
            <p><strong>Story:</strong> As a service user's family member, I need to be able to verify that the person visiting my relative is a legitimate employee, so that I feel confident and secure about who is entering their home.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>When a care worker arrives, they show me their digital ID card</li>
                <li>I can scan the QR code with my phone or visit the verification website</li>
                <li>The system displays the worker's verified name, photo, and employee reference</li>
                <li>I can confirm they match the photo and are who they claim to be</li>
                <li>I see the organisation name to confirm they're from the right provider</li>
                <li>I feel confident knowing the person is verified and legitimate</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Easy verification - no technical knowledge needed</li>
                    <li>Works on any device with internet access</li>
                    <li>Provides peace of mind and confidence</li>
                    <li>Independent verification - no need to contact the organisation</li>
                </ul>
            </div>
            
            <h2>Bank and Financial Institution Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-university"></i> As a bank employee...</h4>
            </div>
            
            <h3>Verifying Care Worker Identity</h3>
            <p><strong>Story:</strong> As a bank employee, I need to verify that a person claiming to act on behalf of a vulnerable client is a legitimate, authorised employee of a care organisation, so that I can proceed with financial transactions securely and legally.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>A person arrives claiming to be a care worker acting on behalf of a client</li>
                <li>They show me their digital ID card on their phone</li>
                <li>I scan the QR code using the public verification page</li>
                <li>The system displays their verified identity and employment status</li>
                <li>I can confirm their photo matches, see their employee reference, and organisation</li>
                <li>I can proceed with the transaction, knowing they're verified</li>
                <li>The verification is logged for our records</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Quick verification process - no phone calls needed</li>
                    <li>Secure digital verification replaces paper authorisation</li>
                    <li>Time-limited tokens prevent replay attacks</li>
                    <li>Automatic logging provides audit trail</li>
                    <li>Confirms current employment status</li>
                </ul>
            </div>
            
            <h2>Use Case Mapping</h2>
            <p>Digital ID supports various scenarios across different contexts:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Use Case</th>
                        <th>Primary User</th>
                        <th>Verification Method</th>
                        <th>Key Benefit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bank transactions</td>
                        <td>Care worker</td>
                        <td>QR code</td>
                        <td>Secure, logged verification</td>
                    </tr>
                    <tr>
                        <td>Service user visits</td>
                        <td>Care worker, Service user</td>
                        <td>Visual, QR code</td>
                        <td>Building trust and confidence</td>
                    </tr>
                    <tr>
                        <td>Emergency situations</td>
                        <td>Care worker</td>
                        <td>Visual, QR code</td>
                        <td>Quick identity verification</td>
                    </tr>
                    <tr>
                        <td>Lone working</td>
                        <td>Care worker</td>
                        <td>Visual, QR code</td>
                        <td>Security and safety</td>
                    </tr>
                    <tr>
                        <td>Meeting attendance</td>
                        <td>All staff</td>
                        <td>QR code</td>
                        <td>Digital attendance records</td>
                    </tr>
                    <tr>
                        <td>Fire drills</td>
                        <td>All staff</td>
                        <td>QR code</td>
                        <td>Safety compliance logging</td>
                    </tr>
                    <tr>
                        <td>Staff verification</td>
                        <td>Administrator</td>
                        <td>Verification logs</td>
                        <td>Compliance and auditing</td>
                    </tr>
                    <tr>
                        <td>Identity confirmation</td>
                        <td>Family members, Carers</td>
                        <td>Visual, QR code</td>
                        <td>Peace of mind</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Future Use Cases</h2>
            <p>Digital ID is designed to support additional use cases in the future:</p>
            <ul>
                <li><strong>Door access systems:</strong> NFC integration for contactless building access</li>
                <li><strong>Attendance tracking:</strong> Automated attendance systems using QR or NFC</li>
                <li><strong>Time and attendance:</strong> Clocking in/out for shifts</li>
                <li><strong>Visitor management:</strong> Temporary access credentials</li>
                <li><strong>Asset tracking:</strong> Linking staff to equipment and vehicles</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-lightbulb"></i> Have Your Own Story?</h4>
                <p>If you're using Digital ID in an interesting way, we'd love to hear about it! Visit our <a href="<?php echo url('case-studies.php'); ?>">Case Studies</a> page to share your story and help others understand how Digital ID can be used.</p>
            </div>
            
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
            
        <?php elseif ($section === 'check-in-sessions'): ?>
            <h1>Check-In Sessions</h1>
            <p>Digital ID includes a comprehensive check-in system for tracking attendance during fire drills, safety meetings, emergencies, and other events. This feature integrates seamlessly with Microsoft 365 for automatic data synchronisation.</p>
            
            <h2>Overview</h2>
            <p>Check-in sessions allow organisations to:</p>
            <ul>
                <li>Create timed sessions for fire drills, safety meetings, and emergencies</li>
                <li>Track staff attendance in real-time</li>
                <li>Allow staff to check in using QR codes or manual entry</li>
                <li>Export attendance records for compliance reporting</li>
                <li>Automatically sync data to Microsoft 365 (SharePoint, Power Automate, Teams)</li>
            </ul>
            
            <h2>Creating a Check-In Session</h2>
            <p>Organisation administrators can create check-in sessions from the admin panel:</p>
            <ol class="step-list">
                <li>Navigate to <strong>Organisation</strong> → <strong>Check-In Sessions</strong></li>
                <li>Click <strong>"Create New Session"</strong></li>
                <li>Enter a session name (e.g., "Fire Drill - Main Building")</li>
                <li>Select the session type:
                    <ul>
                        <li><strong>Fire Drill:</strong> Planned fire evacuation practice</li>
                        <li><strong>Fire Alarm:</strong> Actual fire alarm activation</li>
                        <li><strong>Safety Meeting:</strong> Health and safety meeting attendance</li>
                        <li><strong>Emergency:</strong> Other emergency situations</li>
                    </ul>
                </li>
                <li>Optionally add a location name</li>
                <li>Click <strong>"Create Session"</strong></li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Session Types</h4>
                <p>Session types help categorise different events and can be used for reporting and filtering. All session types support the same check-in functionality.</p>
            </div>
            
            <h2>Staff Check-In Process</h2>
            <p>Once a session is active, staff can check in using two methods:</p>
            
            <h3>Method 1: QR Code Check-In</h3>
            <ol class="step-list">
                <li>Navigate to <strong>Check In</strong> in the main menu</li>
                <li>Select the active session</li>
                <li>Click <strong>"Check In with QR Code"</strong></li>
                <li>Display your QR code on your device</li>
                <li>The system will automatically verify and check you in</li>
            </ol>
            
            <h3>Method 2: Manual Check-In</h3>
            <ol class="step-list">
                <li>Navigate to <strong>Check In</strong> in the main menu</li>
                <li>Select the active session</li>
                <li>Click <strong>"Check In"</strong> button</li>
                <li>You'll be immediately checked in</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Check-Out</h4>
                <p>Staff can check out from a session at any time. This is useful for tracking when people leave during long sessions or emergencies.</p>
            </div>
            
            <h2>Viewing Session Attendance</h2>
            <p>Administrators can view real-time attendance for any session:</p>
            <ol class="step-list">
                <li>Go to <strong>Organisation</strong> → <strong>Check-In Sessions</strong></li>
                <li>Click on a session to view details</li>
                <li>See the list of all check-ins with timestamps and methods</li>
                <li>Active sessions automatically refresh every 10 seconds</li>
            </ol>
            
            <h2>Exporting Attendance</h2>
            <p>Export attendance records for compliance and reporting:</p>
            <ol class="step-list">
                <li>Open a session from the Check-In Sessions list</li>
                <li>Click <strong>"Export Attendance"</strong></li>
                <li>A CSV file will be downloaded with:
                    <ul>
                        <li>Employee names and references</li>
                        <li>Check-in and check-out times</li>
                        <li>Check-in method (QR scan or manual)</li>
                        <li>Location information</li>
                        <li>Status (checked in or checked out)</li>
                    </ul>
                </li>
            </ol>
            
            <h2>Ending a Session</h2>
            <p>When a session is complete:</p>
            <ol class="step-list">
                <li>Open the session details page</li>
                <li>Click <strong>"End Session"</strong></li>
                <li>Confirm the action</li>
                <li>The session will be marked as ended and no new check-ins will be allowed</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Once a session is ended, staff cannot check in or out. However, you can still view and export the attendance records.</p>
            </div>
            
            <h2>Microsoft 365 Integration</h2>
            <p>Digital ID can automatically synchronise check-in data with Microsoft 365 services:</p>
            
            <h3>SharePoint Lists</h3>
            <p>Check-in data can be automatically synced to SharePoint Lists for integration with existing workflows and reporting tools.</p>
            <ol class="step-list">
                <li>Go to <strong>Organisation</strong> → <strong>Microsoft 365 Settings</strong></li>
                <li>Enable Microsoft 365 synchronisation</li>
                <li>Enter your SharePoint site URL</li>
                <li>Enter the SharePoint List ID where check-ins should be stored</li>
                <li>Save settings</li>
            </ol>
            
            <h3>Power Automate</h3>
            <p>Trigger Power Automate workflows when check-ins occur:</p>
            <ol class="step-list">
                <li>Create a Power Automate flow with a webhook trigger</li>
                <li>Copy the webhook URL</li>
                <li>Paste it into the Microsoft 365 Settings page</li>
                <li>Check-ins will now trigger your workflow automatically</li>
            </ol>
            
            <h3>Microsoft Teams</h3>
            <p>Send notifications to Teams channels when sessions start:</p>
            <ol class="step-list">
                <li>Get your Teams channel ID</li>
                <li>Enter it in the Microsoft 365 Settings page</li>
                <li>Notifications will be sent when sessions are created</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Prerequisites</h4>
                <p>Microsoft 365 integration requires Microsoft Entra (Azure AD) to be configured first. See the <a href="<?php echo url('docs.php?section=entra-integration'); ?>">Microsoft Entra Integration</a> guide for setup instructions.</p>
            </div>
            
            <h2>Use Cases</h2>
            
            <h3>Fire Drill Tracking</h3>
            <p>During fire drills, create a session and have all staff check in at the assembly point. This ensures accurate headcounts and helps identify who may still be in the building.</p>
            
            <h3>Safety Meeting Attendance</h3>
            <p>Track attendance at mandatory safety meetings. Export records for compliance reporting and training records.</p>
            
            <h3>Emergency Evacuations</h3>
            <p>In real emergencies, quickly create a session and track who has safely evacuated. This information is critical for emergency services.</p>
            
            <h2>Best Practices</h2>
            <ul>
                <li><strong>Create sessions in advance:</strong> For planned events like fire drills, create the session before the event starts</li>
                <li><strong>Use clear naming:</strong> Name sessions descriptively (e.g., "Fire Drill - Main Building - 15 Jan 2024")</li>
                <li><strong>End sessions promptly:</strong> End sessions when complete to prevent accidental check-ins</li>
                <li><strong>Export regularly:</strong> Export attendance records after each session for your records</li>
                <li><strong>Test the system:</strong> Run a test session before important events to ensure staff know how to check in</li>
            </ul>
            
            <h2>Access Control</h2>
            <p>Check-in sessions are organisation-specific. Only staff from the same organisation can check in to a session. Organisation administrators can create and manage sessions for their organisation.</p>
            
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


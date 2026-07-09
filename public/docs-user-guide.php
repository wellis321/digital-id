<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'User Guide - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
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
                <p>For faster access, install Digital ID as an app on your phone! See the <a href="<?php echo url('docs-pwa.php'); ?>">Install as App</a> guide for instructions. This lets you access your ID card directly from your home screen.</p>
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
            
            <h3>For Building Access and Turnstiles</h3>
            <p>Many organisations use Digital ID for secure building access:</p>
            <ol>
                <li>Open your Digital ID card on your device</li>
                <li>Present the QR code to the turnstile or access panel scanner</li>
                <li>The system will verify your identity and grant access automatically</li>
                <li>All access attempts are logged for security monitoring</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-door-open"></i> Building Access Features</h4>
                <ul>
                    <li><strong>Automatic Verification:</strong> Your QR code is verified in real-time</li>
                    <li><strong>Access Logging:</strong> All entry/exit attempts are recorded</li>
                    <li><strong>Secure Tokens:</strong> Time-limited QR codes ensure security</li>
                    <li><strong>Works Offline:</strong> Your QR code can be displayed offline, but verification requires internet connection</li>
                </ul>
                <p style="margin-top: 1rem; margin-bottom: 0;">
                    <strong>Note:</strong> If your QR code expires (after 5 minutes), simply refresh your ID card page to get a new one.
                </p>
            </div>
            
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
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

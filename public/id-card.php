<?php
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();

$employeeId = null;
$employee = null;
$idCard = null;

// Get employee for current user or specified employee_id (if admin)
$requestedEmployeeId = $_GET['employee_id'] ?? null;
if ($requestedEmployeeId && RBAC::isAdmin()) {
    // Admin viewing another employee's card
    $employee = Employee::findById($requestedEmployeeId);
    if ($employee && RBAC::canAccessOrganisation($employee['organisation_id'])) {
        $employeeId = $employee['id'];
    }
} else {
    // User viewing their own card
    $employee = Employee::findByUserId(Auth::getUserId());
    if ($employee) {
        $employeeId = $employee['id'];
    }
}

// Superadmins don't need employee profiles - redirect to admin area before any output
if (!$employee && RBAC::isSuperAdmin()) {
    header('Location: ' . url('admin/organisations.php'));
    exit;
}

$pageTitle = 'Digital ID Card';
include dirname(__DIR__) . '/includes/header.php';

// Include PWA install prompt (only show on mobile)
include __DIR__ . '/pwa-install-prompt.php';

// If no employee record exists, find contact person and send notifications
if (!$employee):
    $user = Auth::getUser();
    
    $organisation = null;
    $contactPerson = null;
    $contactAttempts = [];
    $notificationSent = false;
    
    // Get organisation details
    if ($user && $user['organisation_id']) {
        $organisation = ContactHelper::getOrganisationDetails($user['organisation_id']);
    }
    
    // Find contact person (only if not admin - admins can create their own)
    if (!RBAC::isAdmin() && $user) {
        $contactPerson = ContactHelper::findContactPerson($user['id']);
        
        // Send notifications
        if ($contactPerson && $organisation) {
            // Try to notify organisation admin
            if ($contactPerson['role_type'] === 'organisation_admin') {
                $notificationSent = Email::notifyAdminAboutMissingEmployeeProfile(
                    $contactPerson['email'],
                    $contactPerson['first_name'],
                    $user,
                    $organisation
                );
                $contactAttempts[] = "Notified organisation administrator: " . $contactPerson['first_name'] . " " . $contactPerson['last_name'] . " (" . $contactPerson['email'] . ")";
            } else {
                // If no org admin, try to find and notify all org admins
                $orgAdmins = ContactHelper::getOrganisationAdmins($user['organisation_id']);
                if (!empty($orgAdmins)) {
                    foreach ($orgAdmins as $admin) {
                        Email::notifyAdminAboutMissingEmployeeProfile(
                            $admin['email'],
                            $admin['first_name'],
                            $user,
                            $organisation
                        );
                        $contactAttempts[] = "Notified organisation administrator: " . $admin['first_name'] . " " . $admin['last_name'] . " (" . $admin['email'] . ")";
                    }
                    $notificationSent = true;
                } else {
                    // No org admin found, try superadmin
                    $superAdmins = ContactHelper::getSuperAdmins();
                    if (!empty($superAdmins)) {
                        foreach ($superAdmins as $superAdmin) {
                            Email::notifyAdminAboutMissingEmployeeProfile(
                                $superAdmin['email'],
                                $superAdmin['first_name'],
                                $user,
                                $organisation
                            );
                            $contactAttempts[] = "Notified super administrator: " . $superAdmin['first_name'] . " " . $superAdmin['last_name'] . " (" . $superAdmin['email'] . ")";
                        }
                        $notificationSent = true;
                    }
                }
            }
            
            // If no one was found to notify, notify support
            if (empty($contactAttempts)) {
                $contactAttempts[] = "No administrators found in organisation";
                Email::notifySupportAboutMissingEmployeeProfile($user, $organisation, $contactAttempts);
                $contactAttempts[] = "Notified site support team";
            } elseif (!$notificationSent && $contactPerson['role_type'] !== 'organisation_admin') {
                // If we found someone but they're not an admin, still notify support
                $contactAttempts[] = "No organisation administrator found - escalated to support";
                Email::notifySupportAboutMissingEmployeeProfile($user, $organisation, $contactAttempts);
            }
        } elseif ($organisation) {
            // No contact person found at all, notify support
            $contactAttempts[] = "No contact person found in organisation";
            Email::notifySupportAboutMissingEmployeeProfile($user, $organisation, $contactAttempts);
            $contactAttempts[] = "Notified site support team";
        }
    }
?>
<div class="card">
    <h1>Employee Profile Not Found</h1>
    <div class="alert alert-error">
        <p><strong>Your account is active, but you don't have an employee profile set up yet.</strong></p>
        <p>An employee profile with an employee reference number is required to generate your digital ID card.</p>
    </div>
    
    <?php if (RBAC::isAdmin()): ?>
        <div class="card" style="margin-top: 1.5rem; background-color: #e3f2fd; border-left: 4px solid #2196F3;">
            <h2 style="margin-top: 0;">Create Your Employee Profile</h2>
            <p>As an administrator, you can create employee profiles for yourself and other users.</p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo url('admin/employees.php'); ?>" class="btn btn-primary">
                    Go to Employee Management
                </a>
            </p>
        </div>
    <?php else: ?>
        <?php if ($contactPerson): ?>
            <div class="card" style="margin-top: 1.5rem; background-color: #e8f5e9; border-left: 4px solid #4CAF50;">
                <h2 style="margin-top: 0;">✓ Help is on the way!</h2>
                <p>We've automatically notified the appropriate person in your organisation about your missing employee profile.</p>
                
                <div style="background-color: white; padding: 1rem; border-radius: 0; margin-top: 1rem;">
                    <h3 style="margin-top: 0; font-size: 1.1rem;">Your Contact Person:</h3>
                    <p style="margin: 0.5rem 0;"><strong>Name:</strong> <?php echo htmlspecialchars($contactPerson['first_name'] . ' ' . $contactPerson['last_name']); ?></p>
                    <p style="margin: 0.5rem 0;"><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($contactPerson['email']); ?>"><?php echo htmlspecialchars($contactPerson['email']); ?></a></p>
                    <p style="margin: 0.5rem 0;"><strong>Role:</strong> <?php 
                        echo $contactPerson['role_type'] === 'organisation_admin' ? 'Organisation Administrator' : 
                             ($contactPerson['role_type'] === 'superadmin' ? 'System Administrator' : 'Staff Member');
                    ?></p>
                </div>
                
                <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                    They've been sent an email with your details and instructions on how to create your employee profile. 
                    You can also contact them directly using the email above if you need to follow up.
                </p>
            </div>
        <?php else: ?>
            <div class="card" style="margin-top: 1.5rem; background-color: #fff3cd; border-left: 4px solid #ffc107;">
                <h2 style="margin-top: 0;">⚠️ Escalated to Support</h2>
                <p>We couldn't find an administrator in your organisation to assist you.</p>
                <p>Your request has been automatically escalated to our support team, who will contact you shortly to help resolve this issue.</p>
                
                <?php if ($organisation): ?>
                    <div style="background-color: white; padding: 1rem; border-radius: 0; margin-top: 1rem;">
                        <h3 style="margin-top: 0; font-size: 1.1rem;">Your Organisation:</h3>
                        <p style="margin: 0.5rem 0;"><strong>Name:</strong> <?php echo htmlspecialchars($organisation['name']); ?></p>
                        <p style="margin: 0.5rem 0;"><strong>Domain:</strong> <?php echo htmlspecialchars($organisation['domain']); ?></p>
                    </div>
                <?php endif; ?>
                
                <p style="margin-top: 1rem; font-size: 0.9rem;">
                    <strong>Support Email:</strong> 
                    <a href="mailto:<?php echo htmlspecialchars(CONTACT_EMAIL); ?>">
                        <?php echo htmlspecialchars(CONTACT_EMAIL); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="<?php echo url('index.php'); ?>" class="btn btn-secondary">Return to Home</a>
        </div>
    <?php endif; ?>
</div>
<?php
    include dirname(__DIR__) . '/includes/footer.php';
    exit;
endif;

// Get or create ID card
$idCard = DigitalID::getOrCreateIdCard($employeeId);

if (!$idCard) {
    die('Failed to generate ID card. Please contact support.');
}

// Generate QR code
$qrData = QRCodeGenerator::generate($idCard['qr_token']);
$qrImageUrl = QRCodeGenerator::generateImageUrl($idCard['qr_token']);
?>

<div class="id-card" id="id-card-content">
    <div class="id-card-header">
        <h2><?php echo htmlspecialchars($employee['organisation_name']); ?></h2>
        <p>Digital ID Card</p>
    </div>
    
    <?php 
    // Show approved photo, or pending photo if no approved photo yet
    $photoPath = null;
    $photoStatus = $employee['photo_approval_status'] ?? 'none';
    
    // First priority: approved photo
    if ($photoStatus === 'approved' && $employee['photo_path'] && file_exists(dirname(__DIR__) . '/' . $employee['photo_path'])) {
        $photoPath = url('view-image.php?path=' . urlencode($employee['photo_path']));
    }
    // Second priority: pending photo (show what user uploaded)
    elseif ($photoStatus === 'pending' && !empty($employee['photo_pending_path']) && file_exists(dirname(__DIR__) . '/' . $employee['photo_pending_path'])) {
        $photoPath = url('view-image.php?path=' . urlencode($employee['photo_pending_path']));
    }
    ?>
    
        <?php if ($photoPath): ?>
            <?php
            $photoAltText = 'ID card photo for ' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']);
            if ($photoStatus === 'pending') {
                $photoAltText .= ' (pending approval)';
            }
            ?>
            <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="<?php echo $photoAltText; ?>" class="id-card-photo" style="<?php echo $photoStatus === 'pending' ? 'opacity: 0.7; border: 2px dashed #f59e0b;' : ''; ?>">
    <?php else: ?>
        <div class="id-card-photo" style="background-color: #f3f4f6; border: 3px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280;">
            No Photo
        </div>
    <?php endif; ?>
    
    <!-- Photo Upload/Status Info -->
    <div style="text-align: center; margin-top: 1rem;">
        <?php if (($employee['photo_approval_status'] ?? 'none') === 'pending'): ?>
            <p style="color: #f59e0b; font-size: 0.875rem; margin: 0 0 0.75rem 0;">
                <i class="fas fa-clock"></i> Photo pending approval
            </p>
        <?php elseif (($employee['photo_approval_status'] ?? 'none') === 'rejected'): ?>
            <p style="color: #ef4444; font-size: 0.875rem; margin: 0 0 0.75rem 0;">
                <i class="fas fa-exclamation-triangle"></i> Photo was rejected
            </p>
        <?php endif; ?>
        
        <?php if (!RBAC::isAdmin() || Auth::getUserId() == $employee['user_id']): ?>
            <a href="<?php echo url('upload-photo.php'); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                <i class="fas fa-camera"></i> <?php echo $photoPath ? 'Change Photo' : 'Upload Photo'; ?>
            </a>
        <?php endif; ?>
    </div>
    
    <div class="id-card-details">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
        <p><strong>Reference:</strong> <?php echo htmlspecialchars($employee['display_reference'] ?? $employee['employee_reference'] ?? 'N/A'); ?></p>
        <p><strong>Organisation:</strong> <?php echo htmlspecialchars($employee['organisation_name']); ?></p>
    </div>
    
    <div class="id-card-qr">
        <p style="margin-bottom: 1rem; font-size: 0.875rem;">Scan QR code for verification</p>
        <?php
        $qrAltText = 'QR code for verifying ' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) . ' ID card';
        ?>
        <img src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="<?php echo $qrAltText; ?>" style="max-width: 200px; background: white; padding: 1rem; border-radius: 0;">
    </div>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <button id="nfc-activate" class="btn btn-secondary" style="width: 100%;">Activate NFC</button>
        <p style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.8;">Tap to enable NFC verification</p>
    </div>
    
    <div style="margin-top: 1rem; text-align: center; font-size: 0.75rem; opacity: 0.7;">
        <p>Card expires: <?php echo date('d/m/Y', strtotime($idCard['expires_at'])); ?></p>
    </div>
</div>

<script>
// NFC activation (requires Web NFC API - only available on supported devices)
document.getElementById('nfc-activate').addEventListener('click', async function() {
    const nfcToken = '<?php echo $idCard['nfc_token']; ?>';
    const verificationUrl = '<?php echo APP_URL . url('verify.php?token=' . urlencode($idCard['nfc_token']) . '&type=nfc'); ?>';
    
    // Check if we're on HTTPS (required for Web NFC)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        alert('NFC requires HTTPS. Please access this site over HTTPS to use NFC features.');
        return;
    }
    
    // Check for modern Web NFC API (NDEFWriter - Chrome/Edge on Android)
    // Note: Web NFC API is experimental and only works on Chrome/Edge for Android
    if ('NDEFWriter' in window) {
        try {
            const writer = new NDEFWriter();
            await writer.write(verificationUrl);
            alert('NFC tag written successfully!');
        } catch (err) {
            if (err.name === 'NotAllowedError' || err.name === 'SecurityError') {
                alert('NFC permission denied. Please:\n1. Allow NFC access in your browser settings\n2. Ensure NFC is enabled on your device\n3. Grant location permission if prompted');
            } else if (err.name === 'NotSupportedError') {
                alert('NFC is not supported on this device or browser.\n\nWeb NFC requires:\n- Chrome or Edge browser on Android\n- NFC-enabled device\n- HTTPS connection');
            } else if (err.name === 'NotFoundError' || err.message.includes('tag')) {
                alert('No NFC tag detected. Please:\n1. Ensure NFC is enabled on your device\n2. Hold your device near an NFC tag or reader\n3. Try again');
            } else {
                alert('NFC error: ' + err.message + '\n\nPlease ensure:\n- NFC is enabled on your device\n- You are using Chrome or Edge on Android\n- You have granted NFC permissions');
            }
        }
    } 
    // Check for NDEFReader (alternative API - for reading, but indicates NFC support)
    else if ('NDEFReader' in window) {
        try {
            // Try to write using NDEFReader (some implementations support both)
            const reader = new NDEFReader();
            // Note: NDEFReader is primarily for reading, but we can check if device supports NFC
            alert('NFC is detected on your device, but writing may not be supported.\n\nPlease use Chrome or Edge browser on Android for full NFC write support.');
        } catch (err) {
            alert('NFC is available but cannot write tags.\n\nPlease use Chrome or Edge browser on Android for NFC writing.');
        }
    } else {
        // Provide helpful error message
        const isAndroid = /Android/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent) && !/Edge|Edg/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);
        
        if (isAndroid && (isChrome || isEdge)) {
            alert('NFC API not available.\n\nPlease ensure:\n1. You are using the latest version of Chrome or Edge\n2. NFC is enabled in your device settings\n3. The page is loaded over HTTPS\n4. You have granted necessary permissions\n\nIf the issue persists, please use QR code for verification.');
        } else {
            alert('NFC is not supported on this device or browser.\n\nWeb NFC is currently only supported on:\n- Chrome or Edge browser\n- Android devices\n- Requires HTTPS connection\n- Requires NFC-enabled device\n\nPlease use QR code for verification instead.');
        }
    }
});
</script>

<style>
/* Mobile optimizations for ID card quick access */
@media (max-width: 768px) {
    .id-card {
        margin: 1rem 0;
        padding: 1.5rem;
    }
    
    .id-card-qr img {
        max-width: 180px;
    }
    
    /* Hide navigation when in standalone mode (PWA) */
    @media (display-mode: standalone) {
        header {
            display: none;
        }
        
        main.container {
            padding: 0;
            margin: 0;
        }
        
        .id-card {
            margin: 0;
            border-radius: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    }
}

/* Full screen mode for quick access */
@media (display-mode: standalone) {
    body {
        padding: 0;
        margin: 0;
    }
    
    main.container {
        max-width: 100%;
        padding: 0;
    }
}
</style>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>


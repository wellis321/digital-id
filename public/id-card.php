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
        <?php
        // Get organisation logo if available
        $orgLogoPath = null;
        if (!empty($employee['organisation_id'])) {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT logo_path FROM organisations WHERE id = ?");
            $stmt->execute([$employee['organisation_id']]);
            $orgData = $stmt->fetch();
            if ($orgData && $orgData['logo_path'] && file_exists(dirname(__DIR__) . '/' . $orgData['logo_path'])) {
                $orgLogoPath = url('view-image.php?path=' . urlencode($orgData['logo_path']));
            }
        }
        ?>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
            <?php if ($orgLogoPath): ?>
                <img src="<?php echo htmlspecialchars($orgLogoPath); ?>" 
                     alt="<?php echo htmlspecialchars($employee['organisation_name']); ?> Logo" 
                     style="max-height: 50px; max-width: 150px; object-fit: contain;">
            <?php endif; ?>
            <div style="flex: 1;">
                <h2 style="margin: 0;"><?php echo htmlspecialchars($employee['organisation_name']); ?></h2>
                <p style="margin: 0;">Digital ID Card</p>
            </div>
        </div>
    </div>
    
    <?php 
    // Show approved photo if it exists, otherwise show pending photo
    // Always prioritize approved photo - keep showing it even when new photo is pending
    $photoPath = null;
    $photoStatus = $employee['photo_approval_status'] ?? 'none';
    $hasApprovedPhoto = !empty($employee['photo_path']) && file_exists(dirname(__DIR__) . '/' . $employee['photo_path']);
    $hasPendingPhoto = !empty($employee['photo_pending_path']) && file_exists(dirname(__DIR__) . '/' . $employee['photo_pending_path']);
    
    // First priority: always show approved photo if it exists (even if status is pending)
    if ($hasApprovedPhoto) {
        $photoPath = url('view-image.php?path=' . urlencode($employee['photo_path']));
    }
    // Second priority: pending photo only if no approved photo exists
    elseif ($hasPendingPhoto && $photoStatus === 'pending') {
        $photoPath = url('view-image.php?path=' . urlencode($employee['photo_pending_path']));
    }
    ?>
    
        <?php if ($photoPath): ?>
            <?php
            $photoAltText = 'ID card photo for ' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']);
            // Only show pending indicator if we're actually showing a pending photo (no approved photo exists)
            if ($photoStatus === 'pending' && !$hasApprovedPhoto) {
                $photoAltText .= ' (pending approval)';
            }
            ?>
            <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="<?php echo $photoAltText; ?>" class="id-card-photo" style="<?php echo ($photoStatus === 'pending' && !$hasApprovedPhoto) ? 'opacity: 0.7; border: 2px dashed #f59e0b;' : ''; ?>">
    <?php else: ?>
        <div class="id-card-photo" style="background-color: #f3f4f6; border: 3px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280;">
            No Photo
        </div>
    <?php endif; ?>
    
    <!-- Photo Upload/Status Info -->
    <div style="text-align: center; margin-top: 1rem;">
        <?php 
        // Only show pending message if there's no approved photo (meaning we're showing a pending photo)
        $showPendingMessage = ($photoStatus === 'pending' && !$hasApprovedPhoto);
        ?>
        <?php if ($showPendingMessage): ?>
            <p style="color: #f59e0b; font-size: 0.875rem; margin: 0 0 0.75rem 0;">
                <i class="fas fa-clock"></i> Photo pending approval
            </p>
        <?php elseif ($photoStatus === 'pending' && $hasApprovedPhoto): ?>
            <p style="color: #06b6d4; font-size: 0.875rem; margin: 0 0 0.75rem 0;">
                <i class="fas fa-info-circle"></i> New photo uploaded - awaiting approval (current photo shown)
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
        <p style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.75rem;">Additional verification methods:</p>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
            <button id="nfc-activate" class="btn btn-secondary" style="flex: 1; min-width: 120px;">Activate NFC</button>
            <button id="ble-activate" class="btn btn-secondary" style="flex: 1; min-width: 120px;">Activate BLE</button>
        </div>
        <p id="nfc-help-text" style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.8;">NFC: Android Chrome/Edge only</p>
        <p id="ble-help-text" style="margin-top: 0.25rem; font-size: 0.75rem; opacity: 0.8;">BLE: Chrome/Edge (Android/Desktop)</p>
    </div>
    <script>
    // Hide NFC button on iOS - Web NFC API not supported on iOS Safari
    (function() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        if (isIOS) {
            const nfcButton = document.getElementById('nfc-activate');
            const nfcHelpText = document.getElementById('nfc-help-text');
            if (nfcButton) {
                nfcButton.style.display = 'none';
            }
            if (nfcHelpText) {
                nfcHelpText.style.display = 'none';
            }
        }
    })();
    
    // Hide BLE button on iOS - Web Bluetooth API not supported on iOS Safari
    (function() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isFirefox = /Firefox/.test(navigator.userAgent);
        if (isIOS || isFirefox) {
            const bleButton = document.getElementById('ble-activate');
            const bleHelpText = document.getElementById('ble-help-text');
            if (bleButton) {
                bleButton.style.display = 'none';
            }
            if (bleHelpText) {
                bleHelpText.style.display = 'none';
            }
        }
    })();
    </script>
    
    <div style="margin-top: 1rem; text-align: center; font-size: 0.75rem; opacity: 0.7;">
        <p>Card expires: <?php echo date('d/m/Y', strtotime($idCard['expires_at'])); ?></p>
    </div>
</div>

<script>
// NFC activation (requires Web NFC API - only available on supported devices)
document.getElementById('nfc-activate').addEventListener('click', async function() {
    const nfcToken = '<?php echo $idCard['nfc_token']; ?>';
    const verificationUrl = '<?php echo APP_URL . url('verify.php?token=' . urlencode($idCard['nfc_token']) . '&type=nfc'); ?>';
    
    // #region agent log
    fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:nfcButtonClick',message:'NFC button clicked',data:{userAgent:navigator.userAgent,protocol:location.protocol,hostname:location.hostname,hasNDEFWriter:'NDEFWriter' in window,hasNDEFReader:'NDEFReader' in window},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    
    // Check if we're on HTTPS (required for Web NFC)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:notHTTPS',message:'NFC blocked - not HTTPS',data:{protocol:location.protocol},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion
        alert('NFC requires HTTPS. Please access this site over HTTPS to use NFC features.');
        return;
    }
    
    // Detect platform and browser
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    const isChrome = /Chrome/.test(navigator.userAgent) && !/Edg|OPR/.test(navigator.userAgent);
    const isEdge = /Edg/.test(navigator.userAgent);
    const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
    const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
    
    // #region agent log
    fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:browserDetection',message:'Browser and platform detection for NFC',data:{isIOS:isIOS,isAndroid:isAndroid,isChrome:isChrome,isEdge:isEdge,isFirefox:isFirefox,isSafari:isSafari,userAgent:navigator.userAgent},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
    // #endregion
    
    // Check for modern Web NFC API (NDEFWriter - Chrome/Edge on Android)
    // Note: Web NFC API is experimental and only works on Chrome/Edge for Android
    if ('NDEFWriter' in window) {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:ndefWriterFound',message:'NDEFWriter API found, attempting write',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        try {
            const writer = new NDEFWriter();
            await writer.write(verificationUrl);
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:nfcWriteSuccess',message:'NFC write successful',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            alert('NFC tag written successfully!');
        } catch (err) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:nfcWriteError',message:'NFC write error',data:{errorName:err.name,errorMessage:err.message,errorStack:err.stack},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
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
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:ndefReaderFound',message:'NDEFReader found but NDEFWriter not available',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        try {
            // Try to write using NDEFReader (some implementations support both)
            const reader = new NDEFReader();
            // Note: NDEFReader is primarily for reading, but we can check if device supports NFC
            alert('NFC is detected on your device, but writing may not be supported.\n\nPlease use Chrome or Edge browser on Android for full NFC write support.');
        } catch (err) {
            alert('NFC is available but cannot write tags.\n\nPlease use Chrome or Edge browser on Android for NFC writing.');
        }
    } else {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'id-card.php:noNFCAPI',message:'No NFC API available',data:{isIOS:isIOS,isAndroid:isAndroid,isChrome:isChrome,isEdge:isEdge,isFirefox:isFirefox,protocol:location.protocol},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
        // #endregion
        // Provide helpful error message based on platform
        if (isIOS) {
            alert('NFC Writing Not Available on iOS\n\nWhile your iPhone has NFC hardware, iOS Safari does not support the Web NFC API for writing NFC tags.\n\nYour iPhone can:\n- Use NFC for Apple Pay\n- Read NFC tags with third-party apps\n\nBut cannot:\n- Write NFC tags through web browsers\n\nPlease use the QR code above for verification instead. QR codes work perfectly on all devices and browsers.');
        } else if (isAndroid && (isChrome || isEdge)) {
            alert('NFC API not available.\n\nPlease ensure:\n1. You are using the latest version of Chrome or Edge (version 89+)\n2. NFC is enabled in your device settings\n3. The page is loaded over HTTPS\n4. You have granted necessary permissions\n5. Your device has NFC hardware\n\nNote: Web NFC API may not be available on all Android devices, even with Chrome/Edge.\n\nIf the issue persists, please use QR code for verification.');
        } else if (isAndroid && isFirefox) {
            alert('NFC Not Supported in Firefox\n\nFirefox on Android does not support the Web NFC API.\n\nPlease use:\n- Chrome browser on Android, or\n- Edge browser on Android, or\n- QR code for verification (works on all browsers)');
        } else if (isAndroid) {
            alert('NFC Not Supported\n\nWeb NFC writing requires:\n- Chrome or Edge browser (not Firefox or other browsers)\n- Android device\n- Latest browser version (Chrome 89+ or Edge 89+)\n- HTTPS connection\n- NFC-enabled device\n\nPlease use Chrome or Edge browser, or use the QR code above for verification.');
        } else {
            alert('NFC Writing Not Supported\n\nWeb NFC writing is currently only supported on:\n- Chrome or Edge browser\n- Android devices\n- Requires HTTPS connection\n- Requires NFC-enabled device\n\nPlease use the QR code above for verification instead. QR codes work on all devices and browsers.');
        }
    }
});

// BLE activation (supplementary feature - uses Web Bluetooth API)
document.getElementById('ble-activate').addEventListener('click', async function() {
    const nfcToken = '<?php echo $idCard['nfc_token']; ?>';
    const verificationUrl = '<?php echo APP_URL . url('verify.php?token=' . urlencode($idCard['nfc_token']) . '&type=ble'); ?>';
    
    // Check if we're on HTTPS (required for Web Bluetooth)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        alert('BLE requires HTTPS. Please access this site over HTTPS to use BLE features.');
        return;
    }
    
    // Check for Web Bluetooth API
    if (!navigator.bluetooth) {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isFirefox = /Firefox/.test(navigator.userAgent);
        
        if (isIOS) {
            alert('BLE Not Available on iOS\n\nWeb Bluetooth API is not supported in iOS Safari.\n\nPlease use:\n- QR code for verification (works on all devices), or\n- A third-party browser like Bluefy that supports Web Bluetooth');
        } else if (isFirefox) {
            alert('BLE Not Supported in Firefox\n\nWeb Bluetooth API is not supported in Firefox.\n\nPlease use:\n- Chrome browser, or\n- Edge browser, or\n- QR code for verification (works on all browsers)');
        } else {
            alert('BLE Not Supported\n\nWeb Bluetooth API is not available in this browser.\n\nPlease use:\n- Chrome browser (Android/Desktop), or\n- Edge browser (Android/Desktop), or\n- QR code for verification (works on all browsers)');
        }
        return;
    }
    
    try {
        // Request Bluetooth device (this makes device discoverable)
        // Note: Web Bluetooth API primarily supports central mode (connecting to devices)
        // For peripheral mode (broadcasting), we'll use a workaround with GATT server
        
        // Create a custom service UUID for digital ID verification
        const serviceUUID = '12345678-1234-1234-1234-123456789abc';
        const characteristicUUID = '12345678-1234-1234-1234-123456789abd';
        
        // Encode verification URL as Uint8Array
        const encoder = new TextEncoder();
        const urlData = encoder.encode(verificationUrl);
        
        // Try to use Web Bluetooth API to advertise/share the verification URL
        // Since Web Bluetooth doesn't fully support peripheral mode, we'll use a different approach:
        // Copy the verification URL and show instructions for verifier to connect
        
        // For now, copy URL to clipboard and show instructions
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(verificationUrl);
            alert('BLE Verification URL copied!\n\nVerification URL has been copied to your clipboard.\n\nFor BLE verification:\n1. Ensure Bluetooth is enabled\n2. Share this URL with the verifier device\n3. Verifier can scan for BLE devices or use the URL directly\n\nNote: Full BLE broadcasting requires native app support.\nFor web-based verification, QR codes are recommended.');
        } else {
            // Fallback: show URL in prompt
            prompt('BLE Verification URL (copy this):', verificationUrl);
            alert('For BLE verification:\n1. Ensure Bluetooth is enabled\n2. Share this URL with the verifier device\n3. Verifier can scan for BLE devices or use the URL directly\n\nNote: Full BLE broadcasting requires native app support.\nFor web-based verification, QR codes are recommended.');
        }
        
        // Future enhancement: Use Web Bluetooth Advertising API when available
        // This would allow true BLE broadcasting, but has very limited browser support
        
    } catch (err) {
        console.error('BLE error:', err);
        if (err.name === 'NotFoundError') {
            alert('Bluetooth device not found.\n\nPlease ensure:\n1. Bluetooth is enabled on your device\n2. Your device supports Bluetooth Low Energy (BLE)\n3. You have granted Bluetooth permissions');
        } else if (err.name === 'SecurityError' || err.name === 'NotAllowedError') {
            alert('Bluetooth permission denied.\n\nPlease:\n1. Allow Bluetooth access in your browser settings\n2. Ensure Bluetooth is enabled on your device\n3. Grant location permission if prompted (required for Bluetooth on some devices)');
        } else {
            alert('BLE error: ' + err.message + '\n\nPlease use QR code for verification instead, which works on all devices and browsers.');
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


<?php
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();

$sessionId = (int)($_GET['session_id'] ?? 0);
$error = '';
$success = '';

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';
require_once SRC_PATH . '/models/Employee.php';
require_once SRC_PATH . '/classes/DigitalID.php';

// Get current employee
$employee = Employee::findByUserId(Auth::getUserId());
if (!$employee) {
    header('Location: ' . url('index.php'));
    exit;
}

// Get session
if ($sessionId) {
    $session = CheckInSession::findById($sessionId);
    if (!$session || $session['organisation_id'] != Auth::getOrganisationId()) {
        header('Location: ' . url('check-in.php'));
        exit;
    }
} else {
    header('Location: ' . url('check-in.php'));
    exit;
}

// Get ID card and QR token
$idCard = DigitalID::getOrCreateIdCard($employee['id']);
$qrToken = $idCard['qr_token'] ?? null;

$pageTitle = 'Check In with QR Code';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="card">
    <h1>Check In: <?php echo htmlspecialchars($session['session_name']); ?></h1>
    
    <p>Scan your QR code below to check in automatically:</p>
    
    <?php if ($qrToken): ?>
        <div style="text-align: center; margin: 2rem 0;">
            <?php
            require_once SRC_PATH . '/classes/QRCodeGenerator.php';
            $qrImageUrl = QRCodeGenerator::generateImageUrl($qrToken);
            ?>
            <img src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="QR Code" style="max-width: 300px; border: 2px solid #e5e7eb; padding: 1rem; background: white;">
            <p style="margin-top: 1rem; color: #6b7280;">
                <i class="fas fa-mobile-alt"></i> Point your camera at this QR code to scan
            </p>
        </div>
        
        <div style="text-align: center; margin: 2rem 0;">
            <form method="POST" action="<?php echo url('api/check-in.php'); ?>" id="check-in-form">
                <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($qrToken); ?>">
                <input type="hidden" name="method" value="qr_scan">
                <button type="submit" class="btn btn-primary btn-lg" id="check-in-btn" style="display: none;">
                    <i class="fas fa-check"></i> Check In Now
                </button>
            </form>
        </div>
        
        <script>
        // Auto-submit on page load (for QR scanner apps that redirect)
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we're coming from a QR scan redirect
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto') === 'true') {
                document.getElementById('check-in-form').submit();
            } else {
                // Show manual button as fallback
                document.getElementById('check-in-btn').style.display = 'inline-block';
            }
        });
        </script>
    <?php else: ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> Unable to generate QR code. Please try manual check-in.
        </div>
        <a href="<?php echo url('check-in.php'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Check-In
        </a>
    <?php endif; ?>
    
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
        <a href="<?php echo url('check-in.php'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Check-In
        </a>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>


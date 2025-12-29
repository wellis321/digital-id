<?php
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';
require_once SRC_PATH . '/models/Employee.php';

// Get current employee
$employee = Employee::findByUserId(Auth::getUserId());
if (!$employee) {
    header('Location: ' . url('index.php'));
    exit;
}

// Get active sessions
$activeSessions = CheckInService::getActiveSessions($organisationId);

// Handle check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_in') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $token = $_POST['token'] ?? null;
        $locationLat = isset($_POST['location_lat']) ? (float)$_POST['location_lat'] : null;
        $locationLng = isset($_POST['location_lng']) ? (float)$_POST['location_lng'] : null;
        
        if (!$sessionId) {
            $error = 'Please select a session.';
        } else {
            // Get device info
            $deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $result = CheckInService::checkIn(
                $sessionId,
                $token,
                $employee['employee_reference'],
                $organisationId,
                $token ? 'qr_scan' : 'manual',
                $locationLat,
                $locationLng,
                $deviceInfo
            );
            
            if ($result['success']) {
                $success = 'Checked in successfully!';
                // Refresh active sessions
                $activeSessions = CheckInService::getActiveSessions($organisationId);
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_out') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        
        if (!$sessionId) {
            $error = 'Session ID is required.';
        } else {
            $result = CheckInService::checkOut($sessionId, $employee['id']);
            
            if ($result['success']) {
                $success = 'Checked out successfully!';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get check-in status for each active session
$checkInStatus = [];
foreach ($activeSessions as $session) {
    $checkIn = CheckInService::getActiveCheckIn($session['id'], $employee['id']);
    $checkInStatus[$session['id']] = $checkIn ? true : false;
}

$pageTitle = 'Check In';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="card">
    <h1>Check In</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (empty($activeSessions)): ?>
        <p>There are no active check-in sessions at this time.</p>
    <?php else: ?>
        <p>Select a session below to check in or check out:</p>
        
        <div class="check-in-sessions">
            <?php foreach ($activeSessions as $session): ?>
                <div class="card" style="margin-bottom: 1rem;">
                    <h3><?php echo htmlspecialchars($session['session_name']); ?></h3>
                    <p>
                        <strong>Type:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $session['session_type']))); ?><br>
                        <strong>Location:</strong> <?php echo htmlspecialchars($session['location_name'] ?? 'N/A'); ?><br>
                        <strong>Started:</strong> <?php echo date('d/m/Y H:i', strtotime($session['started_at'])); ?>
                    </p>
                    
                    <?php if (isset($checkInStatus[$session['id']]) && $checkInStatus[$session['id']]): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle"></i> You are currently checked in to this session.
                        </div>
                        <form method="POST" action="" style="margin-top: 1rem;">
                            <?php echo CSRF::tokenField(); ?>
                            <input type="hidden" name="action" value="check_out">
                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="" style="margin-top: 1rem;">
                            <?php echo CSRF::tokenField(); ?>
                            <input type="hidden" name="action" value="check_in">
                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Check In
                            </button>
                        </form>
                        <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <a href="<?php echo url('check-in-qr.php?session_id=' . $session['id']); ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-qrcode"></i> Check In with QR Code
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>


<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

// Handle session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $sessionName = trim($_POST['session_name'] ?? '');
        $sessionType = $_POST['session_type'] ?? '';
        $locationName = trim($_POST['location_name'] ?? '');
        $locationId = !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null;
        
        if (empty($sessionName) || empty($sessionType)) {
            $error = 'Session name and type are required.';
        } else {
            $result = CheckInService::createSession(
                $organisationId,
                $sessionName,
                $sessionType,
                Auth::getUserId(),
                $locationName ?: null,
                $locationId,
                null
            );
            
            if ($result['success']) {
                $success = 'Check-in session created successfully.';
                // Trigger Microsoft 365 sync if enabled
                require_once SRC_PATH . '/classes/Microsoft365Integration.php';
                Microsoft365Integration::syncSessionToSharePoint($organisationId, $result['session']);
                Microsoft365Integration::triggerPowerAutomate($organisationId, 'session.created', $result['session']);
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle session end
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'end') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        if ($sessionId) {
            $result = CheckInService::endSession($sessionId);
            if ($result['success']) {
                $success = 'Session ended successfully.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all sessions
$sessions = CheckInSession::findAll($organisationId, 100, 0);
$activeSessions = CheckInService::getActiveSessions($organisationId);

$pageTitle = 'Check-In Sessions';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Check-In Sessions</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div style="margin-bottom: 2rem;">
        <a href="<?php echo url('admin/check-in-sessions/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Session
        </a>
    </div>
    
    <?php if (!empty($activeSessions)): ?>
        <h2>Active Sessions</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Started</th>
                        <th>Started By</th>
                        <th>Check-Ins</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeSessions as $session): ?>
                        <tr>
                            <td>
                                <a href="<?php echo url('admin/check-in-sessions/view.php?id=' . $session['id']); ?>">
                                    <?php echo htmlspecialchars($session['session_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $session['session_type']))); ?></td>
                            <td><?php echo htmlspecialchars($session['location_name'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($session['started_at'])); ?></td>
                            <td><?php echo htmlspecialchars(($session['started_by_first_name'] ?? '') . ' ' . ($session['started_by_last_name'] ?? '')); ?></td>
                            <td><?php echo CheckInSession::getCheckInCount($session['id']); ?></td>
                            <td>
                                <a href="<?php echo url('admin/check-in-sessions/view.php?id=' . $session['id']); ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" action="" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to end this session?');">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="end">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-stop"></i> End
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <h2>All Sessions</h2>
    <?php if (empty($sessions)): ?>
        <p>No check-in sessions found. Create your first session to get started.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Started</th>
                        <th>Ended</th>
                        <th>Started By</th>
                        <th>Check-Ins</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td>
                                <a href="<?php echo url('admin/check-in-sessions/view.php?id=' . $session['id']); ?>">
                                    <?php echo htmlspecialchars($session['session_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $session['session_type']))); ?></td>
                            <td><?php echo htmlspecialchars($session['location_name'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($session['started_at'])); ?></td>
                            <td><?php echo $session['ended_at'] ? date('d/m/Y H:i', strtotime($session['ended_at'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars(($session['started_by_first_name'] ?? '') . ' ' . ($session['started_by_last_name'] ?? '')); ?></td>
                            <td><?php echo CheckInSession::getCheckInCount($session['id']); ?></td>
                            <td>
                                <?php if ($session['ended_at']): ?>
                                    <span class="badge badge-secondary">Ended</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo url('admin/check-in-sessions/view.php?id=' . $session['id']); ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$sessionId = (int)($_GET['id'] ?? 0);

if (!$sessionId) {
    header('Location: ' . url('admin/check-in-sessions.php'));
    exit;
}

require_once SRC_PATH . '/classes/CheckInService.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

$session = CheckInSession::findById($sessionId);

if (!$session || $session['organisation_id'] != $organisationId) {
    header('Location: ' . url('admin/check-in-sessions.php'));
    exit;
}

$checkIns = CheckInService::getSessionCheckIns($sessionId);
$checkInCount = count($checkIns);

$pageTitle = 'View Check-In Session';
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <h1><?php echo htmlspecialchars($session['session_name']); ?></h1>
    
    <div style="margin-bottom: 2rem;">
        <a href="<?php echo url('admin/check-in-sessions.php'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sessions
        </a>
        <?php if (!$session['ended_at']): ?>
            <form method="POST" action="<?php echo url('admin/check-in-sessions.php'); ?>" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to end this session?');">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="end">
                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-stop"></i> End Session
                </button>
            </form>
        <?php endif; ?>
        <a href="<?php echo url('admin/check-in-sessions/export.php?id=' . $sessionId); ?>" class="btn btn-primary">
            <i class="fas fa-download"></i> Export Attendance
        </a>
    </div>
    
    <div class="card" style="margin-bottom: 2rem;">
        <h2>Session Details</h2>
        <table class="info-table">
            <tr>
                <th>Session Name:</th>
                <td><?php echo htmlspecialchars($session['session_name']); ?></td>
            </tr>
            <tr>
                <th>Type:</th>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $session['session_type']))); ?></td>
            </tr>
            <tr>
                <th>Location:</th>
                <td><?php echo htmlspecialchars($session['location_name'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Started:</th>
                <td><?php echo date('d/m/Y H:i:s', strtotime($session['started_at'])); ?></td>
            </tr>
            <tr>
                <th>Ended:</th>
                <td><?php echo $session['ended_at'] ? date('d/m/Y H:i:s', strtotime($session['ended_at'])) : 'Active'; ?></td>
            </tr>
            <tr>
                <th>Started By:</th>
                <td><?php echo htmlspecialchars(($session['started_by_first_name'] ?? '') . ' ' . ($session['started_by_last_name'] ?? '')); ?></td>
            </tr>
            <tr>
                <th>Total Check-Ins:</th>
                <td><?php echo $checkInCount; ?></td>
            </tr>
            <?php if ($session['microsoft_365_synced']): ?>
                <tr>
                    <th>Microsoft 365 Sync:</th>
                    <td>
                        <span class="badge badge-success">Synced</span>
                        <?php if ($session['sharepoint_list_id']): ?>
                            <br><small>SharePoint List ID: <?php echo htmlspecialchars($session['sharepoint_list_id']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="card">
        <h2>Check-Ins (<?php echo $checkInCount; ?>)</h2>
        
        <?php if (empty($checkIns)): ?>
            <p>No check-ins yet. Staff can check in using their Digital ID.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Reference</th>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Method</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkIns as $checkIn): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($checkIn['first_name'] ?? '') . ' ' . ($checkIn['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($checkIn['display_reference'] ?? $checkIn['employee_reference'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($checkIn['checked_in_at'])); ?></td>
                                <td><?php echo $checkIn['checked_out_at'] ? date('d/m/Y H:i:s', strtotime($checkIn['checked_out_at'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    $method = $checkIn['check_in_method'] ?? 'manual';
                                    echo htmlspecialchars(ucfirst(str_replace('_', ' ', $method)));
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($checkIn['location_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($checkIn['checked_out_at']): ?>
                                        <span class="badge badge-secondary">Checked Out</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Checked In</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$session['ended_at']): ?>
<script>
// Auto-refresh check-ins every 10 seconds for active sessions
setInterval(function() {
    location.reload();
}, 10000);
</script>
<?php endif; ?>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


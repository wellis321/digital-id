<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

require_once SRC_PATH . '/classes/CheckInService.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                header('Location: ' . url('admin/check-in-sessions/view.php?id=' . $result['session']['id']));
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

$pageTitle = 'Create Check-In Session';
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <h1>Create Check-In Session</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="create">
        
        <div class="form-group">
            <label for="session_name">Session Name <span class="required">*</span></label>
            <input type="text" id="session_name" name="session_name" 
                   value="<?php echo htmlspecialchars($_POST['session_name'] ?? ''); ?>" 
                   required placeholder="e.g., Fire Drill - Main Building">
            <small>Enter a descriptive name for this check-in session</small>
        </div>
        
        <div class="form-group">
            <label for="session_type">Session Type <span class="required">*</span></label>
            <select id="session_type" name="session_type" required>
                <option value="">Select a type...</option>
                <option value="fire_drill" <?php echo (($_POST['session_type'] ?? '') === 'fire_drill') ? 'selected' : ''; ?>>Fire Drill</option>
                <option value="fire_alarm" <?php echo (($_POST['session_type'] ?? '') === 'fire_alarm') ? 'selected' : ''; ?>>Fire Alarm</option>
                <option value="safety_meeting" <?php echo (($_POST['session_type'] ?? '') === 'safety_meeting') ? 'selected' : ''; ?>>Safety Meeting</option>
                <option value="emergency" <?php echo (($_POST['session_type'] ?? '') === 'emergency') ? 'selected' : ''; ?>>Emergency</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="location_name">Location</label>
            <input type="text" id="location_name" name="location_name" 
                   value="<?php echo htmlspecialchars($_POST['location_name'] ?? ''); ?>" 
                   placeholder="e.g., Main Building, Floor 2">
            <small>Optional location description for this session</small>
        </div>
        
        <div class="form-group">
            <label for="location_id">Location ID</label>
            <input type="number" id="location_id" name="location_id" 
                   value="<?php echo htmlspecialchars($_POST['location_id'] ?? ''); ?>" 
                   placeholder="Optional location ID">
            <small>Optional location ID if using organisational units</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Session
            </button>
            <a href="<?php echo url('admin/check-in-sessions.php'); ?>" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


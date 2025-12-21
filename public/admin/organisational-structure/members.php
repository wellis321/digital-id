<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get organisation details
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
$stmt->execute([$organisationId]);
$org = $stmt->fetch();

if (!$org) {
    die('Organisation not found.');
}

// Get unit ID
$unitId = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;
$unit = $unitId > 0 ? OrganisationalUnits::findById($unitId) : null;

if (!$unit || $unit['organisation_id'] != $organisationId) {
    die('Unit not found.');
}

// Handle add member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $userId = intval($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'member';
        
        if ($userId <= 0) {
            $error = 'Please select a user.';
        } else {
            $result = OrganisationalUnits::addMember($unitId, $userId, $role);
            if ($result['success']) {
                $success = 'Member added successfully.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle remove member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            $error = 'Invalid user.';
        } else {
            $result = OrganisationalUnits::removeMember($unitId, $userId);
            if ($result['success']) {
                $success = 'Member removed successfully.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle update role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $userId = intval($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'member';
        
        if ($userId <= 0) {
            $error = 'Invalid user.';
        } else {
            $result = OrganisationalUnits::updateMemberRole($unitId, $userId, $role);
            if ($result['success']) {
                $success = 'Member role updated successfully.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get current members
$members = OrganisationalUnits::getMembers($unitId);

// Get available users from organisation
$stmt = $db->prepare("
    SELECT id, first_name, last_name, email 
    FROM users 
    WHERE organisation_id = ? AND is_active = TRUE AND email_verified = TRUE
    ORDER BY last_name, first_name
");
$stmt->execute([$organisationId]);
$allUsers = $stmt->fetchAll();

// Filter out users already in this unit
$memberIds = array_column($members, 'user_id');
$availableUsers = array_filter($allUsers, function($u) use ($memberIds) {
    return !in_array($u['id'], $memberIds);
});

$pageTitle = 'Manage Members - ' . $unit['name'];
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Manage Members</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                <?php echo htmlspecialchars($unit['name']); ?>
                <?php if (!empty($unit['unit_type'])): ?>
                    <span style="padding: 0.25rem 0.5rem; background-color: #e5e7eb; border-radius: 4px; font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">
                        <?php echo htmlspecialchars($unit['unit_type']); ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Add Member Form -->
    <?php if (!empty($availableUsers)): ?>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Add Staff Member</h3>
            <form method="POST" action="" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="user_id">Staff Member <span style="color: #dc2626;">*</span></label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Select a staff member...</option>
                        <?php foreach ($availableUsers as $availUser): ?>
                            <option value="<?php echo $availUser['id']; ?>">
                                <?php echo htmlspecialchars($availUser['first_name'] . ' ' . $availUser['last_name']); ?>
                                (<?php echo htmlspecialchars($availUser['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="role">Role <span style="color: #dc2626;">*</span></label>
                    <select id="role" name="role" required>
                        <option value="member">Member</option>
                        <option value="lead">Lead</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Member</button>
            </form>
        </div>
    <?php else: ?>
        <div style="background-color: #e0f2fe; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
            <p style="margin: 0; color: #1e40af; font-size: 0.875rem;">
                All staff members in this organisation are already assigned to this unit.
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Current Members List -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
        <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">
            Current Members (<?php echo count($members); ?>)
        </h3>
        
        <?php if (empty($members)): ?>
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-users" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <p style="color: #6b7280;">No members assigned to this unit yet.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 0.75rem;">
                <?php foreach ($members as $member): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background-color: #f9fafb; border-radius: 4px;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 500; color: #1f2937;">
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </h4>
                            <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: #6b7280;">
                                <?php echo htmlspecialchars($member['email']); ?>
                            </p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; align-items: center;">
                                <span style="padding: 0.25rem 0.5rem; background-color: #dbeafe; border-radius: 4px; font-size: 0.75rem; color: #1e40af;">
                                    Role: <?php echo htmlspecialchars(ucfirst($member['role'])); ?>
                                </span>
                                <?php if (!empty($member['joined_at'])): ?>
                                    <span style="font-size: 0.75rem; color: #9ca3af;">
                                        Joined <?php echo date('d/m/Y', strtotime($member['joined_at'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <form method="POST" action="" style="display: inline;">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">
                                <select name="role" 
                                        onchange="this.form.submit()"
                                        style="padding: 0.375rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db; border-radius: 4px;">
                                    <option value="member" <?php echo $member['role'] === 'member' ? 'selected' : ''; ?>>Member</option>
                                    <option value="lead" <?php echo $member['role'] === 'lead' ? 'selected' : ''; ?>>Lead</option>
                                    <option value="admin" <?php echo $member['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </form>
                            <form method="POST" action="" style="display: inline;"
                                  onsubmit="return confirm('Are you sure you want to remove this member from the unit?');">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


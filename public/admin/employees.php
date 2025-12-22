<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

// Require authentication - this will redirect and exit if not logged in
Auth::requireLogin();
RBAC::requireAdmin();

// Double-check authentication (security measure)
if (!Auth::isLoggedIn() || !RBAC::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit;
}

// Superadmins don't manage employees - they manage organisations
if (RBAC::isSuperAdmin()) {
    header('Location: ' . url('admin/organisations.php'));
    exit;
}

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get users needing employee numbers (only for organisation admins)
require_once dirname(__DIR__, 2) . '/src/classes/AdminNotifications.php';
$usersNeedingEmployeeNumbers = [];
$countUsersNeedingNumbers = 0;
if ($organisationId) {
    $usersNeedingEmployeeNumbers = AdminNotifications::getUsersNeedingEmployeeNumbers($organisationId);
    $countUsersNeedingNumbers = count($usersNeedingEmployeeNumbers);
}

// Handle employee creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $userId = $_POST['user_id'] ?? '';
        $employeeNumber = trim($_POST['employee_number'] ?? '');
        $displayReference = trim($_POST['display_reference'] ?? '');
        
        if (empty($userId) || empty($employeeNumber)) {
            $error = 'User ID and employee number are required.';
        } else {
            // If display reference is empty, it will be auto-generated
            $result = Employee::create($userId, $organisationId, $employeeNumber, $displayReference ?: null);
            if ($result['success']) {
                $success = 'Employee created successfully.';
                if (isset($result['display_reference']) && empty($displayReference)) {
                    $success .= ' Display reference "' . htmlspecialchars($result['display_reference']) . '" was automatically generated.';
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all employees
$employees = Employee::getByOrganisation($organisationId);

// Get all users in organisation for dropdown
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email 
    FROM users u 
    WHERE u.organisation_id = ? 
    AND u.email_verified = TRUE 
    AND u.is_active = TRUE
    AND u.id NOT IN (SELECT user_id FROM employees WHERE organisation_id = ?)
    ORDER BY u.last_name, u.first_name
");
$stmt->execute([$organisationId, $organisationId]);
$availableUsers = $stmt->fetchAll();

$pageTitle = 'Manage Employees';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin: 0;">Manage Employees</h1>
        <a href="<?php echo url('admin/users-import.php'); ?>" class="btn btn-primary">
            <i class="fas fa-upload"></i> Bulk Import Users
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($countUsersNeedingNumbers > 0): ?>
    <!-- Users Needing Employee Numbers Notification -->
    <div style="background: #f0f9ff; border-left: 3px solid #3b82f6; padding: 1rem; border-radius: 0; margin-bottom: 1.5rem;">
        <p style="margin: 0; color: #1e40af; font-size: 0.9375rem;">
            <i class="fas fa-info-circle"></i> <strong><?php echo $countUsersNeedingNumbers; ?> verified user<?php echo $countUsersNeedingNumbers !== 1 ? 's' : ''; ?> need<?php echo $countUsersNeedingNumbers === 1 ? 's' : ''; ?> employee records created.</strong> Use the form below to create employee records for them.
        </p>
    </div>
    <?php endif; ?>
    
    <h2>Create New Employee</h2>
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="create">
        
        <div class="form-group">
            <label for="user_id">User</label>
            <select id="user_id" name="user_id" required>
                <option value="">Select a user...</option>
                <?php foreach ($availableUsers as $user): ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="employee_number">Employee Number <span style="color: #dc2626;">*</span></label>
            <input type="text" id="employee_number" name="employee_number" required>
            <small><i class="fas fa-info-circle"></i> <strong>This is the employee number from your HR or payroll system.</strong> This number cannot be changed after creation and is used internally for system integration. It will not be displayed on the digital ID card.</small>
        </div>
        
        <div class="form-group">
            <label for="display_reference">Display Reference</label>
            <input type="text" id="display_reference" name="display_reference">
            <small><i class="fas fa-info-circle"></i> <strong>Optional:</strong> The reference shown on the digital ID card. If left blank, a reference will be automatically generated based on your organisation's reference format settings. This reference must be unique within your organisation.</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Create Employee</button>
    </form>
</div>

<div class="card">
    <h2>Existing Employees</h2>
    
    <?php if (empty($employees)): ?>
        <p>No employees found.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 0.75rem; text-align: left;">Name</th>
                    <th style="padding: 0.75rem; text-align: left;">Email</th>
                    <th style="padding: 0.75rem; text-align: left;">Employee Reference</th>
                    <th style="padding: 0.75rem; text-align: left;">Status</th>
                    <th style="padding: 0.75rem; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($employee['email']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($employee['employee_number'] ?? $employee['employee_reference'] ?? 'N/A'); ?>
                            <small style="display: block; color: #6b7280; font-size: 0.75rem;">(From HR/Payroll)</small>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($employee['display_reference'] ?? $employee['employee_reference'] ?? 'N/A'); ?>
                            <small style="display: block; color: #6b7280; font-size: 0.75rem;">(Shown on ID Card)</small>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo $employee['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>'; ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="<?php echo url('admin/employees-edit.php?id=' . $employee['id']); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?php echo url('id-card.php?employee_id=' . $employee['id']); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;" target="_blank">
                                    <i class="fas fa-id-card"></i> View ID Card
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


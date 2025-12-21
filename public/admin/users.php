<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin();

$error = '';
$success = '';

// Handle user activation/deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            $error = 'Invalid user ID.';
        } else {
            $db = getDbConnection();
            
            if ($_POST['action'] === 'activate') {
                // Activate user and verify email
                $stmt = $db->prepare("
                    UPDATE users 
                    SET is_active = TRUE, 
                        email_verified = TRUE,
                        verification_token = NULL,
                        verification_token_expires_at = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$userId]);
                $success = 'User activated successfully.';
            } elseif ($_POST['action'] === 'deactivate') {
                // Deactivate user
                $stmt = $db->prepare("UPDATE users SET is_active = FALSE WHERE id = ?");
                $stmt->execute([$userId]);
                $success = 'User deactivated successfully.';
            } elseif ($_POST['action'] === 'verify_email') {
                // Verify email without activating
                $stmt = $db->prepare("
                    UPDATE users 
                    SET email_verified = TRUE,
                        verification_token = NULL,
                        verification_token_expires_at = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$userId]);
                $success = 'Email verified successfully.';
            } elseif ($_POST['action'] === 'assign_admin') {
                // Assign organisation admin role
                $result = RBAC::assignRole($userId, 'organisation_admin');
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            } elseif ($_POST['action'] === 'remove_admin') {
                // Remove organisation admin role
                $result = RBAC::removeRole($userId, 'organisation_admin');
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$organisationId = isset($_GET['organisation_id']) ? intval($_GET['organisation_id']) : null;
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$db = getDbConnection();
$where = [];
$params = [];

if ($search) {
    $where[] = "(u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($organisationId) {
    $where[] = "u.organisation_id = ?";
    $params[] = $organisationId;
}

if ($statusFilter === 'active') {
    $where[] = "u.is_active = TRUE AND u.email_verified = TRUE";
} elseif ($statusFilter === 'inactive') {
    $where[] = "u.is_active = FALSE";
} elseif ($statusFilter === 'unverified') {
    $where[] = "u.email_verified = FALSE";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $db->prepare("
    SELECT u.*, 
           o.name as organisation_name,
           GROUP_CONCAT(r.name SEPARATOR ', ') as roles,
           CASE WHEN EXISTS (
               SELECT 1 FROM user_roles ur2 
               JOIN roles r2 ON ur2.role_id = r2.id 
               WHERE ur2.user_id = u.id AND r2.name = 'organisation_admin'
           ) THEN 1 ELSE 0 END as is_organisation_admin
    FROM users u
    LEFT JOIN organisations o ON u.organisation_id = o.id
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    $whereClause
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get all organisations for filter
$stmt = $db->prepare("SELECT id, name FROM organisations ORDER BY name");
$stmt->execute();
$organisations = $stmt->fetchAll();

$pageTitle = 'Manage Users';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Manage Users</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Search and Filter -->
    <form method="GET" action="" style="margin-bottom: 2rem; display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Email, name...">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="organisation_id">Organisation</label>
            <select id="organisation_id" name="organisation_id">
                <option value="">All Organisations</option>
                <?php foreach ($organisations as $org): ?>
                    <option value="<?php echo $org['id']; ?>" <?php echo ($organisationId == $org['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($org['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="all" <?php echo ($statusFilter === 'all') ? 'selected' : ''; ?>>All</option>
                <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($statusFilter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="unverified" <?php echo ($statusFilter === 'unverified') ? 'selected' : ''; ?>>Unverified</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    
    <!-- Users Table -->
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 0.75rem; text-align: left;">Name</th>
                    <th style="padding: 0.75rem; text-align: left;">Email</th>
                    <th style="padding: 0.75rem; text-align: left;">Organisation</th>
                    <th style="padding: 0.75rem; text-align: left;">Roles</th>
                    <th style="padding: 0.75rem; text-align: left;">Status</th>
                    <th style="padding: 0.75rem; text-align: left;">Created</th>
                    <th style="padding: 0.75rem; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem;">
                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($user['organisation_name'] ?? 'N/A'); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php
                            $roles = $user['roles'] ? explode(', ', $user['roles']) : [];
                            // Filter out 'staff' if user has admin roles (cleaner display)
                            $displayRoles = [];
                            $hasAdminRole = false;
                            foreach ($roles as $role) {
                                $role = trim($role);
                                if (in_array($role, ['superadmin', 'organisation_admin'])) {
                                    $hasAdminRole = true;
                                    $displayRoles[] = $role;
                                } elseif ($role === 'staff' && !$hasAdminRole) {
                                    // Only show staff if no admin role
                                    $displayRoles[] = $role;
                                } elseif ($role !== 'staff') {
                                    // Show other roles
                                    $displayRoles[] = $role;
                                }
                            }
                            
                            if (empty($displayRoles)) {
                                echo '<span style="color: #6b7280;">No roles</span>';
                            } else {
                                // Format role names nicely
                                $formattedRoles = array_map(function($role) {
                                    $role = trim($role);
                                    // Convert snake_case to Title Case
                                    $formatted = str_replace('_', ' ', $role);
                                    $formatted = ucwords(strtolower($formatted));
                                    return $formatted;
                                }, $displayRoles);
                                
                                // Add badges for better visual distinction
                                foreach ($formattedRoles as $index => $formattedRole) {
                                    $badgeColor = 'primary';
                                    if (stripos($roles[$index], 'superadmin') !== false) {
                                        $badgeColor = 'danger';
                                    } elseif (stripos($roles[$index], 'admin') !== false) {
                                        $badgeColor = 'warning';
                                    }
                                    echo '<span class="badge badge-' . $badgeColor . '" style="display: inline-block; padding: 0.25rem 0.5rem; margin-right: 0.25rem; margin-bottom: 0.25rem; background-color: ' . ($badgeColor === 'danger' ? '#dc2626' : ($badgeColor === 'warning' ? '#f59e0b' : '#2563eb')) . '; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">' . htmlspecialchars($formattedRole) . '</span>';
                                }
                            }
                            ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php if ($user['is_active'] && $user['email_verified']): ?>
                                <span style="color: #059669; font-weight: 600;">Active</span>
                            <?php elseif (!$user['email_verified']): ?>
                                <span style="color: #dc2626; font-weight: 600;">Unverified</span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 600;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td style="padding: 0.75rem; white-space: nowrap; min-width: 200px;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <?php if (!$user['is_active'] || !$user['email_verified']): ?>
                                    <form method="POST" action="" style="margin: 0;">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">Activate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="" style="margin: 0;">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">Deactivate</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (!$user['email_verified']): ?>
                                    <form method="POST" action="" style="margin: 0;">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="verify_email">
                                        <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">Verify Email</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($user['is_active'] && $user['email_verified']): ?>
                                    <?php if ($user['is_organisation_admin']): ?>
                                        <form method="POST" action="" style="margin: 0;">
                                            <?php echo CSRF::tokenField(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="remove_admin">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">
                                                <i class="fas fa-user-minus"></i> Remove Admin
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="" style="margin: 0;">
                                            <?php echo CSRF::tokenField(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="assign_admin">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">
                                                <i class="fas fa-user-plus"></i> Make Admin
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin();

$error = '';
$success = '';

// Handle organisation creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $seatsAllocated = intval($_POST['seats_allocated'] ?? 0);
        
        if (empty($name) || empty($domain)) {
            $error = 'Organisation name and domain are required.';
        } elseif ($seatsAllocated < 0) {
            $error = 'Seats allocated must be a positive number.';
        } else {
            $db = getDbConnection();
            
            // Check if domain already exists
            $stmt = $db->prepare("SELECT id FROM organisations WHERE domain = ?");
            $stmt->execute([$domain]);
            if ($stmt->fetch()) {
                $error = 'An organisation with this domain already exists.';
            } else {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO organisations (name, domain, seats_allocated) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$name, $domain, $seatsAllocated]);
                    $success = 'Organisation created successfully.';
                } catch (Exception $e) {
                    $error = 'Failed to create organisation: ' . $e->getMessage();
                }
            }
        }
    }
}

// Handle organisation update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $seatsAllocated = intval($_POST['seats_allocated'] ?? 0);
        
        if (empty($name) || empty($domain)) {
            $error = 'Organisation name and domain are required.';
        } elseif ($seatsAllocated < 0) {
            $error = 'Seats allocated must be a positive number.';
        } else {
            $db = getDbConnection();
            
            // Check if domain already exists for another organisation
            $stmt = $db->prepare("SELECT id FROM organisations WHERE domain = ? AND id != ?");
            $stmt->execute([$domain, $id]);
            if ($stmt->fetch()) {
                $error = 'An organisation with this domain already exists.';
            } else {
                try {
                    $stmt = $db->prepare("
                        UPDATE organisations 
                        SET name = ?, domain = ?, seats_allocated = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $domain, $seatsAllocated, $id]);
                    $success = 'Organisation updated successfully.';
                } catch (Exception $e) {
                    $error = 'Failed to update organisation: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get all organisations
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT o.*, 
           COUNT(DISTINCT u.id) as user_count,
           COUNT(DISTINCT CASE WHEN u.email_verified = TRUE AND u.is_active = TRUE THEN u.id END) as active_user_count
    FROM organisations o
    LEFT JOIN users u ON o.id = u.organisation_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$organisations = $stmt->fetchAll();

$pageTitle = 'Manage Organisations';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Manage Organisations</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <h2>Create New Organisation</h2>
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="create">
        
        <div class="form-group">
            <label for="name">Organisation Name</label>
            <input type="text" id="name" name="name" required>
            <small>Full name of the organisation</small>
        </div>
        
        <div class="form-group">
            <label for="domain">Domain</label>
            <input type="text" id="domain" name="domain" required placeholder="example.com">
            <small>Email domain for this organisation (e.g., example.com). Users with emails ending in @example.com will be associated with this organisation.</small>
        </div>
        
        <div class="form-group">
            <label for="seats_allocated">Seats Allocated</label>
            <input type="number" id="seats_allocated" name="seats_allocated" value="100" min="0" required>
            <small>Maximum number of active users allowed for this organisation</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Create Organisation</button>
    </form>
</div>

<div class="card">
    <h2>Existing Organisations</h2>
    
    <?php if (empty($organisations)): ?>
        <p>No organisations found.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 0.75rem; text-align: left;">Name</th>
                    <th style="padding: 0.75rem; text-align: left;">Domain</th>
                    <th style="padding: 0.75rem; text-align: left;">Seats</th>
                    <th style="padding: 0.75rem; text-align: left;">Active Users</th>
                    <th style="padding: 0.75rem; text-align: left;">Total Users</th>
                    <th style="padding: 0.75rem; text-align: left;">Created</th>
                    <th style="padding: 0.75rem; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($organisations as $org): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem;">
                            <strong><?php echo htmlspecialchars($org['name']); ?></strong>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($org['domain']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($org['seats_allocated']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <span style="color: <?php echo ($org['active_user_count'] >= $org['seats_allocated']) ? '#dc2626' : '#059669'; ?>;">
                                <?php echo htmlspecialchars($org['active_user_count']); ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo htmlspecialchars($org['user_count']); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <?php echo date('d/m/Y', strtotime($org['created_at'])); ?>
                        </td>
                        <td style="padding: 0.75rem;">
                            <button onclick="editOrganisation(<?php echo htmlspecialchars(json_encode($org)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; margin-right: 0.5rem;">Edit</button>
                            <button onclick="manageAdmins(<?php echo $org['id']; ?>)" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                <i class="fas fa-users-cog"></i> Manage Admins
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2>Edit Organisation</h2>
        <form method="POST" action="" id="editForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_name">Organisation Name</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_domain">Domain</label>
                <input type="text" id="edit_domain" name="domain" required>
            </div>
            
            <div class="form-group">
                <label for="edit_seats_allocated">Seats Allocated</label>
                <input type="number" id="edit_seats_allocated" name="seats_allocated" min="0" required>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Organisation</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editOrganisation(org) {
    document.getElementById('edit_id').value = org.id;
    document.getElementById('edit_name').value = org.name;
    document.getElementById('edit_domain').value = org.domain;
    document.getElementById('edit_seats_allocated').value = org.seats_allocated;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

function manageAdmins(organisationId) {
    document.getElementById('adminModal').style.display = 'flex';
    document.getElementById('admin_organisation_id').value = organisationId;
    loadOrganisationUsers(organisationId);
}

function closeAdminModal() {
    document.getElementById('adminModal').style.display = 'none';
}

function loadOrganisationUsers(organisationId) {
    // This would ideally use AJAX, but for simplicity we'll reload with a parameter
    window.location.href = '?org_id=' + organisationId + '#admins';
}

// Close admin modal when clicking outside
document.getElementById('adminModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAdminModal();
    }
});
</script>

<!-- Admin Management Modal -->
<div id="adminModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2>Manage Organisation Administrators</h2>
        
        <?php
        $selectedOrgId = $_GET['org_id'] ?? null;
        if ($selectedOrgId):
            $db = getDbConnection();
            
            // Get organisation details
            $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
            $stmt->execute([$selectedOrgId]);
            $selectedOrg = $stmt->fetch();
            
            if ($selectedOrg):
                // Get only organisation admins for this organisation
                $db = getDbConnection();
                $stmt = $db->prepare("
                    SELECT u.*, 
                           GROUP_CONCAT(r.name SEPARATOR ', ') as roles
                    FROM users u
                    JOIN user_roles ur ON u.id = ur.user_id
                    JOIN roles r ON ur.role_id = r.id
                    WHERE u.organisation_id = ? 
                    AND r.name = 'organisation_admin'
                    GROUP BY u.id
                    ORDER BY u.last_name, u.first_name
                ");
                $stmt->execute([$selectedOrgId]);
                $orgUsers = $stmt->fetchAll();
                
                // Mark all as admins since we're only showing admins
                foreach ($orgUsers as &$user) {
                    $user['is_admin'] = 1;
                }
                unset($user);
        ?>
                <p><strong>Organisation:</strong> <?php echo htmlspecialchars($selectedOrg['name']); ?></p>
                <p><strong>Domain:</strong> <?php echo htmlspecialchars($selectedOrg['domain']); ?></p>
                
                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Organisation Administrators</h3>
                <p style="margin-bottom: 1rem; color: #6b7280; font-size: 0.875rem;">
                    <i class="fas fa-info-circle"></i> Only organisation administrators are shown here. To assign or remove admin roles, use the <a href="<?php echo url('admin/users.php'); ?>" style="color: #2563eb;">Users</a> page.
                </p>
                
                <?php if (empty($orgUsers)): ?>
                    <p>No organisation administrators found for this organisation.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ddd;">
                                <th style="padding: 0.75rem; text-align: left;">Name</th>
                                <th style="padding: 0.75rem; text-align: left;">Email</th>
                                <th style="padding: 0.75rem; text-align: left;">Roles</th>
                                <th style="padding: 0.75rem; text-align: left;">Status</th>
                                <th style="padding: 0.75rem; text-align: left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orgUsers as $user): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 0.75rem;">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo htmlspecialchars($user['roles'] ?? 'No roles'); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php if ($user['is_active'] && $user['email_verified']): ?>
                                            <span style="color: #059669;">Active</span>
                                        <?php elseif (!$user['email_verified']): ?>
                                            <span style="color: #dc2626;">Unverified</span>
                                        <?php else: ?>
                                            <span style="color: #dc2626;">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <a href="<?php echo url('admin/users.php?search=' . urlencode($user['email'])); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            <i class="fas fa-edit"></i> Manage in Users
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
        <?php
            endif;
        endif;
        ?>
        
        <div style="margin-top: 2rem;">
            <button type="button" onclick="closeAdminModal()" class="btn btn-secondary">Close</button>
        </div>
        <input type="hidden" id="admin_organisation_id" value="">
    </div>
</div>

<?php if (isset($_GET['org_id'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    manageAdmins(<?php echo intval($_GET['org_id']); ?>);
});
</script>
<?php endif; ?>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


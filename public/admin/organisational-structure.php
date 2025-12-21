<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

// Require authentication - this will redirect and exit if not logged in
Auth::requireLogin();
RBAC::requireOrganisationAdmin();

// Double-check authentication (security measure)
if (!Auth::isLoggedIn() || !RBAC::isOrganisationAdmin()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = Auth::getOrganisationId();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Get users needing employee numbers
require_once dirname(__DIR__, 2) . '/src/classes/AdminNotifications.php';
$usersNeedingEmployeeNumbers = AdminNotifications::getUsersNeedingEmployeeNumbers($organisationId);
$countUsersNeedingNumbers = count($usersNeedingEmployeeNumbers);

// Get organisation details
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
$stmt->execute([$organisationId]);
$org = $stmt->fetch();

if (!$org) {
    die('Organisation not found.');
}

// Get hierarchy tree
$tree = OrganisationalUnits::getHierarchyTree($organisationId);
$allUnits = OrganisationalUnits::getAllByOrganisation($organisationId);

$pageTitle = 'Organisational Structure';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
        <div>
            <h1>Organisational Structure</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;"><?php echo htmlspecialchars($org['name']); ?></p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="<?php echo url('admin/organisational-structure/import.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-upload"></i> Import
            </a>
            <a href="<?php echo url('admin/organisational-structure/new.php'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Unit
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div style="background-color: #e0f2fe; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 0; margin-bottom: 2rem;">
        <h4 style="margin-top: 0; color: #1e40af; font-size: 1rem;">About Organisational Structure</h4>
        <p style="margin: 0; color: #1e40af; font-size: 0.875rem;">
            Define your organisation's hierarchy however you like. Create teams, departments, areas, regions, or any structure that fits your needs.
            You can have flat teams or nested hierarchies - it's completely flexible.
        </p>
    </div>
    
    <?php if (empty($allUnits)): ?>
        <!-- Empty State -->
        <div style="text-align: center; padding: 3rem; background-color: #f9fafb; border-radius: 0; border: 2px dashed #d1d5db;">
            <i class="fas fa-sitemap" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
            <h3 style="margin: 0 0 0.5rem; color: #1f2937;">No Organisational Units Yet</h3>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">Create your first unit to start building your organisational structure.</p>
            <a href="<?php echo url('admin/organisational-structure/new.php'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add First Unit
            </a>
        </div>
    <?php else: ?>
        <!-- Organisational Structure Tree -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 0; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.25rem;">Structure Overview (<?php echo count($allUnits); ?> units)</h3>
            
            <?php
            // Recursive function to render tree
            function renderUnitTree($units, $depth = 0) {
                if (empty($units)) return;
                
                foreach ($units as $unit): ?>
                    <div style="<?php echo $depth > 0 ? 'margin-left: 2rem; margin-top: 0.5rem;' : 'margin-top: 0.5rem;'; ?> border-left: <?php echo $depth > 0 ? '2px solid #d1d5db' : '2px solid #2563eb'; ?> padding-left: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: #f9fafb; border-radius: 0; margin-bottom: 0.5rem; transition: background-color 0.2s;" 
                             onmouseover="this.style.backgroundColor='#f3f4f6'" 
                             onmouseout="this.style.backgroundColor='#f9fafb'">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <h4 style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;">
                                        <?php echo htmlspecialchars($unit['name']); ?>
                                    </h4>
                                    <?php if (!empty($unit['unit_type'])): ?>
                                        <span style="padding: 0.25rem 0.5rem; background-color: #e5e7eb; border-radius: 0; font-size: 0.75rem; color: #6b7280;">
                                            <?php echo htmlspecialchars($unit['unit_type']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($unit['member_count'])): ?>
                                        <span style="font-size: 0.75rem; color: #6b7280;">
                                            <?php echo (int)$unit['member_count']; ?> member<?php echo (int)$unit['member_count'] !== 1 ? 's' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($unit['description'])): ?>
                                    <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($unit['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 0.5rem; opacity: 0.7;" 
                                 onmouseover="this.parentElement.parentElement.style.opacity='1'; this.style.opacity='1'" 
                                 onmouseout="this.parentElement.parentElement.style.opacity='1'; this.style.opacity='0.7'">
                                <a href="<?php echo url('admin/organisational-structure/members.php?unit_id=' . $unit['id']); ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                    <i class="fas fa-users"></i> Members
                                </a>
                                <a href="<?php echo url('admin/organisational-structure/edit.php?unit_id=' . $unit['id']); ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($unit['children'])): ?>
                            <?php renderUnitTree($unit['children'], $depth + 1); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach;
            }
            
            renderUnitTree($tree);
            ?>
        </div>
        
        <!-- Flat List View (Alternative) -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 0; padding: 1.5rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.25rem;">All Units (Flat List)</h3>
            <div style="display: grid; gap: 0.5rem;">
                <?php foreach ($allUnits as $unit): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: #f9fafb; border-radius: 0;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span style="font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($unit['name']); ?></span>
                                <?php if (!empty($unit['unit_type'])): ?>
                                    <span style="padding: 0.25rem 0.5rem; background-color: #e5e7eb; border-radius: 0; font-size: 0.75rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($unit['unit_type']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 1rem; margin-top: 0.25rem; font-size: 0.75rem; color: #6b7280;">
                                <?php if (!empty($unit['parent_name'])): ?>
                                    <span>Parent: <?php echo htmlspecialchars($unit['parent_name']); ?></span>
                                <?php else: ?>
                                    <span style="color: #2563eb;">Top Level</span>
                                <?php endif; ?>
                                <span><?php echo (int)$unit['member_count']; ?> member<?php echo (int)$unit['member_count'] !== 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="<?php echo url('admin/organisational-structure/edit.php?unit_id=' . $unit['id']); ?>" 
                               class="btn btn-secondary" 
                               style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


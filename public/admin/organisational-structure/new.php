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

// Get all existing units (for parent selection)
$existingUnits = OrganisationalUnits::getAllByOrganisation($organisationId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $unitType = trim($_POST['unit_type'] ?? '');
        $parentUnitId = !empty($_POST['parent_unit_id']) ? intval($_POST['parent_unit_id']) : null;
        $description = trim($_POST['description'] ?? '');
        $managerUserId = !empty($_POST['manager_user_id']) ? intval($_POST['manager_user_id']) : null;
        
        // Validation
        if (empty($name)) {
            $error = 'Unit name is required.';
        } else {
            $result = OrganisationalUnits::create(
                $organisationId,
                $name,
                !empty($unitType) ? $unitType : null,
                $parentUnitId,
                !empty($description) ? $description : null,
                $managerUserId
            );
            
            if ($result['success']) {
                $success = "Organisational unit '{$name}' created successfully.";
                // Refresh existing units
                $existingUnits = OrganisationalUnits::getAllByOrganisation($organisationId);
            } else {
                $error = $result['message'];
            }
        }
    }
}

$pageTitle = 'Add Organisational Unit';
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Add Organisational Unit</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;"><?php echo htmlspecialchars($org['name']); ?></p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div style="background-color: #e0f2fe; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
        <h4 style="margin-top: 0; color: #1e40af; font-size: 1rem;">Create Your Structure</h4>
        <p style="margin: 0.5rem 0 0; color: #1e40af; font-size: 0.875rem;">
            Define units however your organisation works. Examples:
        </p>
        <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #1e40af; font-size: 0.875rem;">
            <li><strong>Teams:</strong> "Morning Shift Team", "Mobile Response Team"</li>
            <li><strong>Departments:</strong> "Emergency Care", "Inpatient Services"</li>
            <li><strong>Areas/Regions:</strong> "North Area", "London Region"</li>
            <li><strong>Or your own terms:</strong> "Pod A", "Zone 3", "Care Hub"</li>
        </ul>
    </div>
    
    <!-- Form -->
    <form method="POST" action="" style="max-width: 600px;">
        <?php echo CSRF::tokenField(); ?>
        
        <!-- Name -->
        <div class="form-group">
            <label for="name">Unit Name <span style="color: #dc2626;">*</span></label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                   placeholder="e.g., North Team, Emergency Department, London Region">
            <small>What do you call this unit in your organisation?</small>
        </div>
        
        <!-- Unit Type (Optional) -->
        <div class="form-group">
            <label for="unit_type">Unit Type <span style="color: #6b7280;">(optional)</span></label>
            <input type="text" id="unit_type" name="unit_type" 
                   value="<?php echo htmlspecialchars($_POST['unit_type'] ?? ''); ?>"
                   placeholder="e.g., team, department, area, region, pod">
            <small>Category or type (you choose your own terms)</small>
        </div>
        
        <!-- Parent Unit (Optional) -->
        <div class="form-group">
            <label for="parent_unit_id">Parent Unit <span style="color: #6b7280;">(optional)</span></label>
            <select id="parent_unit_id" name="parent_unit_id">
                <option value="">None - Top Level Unit</option>
                <?php foreach ($existingUnits as $existingUnit): ?>
                    <option value="<?php echo $existingUnit['id']; ?>" 
                            <?php echo (isset($_POST['parent_unit_id']) && (int)$_POST['parent_unit_id'] === (int)$existingUnit['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($existingUnit['name']); ?>
                        <?php if (!empty($existingUnit['unit_type'])): ?>
                            (<?php echo htmlspecialchars($existingUnit['unit_type']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>
                <?php if (empty($existingUnits)): ?>
                    This will be a top-level unit (no parent)
                <?php else: ?>
                    Does this unit belong under another unit? Or leave as top-level.
                <?php endif; ?>
            </small>
        </div>
        
        <!-- Description (Optional) -->
        <div class="form-group">
            <label for="description">Description <span style="color: #6b7280;">(optional)</span></label>
            <textarea id="description" name="description" rows="3" 
                      placeholder="Brief description of this unit's purpose or responsibilities"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        
        <!-- Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">Create Unit</button>
            <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


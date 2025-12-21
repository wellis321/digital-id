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

// Get all existing units (for parent selection, excluding this unit)
$existingUnits = OrganisationalUnits::getAllByOrganisation($organisationId);
$existingUnits = array_filter($existingUnits, function($u) use ($unitId) {
    return (int)$u['id'] !== $unitId;
});

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $unitType = trim($_POST['unit_type'] ?? '');
        $parentUnitId = !empty($_POST['parent_unit_id']) ? intval($_POST['parent_unit_id']) : null;
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($name)) {
            $error = 'Unit name is required.';
        } elseif ($parentUnitId === $unitId) {
            $error = 'A unit cannot be its own parent.';
        } else {
            $result = OrganisationalUnits::update($unitId, [
                'name' => $name,
                'unit_type' => !empty($unitType) ? $unitType : null,
                'parent_unit_id' => $parentUnitId,
                'description' => !empty($description) ? $description : null,
            ]);
            
            if ($result['success']) {
                $success = "Unit '{$name}' updated successfully.";
                // Refresh unit data
                $unit = OrganisationalUnits::findById($unitId);
                $existingUnits = OrganisationalUnits::getAllByOrganisation($organisationId);
                $existingUnits = array_filter($existingUnits, function($u) use ($unitId) {
                    return (int)$u['id'] !== $unitId;
                });
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Check if unit can be deleted
$childCount = 0;
$memberCount = (int)($unit['member_count'] ?? 0);
foreach ($existingUnits as $eu) {
    if ((int)$eu['parent_unit_id'] === $unitId) {
        $childCount++;
    }
}

$pageTitle = 'Edit Organisational Unit';
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Edit Unit</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;"><?php echo htmlspecialchars($org['name']); ?></p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Form -->
    <form method="POST" action="" style="max-width: 600px;">
        <?php echo CSRF::tokenField(); ?>
        
        <!-- Name -->
        <div class="form-group">
            <label for="name">Unit Name <span style="color: #dc2626;">*</span></label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo htmlspecialchars($unit['name']); ?>"
                   placeholder="e.g., North Team, Emergency Department, London Region">
        </div>
        
        <!-- Unit Type (Optional) -->
        <div class="form-group">
            <label for="unit_type">Unit Type <span style="color: #6b7280;">(optional)</span></label>
            <input type="text" id="unit_type" name="unit_type" 
                   value="<?php echo htmlspecialchars($unit['unit_type'] ?? ''); ?>"
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
                            <?php echo (!empty($unit['parent_unit_id']) && (int)$unit['parent_unit_id'] === (int)$existingUnit['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($existingUnit['name']); ?>
                        <?php if (!empty($existingUnit['unit_type'])): ?>
                            (<?php echo htmlspecialchars($existingUnit['unit_type']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Does this unit belong under another unit? Or leave as top-level.</small>
        </div>
        
        <!-- Description (Optional) -->
        <div class="form-group">
            <label for="description">Description <span style="color: #6b7280;">(optional)</span></label>
            <textarea id="description" name="description" rows="3" 
                      placeholder="Brief description of this unit's purpose or responsibilities"><?php echo htmlspecialchars($unit['description'] ?? ''); ?></textarea>
        </div>
        
        <!-- Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">Update Unit</button>
            <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    
    <!-- Delete Section -->
    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626; border-radius: 0; padding: 1.5rem; margin-top: 2rem;">
        <h3 style="margin-top: 0; color: #991b1b; font-size: 1.125rem;">Danger Zone</h3>
        <p style="color: #7f1d1d; font-size: 0.875rem; margin-bottom: 1rem;">
            Delete this organisational unit. This action cannot be undone.
        </p>
        
        <?php if ($childCount > 0): ?>
            <div style="padding: 0.75rem; background-color: #fee2e2; border-radius: 0; margin-bottom: 1rem;">
                <p style="margin: 0; color: #991b1b; font-size: 0.875rem;">
                    Cannot delete: This unit has <?php echo $childCount; ?> child unit<?php echo $childCount !== 1 ? 's' : ''; ?>.
                    Delete or reassign the child units first.
                </p>
            </div>
        <?php elseif ($memberCount > 0): ?>
            <div style="padding: 0.75rem; background-color: #fee2e2; border-radius: 0; margin-bottom: 1rem;">
                <p style="margin: 0; color: #991b1b; font-size: 0.875rem;">
                    Cannot delete: This unit has <?php echo $memberCount; ?> member<?php echo $memberCount !== 1 ? 's' : ''; ?>.
                    Remove all members first.
                </p>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo url('admin/organisational-structure/delete.php'); ?>" 
                  onsubmit="return confirm('Are you sure you want to delete &quot;<?php echo htmlspecialchars($unit['name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="unit_id" value="<?php echo $unitId; ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Unit
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


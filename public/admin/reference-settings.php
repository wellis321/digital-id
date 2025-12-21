<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

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
    header('Location: ' . url('admin/organisational-structure.php?error=not_found'));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $referencePrefix = trim($_POST['reference_prefix'] ?? '');
        $referencePattern = $_POST['reference_pattern'] ?? 'incremental';
        $referenceStartNumber = intval($_POST['reference_start_number'] ?? 1);
        $referenceDigits = intval($_POST['reference_digits'] ?? 6);
        
        // Validate
        if ($referenceStartNumber < 1) {
            $error = 'Start number must be at least 1.';
        } elseif ($referenceDigits < 3 || $referenceDigits > 10) {
            $error = 'Number of digits must be between 3 and 10.';
        } else {
            try {
                $stmt = $db->prepare("
                    UPDATE organisations 
                    SET reference_prefix = ?, 
                        reference_pattern = ?, 
                        reference_start_number = ?, 
                        reference_digits = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $referencePrefix ?: null,
                    $referencePattern,
                    $referenceStartNumber,
                    $referenceDigits,
                    $organisationId
                ]);
                
                $success = 'Reference settings updated successfully.';
                
                // Refresh organisation data
                $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
                $stmt->execute([$organisationId]);
                $org = $stmt->fetch();
            } catch (Exception $e) {
                $error = 'Failed to update settings: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Reference Settings';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Display Reference Settings</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                Configure how display references are automatically generated for your organisation
            </p>
        </div>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 1.5rem; margin-bottom: 2rem; border-radius: 0;">
        <h3 style="margin-top: 0; color: #1e40af;">
            <i class="fas fa-info-circle"></i> About Display References
        </h3>
        <p style="color: #1e40af; margin-bottom: 0.5rem;">
            <strong>Employee Numbers</strong> come from your HR or payroll system and are used internally. They are not displayed on digital ID cards.
        </p>
        <p style="color: #1e40af; margin-bottom: 0.5rem;">
            <strong>Display References</strong> are shown on digital ID cards and can be auto-generated based on your preferences below, or set manually when creating employee records.
        </p>
        <p style="color: #1e40af; margin: 0;">
            If you choose "Custom" pattern, display references must be set manually for each employee.
        </p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label for="reference_prefix">Reference Prefix</label>
            <input type="text" id="reference_prefix" name="reference_prefix" 
                   value="<?php echo htmlspecialchars($org['reference_prefix'] ?? ''); ?>"
                   placeholder="e.g., SAMH">
            <small>The prefix to use for display references (e.g., "SAMH" would create "SAMH-000001"). Leave empty for no prefix.</small>
        </div>
        
        <div class="form-group">
            <label for="reference_pattern">Reference Pattern</label>
            <select id="reference_pattern" name="reference_pattern">
                <option value="incremental" <?php echo ($org['reference_pattern'] ?? 'incremental') === 'incremental' ? 'selected' : ''; ?>>
                    Incremental (e.g., SAMH-000001, SAMH-000002)
                </option>
                <option value="random_alphanumeric" <?php echo ($org['reference_pattern'] ?? '') === 'random_alphanumeric' ? 'selected' : ''; ?>>
                    Random Alphanumeric (e.g., SAMH-A1B2C3, SAMH-X9Y8Z7)
                </option>
                <option value="custom" <?php echo ($org['reference_pattern'] ?? '') === 'custom' ? 'selected' : ''; ?>>
                    Custom (manual entry required)
                </option>
            </select>
            <small>How display references should be generated automatically. If set to "Custom", you must manually enter display references when creating employee records.</small>
        </div>
        
        <div id="incremental-settings" style="<?php echo ($org['reference_pattern'] ?? 'incremental') !== 'incremental' ? 'display: none;' : ''; ?>">
            <div class="form-group">
                <label for="reference_start_number">Starting Number</label>
                <input type="number" id="reference_start_number" name="reference_start_number" 
                       value="<?php echo htmlspecialchars($org['reference_start_number'] ?? 1); ?>"
                       min="1" required>
                <small>The number to start counting from (e.g., 1 for SAMH-000001, 100 for SAMH-000100).</small>
            </div>
            
            <div class="form-group">
                <label for="reference_digits">Number of Digits</label>
                <input type="number" id="reference_digits" name="reference_digits" 
                       value="<?php echo htmlspecialchars($org['reference_digits'] ?? 6); ?>"
                       min="3" max="10" required>
                <small>How many digits to use (e.g., 6 for SAMH-000001, 4 for SAMH-0001).</small>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Settings
        </button>
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">Cancel</a>
    </form>
</div>

<script>
document.getElementById('reference_pattern').addEventListener('change', function() {
    const incrementalSettings = document.getElementById('incremental-settings');
    if (this.value === 'incremental') {
        incrementalSettings.style.display = 'block';
        document.getElementById('reference_start_number').required = true;
        document.getElementById('reference_digits').required = true;
    } else {
        incrementalSettings.style.display = 'none';
        document.getElementById('reference_start_number').required = false;
        document.getElementById('reference_digits').required = false;
    }
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


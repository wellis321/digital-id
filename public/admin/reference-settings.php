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

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_logo') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } elseif (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $error = 'No logo file was uploaded or there was an upload error.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        $fileType = $_FILES['logo']['type'];
        $fileSize = $_FILES['logo']['size'];
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Invalid file type. Only JPEG, PNG, and SVG images are allowed.';
        } elseif ($fileSize > $maxSize) {
            $error = 'File is too large. Maximum size is 2MB.';
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = dirname(__DIR__, 2) . '/uploads/organisations/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $filename = 'org_' . $organisationId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $filename;
            
            // Validate image (skip for SVG)
            if ($fileType !== 'image/svg+xml') {
                $imageInfo = getimagesize($_FILES['logo']['tmp_name']);
                if ($imageInfo === false) {
                    $error = 'Invalid image file. Please ensure the file is a valid image.';
                } else {
                    // Check reasonable dimensions (at least 100x100, max 2000x2000)
                    if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
                        $error = 'Image is too small. Please use a logo that is at least 100x100 pixels.';
                    } elseif ($imageInfo[0] > 2000 || $imageInfo[1] > 2000) {
                        $error = 'Image is too large. Please use a logo that is no more than 2000x2000 pixels.';
                    } else {
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
                            // Delete old logo if exists
                            if ($org['logo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $org['logo_path'])) {
                                @unlink(dirname(__DIR__, 2) . '/' . $org['logo_path']);
                            }
                            
                            // Update organisation record
                            $stmt = $db->prepare("UPDATE organisations SET logo_path = ? WHERE id = ?");
                            $stmt->execute(['uploads/organisations/' . $filename, $organisationId]);
                            
                            // Refresh organisation data
                            $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
                            $stmt->execute([$organisationId]);
                            $org = $stmt->fetch();
                            
                            $success = 'Logo uploaded successfully!';
                        } else {
                            $error = 'Failed to upload logo. Please try again.';
                        }
                    }
                }
            } else {
                // SVG file - just move it
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
                    // Delete old logo if exists
                    if ($org['logo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $org['logo_path'])) {
                        @unlink(dirname(__DIR__, 2) . '/' . $org['logo_path']);
                    }
                    
                    // Update organisation record
                    $stmt = $db->prepare("UPDATE organisations SET logo_path = ? WHERE id = ?");
                    $stmt->execute(['uploads/organisations/' . $filename, $organisationId]);
                    
                    // Refresh organisation data
                    $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
                    $stmt->execute([$organisationId]);
                    $org = $stmt->fetch();
                    
                    $success = 'Logo uploaded successfully!';
                } else {
                    $error = 'Failed to upload logo. Please try again.';
                }
            }
        }
    }
}

// Handle logo deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_logo') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        // Delete file
        if ($org['logo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $org['logo_path'])) {
            @unlink(dirname(__DIR__, 2) . '/' . $org['logo_path']);
        }
        
        // Update database
        $stmt = $db->prepare("UPDATE organisations SET logo_path = NULL WHERE id = ?");
        $stmt->execute([$organisationId]);
        
        // Refresh organisation data
        $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        $success = 'Logo deleted successfully.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_reference') {
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

$pageTitle = 'Organisation Settings';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div style="flex: 1;">
            <h1>Organisation Settings</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                Manage your organisation logo and display reference settings
            </p>
        </div>
        <div>
            <a href="<?php echo url('admin/entra-settings.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-microsoft"></i> Microsoft 365 SSO Settings
            </a>
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
    
    <!-- Organisation Logo Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 style="margin-top: 0;">Organisation Logo</h2>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">
            Upload your organisation's logo to display on all digital ID cards. This makes ID cards look more authentic and branded.
        </p>
        
        <?php if ($org['logo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $org['logo_path'])): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; display: inline-block;">
                <img src="<?php echo url('view-image.php?path=' . urlencode($org['logo_path'])); ?>" 
                     alt="Organisation Logo" 
                     style="max-height: 100px; max-width: 300px; display: block;">
            </div>
            <br>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" style="margin-bottom: 1rem;">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="upload_logo">
            
            <div class="form-group">
                <label for="logo">Upload Logo</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/jpg,image/svg+xml" required>
                <small>Accepted formats: JPEG, PNG, SVG. Maximum size: 2MB. Recommended: Square logo, at least 200x200 pixels.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Logo
            </button>
        </form>
        
        <?php if ($org['logo_path']): ?>
            <form method="POST" action="" style="display: inline-block;">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="delete_logo">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to delete the organisation logo?');">
                    <i class="fas fa-trash"></i> Delete Logo
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Reference Settings Section -->
    <div class="card">
        <h2 style="margin-top: 0;">Display Reference Settings</h2>
        
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_reference">
            
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


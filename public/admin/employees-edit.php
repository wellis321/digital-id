<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get employee ID
$employeeId = $_GET['id'] ?? null;
if (!$employeeId) {
    header('Location: ' . url('admin/employees.php?error=invalid_id'));
    exit;
}

// Get employee
$employee = Employee::findById($employeeId);
if (!$employee || $employee['organisation_id'] != $organisationId) {
    header('Location: ' . url('admin/employees.php?error=not_found'));
    exit;
}

// Get current ID card
$idCard = DigitalID::getOrCreateIdCard($employeeId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $updateData = [];
        
        // Update employee reference if changed
        if (isset($_POST['employee_reference']) && $_POST['employee_reference'] !== $employee['employee_reference']) {
            $newReference = trim($_POST['employee_reference']);
            if (!empty($newReference)) {
                // Check if reference is unique
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT id FROM employees WHERE organisation_id = ? AND employee_reference = ? AND id != ?");
                $stmt->execute([$organisationId, $newReference, $employeeId]);
                if ($stmt->fetch()) {
                    $error = 'Employee reference already exists for this organisation.';
                } else {
                    $updateData['employee_reference'] = $newReference;
                }
            }
        }
        
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $error = 'Invalid file type. Only JPEG, PNG, and GIF images are allowed.';
            } elseif ($_FILES['photo']['size'] > $maxSize) {
                $error = 'File is too large. Maximum size is 5MB.';
            } else {
                // Create uploads directory if it doesn't exist
                $uploadDir = dirname(__DIR__, 2) . '/uploads/employees/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'employee_' . $employeeId . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                    // Delete old photo if exists
                    if ($employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path'])) {
                        @unlink(dirname(__DIR__, 2) . '/' . $employee['photo_path']);
                    }
                    
                    $updateData['photo_path'] = 'uploads/employees/' . $filename;
                } else {
                    $error = 'Failed to upload photo.';
                }
            }
        }
        
        // Update active status
        if (isset($_POST['is_active'])) {
            $updateData['is_active'] = $_POST['is_active'] === '1' ? true : false;
        }
        
        // Update ID card expiration if provided
        if (isset($_POST['card_expires_at']) && !empty($_POST['card_expires_at'])) {
            $expiresAt = date('Y-m-d H:i:s', strtotime($_POST['card_expires_at']));
            if ($idCard) {
                $db = getDbConnection();
                $stmt = $db->prepare("UPDATE digital_id_cards SET expires_at = ? WHERE id = ?");
                $stmt->execute([$expiresAt, $idCard['id']]);
            }
        }
        
        // Update employee if we have data
        if (empty($error) && !empty($updateData)) {
            $result = Employee::update($employeeId, $updateData);
            if ($result['success']) {
                $success = 'Employee updated successfully.';
                // Refresh employee data
                $employee = Employee::findById($employeeId);
                $idCard = DigitalID::getOrCreateIdCard($employeeId);
            } else {
                $error = $result['message'] ?? 'Failed to update employee.';
            }
        } elseif (empty($error) && isset($_POST['card_expires_at'])) {
            // Only expiration date was updated
            $success = 'ID card expiration date updated successfully.';
            $idCard = DigitalID::getOrCreateIdCard($employeeId);
        }
    }
}

$pageTitle = 'Edit Employee';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/employees.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Edit Employee</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
            </p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label>User Information</label>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                <p style="margin: 0.5rem 0;"><strong>Name:</strong> <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
                <p style="margin: 0.5rem 0;"><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                <p style="margin: 0.5rem 0; font-size: 0.875rem; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> User name and email are managed in the Users section.
                </p>
            </div>
        </div>
        
        <div class="form-group">
            <label for="employee_reference">Employee Reference <span style="color: #dc2626;">*</span></label>
            <input type="text" id="employee_reference" name="employee_reference" 
                   value="<?php echo htmlspecialchars($employee['employee_reference']); ?>" required>
            <small>Unique reference for this employee within your organisation</small>
        </div>
        
        <div class="form-group">
            <label>Current Photo</label>
            <div style="margin-top: 0.5rem;">
                <?php if ($employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($employee['photo_path']); ?>" 
                         alt="Employee Photo" 
                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;">
                <?php else: ?>
                    <div style="width: 150px; height: 150px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #9ca3af; border: 2px solid #e5e7eb;">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="photo">Upload New Photo</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif">
            <small>JPEG, PNG, or GIF. Maximum size: 5MB</small>
        </div>
        
        <div class="form-group">
            <label for="is_active">Status</label>
            <select id="is_active" name="is_active">
                <option value="1" <?php echo $employee['is_active'] ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo !$employee['is_active'] ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        
        <?php if ($idCard): ?>
            <div class="form-group">
                <label for="card_expires_at">ID Card Expiration Date</label>
                <input type="datetime-local" id="card_expires_at" name="card_expires_at" 
                       value="<?php echo $idCard['expires_at'] ? date('Y-m-d\TH:i', strtotime($idCard['expires_at'])) : ''; ?>">
                <small>When this ID card expires. Leave empty to use default expiration period.</small>
            </div>
            
            <div style="background: #f0f9ff; border-left: 4px solid #06b6d4; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #0e7490; font-size: 1rem;">
                    <i class="fas fa-info-circle"></i> ID Card Information
                </h4>
                <p style="margin: 0.25rem 0; color: #0e7490; font-size: 0.875rem;">
                    <strong>Issued:</strong> <?php echo date('d/m/Y H:i', strtotime($idCard['issued_at'])); ?>
                </p>
                <p style="margin: 0.25rem 0; color: #0e7490; font-size: 0.875rem;">
                    <strong>Current Expiration:</strong> <?php echo $idCard['expires_at'] ? date('d/m/Y H:i', strtotime($idCard['expires_at'])) : 'Not set'; ?>
                </p>
                <p style="margin: 0.25rem 0; color: #0e7490; font-size: 0.875rem;">
                    <strong>Status:</strong> 
                    <?php if ($idCard['is_revoked']): ?>
                        <span style="color: #dc2626;">Revoked</span>
                    <?php elseif ($idCard['expires_at'] && strtotime($idCard['expires_at']) < time()): ?>
                        <span style="color: #f59e0b;">Expired</span>
                    <?php else: ?>
                        <span style="color: #10b981;">Active</span>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="<?php echo url('admin/employees.php'); ?>" class="btn btn-secondary">Cancel</a>
            <a href="<?php echo url('id-card.php?employee_id=' . $employeeId); ?>" class="btn btn-secondary" target="_blank">
                <i class="fas fa-id-card"></i> View ID Card
            </a>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>




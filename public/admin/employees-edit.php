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

// Handle sync from Staff Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync_staff_service') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        if (!empty($employee['staff_service_person_id'])) {
            $synced = Employee::syncFromStaffService($employee['staff_service_person_id'], $employeeId);
            if ($synced) {
                $success = 'Employee synced from Staff Service successfully.';
                // Re-fetch employee data
                $employee = Employee::findById($employeeId);
            } else {
                $error = 'Failed to sync from Staff Service.';
            }
        } else {
            $error = 'Employee is not linked to Staff Service.';
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'sync_staff_service')) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $updateData = [];
        
        // Prevent updating employee_number (it's from HR/payroll)
        if (isset($_POST['employee_number']) && $_POST['employee_number'] !== ($employee['employee_number'] ?? $employee['employee_reference'])) {
            $error = 'Employee number cannot be changed. This comes from your HR or payroll system and is integral to other systems.';
        }
        
        // Update display_reference if changed
        if (isset($_POST['display_reference'])) {
            $newDisplayReference = trim($_POST['display_reference']);
            if (empty($newDisplayReference)) {
                $error = 'Display reference cannot be empty.';
            } elseif ($newDisplayReference !== ($employee['display_reference'] ?? $employee['employee_reference'])) {
                // The Employee::update method will check for uniqueness
                $updateData['display_reference'] = $newDisplayReference;
            }
        }
        
        // Handle photo upload (admin override - immediately approves)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $error = 'Invalid file type. Only JPEG and PNG images are allowed.';
            } elseif ($_FILES['photo']['size'] > $maxSize) {
                $error = 'File is too large. Maximum size is 5MB.';
            } else {
                // Validate image
                $imageInfo = getimagesize($_FILES['photo']['tmp_name']);
                if ($imageInfo === false) {
                    $error = 'Invalid image file.';
                } elseif ($imageInfo[0] < 300 || $imageInfo[1] < 300) {
                    $error = 'Image is too small. Minimum size is 300x300 pixels.';
                } else {
                    // Create uploads directory if it doesn't exist
                    $uploadDir = dirname(__DIR__, 2) . '/uploads/employees/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $filename = 'employee_' . $employeeId . '_' . time() . '.' . $extension;
                    $filePath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                        // Delete old approved photo if exists
                        if ($employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path'])) {
                            @unlink(dirname(__DIR__, 2) . '/' . $employee['photo_path']);
                        }
                        
                        // Delete pending photo if exists
                        if (!empty($employee['photo_pending_path']) && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path'])) {
                            @unlink(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path']);
                        }
                        
                        // Admin upload immediately approves
                        $updateData['photo_path'] = 'uploads/employees/' . $filename;
                        $updateData['photo_approval_status'] = 'approved';
                        $updateData['photo_pending_path'] = null;
                        $updateData['photo_rejection_reason'] = null;
                        
                        // Set approval metadata (will be set via SQL update)
                        $db = getDbConnection();
                        $stmt = $db->prepare("
                            UPDATE employees 
                            SET photo_approved_at = NOW(),
                                photo_approved_by = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([Auth::getUserId(), $employeeId]);
                    } else {
                        $error = 'Failed to upload photo.';
                    }
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
            <div style="background: #f9fafb; padding: 1rem; border-radius: 0; margin-top: 0.5rem;">
                <p style="margin: 0.5rem 0;"><strong>Name:</strong> <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
                <p style="margin: 0.5rem 0;"><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                <p style="margin: 0.5rem 0; font-size: 0.875rem; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> User name and email are managed in the Users section.
                </p>
            </div>
        </div>
        
        <div class="form-group">
            <label for="employee_number">Employee Number</label>
            <input type="text" id="employee_number" name="employee_number" 
                   value="<?php echo htmlspecialchars($employee['employee_number'] ?? $employee['employee_reference'] ?? ''); ?>" 
                   readonly 
                   style="background-color: #f3f4f6; cursor: not-allowed;">
            <small><i class="fas fa-lock"></i> <strong>This cannot be changed.</strong> This is the employee number from your HR or payroll system and is integral to other systems. It is not displayed on the digital ID card.</small>
        </div>
        
        <div class="form-group">
            <label for="display_reference">Display Reference <span style="color: #dc2626;">*</span></label>
            <input type="text" id="display_reference" name="display_reference" 
                   value="<?php echo htmlspecialchars($employee['display_reference'] ?? $employee['employee_reference'] ?? ''); ?>" required>
            <small><i class="fas fa-id-card"></i> This is the reference shown on the digital ID card. Must be unique within your organisation.</small>
        </div>
        
        <?php if (defined('USE_STAFF_SERVICE') && USE_STAFF_SERVICE): ?>
        <div class="form-group" style="background: #f9fafb; padding: 1rem; border-radius: 0; border-left: 3px solid #3b82f6;">
            <label style="font-weight: 600; margin-bottom: 0.5rem;">Staff Service Integration</label>
            <?php if (!empty($employee['staff_service_person_id'])): ?>
                <p style="margin: 0.5rem 0; color: #10b981;">
                    <i class="fas fa-link"></i> <strong>Linked to Staff Service</strong>
                    <br><small style="color: #6b7280;">Person ID: <?php echo $employee['staff_service_person_id']; ?></small>
                </p>
                <?php if (!empty($employee['last_synced_from_staff_service'])): ?>
                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                        Last synced: <?php echo date('d/m/Y H:i', strtotime($employee['last_synced_from_staff_service'])); ?>
                    </p>
                <?php else: ?>
                    <p style="margin: 0.5rem 0; color: #f59e0b; font-size: 0.875rem;">
                        <i class="fas fa-exclamation-triangle"></i> Never synced
                    </p>
                <?php endif; ?>
                <form method="POST" action="" style="margin-top: 0.75rem;">
                    <?php echo CSRF::tokenField(); ?>
                    <input type="hidden" name="action" value="sync_staff_service">
                    <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        <i class="fas fa-sync"></i> Sync from Staff Service
                    </button>
                </form>
            <?php else: ?>
                <p style="margin: 0.5rem 0; color: #6b7280;">
                    <i class="fas fa-unlink"></i> Not linked to Staff Service
                </p>
                <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                    Link this employee to a Staff Service person record to enable automatic syncing.
                </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Photo Status</label>
            <div style="margin-top: 0.5rem;">
                <?php 
                $photoStatus = $employee['photo_approval_status'] ?? 'none';
                $hasApprovedPhoto = $photoStatus === 'approved' && $employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path']);
                ?>
                
                <?php if ($hasApprovedPhoto): ?>
                    <div style="margin-bottom: 1rem;">
                        <p style="color: #10b981; margin: 0 0 0.5rem 0;">
                            <i class="fas fa-check-circle"></i> <strong>Approved Photo</strong>
                        </p>
                        <img src="<?php echo htmlspecialchars(url('view-image.php?path=' . urlencode($employee['photo_path']))); ?>" 
                             alt="Employee Photo" 
                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 0; border: 3px solid #10b981;">
                        <?php if ($employee['photo_approved_at']): ?>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0 0 0;">
                                Approved on <?php echo date('d/m/Y H:i', strtotime($employee['photo_approved_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($photoStatus === 'pending' && !empty($employee['photo_pending_path']) && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path'])): ?>
                    <div style="margin-bottom: 1rem;">
                        <p style="color: #f59e0b; margin: 0 0 0.5rem 0;">
                            <i class="fas fa-clock"></i> <strong>Pending Approval</strong>
                        </p>
                        <img src="<?php echo htmlspecialchars($employee['photo_pending_path']); ?>" 
                             alt="Pending Photo" 
                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 0; border: 3px solid #f59e0b;">
                        <p style="margin-top: 0.5rem;">
                            <a href="<?php echo url('admin/photo-approvals.php'); ?>" class="btn" style="background: #f59e0b; color: white; padding: 0.5rem 1rem; font-size: 0.875rem;">
                                <i class="fas fa-eye"></i> Review Photo
                            </a>
                        </p>
                    </div>
                <?php elseif ($photoStatus === 'rejected'): ?>
                    <div style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; border-radius: 0; border-left: 4px solid #ef4444;">
                        <p style="color: #991b1b; margin: 0 0 0.5rem 0;">
                            <i class="fas fa-times-circle"></i> <strong>Rejected</strong>
                        </p>
                        <?php if (!empty($employee['photo_rejection_reason'])): ?>
                            <p style="color: #7f1d1d; font-size: 0.875rem; margin: 0;">
                                <?php echo nl2br(htmlspecialchars($employee['photo_rejection_reason'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="width: 150px; height: 150px; background: #f3f4f6; border-radius: 0; display: flex; align-items: center; justify-content: center; color: #9ca3af; border: 2px solid #e5e7eb;">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                    <p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                        No photo uploaded
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 0; margin-bottom: 1.5rem;">
            <p style="margin: 0; color: #0c4a6e; font-size: 0.875rem;">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> Employees can upload their own photos through their profile. Use the <a href="<?php echo url('admin/photo-approvals.php'); ?>" style="color: #0284c7; font-weight: 600;">Photo Approvals</a> page to review and approve uploaded photos.
            </p>
        </div>
        
        <div class="form-group">
            <label for="photo">Upload Photo (Admin Override)</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
            <small>JPEG or PNG only. Maximum size: 5MB. This will immediately approve the photo (bypassing normal approval workflow).</small>
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
            
            <div style="background: #f0f9ff; border-left: 4px solid #06b6d4; padding: 1rem; border-radius: 0; margin-bottom: 1.5rem;">
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




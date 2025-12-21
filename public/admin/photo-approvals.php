<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Handle photo approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        $employeeId = intval($_POST['employee_id'] ?? 0);
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        
        if (empty($action) || !$employeeId) {
            $error = 'Invalid request.';
        } else {
            $db = getDbConnection();
            
            // Verify employee belongs to admin's organisation
            $stmt = $db->prepare("SELECT id, photo_pending_path, photo_path FROM employees WHERE id = ? AND organisation_id = ?");
            $stmt->execute([$employeeId, $organisationId]);
            $employee = $stmt->fetch();
            
            if (!$employee) {
                $error = 'Employee not found.';
            } else {
                try {
                    $db->beginTransaction();
                    
                    if ($action === 'approve') {
                        // Move pending photo to approved location
                        if ($employee['photo_pending_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path'])) {
                            $pendingPath = dirname(__DIR__, 2) . '/' . $employee['photo_pending_path'];
                            $approvedDir = dirname(__DIR__, 2) . '/uploads/employees/';
                            if (!is_dir($approvedDir)) {
                                mkdir($approvedDir, 0755, true);
                            }
                            
                            // Generate approved filename
                            $filename = 'employee_' . $employeeId . '_' . time() . '.' . pathinfo($employee['photo_pending_path'], PATHINFO_EXTENSION);
                            $approvedPath = $approvedDir . $filename;
                            
                            if (rename($pendingPath, $approvedPath)) {
                                // Delete old approved photo if exists
                                if ($employee['photo_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_path'])) {
                                    @unlink(dirname(__DIR__, 2) . '/' . $employee['photo_path']);
                                }
                                
                                // Update employee record
                                $stmt = $db->prepare("
                                    UPDATE employees 
                                    SET photo_path = ?,
                                        photo_pending_path = NULL,
                                        photo_approval_status = 'approved',
                                        photo_approved_at = NOW(),
                                        photo_approved_by = ?,
                                        photo_rejection_reason = NULL
                                    WHERE id = ?
                                ");
                                $stmt->execute(['uploads/employees/' . $filename, Auth::getUserId(), $employeeId]);
                                
                                $db->commit();
                                $success = 'Photo approved successfully.';
                                
                                // TODO: Send email notification to employee
                            } else {
                                $db->rollBack();
                                $error = 'Failed to move photo to approved location.';
                            }
                        } else {
                            $db->rollBack();
                            $error = 'Pending photo file not found.';
                        }
                    } elseif ($action === 'reject') {
                        // Delete pending photo
                        if ($employee['photo_pending_path'] && file_exists(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path'])) {
                            @unlink(dirname(__DIR__, 2) . '/' . $employee['photo_pending_path']);
                        }
                        
                        // Update employee record
                        $stmt = $db->prepare("
                            UPDATE employees 
                            SET photo_pending_path = NULL,
                                photo_approval_status = 'rejected',
                                photo_rejection_reason = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$rejectionReason ?: null, $employeeId]);
                        
                        $db->commit();
                        $success = 'Photo rejected. The employee has been notified.';
                        
                        // TODO: Send email notification to employee
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = 'Failed to process request: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get employees with pending photos
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT e.*, u.first_name, u.last_name, u.email
    FROM employees e
    JOIN users u ON e.user_id = u.id
    WHERE e.organisation_id = ? 
    AND e.photo_approval_status = 'pending'
    AND e.photo_pending_path IS NOT NULL
    ORDER BY e.updated_at DESC
");
$stmt->execute([$organisationId]);
$pendingPhotos = $stmt->fetchAll();

// Get count of pending photos for notification
$pendingCount = count($pendingPhotos);

$pageTitle = 'Photo Approvals';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0;">Photo Approvals</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                Review and approve employee photos for digital ID cards
            </p>
        </div>
        <?php if ($pendingCount > 0): ?>
            <div style="background: #fef3c7; padding: 0.75rem 1.5rem; border-radius: 0; border: 2px solid #f59e0b;">
                <span style="font-size: 1.5rem; font-weight: bold; color: #92400e;">
                    <?php echo $pendingCount; ?>
                </span>
                <span style="color: #78350f; margin-left: 0.5rem;">
                    Photo<?php echo $pendingCount !== 1 ? 's' : ''; ?> Pending
                </span>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (empty($pendingPhotos)): ?>
        <div style="text-align: center; padding: 3rem; background: #f9fafb; border-radius: 0;">
            <i class="fas fa-check-circle" style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;"></i>
            <h2 style="color: #1f2937; margin-bottom: 0.5rem;">No Pending Photos</h2>
            <p style="color: #6b7280;">
                All photos have been reviewed. Check back later for new uploads.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 2rem; margin-top: 2rem;">
            <?php foreach ($pendingPhotos as $emp): ?>
                <div style="background: white; border: 2px solid #e5e7eb; border-radius: 0; padding: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; align-items: start;">
                        <!-- Photo -->
                        <div>
                            <?php 
                            $pendingPhotoPath = $emp['photo_pending_path'] ?? null;
                            $pendingPhotoFullPath = $pendingPhotoPath ? dirname(__DIR__, 2) . '/' . $pendingPhotoPath : null;
                            $pendingPhotoExists = $pendingPhotoPath && $pendingPhotoFullPath && file_exists($pendingPhotoFullPath);
                            ?>
                            <?php if ($pendingPhotoExists): ?>
                                <?php 
                                // Use the image viewer to serve pending photos
                                $imageUrl = url('view-image.php?path=' . urlencode($pendingPhotoPath));
                                ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     alt="Pending Photo" 
                                     style="width: 200px; height: 200px; object-fit: cover; border-radius: 0; border: 3px solid #f59e0b; display: block;">
                                <p style="text-align: center; margin-top: 0.5rem; color: #f59e0b; font-size: 0.875rem; font-weight: 600;">
                                    <i class="fas fa-clock"></i> Pending Review
                                </p>
                            <?php else: ?>
                                <div style="width: 200px; height: 200px; background: #f3f4f6; border-radius: 0; display: flex; align-items: center; justify-content: center; color: #9ca3af; border: 3px solid #f59e0b;">
                                    <i class="fas fa-image" style="font-size: 3rem;"></i>
                                </div>
                                <p style="text-align: center; margin-top: 0.5rem; color: #ef4444; font-size: 0.875rem;">
                                    <i class="fas fa-exclamation-triangle"></i> Photo file not found
                                    <?php if ($pendingPhotoPath): ?>
                                        <br><small style="font-size: 0.75rem; color: #9ca3af;">Path: <?php echo htmlspecialchars($pendingPhotoPath); ?></small>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Employee Info and Actions -->
                        <div>
                            <h3 style="margin-top: 0; color: #1f2937;">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </h3>
                            <p style="color: #6b7280; margin: 0.25rem 0;">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($emp['email']); ?>
                            </p>
                            <?php if ($emp['display_reference'] ?? $emp['employee_reference']): ?>
                                <p style="color: #6b7280; margin: 0.25rem 0;">
                                    <i class="fas fa-id-card"></i> Reference: <?php echo htmlspecialchars($emp['display_reference'] ?? $emp['employee_reference']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Approval Form -->
                            <form method="POST" action="" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label for="rejection_reason_<?php echo $emp['id']; ?>" style="font-weight: 600;">Rejection Reason (if rejecting)</label>
                                    <textarea id="rejection_reason_<?php echo $emp['id']; ?>" 
                                              name="rejection_reason" 
                                              rows="3" 
                                              placeholder="Optional: Provide feedback on why the photo doesn't meet requirements..."></textarea>
                                    <small>This will be shown to the employee to help them upload a better photo.</small>
                                </div>
                                
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <button type="submit" name="action" value="approve" class="btn" style="background: #10b981; color: white; border: none;">
                                        <i class="fas fa-check"></i> Approve Photo
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger reject-photo-btn" data-employee-name="<?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>">
                                        <i class="fas fa-times"></i> Reject Photo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Add confirmation dialog for photo rejection
document.querySelectorAll('.reject-photo-btn').forEach(function(button) {
    button.addEventListener('click', function(e) {
        const employeeName = this.getAttribute('data-employee-name');
        const form = this.closest('form');
        const rejectionReason = form.querySelector('textarea[name="rejection_reason"]').value.trim();
        
        let message = 'Are you sure you want to reject the photo for ' + employeeName + '?';
        if (!rejectionReason) {
            message += '\n\nNote: You haven\'t provided a rejection reason. The employee won\'t know why their photo was rejected.';
        }
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


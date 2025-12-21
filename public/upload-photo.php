<?php
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();

$employee = Employee::findByUserId(Auth::getUserId());

if (!$employee) {
    header('Location: ' . url('id-card.php?error=no_employee'));
    exit;
}

$error = '';
$success = '';

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $error = 'No photo file was uploaded or there was an upload error.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = $_FILES['photo']['type'];
        $fileSize = $_FILES['photo']['size'];
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Invalid file type. Only JPEG and PNG images are allowed.';
        } elseif ($fileSize > $maxSize) {
            $error = 'File is too large. Maximum size is 5MB.';
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = dirname(__DIR__) . '/uploads/employees/pending/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $filename = 'employee_' . $employee['id'] . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $filename;
            
            // Validate image dimensions and quality
            $imageInfo = getimagesize($_FILES['photo']['tmp_name']);
            if ($imageInfo === false) {
                $error = 'Invalid image file. Please ensure the file is a valid image.';
            } else {
                // Check minimum dimensions (e.g., at least 300x300 pixels)
                if ($imageInfo[0] < 300 || $imageInfo[1] < 300) {
                    $error = 'Image is too small. Please use a photo that is at least 300x300 pixels.';
                } else {
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                        // Update employee record with pending photo
                        $db = getDbConnection();
                        $stmt = $db->prepare("
                            UPDATE employees 
                            SET photo_pending_path = ?, 
                                photo_approval_status = 'pending',
                                photo_rejection_reason = NULL
                            WHERE id = ?
                        ");
                        $stmt->execute(['uploads/employees/pending/' . $filename, $employee['id']]);
                        
                        // Refresh employee data to get updated photo status
                        $employee = Employee::findById($employee['id']);
                        $success = 'Photo uploaded successfully! An administrator will review it shortly. You\'ll be notified once it\'s been approved.';
                        
                        // Notify admins about pending photo (optional - could add email notification here)
                    } else {
                        $error = 'Failed to upload photo. Please try again.';
                    }
                }
            }
        }
    }
}

// Always refresh employee data to ensure we have latest status
$employee = Employee::findByUserId(Auth::getUserId());
if (!$employee) {
    header('Location: ' . url('id-card.php?error=no_employee'));
    exit;
}

$currentPhotoStatus = $employee['photo_approval_status'] ?? 'none';
$hasPendingPhoto = !empty($employee['photo_pending_path']) && file_exists(dirname(__DIR__) . '/' . $employee['photo_pending_path']);

$pageTitle = 'Upload Photo';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Upload Your ID Card Photo</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-bottom: 2rem;">
        <a href="<?php echo url('photo-guidelines.php'); ?>" style="color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-info-circle"></i> View Photo Guidelines
        </a>
    </div>
    
    <!-- Current Photo Status -->
    <?php if ($employee['photo_path'] && file_exists(dirname(__DIR__) . '/' . $employee['photo_path']) && ($currentPhotoStatus === 'approved' || $currentPhotoStatus === 'none')): ?>
        <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 1.5rem; border-radius: 0; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; color: #065f46; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> Current Approved Photo
            </h3>
            <div style="display: flex; gap: 1.5rem; align-items: start; margin-top: 1rem;">
                <img src="<?php echo htmlspecialchars(url('view-image.php?path=' . urlencode($employee['photo_path']))); ?>" 
                     alt="Current Photo" 
                     style="width: 150px; height: 150px; object-fit: cover; border-radius: 0; border: 3px solid #10b981;">
                <div style="flex: 1;">
                    <p style="color: #065f46; margin: 0 0 0.5rem 0;">
                        You currently have an approved photo. You can upload a new photo to replace it, which will need administrator approval.
                    </p>
                    <?php if ($employee['photo_approved_at']): ?>
                        <p style="color: #047857; margin: 0; font-size: 0.875rem;">
                            <i class="fas fa-calendar-check"></i> Approved on <?php echo date('d/m/Y H:i', strtotime($employee['photo_approved_at'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($currentPhotoStatus === 'pending'): ?>
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1.5rem; border-radius: 0; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; color: #92400e; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-clock"></i> Photo Pending Approval
            </h3>
            <?php 
            $pendingPhotoPath = $employee['photo_pending_path'] ?? null;
            $pendingPhotoExists = $pendingPhotoPath && file_exists(dirname(__DIR__) . '/' . $pendingPhotoPath);
            ?>
            <?php if ($pendingPhotoExists): ?>
                <div style="display: flex; gap: 1.5rem; align-items: start; margin-top: 1rem;">
                    <img src="<?php echo htmlspecialchars(url('view-image.php?path=' . urlencode($pendingPhotoPath))); ?>" 
                         alt="Pending Photo" 
                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 0; border: 3px solid #f59e0b; display: block;">
                    <div style="flex: 1;">
                        <p style="color: #92400e; margin: 0;">
                            Your photo has been uploaded and is waiting for administrator approval. You'll be notified once it's been reviewed.
                        </p>
                        <p style="color: #78350f; margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                            You can upload a different photo if you want to replace this one.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <p style="color: #92400e; margin: 0;">
                    Your photo is pending approval. The image may still be processing.
                </p>
            <?php endif; ?>
        </div>
    <?php elseif ($currentPhotoStatus === 'rejected'): ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 1.5rem; border-radius: 0; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; color: #991b1b; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-times-circle"></i> Photo Rejected
            </h3>
            <?php if (!empty($employee['photo_rejection_reason'])): ?>
                <div style="background: white; padding: 1rem; border-radius: 0; margin-top: 1rem;">
                    <p style="margin: 0 0 0.5rem 0; color: #991b1b; font-weight: 600;">Reason:</p>
                    <p style="margin: 0; color: #7f1d1d;"><?php echo nl2br(htmlspecialchars($employee['photo_rejection_reason'])); ?></p>
                </div>
            <?php else: ?>
                <p style="color: #991b1b; margin: 0;">
                    Your photo was not approved. Please review the guidelines above and upload a new photo that meets the requirements.
                </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="background: #f3f4f6; border-left: 4px solid #6b7280; padding: 1.5rem; border-radius: 0; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; color: #374151;">
                <i class="fas fa-user-circle"></i> No Photo Uploaded
            </h3>
            <p style="color: #4b5563; margin: 0;">
                Upload a photo to be displayed on your digital ID card. Your photo must be approved by an administrator before it appears on your card.
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Upload Form -->
    <form method="POST" action="" enctype="multipart/form-data" id="photo-upload-form">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="upload">
        
        <div class="form-group">
            <label for="photo">Select Photo</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/jpg,image/png" required>
            <small>
                <i class="fas fa-info-circle"></i> JPEG or PNG only. Minimum 300x300 pixels. Maximum 5MB.
            </small>
        </div>
        
        <div id="photo-preview" style="display: none; margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Photo Preview:</label>
            <div style="display: inline-block; position: relative;">
                <img id="preview-image" src="" alt="Preview" style="width: 200px; height: 200px; object-fit: cover; border-radius: 0; border: 3px solid #3b82f6;">
                <div id="preview-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); border-radius: 0; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    Preview
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Photo for Review
        </button>
        <a href="<?php echo url('id-card.php'); ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">
            <i class="fas fa-arrow-left"></i> Back to ID Card
        </a>
    </form>
</div>

<script>
// Photo preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photo-preview');
    const previewImage = document.getElementById('preview-image');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>


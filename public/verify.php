<?php
require_once dirname(__DIR__) . '/config/config.php';

$verificationResult = null;
$token = $_GET['token'] ?? '';
$type = $_GET['type'] ?? 'qr';
$organisationId = $_POST['organisation_id'] ?? null;
$employeeReference = $_POST['employee_reference'] ?? '';

// Verify by token (QR or NFC)
if ($token) {
    $verificationResult = VerificationService::verifyByToken($token, $type);
} 
// Verify by reference (manual lookup)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $organisationId && $employeeReference) {
    if (!CSRF::validatePost()) {
        $verificationResult = [
            'success' => false,
            'message' => 'Invalid security token.'
        ];
    } else {
        $verificationResult = VerificationService::verifyByReference($organisationId, $employeeReference);
    }
}

// Get all organisations for manual lookup
$db = getDbConnection();
$stmt = $db->prepare("SELECT id, name FROM organisations ORDER BY name");
$stmt->execute();
$organisations = $stmt->fetchAll();

$pageTitle = 'Verify Digital ID';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="card">
    <h1>Verify Digital ID</h1>
    
    <?php if ($verificationResult): ?>
        <?php if ($verificationResult['success']): ?>
            <div class="verification-result">
                <div class="verification-success">✓ Verification Successful</div>
                
                <div class="id-card" style="margin: 2rem auto;">
                    <div class="id-card-header">
                        <?php
                        // Get organisation logo if available
                        $orgLogoPath = null;
                        if (!empty($verificationResult['employee']['organisation_id'])) {
                            $db = getDbConnection();
                            $stmt = $db->prepare("SELECT logo_path FROM organisations WHERE id = ?");
                            $stmt->execute([$verificationResult['employee']['organisation_id']]);
                            $orgData = $stmt->fetch();
                            if ($orgData && $orgData['logo_path'] && file_exists(dirname(__DIR__) . '/' . $orgData['logo_path'])) {
                                $orgLogoPath = url('view-image.php?path=' . urlencode($orgData['logo_path']));
                            }
                        }
                        ?>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <?php if ($orgLogoPath): ?>
                                <img src="<?php echo htmlspecialchars($orgLogoPath); ?>" 
                                     alt="<?php echo htmlspecialchars($verificationResult['employee']['organisation_name']); ?> Logo" 
                                     style="max-height: 50px; max-width: 150px; object-fit: contain;">
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <h2 style="margin: 0;"><?php echo htmlspecialchars($verificationResult['employee']['organisation_name']); ?></h2>
                                <p style="margin: 0;">Verified Employee</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                    // Only show approved photos
                    $verifiedPhotoPath = null;
                    if (($verificationResult['employee']['photo_approval_status'] ?? 'none') === 'approved' && 
                        $verificationResult['employee']['photo_path'] && 
                        file_exists(dirname(__DIR__) . '/' . $verificationResult['employee']['photo_path'])) {
                        $verifiedPhotoPath = $verificationResult['employee']['photo_path'];
                    }
                    ?>
                    <?php if ($verifiedPhotoPath): ?>
                        <?php
                        // Use image viewer for photos outside public directory
                        $photoUrl = strpos($verifiedPhotoPath, 'uploads/') === 0 
                            ? url('view-image.php?path=' . urlencode($verifiedPhotoPath))
                            : $verifiedPhotoPath;
                        ?>
                        <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Photo" class="id-card-photo">
                    <?php else: ?>
                        <div class="id-card-photo" style="background-color: #f3f4f6; border: 3px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                            No Photo
                        </div>
                    <?php endif; ?>
                    
                    <div class="id-card-details">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($verificationResult['employee']['first_name'] . ' ' . $verificationResult['employee']['last_name']); ?></p>
                        <p><strong>Reference:</strong> <?php echo htmlspecialchars($verificationResult['employee']['display_reference'] ?? $verificationResult['employee']['employee_reference'] ?? 'N/A'); ?></p>
                        <p><strong>Organisation:</strong> <?php echo htmlspecialchars($verificationResult['employee']['organisation_name']); ?></p>
                        <p><strong>Verification Method:</strong> <?php echo strtoupper($verificationResult['verification_type']); ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="verification-result">
                <div class="verification-failed">✗ Verification Failed</div>
                <p><?php echo htmlspecialchars($verificationResult['message'] ?? 'Verification failed.'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="card" style="margin-top: 2rem;">
        <h2>Manual Lookup</h2>
        <p>Enter employee details to verify their identity:</p>
        
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            
            <div class="form-group">
                <label for="organisation_id">Organisation</label>
                <select id="organisation_id" name="organisation_id" required>
                    <option value="">Select an organisation...</option>
                    <?php foreach ($organisations as $org): ?>
                        <option value="<?php echo $org['id']; ?>" <?php echo ($organisationId == $org['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($org['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="employee_reference">Employee Reference</label>
                <input type="text" id="employee_reference" name="employee_reference" value="<?php echo htmlspecialchars($employeeReference); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>


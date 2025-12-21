<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';
$warnings = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $importType = $_POST['import_type'] ?? 'users';
        $createEmployees = isset($_POST['create_employees']) && $_POST['create_employees'] === '1';
        
        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a file to upload.';
        } else {
            $file = $_FILES['import_file'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileExt, ['csv', 'json'])) {
                $error = 'Invalid file type. Only CSV and JSON files are supported.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'File is too large. Maximum file size is 5MB.';
            } else {
                // Process import
                try {
                    require_once dirname(__DIR__, 2) . '/src/classes/UserImport.php';
                    
                    if ($fileExt === 'csv') {
                        $result = UserImport::importFromCsv($organisationId, $file['tmp_name'], $createEmployees);
                    } else {
                        $result = UserImport::importFromJson($organisationId, $file['tmp_name'], $createEmployees);
                    }
                    
                    if ($result['success']) {
                        $successMessage = "Import complete! {$result['users_created']} users created";
                        if ($result['users_updated'] > 0) {
                            $successMessage .= ", {$result['users_updated']} updated";
                        }
                        if ($createEmployees && $result['employees_created'] > 0) {
                            $successMessage .= ", {$result['employees_created']} employee profiles created";
                        }
                        if ($result['users_skipped'] > 0) {
                            $successMessage .= ", {$result['users_skipped']} skipped";
                        }
                        if (!empty($result['warnings'])) {
                            $successMessage .= " (" . count($result['warnings']) . " warnings)";
                        }
                        $success = $successMessage;
                        $warnings = $result['warnings'] ?? [];
                    } else {
                        $error = $result['message'] ?? 'Import failed.';
                        $warnings = $result['warnings'] ?? [];
                    }
                } catch (Exception $e) {
                    $error = 'Import failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Import Users';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/users.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Import Users</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;">Bulk import users from CSV or JSON files</p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($warnings)): ?>
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0; margin-bottom: 2rem;">
            <h4 style="margin-top: 0; color: #92400e; font-size: 1rem;">Import Warnings</h4>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #92400e; font-size: 0.875rem;">
                <?php foreach ($warnings as $warning): ?>
                    <li><?php echo htmlspecialchars($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div style="background-color: #e0f2fe; border-left: 4px solid #06b6d4; padding: 1rem; border-radius: 0; margin-bottom: 2rem;">
        <h4 style="margin-top: 0; color: #0e7490; font-size: 1rem;">Bulk User Import</h4>
        <p style="margin: 0.5rem 0 0; color: #0e7490; font-size: 0.875rem;">
            Import users from CSV or JSON files exported from Microsoft Entra ID, recruitment systems, or HR databases. 
            Users can be created with temporary passwords that they'll need to change on first login.
        </p>
    </div>
    
    <!-- Upload Form -->
    <form method="POST" action="" enctype="multipart/form-data" style="max-width: 800px;">
        <?php echo CSRF::tokenField(); ?>
        
        <!-- Import Options -->
        <div class="form-group">
            <label>
                <input type="checkbox" name="create_employees" value="1" checked>
                Also create employee profiles for imported users
            </label>
            <small style="display: block; margin-top: 0.25rem; color: #6b7280;">
                If checked, employee profiles will be created automatically using the employee_reference field from the import file.
            </small>
        </div>
        
        <!-- File Upload -->
        <div class="form-group">
            <label for="import_file">Upload File <span style="color: #dc2626;">*</span></label>
            <div style="margin-top: 0.5rem; padding: 2rem; border: 2px dashed #d1d5db; border-radius: 0; text-align: center; background-color: #f9fafb;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #9ca3af; margin-bottom: 0.5rem;"></i>
                <div style="margin-top: 0.5rem;">
                    <label for="import_file" style="color: #06b6d4; cursor: pointer; font-weight: 500;">
                        Upload a file
                    </label>
                    <span style="color: #6b7280;"> or drag and drop</span>
                </div>
                <input type="file" id="import_file" name="import_file" accept=".csv,.json" required 
                       style="display: none;" onchange="this.nextElementSibling.textContent = this.files[0]?.name || 'No file chosen'">
                <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #6b7280;">No file chosen</p>
                <p style="margin: 0.5rem 0 0; font-size: 0.75rem; color: #9ca3af;">CSV or JSON up to 5MB</p>
            </div>
        </div>
        
        <!-- Format Guide -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;">File Format Guide</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?php echo url('api/download-example.php?type=users&format=csv'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example CSV
                    </a>
                    <a href="<?php echo url('api/download-example.php?type=users&format=json'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example JSON
                    </a>
                </div>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-csv" style="margin-right: 0.5rem;"></i>CSV Format
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Required columns: <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">email</code> (required), 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">first_name</code> (required), 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">last_name</code> (required)
                </p>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Optional columns: <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">employee_reference</code>, 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">password</code> (if not provided, temporary password will be generated)
                </p>
                <pre style="font-size: 0.75rem; color: #6b7280; overflow-x: auto; margin: 0.5rem 0 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0;"><code>email,first_name,last_name,employee_reference
john.doe@example.com,John,Doe,EMP001
jane.smith@example.com,Jane,Smith,EMP002
bob.jones@example.com,Bob,Jones,EMP003</code></pre>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-code" style="margin-right: 0.5rem;"></i>JSON Format
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Array of user objects with required fields: <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">email</code>, 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">first_name</code>, 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">last_name</code>
                </p>
                <pre style="font-size: 0.7rem; color: #6b7280; overflow-x: auto; margin: 0.5rem 0 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0; max-height: 200px; overflow-y: auto;"><code>{
  "users": [
    {
      "email": "john.doe@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "employee_reference": "EMP001"
    },
    {
      "email": "jane.smith@example.com",
      "first_name": "Jane",
      "last_name": "Smith",
      "employee_reference": "EMP002"
    }
  ]
}</code></pre>
            </div>
            
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 0.75rem; margin-top: 1rem; border-radius: 0;">
                <p style="margin: 0; font-size: 0.75rem; color: #92400e;">
                    <strong><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Important:</strong> 
                    If passwords are not provided, temporary passwords will be generated and users will need to change them on first login. 
                    Email addresses must be unique. Existing users with the same email will be updated (if they belong to your organisation).
                </p>
            </div>
        </div>
        
        <!-- Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Import Users
            </button>
            <a href="<?php echo url('admin/users.php'); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Update file name display
document.getElementById('import_file').addEventListener('change', function() {
    const fileName = this.files[0]?.name || 'No file chosen';
    this.nextElementSibling.textContent = fileName;
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>




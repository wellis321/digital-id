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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $importType = $_POST['import_type'] ?? 'units';
        
        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a file to upload.';
        } else {
            $file = $_FILES['import_file'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileExt, ['csv', 'json'])) {
                $error = 'Invalid file type. Only CSV and JSON files are supported.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error = 'File is too large. Maximum file size is 2MB.';
            } else {
                // Process import
                try {
                    if ($importType === 'members') {
                        if ($fileExt === 'csv') {
                            $result = OrganisationalUnits::importMembersFromCsv($organisationId, $file['tmp_name']);
                        } else {
                            $result = OrganisationalUnits::importMembersFromJson($organisationId, $file['tmp_name']);
                        }
                        $successMessage = "Import complete! {$result['members_assigned']} members assigned";
                        if ($result['members_skipped'] > 0) {
                            $successMessage .= ", {$result['members_skipped']} skipped";
                        }
                        if (!empty($result['warnings'])) {
                            $successMessage .= " (" . count($result['warnings']) . " warnings)";
                        }
                        $success = $successMessage;
                    } elseif ($fileExt === 'csv') {
                        $result = OrganisationalUnits::importFromCsv($organisationId, $file['tmp_name']);
                        $successMessage = "Import complete! {$result['units_created']} organisational units created";
                        if (!empty($result['warnings'])) {
                            $successMessage .= " (" . count($result['warnings']) . " warnings)";
                        }
                        $success = $successMessage;
                    } else {
                        $result = OrganisationalUnits::importFromJson($organisationId, $file['tmp_name']);
                        $successMessage = "Import complete! {$result['units_created']} units created, {$result['members_assigned']} members assigned";
                        if (!empty($result['warnings'])) {
                            $successMessage .= " (" . count($result['warnings']) . " warnings)";
                        }
                        $success = $successMessage;
                    }
                    
                    // Store warnings in session if any
                    if (!empty($result['warnings'])) {
                        $_SESSION['import_warnings'] = $result['warnings'];
                    }
                } catch (Exception $e) {
                    $error = 'Import failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Import Organisational Structure';
include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="card">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('admin/organisational-structure.php'); ?>" style="color: #6b7280;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1>Import Organisational Structure</h1>
            <p style="color: #6b7280; margin-top: 0.5rem;"><?php echo htmlspecialchars($org['name']); ?></p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['import_warnings']) && !empty($_SESSION['import_warnings'])): ?>
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0; margin-bottom: 2rem;">
            <h4 style="margin-top: 0; color: #92400e; font-size: 1rem;">Import Warnings</h4>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem; color: #92400e; font-size: 0.875rem;">
                <?php foreach ($_SESSION['import_warnings'] as $warning): ?>
                    <li><?php echo htmlspecialchars($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['import_warnings']); ?>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div style="background-color: #e0f2fe; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 0; margin-bottom: 2rem;">
        <h4 style="margin-top: 0; color: #1e40af; font-size: 1rem;">Bulk Import</h4>
        <p style="margin: 0.5rem 0 0; color: #1e40af; font-size: 0.875rem;">
            Upload a CSV or JSON file to quickly create your organisational structure and assign members. Perfect for organisations with existing team data in spreadsheets or HR systems.
        </p>
    </div>
    
    <!-- Upload Form -->
    <form method="POST" action="" enctype="multipart/form-data" style="max-width: 800px;">
        <?php echo CSRF::tokenField(); ?>
        
        <!-- Import Type -->
        <div class="form-group">
            <label>What are you importing?</label>
            <div style="display: grid; gap: 0.75rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: start; padding: 1rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0; cursor: pointer;">
                    <input type="radio" name="import_type" value="units" checked style="margin-top: 0.25rem; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 500; color: #1f2937; margin-bottom: 0.25rem;">Organisational Units</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">Teams, departments, areas, regions (CSV or JSON)</div>
                    </div>
                </label>
                <label style="display: flex; align-items: start; padding: 1rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0; cursor: pointer;">
                    <input type="radio" name="import_type" value="members" style="margin-top: 0.25rem; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 500; color: #1f2937; margin-bottom: 0.25rem;">Member Assignments</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">Assign users to organisational units (CSV or JSON)</div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- File Upload -->
        <div class="form-group">
            <label for="import_file">Upload File <span style="color: #dc2626;">*</span></label>
            <div style="margin-top: 0.5rem; padding: 2rem; border: 2px dashed #d1d5db; border-radius: 0; text-align: center; background-color: #f9fafb;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #9ca3af; margin-bottom: 0.5rem;"></i>
                <div style="margin-top: 0.5rem;">
                    <label for="import_file" style="color: #2563eb; cursor: pointer; font-weight: 500;">
                        Upload a file
                    </label>
                    <span style="color: #6b7280;"> or drag and drop</span>
                </div>
                <input type="file" id="import_file" name="import_file" accept=".csv,.json" required 
                       style="display: none;" onchange="this.nextElementSibling.textContent = this.files[0]?.name || 'No file chosen'">
                <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #6b7280;">No file chosen</p>
                <p style="margin: 0.5rem 0 0; font-size: 0.75rem; color: #9ca3af;">CSV or JSON up to 2MB</p>
            </div>
        </div>
        
        <!-- Format Guide -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;">File Format Guide</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?php echo url('api/download-example.php?type=units&format=csv'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example CSV (Units)
                    </a>
                    <a href="<?php echo url('api/download-example.php?type=units&format=json'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example JSON (Units)
                    </a>
                    <a href="<?php echo url('api/download-example.php?type=members&format=csv'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example CSV (Members)
                    </a>
                    <a href="<?php echo url('api/download-example.php?type=members&format=json'); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;" download>
                        <i class="fas fa-download"></i> Example JSON (Members)
                    </a>
                </div>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-csv" style="margin-right: 0.5rem;"></i>CSV Format for Organisational Units
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Required columns: <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">name</code> (required), 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">unit_type</code>, 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">parent</code>, 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">description</code>
                </p>
                <pre style="font-size: 0.75rem; color: #6b7280; overflow-x: auto; margin: 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0;"><code>name,unit_type,parent,description
North Region,region,,Regional grouping
Newcastle Area,area,North Region,Newcastle area
Newcastle Team,team,Newcastle Area,Acute care team</code></pre>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-csv" style="margin-right: 0.5rem;"></i>CSV Format for Member Assignments
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Required columns: <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">email</code> (required), 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">unit_name</code> (required), 
                    <code style="background-color: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0;">role</code> (defaults to "member")
                </p>
                <pre style="font-size: 0.75rem; color: #6b7280; overflow-x: auto; margin: 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0;"><code>email,unit_name,role
john@example.com,Newcastle Team,member
jane@example.com,Newcastle Team,lead</code></pre>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-code" style="margin-right: 0.5rem;"></i>JSON Format for Organisational Units
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Hierarchical structure with nested units and members. Supports creating units and assigning members in one import.
                </p>
                <pre style="font-size: 0.7rem; color: #6b7280; overflow-x: auto; margin: 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0; max-height: 200px; overflow-y: auto;"><code>{
  "units": [
    {
      "name": "North Region",
      "unit_type": "region",
      "description": "Regional grouping",
      "children": [
        {
          "name": "Newcastle Area",
          "unit_type": "area",
          "members": [
            {"email": "manager@example.com", "role": "lead"}
          ],
          "children": [
            {
              "name": "Newcastle Team",
              "unit_type": "team",
              "members": [
                {"email": "john@example.com", "role": "member"}
              ]
            }
          ]
        }
      ]
    }
  ]
}</code></pre>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                <h4 style="margin-top: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                    <i class="fas fa-file-code" style="margin-right: 0.5rem;"></i>JSON Format for Member Assignments
                </h4>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Simple array of assignments. Units must already exist in the system.
                </p>
                <pre style="font-size: 0.7rem; color: #6b7280; overflow-x: auto; margin: 0; background-color: #ffffff; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 0;"><code>{
  "assignments": [
    {
      "email": "john@example.com",
      "unit_name": "Newcastle Team",
      "role": "member"
    },
    {
      "email": "jane@example.com",
      "unit_name": "Newcastle Team",
      "role": "lead"
    }
  ]
}</code></pre>
            </div>
            
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 0.75rem; margin-top: 1rem; border-radius: 0;">
                <p style="margin: 0; font-size: 0.75rem; color: #92400e;">
                    <strong><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Important:</strong> 
                    Users must already exist in your organisation before you can assign them to units. 
                    Email addresses must match exactly. Unit names must match existing units exactly (case-sensitive).
                </p>
            </div>
        </div>
        
        <!-- Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Import
            </button>
            <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="btn btn-secondary">Cancel</a>
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

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


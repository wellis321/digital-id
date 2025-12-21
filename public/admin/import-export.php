<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();

$employee = Employee::findByUserId(Auth::getUserId());

if (!$employee) {
    header('Location: ' . url('admin/employees.php?error=employee_not_found'));
    exit;
}

$pageTitle = 'Import/Export ID Data';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="card">
    <h1>Import/Export ID Card Data</h1>
    <p>Export your digital ID card data for portability, or import data from a previous organization.</p>
    
    <h2>Export ID Card Data</h2>
    <p>Download your ID card data as a JSON file that can be imported when moving to a new organization.</p>
    <a href="<?php echo url('api/export-id.php'); ?>" class="btn btn-primary" download>Export ID Data</a>
    
    <h2 style="margin-top: 2rem;">Import ID Card Data</h2>
    <p>Upload a JSON file containing your ID card data from a previous organization.</p>
    
    <form method="POST" action="<?php echo url('api/import-id.php'); ?>" enctype="multipart/form-data">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label for="id_data">ID Card Data File (JSON)</label>
            <input type="file" id="id_data" name="id_data" accept=".json,application/json" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Import ID Data</button>
    </form>
    
    <div style="margin-top: 2rem; padding: 1rem; background-color: #f0f0f0; border-radius: 4px;">
        <h3>Note</h3>
        <p>When importing ID card data:</p>
        <ul style="margin-left: 1.5rem;">
            <li>Your employee reference and organization cannot be changed</li>
            <li>Only the ID card data structure will be updated</li>
            <li>You may need to request a new ID card after importing</li>
        </ul>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>


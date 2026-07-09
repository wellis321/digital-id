<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Organisational Structure - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Organisational Structure</h1>
            <p>Manage your organisation's hierarchical structure with teams, departments, areas, and regions.</p>
            
            <h2>Understanding Organisational Units</h2>
            <p>Organisational units allow you to structure your organisation hierarchically:</p>
            <ul>
                <li><strong>Regions:</strong> Top-level geographical or organisational groupings</li>
                <li><strong>Areas:</strong> Sub-divisions within regions</li>
                <li><strong>Teams:</strong> Individual teams or departments</li>
                <li><strong>Custom Types:</strong> Create your own unit types as needed</li>
            </ul>
            
            <h2>Creating Organisational Units</h2>
            <h3>Manual Creation</h3>
            <ol class="step-list">
                <li>Go to "Organisational Structure" in the admin menu</li>
                <li>Click "Add New Unit"</li>
                <li>Enter unit details:
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Name (required)</li>
                        <li>Unit type (optional)</li>
                        <li>Parent unit (for hierarchical structure)</li>
                        <li>Description (optional)</li>
                    </ul>
                </li>
                <li>Save the unit</li>
            </ol>
            
            <h3>Bulk Import</h3>
            <p>Import your organisational structure from CSV or JSON files. See the <a href="<?php echo url('docs-import-export.php'); ?>">Import & Export</a> section for details.</p>
            
            <h2>Assigning Members</h2>
            <h3>Adding Members to Units</h3>
            <ol>
                <li>Go to the organisational unit</li>
                <li>Click "Members"</li>
                <li>Click "Add Member"</li>
                <li>Select a user by email</li>
                <li>Choose a role (member, lead, etc.)</li>
                <li>Save the assignment</li>
            </ol>
            
            <h3>Member Roles</h3>
            <ul>
                <li><strong>Member:</strong> Standard member of the unit</li>
                <li><strong>Lead:</strong> Unit leader or manager</li>
                <li><strong>Custom Roles:</strong> Create custom roles as needed</li>
            </ul>
            
            <h2>Unit Administrators</h2>
            <p>You can assign unit administrators who have specific permissions for their unit:</p>
            <ul>
                <li>Manage members within their unit</li>
                <li>Manage child units (if permitted)</li>
                <li>View unit-specific information</li>
            </ul>
            
            <h2>Hierarchical Structure</h2>
            <p>Organisational units can be nested to create a hierarchical structure:</p>
            <pre><code>North Region
  └── Newcastle Area
      ├── Newcastle Team
      └── Newcastle Admin
  └── Leeds Area
      └── Leeds Team
South Region
  └── London Area
      └── London Team</code></pre>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Tip</h4>
                <p>When creating units, start with top-level units first, then add child units. This ensures parent relationships are properly established.</p>
            </div>
            
            <h2>Importing Structure</h2>
            <p>You can import your entire organisational structure from CSV or JSON:</p>
            <ul>
                <li>CSV format for simple unit creation</li>
                <li>JSON format for hierarchical structures with members</li>
                <li>Bulk member assignment via CSV</li>
            </ul>
            
            <p>See the <a href="<?php echo url('docs-import-export.php'); ?>">Import & Export</a> section for file formats and examples.</p>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

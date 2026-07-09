<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Import & Export - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Import & Export</h1>
            <p>Import and export data to streamline your organisation's setup and data portability.</p>
            
            <h2>Importing Organisational Structure</h2>
            <p>You can import your organisational structure from CSV or JSON files.</p>
            
            <h3>CSV Format for Units</h3>
            <p>Required columns: <code>name</code> (required), <code>unit_type</code>, <code>parent</code>, <code>description</code></p>
            <pre><code>name,unit_type,parent,description
North Region,region,,Regional grouping
Newcastle Area,area,North Region,Newcastle area
Newcastle Team,team,Newcastle Area,Acute care team</code></pre>
            
            <h3>JSON Format for Units</h3>
            <p>Hierarchical structure with nested units and members:</p>
            <pre><code>{
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
            
            <h2>Importing Member Assignments</h2>
            <h3>CSV Format for Members</h3>
            <p>Required columns: <code>email</code> (required), <code>unit_name</code> (required), <code>role</code></p>
            <pre><code>email,unit_name,role
john@example.com,Newcastle Team,member
jane@example.com,Newcastle Team,lead</code></pre>
            
            <h3>JSON Format for Members</h3>
            <pre><code>{
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
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Users must already exist in your organisation before you can assign them to units. Email addresses must match exactly. Unit names must match existing units exactly (case-sensitive).</p>
            </div>
            
            <h2>Downloading Example Files</h2>
            <p>You can download example CSV and JSON files from the import page:</p>
            <ol>
                <li>Go to "Organisational Structure" → "Import"</li>
                <li>Click the download buttons for example files</li>
                <li>Use these as templates for your own imports</li>
            </ol>
            
            <h2>Exporting ID Card Data</h2>
            <p>Employees can export their ID card data when moving organisations:</p>
            <ol>
                <li>Go to "Import/Export ID Data"</li>
                <li>Click "Export ID Data"</li>
                <li>A JSON file will be downloaded</li>
                <li>Keep this file safe for import at the new organisation</li>
            </ol>
            
            <h2>Importing ID Card Data</h2>
            <p>When joining a new organisation, employees can import their previous ID card data:</p>
            <ol>
                <li>Go to "Import/Export ID Data"</li>
                <li>Upload the JSON file exported from the previous organisation</li>
                <li>The ID card data structure will be updated</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Note</h4>
                <p>Employee reference and organisation cannot be changed when importing. Only the ID card data structure will be updated.</p>
            </div>
            
            <h2>File Size Limits</h2>
            <ul>
                <li>Maximum file size: 2MB</li>
                <li>Supported formats: CSV, JSON</li>
                <li>File encoding: UTF-8 recommended</li>
            </ul>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

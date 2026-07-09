<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Check-In Sessions - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Check-In Sessions</h1>
            <p>Digital ID includes a comprehensive check-in system for tracking attendance during fire drills, safety meetings, emergencies, and other events. This feature integrates seamlessly with Microsoft 365 for automatic data synchronisation.</p>
            
            <h2>Overview</h2>
            <p>Check-in sessions allow organisations to:</p>
            <ul>
                <li>Create timed sessions for fire drills, safety meetings, and emergencies</li>
                <li>Track staff attendance in real-time</li>
                <li>Allow staff to check in using QR codes or manual entry</li>
                <li>Export attendance records for compliance reporting</li>
                <li>Automatically sync data to Microsoft 365 (SharePoint, Power Automate, Teams)</li>
            </ul>
            
            <h2>Creating a Check-In Session</h2>
            <p>Organisation administrators can create check-in sessions from the admin panel:</p>
            <ol class="step-list">
                <li>Navigate to <strong>Organisation</strong> → <strong>Check-In Sessions</strong></li>
                <li>Click <strong>"Create New Session"</strong></li>
                <li>Enter a session name (e.g., "Fire Drill - Main Building")</li>
                <li>Select the session type:
                    <ul>
                        <li><strong>Fire Drill:</strong> Planned fire evacuation practice</li>
                        <li><strong>Fire Alarm:</strong> Actual fire alarm activation</li>
                        <li><strong>Safety Meeting:</strong> Health and safety meeting attendance</li>
                        <li><strong>Emergency:</strong> Other emergency situations</li>
                    </ul>
                </li>
                <li>Optionally add a location name</li>
                <li>Click <strong>"Create Session"</strong></li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Session Types</h4>
                <p>Session types help categorise different events and can be used for reporting and filtering. All session types support the same check-in functionality.</p>
            </div>
            
            <h2>Staff Check-In Process</h2>
            <p>Once a session is active, staff can check in using two methods:</p>
            
            <h3>Method 1: QR Code Check-In</h3>
            <ol class="step-list">
                <li>Navigate to <strong>Check In</strong> in the main menu</li>
                <li>Select the active session</li>
                <li>Click <strong>"Check In with QR Code"</strong></li>
                <li>Display your QR code on your device</li>
                <li>The system will automatically verify and check you in</li>
            </ol>
            
            <h3>Method 2: Manual Check-In</h3>
            <ol class="step-list">
                <li>Navigate to <strong>Check In</strong> in the main menu</li>
                <li>Select the active session</li>
                <li>Click <strong>"Check In"</strong> button</li>
                <li>You'll be immediately checked in</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Check-Out</h4>
                <p>Staff can check out from a session at any time. This is useful for tracking when people leave during long sessions or emergencies.</p>
            </div>
            
            <h2>Viewing Session Attendance</h2>
            <p>Administrators can view real-time attendance for any session:</p>
            <ol class="step-list">
                <li>Go to <strong>Organisation</strong> → <strong>Check-In Sessions</strong></li>
                <li>Click on a session to view details</li>
                <li>See the list of all check-ins with timestamps and methods</li>
                <li>Active sessions automatically refresh every 10 seconds</li>
            </ol>
            
            <h2>Exporting Attendance</h2>
            <p>Export attendance records for compliance and reporting:</p>
            <ol class="step-list">
                <li>Open a session from the Check-In Sessions list</li>
                <li>Click <strong>"Export Attendance"</strong></li>
                <li>A CSV file will be downloaded with:
                    <ul>
                        <li>Employee names and references</li>
                        <li>Check-in and check-out times</li>
                        <li>Check-in method (QR scan or manual)</li>
                        <li>Location information</li>
                        <li>Status (checked in or checked out)</li>
                    </ul>
                </li>
            </ol>
            
            <h2>Ending a Session</h2>
            <p>When a session is complete:</p>
            <ol class="step-list">
                <li>Open the session details page</li>
                <li>Click <strong>"End Session"</strong></li>
                <li>Confirm the action</li>
                <li>The session will be marked as ended and no new check-ins will be allowed</li>
            </ol>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>Once a session is ended, staff cannot check in or out. However, you can still view and export the attendance records.</p>
            </div>
            
            <h2>Microsoft 365 Integration</h2>
            <p>Digital ID can automatically synchronise check-in data with Microsoft 365 services:</p>
            
            <h3>SharePoint Lists</h3>
            <p>Check-in data can be automatically synced to SharePoint Lists for integration with existing workflows and reporting tools.</p>
            <ol class="step-list">
                <li>Go to <strong>Organisation</strong> → <strong>Microsoft 365 Settings</strong></li>
                <li>Enable Microsoft 365 synchronisation</li>
                <li>Enter your SharePoint site URL</li>
                <li>Enter the SharePoint List ID where check-ins should be stored</li>
                <li>Save settings</li>
            </ol>
            
            <h3>Power Automate</h3>
            <p>Trigger Power Automate workflows when check-ins occur:</p>
            <ol class="step-list">
                <li>Create a Power Automate flow with a webhook trigger</li>
                <li>Copy the webhook URL</li>
                <li>Paste it into the Microsoft 365 Settings page</li>
                <li>Check-ins will now trigger your workflow automatically</li>
            </ol>
            
            <h3>Microsoft Teams</h3>
            <p>Send notifications to Teams channels when sessions start:</p>
            <ol class="step-list">
                <li>Get your Teams channel ID</li>
                <li>Enter it in the Microsoft 365 Settings page</li>
                <li>Notifications will be sent when sessions are created</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Prerequisites</h4>
                <p>Microsoft 365 integration requires Microsoft Entra (Azure AD) to be configured first. See the <a href="<?php echo url('docs-entra-integration.php'); ?>">Microsoft Entra Integration</a> guide for setup instructions.</p>
            </div>
            
            <h2>Use Cases</h2>
            
            <h3>Fire Drill Tracking</h3>
            <p>During fire drills, create a session and have all staff check in at the assembly point. This ensures accurate headcounts and helps identify who may still be in the building.</p>
            
            <h3>Safety Meeting Attendance</h3>
            <p>Track attendance at mandatory safety meetings. Export records for compliance reporting and training records.</p>
            
            <h3>Emergency Evacuations</h3>
            <p>In real emergencies, quickly create a session and track who has safely evacuated. This information is critical for emergency services.</p>
            
            <h2>Best Practices</h2>
            <ul>
                <li><strong>Create sessions in advance:</strong> For planned events like fire drills, create the session before the event starts</li>
                <li><strong>Use clear naming:</strong> Name sessions descriptively (e.g., "Fire Drill - Main Building - 15 Jan 2024")</li>
                <li><strong>End sessions promptly:</strong> End sessions when complete to prevent accidental check-ins</li>
                <li><strong>Export regularly:</strong> Export attendance records after each session for your records</li>
                <li><strong>Test the system:</strong> Run a test session before important events to ensure staff know how to check in</li>
            </ul>
            
            <h2>Access Control</h2>
            <p>Check-in sessions are organisation-specific. Only staff from the same organisation can check in to a session. Organisation administrators can create and manage sessions for their organisation.</p>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

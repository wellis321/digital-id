<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'User Stories - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>User Stories</h1>
            <p>Digital ID helps organisations and individuals in various scenarios. Below are user stories that demonstrate how different people use the system to solve real-world challenges.</p>
            
            <h2>Social Care Worker Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-user"></i> As a social care worker...</h4>
            </div>
            
            <h3>Bank Transactions</h3>
            <p><strong>Story:</strong> As a social care worker, I need to prove my identity when acting on behalf of vulnerable clients at banks, so that I can complete financial transactions legally and securely.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>I open my Digital ID app on my phone before going to the bank</li>
                <li>At the bank, I show my digital ID card to the bank staff</li>
                <li>Bank staff scan the QR code using the verification page</li>
                <li>The system confirms my identity, employee status, and organisation</li>
                <li>The bank can proceed with the transaction, knowing I'm a verified employee</li>
                <li>The verification is logged for compliance and audit purposes</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>No need to carry physical ID cards that can be lost or stolen</li>
                    <li>Quick verification process - no waiting for manual checks</li>
                    <li>Secure digital verification replaces paper-based authorisation</li>
                    <li>Complete audit trail for compliance</li>
                </ul>
            </div>
            
            <h3>Service User Visits</h3>
            <p><strong>Story:</strong> As a social care worker visiting service users in their homes, I need service users and their families to be able to verify my identity, so they feel safe and confident that I'm a legitimate employee.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Before visiting, I share the verification link with the service user or their family</li>
                <li>When I arrive, I show my digital ID card</li>
                <li>They can scan the QR code or visit the verification page</li>
                <li>The system displays my verified name, photo, and employee reference</li>
                <li>They can confirm I'm the person they're expecting</li>
                <li>They feel confident and secure knowing I'm verified</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Builds trust and confidence with service users and families</li>
                    <li>Easy verification process - no technical knowledge required</li>
                    <li>Service users can verify staff independently</li>
                    <li>Reduces anxiety and security concerns</li>
                </ul>
            </div>
            
            <h3>Emergency Situations</h3>
            <p><strong>Story:</strong> As a social care worker, I need to quickly prove my identity during emergencies, fire drills, or safety checks, so that emergency services and site managers can verify I'm authorised to be on site.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>During an emergency or fire drill, I quickly access my digital ID card</li>
                <li>I can use visual verification - showing my photo and details immediately</li>
                <li>For logged verification, emergency staff can scan the QR code</li>
                <li>The system confirms my identity and employment status instantly</li>
                <li>Attendance and safety checks are automatically logged</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Instant access - no need to search for physical ID cards</li>
                    <li>Works even if phone is offline (visual verification)</li>
                    <li>Automatic logging for safety compliance</li>
                    <li>Fast verification during critical situations</li>
                </ul>
            </div>
            
            <h3>Lone Working</h3>
            <p><strong>Story:</strong> As a social care worker working alone or late hours, I need to be able to prove my identity if challenged, so that security staff or members of the public can verify I'm authorised to be on site.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>While working alone or during late hours, I keep my digital ID accessible</li>
                <li>If questioned by security or concerned members of the public, I show my ID card</li>
                <li>They can quickly verify my identity using the QR code</li>
                <li>The system confirms I'm a current, active employee</li>
                <li>Verification attempts are logged for security records</li>
            </ol>
            
            <h2>Organisation Administrator Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-user-shield"></i> As an organisation administrator...</h4>
            </div>
            
            <h3>Employee Management</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to easily manage employee ID cards, approve photos, and revoke access when staff leave, so that our organisation maintains secure, up-to-date identification records.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>I create employee profiles for new staff members</li>
                <li>I can approve or reject employee photo uploads</li>
                <li>I set employee references and expiration dates</li>
                <li>When staff leave, I can instantly revoke their ID cards</li>
                <li>Revoked cards cannot be verified, even with valid tokens</li>
                <li>All changes are tracked and auditable</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Centralised employee management</li>
                    <li>Instant revocation when staff leave</li>
                    <li>Photo approval workflow ensures quality</li>
                    <li>Complete audit trail for compliance</li>
                </ul>
            </div>
            
            <h3>Compliance and Auditing</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to maintain complete records of all ID verifications for regulatory compliance and quality assurance reviews.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Every verification attempt is automatically logged</li>
                <li>I can view verification logs with filters by date, employee, or type</li>
                <li>I can export logs as CSV for compliance reporting</li>
                <li>Logs include timestamps, verification methods, results, and verifier information</li>
                <li>Complete audit trail for inspections and reviews</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Automatic logging - no manual record keeping required</li>
                    <li>Exportable data for regulatory submissions</li>
                    <li>Comprehensive audit trail</li>
                    <li>Searchable and filterable logs</li>
                </ul>
            </div>
            
            <h3>Meeting Attendance</h3>
            <p><strong>Story:</strong> As an organisation administrator, I need to track attendance at meetings, training sessions, and mandatory briefings, so that we have digital records for compliance and quality assurance.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>Staff scan their QR codes at the start of meetings</li>
                <li>Each scan is logged with timestamp and employee details</li>
                <li>I can view attendance records in the verification logs</li>
                <li>I can filter logs by date range to see meeting attendance</li>
                <li>Digital records replace paper sign-in sheets</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Digital records - no lost paperwork</li>
                    <li>Automatic timestamping</li>
                    <li>Easy to query and report on attendance</li>
                    <li>Integrated with existing verification system</li>
                </ul>
            </div>
            
            <h2>Service User and Family Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-users"></i> As a service user, family member, or carer...</h4>
            </div>
            
            <h3>Verifying Staff Identity</h3>
            <p><strong>Story:</strong> As a service user's family member, I need to be able to verify that the person visiting my relative is a legitimate employee, so that I feel confident and secure about who is entering their home.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>When a care worker arrives, they show me their digital ID card</li>
                <li>I can scan the QR code with my phone or visit the verification website</li>
                <li>The system displays the worker's verified name, photo, and employee reference</li>
                <li>I can confirm they match the photo and are who they claim to be</li>
                <li>I see the organisation name to confirm they're from the right provider</li>
                <li>I feel confident knowing the person is verified and legitimate</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Easy verification - no technical knowledge needed</li>
                    <li>Works on any device with internet access</li>
                    <li>Provides peace of mind and confidence</li>
                    <li>Independent verification - no need to contact the organisation</li>
                </ul>
            </div>
            
            <h2>Bank and Financial Institution Stories</h2>
            
            <div class="info-box" style="margin-bottom: 2rem;">
                <h4><i class="fas fa-university"></i> As a bank employee...</h4>
            </div>
            
            <h3>Verifying Care Worker Identity</h3>
            <p><strong>Story:</strong> As a bank employee, I need to verify that a person claiming to act on behalf of a vulnerable client is a legitimate, authorised employee of a care organisation, so that I can proceed with financial transactions securely and legally.</p>
            <p><strong>How Digital ID helps:</strong></p>
            <ol class="step-list">
                <li>A person arrives claiming to be a care worker acting on behalf of a client</li>
                <li>They show me their digital ID card on their phone</li>
                <li>I scan the QR code using the public verification page</li>
                <li>The system displays their verified identity and employment status</li>
                <li>I can confirm their photo matches, see their employee reference, and organisation</li>
                <li>I can proceed with the transaction, knowing they're verified</li>
                <li>The verification is logged for our records</li>
            </ol>
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Benefits</h4>
                <ul style="margin-top: 0.5rem;">
                    <li>Quick verification process - no phone calls needed</li>
                    <li>Secure digital verification replaces paper authorisation</li>
                    <li>Time-limited tokens prevent replay attacks</li>
                    <li>Automatic logging provides audit trail</li>
                    <li>Confirms current employment status</li>
                </ul>
            </div>
            
            <h2>Use Case Mapping</h2>
            <p>Digital ID supports various scenarios across different contexts:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Use Case</th>
                        <th>Primary User</th>
                        <th>Verification Method</th>
                        <th>Key Benefit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bank transactions</td>
                        <td>Care worker</td>
                        <td>QR code</td>
                        <td>Secure, logged verification</td>
                    </tr>
                    <tr>
                        <td>Service user visits</td>
                        <td>Care worker, Service user</td>
                        <td>Visual, QR code</td>
                        <td>Building trust and confidence</td>
                    </tr>
                    <tr>
                        <td>Emergency situations</td>
                        <td>Care worker</td>
                        <td>Visual, QR code</td>
                        <td>Quick identity verification</td>
                    </tr>
                    <tr>
                        <td>Lone working</td>
                        <td>Care worker</td>
                        <td>Visual, QR code</td>
                        <td>Security and safety</td>
                    </tr>
                    <tr>
                        <td>Meeting attendance</td>
                        <td>All staff</td>
                        <td>QR code</td>
                        <td>Digital attendance records</td>
                    </tr>
                    <tr>
                        <td>Fire drills</td>
                        <td>All staff</td>
                        <td>QR code</td>
                        <td>Safety compliance logging</td>
                    </tr>
                    <tr>
                        <td>Staff verification</td>
                        <td>Administrator</td>
                        <td>Verification logs</td>
                        <td>Compliance and auditing</td>
                    </tr>
                    <tr>
                        <td>Identity confirmation</td>
                        <td>Family members, Carers</td>
                        <td>Visual, QR code</td>
                        <td>Peace of mind</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Future Use Cases</h2>
            <p>Digital ID is designed to support additional use cases in the future:</p>
            <ul>
                <li><strong>Visitor management:</strong> Temporary access credentials for visitors and contractors</li>
                <li><strong>Asset tracking:</strong> Linking staff to equipment and vehicles</li>
            </ul>
            
            <div class="success-box" style="margin-top: 2rem;">
                <h4><i class="fas fa-check-circle"></i> Already Available</h4>
                <p>The following features are already fully implemented:</p>
                <ul style="margin-top: 0.5rem;">
                    <li><strong>Building access systems:</strong> QR code and NFC integration for turnstiles and door access (see <a href="<?php echo url('docs-building-access.php'); ?>">Building Access Integration</a>)</li>
                    <li><strong>Attendance tracking:</strong> Automated check-in sessions for fire drills, safety meetings, and emergencies using QR codes</li>
                    <li><strong>Time and attendance:</strong> Clock in/out functionality via check-in sessions</li>
                </ul>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-lightbulb"></i> Have Your Own Story?</h4>
                <p>If you're using Digital ID in an interesting way, we'd love to hear about it! Visit our <a href="<?php echo url('case-studies.php'); ?>">Case Studies</a> page to share your story and help others understand how Digital ID can be used.</p>
            </div>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

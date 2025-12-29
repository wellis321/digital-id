<?php
require_once dirname(__DIR__) . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $organisationName = trim($_POST['organisation_name'] ?? '');
        $contactName = trim($_POST['contact_name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($organisationName) || empty($contactName) || empty($contactEmail)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Send email to support
            $subject = 'Case Study Interest - ' . htmlspecialchars($organisationName);
            $emailBody = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .field { margin-bottom: 15px; }
                        .label { font-weight: bold; color: #1f2937; }
                        .value { color: #4b5563; margin-top: 5px; }
                        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Case Study Interest</h1>
                        </div>
                        <div class='content'>
                            <div class='field'>
                                <div class='label'>Organisation Name:</div>
                                <div class='value'>" . htmlspecialchars($organisationName) . "</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Contact Name:</div>
                                <div class='value'>" . htmlspecialchars($contactName) . "</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Contact Email:</div>
                                <div class='value'>" . htmlspecialchars($contactEmail) . "</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Contact Phone:</div>
                                <div class='value'>" . htmlspecialchars($contactPhone ?: 'Not provided') . "</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Message:</div>
                                <div class='value'>" . nl2br(htmlspecialchars($message ?: 'No additional message')) . "</div>
                            </div>
                        </div>
                        <div class='footer'>
                            <p>This is an automated email from " . htmlspecialchars(APP_NAME) . "</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . APP_NAME . " <" . CONTACT_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . $contactEmail . "\r\n";
            
            if (mail(CONTACT_EMAIL, $subject, $emailBody, $headers)) {
                $success = 'Thank you for your interest! We\'ve received your message and will be in touch shortly to discuss how we can work together on a case study.';
            } else {
                $error = 'Sorry, there was an error sending your message. Please try again or contact us directly at ' . CONTACT_EMAIL;
            }
        }
    }
}

$pageTitle = 'Case Studies';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Case Studies</h1>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 1.5rem; margin-bottom: 2rem; border-radius: 0;">
        <h3 style="margin-top: 0; color: #1e40af;">We're Looking for Partners</h3>
        <p style="margin-bottom: 0.75rem; color: #1e40af;">
            We're building a library of case studies to showcase how organisations are using Digital ID to improve their identity management, 
            enhance security, and streamline operations. If you're using Digital ID and would like to share your story, we'd love to work with you.
        </p>
    </div>
    
    <div style="margin-bottom: 3rem;">
        <h2>What We're Looking For</h2>
        <p>
            We're interested in working with organisations that are using Digital ID to solve real challenges. 
            Whether you're using it for bank transactions, emergency verification, compliance, or any other use case, 
            your story could help other organisations understand the value of digital identity management.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div style="padding: 1.5rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #eff6ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-handshake" style="font-size: 1.5rem; color: #2563eb;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">Collaboration</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">We'll work together to tell your story accurately and highlight the benefits you've experienced.</p>
            </div>
            
            <div style="padding: 1.5rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #eff6ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-chart-line" style="font-size: 1.5rem; color: #2563eb;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">Real Results</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">Share measurable outcomes, time savings, cost reductions, or improvements in security and compliance.</p>
            </div>
            
            <div style="padding: 1.5rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #eff6ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-users" style="font-size: 1.5rem; color: #2563eb;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">Diverse Stories</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">We're interested in organisations of all sizes and use cases - every story helps others understand the possibilities.</p>
            </div>
        </div>
    </div>
    
    <div style="margin-bottom: 3rem;">
        <h2>What's Involved</h2>
        <p>If you're interested in participating, here's what the process typically looks like:</p>
        <ol style="margin-top: 1rem; padding-left: 1.5rem; line-height: 1.8;">
            <li><strong>Initial Discussion:</strong> We'll have a conversation to understand your use case and what you'd like to share.</li>
            <li><strong>Content Development:</strong> We'll work together to develop the case study content, ensuring it accurately represents your experience.</li>
            <li><strong>Review & Approval:</strong> You'll have the opportunity to review and approve the final case study before it's published.</li>
            <li><strong>Publication:</strong> Once approved, we'll publish the case study on our website and may use it in marketing materials (with your permission).</li>
        </ol>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <p style="margin-top: 1.5rem;">
            <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">Return to Home</a>
        </p>
    <?php else: ?>
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 2rem; margin-top: 2rem; border-radius: 0;">
            <h2 style="margin-top: 0;">Get in Touch</h2>
            <p style="margin-bottom: 1.5rem;">
                If you're interested in working with us on a case study, please fill out the form below and we'll get back to you soon.
            </p>
            
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                
                <div class="form-group">
                    <label for="organisation_name">Organisation Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="organisation_name" name="organisation_name" required 
                           value="<?php echo htmlspecialchars($_POST['organisation_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_name">Your Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" required 
                           value="<?php echo htmlspecialchars($_POST['contact_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email <span style="color: #dc2626;">*</span></label>
                    <input type="email" id="contact_email" name="contact_email" required 
                           value="<?php echo htmlspecialchars($_POST['contact_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_phone">Phone Number</label>
                    <input type="tel" id="contact_phone" name="contact_phone" 
                           value="<?php echo htmlspecialchars($_POST['contact_phone'] ?? ''); ?>">
                    <small>Optional - helpful if we need to discuss details</small>
                </div>
                
                <div class="form-group">
                    <label for="message">Tell Us About Your Experience</label>
                    <textarea id="message" name="message" rows="5" 
                              placeholder="How are you using Digital ID? What benefits have you seen? What would you like to share?"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    <small>Optional - but helpful for us to understand your story</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0;">
            <h4 style="margin-top: 0; color: #92400e;">
                <i class="fas fa-info-circle"></i> Prefer to Email Directly?
            </h4>
            <p style="color: #92400e; margin-bottom: 0;">
                You can also reach us directly at <a href="mailto:<?php echo CONTACT_EMAIL; ?>" style="color: #92400e; font-weight: 500;"><?php echo CONTACT_EMAIL; ?></a> 
                if you'd prefer to discuss case study opportunities via email.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>





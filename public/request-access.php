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
        $estimatedSeats = intval($_POST['estimated_seats'] ?? 0);
        $domain = trim($_POST['domain'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($organisationName) || empty($contactName) || empty($contactEmail) || empty($domain)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($estimatedSeats < 1) {
            $error = 'Please provide an estimate of how many seats you need.';
        } else {
            // Send email to support
            $subject = 'New Organisation Access Request - ' . htmlspecialchars($organisationName);
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
                            <h1>New Organisation Access Request</h1>
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
                                <div class='label'>Email Domain:</div>
                                <div class='value'>" . htmlspecialchars($domain) . "</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Estimated Seats:</div>
                                <div class='value'>" . htmlspecialchars($estimatedSeats) . "</div>
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
                $success = 'Thank you for your interest! We\'ve received your request and will be in touch shortly to discuss your organisation\'s needs and set up your account.';
            } else {
                $error = 'Sorry, there was an error sending your request. Please try again or contact us directly at ' . CONTACT_EMAIL;
            }
        }
    }
}

$pageTitle = 'Request Access';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Request Access for Your Organisation</h1>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 1.5rem; margin-bottom: 2rem; border-radius: 0;">
        <h3 style="margin-top: 0; color: #1e40af;">How It Works</h3>
        <p style="margin-bottom: 0.75rem; color: #1e40af;">Getting started with Digital ID is a simple three-step process:</p>
        <ol style="margin: 0.75rem 0 0 1.5rem; color: #1e40af; line-height: 1.8;">
            <li><strong>Contact Us:</strong> Fill out the form below with your organisation details. We'll discuss your needs, including how many seats you require and who will be your administrators.</li>
            <li><strong>We Set Up Your Organisation:</strong> Once we've confirmed the details, we'll create your organisation account, configure your domain, and allocate your seats.</li>
            <li><strong>You Register & Log In:</strong> After setup is complete, you and your team can register using your organisation email addresses and start using Digital ID.</li>
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
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            
            <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Organisation Details</h2>
            
            <div class="form-group">
                <label for="organisation_name">Organisation Name <span style="color: #dc2626;">*</span></label>
                <input type="text" id="organisation_name" name="organisation_name" required 
                       value="<?php echo htmlspecialchars($_POST['organisation_name'] ?? ''); ?>">
                <small>The full name of your organisation</small>
            </div>
            
            <div class="form-group">
                <label for="domain">Email Domain <span style="color: #dc2626;">*</span></label>
                <input type="text" id="domain" name="domain" required placeholder="example.com"
                       value="<?php echo htmlspecialchars($_POST['domain'] ?? ''); ?>">
                <small>The email domain your organisation uses (e.g., example.com). Users with emails ending in @example.com will be associated with your organisation.</small>
            </div>
            
            <div class="form-group">
                <label for="estimated_seats">Estimated Number of Seats <span style="color: #dc2626;">*</span></label>
                <input type="number" id="estimated_seats" name="estimated_seats" required min="1" 
                       value="<?php echo htmlspecialchars($_POST['estimated_seats'] ?? ''); ?>">
                <small>How many users do you expect to have? This helps us allocate the right number of seats for your organisation.</small>
            </div>
            
            <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Contact Information</h2>
            
            <div class="form-group">
                <label for="contact_name">Your Name <span style="color: #dc2626;">*</span></label>
                <input type="text" id="contact_name" name="contact_name" required 
                       value="<?php echo htmlspecialchars($_POST['contact_name'] ?? ''); ?>">
                <small>The name of the person we should contact about this request</small>
            </div>
            
            <div class="form-group">
                <label for="contact_email">Your Email <span style="color: #dc2626;">*</span></label>
                <input type="email" id="contact_email" name="contact_email" required 
                       value="<?php echo htmlspecialchars($_POST['contact_email'] ?? ''); ?>">
                <small>We'll use this email to contact you about setting up your organisation</small>
            </div>
            
            <div class="form-group">
                <label for="contact_phone">Phone Number</label>
                <input type="tel" id="contact_phone" name="contact_phone" 
                       value="<?php echo htmlspecialchars($_POST['contact_phone'] ?? ''); ?>">
                <small>Optional - helpful if we need to discuss your requirements</small>
            </div>
            
            <div class="form-group">
                <label for="message">Additional Information</label>
                <textarea id="message" name="message" rows="5" 
                          placeholder="Tell us about your organisation, specific requirements, or any questions you have..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <small>Optional - any additional details that would help us understand your needs</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background-color: #f9fafb; border-radius: 0;">
            <h3 style="margin-top: 0;">What Happens Next?</h3>
            <p>After you submit this form:</p>
            <ul style="line-height: 1.8;">
                <li>We'll review your request and contact you within 1-2 business days</li>
                <li>We'll discuss your specific requirements, including seat allocation and administrator setup</li>
                <li>Once confirmed, we'll create your organisation account and configure your domain</li>
                <li>You'll receive instructions on how to register and get started</li>
            </ul>
            <p style="margin-top: 1rem; margin-bottom: 0;">
                <strong>Questions?</strong> Contact us directly at <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


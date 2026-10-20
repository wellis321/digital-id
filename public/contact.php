<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Contact Us';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="max-width: 800px; margin: 2rem auto;">
    <h1>Get in Touch</h1>
    <p style="color: #5b6371; font-size: 1.125rem; margin-bottom: 2rem;">
        Have questions about Digital ID? We're here to help. Get in touch with us using any of the methods below.
    </p>
    
    <div style="display: grid; gap: 2rem; margin-top: 3rem;">
        <!-- Email -->
        <div style="padding: 2rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0;">
            <div style="display: flex; align-items: start; gap: 1.5rem;">
                <div style="width: 64px; height: 64px; background-color: #f0f9ff; border-radius: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-envelope" style="font-size: 1.75rem; color: #0e7490;"></i>
                </div>
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 1.5rem; color: #111827;">Email</h2>
                    <p style="color: #5b6371; margin: 0 0 1rem 0;">
                        Send us an email and we'll get back to you as soon as possible.
                    </p>
                    <a href="mailto:<?php echo CONTACT_EMAIL; ?>" style="font-size: 1.125rem; color: #0e7490; text-decoration: none; font-weight: 600;">
                        <?php echo CONTACT_EMAIL; ?>
                        <i class="fas fa-external-link-alt" style="margin-left: 0.5rem; font-size: 0.875rem;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 3rem; padding: 2rem; background: #f0f9ff; border: 2px solid #0e7490; border-radius: 0;">
        <h2 style="margin: 0 0 1rem 0; color: #0369a1;">
            <i class="fas fa-info-circle"></i> Response Times
        </h2>
        <p style="margin: 0; color: #0369a1; line-height: 1.6;">
            We aim to respond to all enquiries within 24-48 hours during business days. For urgent matters, please mention this in your email subject line.
        </p>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


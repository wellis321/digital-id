<?php
require_once dirname(__DIR__) . '/config/config.php';

// This page is publicly accessible - no login required
$pageTitle = 'Photo Guidelines';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div style="margin-bottom: 2rem;">
        <a href="<?php echo url('upload-photo.php'); ?>" style="color: #6b7280; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Upload Photo
        </a>
        <h1 style="margin-top: 0;">Photo Guidelines for Digital ID Cards</h1>
        <p style="color: #6b7280;">
            Follow these guidelines to ensure your photo is approved for use on your digital ID card.
        </p>
    </div>
    
    <!-- Photo Guidelines -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 4px solid #3b82f6; padding: 2rem; border-radius: 0; margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 0;">
                <div style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1.125rem;">Good Lighting</h3>
                <p style="margin: 0; color: #4b5563; font-size: 0.9375rem;">
                    Use natural or even lighting. Face the light source directly. Avoid shadows on your face or background.
                </p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 0;">
                <div style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1.125rem;">Plain Background</h3>
                <p style="margin: 0; color: #4b5563; font-size: 0.9375rem;">
                    Use a plain, light-coloured background (white, light grey, or light blue). Avoid busy or distracting backgrounds.
                </p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 0;">
                <div style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1.125rem;">Face Forward</h3>
                <p style="margin: 0; color: #4b5563; font-size: 0.9375rem;">
                    Look directly at the camera with a neutral expression. Keep your eyes open and visible. Remove sunglasses or hats (unless for religious reasons).
                </p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 0;">
                <div style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1.125rem;">Good Quality</h3>
                <p style="margin: 0; color: #4b5563; font-size: 0.9375rem;">
                    Photo should be clear and in focus. Minimum size: 300x300 pixels. Accepted formats: JPEG or PNG. Maximum file size: 5MB.
                </p>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 0; margin-top: 1.5rem; border: 2px solid #3b82f6;">
            <h3 style="margin: 0 0 1rem 0; color: #1e40af; font-size: 1.125rem;">
                <i class="fas fa-info-circle"></i> Important Reminders
            </h3>
            <ul style="margin: 0; padding-left: 1.5rem; color: #1e40af; line-height: 1.8;">
                <li>Your photo must clearly show your face and be suitable for professional identification</li>
                <li>Photos will be reviewed by an administrator before being approved</li>
                <li>You'll be notified once your photo has been approved or if changes are needed</li>
                <li>Only approved photos will appear on your digital ID card</li>
            </ul>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <?php if (Auth::isLoggedIn()): ?>
            <a href="<?php echo url('upload-photo.php'); ?>" class="btn btn-primary">
                <i class="fas fa-camera"></i> Upload Your Photo
            </a>
        <?php else: ?>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Log In to Upload Photo
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


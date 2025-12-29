<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Digital ID Card Demo';
include INCLUDES_PATH . '/header.php';
?>

<style>
.two-column-section {
    padding: 0;
    background: transparent;
}

.two-column-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: start;
}

@media (max-width: 968px) {
    .two-column-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<div class="card" style="max-width: 600px; margin: 2rem auto;">
    <h1>Digital ID Card Example</h1>
    <p style="color: #6b7280; margin-bottom: 2rem;">
        This is an example of what a Digital ID card looks like. When you register and your organisation administrator creates your employee profile, you'll receive your own personalised digital ID card.
    </p>
    
    <div style="background: #f0f9ff; border: 2px solid #06b6d4; border-radius: 0; padding: 1rem; margin-bottom: 2rem; text-align: center;">
        <i class="fas fa-info-circle" style="color: #06b6d4; font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
        <p style="margin: 0; color: #0369a1; font-weight: 500;">This is a demonstration card with example data</p>
    </div>
</div>

<div class="two-column-section" style="max-width: 1200px; margin: 2rem auto; padding: 0 20px;">
    <div class="two-column-content">
        <!-- Features Section -->
        <div class="card" style="margin: 0;">
            <h2>Key Features of Digital ID Cards</h2>
            <ul style="line-height: 2;">
                <li><strong>Secure Verification:</strong> Time-limited QR codes that expire after 5 minutes for security</li>
                <li><strong>Multiple Methods:</strong> Visual verification, QR code scanning, and NFC/BLE support</li>
                <li><strong>Organisation Branding:</strong> Your organisation's logo appears on all ID cards</li>
                <li><strong>Photo Verification:</strong> Admin-approved photos for visual identity confirmation</li>
                <li><strong>Employee Reference:</strong> Unique reference number for each employee</li>
                <li><strong>Offline Access:</strong> View your ID card even without internet connection</li>
            </ul>
            
            <div style="margin-top: 2rem; text-align: center;">
                <?php if (!Auth::isLoggedIn()): ?>
                    <a href="<?php echo url('register.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register for Digital ID
                    </a>
                    <a href="<?php echo url('features.php'); ?>" class="btn btn-secondary" style="margin-left: 1rem;">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-id-card"></i> View Your ID Card
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ID Card -->
        <div style="position: relative;">
            <div class="id-card" id="id-card-content" style="position: relative; margin: 0;">
                <!-- Demo Badge -->
                <div style="position: absolute; top: 1rem; right: 1rem; background: #f59e0b; color: white; padding: 0.5rem 1rem; font-size: 0.75rem; font-weight: 600; border-radius: 0; z-index: 10;">
                    <i class="fas fa-eye"></i> DEMO
                </div>
                
                <div class="id-card-header">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="flex: 1;">
                            <h2 style="margin: 0;">Example Organisation</h2>
                            <p style="margin: 0;">Digital ID Card</p>
                        </div>
                    </div>
                </div>
                
                <!-- Demo Photo -->
                <img src="<?php echo url('assets/images/home/Digital ID.jpg'); ?>" alt="Example ID Card Photo" class="id-card-photo" style="object-fit: cover;">
                
                <div class="id-card-details">
                    <p><strong>Name:</strong> Sarah Doe</p>
                    <p><strong>Reference:</strong> EXMP-L0Z7H7</p>
                    <p><strong>Organisation:</strong> Example Organisation</p>
                </div>
                
                <!-- Demo QR Code -->
                <div class="id-card-qr" style="margin-top: 1.5rem; text-align: center;">
                    <p style="margin-bottom: 1rem; font-size: 0.875rem; color: #6b7280;">Scan QR code to learn more</p>
                    <?php
                    // Generate a real QR code that links to the request access page
                    require_once SRC_PATH . '/classes/QRCodeGenerator.php';
                    $demoUrl = APP_URL . url('request-access.php');
                    $encodedUrl = urlencode($demoUrl);
                    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$encodedUrl}";
                    ?>
                    <img src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="QR Code - Scan to request access" style="max-width: 200px; background: white; padding: 1rem; border-radius: 0; border: 1px solid #e5e7eb;">
                    <p style="margin-top: 0.75rem; font-size: 0.75rem; color: #9ca3af;">
                        <i class="fas fa-info-circle"></i> Scan this QR code to visit the request access page
                    </p>
                </div>
                
                <div style="margin-top: 1.5rem; text-align: center;">
                    <p style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.75rem;">Additional verification methods:</p>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                        <button class="btn btn-secondary" style="flex: 1; min-width: 120px; opacity: 0.6; cursor: not-allowed;" disabled>Activate NFC</button>
                        <button class="btn btn-secondary" style="flex: 1; min-width: 120px; opacity: 0.6; cursor: not-allowed;" disabled>Activate BLE</button>
                    </div>
                    <p style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.8;">NFC: Android Chrome/Edge only</p>
                    <p style="margin-top: 0.25rem; font-size: 0.75rem; opacity: 0.8;">BLE: Chrome/Edge (Android/Desktop)</p>
                </div>
                
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; text-align: center;">
                    <p style="font-size: 0.75rem; color: #9ca3af; margin: 0;">Card expires: <?php echo date('d/m/Y', strtotime('+1 year')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


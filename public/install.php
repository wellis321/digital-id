<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Install Digital ID App';
include dirname(__DIR__) . '/includes/header.php';

// Detect device
$isIOS = preg_match('/iPad|iPhone|iPod/', $_SERVER['HTTP_USER_AGENT'] ?? '');
$isAndroid = preg_match('/Android/', $_SERVER['HTTP_USER_AGENT'] ?? '');
$isMobile = $isIOS || $isAndroid;
?>

<div class="card">
    <h1>Install Digital ID App</h1>
    <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem;">
        Install Digital ID as a Progressive Web App (PWA) on your device for quick, app-like access to your ID card.
    </p>
    
    <?php if ($isMobile): ?>
        <div id="mobile-install-button-container" style="margin-bottom: 2rem;">
            <button id="install-button" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                <i class="fas fa-download"></i> Install App Now
            </button>
            <p style="text-align: center; margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">
                Tap the button above if it appears, or follow the instructions below for your device
            </p>
        </div>
    <?php endif; ?>
    
    <?php if ($isIOS): ?>
        <div class="card" style="background-color: #f0f9ff; border-left: 4px solid #2563eb; margin-bottom: 2rem;">
            <h2 style="margin-top: 0; color: #1e40af;">
                <i class="fas fa-mobile-alt"></i> iPhone/iPad Installation
            </h2>
            <ol style="line-height: 2; color: #1e40af;">
                <li>Open this page in <strong>Safari</strong> (not Chrome or other browsers)</li>
                <li>Tap the <i class="fas fa-share"></i> <strong>Share</strong> button at the bottom of your screen</li>
                <li>Scroll down in the share menu and tap <strong>"Add to Home Screen"</strong></li>
                <li>Tap <strong>"Add"</strong> in the top right corner</li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            <p style="color: #1e40af; margin-top: 1rem; font-weight: 600;">
                <i class="fas fa-info-circle"></i> Note: You must use Safari browser. Chrome and other browsers on iOS do not support PWA installation.
            </p>
        </div>
    <?php endif; ?>
    
    <?php if ($isAndroid): ?>
        <div class="card" style="background-color: #f0fdf4; border-left: 4px solid #10b981; margin-bottom: 2rem;">
            <h2 style="margin-top: 0; color: #047857;">
                <i class="fas fa-mobile-alt"></i> Android Installation
            </h2>
            <ol style="line-height: 2; color: #047857;">
                <li>Open this page in <strong>Chrome</strong> or <strong>Edge</strong> browser</li>
                <li>Tap the <i class="fas fa-ellipsis-vertical"></i> <strong>menu</strong> button (three dots) in the top right</li>
                <li>Select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                <li>Tap <strong>"Install"</strong> or <strong>"Add"</strong> to confirm</li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            <p style="color: #047857; margin-top: 1rem; font-weight: 600;">
                <i class="fas fa-info-circle"></i> Note: If you see an "Install App" button above, tap it for automatic installation.
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (!$isMobile): ?>
        <div class="card" style="background-color: #f9fafb; border-left: 4px solid #6b7280; margin-bottom: 2rem;">
            <h2 style="margin-top: 0;">
                <i class="fas fa-desktop"></i> Desktop Installation
            </h2>
            <p>To install Digital ID on your desktop computer:</p>
            <ol style="line-height: 2;">
                <li>Look for an <strong>install icon</strong> in your browser's address bar (usually appears as a "+" or download icon)</li>
                <li>Or use your browser's menu:
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        <li><strong>Chrome/Edge:</strong> Menu → "Install Digital ID"</li>
                        <li><strong>Firefox:</strong> Menu → "Install"</li>
                        <li><strong>Safari:</strong> File → "Add to Dock"</li>
                    </ul>
                </li>
                <li>Follow the prompts to complete installation</li>
            </ol>
        </div>
    <?php endif; ?>
    
    <div class="card" style="background-color: #fffbeb; border-left: 4px solid #f59e0b;">
        <h2 style="margin-top: 0; color: #92400e;">
            <i class="fas fa-question-circle"></i> Troubleshooting
        </h2>
        <h3 style="color: #92400e; margin-top: 1rem;">Install button doesn't appear</h3>
        <ul style="color: #92400e; line-height: 2;">
            <li>Ensure you're using a supported browser (Safari on iOS, Chrome/Edge on Android)</li>
            <li>Make sure you're accessing the site over HTTPS (required for PWA installation)</li>
            <li>Try refreshing the page</li>
            <li>Check that your browser is up to date</li>
        </ul>
        
        <h3 style="color: #92400e; margin-top: 1.5rem;">Already installed?</h3>
        <p style="color: #92400e;">
            If you've already installed the app, you'll see it on your home screen. Tap the icon to open it. 
            The app works offline and provides quick access to your ID card.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

<script>
// Handle install button
document.addEventListener('DOMContentLoaded', function() {
    const installButton = document.getElementById('install-button');
    if (installButton && window.pwaInstallHandler) {
        installButton.addEventListener('click', function() {
            window.pwaInstallHandler.triggerInstall();
        });
        
        // Show button if deferred prompt is available
        if (window.pwaInstallHandler.deferredPrompt) {
            installButton.style.display = 'block';
        }
    }
    
    // Update button visibility when deferred prompt becomes available
    if (window.pwaInstallHandler) {
        const originalInit = window.pwaInstallHandler.init;
        window.pwaInstallHandler.init = function() {
            originalInit.call(this);
            const installButton = document.getElementById('install-button');
            if (installButton && this.deferredPrompt) {
                installButton.style.display = 'block';
            }
        };
    }
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>


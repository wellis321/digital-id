<?php if (empty($marketing)): ?>
    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <a href="<?php echo url('index.php'); ?>" class="footer-logo">
                    <img src="<?php echo url('assets/images/300-high-digitalID-logo.png'); ?>" alt="" class="footer-logo-image">
                    <span class="footer-logo-text"><?php echo APP_NAME; ?></span>
                </a>
                <p style="color: #c9d3d3; line-height: 1.6; margin-top: 1rem;">
                    Secure, verifiable digital identification for social care providers. 
                    Replace paper-based ID cards with modern, secure technology.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h2 class="footer-heading">Product</h2>
                <ul>
                    <li><a href="<?php echo url('features.php'); ?>">Features</a></li>
                    <li><a href="<?php echo url('security.php'); ?>#how-it-works">How It Works</a></li>
                    <li><a href="<?php echo url('security.php'); ?>">Security</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h2 class="footer-heading">Resources</h2>
                <ul>
                    <li><a href="<?php echo url('docs-getting-started.php'); ?>">Documentation</a></li>
                    <li><a href="<?php echo url('docs-mcp-integration.php'); ?>">AI Integration (MCP)</a></li>
                    <li><a href="https://findcarescotland.com/software/care-core" target="_blank" rel="noopener noreferrer">Care Core</a></li>
                    <li><a href="<?php echo url('contact.php'); ?>">Support</a></li>
                    <li><a href="<?php echo url('case-studies.php'); ?>">Case Studies</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h2 class="footer-heading">Company</h2>
                <ul>
                    <li><a href="<?php echo url('contact.php'); ?>">Contact</a></li>
                    <li><a href="<?php echo url('request-access.php'); ?>">Request Access</a></li>
                    <li><a href="<?php echo url('privacy-policy.php'); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo url('terms-of-service.php'); ?>">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom-section">
            <div class="footer-section footer-install-app">
                <h2 class="footer-heading">Install App</h2>
                <p style="color: #c9d3d3; font-size: 0.875rem; margin-bottom: 1rem;">
                    Install Digital ID as an app on your phone for quick access
                </p>
                <button id="footer-install-button" class="btn btn-primary" style="display: none; width: 100%; margin-bottom: 0.5rem; gap: 0.5rem;">
                    <i class="fas fa-download"></i> <span>Install App</span>
                </button>
                <a href="<?php echo url('install.php'); ?>" class="btn btn-secondary" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-mobile-alt"></i> Installation Guide
                </a>
            </div>
            
            <div class="footer-section footer-care-core">
                <h2 class="footer-heading">Care Core</h2>
                <p style="color: #c9d3d3; font-size: 0.875rem; line-height: 1.6; margin-bottom: 1rem;">
                    One record of your staff, teams and the people you support. Joiners, movers and leavers change everything at once, and Digital ID follows.
                </p>
                <a href="https://findcarescotland.com/software/care-core" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-external-link-alt"></i> <span>Learn More</span>
                </a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
        
        <script>
        // Handle footer install button
        document.addEventListener('DOMContentLoaded', function() {
            const footerInstallBtn = document.getElementById('footer-install-button');
            if (footerInstallBtn && window.pwaInstallHandler) {
                footerInstallBtn.addEventListener('click', function() {
                    window.pwaInstallHandler.triggerInstall();
                });
                
                // Check for iOS non-Safari browsers - hide button
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
                const isIOSNonSafari = isIOS && !isSafari;
                
                if (isIOSNonSafari) {
                    footerInstallBtn.style.display = 'none';
                    return;
                }
                
                // Show install button for Firefox on supported platforms (if not already shown)
                const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
                if (isFirefox) {
                    const isWindows = /Windows/.test(navigator.userAgent);
                    const isAndroid = /Android/.test(navigator.userAgent);
                    const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
                    const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
                    
                    // Show for Firefox on Windows (143+) and Android ONLY
                    if ((isWindows || isAndroid) && !isMacOS && !isLinux) {
                        footerInstallBtn.style.display = 'inline-flex';
                        footerInstallBtn.innerHTML = '<i class="fas fa-download"></i> <span>Install App (Use Browser Menu)</span>';
                    } else {
                        // Explicitly hide button on unsupported platforms
                        footerInstallBtn.style.display = 'none';
                    }
                }
            }
        });
        </script>
        <?php echo demo_other_products_html('digital-id'); ?>
</footer>
<?php else: include __DIR__ . '/marketing-footer.php'; endif; ?>
    
</body>
</html>


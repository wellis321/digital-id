    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3><?php echo APP_NAME; ?></h3>
                <p style="color: #9ca3af; line-height: 1.6;">
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
                <h3>Product</h3>
                <ul>
                    <li><a href="<?php echo url('features.php'); ?>">Features</a></li>
                    <li><a href="<?php echo url('security.php'); ?>#how-it-works">How It Works</a></li>
                    <li><a href="<?php echo url('security.php'); ?>">Security</a></li>
                    <li><a href="<?php echo url('docs.php'); ?>">Documentation</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Resources</h3>
                <ul>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">API Reference</a></li>
                    <li><a href="#">Support</a></li>
                    <li><a href="#">Case Studies</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Company</h3>
                <ul>
                    <li><a href="mailto:<?php echo CONTACT_EMAIL; ?>">Contact</a></li>
                    <li><a href="<?php echo url('request-access.php'); ?>">Request Access</a></li>
                    <li><a href="<?php echo url('privacy-policy.php'); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo url('terms-of-service.php'); ?>">Terms of Service</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Install App</h3>
                <p style="color: #9ca3af; font-size: 0.875rem; margin-bottom: 1rem;">
                    Install Digital ID as an app on your phone for quick access
                </p>
                <button id="footer-install-button" class="btn btn-primary" style="display: none; width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-download"></i> Install App
                </button>
                <a href="<?php echo url('install.php'); ?>" class="btn btn-secondary" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-mobile-alt"></i> Installation Guide
                </a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
        
        <script>
        // Handle footer install button
        document.addEventListener('DOMContentLoaded', function() {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'footer.php:DOMContentLoaded',message:'Footer script DOMContentLoaded',data:{pwaHandlerExists:!!window.pwaInstallHandler},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
            // #endregion
            
            const footerInstallBtn = document.getElementById('footer-install-button');
            if (footerInstallBtn && window.pwaInstallHandler) {
                footerInstallBtn.addEventListener('click', function() {
                    window.pwaInstallHandler.triggerInstall();
                });
                
                // Show install button for Firefox on supported platforms (if not already shown)
                const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
                if (isFirefox) {
                    const isWindows = /Windows/.test(navigator.userAgent);
                    const isAndroid = /Android/.test(navigator.userAgent);
                    const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
                    const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
                    
                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'footer.php:firefoxCheck',message:'Checking Firefox platform support',data:{isWindows:isWindows,isAndroid:isAndroid,isMacOS:isMacOS,isLinux:isLinux,currentDisplay:footerInstallBtn.style.display},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
                    // #endregion
                    
                    // Show for Firefox on Windows (143+) and Android
                    if ((isWindows || isAndroid) && !isMacOS && !isLinux) {
                        footerInstallBtn.style.display = 'inline-flex';
                        footerInstallBtn.innerHTML = '<i class="fas fa-download"></i> Install App (Use Browser Menu)';
                        // #region agent log
                        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'footer.php:firefoxShowButton',message:'Showing Firefox install button in footer',data:{display:footerInstallBtn.style.display},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
                        // #endregion
                    }
                }
            } else {
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'footer.php:buttonNotFound',message:'Footer install button or handler not found',data:{buttonFound:!!footerInstallBtn,handlerExists:!!window.pwaInstallHandler},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
                // #endregion
            }
        });
        </script>
    </footer>
</body>
</html>


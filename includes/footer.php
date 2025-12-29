    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <a href="<?php echo url('index.php'); ?>" class="footer-logo">
                    <img src="<?php echo url('assets/images/300-high-digitalID-logo.png'); ?>" alt="<?php echo APP_NAME; ?>" class="footer-logo-image">
                    <span class="footer-logo-text"><?php echo APP_NAME; ?></span>
                </a>
                <p style="color: #9ca3af; line-height: 1.6; margin-top: 1rem;">
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
                    <li><a href="<?php echo url('docs.php'); ?>">Documentation</a></li>
                    <li><a href="<?php echo url('docs.php?section=mcp-integration'); ?>">AI Integration (MCP)</a></li>
                    <li><a href="<?php echo url('contact.php'); ?>">Support</a></li>
                    <li><a href="<?php echo url('case-studies.php'); ?>">Case Studies</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Company</h3>
                <ul>
                    <li><a href="<?php echo url('contact.php'); ?>">Contact</a></li>
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
                <button id="footer-install-button" class="btn btn-primary" style="display: none; width: 100%; margin-bottom: 0.5rem; gap: 0.5rem;">
                    <i class="fas fa-download"></i> <span>Install App</span>
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
    </footer>
    
    <!-- Cookie Banner -->
    <div id="cookieBanner" class="cookie-banner">
        <div class="cookie-banner-content">
            <div class="cookie-banner-text">
                <p>
                    We use cookies to enhance your experience, maintain your login session, and improve our service. 
                    By clicking "Accept", you consent to our use of cookies. 
                    <a href="<?php echo url('privacy-policy.php'); ?>#cookies">Learn more in our Privacy Policy</a>.
                </p>
            </div>
            <div class="cookie-banner-buttons">
                <button class="cookie-banner-btn cookie-banner-btn-reject" onclick="rejectCookies()">
                    Reject
                </button>
                <button class="cookie-banner-btn cookie-banner-btn-accept" onclick="acceptCookies()">
                    Accept
                </button>
            </div>
        </div>
    </div>
    
    <script>
    // Cookie Banner Functionality
    (function() {
        const COOKIE_CONSENT_KEY = 'digital_id_cookie_consent';
        const COOKIE_CONSENT_EXPIRY_DAYS = 365;
        
        function getCookieConsent() {
            return localStorage.getItem(COOKIE_CONSENT_KEY);
        }
        
        function setCookieConsent(consent) {
            const expiryDate = new Date();
            expiryDate.setDate(expiryDate.getDate() + COOKIE_CONSENT_EXPIRY_DAYS);
            localStorage.setItem(COOKIE_CONSENT_KEY, consent);
            localStorage.setItem(COOKIE_CONSENT_KEY + '_date', expiryDate.toISOString());
        }
        
        function showCookieBanner() {
            const banner = document.getElementById('cookieBanner');
            if (banner) {
                banner.classList.add('show');
            }
        }
        
        function hideCookieBanner() {
            const banner = document.getElementById('cookieBanner');
            if (banner) {
                banner.classList.remove('show');
            }
        }
        
        window.acceptCookies = function() {
            setCookieConsent('accepted');
            hideCookieBanner();
        };
        
        window.rejectCookies = function() {
            setCookieConsent('rejected');
            hideCookieBanner();
        };
        
        // Check if consent has been given
        document.addEventListener('DOMContentLoaded', function() {
            const consent = getCookieConsent();
            if (!consent) {
                // Show banner after a short delay for better UX
                setTimeout(showCookieBanner, 500);
            }
        });
    })();
    </script>
</body>
</html>


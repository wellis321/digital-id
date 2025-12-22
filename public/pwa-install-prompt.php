<?php
/**
 * PWA Install Prompt Component
 * Shows instructions for installing the app to home screen
 */
?>
<div id="pwa-install-prompt" class="pwa-install-prompt" style="display: none;">
    <div class="pwa-install-content">
        <button id="pwa-install-close" class="pwa-install-close" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <div class="pwa-install-icon">
            <i class="fas fa-mobile-alt fa-3x"></i>
        </div>
        <h3>Add to Home Screen</h3>
        <p>Install Digital ID for quick access to your ID card</p>
        <div id="pwa-install-instructions" class="pwa-install-instructions">
            <!-- Instructions will be populated by JavaScript based on device -->
        </div>
        <button id="pwa-install-button" class="btn btn-primary" style="margin-top: 1rem; width: 100%;">
            <i class="fas fa-download"></i> <span>Install App</span>
        </button>
    </div>
</div>

<style>
.pwa-install-prompt {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
    z-index: 10000;
    padding: 1.5rem;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    max-width: 500px;
    margin: 0 auto;
}

.pwa-install-content {
    position: relative;
    text-align: center;
}

.pwa-install-close {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0;
    width: 32px;
    height: 32px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.pwa-install-close:hover {
    background: #e5e7eb;
}

.pwa-install-icon {
    color: #2563eb;
    margin-bottom: 1rem;
}

.pwa-install-prompt h3 {
    margin: 0 0 0.5rem 0;
    color: #111827;
    font-size: 1.25rem;
}

.pwa-install-prompt p {
    margin: 0 0 1rem 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.pwa-install-instructions {
    text-align: left;
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0;
    margin: 1rem 0;
    font-size: 0.875rem;
    color: #4b5563;
}

.pwa-install-instructions ol {
    margin: 0;
    padding-left: 1.5rem;
}

.pwa-install-instructions li {
    margin: 0.5rem 0;
    line-height: 1.5;
}
</style>

<script>
(function() {
// Global PWA install handler - accessible from anywhere on the page
window.pwaInstallHandler = {
    deferredPrompt: null,
    isInstalled: false,
    
    init: function() {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:init',message:'PWA handler init started',data:{userAgent:navigator.userAgent,standalone:window.matchMedia('(display-mode: standalone)').matches,navigatorStandalone:!!window.navigator.standalone},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        
        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            this.isInstalled = true;
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:alreadyInstalled',message:'PWA already installed, returning early',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
            return; // Already installed
        }

        // Detect browser and platform
        const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent) && !/Edg|OPR/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);
        const isBrave = navigator.brave && typeof navigator.brave.isBrave === 'function';
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isWindows = /Windows/.test(navigator.userAgent);
        const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
        const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
        // Detect Safari on iOS (Safari is the only browser that supports PWA on iOS)
        const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
        const isIOSNonSafari = isIOS && !isSafari;
        
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:browserDetection',message:'Browser and platform detection',data:{isFirefox:isFirefox,isChrome:isChrome,isEdge:isEdge,isBrave:isBrave,isIOS:isIOS,isAndroid:isAndroid,isWindows:isWindows,isMacOS:isMacOS,isLinux:isLinux},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion

        // Store deferred prompt globally when available (Chrome/Edge/Brave)
        window.addEventListener('beforeinstallprompt', (e) => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:beforeinstallprompt',message:'beforeinstallprompt event fired',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            e.preventDefault();
            this.deferredPrompt = e;
            // Show install button in prompt if it exists
            const installButton = document.getElementById('pwa-install-button');
            if (installButton) {
                installButton.style.display = 'block';
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:showPromptButton',message:'Showing prompt install button',data:{buttonFound:!!installButton},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
                // #endregion
            }
            // Show install button in footer if it exists
            this.showFooterInstallButton('Install App');
        });

        // Firefox doesn't fire beforeinstallprompt, but we can still show install options
        // Firefox supports PWA installation via menu on Windows (143+) and Android
        if (isFirefox) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:firefoxDetected',message:'Firefox detected, checking platform support',data:{isWindows:isWindows,isAndroid:isAndroid,isMacOS:isMacOS,isLinux:isLinux},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            
            // Show install button for Firefox on Windows (143+) and Android
            if ((isWindows || isAndroid) && !isMacOS && !isLinux) {
                const installButton = document.getElementById('pwa-install-button');
                if (installButton) {
                    installButton.style.display = 'block';
                    installButton.textContent = 'Install App (Use Browser Menu)';
                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:firefoxShowButton',message:'Showing Firefox install button',data:{buttonFound:!!installButton},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
                    // #endregion
                }
                this.showFooterInstallButton('Install App (Use Browser Menu)');
            } else {
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:firefoxNotSupported',message:'Firefox on unsupported platform',data:{platform:isMacOS?'macOS':isLinux?'Linux':'Other'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
                // #endregion
            }
        }
        
        // iOS with non-Safari browsers - these browsers don't support PWA installation on iOS
        // Only Safari supports PWA installation on iOS
        if (isIOSNonSafari) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:iosNonSafari',message:'iOS with non-Safari browser detected - PWA not supported',data:{userAgent:navigator.userAgent},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            // Hide install buttons - user needs Safari
            const installButton = document.getElementById('pwa-install-button');
            if (installButton) {
                installButton.style.display = 'none';
            }
            const footerInstallBtn = document.getElementById('footer-install-button');
            if (footerInstallBtn) {
                footerInstallBtn.style.display = 'none';
            }
        }
        
        // Don't automatically show footer button - it will be shown by:
        // 1. beforeinstallprompt event (Chrome/Edge/Brave)
        // 2. Firefox detection above (only on Windows/Android)

        // Handle prompt-specific functionality
        const prompt = document.getElementById('pwa-install-prompt');
        if (!prompt) return;

        const closeButton = document.getElementById('pwa-install-close');
        const installButton = document.getElementById('pwa-install-button');
        const instructions = document.getElementById('pwa-install-instructions');

        // Reuse browser/platform detection variables already declared above
        // (isIOS, isAndroid, isFirefox, isWindows are already declared in init function scope)
        // Re-detect Safari for prompt instructions
        const isSafariPrompt = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
        const isIOSNonSafariPrompt = isIOS && !isSafariPrompt;

        if (isIOSNonSafariPrompt) {
            // iOS non-Safari - hide install button and show Safari message
            if (installButton) {
                installButton.style.display = 'none';
            }
            instructions.innerHTML = `
                <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin: 1rem 0; border-radius: 0;">
                    <p style="margin: 0; color: #856404; font-weight: 600;">
                        <i class="fas fa-exclamation-triangle"></i> Safari Required
                    </p>
                    <p style="margin: 0.5rem 0 0 0; color: #856404; font-size: 0.875rem;">
                        To install this app on iPhone/iPad, you must use <strong>Safari</strong> browser. Chrome and other browsers on iOS do not support app installation.
                    </p>
                    <p style="margin: 0.5rem 0 0 0; color: #856404; font-size: 0.875rem;">
                        <a href="<?php echo url('install.php'); ?>" style="color: #856404; text-decoration: underline; font-weight: 600;">View Installation Guide</a>
                    </p>
                </div>
            `;
        } else if (isIOS) {
            // iOS Safari - show Safari-specific instructions
            instructions.innerHTML = `
                <ol>
                    <li>Tap the <i class="fas fa-share"></i> Share button at the bottom of Safari</li>
                    <li>Scroll down and tap "Add to Home Screen"</li>
                    <li>Tap "Add" to confirm</li>
                </ol>
            `;
            if (installButton) installButton.style.display = 'none';
        } else if (isAndroid) {
            if (isFirefox) {
                instructions.innerHTML = `
                    <ol>
                        <li>Tap the menu <i class="fas fa-ellipsis-vertical"></i> button (three horizontal lines) in the top right</li>
                        <li>Select <strong>"Install"</strong> or <strong>"Add to Home Screen"</strong></li>
                        <li>Confirm the installation</li>
                    </ol>
                    <p style="margin-top: 1rem; color: #6b7280; font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> Firefox supports PWA installation on Android. Look for the "Install" option in the browser menu.
                    </p>
                `;
            } else {
                instructions.innerHTML = `
                    <ol>
                        <li>Tap the menu <i class="fas fa-ellipsis-vertical"></i> button in your browser</li>
                        <li>Select "Add to Home screen" or "Install app"</li>
                        <li>Confirm the installation</li>
                    </ol>
                `;
            }
        } else {
            // Desktop/other
            if (isFirefox && isWindows) {
                instructions.innerHTML = `
                    <p>To install this app in Firefox:</p>
                    <ol>
                        <li>Click the <strong>menu button</strong> (three horizontal lines) in the top right</li>
                        <li>Select <strong>"Install"</strong> or look for the install icon in the address bar</li>
                        <li>Confirm the installation</li>
                    </ol>
                    <p style="margin-top: 1rem; color: #6b7280; font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> Requires Firefox 143.0 or later on Windows. macOS and Linux users should use Chrome or Edge for PWA installation.
                    </p>
                `;
            } else if (isFirefox) {
                instructions.innerHTML = `
                    <p>Firefox PWA installation:</p>
                    <ol>
                        <li><strong>Windows:</strong> Use Firefox 143.0+ and look for "Install" in the menu</li>
                        <li><strong>macOS/Linux:</strong> PWA installation is not natively supported. Please use Chrome or Edge browser instead.</li>
                    </ol>
                    <p style="margin-top: 1rem;">
                        <a href="<?php echo url('install.php'); ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-info-circle"></i> View Installation Guide
                        </a>
                    </p>
                `;
            } else {
                instructions.innerHTML = `
                    <p>To install this app:</p>
                    <ol>
                        <li>Look for an install icon in your browser's address bar</li>
                        <li>Or use your browser's menu to find "Install" or "Add to Home Screen"</li>
                    </ol>
                `;
            }
        }

        // Show prompt after a delay (only on mobile, and only if not dismissed in this session)
        if ((isIOS || isAndroid) && !sessionStorage.getItem('pwa-prompt-dismissed-session')) {
            setTimeout(() => {
                prompt.style.display = 'block';
            }, 3000); // Show after 3 seconds
        }

        // Handle install button click in prompt
        if (installButton) {
            installButton.addEventListener('click', () => {
                this.triggerInstall();
            });
        }

        // Close button - only dismisses for this session, not permanently
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                prompt.style.display = 'none';
                sessionStorage.setItem('pwa-prompt-dismissed-session', 'true');
            });
        }
    },
    
    triggerInstall: function() {
        const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
        const isWindows = /Windows/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
        const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
        const isIOSNonSafari = isIOS && !isSafari;
        
        // iOS non-Safari browsers - redirect to install guide with Safari instructions
        if (isIOSNonSafari) {
            const installUrl = window.location.origin + '/install.php';
            window.location.href = installUrl;
            return;
        }
        
        if (this.deferredPrompt) {
            // Chrome/Edge/Brave - use native prompt
            this.deferredPrompt.prompt();
            this.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    const prompt = document.getElementById('pwa-install-prompt');
                    if (prompt) {
                        prompt.style.display = 'none';
                    }
                }
                this.deferredPrompt = null;
            });
        } else if (isFirefox) {
            // Firefox - check platform support before showing instructions
            if ((isWindows || isAndroid) && !isMacOS && !isLinux) {
                // Supported platform - show instructions
                this.showFirefoxInstructions();
            } else {
                // Unsupported platform - redirect to install guide
                const installUrl = window.location.origin + '/install.php';
                window.location.href = installUrl;
            }
        } else {
            // Fallback: show instructions (will be Safari-specific for iOS)
            this.showInstallInstructions();
        }
    },
    
    showFirefoxInstructions: function() {
        const isWindows = /Windows/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
        const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
        
        let message = '';
        if (isAndroid) {
            message = 'Firefox on Android:\n\n1. Tap the menu button (three horizontal lines) in the top right\n2. Select "Install" or "Add to Home Screen"\n3. Confirm the installation\n\nThe app will appear on your home screen.';
        } else if (isWindows) {
            message = 'Firefox on Windows:\n\n1. Click the menu button (three horizontal lines) in the top right\n2. Select "Install" or look for the install icon in the address bar\n3. Confirm the installation\n\nNote: Requires Firefox 143.0 or later.';
        } else if (isMacOS || isLinux) {
            message = 'Firefox on macOS/Linux:\n\nPWA installation is not natively supported in Firefox on macOS/Linux.\n\nPlease use Chrome or Edge browser for PWA installation, or visit the Installation Guide page for more options.';
        } else {
            message = 'Firefox Installation:\n\nUse the browser menu to find "Install" option, or visit the Installation Guide page for detailed instructions.';
        }
        alert(message);
    },
    
    showFooterInstallButton: function(text) {
        const footerInstallBtn = document.getElementById('footer-install-button');
        if (!footerInstallBtn) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:footerButtonNotFound',message:'Footer install button not found',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        // Check platform before showing - only show on supported platforms
        const isFirefox = /Firefox/.test(navigator.userAgent) && !/Seamonkey/.test(navigator.userAgent);
        const isWindows = /Windows/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isMacOS = /Macintosh|Mac OS X/.test(navigator.userAgent);
        const isLinux = /Linux/.test(navigator.userAgent) && !/Android/.test(navigator.userAgent);
        
        // For Firefox, only show on Windows/Android (not macOS/Linux)
        if (isFirefox && (isMacOS || isLinux)) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:firefoxUnsupportedPlatform',message:'Not showing footer button - Firefox on unsupported platform',data:{platform:isMacOS?'macOS':'Linux'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
            // #endregion
            return; // Don't show button on Firefox macOS/Linux
        }
        
        if (text) {
            footerInstallBtn.innerHTML = '<i class="fas fa-download"></i> <span>' + text + '</span>';
        }
        footerInstallBtn.style.display = 'inline-flex';
        footerInstallBtn.style.gap = '0.5rem';
        footerInstallBtn.style.alignItems = 'center';
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/1fc7ae7c-df4c-4686-a382-3cb17e5a246c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'pwa-install-prompt.php:showFooterButton',message:'Showing footer install button',data:{buttonFound:!!footerInstallBtn,text:text||'default',display:footerInstallBtn.style.display,platform:isFirefox?(isWindows?'Windows':isAndroid?'Android':'Other'):'Chrome/Edge'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
        // #endregion
    },
    
    showInstallInstructions: function() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome|CriOS|FxiOS|OPiOS/.test(navigator.userAgent);
        const isIOSNonSafari = isIOS && !isSafari;
        
        let message = '';
        if (isIOSNonSafari) {
            // iOS non-Safari - redirect to install guide
            const installUrl = window.location.origin + '/install.php';
            window.location.href = installUrl;
            return;
        } else if (isIOS) {
            // iOS Safari - show Safari-specific instructions
            message = 'To install on iPhone/iPad:\n\n1. Tap the Share button (square with arrow) at the bottom of Safari\n2. Scroll down and tap "Add to Home Screen"\n3. Tap "Add" to confirm\n\nThe app will appear on your home screen.\n\nNote: You must use Safari browser. Chrome and other browsers on iOS do not support app installation.';
        } else if (isAndroid) {
            message = 'To install:\n1. Tap the menu (three dots)\n2. Select "Add to Home screen" or "Install app"\n3. Confirm';
        } else {
            message = 'Look for an install icon in your browser\'s address bar, or use the browser menu to find "Install" or "Add to Home Screen"';
        }
        alert(message);
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pwaInstallHandler.init();
    });
} else {
    window.pwaInstallHandler.init();
}
})(); // End IIFE
</script>


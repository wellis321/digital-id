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
            <i class="fas fa-download"></i> Install App
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
// Global PWA install handler - accessible from anywhere on the page
window.pwaInstallHandler = {
    deferredPrompt: null,
    isInstalled: false,
    
    init: function() {
        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            this.isInstalled = true;
            return; // Already installed
        }

        // Store deferred prompt globally when available
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            // Show install button in prompt if it exists
            const installButton = document.getElementById('pwa-install-button');
            if (installButton) {
                installButton.style.display = 'block';
            }
            // Show install button in footer if it exists
            const footerInstallBtn = document.getElementById('footer-install-button');
            if (footerInstallBtn) {
                footerInstallBtn.style.display = 'inline-flex';
            }
        });

        // Handle prompt-specific functionality
        const prompt = document.getElementById('pwa-install-prompt');
        if (!prompt) return;

        const closeButton = document.getElementById('pwa-install-close');
        const installButton = document.getElementById('pwa-install-button');
        const instructions = document.getElementById('pwa-install-instructions');

        // Detect device and show appropriate instructions
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);

        if (isIOS) {
            instructions.innerHTML = `
                <ol>
                    <li>Tap the <i class="fas fa-share"></i> Share button at the bottom of your screen</li>
                    <li>Scroll down and tap "Add to Home Screen"</li>
                    <li>Tap "Add" to confirm</li>
                </ol>
            `;
            installButton.style.display = 'none';
        } else if (isAndroid) {
            instructions.innerHTML = `
                <ol>
                    <li>Tap the menu <i class="fas fa-ellipsis-vertical"></i> button in your browser</li>
                    <li>Select "Add to Home screen" or "Install app"</li>
                    <li>Confirm the installation</li>
                </ol>
            `;
        } else {
            // Desktop/other - show generic instructions
            instructions.innerHTML = `
                <p>To install this app:</p>
                <ol>
                    <li>Look for an install icon in your browser's address bar</li>
                    <li>Or use your browser's menu to find "Install" or "Add to Home Screen"</li>
                </ol>
            `;
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
        if (this.deferredPrompt) {
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
        } else {
            // Fallback: show instructions
            this.showInstallInstructions();
        }
    },
    
    showInstallInstructions: function() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        
        let message = '';
        if (isIOS) {
            message = 'To install:\n1. Tap the Share button\n2. Select "Add to Home Screen"\n3. Tap "Add"';
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
</script>


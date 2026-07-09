<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Install as App - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>Install Digital ID as an App</h1>
            <p>Digital ID can be installed as a Progressive Web App (PWA) on your phone, giving you quick access to your ID card directly from your home screen.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> What is a PWA?</h4>
                <p>A Progressive Web App (PWA) is a website that works like a native app. You can install it on your phone without going through an app store, and it provides quick access and offline capabilities.</p>
            </div>
            
            <h2>Benefits of Installing as an App</h2>
            <ul>
                <li><strong>Quick Access:</strong> Tap the app icon on your home screen to instantly open your ID card</li>
                <li><strong>App-like Experience:</strong> Works like a native app with full-screen mode</li>
                <li><strong>Offline Support:</strong> Your ID card can be cached for offline viewing</li>
                <li><strong>No App Store:</strong> Install directly from your browser - no need for Apple App Store or Google Play Store</li>
                <li><strong>Always Up to Date:</strong> Automatically updates when you're online</li>
            </ul>
            
            <h2>How to Install on iPhone/iPad</h2>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Safari</strong> (other browsers don't support PWA installation on iOS)</li>
                <li>Tap the <strong>Share</strong> button <i class="fas fa-share"></i> at the bottom of your screen</li>
                <li>Scroll down in the share menu and tap <strong>"Add to Home Screen"</strong></li>
                <li>Customise the name if desired (default is "Digital ID")</li>
                <li>Tap <strong>"Add"</strong> in the top right corner</li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> iOS Note</h4>
                <p>On iPhone and iPad, PWA installation is only available through Safari. Chrome and other browsers on iOS don't support PWA installation.</p>
            </div>
            
            <h2>How to Install on Android</h2>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Chrome</strong> or <strong>Edge</strong> browser</li>
                <li>Look for an <strong>"Install"</strong> or <strong>"Add to Home screen"</strong> banner at the top of the page</li>
                <li>If you don't see the banner, tap the <strong>menu button</strong> <i class="fas fa-ellipsis-vertical"></i> (three dots) in the top right</li>
                <li>Select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                <li>Review the installation prompt and tap <strong>"Install"</strong> or <strong>"Add"</strong></li>
                <li>The Digital ID icon will appear on your home screen</li>
            </ol>
            
            <h2>How to Install on Desktop</h2>
            <p>On desktop computers (Windows, Mac, Linux), you can install Digital ID as an app:</p>
            <ol class="step-list">
                <li>Open the Digital ID website in <strong>Chrome</strong>, <strong>Edge</strong>, or <strong>Opera</strong></li>
                <li>Look for an <strong>"Install"</strong> icon in the browser's address bar (usually appears as a plus or download icon)</li>
                <li>Click the install icon and confirm the installation</li>
                <li>The app will open in its own window, separate from your browser</li>
            </ol>
            
            <h2>Using the Installed App</h2>
            <p>Once installed, using Digital ID is simple:</p>
            <ol class="step-list">
                <li>Tap the Digital ID icon on your home screen</li>
                <li>The app opens directly to the login page (or your ID card if already logged in)</li>
                <li>Log in with your credentials</li>
                <li>Access your ID card instantly - no need to navigate through menus</li>
            </ol>
            
            <h3>Quick Access Shortcut</h3>
            <p>On supported devices, you can use a shortcut to go directly to your ID card:</p>
            <ol class="step-list">
                <li>Long-press the Digital ID icon on your home screen</li>
                <li>Select <strong>"My ID Card"</strong> from the shortcut menu</li>
                <li>The app opens directly to your ID card page</li>
            </ol>
            
            <h2>Offline Access</h2>
            <p>The installed app can cache your ID card for offline viewing:</p>
            <ul>
                <li>Your ID card data is cached when you view it online</li>
                <li>You can view your cached ID card even without an internet connection</li>
                <li><strong>Important:</strong> QR code verification requires the person scanning the QR code to have internet access. When someone scans your QR code, their device needs to connect to the verification server to validate the token.</li>
                <li>QR code tokens expire after 5 minutes. If your token expires while offline, you'll need internet access to refresh your ID card page and get a new QR code.</li>
                <li>The app will automatically update when you're back online</li>
            </ul>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Who Needs Internet for QR Verification?</h4>
                <p><strong>The person scanning/verifying your QR code needs internet access</strong> to send the verification request to the server. You (the ID card owner) don't need internet at the moment of verification - you just need to have your QR code displayed. However, if your QR code token expires (after 5 minutes), you'll need internet to refresh your ID card page and get a new QR code.</p>
            </div>
            
            <h2>Updating the App</h2>
            <p>The PWA automatically updates when you're online:</p>
            <ul>
                <li>No need to manually update - changes are downloaded automatically</li>
                <li>You'll always have the latest version when connected to the internet</li>
                <li>If you notice issues, try closing and reopening the app</li>
            </ul>
            
            <h2>Uninstalling the App</h2>
            <p>If you no longer want the app installed:</p>
            
            <h3>On iPhone/iPad:</h3>
            <ol class="step-list">
                <li>Long-press the app icon on your home screen</li>
                <li>Tap <strong>"Remove App"</strong></li>
                <li>Confirm removal</li>
            </ol>
            
            <h3>On Android:</h3>
            <ol class="step-list">
                <li>Long-press the app icon</li>
                <li>Drag it to <strong>"Uninstall"</strong> or tap the info icon and select <strong>"Uninstall"</strong></li>
                <li>Confirm removal</li>
            </ol>
            
            <h3>On Desktop:</h3>
            <ol class="step-list">
                <li>Open the installed app</li>
                <li>Use the browser menu to find <strong>"Uninstall"</strong> or <strong>"Remove"</strong></li>
                <li>Or uninstall through your system's installed apps list</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Still Works in Browser</h4>
                <p>Uninstalling the app doesn't affect your ability to use Digital ID in your web browser. You can always access it at the website URL.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            
            <h3>Install Prompt Not Appearing</h3>
            <ul>
                <li><strong>iOS:</strong> Make sure you're using Safari, not Chrome or other browsers</li>
                <li><strong>Android:</strong> Ensure you're using Chrome or Edge browser</li>
                <li>Try visiting the site again - the prompt may appear after a few seconds</li>
                <li>Check that you haven't previously dismissed the prompt</li>
            </ul>
            
            <h3>App Not Working Offline</h3>
            <ul>
                <li>Make sure you've viewed your ID card at least once while online</li>
                <li>The app needs to cache the data before offline access works</li>
                <li>Try refreshing the page while online, then test offline</li>
            </ul>
            
            <h3>App Looks Different</h3>
            <ul>
                <li>When installed as a PWA, the app runs in "standalone" mode</li>
                <li>Navigation menus may be hidden for a cleaner, app-like experience</li>
                <li>This is normal and designed for quick access to your ID card</li>
            </ul>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

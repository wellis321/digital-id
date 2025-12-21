# Digital ID - Quick Access Guide

## How Users Access Their Digital ID Card

### Current Method (Web Browser)
Users can access their ID card via:
1. **URL**: Navigate to `{APP_URL}/id-card.php` in any web browser
2. **Navigation Menu**: Click "My ID Card" in the main navigation (requires login)
3. **Direct Link**: Bookmark the ID card page for quick access

### New Method: Progressive Web App (PWA)

The system now supports **Progressive Web App (PWA)** functionality, allowing users to install the Digital ID system as an app on their phone for instant access.

#### Benefits of PWA Installation:
- **Quick Access**: Tap the app icon on home screen to instantly open ID card
- **App-like Experience**: Works like a native app without app store installation
- **Offline Support**: ID card can be cached for offline viewing
- **No App Store**: No need to publish to Apple App Store or Google Play Store

#### How Users Install the PWA:

**On iPhone/iPad (Safari):**
1. Open the Digital ID website in Safari
2. Tap the Share button (square with arrow) at the bottom
3. Scroll down and tap "Add to Home Screen"
4. Tap "Add" to confirm
5. The Digital ID icon will appear on the home screen

**On Android (Chrome/Edge):**
1. Open the Digital ID website in Chrome or Edge
2. Tap the menu button (three dots) in the browser
3. Select "Add to Home screen" or "Install app"
4. Confirm the installation
5. The Digital ID icon will appear on the home screen

**On Desktop:**
- Look for an install icon in the browser's address bar
- Or use the browser menu to find "Install" option

#### After Installation:
- Users tap the Digital ID icon on their home screen
- The app opens directly to the login page (or ID card if already logged in)
- Navigation is simplified for quick access
- Works offline with cached data

### Quick Access Shortcut

When installed as a PWA, users can also use the "My ID Card" shortcut:
- Long-press the app icon on home screen
- Select "My ID Card" shortcut
- Opens directly to the ID card page

### Technical Details

- **Service Worker**: Caches pages and assets for offline access
- **Manifest**: Defines app name, icons, and display mode
- **Install Prompt**: Automatically shows on mobile devices after a few seconds
- **Standalone Mode**: App runs in full-screen mode when launched from home screen

### For Administrators

The PWA installation is optional - users can still access the system via web browser if they prefer. The PWA simply provides a more convenient way to access the ID card quickly when needed.


<?php
/**
 * Main Configuration File
 * Digital ID Application
 */

// Error reporting (environment-based)
$isProduction = getenv('APP_ENV') === 'production' || getenv('APP_ENV') === 'prod';
if ($isProduction) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Europe/London');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Use secure cookies in production (requires HTTPS)
ini_set('session.cookie_secure', $isProduction ? 1 : 0);

// Application paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Application settings (from .env or defaults)
define('APP_NAME', getenv('APP_NAME') ?: 'Digital ID');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Contact email - Main super admin and point of contact
define('CONTACT_EMAIL', 'digital-ids@outlook.com');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('VERIFICATION_TOKEN_EXPIRY_HOURS', 24);

// Digital ID specific settings
define('QR_TOKEN_EXPIRY_MINUTES', 5); // QR code tokens expire after 5 minutes
define('NFC_TOKEN_EXPIRY_MINUTES', 5); // NFC tokens expire after 5 minutes
define('ID_CARD_EXPIRY_DAYS', 365); // ID cards expire after 1 year

// Pagination
define('ITEMS_PER_PAGE', 20);

// Date format (UK format)
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    // Prevent caching of pages with forms to avoid CSRF token mismatches
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    session_start();
}

// Include shared authentication package
require_once ROOT_PATH . '/shared-auth/src/Database.php';
require_once ROOT_PATH . '/shared-auth/src/Auth.php';
require_once ROOT_PATH . '/shared-auth/src/RBAC.php';
require_once ROOT_PATH . '/shared-auth/src/CSRF.php';
require_once ROOT_PATH . '/shared-auth/src/Email.php';
require_once ROOT_PATH . '/shared-auth/src/OrganisationalUnits.php';

// Include database configuration
require_once CONFIG_PATH . '/database.php';

// Autoload classes (simple autoloader)
spl_autoload_register(function ($class) {
    $paths = [
        SRC_PATH . '/models/' . $class . '.php',
        SRC_PATH . '/controllers/' . $class . '.php',
        SRC_PATH . '/classes/' . $class . '.php',
        SRC_PATH . '/services/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Calculate base URL dynamically based on document root
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        static $baseUrl = null;
        if ($baseUrl === null) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $docRootNormalized = rtrim(str_replace('\\', '/', $docRoot), '/');
            $lastPart = strtolower(basename($docRootNormalized));
            
            // Check if document root is public folder
            $isPublicRoot = ($lastPart === 'public');
            
            // Also check if script is in public folder but doc root is project root
            if (!$isPublicRoot && strpos($scriptName, '/public/') !== false) {
                $baseUrl = '/public';
            } elseif ($isPublicRoot) {
                $baseUrl = '';
            } else {
                // Default: assume document root is project root, so we need /public prefix
                $baseUrl = '/public';
            }
        }
        return $baseUrl;
    }
}

// Helper function to generate URLs with proper base path
if (!function_exists('url')) {
    function url($path) {
        $baseUrl = getBaseUrl();
        // Remove leading slash from path if present, we'll add it
        $path = ltrim($path, '/');
        // If baseUrl is empty, we still need a leading slash
        if (empty($baseUrl)) {
            return '/' . $path;
        }
        return $baseUrl . '/' . $path;
    }
}


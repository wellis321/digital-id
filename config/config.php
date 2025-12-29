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

// Staff Service Integration
// Check database first (for web-configured settings), then fall back to .env
function getStaffServiceSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        try {
            if (function_exists('getDbConnection')) {
                $db = getDbConnection();
                // Only check if we have an organisation context (user is logged in)
                if (defined('Auth') && class_exists('Auth') && Auth::isLoggedIn()) {
                    $organisationId = Auth::getOrganisationId();
                    if ($organisationId) {
                        try {
                            $stmt = $db->prepare("SELECT setting_key, setting_value FROM organisation_settings WHERE organisation_id = ?");
                            $stmt->execute([$organisationId]);
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($results as $row) {
                                $settings[$row['setting_key']] = $row['setting_value'];
                            }
                        } catch (PDOException $e) {
                            // Table might not exist yet, ignore
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore errors during config loading
        }
    }
    
    return $settings[$key] ?? $default;
}

// Get settings from database or .env
$useStaffServiceDb = getStaffServiceSetting('use_staff_service', '');
$staffServiceUrlDb = getStaffServiceSetting('staff_service_url', '');
$staffServiceApiKeyDb = getStaffServiceSetting('staff_service_api_key', '');
$staffSyncIntervalDb = getStaffServiceSetting('staff_sync_interval', '');

define('USE_STAFF_SERVICE', 
    $useStaffServiceDb === '1' || 
    getenv('USE_STAFF_SERVICE') === 'true' || 
    getenv('USE_STAFF_SERVICE') === '1'
);
define('STAFF_SERVICE_URL', 
    !empty($staffServiceUrlDb) ? $staffServiceUrlDb : (getenv('STAFF_SERVICE_URL') ?: '')
);
define('STAFF_SERVICE_API_KEY', 
    !empty($staffServiceApiKeyDb) ? $staffServiceApiKeyDb : (getenv('STAFF_SERVICE_API_KEY') ?: '')
);
define('STAFF_SYNC_INTERVAL', 
    !empty($staffSyncIntervalDb) ? (int)$staffSyncIntervalDb : ((int)(getenv('STAFF_SYNC_INTERVAL') ?: 3600))
);

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

// Ensure RateLimiter is available (for rate limiting functionality)
if (class_exists('RateLimiter') === false && file_exists(SRC_PATH . '/classes/RateLimiter.php')) {
    require_once SRC_PATH . '/classes/RateLimiter.php';
}

// Calculate base URL dynamically based on document root
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        static $baseUrl = null;
        if ($baseUrl === null) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
            $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            
            $docRootNormalized = rtrim(str_replace('\\', '/', $docRoot), '/');
            $lastPart = strtolower(basename($docRootNormalized));
            
            // Check if document root is public folder
            $isPublicRoot = ($lastPart === 'public');
            
            if ($isPublicRoot) {
                // Document root is public folder - no prefix needed
                $baseUrl = '';
            } else {
                // Document root is project root
                // Since .htaccess rewrites all requests to /public/, URLs should NOT include /public/
                // The rewrite handles the routing internally
                $baseUrl = '';
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


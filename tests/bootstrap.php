<?php
/**
 * Test Bootstrap
 * Sets up the test environment before running tests
 */

// Set test environment BEFORE loading config
define('APP_ENV', 'testing');
define('ROOT_PATH', dirname(__DIR__));

// Prevent headers from being sent in CLI mode
if (php_sapi_name() === 'cli') {
    // Suppress header output in CLI
    ob_start();
}

// Load configuration
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// Clear output buffer if we started one
if (php_sapi_name() === 'cli' && ob_get_level() > 0) {
    ob_end_clean();
}

// Set test database if not already set
if (!defined('DB_NAME') || DB_NAME !== 'digital_ids_test') {
    // Override database name for testing
    // Note: This should be set via phpunit.xml environment variables
    if (getenv('DB_NAME')) {
        // Only define if not already defined
        if (!defined('DB_NAME')) {
            define('DB_NAME', getenv('DB_NAME'));
        }
    }
}

// Load test helpers
require_once __DIR__ . '/helpers/TestHelper.php';
require_once __DIR__ . '/helpers/DatabaseHelper.php';

// Start session for testing (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    // In CLI mode, we can start session without headers
    if (php_sapi_name() === 'cli') {
        session_start();
    }
}

// Clear any existing session data
$_SESSION = [];

// Set up error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');


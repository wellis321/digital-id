<?php
// Use absolute path to ensure correct location
$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    die('Configuration file not found. Please check file permissions.');
}
require_once $configPath;

// Set flag to indicate intentional logout (prevents shutdown function from restoring session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['_logout_intentional'] = true;

// Check if actually logged in before logging out
if (Auth::isLoggedIn()) {
    // Logout user
    Auth::logout();
}

// Preserve CSRF token
$csrfToken = $_SESSION[CSRF_TOKEN_NAME] ?? null;

// Ensure session is cleared regardless
$_SESSION = [];

// Restore CSRF token if it existed
if ($csrfToken !== null) {
    $_SESSION[CSRF_TOKEN_NAME] = $csrfToken;
}

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}

// Start new clean session
session_start();
session_regenerate_id(true);

// Restore CSRF token in new session
if ($csrfToken !== null) {
    $_SESSION[CSRF_TOKEN_NAME] = $csrfToken;
}

// Redirect to home page with cache-busting to ensure fresh page load
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// Add cache-busting query parameter to force browser to fetch fresh page
header('Location: ' . url('index.php?logout=' . time()));
exit;


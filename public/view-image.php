<?php
/**
 * Image Viewer
 * Safely serves images from outside the public directory
 */

require_once dirname(__DIR__) . '/config/config.php';

// Get image path from query parameter
$imagePath = $_GET['path'] ?? '';

if (empty($imagePath)) {
    http_response_code(400);
    die('Image path required');
}

// Sanitize path - only allow uploads directory
$allowedBase = dirname(__DIR__) . '/uploads/';
$allowedBaseNormalized = str_replace('\\', '/', realpath($allowedBase)) . '/';

// Remove any null bytes and normalize path
$imagePath = str_replace("\0", '', $imagePath);
$requestedPath = dirname(__DIR__) . '/' . ltrim($imagePath, '/');
$fullPath = realpath($requestedPath);

// Security check: ensure the file is within the uploads directory
// Use realpath comparison to prevent directory traversal
if (!$fullPath || !$allowedBaseNormalized) {
    http_response_code(403);
    die('Access denied');
}

// Normalize both paths for comparison (handle Windows/Unix path separators)
$fullPathNormalized = str_replace('\\', '/', $fullPath) . '/';

// Strict check: file must be within uploads directory (not equal to uploads root)
if (strpos($fullPathNormalized, $allowedBaseNormalized) !== 0 || $fullPathNormalized === $allowedBaseNormalized) {
    http_response_code(403);
    die('Access denied');
}

// Check if file exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    die('Image not found');
}

// Get mime type
$mimeType = mime_content_type($fullPath);
if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
    http_response_code(403);
    die('Invalid image type');
}

// For pending photos, require login
if (strpos($imagePath, 'pending') !== false) {
    Auth::requireLogin();
    
    // Admins can view any pending photo
    if (RBAC::isAdmin()) {
        // Allow access
    } else {
        // Regular users can only view their own pending photos
        $employee = Employee::findByUserId(Auth::getUserId());
        if (!$employee) {
            http_response_code(403);
            die('Access denied');
        }
        
        // Check if this is the user's own pending photo
        $filename = basename($fullPath);
        $expectedPrefix = 'employee_' . $employee['id'] . '_';
        
        if (strpos($filename, $expectedPrefix) !== 0) {
            http_response_code(403);
            die('Access denied');
        }
    }
}

// Set headers and serve file
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=3600');

readfile($fullPath);
exit;


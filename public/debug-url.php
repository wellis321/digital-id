<?php
/**
 * Debug script to check URL base detection
 * DELETE THIS FILE AFTER DEBUGGING
 */

echo "<pre>";
echo "DEBUG: URL Base Detection\n";
echo "========================\n\n";

echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n\n";

$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$docRootNormalized = rtrim(str_replace('\\', '/', $docRoot), '/');
$lastPart = strtolower(basename($docRootNormalized));
$isPublicRoot = ($lastPart === 'public');

echo "Document Root (normalized): $docRootNormalized\n";
echo "Last part: $lastPart\n";
echo "Is Public Root: " . ($isPublicRoot ? 'YES' : 'NO') . "\n\n";

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
$scriptInPublic = strpos($scriptName, '/public/') !== false || strpos(str_replace('\\', '/', $scriptFilename), '/public/') !== false;

echo "Script Name contains /public/: " . (strpos($scriptName, '/public/') !== false ? 'YES' : 'NO') . "\n";
echo "Script Filename contains /public/: " . (strpos(str_replace('\\', '/', $scriptFilename), '/public/') !== false ? 'YES' : 'NO') . "\n";
echo "Script is in public folder: " . ($scriptInPublic ? 'YES' : 'NO') . "\n\n";

// Simulate the function
if ($isPublicRoot) {
    $baseUrl = '';
} else {
    if ($scriptInPublic) {
        $baseUrl = '';
    } else {
        $baseUrl = '/public';
    }
}

echo "CALCULATED BASE URL: '$baseUrl'\n";
echo "Expected: '' (empty string)\n\n";

echo "Test URL generation:\n";
echo "url('features.php') would be: " . ($baseUrl ? $baseUrl . '/features.php' : '/features.php') . "\n";
echo "</pre>";
?>


<?php
/**
 * Backward-compatible redirect for old docs.php?section=X bookmarks/links.
 * Documentation now lives as separate pages (docs-*.php) - see includes/docs-sections.php.
 */
require_once dirname(__DIR__) . '/config/config.php';

$sectionToFile = [
    'getting-started' => 'docs-getting-started.php',
    'pwa' => 'docs-pwa.php',
    'user-guide' => 'docs-user-guide.php',
    'admin-guide' => 'docs-admin-guide.php',
    'verification' => 'docs-verification.php',
    'organisational-structure' => 'docs-organisational-structure.php',
    'import-export' => 'docs-import-export.php',
    'entra-integration' => 'docs-entra-integration.php',
    'mcp-integration' => 'docs-mcp-integration.php',
    'user-stories' => 'docs-user-stories.php',
    'security' => 'docs-security.php',
    'check-in-sessions' => 'docs-check-in-sessions.php',
    'staff-service' => 'docs-staff-service.php',
    'building-access' => 'docs-building-access.php',
    'troubleshooting' => 'docs-troubleshooting.php',
];

$section = $_GET['section'] ?? 'getting-started';
$target = $sectionToFile[$section] ?? 'docs-getting-started.php';

header('Location: ' . url($target), true, 301);
exit;

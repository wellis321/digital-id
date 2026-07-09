<?php
/**
 * Single source of truth for documentation section metadata - used by
 * docs-sidebar.php (per-page sidebar nav) and header.php (main nav dropdown).
 * Keeping this as one shared list avoids the sidebar and dropdown drifting
 * out of sync with each other.
 */
$docsSections = [
    'docs-getting-started.php' => ['icon' => 'fas fa-rocket', 'label' => 'Getting Started'],
    'docs-user-guide.php' => ['icon' => 'fas fa-user', 'label' => 'User Guide'],
    'docs-pwa.php' => ['icon' => 'fas fa-mobile-alt', 'label' => 'Install as App'],
    'docs-admin-guide.php' => ['icon' => 'fas fa-user-shield', 'label' => 'Admin Guide'],
    'docs-verification.php' => ['icon' => 'fas fa-check-circle', 'label' => 'Verification'],
    'docs-organisational-structure.php' => ['icon' => 'fas fa-sitemap', 'label' => 'Organisational Structure'],
    'docs-import-export.php' => ['icon' => 'fas fa-exchange-alt', 'label' => 'Import & Export'],
    'docs-entra-integration.php' => ['icon' => 'fab fa-microsoft', 'label' => 'Microsoft Entra'],
    'docs-check-in-sessions.php' => ['icon' => 'fas fa-clipboard-check', 'label' => 'Check-In Sessions'],
    'docs-staff-service.php' => ['icon' => 'fas fa-users', 'label' => 'Staff Service Integration'],
    'docs-building-access.php' => ['icon' => 'fas fa-door-open', 'label' => 'Building Access Integration'],
    'docs-mcp-integration.php' => ['icon' => 'fas fa-robot', 'label' => 'AI Integration (MCP)'],
    'docs-security.php' => ['icon' => 'fas fa-shield-alt', 'label' => 'Security'],
    'docs-user-stories.php' => ['icon' => 'fas fa-book-open', 'label' => 'User Stories'],
    'docs-troubleshooting.php' => ['icon' => 'fas fa-question-circle', 'label' => 'Troubleshooting'],
];

<?php
/**
 * Security and Accessibility Test Suite
 * Tests the critical fixes implemented for production readiness
 */

require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & Accessibility Test Results</title>
    <style>
        body { font-family: monospace; padding: 2rem; background: #f5f5f5; }
        .test-section { background: white; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 0; border: 1px solid #ddd; }
        .test-section h2 { margin-top: 0; color: #2563eb; }
        .test-item { padding: 0.75rem; margin: 0.5rem 0; border-left: 4px solid #ddd; }
        .test-item.pass { border-left-color: #10b981; background: #f0fdf4; }
        .test-item.fail { border-left-color: #ef4444; background: #fef2f2; }
        .test-item.warning { border-left-color: #f59e0b; background: #fffbeb; }
        .test-item h3 { margin: 0 0 0.5rem 0; font-size: 1rem; }
        .test-item p { margin: 0.25rem 0; color: #6b7280; font-size: 0.875rem; }
        .code { background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Security & Accessibility Test Results</h1>
    <p><strong>Test Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php
    $results = [
        'security' => [],
        'accessibility' => [],
        'error_pages' => []
    ];

    // ========== SECURITY TESTS ==========
    echo '<div class="test-section">';
    echo '<h2>🔒 Security Tests</h2>';

    // Test 1: RateLimiter class exists
    $rateLimiterExists = class_exists('RateLimiter');
    $results['security'][] = [
        'name' => 'RateLimiter Class',
        'status' => $rateLimiterExists ? 'pass' : 'fail',
        'message' => $rateLimiterExists ? 'RateLimiter class found' : 'RateLimiter class not found - rate limiting not available'
    ];

    // Test 2: view-image.php path validation
    $viewImagePath = dirname(__DIR__) . '/public/view-image.php';
    if (file_exists($viewImagePath)) {
        $content = file_get_contents($viewImagePath);
        $hasRealpath = strpos($content, 'realpath') !== false;
        $hasNormalized = strpos($content, 'str_replace') !== false && strpos($content, 'normalized') !== false;
        $results['security'][] = [
            'name' => 'Path Traversal Protection',
            'status' => ($hasRealpath && $hasNormalized) ? 'pass' : 'fail',
            'message' => ($hasRealpath && $hasNormalized) 
                ? 'Enhanced path validation using realpath and normalization found' 
                : 'Path validation may be insufficient'
        ];
    } else {
        $results['security'][] = [
            'name' => 'Path Traversal Protection',
            'status' => 'fail',
            'message' => 'view-image.php file not found'
        ];
    }

    // Test 3: CSP Header in .htaccess
    $htaccessPath = dirname(__DIR__) . '/public/.htaccess';
    if (file_exists($htaccessPath)) {
        $htaccessContent = file_get_contents($htaccessPath);
        $hasCSP = strpos($htaccessContent, 'Content-Security-Policy') !== false;
        $results['security'][] = [
            'name' => 'Content Security Policy',
            'status' => $hasCSP ? 'pass' : 'fail',
            'message' => $hasCSP 
                ? 'CSP header found in .htaccess' 
                : 'CSP header not found - XSS protection may be insufficient'
        ];
    } else {
        $results['security'][] = [
            'name' => 'Content Security Policy',
            'status' => 'fail',
            'message' => '.htaccess file not found'
        ];
    }

    // Test 4: HTTPS redirect (commented, but present)
    if (file_exists($htaccessPath)) {
        $htaccessContent = file_get_contents($htaccessPath);
        $hasHttpsRedirect = strpos($htaccessContent, 'HTTPS') !== false || strpos($htaccessContent, 'https') !== false;
        $results['security'][] = [
            'name' => 'HTTPS Enforcement',
            'status' => $hasHttpsRedirect ? 'warning' : 'fail',
            'message' => $hasHttpsRedirect 
                ? 'HTTPS redirect rule found (may be commented - uncomment for production)' 
                : 'HTTPS redirect not found - add for production'
        ];
    }

    // Test 5: Rate limiting on login
    $loginPath = dirname(__DIR__) . '/public/login.php';
    if (file_exists($loginPath)) {
        $loginContent = file_get_contents($loginPath);
        $hasRateLimit = strpos($loginContent, 'RateLimiter') !== false;
        $results['security'][] = [
            'name' => 'Login Rate Limiting',
            'status' => $hasRateLimit ? 'pass' : 'fail',
            'message' => $hasRateLimit 
                ? 'Rate limiting implemented on login endpoint' 
                : 'Rate limiting not found on login endpoint'
        ];
    }

    // Display security results
    foreach ($results['security'] as $test) {
        echo '<div class="test-item ' . $test['status'] . '">';
        echo '<h3>' . ($test['status'] === 'pass' ? '✅' : ($test['status'] === 'warning' ? '⚠️' : '❌')) . ' ' . htmlspecialchars($test['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($test['message']) . '</p>';
        echo '</div>';
    }
    echo '</div>';

    // ========== ACCESSIBILITY TESTS ==========
    echo '<div class="test-section">';
    echo '<h2>♿ Accessibility Tests</h2>';

    // Test 1: Skip link in header
    $headerPath = dirname(__DIR__) . '/includes/header.php';
    if (file_exists($headerPath)) {
        $headerContent = file_get_contents($headerPath);
        $hasSkipLink = strpos($headerContent, 'skip-link') !== false || strpos($headerContent, 'skip to main') !== false;
        $results['accessibility'][] = [
            'name' => 'Skip Link',
            'status' => $hasSkipLink ? 'pass' : 'fail',
            'message' => $hasSkipLink 
                ? 'Skip to main content link found' 
                : 'Skip link not found - keyboard navigation impaired'
        ];
    }

    // Test 2: ARIA labels on navigation
    if (file_exists($headerPath)) {
        $headerContent = file_get_contents($headerPath);
        $hasAriaLabel = strpos($headerContent, 'aria-label') !== false;
        $hasAriaExpanded = strpos($headerContent, 'aria-expanded') !== false;
        $hasRoleNav = strpos($headerContent, 'role="navigation"') !== false;
        $results['accessibility'][] = [
            'name' => 'Navigation ARIA Attributes',
            'status' => ($hasAriaLabel && $hasAriaExpanded && $hasRoleNav) ? 'pass' : 'warning',
            'message' => ($hasAriaLabel && $hasAriaExpanded && $hasRoleNav)
                ? 'ARIA labels, expanded state, and navigation role found'
                : 'Some ARIA attributes may be missing'
        ];
    }

    // Test 3: Form error associations
    if (file_exists($loginPath)) {
        $loginContent = file_get_contents($loginPath);
        $hasAriaInvalid = strpos($loginContent, 'aria-invalid') !== false;
        $hasAriaDescribedBy = strpos($loginContent, 'aria-describedby') !== false;
        $hasAriaRequired = strpos($loginContent, 'aria-required') !== false;
        $results['accessibility'][] = [
            'name' => 'Form Error Associations',
            'status' => ($hasAriaInvalid && $hasAriaDescribedBy && $hasAriaRequired) ? 'pass' : 'warning',
            'message' => ($hasAriaInvalid && $hasAriaDescribedBy && $hasAriaRequired)
                ? 'Form inputs have aria-invalid, aria-describedby, and aria-required'
                : 'Some form accessibility attributes may be missing'
        ];
    }

    // Test 4: Alt text improvements
    $idCardPath = dirname(__DIR__) . '/public/id-card.php';
    if (file_exists($idCardPath)) {
        $idCardContent = file_get_contents($idCardPath);
        $hasDescriptiveAlt = strpos($idCardContent, 'ID card photo for') !== false || strpos($idCardContent, 'photo for') !== false;
        $results['accessibility'][] = [
            'name' => 'Descriptive Alt Text',
            'status' => $hasDescriptiveAlt ? 'pass' : 'fail',
            'message' => $hasDescriptiveAlt
                ? 'Descriptive alt text found for images'
                : 'Alt text may be too generic (e.g., just "Photo")'
        ];
    }

    // Test 5: Live regions for alerts
    if (file_exists($loginPath)) {
        $loginContent = file_get_contents($loginPath);
        $hasRoleAlert = strpos($loginContent, 'role="alert"') !== false;
        $hasAriaLive = strpos($loginContent, 'aria-live') !== false;
        $results['accessibility'][] = [
            'name' => 'Live Regions for Alerts',
            'status' => ($hasRoleAlert && $hasAriaLive) ? 'pass' : 'warning',
            'message' => ($hasRoleAlert && $hasAriaLive)
                ? 'Alerts have role="alert" and aria-live attributes'
                : 'Some alerts may not be announced to screen readers'
        ];
    }

    // Display accessibility results
    foreach ($results['accessibility'] as $test) {
        echo '<div class="test-item ' . $test['status'] . '">';
        echo '<h3>' . ($test['status'] === 'pass' ? '✅' : ($test['status'] === 'warning' ? '⚠️' : '❌')) . ' ' . htmlspecialchars($test['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($test['message']) . '</p>';
        echo '</div>';
    }
    echo '</div>';

    // ========== ERROR PAGES TESTS ==========
    echo '<div class="test-section">';
    echo '<h2>📄 Error Pages Tests</h2>';

    $errorPages = ['404.php', '403.php', '500.php'];
    foreach ($errorPages as $page) {
        $pagePath = dirname(__DIR__) . '/public/' . $page;
        $exists = file_exists($pagePath);
        $results['error_pages'][] = [
            'name' => $page,
            'status' => $exists ? 'pass' : 'fail',
            'message' => $exists ? 'Error page exists' : 'Error page not found'
        ];
    }

    // Check .htaccess ErrorDocument directives
    if (file_exists($htaccessPath)) {
        $htaccessContent = file_get_contents($htaccessPath);
        $hasErrorDocs = strpos($htaccessContent, 'ErrorDocument') !== false;
        $results['error_pages'][] = [
            'name' => 'ErrorDocument Directives',
            'status' => $hasErrorDocs ? 'pass' : 'warning',
            'message' => $hasErrorDocs
                ? 'ErrorDocument directives found in .htaccess'
                : 'ErrorDocument directives not found - error pages may not be used'
        ];
    }

    // Display error page results
    foreach ($results['error_pages'] as $test) {
        echo '<div class="test-item ' . $test['status'] . '">';
        echo '<h3>' . ($test['status'] === 'pass' ? '✅' : ($test['status'] === 'warning' ? '⚠️' : '❌')) . ' ' . htmlspecialchars($test['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($test['message']) . '</p>';
        echo '</div>';
    }
    echo '</div>';

    // ========== SUMMARY ==========
    $totalTests = count($results['security']) + count($results['accessibility']) + count($results['error_pages']);
    $passedTests = 0;
    $failedTests = 0;
    $warningTests = 0;

    foreach ($results as $category) {
        foreach ($category as $test) {
            if ($test['status'] === 'pass') $passedTests++;
            elseif ($test['status'] === 'fail') $failedTests++;
            elseif ($test['status'] === 'warning') $warningTests++;
        }
    }

    echo '<div class="test-section" style="background: ' . ($failedTests === 0 ? '#f0fdf4' : '#fef2f2') . ';">';
    echo '<h2>📊 Test Summary</h2>';
    echo '<p><strong>Total Tests:</strong> ' . $totalTests . '</p>';
    echo '<p><strong style="color: #10b981;">✅ Passed:</strong> ' . $passedTests . '</p>';
    echo '<p><strong style="color: #f59e0b;">⚠️ Warnings:</strong> ' . $warningTests . '</p>';
    echo '<p><strong style="color: #ef4444;">❌ Failed:</strong> ' . $failedTests . '</p>';
    echo '</div>';

    // ========== MANUAL TESTING INSTRUCTIONS ==========
    echo '<div class="test-section">';
    echo '<h2>🧪 Manual Testing Required</h2>';
    echo '<p>The following tests require manual browser testing:</p>';
    echo '<ol>';
    echo '<li><strong>Rate Limiting:</strong> Try logging in with wrong credentials 6 times - should see rate limit message</li>';
    echo '<li><strong>Skip Link:</strong> Press Tab on page load - skip link should appear, Enter should jump to main content</li>';
    echo '<li><strong>Form Errors:</strong> Submit login form with errors - errors should be associated with inputs</li>';
    echo '<li><strong>Screen Reader:</strong> Test with NVDA/JAWS/VoiceOver - navigation and alerts should be announced</li>';
    echo '<li><strong>Error Pages:</strong> Visit non-existent URL - should see 404 page</li>';
    echo '<li><strong>CSP Header:</strong> Check browser DevTools Network tab - Content-Security-Policy header should be present</li>';
    echo '<li><strong>HTTPS Redirect:</strong> (When HTTPS enabled) Visit HTTP URL - should redirect to HTTPS</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="test-section">
        <h2>📝 Notes</h2>
        <p><strong>⚠️ Important:</strong> Delete this test file after testing in production!</p>
        <p>This file should not be accessible in production. Remove it or protect it with authentication.</p>
    </div>
</body>
</html>


<?php
/**
 * PHP Built-in Server Router
 * Handles routing for PHP's built-in development server
 * This allows ErrorDocument-like functionality in development
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove query string for file checking
$filePath = __DIR__ . $requestPath;

// If it's a directory, look for index.php or index.html
if (is_dir($filePath)) {
    if (file_exists($filePath . '/index.php')) {
        $filePath = $filePath . '/index.php';
    } elseif (file_exists($filePath . '/index.html')) {
        $filePath = $filePath . '/index.html';
    } else {
        // Directory exists but no index file - show 404
        http_response_code(404);
        require_once dirname(__DIR__) . '/config/config.php';
        $pageTitle = 'Page Not Found';
        include dirname(__DIR__) . '/includes/header.php';
        ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <h1 style="font-size: 6rem; margin-bottom: 1rem; color: #6b7280;">404</h1>
            <h2 style="margin-bottom: 1rem;">Page Not Found</h2>
            <p style="color: #6b7280; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
                <?php if (Auth::isLoggedIn()): ?>
                    <a href="<?php echo url('id-card.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-id-card"></i> View ID Card
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('login.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
        <?php
        exit;
    }
}

// If file exists and is readable, serve it
if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
    // Ensure file is within public directory (security check)
    $realPath = realpath($filePath);
    $publicPath = realpath(__DIR__);
    
    if ($realPath && strpos($realPath, $publicPath) === 0) {
        return false; // Let PHP serve the file
    }
}

// File doesn't exist - show 404 page
http_response_code(404);
require_once dirname(__DIR__) . '/config/config.php';
$pageTitle = 'Page Not Found';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="card" style="text-align: center; padding: 4rem 2rem;">
    <h1 style="font-size: 6rem; margin-bottom: 1rem; color: #6b7280;">404</h1>
    <h2 style="margin-bottom: 1rem;">Page Not Found</h2>
    <p style="color: #6b7280; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Go to Homepage
        </a>
        <?php if (Auth::isLoggedIn()): ?>
            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-id-card"></i> View ID Card
            </a>
        <?php else: ?>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<?php
exit;


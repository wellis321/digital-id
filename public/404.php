<?php
/**
 * 404 Not Found Error Page
 */
http_response_code(404);
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Page Not Found';
include INCLUDES_PATH . '/header.php';
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

<?php include INCLUDES_PATH . '/footer.php'; ?>


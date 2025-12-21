<?php
/**
 * 403 Forbidden Error Page
 */
http_response_code(403);
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Access Forbidden';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="text-align: center; padding: 4rem 2rem;">
    <h1 style="font-size: 6rem; margin-bottom: 1rem; color: #dc2626;">403</h1>
    <h2 style="margin-bottom: 1rem;">Access Forbidden</h2>
    <p style="color: #6b7280; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        You don't have permission to access this resource. If you believe this is an error, please contact your administrator.
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


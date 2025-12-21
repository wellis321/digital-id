<?php
/**
 * 500 Internal Server Error Page
 */
http_response_code(500);
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Server Error';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="text-align: center; padding: 4rem 2rem;">
    <h1 style="font-size: 6rem; margin-bottom: 1rem; color: #dc2626;">500</h1>
    <h2 style="margin-bottom: 1rem;">Internal Server Error</h2>
    <p style="color: #6b7280; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        Something went wrong on our end. We've been notified and are working to fix the issue. Please try again later.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Go to Homepage
        </a>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
    </div>
    <p style="margin-top: 2rem; color: #9ca3af; font-size: 0.875rem;">
        If this problem persists, please <a href="mailto:<?php echo htmlspecialchars(CONTACT_EMAIL); ?>" style="color: #2563eb;">contact support</a>.
    </p>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


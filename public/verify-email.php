<?php
require_once dirname(__DIR__) . '/config/config.php';

$token = $_GET['token'] ?? '';
$result = null;

if ($token) {
    $result = Auth::verifyEmail($token);
}

$pageTitle = 'Verify Email';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Verify Email Address</h1>
    
    <?php if ($result): ?>
        <?php if ($result['success']): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($result['message']); ?></div>
            <p><a href="<?php echo url('login.php'); ?>" class="btn btn-primary">Go to Login</a></p>
        <?php else: ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($result['message']); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-error">Invalid verification link.</div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


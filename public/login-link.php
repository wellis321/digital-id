<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once SRC_PATH . '/classes/RateLimiter.php';

$error = ''; $sent = false;
if (DEMO_MODE) { http_response_code(403); $pageTitle = 'Not available in the demo'; include dirname(__DIR__) . '/includes/header.php'; echo '<div class="card"><h1>Not available in the demo</h1><p>The demo does not send email. Use one of the demo logins on the sign-in page.</p><p><a href="' . url('login.php') . '">Back to sign in</a></p></div>'; include dirname(__DIR__) . '/includes/footer.php'; exit; }
$token = (string) ($_GET['t'] ?? $_POST['t'] ?? '');

if ($token !== '') {
    // The link in the email only SHOWS a button; pressing it signs in. Mail scanners that open links therefore cannot use it up.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && CSRF::validatePost()) {
        $ml = MagicLink::consume($token);
        if ($ml === 'second_step_required') { header('Location: ' . url('login-2fa.php')); exit; }
        if ($ml) { header('Location: ' . url('id-card.php')); exit; }
        $error = 'That link has expired or has already been used. Ask for a new one below.'; $token = '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') { $error = 'Please try again.'; }
    elseif (!MagicLink::valid($token)) { $error = 'That link has expired or has already been used. Ask for a new one below.'; $token = ''; }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = 'magic_' . RateLimiter::getClientIdentifier();
    if (RateLimiter::isRateLimited($key, 5, 900)) { $error = 'Too many requests. Please try again in a few minutes.'; }
    elseif (!CSRF::validatePost()) { $error = 'Please try again.'; }
    else { MagicLink::request((string) ($_POST['email'] ?? ''), RateLimiter::getClientIdentifier()); $sent = true; }
}
$pageTitle = 'Sign in by email';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="card" style="max-width:480px;margin:2rem auto">
    <h1>Sign in by email</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($token !== ''): ?>
        <p>Press the button to sign in on this device.</p>
        <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="t" value="<?= htmlspecialchars($token) ?>"><button class="btn btn-primary" type="submit">Sign in</button></form>
    <?php elseif ($sent): ?>
        <div class="alert alert-success">If that address belongs to a member of staff, a sign-in link is on its way. It works once and lasts 15 minutes.</div>
    <?php else: ?>
        <p>Enter your work email address and we will email you a link. No password needed.</p>
        <form method="POST"><?= CSRF::tokenField() ?>
            <div class="form-group"><label for="email">Work email</label><input type="email" id="email" name="email" required autocomplete="email"></div>
            <button class="btn btn-primary" type="submit">Email me a link</button>
        </form>
    <?php endif; ?>
    <p><a href="<?= url('login.php') ?>">Sign in another way</a></p>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

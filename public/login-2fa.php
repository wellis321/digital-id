<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once SRC_PATH . '/classes/TwoFactor.php';

$pending = TwoFactor::pending();
if (!$pending) { header('Location: ' . url('login.php')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Please try again.';
    } else {
        $r = TwoFactor::check((int) $pending['uid'], (string) ($_POST['code'] ?? ''));
        if ($r === true) {
            unset($_SESSION['pending_2fa']);
            if (Auth::loginUserById((int) $pending['uid'], false) === true) { header('Location: ' . url('id-card.php')); exit; }
            $error = 'Your account cannot sign in. Please contact your administrator.';
        } else {
            $error = $r;
            if (str_starts_with($r, 'Too many')) { unset($_SESSION['pending_2fa']); }
        }
    }
}
$pageTitle = 'One more step';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="card" style="max-width:480px;margin:2rem auto">
    <h1>One more step</h1>
    <p>Your password was right. Enter the 6-digit code from your authenticator app. If you have lost your phone, enter one of your recovery codes instead (each works once).</p>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" autocomplete="off"><?= CSRF::tokenField() ?>
        <div class="form-group"><label for="code">Code</label><input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required autofocus maxlength="14"></div>
        <button class="btn btn-primary" type="submit">Sign in</button> <a class="btn btn-secondary" href="<?= url('login.php') ?>">Start again</a>
    </form>
    <p class="small" style="color:#666">Lost your phone and your codes? Ask an administrator to reset two-step sign-in for you.</p>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

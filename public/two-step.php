<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once SRC_PATH . '/classes/TwoFactor.php';

Auth::requireLogin();
$uid = (int) Auth::getUserId();
$db = getDbConnection();
$st = $db->prepare('SELECT id, email, password_hash FROM users WHERE id = ?'); $st->execute([$uid]); $me = $st->fetch();
$available = $me && TwoFactor::available($me);
$error = ''; $success = ''; $codes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!CSRF::validatePost()) { $error = 'Please try again.'; }
    elseif (!$available) { $error = 'Two-step sign-in is for accounts that sign in with a password. A shared demonstration account cannot use it.'; }
    elseif ($action === 'start') { TwoFactor::begin($uid); }
    elseif ($action === 'enable') {
        $r = TwoFactor::enable($uid, (string) ($_POST['code'] ?? ''));
        if (is_array($r)) { $codes = $r; $success = 'Two-step sign-in is on.'; } else { $error = $r; }
    } elseif ($action === 'disable') {
        $r = TwoFactor::disable($uid, (string) ($_POST['password'] ?? ''));
        if ($r === true) { $success = 'Two-step sign-in is switched off.'; } else { $error = $r; }
    } elseif ($action === 'codes') {
        $hash = (string) ($me['password_hash'] ?? '');
        if (!TwoFactor::on($uid) || $hash === '' || !password_verify((string) ($_POST['password'] ?? ''), $hash)) { $error = 'Your password is not right.'; }
        else { $codes = TwoFactor::newRecoveryCodes($uid); }
    }
}
$on = TwoFactor::on($uid);
$secret = (!$on && $available) ? TwoFactor::pendingSecret($uid) : null;
$pageTitle = 'Two-step sign-in';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="card" style="max-width:560px;margin:2rem auto">
    <h1>Two-step sign-in</h1>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($codes): ?>
        <h2>Save your recovery codes</h2>
        <p>These eight codes are for the day you lose your phone. <strong>Each works once, and they will not be shown again.</strong> Keep them somewhere safe and apart from your password.</p>
        <ul style="font-family:monospace;font-size:1.1rem;list-style:none;padding:0;columns:2"><?php foreach ($codes as $c): ?><li><?= htmlspecialchars($c) ?></li><?php endforeach; ?></ul>
        <p><a class="btn btn-primary" href="<?= url('two-step.php') ?>">I have saved them</a></p>
    <?php elseif (!$available): ?>
        <p>Two-step sign-in applies to accounts that sign in with a password. If you sign in with Microsoft, your Microsoft settings decide this. A shared demonstration account cannot use it.</p>
    <?php elseif ($on): ?>
        <p><strong>It is on.</strong> After your password, Digital ID asks for a code from your authenticator app. You have <?= TwoFactor::recoveryLeft($uid) ?> recovery code<?= TwoFactor::recoveryLeft($uid) === 1 ? '' : 's' ?> left.</p>
        <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="codes">
            <div class="form-group"><label for="pw1">Make new recovery codes (your password)</label><input type="password" id="pw1" name="password" required autocomplete="current-password"></div>
            <button class="btn btn-secondary" type="submit">New recovery codes</button></form>
        <form method="POST" style="margin-top:1.25rem"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="disable">
            <div class="form-group"><label for="pw2">Switch it off (your password)</label><input type="password" id="pw2" name="password" required autocomplete="current-password"></div>
            <button class="btn btn-secondary" type="submit">Switch off two-step sign-in</button></form>
    <?php elseif ($secret): ?>
        <p>1. In an authenticator app (such as Microsoft Authenticator or Google Authenticator), add an account and type in this setup key:</p>
        <p style="font-family:monospace;font-size:1.15rem;letter-spacing:.08em"><strong><?= htmlspecialchars(trim(chunk_split($secret, 4, ' '))) ?></strong></p>
        <p class="small">On a phone, <a href="<?= htmlspecialchars(Totp::uri($secret, (string) $me['email'], 'Digital ID')) ?>">this link</a> opens your app directly.</p>
        <p>2. Enter the 6-digit code it shows, to prove it works:</p>
        <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="enable">
            <div class="form-group"><label for="code">Code</label><input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required maxlength="7"></div>
            <button class="btn btn-primary" type="submit">Switch it on</button></form>
    <?php else: ?>
        <p>Add a second step to signing in: a 6-digit code from an authenticator app on your phone, as well as your password. It is not on yet.</p>
        <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="start"><button class="btn btn-primary" type="submit">Set it up</button></form>
    <?php endif; ?>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once SRC_PATH . '/classes/TwoFactor.php';

Auth::requireLogin();
RBAC::requireAdmin();

$db = getDbConnection();
TwoFactor::ensureSchema();
$orgId = (int) Auth::getOrganisationId();
$isSuper = RBAC::isSuperAdmin();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    if (!CSRF::validatePost()) { $error = 'Please try again.'; }
    else {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $q = $db->prepare('SELECT id, email, organisation_id FROM users WHERE id = ?'); $q->execute([$uid]); $u = $q->fetch();
        if (!$u || (!$isSuper && (int) $u['organisation_id'] !== $orgId)) { $error = 'That person was not found.'; }
        else { TwoFactor::reset($uid); error_log('two-step reset for user ' . $uid . ' by user ' . Auth::getUserId()); $success = 'Two-step sign-in was reset for ' . $u['email'] . '. They sign in with their password and can set it up again from their own Two-step sign-in page.'; }
    }
}
$sql = 'SELECT u.id, u.email, u.first_name, u.last_name, s.totp_enabled_at FROM users u JOIN user_security s ON s.user_id = u.id WHERE s.totp_enabled_at IS NOT NULL' . ($isSuper ? '' : ' AND u.organisation_id = ?') . ' ORDER BY u.last_name, u.first_name';
$st = $db->prepare($sql); $st->execute($isSuper ? [] : [$orgId]); $rows = $st->fetchAll();
$pageTitle = 'Two-step sign-in';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="card">
    <h1>Two-step sign-in</h1>
    <p>People who have switched on two-step sign-in. If someone has lost their phone <em>and</em> their recovery codes, reset it here: they sign in with their password and can set it up again. Make sure you know who is asking: a reset is the way round the second step.</p>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (!$rows): ?><p>Nobody has switched it on yet. People do that from <strong>Two-step sign-in</strong> in the menu.</p><?php else: ?>
    <table style="width:100%;border-collapse:collapse"><thead><tr><th style="text-align:left;padding:.5rem">Person</th><th style="text-align:left;padding:.5rem">Email</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td style="padding:.5rem"><?= htmlspecialchars(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td><td style="padding:.5rem"><?= htmlspecialchars($r['email']) ?></td>
        <td style="padding:.5rem;text-align:right"><form method="POST" onsubmit="return confirm('Remove two-step sign-in for this person?')"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>"><button class="btn btn-secondary" type="submit">Reset</button></form></td></tr><?php endforeach; ?>
    </tbody></table><?php endif; ?>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>

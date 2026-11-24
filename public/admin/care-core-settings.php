<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
demo_block_posts();
Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$orgId = Auth::getOrganisationId();
CareCore::ensureSchema();
$error = ''; $success = ''; $result = null;
$events = ['person.created', 'person.updated', 'employment.started', 'employment.changed', 'employment.ended', 'learning.recorded'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) { $error = 'Invalid security token.'; }
    else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'connect') {
                $url = trim((string) ($_POST['url'] ?? '')); $key = trim((string) ($_POST['api_key'] ?? ''));
                if ($url === '' || $key === '') throw new RuntimeException('Care Core address and API key are both needed.');
                if ($p = CareCoreClient::addressProblem($url)) throw new RuntimeException($p);
                $client = new CareCoreClient($url, $key);
                $client->check();   // proves the key works before we keep it
                // replace any earlier notification endpoint
                $old = CareCore::get($orgId, 'care_core_webhook_id');
                if ($old !== '') { try { CareCore::client($orgId)->deleteWebhook($old); } catch (Throwable $e) {} }
                CareCore::set($orgId, 'care_core_url', rtrim($url, '/')); CareCore::set($orgId, 'care_core_api_key', $key);
                $token = bin2hex(random_bytes(20));
                $hook = $client->createWebhook(CareCore::webhookUrl($token), $events);
                CareCore::set($orgId, 'care_core_webhook_token', $token); CareCore::set($orgId, 'care_core_webhook_id', $hook['id']); CareCore::set($orgId, 'care_core_webhook_secret', $hook['secret']);
                $result = CareCoreSync::syncOrganisation($orgId, true, $client);
                $success = 'Connected. Staff now come from Care Core.';
            } elseif ($action === 'sync' && CareCore::managed($orgId)) {
                $result = CareCoreSync::syncOrganisation($orgId);
                $success = 'Sync finished.';
            } elseif ($action === 'signin') {
                $r = CareCoreSignIn::save($orgId, (string) ($_POST['client_id'] ?? ''), (string) ($_POST['client_secret'] ?? ''), ($_POST['enabled'] ?? '') === '1', ($_POST['only'] ?? '') === '1');
                if ($r !== true) throw new RuntimeException($r);
                $success = 'Saved.';
            } elseif ($action === 'disconnect') {
                $old = CareCore::get($orgId, 'care_core_webhook_id');
                if ($old !== '' && CareCore::managed($orgId)) { try { CareCore::client($orgId)->deleteWebhook($old); } catch (Throwable $e) {} }
                foreach (['care_core_api_key', 'care_core_webhook_id', 'care_core_webhook_secret', 'care_core_webhook_token'] as $k) CareCore::set($orgId, $k, null);
                $success = 'Disconnected. Existing staff and cards are kept; they are no longer updated from Care Core.';
            }
        } catch (Throwable $e) { $error = $e->getMessage(); }
    }
}
$managed = CareCore::managed($orgId);
$last = CareCore::get($orgId, 'care_core_last_sync');
$lastResult = json_decode(CareCore::get($orgId, 'care_core_last_result') ?: 'null', true);
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="card">
    <h1>Care Core connection</h1>
    <p>Care Core is where your staff are added and looked after. Connect it and Digital ID keeps itself up to date: new staff get a card, role changes follow, and when someone leaves their card stops working straight away. Nobody is added or edited by hand here.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php $res = $result ?? null; if ($res): ?>
        <p>Created <?= (int) $res['created'] ?>, updated <?= (int) $res['updated'] ?>, brought back <?= (int) $res['reactivated'] ?>, switched off <?= (int) $res['deactivated'] ?>, photos <?= (int) $res['photos'] ?>.</p>
        <?php if (!empty($res['errors'])): ?><div class="alert alert-error"><strong>Could not add:</strong><ul><?php foreach ($res['errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php endif; ?>

    <?php if ($managed): ?>
        <p><strong>Connected</strong> to <?= htmlspecialchars(CareCore::get($orgId, 'care_core_url')) ?>. Last full check: <?= $last ? htmlspecialchars($last) : 'never' ?>. Changes arrive within seconds, and a full check runs every night.</p>
        <?php if (!$res && $lastResult && !empty($lastResult['errors'])): ?><div class="alert alert-error">Last check had <?= count($lastResult['errors']) ?> problem(s): <?= htmlspecialchars(implode('; ', array_slice($lastResult['errors'], 0, 5))) ?></div><?php endif; ?>
        <form method="POST" style="display:inline"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="sync"><button class="btn btn-primary" type="submit">Check now</button></form>
        <form method="POST" style="display:inline" onsubmit="return confirm('Stop updating from Care Core? Existing staff and cards stay as they are.')"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="disconnect"><button class="btn btn-secondary" type="submit">Disconnect</button></form>
    <?php else: ?>
        <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="connect">
            <div class="form-group"><label for="url">Care Core address</label><input type="url" id="url" name="url" placeholder="https://core.example.org" required></div>
            <div class="form-group"><label for="api_key">API key</label><input type="password" id="api_key" name="api_key" autocomplete="off" required>
                <small>Create a key in Care Core with permission to read people and employments. It is stored encrypted.</small></div>
            <button class="btn btn-primary" type="submit">Connect and import staff</button>
        </form>
    <?php endif; ?>
    <?php if ($managed): ?>
    <hr>
    <h2>Sign in with Care Core</h2>
    <p>Let people sign in to Digital ID through Care Core, with the same password and second step as everything else. Register Digital ID in Care Core (Admin, Single sign-in) with this redirect address, then paste the client ID and secret it gives you. People still need an account here: signing in never creates one and never gives a role.</p>
    <p><code><?= htmlspecialchars(CareCoreSignIn::redirectUri()) ?></code></p>
    <form method="POST"><?= CSRF::tokenField() ?><input type="hidden" name="action" value="signin">
        <div class="form-group"><label for="ccid">Client ID</label><input id="ccid" name="client_id" value="<?= htmlspecialchars(CareCore::get($orgId, 'sso_client_id')) ?>" autocomplete="off"></div>
        <div class="form-group"><label for="ccs">Client secret<?= CareCore::get($orgId, 'sso_secret') !== '' ? ' (leave empty to keep the one saved)' : '' ?></label><input id="ccs" name="client_secret" type="password" autocomplete="off"></div>
        <div class="form-group"><label><input type="checkbox" name="enabled" value="1" <?= CareCore::get($orgId, 'sso_enabled') === '1' ? 'checked' : '' ?>> Show "Sign in with Care Core"</label></div>
        <div class="form-group"><label><input type="checkbox" name="only" value="1" <?= CareCore::get($orgId, 'sso_only') === '1' ? 'checked' : '' ?>> Everyone except administrators must use it (administrators keep a password in case Care Core is down)</label></div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
    <?php endif; ?>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>

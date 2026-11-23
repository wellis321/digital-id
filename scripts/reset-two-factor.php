<?php
/** For a locked-out administrator who has lost both their phone and their recovery codes:  php scripts/reset-two-factor.php email */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/config/config.php';
require_once SRC_PATH . '/classes/TwoFactor.php';
$email = strtolower(trim((string) ($argv[1] ?? '')));
$db = getDbConnection();
$s = $db->prepare('SELECT id, email FROM users WHERE email = ?'); $s->execute([$email]); $u = $email === '' ? false : $s->fetch();
if (!$u) { fwrite(STDERR, "Usage: php scripts/reset-two-factor.php email   (no login with that address)\n"); exit(1); }
TwoFactor::reset((int) $u['id']);
echo "Two-step sign-in removed for {$u['email']}. They can sign in with their password and set it up again.\n";

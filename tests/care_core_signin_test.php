<?php
/**
 * Sign in through Care Core:  php tests/care_core_signin_test.php
 * Needs a MySQL server (credentials from .env or the environment). It makes and empties its own database, digitalid_signin_test, with just the tables it needs.
 */
$env = [];
foreach (is_file(dirname(__DIR__) . '/.env') ? file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $l) { $l = trim($l); if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue; [$k, $v] = explode('=', $l, 2); $env[trim($k)] = trim($v, " \t\"'"); }
$g = fn(string $k, string $d = '') => (string) (getenv($k) !== false && getenv($k) !== '' ? getenv($k) : ($env[$k] ?? $d));
$pdo = new PDO('mysql:host=' . $g('DB_HOST', '127.0.0.1') . ';port=' . $g('DB_PORT', '3306'), $g('DB_USER', 'root'), $g('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec('DROP DATABASE IF EXISTS digitalid_signin_test'); $pdo->exec('CREATE DATABASE digitalid_signin_test CHARACTER SET utf8mb4'); $pdo->exec('USE digitalid_signin_test');
$pdo->exec('CREATE TABLE organisations (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100)) ENGINE=InnoDB');
$pdo->exec("CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, organisation_id INT NULL, email VARCHAR(190), is_active TINYINT(1) NOT NULL DEFAULT 1, email_verified TINYINT(1) NOT NULL DEFAULT 1, password_hash VARCHAR(255) NULL) ENGINE=InnoDB");
$pdo->exec('CREATE TABLE roles (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50)) ENGINE=InnoDB');
$pdo->exec('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL) ENGINE=InnoDB');
$pdo->exec('CREATE TABLE employees (id INT AUTO_INCREMENT PRIMARY KEY, organisation_id INT NULL) ENGINE=InnoDB');
$pdo->exec("INSERT INTO roles (name) VALUES ('superadmin'), ('organisation_admin'), ('staff')");
define('DEMO_MODE', false); define('DB_NAME', 'digitalid_signin_test'); define('DB_USER', 'test'); define('DB_PASS', 'test'); define('APP_URL', 'https://id.example.test');
function getDbConnection() { return $GLOBALS['pdo']; }
function url($p) { return '/' . ltrim($p, '/'); }
$GLOBALS['pdo'] = $pdo;
putenv('APP_KEY=' . base64_encode(str_repeat('k', 32)));
require dirname(__DIR__) . '/shared-auth/src/RBAC.php';
require dirname(__DIR__) . '/src/classes/CareCore.php';
require dirname(__DIR__) . '/src/classes/CareCoreSignIn.php';
$pdo->exec("INSERT INTO organisations (name) VALUES ('Test')"); $org = (int) $pdo->lastInsertId();
$mk = function (string $email, string $role) use ($pdo, $org) { $pdo->prepare('INSERT INTO users (organisation_id, email) VALUES (?,?)')->execute([$org, $email]); $id = (int) $pdo->lastInsertId(); $pdo->exec("INSERT INTO user_roles (user_id, role_id) SELECT $id, id FROM roles WHERE name = '$role'"); return $pdo->query("SELECT * FROM users WHERE id = $id")->fetch(); };
$admin = $mk('admin@example.test', 'organisation_admin'); $user = $mk('person@example.test', 'staff');

$fail = 0;
function check(string $name, bool $ok, string $why = ''): void { global $fail; echo ($ok ? '  ok    ' : '  FAIL  ') . $name . ($ok || !$why ? '' : "\n        $why") . "\n"; if (!$ok) $fail++; }
$b64 = fn(string $r): string => rtrim(strtr(base64_encode($r), '+/', '-_'), '=');
$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]); $det = openssl_pkey_get_details($key);
$jwks = ['keys' => [['kty' => 'RSA', 'kid' => 'k1', 'alg' => 'RS256', 'n' => $b64($det['rsa']['n']), 'e' => $b64($det['rsa']['e'])]]];
$issuer = 'https://cc.example.test'; $cid = 'cc_testclient01';
$disc = ['issuer' => $issuer, 'authorization_endpoint' => $issuer . '/oidc/authorize', 'token_endpoint' => $issuer . '/oidc/token', 'jwks_uri' => $issuer . '/oidc/jwks'];
$GLOBALS['tok'] = null; $GLOBALS['nonce'] = 'x';
CareCoreSignIn::$http = function (string $m, string $url, ?array $form) use ($disc, $jwks): array {
    if (str_ends_with($url, 'openid-configuration')) return [200, json_encode($disc)];
    if (str_ends_with($url, '/oidc/jwks')) return [200, json_encode($jwks)];
    if (str_ends_with($url, '/oidc/token')) return $GLOBALS['tok'] ? [200, json_encode(['id_token' => $GLOBALS['tok']])] : [400, '{"error":"invalid_grant"}'];
    return [404, ''];
};
$mint = function (array $over = [], string $alg = 'RS256') use ($b64, $key, $issuer, $cid): string {
    $claims = $over + ['iss' => $issuer, 'sub' => 'u-1', 'aud' => $cid, 'iat' => time(), 'exp' => time() + 300, 'nonce' => $GLOBALS['nonce'], 'email' => 'Person@Example.test', 'amr' => ['pwd', 'otp']];
    $h = $b64(json_encode(['alg' => $alg, 'kid' => 'k1'])); $b = $b64(json_encode($claims)); openssl_sign("$h.$b", $sig, $key, OPENSSL_ALGO_SHA256);
    return "$h.$b." . $b64($sig);
};

// storage: connect, save, read back
CareCore::ensureSchema(); CareCore::set($org, 'care_core_url', $issuer);
check('off until set up', !CareCoreSignIn::on($org) && CareCoreSignIn::organisations() === []);
check('cannot switch on without a client ID', is_string(CareCoreSignIn::save($org, '', '', true, false)));
check('saved and on', CareCoreSignIn::save($org, $cid, 'secret-value-long-enough', true, false) === true && CareCoreSignIn::on($org));
$s = CareCoreSignIn::settings($org);
check('the secret is read back opened, and listed as on', $s && $s['secret'] === 'secret-value-long-enough' && count(CareCoreSignIn::organisations()) === 1);
check('the secret is stored sealed', !str_contains((string) $pdo->query("SELECT setting_value FROM organisation_settings WHERE setting_key = 'sso_secret'")->fetchColumn(), 'secret-value'));
check('only-way-in is off until asked for', !CareCoreSignIn::only($org, $user) && !CareCoreSignIn::only($org, $admin));
check('when it is the only way in, administrators keep a password', CareCoreSignIn::save($org, $cid, '', true, true) === true && CareCoreSignIn::only($org, $user) && !CareCoreSignIn::only($org, $admin));
CareCoreSignIn::save($org, $cid, '', true, false);

// the sign-in
$go = function (array $over = [], string $alg = 'RS256') use ($org, $mint): array|string {
    $_SESSION = []; [$u, $e] = CareCoreSignIn::begin($org, '/next');
    if (!$u) return (string) $e;
    parse_str((string) parse_url($u, PHP_URL_QUERY), $q); $GLOBALS['nonce'] = $q['nonce']; $GLOBALS['q'] = $q;
    $GLOBALS['tok'] = $mint($over, $alg);
    return CareCoreSignIn::complete(['code' => 'c', 'state' => $q['state']]);
};
$r = $go(); $q = $GLOBALS['q'];
check('the sign-in address asks for the code flow with PKCE', str_starts_with((string) ($r ? '1' : ''), '1') && $q['code_challenge_method'] === 'S256' && $q['client_id'] === $cid && $q['response_type'] === 'code');
check('a valid sign-in finds the account by email (any capitals), keeps where to go, and notes the second step', is_array($r) && (int) $r[0]['id'] === (int) $user['id'] && $r[1] === '/next' && $r[2] === true, is_string($r) ? $r : '');
$_SESSION = []; [$u] = CareCoreSignIn::begin($org, '/'); parse_str((string) parse_url($u, PHP_URL_QUERY), $q); $GLOBALS['nonce'] = $q['nonce']; $GLOBALS['tok'] = $mint();
$ok1 = is_array(CareCoreSignIn::complete(['code' => 'c', 'state' => $q['state']])); $again = CareCoreSignIn::complete(['code' => 'c', 'state' => $q['state']]);
check('a callback cannot be replayed', $ok1 && is_string($again));
check('without a second step in Care Core it says so', ($x = $go(['amr' => ['pwd']])) && is_array($x) && $x[2] === false);
check('the wrong issuer is refused', is_string($go(['iss' => 'https://evil.example'])));
check('a token for another product is refused', is_string($go(['aud' => 'someone-else'])));
check('an expired token is refused', is_string($go(['exp' => time() - 600])));
check('a token from the future is refused', is_string($go(['iat' => time() + 600, 'exp' => time() + 900])));
check('the wrong nonce is refused', is_string($go(['nonce' => 'other'])));
check('no algorithm other than RS256 is accepted', is_string($go([], 'none')) && is_string($go([], 'HS256')));
$_SESSION = []; [$u] = CareCoreSignIn::begin($org, '/'); $GLOBALS['tok'] = $mint(['nonce' => 'whatever']);
check('the wrong state is refused', is_string(CareCoreSignIn::complete(['code' => 'c', 'state' => 'wrong'])));
$_SESSION = []; [$u] = CareCoreSignIn::begin($org, '/'); $_SESSION['cc_oidc']['at'] = time() - 700;
check('a sign-in left for ten minutes expires', is_string(CareCoreSignIn::complete(['code' => 'c', 'state' => $_SESSION['cc_oidc']['state']])));
$_SESSION = []; [$u] = CareCoreSignIn::begin($org, '/'); parse_str((string) parse_url($u, PHP_URL_QUERY), $q); $GLOBALS['nonce'] = $q['nonce'];
$t = explode('.', $mint()); $t[1] = $b64(json_encode(['iss' => $issuer, 'sub' => 'x', 'aud' => $cid, 'iat' => time(), 'exp' => time() + 300, 'nonce' => $q['nonce'], 'email' => 'admin@example.test'])); $GLOBALS['tok'] = implode('.', $t);
check('a token whose contents were changed is refused', is_string(CareCoreSignIn::complete(['code' => 'c', 'state' => $q['state']])));

// accounts
$before = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$x = $go(['email' => 'stranger@example.test']);
check('someone with no account is told which address Care Core sent, and nothing is created', is_string($x) && str_contains($x, 'stranger@example.test') && (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === $before);
$pdo->exec('UPDATE users SET is_active = 0 WHERE id = ' . (int) $user['id']); check('a deactivated account is refused', is_string($go())); $pdo->exec('UPDATE users SET is_active = 1 WHERE id = ' . (int) $user['id']);
$_SESSION = ['sso_strong' => 1]; require_once dirname(__DIR__) . '/src/classes/Totp.php'; require_once dirname(__DIR__) . '/src/classes/TwoFactor.php';
check('a second step done in Care Core counts for the require setting', TwoFactor::mustSetUp((int) $user['id']) === false);
$_SESSION = [];
CareCoreSignIn::$http = null;
echo $fail ? "\n$fail FAILED\n" : "\nAll checks passed.\n";
exit($fail ? 1 : 0);

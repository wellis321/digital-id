<?php
/**
 * Sign in through Care Core (OpenID Connect, authorisation code with PKCE). Care Core proves who someone is and whether they used a second step.
 * This product still owns the person's account and role: nobody is created by signing in, and no role is taken from Care Core.
 * Per organisation: the client ID and sealed secret Care Core gave this product, whether the button is on, and whether everyone except administrators
 * must use it (administrators always keep a password, so a Care Core outage cannot lock a service out).
 * $http can be replaced in tests: fn(string $method, string $url, ?array $form): array{0:int,1:string}
 */
class CareCoreSignIn
{
    /** @var callable|null */
    public static $http = null;
    /** Tests only: stands in for stored settings. @var array<string,mixed>|null */
    public static ?array $testSettings = null;
    public const SKEW = 60;
    /** Seconds between checks that someone who came in through Care Core is still allowed in. */
    public const RECHECK = 300;

    public static function redirectUri(): string { return rtrim((string) APP_URL, '/') . '/care-core-login.php'; }   // not url(): that adds /public/, and the address registered in Care Core must be exact

    private static function pdo(): PDO { return getDbConnection(); }

    /** @return ?array<string,mixed> this organisation's sign-in settings (secret opened), or null when not set up */
    public static function settings(int $org): ?array
    {
        if (self::$testSettings !== null) return self::$testSettings['org'] === $org ? self::$testSettings : null;
        try { CareCore::ensureSchema(); $url = CareCore::get($org, 'care_core_url'); $id = CareCore::get($org, 'sso_client_id'); $secret = CareCore::get($org, 'sso_secret'); }
        catch (Throwable $e) { return null; }
        if ($url === '' || $id === '' || $secret === '') return null;
        return ['org' => $org, 'base_url' => $url, 'sso_client_id' => $id, 'secret' => $secret, 'sso_enabled' => (int) (CareCore::get($org, 'sso_enabled') === '1'), 'sso_only' => (int) (CareCore::get($org, 'sso_only') === '1')];
    }

    /** @return list<array{id:int,name:string}> organisations that have switched it on */
    public static function organisations(): array
    {
        try {
            CareCore::ensureSchema();
            $rows = self::pdo()->query("SELECT o.id, o.name FROM organisation_settings e JOIN organisations o ON o.id = e.organisation_id WHERE e.setting_key = 'sso_enabled' AND e.setting_value = '1' ORDER BY o.name")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) { return []; }
        return array_values(array_filter($rows, static fn($o) => self::on((int) $o['id'])));
    }

    /** @return true|string */
    public static function save(int $org, string $clientId, string $secret, bool $enabled, bool $only): true|string
    {
        CareCore::ensureSchema();
        if (CareCore::get($org, 'care_core_url') === '') return 'Connect to Care Core first.';
        $clientId = trim($clientId); $secret = trim($secret);
        if ($enabled && $clientId === '') return 'Paste the client ID Care Core gave this product.';
        if ($clientId !== '' && !preg_match('/^[A-Za-z0-9_\-]{6,60}$/', $clientId)) return 'That client ID does not look right.';
        if ($enabled && $secret === '' && CareCore::get($org, 'sso_secret') === '') return 'Paste the client secret Care Core gave this product.';
        CareCore::set($org, 'sso_client_id', $clientId);
        if ($secret !== '') CareCore::set($org, 'sso_secret', $secret);
        CareCore::set($org, 'sso_enabled', $enabled ? '1' : '0');
        CareCore::set($org, 'sso_only', $enabled && $only ? '1' : '0');
        return true;
    }

    /** @param array<string,mixed> $user */
    public static function isAdmin(array $user): bool
    {
        $roles = RBAC::getUserRolesById((int) $user['id']);
        return in_array('superadmin', $roles, true) || in_array('organisation_admin', $roles, true);
    }

    /** This product's own account for the person, matched by email within the organisation. Nobody is created. @param array<string,mixed> $c @return array<string,mixed>|string */
    private static function userFor(int $org, array $c): array|string
    {
        $email = strtolower((string) ($c['email'] ?? ''));
        $none = 'Care Core knows you as ' . ($email !== '' ? $email : 'an unknown address') . ', but there is no account with that email address in this product yet. Ask your administrator to add you with exactly that address.';
        if ($email === '') return $none;
        $s = self::pdo()->prepare('SELECT * FROM users WHERE organisation_id = ? AND LOWER(email) = ?'); $s->execute([$org, $email]);
        $u = $s->fetch(PDO::FETCH_ASSOC);
        if (!$u) return $none;
        if (!$u['is_active']) return 'Your account here has been deactivated. Ask your administrator.';
        return $u;
    }


    public static function on(int $org): bool { $s = self::settings($org); return $s !== null && (int) $s['sso_enabled'] === 1; }

    /** Must this person come in through Care Core? Everyone except administrators, when the organisation has made it the only way in. @param array<string,mixed> $user */
    public static function only(int $org, array $user): bool
    {
        $s = self::settings($org);
        return $s !== null && (int) $s['sso_enabled'] === 1 && (int) $s['sso_only'] === 1 && !self::isAdmin($user);
    }

    /** Remember, in the session, that this person came in through Care Core, so they can be checked again later. */
    public static function remember(int $org, string $sub): void
    {
        $_SESSION['cc_sub'] = $sub; $_SESSION['cc_org'] = $org; $_SESSION['cc_checked'] = time();
    }

    /**
     * May someone who came in through Care Core stay in? Asked of Care Core at most every five minutes, so a leaver, or a login switched off there, is cut off within
     * minutes. If Care Core cannot be reached the person stays in and it is asked again in a minute: Care Core being down never locks a service out.
     */
    public static function stillAllowed(): bool
    {
        $sub = (string) ($_SESSION['cc_sub'] ?? '');
        if ($sub === '' || time() - (int) ($_SESSION['cc_checked'] ?? 0) < self::RECHECK) return true;
        $r = self::introspect((int) ($_SESSION['cc_org'] ?? 0), $sub);
        if ($r === null) { $_SESSION['cc_checked'] = time() - self::RECHECK + 60; return true; }
        if ($r) { $_SESSION['cc_checked'] = time(); return true; }
        return false;
    }

    /** @return ?bool true = still allowed, false = no longer, null = could not find out (unreachable, or our own credentials no longer accepted) */
    public static function introspect(int $org, string $sub): ?bool
    {
        $s = self::settings($org);
        if (!$s) return null;
        [$st, $body] = self::request('POST', rtrim((string) $s['base_url'], '/') . '/oidc/introspect', ['sub' => $sub, 'client_id' => $s['sso_client_id'], 'client_secret' => (string) $s['secret']], 4);
        $d = json_decode($body, true);
        return $st === 200 && is_array($d) && array_key_exists('active', $d) ? (bool) $d['active'] : null;
    }

    private static function b64u(string $raw): string { return rtrim(strtr(base64_encode($raw), '+/', '-_'), '='); }
    private static function unb64u(string $s): ?string { $r = base64_decode(strtr($s, '-_', '+/'), true); return $r === false ? null : $r; }

    /** @return array{0:int,1:string} */
    private static function request(string $method, string $url, ?array $form = null, int $timeout = 10): array
    {
        if (self::$http) return (self::$http)($method, $url, $form);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_FOLLOWLOCATION => false, CURLOPT_USERAGENT => 'digital-id-signin',
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP]);
        if ($form !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form)); }
        $body = curl_exec($ch); $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
        return [$code, is_string($body) ? $body : ''];
    }

    /** @return array<string,mixed>|null */
    private static function discovery(string $base): ?array
    {
        [$st, $body] = self::request('GET', rtrim($base, '/') . '/.well-known/openid-configuration');
        $d = json_decode($body, true);
        return $st === 200 && is_array($d) && ($d['issuer'] ?? '') === rtrim($base, '/') ? $d : null;
    }

    /** Start: [address to send the browser to, null] or [null, message]. Remembers state, nonce and the PKCE verifier. @return array{0:?string,1:?string} */
    public static function begin(int $org, string $next): array
    {
        $s = self::settings($org);
        if (!$s || !(int) $s['sso_enabled']) return [null, 'Sign-in through Care Core is not switched on for that organisation.'];
        $d = self::discovery((string) $s['base_url']);
        if (!$d) return [null, 'Care Core could not be reached just now. Use your password instead, or try again in a minute.'];
        $state = self::b64u(random_bytes(24)); $nonce = self::b64u(random_bytes(24)); $verifier = self::b64u(random_bytes(48));
        $_SESSION['cc_oidc'] = ['org' => $org, 'state' => $state, 'nonce' => $nonce, 'verifier' => $verifier, 'next' => $next, 'at' => time()];
        return [$d['authorization_endpoint'] . '?' . http_build_query([
            'client_id' => $s['sso_client_id'], 'response_type' => 'code', 'redirect_uri' => self::redirectUri(), 'scope' => 'openid email profile', 'state' => $state, 'nonce' => $nonce,
            'code_challenge' => self::b64u(hash('sha256', $verifier, true)), 'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986), null];
    }

    /** Finish. Returns [user row, next path, second step done in Care Core] or a message safe to show. @param array<string,string> $query @return array{0:array<string,mixed>,1:string,2:bool,3:string}|string */
    public static function complete(array $query): array|string
    {
        $saved = $_SESSION['cc_oidc'] ?? null; unset($_SESSION['cc_oidc']);
        $bad = 'That sign-in could not be checked. Please try again.';
        if (!is_array($saved) || time() - (int) $saved['at'] > 600) return 'That sign-in took too long or was not started here. Please try again.';
        if (!empty($query['error'])) return $query['error'] === 'login_required' ? 'You are not signed in to Care Core.' : 'Care Core did not sign you in.';
        if (!hash_equals((string) $saved['state'], (string) ($query['state'] ?? '')) || empty($query['code'])) return $bad;
        $org = (int) $saved['org']; $s = self::settings($org);
        if (!$s || !(int) $s['sso_enabled']) return $bad;
        $d = self::discovery((string) $s['base_url']);
        if (!$d) return 'Care Core could not be reached just now. Use your password instead, or try again in a minute.';
        [$st, $body] = self::request('POST', (string) $d['token_endpoint'], [
            'grant_type' => 'authorization_code', 'code' => $query['code'], 'redirect_uri' => self::redirectUri(), 'code_verifier' => $saved['verifier'],
            'client_id' => $s['sso_client_id'], 'client_secret' => (string) $s['secret'],
        ]);
        $tok = json_decode($body, true);
        if ($st !== 200 || !is_array($tok) || empty($tok['id_token'])) return 'Care Core would not complete the sign-in. Please try again.';
        $claims = self::verify((string) $tok['id_token'], (string) $saved['nonce'], $s, $d);
        if ($claims === null) return $bad;
        $u = self::userFor($org, $claims);
        if (is_string($u)) return $u;
        return [$u, (string) $saved['next'], in_array('otp', (array) ($claims['amr'] ?? []), true), (string) $claims['sub']];
    }

    /** Check an ID token in full. Null for anything wrong. @param array<string,mixed> $s @param array<string,mixed> $d @return array<string,mixed>|null */
    public static function verify(string $jwt, string $nonce, array $s, array $d, ?int $now = null): ?array
    {
        $now ??= time();
        $p = explode('.', $jwt); if (count($p) !== 3) return null;
        $h = json_decode((string) self::unb64u($p[0]), true); $c = json_decode((string) self::unb64u($p[1]), true); $sig = self::unb64u($p[2]);
        if (!is_array($h) || !is_array($c) || $sig === null || ($h['alg'] ?? '') !== 'RS256' || empty($h['kid'])) return null;   // never "none", never HS256
        [$st, $body] = self::request('GET', (string) $d['jwks_uri']);
        $jwks = json_decode($body, true); $jwk = null;
        if ($st === 200 && is_array($jwks)) foreach ((array) ($jwks['keys'] ?? []) as $k) if (is_array($k) && ($k['kid'] ?? '') === $h['kid'] && ($k['kty'] ?? '') === 'RSA') $jwk = $k;
        $pem = $jwk ? self::pem((string) ($jwk['n'] ?? ''), (string) ($jwk['e'] ?? '')) : null;
        if (!$pem || openssl_verify($p[0] . '.' . $p[1], $sig, $pem, OPENSSL_ALGO_SHA256) !== 1) return null;
        $aud = $c['aud'] ?? null; $aud = is_array($aud) ? $aud : [$aud];
        if (($c['iss'] ?? '') !== $d['issuer'] || !in_array($s['sso_client_id'], $aud, true) || !hash_equals($nonce, (string) ($c['nonce'] ?? ''))) return null;
        if ((int) ($c['exp'] ?? 0) < $now - self::SKEW || (int) ($c['iat'] ?? PHP_INT_MAX) > $now + self::SKEW || empty($c['sub'])) return null;
        return $c;
    }

    private static function pem(string $n, string $e): ?string
    {
        $nn = self::unb64u($n); $ee = self::unb64u($e); if ($nn === null || $ee === null || $nn === '' || $ee === '') return null;
        $der = static fn(string $x): string => (ord($x[0]) > 127 ? "\x00" : '') . $x;
        $len = static fn(string $x): string => strlen($x) < 128 ? chr(strlen($x)) : (strlen($x) < 256 ? "\x81" . chr(strlen($x)) : "\x82" . pack('n', strlen($x)));
        $int = static fn(string $x): string => "\x02" . $len($x) . $x; $seq = static fn(string $x): string => "\x30" . $len($x) . $x;
        $rsa = $seq($int($der($nn)) . $int($der($ee)));
        $spki = $seq($seq("\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00") . "\x03" . $len("\x00" . $rsa) . "\x00" . $rsa);
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }
}

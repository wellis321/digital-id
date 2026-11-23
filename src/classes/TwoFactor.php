<?php
/**
 * Two-step sign-in with an authenticator app (RFC 6238) and one-time recovery codes. Password sign-in and the emailed sign-in link ask for it once it is switched on.
 * Sign-in with Microsoft does not, because Microsoft's own settings decide that; the shared demonstration accounts cannot use it. The secret is encrypted (CareCore::seal);
 * recovery codes are kept only as hashes and each works once; an app code works once; wrong tries are limited.
 */
require_once __DIR__ . '/Totp.php';
require_once __DIR__ . '/CareCore.php';

class TwoFactor
{
    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        $db = getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS user_security (user_id INT NOT NULL PRIMARY KEY, totp_secret_sealed TEXT NULL, totp_enabled_at DATETIME NULL, totp_last_step BIGINT NULL,
                   CONSTRAINT fk_usec_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS recovery_codes (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, code_hash CHAR(64) NOT NULL, used_at DATETIME NULL, INDEX idx_rc_user (user_id),
                   CONSTRAINT fk_rc_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public static function on(int $userId): bool
    {
        self::ensureSchema();
        $s = getDbConnection()->prepare('SELECT 1 FROM user_security WHERE user_id = ? AND totp_enabled_at IS NOT NULL');
        $s->execute([$userId]);
        return (bool) $s->fetchColumn();
    }

    /** Can this account use it? A password account, and not a shared demonstration account. */
    public static function available(array $user): bool
    {
        if (defined('DEMO_MODE') && DEMO_MODE && str_ends_with(strtolower((string) ($user['email'] ?? '')), '@demo.lochview.test')) return false;
        return !empty($user['password_hash']);
    }

    /** Start setting up: a new secret, kept encrypted and not yet in force. Returns it, to show. */
    public static function begin(int $userId): string
    {
        self::ensureSchema();
        $secret = Totp::newSecret();
        getDbConnection()->prepare('INSERT INTO user_security (user_id, totp_secret_sealed, totp_enabled_at, totp_last_step) VALUES (?, ?, NULL, NULL)
            ON DUPLICATE KEY UPDATE totp_secret_sealed = VALUES(totp_secret_sealed), totp_enabled_at = NULL, totp_last_step = NULL')->execute([$userId, CareCore::seal($secret)]);
        return $secret;
    }

    public static function pendingSecret(int $userId): ?string
    {
        self::ensureSchema();
        $s = getDbConnection()->prepare('SELECT totp_secret_sealed, totp_enabled_at FROM user_security WHERE user_id = ?');
        $s->execute([$userId]);
        $r = $s->fetch();
        if (!$r || $r['totp_secret_sealed'] === null || $r['totp_enabled_at'] !== null) return null;
        return CareCore::open($r['totp_secret_sealed']) ?: null;
    }

    /** Switch it on once the person has shown the app works. @return list<string>|string the recovery codes (shown once), or a message saying what is wrong */
    public static function enable(int $userId, string $code)
    {
        $secret = self::pendingSecret($userId);
        if (!$secret) return 'Start again: there is nothing waiting to be switched on.';
        $step = Totp::verify($secret, $code);
        if ($step === null) return 'That code did not match. Check the time on your phone and try the next code.';
        $db = getDbConnection();
        $db->prepare('UPDATE user_security SET totp_enabled_at = NOW(), totp_last_step = ? WHERE user_id = ?')->execute([$step, $userId]);
        return self::newRecoveryCodes($userId);
    }

    /** @return list<string> */
    public static function newRecoveryCodes(int $userId): array
    {
        self::ensureSchema();
        $db = getDbConnection();
        $db->prepare('DELETE FROM recovery_codes WHERE user_id = ?')->execute([$userId]);
        $out = [];
        $ins = $db->prepare('INSERT INTO recovery_codes (user_id, code_hash) VALUES (?, ?)');
        for ($i = 0; $i < 8; $i++) { $c = bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)); $out[] = $c; $ins->execute([$userId, hash('sha256', $c)]); }
        return $out;
    }

    public static function recoveryLeft(int $userId): int
    {
        self::ensureSchema();
        $s = getDbConnection()->prepare('SELECT COUNT(*) FROM recovery_codes WHERE user_id = ? AND used_at IS NULL');
        $s->execute([$userId]);
        return (int) $s->fetchColumn();
    }

    /** Switch it off. Needs the person's password, so a stolen session cannot do it. @return true|string */
    public static function disable(int $userId, string $password)
    {
        $db = getDbConnection();
        $s = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $s->execute([$userId]);
        $hash = (string) $s->fetchColumn();
        if ($hash === '' || !password_verify($password, $hash)) return 'Your password is not right.';
        self::reset($userId);
        return true;
    }

    /** Remove it: for the person themselves (above) or an administrator helping someone who has lost their phone and codes. */
    public static function reset(int $userId): void
    {
        self::ensureSchema();
        $db = getDbConnection();
        $db->prepare('DELETE FROM user_security WHERE user_id = ?')->execute([$userId]);
        $db->prepare('DELETE FROM recovery_codes WHERE user_id = ?')->execute([$userId]);
    }

    /** Wait for the second step: nothing is signed in yet. */
    public static function startPending(int $userId, string $next = ''): void
    {
        $_SESSION['pending_2fa'] = ['uid' => $userId, 'at' => time(), 'next' => $next];
    }

    /** @return array{uid:int,at:int,next:string}|null the waiting sign-in, or null when none or it has lapsed (10 minutes) */
    public static function pending(): ?array
    {
        $p = $_SESSION['pending_2fa'] ?? null;
        if (!is_array($p) || time() - (int) $p['at'] > 600) { unset($_SESSION['pending_2fa']); return null; }
        return $p;
    }

    /** Check a code at sign-in: the app's current code or a recovery code, each only once. Five wrong tries in 15 minutes pause it. @return true|string true, or what is wrong */
    public static function check(int $userId, string $code)
    {
        require_once __DIR__ . '/RateLimiter.php';
        self::ensureSchema();
        $key = 'twostep_' . $userId;
        if (RateLimiter::isRateLimited($key, 5, 900)) return 'Too many tries. Please wait 15 minutes and sign in again.';
        if (!self::accept($userId, $code)) return 'That code is not right. Use the current code from your app, or a recovery code.';
        RateLimiter::reset($key);
        return true;
    }

    private static function accept(int $userId, string $code): bool
    {
        $db = getDbConnection();
        $code = strtolower(trim($code));
        $s = $db->prepare('SELECT * FROM user_security WHERE user_id = ? AND totp_enabled_at IS NOT NULL');
        $s->execute([$userId]);
        $r = $s->fetch();
        if (!$r) return false;
        if (preg_match('/^\d{3}\s?\d{3}$/', $code)) {
            $step = Totp::verify(CareCore::open((string) $r['totp_secret_sealed']), $code);
            if ($step === null) return false;
            $u = $db->prepare('UPDATE user_security SET totp_last_step = ? WHERE user_id = ? AND (totp_last_step IS NULL OR totp_last_step < ?)');
            $u->execute([$step, $userId, $step]);
            return $u->rowCount() === 1;   // a code works once
        }
        if (preg_match('/^[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}$/', $code)) {
            $u = $db->prepare('UPDATE recovery_codes SET used_at = NOW() WHERE user_id = ? AND code_hash = ? AND used_at IS NULL');
            $u->execute([$userId, hash('sha256', $code)]);
            return $u->rowCount() === 1;
        }
        return false;
    }

    // ── Requiring it ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    public const LEVELS = [
        'off'      => 'Not required: each person chooses',
        'admins'   => 'Required for administrators',
        'everyone' => 'Required for everyone who signs in with a password',
    ];
    private const SETTING = 'require_two_step';

    public static function requirement(int $orgId): string
    {
        $s = getDbConnection()->prepare('SELECT setting_value FROM organisation_settings WHERE organisation_id = ? AND setting_key = ?');
        $s->execute([$orgId, self::SETTING]);
        $v = (string) $s->fetchColumn();
        return isset(self::LEVELS[$v]) ? $v : 'off';
    }

    public static function setRequirement(int $orgId, string $level): bool
    {
        if (!isset(self::LEVELS[$level])) return false;
        getDbConnection()->prepare('INSERT INTO organisation_settings (organisation_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')->execute([$orgId, self::SETTING, $level]);
        return true;
    }

    /** Must this person set it up before going on? Only people who sign in with a password; not the shared demonstration accounts; not once it is on. */
    public static function mustSetUp(int $userId): bool
    {
        $db = getDbConnection();
        $s = $db->prepare('SELECT id, email, password_hash, organisation_id FROM users WHERE id = ?');
        $s->execute([$userId]);
        $u = $s->fetch();
        if (!$u || !self::available($u)) return false;
        $level = self::requirement((int) $u['organisation_id']);
        if ($level === 'off') return false;
        if ($level === 'admins') {
            $roles = RBAC::getUserRolesById($userId);
            if (!in_array('superadmin', $roles, true) && !in_array('organisation_admin', $roles, true)) return false;
        }
        return !self::on($userId);
    }
}

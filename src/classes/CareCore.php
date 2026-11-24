<?php
/**
 * The Care Core connection for an organisation: its settings (the address and API key are stored sealed, never in plain text), the database
 * additions the connector needs (created on first use, so a deploy never depends on running a SQL file by hand), and a client for it.
 *
 * When an organisation is "managed by Care Core", staff are NOT added, imported or edited in Digital ID: Care Core is the single source of truth.
 */
class CareCore
{
    private const KEYS = ['sso_client_id', 'sso_secret', 'sso_enabled', 'sso_only', 'care_core_url', 'care_core_api_key', 'care_core_webhook_id', 'care_core_webhook_secret', 'care_core_webhook_token', 'care_core_last_sync', 'care_core_last_result'];

    // ── Sealing (AES-256-GCM, key from APP_KEY, or derived from the database settings when no APP_KEY is set) ──────────────────────────
    private static function key(): string
    {
        $k = (string) getenv('APP_KEY');
        if ($k !== '') { $raw = base64_decode($k, true); if ($raw !== false && strlen($raw) >= 32) return substr($raw, 0, 32); return hash('sha256', $k, true); }
        return hash('sha256', 'digital-id|' . (defined('DB_NAME') ? DB_NAME : '') . '|' . (defined('DB_USER') ? DB_USER : '') . '|' . (defined('DB_PASS') ? DB_PASS : ''), true);
    }

    public static function seal(string $plain): string
    {
        $iv = random_bytes(12);
        $c = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return 'v1:' . base64_encode($iv . $tag . $c);
    }

    public static function open(?string $sealed): string
    {
        if ($sealed === null || !str_starts_with($sealed, 'v1:')) return '';
        $raw = base64_decode(substr($sealed, 3), true);
        if ($raw === false || strlen($raw) < 29) return '';
        $p = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $p === false ? '' : $p;
    }

    // ── Schema ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        $db = getDbConnection();
        $has = static function (string $table, string $col) use ($db): bool {
            $s = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $s->execute([$table, $col]);
            return (int) $s->fetchColumn() > 0;
        };
        foreach (['care_core_person_id' => 'CHAR(36) NULL', 'care_core_employment_id' => 'CHAR(36) NULL', 'leaves_on' => 'DATE NULL', 'signature_path' => 'VARCHAR(255) NULL',
                  'care_core_photo_hash' => 'CHAR(64) NULL', 'care_core_signature_hash' => 'CHAR(64) NULL', 'last_synced_from_care_core' => 'TIMESTAMP NULL'] as $col => $def) {
            if (!$has('employees', $col)) $db->exec("ALTER TABLE employees ADD COLUMN $col $def");
        }
        $idx = $db->query("SHOW INDEX FROM employees WHERE Key_name = 'uq_cc_person'")->fetch();
        if (!$idx) $db->exec('ALTER TABLE employees ADD UNIQUE KEY uq_cc_person (organisation_id, care_core_person_id)');
        $db->exec("CREATE TABLE IF NOT EXISTS organisation_settings (id INT AUTO_INCREMENT PRIMARY KEY, organisation_id INT NOT NULL, setting_key VARCHAR(255) NOT NULL, setting_value TEXT NULL,
                   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY unique_org_setting (organisation_id, setting_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS care_core_events (event_id CHAR(36) NOT NULL PRIMARY KEY, organisation_id INT NOT NULL, type VARCHAR(60) NOT NULL, outcome VARCHAR(20) NOT NULL DEFAULT 'ok',
                   detail VARCHAR(255) NULL, received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_cc_events_org (organisation_id, received_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS employee_training (id INT AUTO_INCREMENT PRIMARY KEY, employee_id INT NOT NULL, organisation_id INT NOT NULL, care_core_record_id CHAR(36) NOT NULL,
                   title VARCHAR(200) NOT NULL, record_type VARCHAR(30) NOT NULL DEFAULT 'training', provider VARCHAR(200) NULL, completion_date DATE NULL, expiry_date DATE NULL,
                   status VARCHAR(20) NOT NULL DEFAULT 'completed', is_mandatory TINYINT(1) NOT NULL DEFAULT 0, synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                   UNIQUE KEY uq_training_record (employee_id, care_core_record_id), INDEX idx_training_emp (employee_id, expiry_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS login_links (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, token_hash CHAR(64) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME NULL,
                   requested_ip VARCHAR(45) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_login_link_token (token_hash), INDEX idx_login_links_user (user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // ── Settings ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    public static function get(int $orgId, string $key): string
    {
        $s = getDbConnection()->prepare('SELECT setting_value FROM organisation_settings WHERE organisation_id = ? AND setting_key = ?');
        $s->execute([$orgId, $key]);
        $v = (string) ($s->fetchColumn() ?: '');
        return in_array($key, ['care_core_api_key', 'care_core_webhook_secret', 'sso_secret'], true) ? self::open($v) : $v;
    }

    public static function set(int $orgId, string $key, ?string $value): void
    {
        if (!in_array($key, self::KEYS, true)) throw new InvalidArgumentException('unknown setting');
        $db = getDbConnection();
        if ($value === null || $value === '') { $db->prepare('DELETE FROM organisation_settings WHERE organisation_id = ? AND setting_key = ?')->execute([$orgId, $key]); return; }
        if (in_array($key, ['care_core_api_key', 'care_core_webhook_secret', 'sso_secret'], true)) $value = self::seal($value);
        $db->prepare('INSERT INTO organisation_settings (organisation_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')->execute([$orgId, $key, $value]);
    }

    /** Is this organisation's staff list owned by Care Core? */
    public static function managed(int $orgId): bool
    {
        try { self::ensureSchema(); return self::get($orgId, 'care_core_url') !== '' && self::get($orgId, 'care_core_api_key') !== ''; }
        catch (Throwable $e) { return false; }
    }

    public static function client(int $orgId): CareCoreClient
    {
        return new CareCoreClient(self::get($orgId, 'care_core_url'), self::get($orgId, 'care_core_api_key'));
    }

    /** The organisation a webhook belongs to: found by the unguessable token in its address. */
    public static function orgForToken(string $token): ?int
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) return null;
        $s = getDbConnection()->prepare("SELECT organisation_id FROM organisation_settings WHERE setting_key = 'care_core_webhook_token' AND setting_value = ?");
        $s->execute([$token]);
        $id = $s->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    /** The address Care Core is told to send events to. */
    public static function webhookUrl(string $token): string
    {
        $base = defined('APP_URL') ? rtrim((string) APP_URL, '/') : '';
        return $base . url('api/care-core-webhook.php') . '?t=' . $token;
    }
}

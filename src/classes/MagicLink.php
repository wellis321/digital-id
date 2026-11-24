<?php
/**
 * Passwordless sign-in for staff whose records come from Care Core: they ask for a link, we email it, one tap signs them in.
 * Links last 15 minutes, work once, and are stored only as a hash. Asking never reveals whether an address is known.
 * Only someone who is currently staff (active, not past their last day) is sent a link.
 */
class MagicLink
{
    public const TTL_MINUTES = 15;

    /** Send a link if this address belongs to current staff. Always returns quietly: the caller shows the same message either way. */
    public static function request(string $email, string $ip): void
    {
        CareCore::ensureSchema();
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return;
        $db = getDbConnection();
        $s = $db->prepare('SELECT u.id, u.first_name FROM users u LEFT JOIN employees e ON e.user_id = u.id
                           WHERE u.email = ? AND u.is_active = 1 AND u.email_verified = 1 AND (e.id IS NULL OR (e.is_active = 1 AND (e.leaves_on IS NULL OR e.leaves_on >= CURDATE()))) LIMIT 1');
        $s->execute([$email]);
        $u = $s->fetch();
        if (!$u) return;
        $full = $db->prepare('SELECT id, organisation_id FROM users WHERE id = ?'); $full->execute([$u['id']]); $fu = $full->fetch();
        if ($fu && CareCoreSignIn::only((int) $fu['organisation_id'], $fu)) return;   // Care Core is the only way in for this person
        $n = $db->prepare('SELECT COUNT(*) FROM login_links WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'); $n->execute([$u['id']]);
        if ((int) $n->fetchColumn() >= 5) return;   // enough emails for one person in an hour
        $token = bin2hex(random_bytes(32));
        $db->prepare('INSERT INTO login_links (user_id, token_hash, expires_at, requested_ip) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ' . self::TTL_MINUTES . ' MINUTE), ?)')
           ->execute([$u['id'], hash('sha256', $token), substr($ip, 0, 45)]);
        $link = rtrim((string) APP_URL, '/') . url('login-link.php') . '?t=' . $token;
        $name = trim((string) $u['first_name']) ?: 'there';
        Mailer::send($email, 'Your sign-in link for ' . (defined('APP_NAME') ? APP_NAME : 'Digital ID'),
            "Hello $name,\n\nUse this link to sign in. It works once and expires in " . self::TTL_MINUTES . " minutes:\n\n$link\n\nIf you did not ask for it, you can ignore this email: nobody can sign in without it.",
            null, ['personal' => true]);
    }

    /** Is this link still good? (does not use it up) */
    public static function valid(string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return false;
        CareCore::ensureSchema();
        $s = getDbConnection()->prepare('SELECT 1 FROM login_links WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()');
        $s->execute([hash('sha256', $token)]);
        return (bool) $s->fetchColumn();
    }

    /** Use the link: signs the person in. Returns false when it is wrong, used, expired, or they have since left. */
    public static function consume(string $token)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return false;
        CareCore::ensureSchema();
        $db = getDbConnection();
        $hash = hash('sha256', $token);
        $claim = $db->prepare('UPDATE login_links SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()');
        $claim->execute([$hash]);
        if ($claim->rowCount() !== 1) return false;   // the single UPDATE is what makes it one-time, even with two taps at once
        $s = $db->prepare('SELECT l.user_id FROM login_links l JOIN users u ON u.id = l.user_id LEFT JOIN employees e ON e.user_id = u.id
                           WHERE l.token_hash = ? AND u.is_active = 1 AND (e.id IS NULL OR (e.is_active = 1 AND (e.leaves_on IS NULL OR e.leaves_on >= CURDATE())))');
        $s->execute([$hash]);
        $uid = $s->fetchColumn();
        return $uid ? Auth::loginUserById((int) $uid) : false;   // true, false, or 'second_step_required'
    }
}

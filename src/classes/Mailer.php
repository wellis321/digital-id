<?php
/**
 * Outgoing email: authenticated SMTP when SMTP_HOST is set in .env (a real mailbox on your own domain, so SPF/DKIM line up), otherwise PHP's mail().
 * MAIL_TRANSPORT=log writes each message to storage/mail.log instead of sending it (development and tests; the public demo never sends).
 *   SMTP_HOST=smtp.example.com SMTP_PORT=465 SMTP_SECURE=ssl SMTP_USER=... SMTP_PASS=...   MAIL_FROM=hello@example.com  MAIL_FROM_NAME="Digital ID"
 */
class Mailer
{
    /** @param array{personal?:bool,unsubscribe?:string} $opts @return bool true when the message was accepted for delivery */
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null, array $opts = []): bool
    {
        if (defined('DEMO_MODE') && DEMO_MODE) return true;                       // the public demo never sends email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
        $html = preg_match('/<(p|br|div|a|table|h[1-6])\b/i', $body) ? $body : '<p>' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</p>';
        $from = (string) (getenv('MAIL_FROM') ?: 'noreply@example.com');
        $name = (string) (getenv('MAIL_FROM_NAME') ?: (defined('APP_NAME') ? APP_NAME : 'Digital ID'));
        $replyTo = $replyTo ?: (getenv('MAIL_REPLY_TO') ?: null);

        if (strtolower((string) getenv('MAIL_TRANSPORT')) === 'log') {
            $dir = dirname(__DIR__, 2) . '/storage';
            if (!is_dir($dir)) @mkdir($dir, 0750, true);
            file_put_contents($dir . '/mail.log', json_encode(['at' => date('c'), 'to' => $to, 'subject' => $subject, 'text' => self::text($html)], JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
            return true;
        }
        try {
            [$headers, $message] = self::build($to, $subject, $html, $from, $name, $replyTo, getenv('SMTP_HOST') ? true : false, $opts);
            if (getenv('SMTP_HOST')) return self::smtp($to, $from, $headers, $message);
            return mail($to, self::subject($subject), $message, $headers);
        } catch (Throwable $e) {
            error_log('Mailer: ' . $e->getMessage());
            return false;
        }
    }

    private static function clean(string $s): string { return trim(preg_replace('/[\r\n\x00]+/', ' ', $s)); }

    private static function subject(string $s): string { $s = self::clean($s); return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s; }

    public static function text(string $html): string
    {
        $t = preg_replace('#<(br|/p|/div|/li|/h[1-6]|/tr)\b[^>]*>#i', "\n", $html);
        $t = preg_replace('#<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', '$2 ($1)', $t);
        return trim(preg_replace("/[ \t]+\n|\n{3,}/", "\n\n", html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** @return array{0:string,1:string} */
    private static function build(string $to, string $subject, string $html, string $from, string $name, ?string $replyTo, bool $smtp, array $opts): array
    {
        $domain = substr((string) strrchr($from, '@'), 1) ?: 'localhost';
        $b = '=_did_' . bin2hex(random_bytes(12));
        $h = [];
        if ($smtp) { $h[] = 'To: ' . self::clean($to); $h[] = 'Subject: ' . self::subject($subject); }
        $h[] = 'Date: ' . date('r');
        $h[] = 'From: ' . self::subject($name) . ' <' . self::clean($from) . '>';
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $h[] = 'Reply-To: ' . self::clean($replyTo);
        $h[] = 'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>';
        $h[] = 'MIME-Version: 1.0';
        if (empty($opts['personal'])) $h[] = 'Auto-Submitted: auto-generated';
        $h[] = 'Content-Type: multipart/alternative; boundary="' . $b . '"';
        $part = fn(string $type, string $c) => "--$b\r\nContent-Type: $type; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($c), 76, "\r\n");
        return [implode("\r\n", $h), $part('text/plain', self::text($html)) . $part('text/html', $html) . "--$b--\r\n"];
    }

    private static function smtp(string $to, string $from, string $headers, string $message): bool
    {
        $host = (string) getenv('SMTP_HOST'); $port = (int) (getenv('SMTP_PORT') ?: 587);
        $secure = strtolower((string) getenv('SMTP_SECURE')) ?: ($port === 465 ? 'ssl' : 'tls');
        $user = (string) getenv('SMTP_USER'); $pass = (string) getenv('SMTP_PASS');
        $ehlo = substr((string) strrchr($from, '@'), 1) ?: 'localhost';
        $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 12);
        if (!$fp) throw new RuntimeException("SMTP connect failed: $errstr");
        stream_set_timeout($fp, 15);
        $read = function () use ($fp): array { $t = ''; while (($l = fgets($fp, 1024)) !== false) { $t .= $l; if (strlen($l) < 4 || $l[3] !== '-') break; } return [(int) substr($t, 0, 3), trim($t)]; };
        $cmd = function (string $c, array $ok) use ($fp, $read): void { fwrite($fp, $c . "\r\n"); [$code, $txt] = $read(); if (!in_array($code, $ok, true)) throw new RuntimeException('SMTP ' . strtok($c, ' ') . ' → ' . $txt); };
        try {
            [$c] = $read(); if ($c !== 220) throw new RuntimeException('SMTP greeting refused');
            $cmd("EHLO $ehlo", [250]);
            if ($secure === 'tls') { $cmd('STARTTLS', [220]); if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('STARTTLS failed'); $cmd("EHLO $ehlo", [250]); }
            if ($user !== '') { $cmd('AUTH LOGIN', [334]); $cmd(base64_encode($user), [334]); $cmd(base64_encode($pass), [235]); }
            $cmd('MAIL FROM:<' . self::clean($from) . '>', [250]); $cmd('RCPT TO:<' . self::clean($to) . '>', [250, 251]); $cmd('DATA', [354]);
            fwrite($fp, preg_replace('/^\./m', '..', $headers . "\r\n\r\n" . $message) . "\r\n.\r\n");
            [$c, $t] = $read(); if ($c !== 250) throw new RuntimeException('SMTP message rejected → ' . $t);
            @fwrite($fp, "QUIT\r\n");
            return true;
        } finally { @fclose($fp); }
    }
}

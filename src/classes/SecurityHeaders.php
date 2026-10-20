<?php
/**
 * Browser security headers, sent on every web response (see config/config.php).
 *
 * The Content-Security-Policy deliberately contains only directives that do not restrict scripts or
 * styles: this app still uses inline <script> blocks and inline event handlers, which a strict
 * script-src would break. These directives still block clickjacking, <base> tag injection, form
 * hijacking and plugins. Tighten script-src once the inline JavaScript has been moved to files.
 */
class SecurityHeaders
{
    /** @return array<string,string> header name => value */
    public static function build(bool $isProduction, bool $isHttps): array
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'SAMEORIGIN',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()',
            'Content-Security-Policy' => "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'",
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
        // HSTS tells browsers to use HTTPS only for this host. Browsers ignore the header when it arrives over
        // plain HTTP, so it is safe to send in production even though this app often sits behind a CDN that
        // terminates TLS and hides the original protocol (so $isHttps cannot be relied on).
        if ($isProduction) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }
        return $headers;
    }

    public static function isHttps(array $server): bool
    {
        return (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off')
            || (($server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($server['SERVER_PORT'] ?? '') === '443');
    }

    public static function send(bool $isProduction): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }
        foreach (self::build($isProduction, self::isHttps($_SERVER)) as $name => $value) {
            header($name . ': ' . $value);
        }
        header_remove('X-Powered-By');
    }
}

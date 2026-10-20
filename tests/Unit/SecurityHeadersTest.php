<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/classes/SecurityHeaders.php';

class SecurityHeadersTest extends TestCase
{
    public function testAlwaysSendsCoreProtectiveHeaders(): void
    {
        $h = SecurityHeaders::build(false, false);
        $this->assertSame('nosniff', $h['X-Content-Type-Options']);
        $this->assertSame('SAMEORIGIN', $h['X-Frame-Options']);
        $this->assertStringContainsString("frame-ancestors 'self'", $h['Content-Security-Policy']);
        $this->assertStringContainsString("form-action 'self'", $h['Content-Security-Policy']);
        $this->assertStringContainsString("object-src 'none'", $h['Content-Security-Policy']);
    }

    public function testPolicyDoesNotRestrictScriptsOrStyles(): void
    {
        $csp = SecurityHeaders::build(true, true)['Content-Security-Policy'];
        $this->assertStringNotContainsString('script-src', $csp);
        $this->assertStringNotContainsString('default-src', $csp);
        $this->assertStringNotContainsString('style-src', $csp);
    }

    public function testHstsOnlyInProduction(): void
    {
        $this->assertArrayNotHasKey('Strict-Transport-Security', SecurityHeaders::build(false, true));
        $this->assertArrayHasKey('Strict-Transport-Security', SecurityHeaders::build(true, false));
    }

    public function testDetectsHttpsDirectlyAndBehindProxy(): void
    {
        $this->assertTrue(SecurityHeaders::isHttps(['HTTPS' => 'on']));
        $this->assertFalse(SecurityHeaders::isHttps(['HTTPS' => 'off']));
        $this->assertTrue(SecurityHeaders::isHttps(['HTTP_X_FORWARDED_PROTO' => 'https']));
        $this->assertFalse(SecurityHeaders::isHttps([]));
    }
}

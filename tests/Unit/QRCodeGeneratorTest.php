<?php

use PHPUnit\Framework\TestCase;

if (!defined('APP_URL')) {
    define('APP_URL', 'https://example.test');
}
if (!function_exists('url')) {
    function url($path) { return '/' . ltrim($path, '/'); }
}
require_once dirname(__DIR__, 2) . '/src/classes/QRCodeGenerator.php';

class QRCodeGeneratorTest extends TestCase
{
    public function testImageSourceIsAnInlineDataUriNotAnExternalUrl(): void
    {
        $src = QRCodeGenerator::generateImageUrl(str_repeat('a1b2', 16));
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $src);
        $this->assertStringNotContainsString('http', $src);
    }

    public function testSvgIsWellFormedAndSquare(): void
    {
        $svg = QRCodeGenerator::svg('https://example.test/verify.php?token=abc', 240);
        $xml = simplexml_load_string($svg);
        $this->assertNotFalse($xml, 'SVG must be valid XML');
        $this->assertSame('240', (string) $xml['width']);
        $this->assertSame('240', (string) $xml['height']);
        $box = explode(' ', (string) $xml['viewBox']);
        $this->assertSame($box[2], $box[3], 'viewBox is square');
    }

    public function testSameInputGivesSameOutputAndDifferentInputDiffers(): void
    {
        $this->assertSame(QRCodeGenerator::svg('one'), QRCodeGenerator::svg('one'));
        $this->assertNotSame(QRCodeGenerator::svg('one'), QRCodeGenerator::svg('two'));
    }

    public function testVerificationLinkIsWhatIsEncoded(): void
    {
        $info = QRCodeGenerator::generate('tok123');
        $this->assertSame(APP_URL . url('verify.php?token=tok123'), $info['url']);
    }

    /** The QR content includes a live verification token: it must never be sent to another site. */
    public function testNoCodeSendsQrContentToAThirdPartyService(): void
    {
        $root = dirname(__DIR__, 2);
        $bad = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $path = $file->getPathname();
            if (!preg_match('/\.(php|js)$/', $path) || preg_match('#/(vendor|node_modules|tests|playwright-report|test-results)/#', $path)) {
                continue;
            }
            if (preg_match('#(qrserver\.com|chart\.googleapis\.com/chart|api\.qrcode|qr-code-generator\.com|goqr\.me)#i', file_get_contents($path))) {
                $bad[] = substr($path, strlen($root) + 1);
            }
        }
        $this->assertSame([], $bad, 'These files reference an external QR service: ' . implode(', ', $bad));
    }
}

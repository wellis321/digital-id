<?php

use PHPUnit\Framework\TestCase;

/**
 * Pages must not load scripts, styles or fonts from other sites: it leaks visitors' IP addresses and page
 * addresses to third parties, and it lets a third party change what runs in the app. Libraries are bundled
 * under public/assets/libs/.
 */
class ExternalAssetsTest extends TestCase
{
    public function testNoPageLoadsCodeOrFontsFromAnotherSite(): void
    {
        $root = dirname(__DIR__, 2);
        $bad = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $path = $file->getPathname();
            if (!preg_match('/\.(php|html|js|css)$/', $path) || preg_match('#/(vendor|node_modules|tests|playwright-report|test-results|assets/libs|assets/suite)/#', $path)) {
                continue;
            }
            $s = file_get_contents($path);
            if (preg_match('#(cdnjs\.cloudflare\.com|cdn\.jsdelivr\.net|unpkg\.com|fonts\.googleapis\.com|fonts\.gstatic\.com|code\.jquery\.com|maxcdn\.bootstrapcdn\.com|use\.fontawesome\.com)#i', $s)) {
                $bad[] = substr($path, strlen($root) + 1);
            }
        }
        $this->assertSame([], $bad, 'These files load assets from a CDN: ' . implode(', ', $bad));
    }
}

<?php

use PHPUnit\Framework\TestCase;

/**
 * The signed-out front page (includes/home-public.php): it must stay honest and accessible.
 */
class FrontPageTest extends TestCase
{
    private string $root;
    private string $html;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
        $this->html = (string) file_get_contents($this->root . '/includes/home-public.php');
    }

    public function testEveryProductImageExistsAndHasAlt(): void
    {
        preg_match_all('/<img src="<\?php echo \$img\(\'([a-z\-]+\.webp)\'\); \?>"([^>]*)>/', $this->html, $m, PREG_SET_ORDER);
        $this->assertNotEmpty($m);
        foreach ($m as $img) {
            $this->assertFileExists($this->root . '/public/assets/product/' . $img[1]);
            $this->assertMatchesRegularExpression('/alt="[^"]{20,}"/', $img[2], $img[1] . ' needs real alt text');
        }
    }

    public function testNoInlineStylesOrHandlers(): void
    {
        $this->assertDoesNotMatchRegularExpression('/\sstyle="/', $this->html);
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+="/', $this->html);
    }

    public function testExactlyOneH1AndIdsAreUnique(): void
    {
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $this->html));
        preg_match_all('/\sid="([^"<>?]+)"/', $this->html, $m);
        $this->assertSame(count($m[1]), count(array_unique($m[1])));
    }

    public function testDemonstrationIsLabelledAndNoDisclaimerClutter(): void
    {
        $this->assertStringContainsString('invented people', $this->html);
        foreach (['What it will not do', 'Not joined yet', 'not endorsed', 'independently audited'] as $needle) {
            $this->assertStringNotContainsString($needle, $this->html, $needle);
        }
    }

    public function testNoUnsupportedClaimsOrEmoji(): void
    {
        $this->assertDoesNotMatchRegularExpression('/GDPR[- ]compliant|certified|guarantee|ISO ?27001|100%/i', $this->html);
        $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $this->html);
    }

    public function testEveryInPageLinkHasATarget(): void
    {
        $page = $this->html . (string) file_get_contents($this->root . '/includes/marketing-header.php');
        preg_match_all('/href="#([a-z0-9\-]+)"/', $page, $links);
        preg_match_all('/\sid="([a-z0-9\-]+)"/', $page, $ids);
        foreach (array_unique($links[1]) as $l) {
            $this->assertContains($l, $ids[1], "#$l has no target");
        }
    }

    public function testAnimationsRespectReducedMotion(): void
    {
        $css = (string) file_get_contents($this->root . '/public/assets/css/home.css');
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $css);
        $this->assertStringContainsString('.dh-scan { animation: none', $css);
    }
}

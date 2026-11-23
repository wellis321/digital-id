<?php
/** Two-step sign-in: authenticator codes, recovery codes, limits, and that the password step alone signs nobody in once it is on. */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once SRC_PATH . '/classes/TwoFactor.php';

class TwoFactorTest extends TestCase
{
    private $organisationId;
    private $userId;

    protected function setUp(): void
    {
        $this->organisationId = TestHelper::createTestOrganisation('Two Step Org', 'twostep.local');
        $this->userId = TestHelper::createTestUser($this->organisationId, 'ts' . bin2hex(random_bytes(3)) . '@twostep.local', 'password12345', 'staff');
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        TestHelper::cleanupTestData($this->organisationId);
        $_SESSION = [];
        RateLimiter::reset('twostep_' . $this->userId);
    }

    private function switchOn(): array
    {
        $secret = TwoFactor::begin($this->userId);
        $codes = TwoFactor::enable($this->userId, Totp::code($secret));
        $this->assertIsArray($codes);
        return [$secret, $codes];
    }

    public function testCodesFollowTheStandard(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';   // "12345678901234567890", the RFC 6238 test key
        $this->assertSame('94287082', Totp::code($secret, 59, 8));
        $this->assertSame('07081804', Totp::code($secret, 1111111109, 8));
        $this->assertSame('65353130', Totp::code($secret, 20000000000, 8));
    }

    public function testItIsOnlyOnceTheAppHasShownItWorks(): void
    {
        $secret = TwoFactor::begin($this->userId);
        $this->assertFalse(TwoFactor::on($this->userId));
        $this->assertIsString(TwoFactor::enable($this->userId, '000000'));
        $codes = TwoFactor::enable($this->userId, Totp::code($secret));
        $this->assertTrue(TwoFactor::on($this->userId));
        $this->assertCount(8, $codes);
        $this->assertSame(8, TwoFactor::recoveryLeft($this->userId));
    }

    public function testTheSecretIsEncryptedAndRecoveryCodesAreOnlyHashes(): void
    {
        [$secret, $codes] = $this->switchOn();
        $db = getDbConnection();
        $stored = (string) $db->query('SELECT totp_secret_sealed FROM user_security WHERE user_id = ' . (int) $this->userId)->fetchColumn();
        $this->assertStringNotContainsString($secret, $stored);
        $hashes = $db->query('SELECT code_hash FROM recovery_codes WHERE user_id = ' . (int) $this->userId)->fetchAll(PDO::FETCH_COLUMN);
        $this->assertNotContains($codes[0], $hashes);
        $this->assertContains(hash('sha256', $codes[0]), $hashes);
    }

    public function testACodeWorksOnceAndARecoveryCodeWorksOnce(): void
    {
        [$secret, $codes] = $this->switchOn();
        $next = Totp::code($secret, time() + 30);
        $this->assertTrue(TwoFactor::check($this->userId, $next));
        $this->assertIsString(TwoFactor::check($this->userId, $next), 'the same code is refused the second time');
        $this->assertTrue(TwoFactor::check($this->userId, $codes[0]));
        $this->assertIsString(TwoFactor::check($this->userId, $codes[0]), 'a recovery code works once');
        $this->assertSame(7, TwoFactor::recoveryLeft($this->userId));
    }

    public function testWrongCodesAreLimited(): void
    {
        $this->switchOn();
        for ($i = 0; $i < 5; $i++) { $this->assertIsString(TwoFactor::check($this->userId, '000000')); }
        $this->assertStringContainsString('Too many', (string) TwoFactor::check($this->userId, '000000'));
    }

    public function testSwitchingOffNeedsThePasswordAndAnAdministratorCanReset(): void
    {
        $this->switchOn();
        $this->assertIsString(TwoFactor::disable($this->userId, 'wrong password'));
        $this->assertTrue(TwoFactor::on($this->userId));
        $this->assertTrue(TwoFactor::disable($this->userId, 'password12345'));
        $this->assertFalse(TwoFactor::on($this->userId));
        $this->switchOn();
        TwoFactor::reset($this->userId);
        $this->assertFalse(TwoFactor::on($this->userId));
        $this->assertSame(0, TwoFactor::recoveryLeft($this->userId));
    }

    public function testThePasswordAloneDoesNotSignAnyoneInOnceItIsOn(): void
    {
        $email = getDbConnection()->query('SELECT email FROM users WHERE id = ' . (int) $this->userId)->fetchColumn();
        $this->switchOn();
        $r = Auth::login($email, 'password12345');
        $this->assertIsArray($r);
        $this->assertSame('second_step_required', $r['error']);
        $this->assertArrayNotHasKey('user_id', $_SESSION, 'no session is started');
        $this->assertNotNull(TwoFactor::pending());
        $this->assertTrue(Auth::loginUserById($this->userId, false), 'after the second step the session starts');
        $this->assertSame((int) $this->userId, (int) $_SESSION['user_id']);
    }

    public function testTheEmailedLinkPathAsksForItToo(): void
    {
        $this->switchOn();
        $this->assertSame('second_step_required', Auth::loginUserById($this->userId));
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testWithoutItSignInIsAsBefore(): void
    {
        $email = getDbConnection()->query('SELECT email FROM users WHERE id = ' . (int) $this->userId)->fetchColumn();
        $this->assertTrue(Auth::login($email, 'password12345'));
        $this->assertSame((int) $this->userId, (int) $_SESSION['user_id']);
    }
}

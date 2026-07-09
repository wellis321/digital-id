<?php
/**
 * Tests for the public verify.php manual (organisation name + employee reference) lookup.
 *
 * Covers the security fixes applied after the "how confident can we be" review:
 * - The full organisation list is no longer exposed to anonymous visitors
 * - Wrong organisation name and wrong employee reference return the identical
 *   generic message (no oracle revealing which part was wrong)
 * - The manual lookup path is rate limited
 */

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

require_once __DIR__ . '/../bootstrap.php';

class VerifyReferenceApiTest extends TestCase {

    private $client;
    private $baseUrl;
    private $organisationId;
    private $organisationName;
    private $otherOrganisationId;
    private $otherOrganisationName;
    private $userId;
    private $employeeId;
    private $employeeReference;

    protected function setUp(): void {
        $this->baseUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false
        ]);

        $suffix = uniqid();
        $this->organisationName = 'Verify Ref Test Org ' . $suffix;
        $this->otherOrganisationName = 'Other Secret Org ' . $suffix;
        $this->employeeReference = 'EMPREF' . $suffix;

        $this->organisationId = TestHelper::createTestOrganisation($this->organisationName, 'verify-ref-test-' . $suffix . '.local');
        // A second organisation, never referenced in requests - used to confirm its name
        // never leaks out via the manual lookup page or its responses.
        $this->otherOrganisationId = TestHelper::createTestOrganisation($this->otherOrganisationName, 'other-secret-' . $suffix . '.local');

        $this->userId = TestHelper::createTestUser(
            $this->organisationId,
            'verify-ref-' . $suffix . '@test.local',
            'password123',
            'staff'
        );
        $this->employeeId = TestHelper::createTestEmployee(
            $this->organisationId,
            $this->userId,
            $this->employeeReference
        );

        // Make sure no earlier test run left this client's rate limit in a tripped state
        $this->resetRateLimits();
    }

    protected function tearDown(): void {
        TestHelper::cleanupTestData($this->organisationId);
        TestHelper::cleanupTestData($this->otherOrganisationId);
        $this->resetRateLimits();
    }

    /**
     * The rate limiter keys on client IP, which is shared by every test in this
     * class (all requests come from this machine) - reset both common loopback
     * forms so one test's attempts never bleed into another's assertions.
     */
    private function resetRateLimits() {
        foreach (['127.0.0.1', '::1'] as $ip) {
            RateLimiter::reset('verify_ref_' . $ip);
        }
    }

    /**
     * Fetch the verify page and pull out the CSRF token, keeping the session
     * cookie so a follow-up POST is validated against the same session.
     */
    private function getCsrfTokenAndCookies() {
        $jar = new CookieJar();
        $response = $this->client->get('/verify.php', ['cookies' => $jar]);
        $body = (string) $response->getBody();

        preg_match('/name="csrf_token" value="([^"]*)"/', $body, $matches);
        $this->assertNotEmpty($matches[1] ?? null, 'Expected to find a CSRF token on the verify page');

        return ['token' => $matches[1], 'jar' => $jar];
    }

    private function postManualLookup($organisationName, $employeeReference, $csrf) {
        return $this->client->post('/verify.php', [
            'cookies' => $csrf['jar'],
            'form_params' => [
                'csrf_token' => $csrf['token'],
                'organisation_name' => $organisationName,
                'employee_reference' => $employeeReference
            ]
        ]);
    }

    /**
     * The organisation picker used to be a <select> populated with every
     * organisation in the system. It must now be a plain text field, and the
     * page must never reveal any organisation's name it wasn't asked about.
     */
    public function testVerifyPageDoesNotExposeOrganisationList() {
        $response = $this->client->get('/verify.php');
        $body = (string) $response->getBody();

        $this->assertStringNotContainsString('organisation_id', $body);
        $this->assertStringNotContainsString('<select', strtolower($body));
        $this->assertStringNotContainsString($this->organisationName, $body);
        $this->assertStringNotContainsString($this->otherOrganisationName, $body);
    }

    public function testManualLookupSucceedsWithValidOrganisationAndReference() {
        $csrf = $this->getCsrfTokenAndCookies();
        $response = $this->postManualLookup($this->organisationName, $this->employeeReference, $csrf);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Verification Successful', $body);
        $this->assertStringContainsString($this->employeeReference, $body);
    }

    public function testWrongOrganisationNameGivesGenericMessage() {
        $csrf = $this->getCsrfTokenAndCookies();
        $response = $this->postManualLookup('Not A Real Organisation ' . uniqid(), $this->employeeReference, $csrf);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('Employee not found.', $body);
        // Must not leak the real organisation's name, or the other organisation's name,
        // while rejecting a made-up one
        $this->assertStringNotContainsString($this->otherOrganisationName, $body);
    }

    public function testWrongEmployeeReferenceGivesGenericMessage() {
        $csrf = $this->getCsrfTokenAndCookies();
        $response = $this->postManualLookup($this->organisationName, 'NOT-A-REAL-REFERENCE', $csrf);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('Employee not found.', $body);
    }

    /**
     * The single most important check from the security review: a wrong
     * organisation name and a wrong employee reference must be indistinguishable,
     * otherwise the error message itself becomes an oracle for enumerating
     * valid organisation names.
     */
    public function testWrongOrganisationAndWrongReferenceGiveIdenticalMessage() {
        $csrfA = $this->getCsrfTokenAndCookies();
        $responseWrongOrg = $this->postManualLookup('Not A Real Organisation ' . uniqid(), $this->employeeReference, $csrfA);

        $this->resetRateLimits();

        $csrfB = $this->getCsrfTokenAndCookies();
        $responseWrongRef = $this->postManualLookup($this->organisationName, 'NOT-A-REAL-REFERENCE', $csrfB);

        preg_match('/verification-failed.*?<p>(.*?)<\/p>/s', (string) $responseWrongOrg->getBody(), $matchWrongOrg);
        preg_match('/verification-failed.*?<p>(.*?)<\/p>/s', (string) $responseWrongRef->getBody(), $matchWrongRef);

        $this->assertNotEmpty($matchWrongOrg[1] ?? null);
        $this->assertNotEmpty($matchWrongRef[1] ?? null);
        $this->assertEquals(trim($matchWrongOrg[1]), trim($matchWrongRef[1]));
    }

    public function testManualLookupIsRateLimited() {
        // 10 attempts/15 min is the configured limit for the reference lookup path
        for ($i = 0; $i < 10; $i++) {
            $csrf = $this->getCsrfTokenAndCookies();
            $response = $this->postManualLookup('Not A Real Organisation', 'NOT-A-REAL-REFERENCE', $csrf);
            $this->assertStringContainsString(
                'Employee not found.',
                (string) $response->getBody(),
                "Attempt " . ($i + 1) . " should still be allowed through"
            );
        }

        $csrf = $this->getCsrfTokenAndCookies();
        $blockedResponse = $this->postManualLookup('Not A Real Organisation', 'NOT-A-REAL-REFERENCE', $csrf);
        $this->assertStringContainsString('Too many verification attempts', (string) $blockedResponse->getBody());
    }
}

<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/classes/CareCoreClient.php';

/** When Care Core cannot answer for a moment, a read is asked again. No network and no real waiting: both are stood in for. */
class CareCoreRetryTest extends TestCase
{
    /** @var list<int> */
    private array $waited = [];
    private int $calls = 0;

    protected function setUp(): void
    {
        $this->waited = []; $this->calls = 0;
        CareCoreClient::$sleep = function (int $s): void { $this->waited[] = $s; };
    }

    protected function tearDown(): void { CareCoreClient::$http = null; CareCoreClient::$sleep = null; }

    /** @param list<int> $statuses one per call; the last one repeats */
    private function answering(array $statuses, string $body = '{"data":[]}'): CareCoreClient
    {
        CareCoreClient::$http = function () use ($statuses, $body): array {
            $status = $statuses[min($this->calls, count($statuses) - 1)]; $this->calls++;
            return [$status, $status === 200 ? $body : '{"detail":"Something went wrong on our side."}', ['content-type' => 'image/jpeg']];
        };
        return new CareCoreClient('https://core.example.test', 'cc_key');
    }

    public function testAReadThatFailsForAMomentSucceeds(): void
    {
        $this->assertSame(['data' => []], $this->answering([500, 503, 200])->json('GET', '/people'));
        $this->assertSame(3, $this->calls);
        $this->assertSame([2, 5], $this->waited);
    }

    public function testAPhotoIsAskedForAgain(): void
    {
        $this->assertSame(['picture', 'image/jpeg'], $this->answering([500, 200], 'picture')->image('abc', 'photo'));
        $this->assertSame(2, $this->calls);
    }

    public function testItGivesUpAndSaysWhy(): void
    {
        $client = $this->answering([500]);
        try { $client->json('GET', '/people'); $this->fail('it should have given up'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('Care Core answered 500', $e->getMessage()); }
        $this->assertSame(4, $this->calls);
        $this->assertSame([2, 5, 10], $this->waited);
    }

    public function testACareCoreThatStaysDownDoesNotHoldTheSyncForEver(): void
    {
        $client = $this->answering([500]);
        for ($i = 0; $i < 20; $i++) { try { $client->json('GET', '/people'); } catch (RuntimeException $e) {} }
        $this->assertLessThanOrEqual(60, array_sum($this->waited));
        $this->assertGreaterThanOrEqual(50, array_sum($this->waited));
    }

    public function testOnlyReadsThatFailedOnCareCoresSideAreAskedAgain(): void
    {
        foreach ([401, 403, 404, 429] as $status) {
            $this->calls = 0;
            try { $this->answering([$status])->json('GET', '/people'); } catch (RuntimeException $e) {}
            $this->assertSame(1, $this->calls, "a $status is not repeated");
        }
        $this->calls = 0;
        try { $this->answering([500])->json('POST', '/webhooks', ['url' => 'x']); } catch (RuntimeException $e) {}
        $this->assertSame(1, $this->calls, 'something being created is never sent twice');
        $this->assertSame([], $this->waited);
    }
}

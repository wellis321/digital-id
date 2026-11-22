<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/classes/CareCoreSync.php';

/** What Care Core's learning records become here (the part that needs no database). */
class TrainingRowsTest extends TestCase
{
    private const T = '2026-11-22';
    private const ID1 = '11111111-1111-4111-8111-111111111111';
    private const ID2 = '22222222-2222-4222-8222-222222222222';

    public function testRecordsWithoutAnIdOrTitleAreIgnored(): void
    {
        $rows = CareCoreSync::trainingRows([['id' => 'nope', 'title' => 'X'], ['id' => self::ID1, 'title' => '  '], ['id' => self::ID2, 'title' => 'Moving and handling']], self::T);
        $this->assertCount(1, $rows);
        $this->assertSame('Moving and handling', $rows[0]['title']);
    }

    public function testACompletedRecordPastItsExpiryIsOutOfDate(): void
    {
        $rows = CareCoreSync::trainingRows([
            ['id' => self::ID1, 'title' => 'First aid', 'status' => 'completed', 'expiry_date' => '2026-01-01'],
            ['id' => self::ID2, 'title' => 'Fire safety', 'status' => 'completed', 'expiry_date' => '2027-01-01'],
        ], self::T);
        $this->assertSame('expired', $rows[0]['status']);
        $this->assertSame('completed', $rows[1]['status']);
    }

    public function testUnknownStatusesAndBadDatesAreNotTrusted(): void
    {
        $rows = CareCoreSync::trainingRows([['id' => self::ID1, 'title' => 'Course', 'status' => 'whatever', 'expiry_date' => '31/12/2027', 'completion_date' => '2026-02-30x', 'is_mandatory' => 1]], self::T);
        $this->assertSame('completed', $rows[0]['status']);
        $this->assertNull($rows[0]['expiry_date']);
        $this->assertNull($rows[0]['completion_date']);
        $this->assertSame(1, $rows[0]['mandatory']);
    }

    public function testLongTextIsCut(): void
    {
        $rows = CareCoreSync::trainingRows([['id' => self::ID1, 'title' => str_repeat('a', 500), 'provider' => str_repeat('b', 500)]], self::T);
        $this->assertSame(200, mb_strlen($rows[0]['title']));
        $this->assertSame(200, mb_strlen($rows[0]['provider']));
    }
}

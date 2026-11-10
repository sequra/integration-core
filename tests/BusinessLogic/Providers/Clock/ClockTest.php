<?php

namespace SeQura\Core\Tests\BusinessLogic\Providers\Clock;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Providers\Clock\Clock;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class ClockTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Providers\Clock
 */
class ClockTest extends TestCase
{
    /**
     * @var string
     */
    private $defaultTimezone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultTimezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);

        parent::tearDown();
    }

    public function testReturnsTheCurrentTimeInPhpsDefaultTimezone(): void
    {
        date_default_timezone_set('Europe/Madrid');

        $timeProvider = new class extends TimeProvider {
            public function __construct()
            {
                parent::__construct();
            }
        };

        $now = (new Clock($timeProvider))->now();

        self::assertSame('Europe/Madrid', $now->getTimezone()->getName());
        self::assertEqualsWithDelta(time(), $now->getTimestamp(), 5);
    }
}

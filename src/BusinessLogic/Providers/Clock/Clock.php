<?php

namespace SeQura\Core\BusinessLogic\Providers\Clock;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\Time\ClockInterface;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class Clock
 *
 * Reads the current time from the infrastructure time provider.
 *
 * @package SeQura\Core\BusinessLogic\Providers\Clock
 */
class Clock implements ClockInterface
{
    /**
     * @var TimeProvider
     */
    protected $timeProvider;

    /**
     * @param TimeProvider $timeProvider
     */
    public function __construct(TimeProvider $timeProvider)
    {
        $this->timeProvider = $timeProvider;
    }

    /**
     * @inheritDoc
     */
    public function now(): DateTime
    {
        return $this->timeProvider->getCurrentLocalTime();
    }
}

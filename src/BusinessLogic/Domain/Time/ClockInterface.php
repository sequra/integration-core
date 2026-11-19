<?php

namespace SeQura\Core\BusinessLogic\Domain\Time;

use DateTime;

/**
 * Interface ClockInterface
 *
 * Tells the domain the current time, so domain logic reads no clock itself.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Time
 */
interface ClockInterface
{
    /**
     * Returns the current time.
     *
     * @return DateTime
     */
    public function now(): DateTime;
}

<?php

namespace SeQura\Core\Infrastructure\TaskExecution\TaskEvents;

use SeQura\Core\Infrastructure\Utility\Events\Event;

/**
 * Class TickEvent.
 *
 * @package SeQura\Core\Infrastructure\Scheduler
 */
class TickEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
}

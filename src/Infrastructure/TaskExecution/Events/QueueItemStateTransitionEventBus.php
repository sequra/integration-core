<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Events;

use SeQura\Core\Infrastructure\Utility\Events\EventBus;

/**
 * Class QueueItemStateTransitionEventBus
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemStateTransitionEventBus extends EventBus
{
    const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
}
